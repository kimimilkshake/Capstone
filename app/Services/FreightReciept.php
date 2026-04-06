<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Throwable;


class FreightReciept
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
            // Check if booking has required relationships
            if (!$booking->exists) {
                Log::error('FreightReciept: Invalid booking object');
                return null;
            }

            // Ensure relationships are loaded
            $booking->load([
                'sender',
                'consignee',
                'voyage.routePort',
                'voyage.vessel',
                'cargoBookings.cargoItem',
                'cargoBookings.cargoClassification',
                'cargoBookings.measurementUnit',
                'cargoBookings.approvedByStaff'
            ]);

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

            $output = $pdf->output();
            
            Log::info('FreightReciept: PDF generated successfully for booking ' . $booking->booking_ref_no);
            
            return $output;
        } catch (Throwable $e) {
            Log::error('FreightReciept: PDF generation failed for booking ' . ($booking->booking_ref_no ?? 'unknown'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
