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
        $voyage = $booking->voyage;
        $lines = [];
        $lines[] = 'LAPULAPU SHIPPING LINES';
        $lines[] = 'BILL OF LADING';
        $lines[] = '';
        $lines[] = 'Vessel Name: ' . ($voyage->vessel_name ?? 'Not specified');
        $lines[] = 'Voyage No.: ' . ($voyage->voyage_code ?? 'N/A');
        $lines[] = 'Bill of Lading (B/L) No.: ' . ($booking->booking_ref_no ?? 'N/A');
        $lines[] = 'Sailing Date: ' . ($voyage ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y') : 'N/A');
        $lines[] = 'Loading Port: ' . ($voyage->loading_port ?? 'Not specified');
        $lines[] = 'Unloading Port: ' . ($voyage->unloading_port ?? 'Not specified');
        $lines[] = '';

        $lines[] = 'Party Details';
        $lines[] = 'Shipper: ' . ($booking->sender->sender_name ?? '');
        $lines[] = 'Shipper Contact Number : ' . ($booking->sender->sender_contactno ?? '');
        $lines[] = 'Shipper Email : ' . ($booking->sender->sender_email ?? '');
        $lines[] = '';
        $lines[] = 'Consignee: ' . ($booking->consignee->consignee_name ?? '');
        $lines[] = 'Consignee Contact Number : ' . ($booking->consignee->consignee_contactno ?? '');
        $lines[] = '';
        $lines[] = 'CARGO DESCRIPTION';

        foreach ($booking->cargoBookings as $c) {
            $desc = $c->cargoItem->cargo_item_description ?? '';
            $class = $c->cargoItem->cargo_item_classification ?? '';
            $dim = "{$c->length}x{$c->width}x{$c->height}";
            $lines[] = "Qty: {$c->quantity} | {$class} | {$desc} | Dim: {$dim} | Wt: {$c->weight}kg";
        }

        // Build content stream
        $stream = "BT\n/F1 12 Tf\n";
        $y = 760;
        foreach ($lines as $line) {
            $stream .= "50 {$y} Td (" . self::escape($line) . ") Tj\n";
            $y -= 16;
        }
        $stream .= "ET\n";

        $objects = [];

        $objects[] = "%PDF-1.4\n";

        // obj 1: catalog
        $objects[] = "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

        // obj 2: pages
        $objects[] = "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

        // obj 3: page
        $mediaBox = '[0 0 612 792]';
        $objects[] = "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox {$mediaBox} /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";

        // obj 4: font
        $objects[] = "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

        // obj 5: content stream (length placeholder)
        $streamLen = strlen($stream);
        $objects[] = "5 0 obj\n<< /Length {$streamLen} >>\nstream\n" . $stream . "endstream\nendobj\n";

        // assemble and compute xref
        $pdf = '';
        $offsets = [];
        foreach ($objects as $obj) {
            $offsets[] = strlen($pdf);
            $pdf .= $obj;
        }

        $xrefPos = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= sprintf("%010d %05d f \n", 0, 65535);
        foreach ($offsets as $off) {
            $pdf .= sprintf("%010d %05d n \n", $off, 0);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xrefPos}\n%%EOF\n";

        return $pdf;
    }
}
