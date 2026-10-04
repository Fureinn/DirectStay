<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\ComplianceDocument;
use App\Services\GatePassService;
use App\Services\RentalAgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'This reservation has been cancelled. New payment uploads are not accepted.');
        }

        if ($booking->payment_status === 'verified') {
            return back()->with('error', 'Payment for this reservation has already been verified by the host.');
        }

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
                'notes' => $validated['reference_number'] ? 'GCash Ref: '.trim($validated['reference_number']) : null,
            ]
        );

        $booking->update([
            'payment_status' => 'submitted',
        ]);

        return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 3])
            ->with('success', 'Payment proof submitted successfully! The host will review and verify it.');
    }

    /**
     * Save / update guest information roster (Phase 1).
     */
    public function saveRoster(Request $request, string $bookingCode): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'This reservation has been cancelled.');
        }

        $validated = $request->validate([
            'lead_name' => ['nullable', 'string', 'max:255'],
            'lead_phone' => ['nullable', 'string', 'max:50'],
            'lead_email' => ['nullable', 'email', 'max:255'],
            'companion_names' => ['nullable', 'array'],
            'companion_names.*' => ['nullable', 'string', 'max:255'],
            'companion_relationships' => ['nullable', 'array'],
            'companion_relationships.*' => ['nullable', 'string', 'max:100'],
            'companion_id_types' => ['nullable', 'array'],
            'companion_id_types.*' => ['nullable', 'string', 'max:100'],
        ]);

        $roster = [];
        if (! empty($validated['companion_names'])) {
            foreach ($validated['companion_names'] as $idx => $name) {
                if (trim((string) $name) !== '') {
                    $roster[] = [
                        'name' => trim((string) $name),
                        'relationship' => $validated['companion_relationships'][$idx] ?? 'Companion',
                        'id_type' => $validated['companion_id_types'][$idx] ?? 'Government Photo ID',
                    ];
                }
            }
        }

        $updateData = ['guest_roster' => $roster];
        if (! empty($validated['lead_name'])) {
            $updateData['guest_name'] = $validated['lead_name'];
        }
        if (! empty($validated['lead_phone'])) {
            $updateData['guest_phone'] = $validated['lead_phone'];
        }

        $booking->update($updateData);

        return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 2])
            ->with('success', 'Guest information and companion roster saved successfully! Proceed to upload identity documents.');
    }

    /**
     * Handle Government ID and live verification selfie upload.
     * Supports full batch upload, individual occupant card upload, and progressive re-uploads.
     */
    public function uploadIdentity(Request $request, string $bookingCode): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'This reservation has been cancelled. Document submissions are disabled.');
        }

        $existingGovIds = $booking->complianceDocuments
            ->filter(fn (ComplianceDocument $document): bool => str_starts_with($document->document_type, 'gov_id_'))
            ->keyBy(fn (ComplianceDocument $document): int => (int) str_replace('gov_id_', '', $document->document_type));
        $hasExistingSelfie = $booking->complianceDocuments->contains('document_type', 'selfie');

        $rules = [
            'gov_ids' => ['nullable', 'array'],
            'selfie' => [$hasExistingSelfie ? 'nullable' : 'sometimes', 'file', 'mimes:jpeg,png,jpg,webp,heic', 'max:10240'],
            'id_types' => ['nullable', 'array'],
            'id_numbers' => ['nullable', 'array'],
        ];

        // If targeted single occupant upload via dedicated card form
        if ($request->has('occupant_index')) {
            $rules['gov_id_single'] = ['required', 'file', 'mimes:jpeg,png,jpg,webp,heic', 'max:10240'];
            $rules['occupant_index'] = ['required', 'integer', 'min:0', 'max:'.($booking->guest_count - 1)];
        } else {
            // Batch or standard form: check if array contains files
            for ($occupantIndex = 0; $occupantIndex < $booking->guest_count; $occupantIndex++) {
                $rules['gov_ids.'.$occupantIndex] = ['nullable', 'file', 'mimes:jpeg,png,jpg,webp,heic', 'max:10240'];
            }
        }

        // If no files uploaded at all and nothing already existing
        if ($existingGovIds->isEmpty() && ! $hasExistingSelfie && ! $request->hasFile('gov_ids') && ! $request->hasFile('gov_id_single') && ! $request->hasFile('selfie')) {
            return back()->withErrors([
                'gov_ids' => 'Please select at least one government photo ID or selfie to upload.',
            ]);
        }

        $validated = $request->validate($rules);
        $folder = 'compliance/'.$booking->booking_code;
        $uploadedCount = 0;

        // 1. Single occupant direct card upload
        if ($request->hasFile('gov_id_single') && $request->has('occupant_index')) {
            $idx = (int) $request->input('occupant_index');
            $file = $request->file('gov_id_single');
            $path = $file->store($folder, 'local');

            $idType = $request->input('id_type', 'Government Photo ID');
            $idNum = $request->input('id_number');
            $note = $idType.($idNum ? ' #'.$idNum : '');

            ComplianceDocument::updateOrCreate(
                ['booking_id' => $booking->id, 'document_type' => 'gov_id_'.$idx],
                [
                    'file_path' => $path,
                    'original_filename' => $file->getClientOriginalName(),
                    'mime_type' => $file->getClientMimeType(),
                    'notes' => $note,
                ]
            );
            $uploadedCount++;
        }

        // 2. Batch array uploads
        if (! empty($validated['gov_ids'])) {
            foreach ($validated['gov_ids'] as $occupantIndex => $governmentIdFile) {
                if ($governmentIdFile instanceof UploadedFile) {
                    $path = $governmentIdFile->store($folder, 'local');
                    $idType = $validated['id_types'][$occupantIndex] ?? 'Government Photo ID';
                    $idNum = $validated['id_numbers'][$occupantIndex] ?? null;
                    $note = $idType.($idNum ? ' #'.$idNum : '');

                    ComplianceDocument::updateOrCreate(
                        ['booking_id' => $booking->id, 'document_type' => 'gov_id_'.$occupantIndex],
                        [
                            'file_path' => $path,
                            'original_filename' => $governmentIdFile->getClientOriginalName(),
                            'mime_type' => $governmentIdFile->getClientMimeType(),
                            'notes' => $note,
                        ]
                    );
                    $uploadedCount++;
                }
            }
        }

        // 3. Biometric Selfie
        if ($request->hasFile('selfie')) {
            $selfieFile = $request->file('selfie');
            $selfiePath = $selfieFile->store($folder, 'local');

            ComplianceDocument::updateOrCreate(
                ['booking_id' => $booking->id, 'document_type' => 'selfie'],
                [
                    'file_path' => $selfiePath,
                    'original_filename' => $selfieFile->getClientOriginalName(),
                    'mime_type' => $selfieFile->getClientMimeType(),
                    'notes' => 'Lead Guest Biometric Verification Selfie',
                ]
            );
            $uploadedCount++;
        }

        $freshIdsCount = ComplianceDocument::where('booking_id', $booking->id)
            ->where('document_type', 'like', 'gov_id_%')
            ->count();
        $freshSelfie = ComplianceDocument::where('booking_id', $booking->id)
            ->where('document_type', 'selfie')
            ->exists();

        if ($freshIdsCount >= $booking->guest_count && $freshSelfie) {
            $msg = 'All '.$booking->guest_count.' guest government IDs and lead guest biometric selfie are secured in the Identity Vault!';
        } else {
            $msg = 'Document uploaded successfully! ('.$freshIdsCount.' of '.$booking->guest_count.' guest IDs secured in vault).';
        }

        return redirect()->route('compliance.portal', ['bookingCode' => $booking->booking_code, 'step' => 2])
            ->with('success', $msg);
    }

    /**
     * Stream private compliance document preview to the authorized guest.
     */
    public function streamDocument(string $bookingCode, ComplianceDocument $document): StreamedResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($document->booking_id !== $booking->id) {
            abort(403, 'Unauthorized access to compliance document.');
        }

        if (! Storage::disk('local')->exists($document->file_path)) {
            abort(404, 'Compliance document file not found.');
        }

        return Storage::disk('local')->response(
            $document->file_path,
            $document->original_filename ?? 'document.jpg'
        );
    }

    /**
     * Handle digital waiver agreement & companion roster submission.
     */
    public function acceptWaiver(Request $request, string $bookingCode): RedirectResponse
    {
        $booking = Booking::where('booking_code', $bookingCode)->firstOrFail();

        if ($booking->status === 'cancelled') {
            return back()->with('error', 'This reservation has been cancelled.');
        }

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
        $booking = Booking::with(['unit.building', 'complianceDocuments', 'bookingAddOns.addOn', 'review'])
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
