<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CargoSampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a route port ID (first available)
        $routePortId = DB::table('route_port')->first()?->route_port_id ?? 1;

        // Create sample cargo items
        $cargoItems = [
            ['cargo_item_description' => 'Rice Sacks (50kg)', 'cargo_item_classification' => 'Dry Goods', 'cargo_item_freight' => 100, 'cargo_item_arrastre' => 50, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
            ['cargo_item_description' => 'Steel Coils', 'cargo_item_classification' => 'Metal/Heavy', 'cargo_item_freight' => 500, 'cargo_item_arrastre' => 200, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
            ['cargo_item_description' => 'Wooden Pallets', 'cargo_item_classification' => 'Wood Products', 'cargo_item_freight' => 150, 'cargo_item_arrastre' => 75, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
            ['cargo_item_description' => 'Plastic Drums', 'cargo_item_classification' => 'Plastic', 'cargo_item_freight' => 120, 'cargo_item_arrastre' => 60, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
            ['cargo_item_description' => 'Glass Bottles (Cartons)', 'cargo_item_classification' => 'Fragile', 'cargo_item_freight' => 200, 'cargo_item_arrastre' => 100, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
            ['cargo_item_description' => 'Electronics Equipment', 'cargo_item_classification' => 'Electronics', 'cargo_item_freight' => 400, 'cargo_item_arrastre' => 150, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
            ['cargo_item_description' => 'Textiles (Rolls)', 'cargo_item_classification' => 'Textiles', 'cargo_item_freight' => 250, 'cargo_item_arrastre' => 125, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
            ['cargo_item_description' => 'Cement Bags (50kg)', 'cargo_item_classification' => 'Dry Goods', 'cargo_item_freight' => 110, 'cargo_item_arrastre' => 55, 'route_port_id' => $routePortId, 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('cargo_item')->insert($cargoItems);

        // Get the most recent active voyage (should be Feb 12 after fresh migrate)
        $voyages = DB::table('voyage')
            ->whereIn('voyage_status', ['Active', 'Pending', 'Scheduled'])
            ->orderBy('voyage_departure_date', 'desc')
            ->limit(1)
            ->get();

        // Only add cargo to existing voyages
        if ($voyages->isNotEmpty()) {
            $voyage = $voyages->first();
            $cargoItemIds = DB::table('cargo_item')->limit(6)->pluck('cargo_item_id')->toArray();

            // Get a sender_id (first available, or create a default one)
            $senderId = DB::table('sender')->first()?->sender_id;
            if (!$senderId) {
                // Create a default sender if none exists
                DB::table('sender')->insert([
                    'sender_name' => 'Default Sender',
                    'sender_email' => 'sender@example.com',
                    'sender_contactno' => '0000000000',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $senderId = DB::table('sender')->latest('sender_id')->first()->sender_id;
            }

            // Get a consignee_id (first available, or create a default one)
            $consigneeId = DB::table('consignee')->first()?->consignee_id;
            if (!$consigneeId) {
                // Create a default consignee if none exists
                DB::table('consignee')->insert([
                    'consignee_name' => 'Default Consignee',
                    'consignee_contactno' => '0000000000',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $consigneeId = DB::table('consignee')->latest('consignee_id')->first()->consignee_id;
            }

            // Create simple cargo data with dimensions
            // Realistic weights: 5-15kg per item (what workers can carry)
            // Large quantities to properly populate hatches with many visible items
            $cargoData = [
                ['cargo_item_id' => $cargoItemIds[0] ?? 1, 'qty' => 100, 'length' => 1.2, 'width' => 0.8, 'height' => 0.6, 'weight' => 10],
                ['cargo_item_id' => $cargoItemIds[1] ?? 2, 'qty' => 80, 'length' => 2.0, 'width' => 1.5, 'height' => 1.0, 'weight' => 8],
                ['cargo_item_id' => $cargoItemIds[2] ?? 3, 'qty' => 120, 'length' => 1.0, 'width' => 1.0, 'height' => 1.2, 'weight' => 12],
                ['cargo_item_id' => $cargoItemIds[3] ?? 4, 'qty' => 90, 'length' => 0.9, 'width' => 0.9, 'height' => 1.0, 'weight' => 9],
                ['cargo_item_id' => $cargoItemIds[4] ?? 5, 'qty' => 150, 'length' => 0.6, 'width' => 0.4, 'height' => 0.5, 'weight' => 5],
                ['cargo_item_id' => $cargoItemIds[5] ?? 6, 'qty' => 110, 'length' => 1.5, 'width' => 1.0, 'height' => 0.8, 'weight' => 11],
            ];

            // Create bookings and receipts
            // Note: We need to create booking records first, then link cargo to them
            $baseRefNo = 100000;
            foreach ($cargoData as $index => $cargo) {
                $refNo = $baseRefNo + $index;

                try {
                    // First, create a booking record if it doesn't exist
                    $bookingExists = DB::table('booking')
                        ->where('booking_ref_no', $refNo)
                        ->exists();

                    if (!$bookingExists) {
                        DB::table('booking')->insert([
                            'booking_ref_no' => $refNo,
                            'voyage_id' => $voyage->voyage_id,
                            'booking_status' => 'Confirmed',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    // Insert cargo receipt
                    DB::table('cargo_receipt')->insert([
                        'voyage_id' => $voyage->voyage_id,
                        'booking_ref_no' => $refNo,
                        'cargo_item_id' => $cargo['cargo_item_id'],
                        'cargo_item_qty' => $cargo['qty'],
                        'sender_id' => $senderId,
                        'consignee_id' => $consigneeId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Insert cargo booking with dimensions
                    DB::table('cargo_booking')->insert([
                        'booking_ref_no' => $refNo,
                        'cargo_item_id' => $cargo['cargo_item_id'],
                        'quantity' => $cargo['qty'],
                        'length' => $cargo['length'],
                        'width' => $cargo['width'],
                        'height' => $cargo['height'],
                        'weight' => $cargo['weight'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    echo "✓ Added cargo booking $refNo\n";
                } catch (\Exception $e) {
                    echo "✗ Error on booking $refNo: " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "✗ No active voyages found!\n";
        }
    }
}
