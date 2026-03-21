<?php

namespace App\Services;

use App\Models\Booking;
use App\Services\QrCodeGenerator;
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

            // Generate QR codes as base64 data URLs (avoids dompdf chroot restrictions)
            $qrCodes = [];
            foreach ($tickets as $ticket) {
                $qrData = $bookingRef . ':' . $ticket->passenger_id;
                $filename = 'pdf_qr_' . $bookingRef . '_' . $ticket->passenger_id;

                $qrCode = QrCodeGenerator::generateAndStore(
                    $bookingRef,
                    $ticket->passenger_id,
                    $qrData,
                    $filename
                );

                if ($qrCode && file_exists($qrCode->qr_code_path)) {
                    $imageData = base64_encode(file_get_contents($qrCode->qr_code_path));
                    $qrCodes[$ticket->passenger_id] = 'data:image/png;base64,' . $imageData;
                    \Log::info('PassengerTicketPdf: QR embedded for passenger ' . $ticket->passenger_id);
                } else {
                    \Log::warning('PassengerTicketPdf: QR missing for passenger ' . $ticket->passenger_id);
                }
            }

            \Log::info('PassengerTicketPdf: qrCodes count=' . count($qrCodes));

            // Use a PDF-specific Blade template for passenger tickets
            $html = view('passenger.passenger_ticket_pdf', [
                'booking'        => $booking,
                'passengerEmail' => $passengerEmail,
                'tickets'        => $tickets,
                'qrCodes'        => $qrCodes,
            ])->render();

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
            \Log::error('PassengerTicketPdf::generate error: ' . $e->getMessage());
            report($e);
            return null;
        }
    }

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
                    if ($cotNo >= (int)trim($start) && $cotNo <= (int)trim($end)) {
                        return $accommodation;
                    }
                } else {
                    if ($cotNo == (int)trim($range)) {
                        return $accommodation;
                    }
                }
            }
        }
        return null;
    }
}
