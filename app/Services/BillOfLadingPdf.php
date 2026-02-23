<?php

namespace App\Services;

use App\Models\Booking;


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

    public static function generate(Booking $booking)
    {
        // Render the Blade view to HTML
        $html = view('authorized.staff.bill_of_lading', ['booking' => $booking])->render();
        // Generate PDF from HTML using dompdf
        $pdf = app('dompdf.wrapper');
        $dompdf = $pdf->getDomPDF();
        $dompdf->set_option('defaultFont', 'DejaVu Sans');
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);

        $pdf->loadHTML($html)->setPaper('A4', 'portrait');
        return $pdf->output();
    }
}
