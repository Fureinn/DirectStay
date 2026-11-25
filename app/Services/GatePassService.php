<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class GatePassService
{
    /**
     * Compile DomPDF instance for the given booking and building.
     */
    public function render(Booking $booking): DomPdfWrapper
    {
        File::ensureDirectoryExists(storage_path('fonts'));

        $booking->loadMissing(['unit.building', 'unit.host', 'complianceDocuments']);

        $building = $booking->unit->building;
        $template = $this->resolveTemplate($building->gate_pass_template);

        $decaLogoPath = public_path('images/pdf_assets/deca_ortigas_logo.png');
        $decaLogoBase64 = file_exists($decaLogoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($decaLogoPath)) : null;

        $buildingLetter = str_contains($booking->unit->unit_number, 'N') ? 'N' : 'P';
        $unitOnlyNumber = preg_replace('/[^0-9]/', '', $booking->unit->unit_number);
        $guestIdDocuments = $this->buildGuestIdDocuments($booking);

        $data = [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $building,
            'buildingLetter' => $buildingLetter,
            'unitOnlyNumber' => $unitOnlyNumber,
            'host' => $booking->unit->host,
            'decaLogoBase64' => $decaLogoBase64,
            'guestIdsByIndex' => $guestIdDocuments['by_index'],
            'attachedGuestIds' => $guestIdDocuments['attached'],
            'generatedAt' => now()->format('F d, Y h:i A'),
        ];

        return Pdf::loadView($template, $data)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
            ]);
    }

    /**
     * Save the rendered PDF file to local private storage and return path.
     */
    public function saveToFile(Booking $booking): string
    {
        $pdf = $this->render($booking);
        $fileName = 'gate_passes/'.$booking->booking_code.'_gate_pass.pdf';

        Storage::disk('local')->put($fileName, $pdf->output());

        return $fileName;
    }

    /**
     * Resolve the view template with safe fallbacks.
     */
    protected function resolveTemplate(?string $template): string
    {
        if ($template && view()->exists($template)) {
            return $template;
        }

        return 'pdf.gate_pass_generic';
    }

    /**
     * Build guest government ID data URIs keyed by occupant index for PDF rendering.
     *
     * @return array{by_index: array<int, array<string, mixed>>, attached: list<array<string, mixed>>}
     */
    protected function buildGuestIdDocuments(Booking $booking): array
    {
        $govDocs = $booking->complianceDocuments
            ->filter(fn ($doc): bool => str_starts_with((string) $doc->document_type, 'gov_id_'))
            ->sortBy(fn ($doc): int => (int) str_replace('gov_id_', '', (string) $doc->document_type));

        $byIndex = [];

        foreach ($govDocs as $doc) {
            $index = (int) str_replace('gov_id_', '', (string) $doc->document_type);
            $guestName = $index === 0
                ? $booking->guest_name
                : ($booking->guest_roster[$index - 1]['name'] ?? 'Guest '.($index + 1));
            $role = $index === 0
                ? 'Lead Guest'
                : ($booking->guest_roster[$index - 1]['relationship'] ?? 'Companion');

            $byIndex[$index] = [
                'occupant_index' => $index,
                'guest_name' => $guestName,
                'role' => $role,
                'notes' => $doc->notes,
                'filename' => $doc->original_filename ?? basename((string) $doc->file_path),
                'data_uri' => $this->encodeImageAsDataUri((string) $doc->file_path, (string) $doc->mime_type),
            ];
        }

        return [
            'by_index' => $byIndex,
            'attached' => array_values($byIndex),
        ];
    }

    /**
     * Read an uploaded image from private storage and return a DomPDF-safe base64 data URI.
     */
    protected function encodeImageAsDataUri(string $filePath, string $mimeType = ''): ?string
    {
        if ($filePath === '' || ! Storage::disk('local')->exists($filePath)) {
            return null;
        }

        $contents = Storage::disk('local')->get($filePath);
        if ($contents === '' || $contents === null) {
            return null;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $resolvedMime = strtolower(trim($mimeType));

        if ($resolvedMime === 'image/webp' || $extension === 'webp') {
            if (function_exists('imagecreatefromstring')) {
                $image = @imagecreatefromstring($contents);
                if ($image !== false) {
                    ob_start();
                    imagepng($image);
                    $pngBytes = ob_get_clean();
                    imagedestroy($image);

                    if (is_string($pngBytes) && $pngBytes !== '') {
                        return 'data:image/png;base64,'.base64_encode($pngBytes);
                    }
                }
            }
        }

        if (! str_starts_with($resolvedMime, 'image/')) {
            $resolvedMime = match ($extension) {
                'png' => 'image/png',
                'gif' => 'image/gif',
                default => 'image/jpeg',
            };
        }

        if (! in_array($resolvedMime, ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'], true)) {
            return null;
        }

        return 'data:'.$resolvedMime.';base64,'.base64_encode($contents);
    }
}
