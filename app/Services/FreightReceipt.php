<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Throwable;


class FreightReceiptPdf
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
            Log::info('FreightReceiptPdf: Starting generation for booking ' . ($booking->booking_ref_no ?? 'unknown'));
            
            // Check if booking has required relationships
            if (!$booking->exists) {
                Log::error('FreightReceiptPdf: Invalid booking object');
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

            // Validate required data
            if (!$booking->sender) {
                Log::error('FreightReceiptPdf: Missing sender for booking ' . $booking->booking_ref_no);
                return null;
            }
            if (!$booking->consignee) {
                Log::error('FreightReceiptPdf: Missing consignee for booking ' . $booking->booking_ref_no);
                return null;
            }
            if ($booking->cargoBookings->isEmpty()) {
                Log::error('FreightReceiptPdf: No cargo bookings for booking ' . $booking->booking_ref_no);
                return null;
            }

            // Check cargo bookings have required relationships
            foreach ($booking->cargoBookings as $cargo) {
                if (!$cargo->cargoItem) {
                    Log::error('FreightReceiptPdf: Missing cargoItem for cargo booking ' . $cargo->id . ' in booking ' . $booking->booking_ref_no);
                    return null;
                }
                if (!$cargo->cargoClassification) {
                    Log::error('FreightReceiptPdf: Missing cargoClassification for cargo booking ' . $cargo->id . ' in booking ' . $booking->booking_ref_no);
                    return null;
                }
                if (!$cargo->measurementUnit) {
                    Log::error('FreightReceiptPdf: Missing measurementUnit for cargo booking ' . $cargo->id . ' in booking ' . $booking->booking_ref_no);
                    return null;
                }
            }

            Log::info('FreightReceiptPdf: All required data validated for booking ' . $booking->booking_ref_no);

            // Use a PDF-specific Blade template with print-friendly styles for dompdf.
            $html = view('authorized.staff.freight_receipt_pdf', ['booking' => $booking])->render();

            Log::info('FreightReceiptPdf: HTML rendered successfully, length: ' . strlen($html));

            // Generate PDF from HTML using dompdf
            $pdf = app('dompdf.wrapper');
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('defaultFont', 'DejaVu Sans');
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('isRemoteEnabled', true);
            $dompdf->set_option('dpi', 96);

            $pdf->loadHTML($html)->setPaper('A4', 'portrait');

            $output = $pdf->output();
            
            Log::info('FreightReceiptPdf: PDF generated successfully for booking ' . $booking->booking_ref_no . ', size: ' . strlen($output) . ' bytes');
            
            return $output;
        } catch (Throwable $e) {
            Log::error('FreightReceiptPdf: PDF generation failed for booking ' . ($booking->booking_ref_no ?? 'unknown'), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}
