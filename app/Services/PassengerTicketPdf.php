<?php

namespace App\Services;

use App\Models\Booking;
use Throwable;

class PassengerTicketPdf
{
    public static function generate($bookingRef, $booking = null, $voyage = null, $passengerEmail = null): ?string
    {
        try {
            // Load full booking model with all relationships if not provided
            $booking = Booking::with([
                'voyage',
                'voyage.vessel',
                'voyage.vessel.accommodations',
                'voyage.routePort',
                'passengerTickets.passenger',
                'passengerTickets.promo'
            ])->where('booking_ref_no', $bookingRef)->first();

            if (!$booking) {
                \Log::warning('PassengerTicketPdf::generate - Booking not found: ' . $bookingRef);
                return null;
            }

            \Log::info('PassengerTicketPdf::generate - Generating PDF for booking ' . $bookingRef);

            // Get tickets (filtered by passenger email if provided)
            $allTickets = $booking->passengerTickets;
            if ($passengerEmail) {
                $tickets = $allTickets->filter(function ($ticket) use ($passengerEmail) {
                    return $ticket->passenger->passenger_email === $passengerEmail;
                });
            } else {
                $tickets = $allTickets;
            }

            // Generate QR codes as PNG base64 data URLs (dompdf cannot render SVG)
            $qrCodes = [];
            \Log::info('PassengerTicketPdf: Starting QR generation for ' . count($tickets) . ' tickets');

            foreach ($tickets as $ticket) {
                $qrData = $bookingRef . ':' . $ticket->passenger_id;
                try {
                    $options = new \chillerlan\QRCode\QROptions([
                        'outputInterface' => \chillerlan\QRCode\Output\QRGdImagePNG::class,
                        'scale' => 5,
                        'outputBase64' => false,
                    ]);
                    $pngBlob = (new \chillerlan\QRCode\QRCode($options))->render($qrData);
                    $qrCodes[$ticket->passenger_id] = 'data:image/png;base64,' . base64_encode($pngBlob);
                    \Log::info('  PNG QR generated for passenger ' . $ticket->passenger_id);
                } catch (\Exception $e) {
                    \Log::warning('  QR generation failed for passenger ' . $ticket->passenger_id . ': ' . $e->getMessage());
                }
            }

            \Log::info('PassengerTicketPdf: Final QR count: ' . count($qrCodes));

            // Use a PDF-specific Blade template for passenger tickets
            $html = view('passenger.passenger_ticket_pdf', [
                'booking' => $booking,
                'passengerEmail' => $passengerEmail,
                'tickets' => $tickets,
                'qrCodes' => $qrCodes
            ])->render();

            \Log::info('PassengerTicketPdf - Template data: tickets=' . count($tickets) . ', qrCodes=' . count($qrCodes));
            foreach ($qrCodes as $passId => $url) {
                \Log::info('  QR Code for passenger ' . $passId . ': ' . substr($url, 0, 100));
            }

            // Generate PDF from HTML using dompdf
            $pdf = app('dompdf.wrapper');
            $dompdf = $pdf->getDomPDF();
            $dompdf->set_option('defaultFont', 'DejaVu Sans');
            $dompdf->set_option('isHtml5ParserEnabled', true);
            $dompdf->set_option('isRemoteEnabled', false); // Disabled: prevents HTTP timeout in containerised environments
            $dompdf->set_option('isFontSubsettingEnabled', false); // Disabled: font subsetting is CPU-intensive, causes timeouts
            $dompdf->set_option('dpi', 96);

            $pdf->loadHTML($html)->setPaper('A4', 'portrait');

            return $pdf->output();
        } catch (Throwable $e) {
            \Log::error('PassengerTicketPdf::generate error: ' . $e->getMessage());
            report($e);
            return null;
        }
    }

    /**
     * Find accommodation by COT number
     */
    private static function findAccommodationByCot($cotNo, $accommodations)
    {
        if (!$cotNo || !$accommodations) {
            return null;
        }

        foreach ($accommodations as $accommodation) {
            $ranges = explode(',', $accommodation->accommodation_cot_range);
            foreach ($ranges as $range) {
                $range = trim($range);
                if (strpos($range, '-') !== false) {
                    list($start, $end) = explode('-', $range);
                    if ($cotNo >= (int) trim($start) && $cotNo <= (int) trim($end)) {
                        return $accommodation;
                    }
                } else {
                    if ($cotNo == (int) trim($range)) {
                        return $accommodation;
                    }
                }
            }
        }
        return null;
    }
}
