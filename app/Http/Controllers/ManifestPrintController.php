<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManifestPrintController extends Controller
{
    // Returns ALL passengers for a voyage as HTML table rows (for printing)
    public function allPassengersTable(Request $request, $voyageId)
    {
        // Fetch all passengers for the voyage (no pagination)
        $voyage = DB::table('voyage')->where('voyage_id', $voyageId)->first();
        $accommodations = DB::table('accommodation')
            ->where('vessel_id', $voyage->vessel_id)
            ->get()
            ->keyBy('accommodation_id');

        $passengers = DB::table('passenger_ticket as pt')
            ->join('passenger as p', 'p.passenger_id', '=', 'pt.passenger_id')
            ->leftJoin('booking as b', 'b.booking_ref_no', '=', 'pt.booking_ref_no')
            ->where('pt.voyage_id', $voyageId)
            ->where(function ($q) {
                $q->whereNotNull('pt.pt_boarded_at')
                  ->orWhere('b.booking_status', 'Confirmed');
            })
            ->select(
                'p.*',
                'pt.passenger_ticket_id',
                'pt.booking_ref_no as booking_ref',
                'pt.pt_boarded_at',
                'pt.pt_ticket_price',
                'pt.pt_cot_no',
                'pt.created_at as ticket_created_at'
            )
            ->get();

        // Add accommodation_name
        foreach ($passengers as $passenger) {
            $matchedAccommodation = null;
            if ($passenger->pt_cot_no) {
                foreach ($accommodations as $accom) {
                    if ($accom->accommodation_cot_range) {
                        $ranges = array_map('trim', explode(',', $accom->accommodation_cot_range));
                        foreach ($ranges as $range) {
                            if (strpos($range, '-') !== false) {
                                [$start, $end] = array_map('trim', explode('-', $range));
                                if ($passenger->pt_cot_no >= (int)$start && $passenger->pt_cot_no <= (int)$end) {
                                    $matchedAccommodation = $accom;
                                    break 2;
                                }
                            } else {
                                if ((int)$range === $passenger->pt_cot_no) {
                                    $matchedAccommodation = $accom;
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
            $passenger->accommodation_name = $matchedAccommodation?->accommodation_name;
        }

        // Render only the table body rows as HTML
        $rows = '';
        foreach ($passengers as $p) {
            $rows .= '<tr>';
            $rows .= '<td>' . ($p->ticket_no ?? $p->booking_ref ?? ($p->booking_ref_no ?? ($p->booking_ref ?? '-'))) . '</td>';
            $rows .= '<td>' . (isset($p->passenger_firstname) || isset($p->passenger_lastname)
                ? trim(($p->passenger_firstname ?? '') . ' ' . ($p->passenger_midinitial ?? '') . ' ' . ($p->passenger_lastname ?? ''))
                : (isset($p->passenger_firstname) ? $p->passenger_firstname : ($p->name ?? ($p->full_name ?? '-')))) . '</td>';
            $rows .= '<td>' . ($p->passenger_age ?? ($p->age ?? '-')) . ' / ' . ($p->passenger_gender ?? ($p->gender ?? '-')) . '</td>';
            $rows .= '<td>' . ($p->passenger_type ?? ($p->category ?? '-')) . '</td>';
            $rows .= '<td>' . ($p->accommodation_name ?? '-') . '</td>';
            $rows .= '<td>' . ($p->pt_cot_no ?? '-') . '</td>';
            $rows .= '<td>' . ($voyage->voyage_departure_date ?? '-') . '</td>';
            $rows .= '<td class="text-end">' . ($p->pt_ticket_price ?? '-') . '</td>';
            $rows .= '<td>' . ($p->pt_boarded_at ? 'Boarded' : (DB::table('booking')->where('booking_ref_no', $p->booking_ref)->value('booking_status') ?? '-')) . '</td>';
            $rows .= '</tr>';
        }
        return response($rows, 200)->header('Content-Type', 'text/html');
    }

    // Returns ALL cargos for a voyage as HTML table rows (for printing)
    public function allCargosTable(Request $request, $voyageId)
    {
        $voyage = \DB::table('voyage')->where('voyage_id', $voyageId)->first();
        $cargos = \DB::table('cargo_receipt as cr')
            ->where('cr.voyage_id', $voyageId)
            ->leftJoin('cargo_booking as cb', function ($join) {
                $join->on('cb.booking_ref_no', '=', 'cr.booking_ref_no')
                     ->on('cb.cargo_item_id', '=', 'cr.cargo_item_id');
            })
            ->leftJoin('cargo_classification as cc', 'cc.cargo_classification_id', '=', 'cb.cargo_classification_id')
            ->leftJoin('cargo_item as ci', 'ci.cargo_item_id', '=', 'cr.cargo_item_id')
            ->leftJoin('cargo_category as cg', 'cg.cargo_category_id', '=', 'ci.cargo_category_id')
            ->leftJoin('sender as s', 's.sender_id', '=', 'cr.sender_id')
            ->leftJoin('consignee as co', 'co.consignee_id', '=', 'cr.consignee_id')
            ->leftJoin('payment as p', 'p.payment_id', '=', 'cr.payment_id')
            ->select(
                'cr.*',
                'cb.quantity',
                'cc.cargo_classification_name',
                'ci.cargo_category_id',
                'cg.cargo_category_name',
                'ci.cargo_item_description',
                'ci.cargo_item_freight',
                's.sender_name',
                's.sender_tin',
                'co.consignee_name',
                'p.total_amount'
            )
            ->distinct()
            ->get();

        $rows = '';
        foreach ($cargos as $c) {
            $rows .= '<tr>';
            $rows .= '<td>' . ($c->bl_number ?? $c->booking_ref_no ?? $c->booking_ref ?? ($c->booking_ref_no ?? '-')) . '</td>';
            $rows .= '<td>' . ($c->quantity ?? $c->cargo_item_qty ?? '-') . '</td>';
            $rows .= '<td>' . ($c->cargo_classification_name ?? 'N/A') . '</td>';
            $rows .= '<td>' . ($c->cargo_category_name ?? 'N/A') . '</td>';
            $rows .= '<td>' . ($c->cargo_item_description ?? 'N/A') . '</td>';
            $rows .= '<td>' . ($c->sender_name ?? 'N/A') . '</td>';
            $rows .= '<td>' . ($c->sender_tin ?? '-') . '</td>';
            $rows .= '<td>' . ($c->consignee_name ?? '-') . '</td>';
            $rows .= '<td class="text-end">' . ($c->cargo_item_freight ?? '-') . '</td>';
            $rows .= '<td class="text-end">' . ((($c->cargo_item_freight ?? 0) * 0.12)) . '</td>';
            $rows .= '<td>20.00</td>';
            $rows .= '<td class="text-end">' . ($c->total_amount ?? 'N/A') . '</td>';
            $rows .= '<td>' . ($c->receipt_no ?? ($c->cargo_receipt_id ?? '-')) . '</td>';
            $rows .= '</tr>';
        }
        return response($rows, 200)->header('Content-Type', 'text/html');
    }
}
