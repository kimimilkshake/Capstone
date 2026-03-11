<?php

namespace App\Services;

use App\Models\Booking;
use Throwable;


class BillOfLadingPdf
{
    private static function escape($text)
    {
        $text = str_replace('\\', '\\\\', $text);
        $text = str_replace('(', '\\(', $text);
        $text = str_replace(')', '\\)', $text);
        $text = str_replace("\r", '', $text);
        $text = str_replace("\n", ' ', $text);
        return $text;
    }

    public static function generate(Booking $booking): ?string
    {
        try {
            // Use a PDF-specific Blade template with print-friendly styles for dompdf.
            $html = view('authorized.staff.bill_of_lading_pdf', ['booking' => $booking])->render();

            // Generate PDF from HTML using dompdf
            $pdf = app('dompdf.wrapper');
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('defaultFont', 'DejaVu Sans');
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_option('dpi', 96);

            $pdf->loadHTML($html)->setPaper('A4', 'portrait');

            return $pdf->output();
        } catch (Throwable $e) {
            report($e);
            return null;
        }
    }
}
