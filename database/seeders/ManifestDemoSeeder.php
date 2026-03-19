<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ManifestDemoSeeder extends Seeder
{
    public function run()
    {
        $voyageId = 2;
        // Seed 10 passengers and 10 passenger_tickets
        for ($i = 1; $i <= 10; $i++) {
            $passengerId = DB::table('passenger')->insertGetId([
                'passenger_firstname' => 'Demo',
                'passenger_midinitial' => 'D',
                'passenger_lastname' => 'User' . $i,
                'passenger_age' => rand(18, 60),
                'passenger_gender' => rand(0, 1) ? 'M' : 'F',
                'passenger_type' => 'Regular',
                'passenger_address' => 'Demo Address',
                'passenger_contactno' => '0917' . rand(1000000, 9999999),
                'passenger_email' => 'demo' . $i . '@mail.com',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // Insert a booking record first to satisfy foreign key constraint
            $bookingRefNo = 1000 + $i;
            $bookingExists = DB::table('booking')->where('booking_ref_no', $bookingRefNo)->exists();
            if (!$bookingExists) {
                DB::table('booking')->insert([
                    'booking_ref_no' => $bookingRefNo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            DB::table('passenger_ticket')->insert([
                'passenger_id' => $passengerId,
                'voyage_id' => $voyageId,
                'booking_ref_no' => $bookingRefNo,
                'pt_ticket_price' => rand(100, 500),
                'pt_cot_no' => $i,
                'pt_valid_until' => Carbon::now()->addYear(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Seed 10 cargos and 10 cargo_receipts
        for ($i = 1; $i <= 10; $i++) {
            $receiptId = DB::table('cargo_receipt')->insertGetId([
                'voyage_id' => $voyageId,
                'cargo_item_id' => 1, // Assumes at least one cargo_item exists
                'cargo_item_qty' => rand(1, 10),
                'sender_id' => 1, // Assumes at least one sender exists
                'consignee_id' => 1, // Assumes at least one consignee exists
                'payment_id' => 1, // Assumes at least one payment exists
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
