<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ComplianceDocument;
use App\Services\GatePassService;
use App\Services\RentalAgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ComplianceController extends Controller
{
    /**
     * Display the Security Compliance and ID Portal for the guest.
     */
    public function portal(string $bookingCode): View
    {
        $booking = Booking::with(['unit.building', 'bookingAddOns.addOn', 'complianceDocuments'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        $paymentDoc = $booking->complianceDocuments->firstWhere('document_type', 'payment_receipt');
        $governmentIds = $booking->complianceDocuments
            ->filter(fn (ComplianceDocument $document): bool => str_starts_with($document->document_type, 'gov_id_'))
            ->keyBy(fn (ComplianceDocument $document): int => (int) str_replace('gov_id_', '', $document->document_type));
        $selfieDoc = $booking->complianceDocuments->firstWhere('document_type', 'selfie');

        return view('compliance.portal', [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $booking->unit->building,
            'paymentDoc' => $paymentDoc,
            'governmentIds' => $governmentIds,
            'selfieDoc' => $selfieDoc,
        ]);
    }

    /**
     * Handle advance deposit payment proof upload (GCash / Bank transfer).
     */
    public function uploadPayment(Request $request, string $bookingCode): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        $validated = $request->validate([
            'payment_receipt' => ['required', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ]);

        $file = $request->file('payment_receipt');
        $folder = 'compliance/'.$booking->booking_code;
        $path = $file->store($folder, 'local');

        ComplianceDocument::updateOrCreate(
            ['booking_id' => $booking->id, 'document_type' => 'payment_receipt'],
            [
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
                'notes' => $validated['reference_number'] ? 'GCash Ref: '.$validated['reference_number'] : null,
            ]
        );

        $booking->update([
            'payment_status' => 'submitted',
        ]);

        return back()->with('success', 'Payment proof submitted successfully! The host will review and verify it.');
    }

    /**
     * Handle Government ID and live verification selfie upload.
     */
    public function uploadIdentity(Request $request, string $bookingCode): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        $rules = [
            'gov_ids' => ['required', 'array', 'size:'.$booking->guest_count],
            'selfie' => ['required', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ];

        for ($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++) {
            $rules['gov_ids.'.$occupantIndex] = ['required', 'file', 'mimes:jpeg,png,jpg,webp', 'max:5120'];
        }

        $validated = $request->validate($rules);

        $folder = 'compliance/'.$booking->booking_code;

        for ($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++) {
            $governmentIdFile = $validated['gov_ids'][$occupantIndex];
            $governmentIdPath = $governmentIdFile->store($folder, 'local');

            ComplianceDocument::updateOrCreate(
                ['booking_id' => $booking->id, 'document_type' => 'gov_id_'.$occupantIndex],
                [
                    'file_path' => $governmentIdPath,
                    'original_filename' => $governmentIdFile->getClientOriginalName(),
                    'mime_type' => $governmentIdFile->getClientMimeType(),
                ]
            );
        }

        // Store Selfie
        $selfieFile = $request->file('selfie');
        $selfiePath = $selfieFile->store($folder, 'local');
        ComplianceDocument::updateOrCreate(
            ['booking_id' => $booking->id, 'document_type' => 'selfie'],
            [
                'file_path' => $selfiePath,
                'original_filename' => $selfieFile->getClientOriginalName(),
                'mime_type' => $selfieFile->getClientMimeType(),
            ]
        );

        return back()->with('success', 'Government IDs for all '.$booking->guest_count.' registered guests and the lead guest selfie were securely uploaded.');
    }

    /**
     * Handle digital waiver agreement & companion roster submission.
     */
    public function acceptWaiver(Request $request, string $bookingCode): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        $validated = $request->validate([
            'agree_rules' => ['accepted'],
            'companion_names' => ['nullable', 'array'],
            'companion_names.*' => ['nullable', 'string', 'max:255'],
            'companion_relationships' => ['nullable', 'array'],
            'companion_relationships.*' => ['nullable', 'string', 'max:100'],
        ]);

        $roster = [];
        if (! empty($validated['companion_names'])) {
            foreach ($validated['companion_names'] as $idx => $name) {
                if (trim((string) $name) !== '') {
                    $roster[] = [
                        'name' => trim((string) $name),
                        'relationship' => $validated['companion_relationships'][$idx] ?? 'Companion',
                    ];
                }
            }
        }

        $booking->update([
            'waiver_accepted_at' => now(),
            'guest_roster' => $roster,
        ]);

        return redirect()->route('compliance.status', ['bookingCode' => $booking->booking_code])
            ->with('success', 'House rules acknowledged and roster logged! Your reservation is queued for host verification.');
    }

    /**
     * Display live booking status and download link once confirmed.
     */
    public function status(string $bookingCode): View
    {
        $booking = Booking::with(['unit.building', 'complianceDocuments', 'bookingAddOns.addOn'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        return view('compliance.status', [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $booking->unit->building,
        ]);
    }

    /**
     * Download the compiled building gate pass PDF (Deca Guest Form).
     */
    public function downloadGatePass(string $bookingCode, GatePassService $gatePassService): Response
    {
        $booking = Booking::with(['unit.building', 'complianceDocuments'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        if ($booking->status !== 'confirmed' && $booking->status !== 'checked_in') {
            abort(403, 'Gate Pass is not available until the host verifies payment and identification.');
        }

        $pdf = $gatePassService->render($booking);

        return $pdf->download($booking->booking_code.'_Deca_Guest_Form_GatePass.pdf');
    }

    /**
     * Download the compiled Staycation Rental Agreement (Official Lease Contract).
     */
    public function downloadRentalAgreement(string $bookingCode, RentalAgreementService $rentalAgreementService): Response
    {
        $booking = Booking::with(['unit.building', 'complianceDocuments'])
            ->where('booking_code', $bookingCode)
            ->firstOrFail();

        if ($booking->status !== 'confirmed' && $booking->status !== 'checked_in') {
            abort(403, 'Rental Agreement contract is available once the reservation is confirmed.');
        }

        $pdf = $rentalAgreementService->render($booking);

        return $pdf->download($booking->booking_code.'_Rental_Agreement_Contract.pdf');
    }
}
