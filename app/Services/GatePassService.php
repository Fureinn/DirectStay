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

        $booking->loadMissing(['unit.building', 'unit.host']);

        $building = $booking->unit->building;
        $template = $this->resolveTemplate($building->gate_pass_template);

        $decaLogoPath = public_path('images/pdf_assets/deca_ortigas_logo.png');
        $decaLogoBase64 = file_exists($decaLogoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($decaLogoPath)) : null;

        $buildingLetter = str_contains($booking->unit->unit_number, 'N') ? 'N' : 'P';
        $unitOnlyNumber = preg_replace('/[^0-9]/', '', $booking->unit->unit_number);

        $data = [
            'booking' => $booking,
            'unit' => $booking->unit,
            'building' => $building,
            'buildingLetter' => $buildingLetter,
            'unitOnlyNumber' => $unitOnlyNumber,
            'host' => $booking->unit->host,
            'decaLogoBase64' => $decaLogoBase64,
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
}
