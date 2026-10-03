<?php

namespace App\Http\Controllers\Host;

use App\Http\Controllers\Controller;
use App\Mail\GuestGatePassMailable;
use App\Models\Booking;
use App\Models\ComplianceDocument;
use App\Models\Transaction;
use App\Services\GatePassService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VerificationController extends Controller
{
    /**
     * Display list of bookings awaiting compliance & payment verification.
     */
    public function index(): View
    {
        $bookings = Booking::with(['unit.building', 'complianceDocuments'])
            ->where(function ($q) {
                $q->where('status', 'pending_verification')
                    ->orWhere('payment_status', 'submitted');
            })
            ->latest()
            ->paginate(15);

        return view('host.verifications.index', [
            'bookings' => $bookings,
        ]);
    }

    /**
     * Display a specific booking verification review screen.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['unit.building', 'unit.host', 'bookingAddOns.addOn', 'complianceDocuments', 'transactions']);

        $paymentDoc = $booking->complianceDocuments->firstWhere('document_type', 'payment_receipt');
        $governmentIds = $booking->complianceDocuments
            ->filter(fn (ComplianceDocument $document): bool => str_starts_with($document->document_type, 'gov_id_'))
            ->keyBy(fn (ComplianceDocument $document): int => (int) str_replace('gov_id_', '', $document->document_type));
        $selfieDoc = $booking->complianceDocuments->firstWhere('document_type', 'selfie');

        return view('host.verifications.show', [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $booking->unit->building,
            'paymentDoc' => $paymentDoc,
            'governmentIds' => $governmentIds,
            'selfieDoc' => $selfieDoc,
        ]);
    }

    /**
     * Securely stream private compliance documents to authorized hosts.
     */
    public function streamDocument(ComplianceDocument $document): StreamedResponse
    {
        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Compliance document file not found.');
        }

        return Storage::disk('local')->response(
            $document->file_path,
            $document->original_filename ?? 'document.jpg'
        );
    }

    /**
     * Approve the booking, confirm payment, compile Gate Pass PDF, and dispatch via email.
     */
    public function approve(Booking $booking, GatePassService $gatePassService): RedirectResponse
    {
        $booking->load(['unit.building', 'complianceDocuments']);

        $governmentIdCount = $booking->complianceDocuments
            ->filter(fn (ComplianceDocument $document): bool => str_starts_with($document->document_type, 'gov_id_'))
            ->count();

        if ($governmentIdCount !== $booking->guest_count || ! $booking->complianceDocuments->contains('document_type', 'selfie')) {
            return back()->with('error', 'Approval requires one government ID for every registered guest and a lead guest verification selfie.');
        }

        // Mark booking and compliance docs as verified
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id(),
        ]);

        $booking->complianceDocuments()->update(['verified_at' => now()]);

        // Complete pending ledger transactions
        Transaction::where('booking_id', $booking->id)
            ->where('status', 'pending')
            ->update(['status' => 'completed']);

        // Compile DomPDF Gate Pass
        try {
            $pdfWrapper = $gatePassService->render($booking);
            $pdfBinary = $pdfWrapper->output();

            // Save PDF gate pass to private storage
            $gatePassService->saveToFile($booking);

            // Dispatch Gate Pass email to guest and building security lobby desk
            $buildingEmail = $booking->unit->building->admin_email;
            $mailable = new GuestGatePassMailable($booking, $pdfBinary);

            Mail::to($booking->guest_email)
                ->cc($buildingEmail)
                ->send($mailable);

            $mailSent = true;
        } catch (\Throwable $e) {
            Log::error('Gate pass generation/email error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $mailSent = false;
        }

        $message = 'Booking '.$booking->booking_code.' verified and confirmed! Building Gate Pass successfully compiled.';
        if ($mailSent) {
            $message .= ' Official Pass emailed to '.$booking->guest_email.' and lobby security ('.$booking->unit->building->admin_email.').';
        }

        return redirect()->route('host.verifications.show', $booking)->with('success', $message);
    }

    /**
     * Reject a booking verification with explanation notes.
     */
    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $booking->update([
            'status' => 'cancelled',
            'notes' => 'Rejected by host: '.$validated['reason'],
        ]);

        return redirect()->route('host.verifications.index')->with('success', 'Booking reservation marked as cancelled.');
    }
}
