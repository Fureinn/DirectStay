<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Illuminate\Support\Facades\File;

class RentalAgreementService
{
    /**
     * Compile DomPDF instance for the Staycation Rental Agreement (Contract).
     */
    public function render(Booking $booking): DomPdfWrapper
    {
        File::ensureDirectoryExists(storage_path('fonts'));

        $booking->loadMissing(['unit.building', 'unit.host']);

        $unit = $booking->unit;
        $building = $unit->building;

        // Choose template based on building / unit
        $template = str_contains($unit->unit_number, 'N')
            ? 'pdf.rental_agreement_deca_n'
            : 'pdf.rental_agreement_deca_p';

        $mirandaLogoPath = public_path('images/pdf_assets/miranda_pad_logo.png');
        $mirandaLogoBase64 = file_exists($mirandaLogoPath) ? 'data:image/png;base64,'.base64_encode(file_get_contents($mirandaLogoPath)) : null;

        $buildingLetter = str_contains($unit->unit_number, 'N') ? 'N' : 'P';
        $unitOnlyNumber = preg_replace('/[^0-9]/', '', $unit->unit_number);

        $data = [
            'booking' => $booking,
            'unit' => $unit,
            'building' => $building,
            'buildingLetter' => $buildingLetter,
            'unitOnlyNumber' => $unitOnlyNumber,
            'host' => $unit->host,
            'mirandaLogoBase64' => $mirandaLogoBase64,
            'agreementDate' => $booking->created_at->format('F d, Y'),
        ];

        return Pdf::loadView($template, $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'serif',
            ]);
    }
}
