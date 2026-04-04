<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\CargoItemSeeder;
use Database\Seeders\PortSeeder;

class DatabaseSeeder extends Seeder
{
    /*
    Seed the application's database.
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }*/

    public function run(): void
    {

        // Check if the database is already seeded
        $this->call(PortSeeder::class);

        // Admin seeders
        DB::table('admin')->insert([
            [
                'admin_name' => 'Admin User',
                'admin_user' => 'admin',
                'admin_email' => 'admin@example.com',
                'admin_password' => Hash::make('12345'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admin_name' => 'Alexander Cohon',
                'admin_user' => 'atcohon',
                'admin_email' => 'lapulines@yahoo.com',
                'admin_password' => Hash::make('cohon01161957'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Get the admin ID from the inserted admins
        $adminIds = DB::table('admin')->pluck('admin_id')->toArray();

        // Staff seeders
        DB::table('staff')->insert([
            [
                'admin_id' => $adminIds[0],
                'staff_name' => 'Staff User',
                'staff_user' => 'staff',
                'staff_password' => Hash::make('12345'), // hashed password
                'staff_dob' => '1990-01-10',
                'staff_gender' => 'M',
                'staff_email' => 'staff@example.com',
                'staff_status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admin_id' => $adminIds[0],
                'staff_name' => 'Sophia Cohon',
                'staff_user' => 'saucohon',
                'staff_password' => Hash::make('cohon07312002'),
                'staff_dob' => '2002-07-31',
                'staff_gender' => 'F',
                'staff_email' => 'sophiaannu.cohon@gmail.com',
                'staff_status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admin_id' => $adminIds[0],
                'staff_name' => 'Shem Cardoza',
                'staff_user' => 'srrcardoza',
                'staff_password' => Hash::make('cardoza03292003'),
                'staff_dob' => '2003-03-29',
                'staff_gender' => 'M',
                'staff_email' => 'shemcardoza7@gmail.com',
                'staff_status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admin_id' => $adminIds[0],
                'staff_name' => 'Clint Englis',
                'staff_user' => 'clcenglis',
                'staff_password' => Hash::make('englis01252002'),
                'staff_dob' => '2002-01-25',
                'staff_gender' => 'M',
                'staff_email' => 'clintenglis16@gmail.com',
                'staff_status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'admin_id' => $adminIds[0],
                'staff_name' => 'Kirzteen Uy',
                'staff_user' => 'kmauy',
                'staff_password' => Hash::make('uy06272002'),
                'staff_dob' => '2002-06-27',
                'staff_gender' => 'F',
                'staff_email' => 'kirzteenuy27@gmail.com',
                'staff_status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        //Measurement Unit Seeders
        DB::table('measurement_unit')->insert([

            [
                'measurement_unit_name' => 'centimeters',
                'measurement_unit_abbreviation' => 'cm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'measurement_unit_name' => 'feet',
                'measurement_unit_abbreviation' => 'ft',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'measurement_unit_name' => 'inches',
                'measurement_unit_abbreviation' => 'in',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'measurement_unit_name' => 'meters',
                'measurement_unit_abbreviation' => 'm',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        //Cargo Classification Seeders
        DB::table('cargo_classification')->insert([
            [
                'cargo_classification_name' => 'bag',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'box',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'bundle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'carbouy',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'carton',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'case',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'drum',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'length',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'pail',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'piece',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'roll',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'sack',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_classification_name' => 'unit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        //Cargo Category Seeders
        DB::table('cargo_category')->insert([
            [
                'cargo_category_name' => 'Agriculture',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Animals/Livestock',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Appliances',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Building Materials',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Electronics',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Fragile',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Furniture',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'General Cargo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Hardware',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Hazardous/Dangerous Goods',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Human Remains',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Liquids',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Machinery',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Oversized/Heavy Cargo',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Perishable',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Pharmaceuticals/Medicals',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Textiles/Clothing',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'cargo_category_name' => 'Vehicle',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('promo')->insert([
            [
                'promo_name' => 'Summer 2026 Promo',
                'promo_code' => 'SUMMER2026',
                'promo_description' => 'Enjoy our special summer promo with discounted fares and exciting offers! Book your voyage now and experience the best of our services while saving big. Don\'t miss out on this limited-time opportunity to make your travel dreams come true!',
                'promo_start_date' => '2026-04-01',
                'promo_end_date' => '2026-06-30',
                'promo_status' => 'Active',
                'promo_discount_rate' => 15.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Route Category seeders
        DB::table('route_category')->insert([
            [
                'route_category_name' => 'BAYBAY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_category_name' => 'TALIBON',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Get inserted Route Category IDs
        $baybayId = DB::table('route_category')->where('route_category_name', 'BAYBAY')->value('route_category_id');
        $talibonId = DB::table('route_category')->where('route_category_name', 'TALIBON')->value('route_category_id');

        // Passenger type discounts for each route category
        $commonDiscounts = [
            ['passenger_type' => 'Regular', 'discount_rate' => 0],
            ['passenger_type' => 'Senior Citizen', 'discount_rate' => 20],
            ['passenger_type' => 'PWD', 'discount_rate' => 20],
            ['passenger_type' => 'Student', 'discount_rate' => 20],
            ['passenger_type' => 'Uniformed Personnel', 'discount_rate' => 20],
            ['passenger_type' => '3 to 11 years old', 'discount_rate' => 50],
        ];

        $baybayDiscounts = array_merge($commonDiscounts, [
            ['passenger_type' => 'Below 3 years old', 'discount_rate' => 75],
        ]);

        $talibonDiscounts = array_merge($commonDiscounts, [
            ['passenger_type' => 'Below 3 years old', 'discount_rate' => 100],
        ]);

        $discountRows = [];
        foreach ($baybayDiscounts as $d) {
            $discountRows[] = array_merge($d, ['route_category_id' => $baybayId, 'created_at' => now(), 'updated_at' => now()]);
        }
        foreach ($talibonDiscounts as $d) {
            $discountRows[] = array_merge($d, ['route_category_id' => $talibonId, 'created_at' => now(), 'updated_at' => now()]);
        }
        DB::table('route_category_passenger_discounts')->insert($discountRows);

        // Get inserted Port IDs
        $cebuPortId = DB::table('ports')
            ->where('terminal_name', 'Pier 2')
            ->where('port_name', 'Cebu Port')
            ->where('city', 'Cebu City')
            ->value('port_id');

        $baybayPortId = DB::table('ports')
            ->where('port_name', 'Baybay Port')
            ->value('port_id');

        $talibonPortId = DB::table('ports')
            ->where('port_name', 'Port of Talibon')
            ->value('port_id');

        if (!$cebuPortId || !$baybayPortId || !$talibonPortId) {
            throw new \Exception('Port IDs not found. Check CSV data.');
        }

        // Route and Port seeders
        DB::table('route_port')->insert([
            [
                'route_category_id' => $baybayId,
                'route_code' => 'CEBBAY',
                'route_origin' => 'Cebu',
                'route_destination' => 'Baybay',
                'port_origin_id' => $cebuPortId,
                'port_destination_id' => $baybayPortId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_category_id' => $talibonId,
                'route_code' => 'CEBTAL',
                'route_origin' => 'Cebu',
                'route_destination' => 'Talibon',
                'port_origin_id' => $cebuPortId,
                'port_destination_id' => $talibonPortId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_category_id' => $baybayId,
                'route_code' => 'BAYCEB',
                'route_origin' => 'Baybay',
                'route_destination' => 'Cebu',
                'port_origin_id' => $baybayPortId,
                'port_destination_id' => $cebuPortId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_category_id' => $talibonId,
                'route_code' => 'TALCEB',
                'route_origin' => 'Talibon',
                'route_destination' => 'Cebu',
                'port_origin_id' => $talibonPortId,
                'port_destination_id' => $cebuPortId,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        //Vessel seeders
        $vessels = [
            [ //FERRY 1
                'admin_id' => 1,
                'vessel_code' => 'F1',
                'vessel_name' => 'MV LAPULAPU FERRY 1',
                'vessel_cot_plan_url' => null,
                'vessel_status' => 'Active',

                'accommodations' => [
                    ['name' => 'Aircon', 'price' => 640.00, 'cot_range' => '141-248'],
                    ['name' => 'Economy A', 'price' => 520.00, 'cot_range' => '1-3, 5-6, 8, 10-12, 14-43, 48-140'],
                    ['name' => 'Economy B', 'price' => 510.00, 'cot_range' => '249-278, 283-338, 343-364'],
                    ['name' => 'Economy C', 'price' => 450.00, 'cot_range' => '365-392, 403-478, 492-511'],
                ],

                'hatches' => [
                    ['label' => '1', 'length' => 10.0, 'width' => 8.0, 'height' => 2.5, 'weight_capacity' => 0.60, 'area_capacity' => 120.0, 'capacity_per_hold' => 78.0],
                    ['label' => '2', 'length' => 10.0, 'width' => 8.0, 'height' => 2.5, 'weight_capacity' => null, 'area_capacity' => 200.0, 'capacity_per_hold' => 130.0],
                ],
            ],

            [ //FERRY 8
                'admin_id' => 1,
                'vessel_code' => 'F8',
                'vessel_name' => 'MV LAPULAPU FERRY 8',
                'vessel_cot_plan_url' => null,
                'vessel_status' => 'Active',

                'accommodations' => [
                    ['name' => 'Aircon A', 'price' => 450.00, 'cot_range' => '201-296'],
                    ['name' => 'Aircon B', 'price' => 430.00, 'cot_range' => '401-468'],
                    ['name' => 'Economy A', 'price' => 370.00, 'cot_range' => '1-12, 15-107, 109, 111, 113'],
                    ['name' => 'Economy B', 'price' => 350.00, 'cot_range' => '301-336'],

                ],

                'hatches' => [
                    ['label' => '1', 'length' => 7.0, 'width' => 7.0, 'height' => 2.2, 'weight_capacity' => 0.60, 'area_capacity' => 65.0, 'capacity_per_hold' => 42.0],
                    ['label' => '2', 'length' => 8.0, 'width' => 7.0, 'height' => 2.2, 'weight_capacity' => null, 'area_capacity' => 123.0, 'capacity_per_hold' => 80.0],
                ],
            ],

            [ //ROSALIA 3
                'admin_id' => 1,
                'vessel_code' => 'R3',
                'vessel_name' => 'MV ROSALIA 3',
                'vessel_cot_plan_url' => null,
                'vessel_status' => 'Active',

                'accommodations' => [
                    ['name' => 'Aircon', 'price' => 640.00, 'cot_range' => '99-140'],
                    ['name' => 'Economy A', 'price' => 520.00, 'cot_range' => '1-98'],
                    ['name' => 'Economy B', 'price' => 510.00, 'cot_range' => '141-250, 259-260'],
                    ['name' => 'Economy C', 'price' => 450.00, 'cot_range' => '261-303, 305-320, 329-377, 382, 395, 398'],

                ],

                'hatches' => [
                    ['label' => '1', 'length' => 8.0, 'width' => 7.0, 'height' => 2.0, 'weight_capacity' => 0.60, 'area_capacity' => 67.0, 'capacity_per_hold' => 44.0],
                    ['label' => '2', 'length' => 7.0, 'width' => 7.0, 'height' => 2.0, 'weight_capacity' => null, 'area_capacity' => 98.0, 'capacity_per_hold' => 63.0],
                ],
            ],
        ];

        //Logic to add to the Accommodations, Hatches, and Vessel tables
        foreach ($vessels as $v) {
            // Calculate total passenger capacity from cot ranges
            $totalPassengerCapacity = 0;
            foreach ($v['accommodations'] as $acc) {
                $ranges = array_map('trim', explode(',', $acc['cot_range']));
                foreach ($ranges as $range) {
                    if (strpos($range, '-') !== false) {
                        [$start, $end] = array_map('intval', explode('-', $range));
                        $totalPassengerCapacity += ($end - $start + 1);
                    }
                }
            }

            // Insert vessel
            $vesselId = DB::table('vessel')->insertGetId([
                'admin_id' => $v['admin_id'],
                'vessel_code' => $v['vessel_code'],
                'vessel_name' => $v['vessel_name'],
                'vessel_total_passenger_capacity' => $totalPassengerCapacity,
                'vessel_cot_plan_url' => $v['vessel_cot_plan_url'],
                'vessel_status' => $v['vessel_status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Insert accommodations
            foreach ($v['accommodations'] as $acc) {
                DB::table('accommodation')->insert([
                    'vessel_id' => $vesselId,
                    'accommodation_name' => $acc['name'],
                    'accommodation_regular_price' => $acc['price'],
                    'accommodation_cot_range' => $acc['cot_range'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Insert hatches
            foreach ($v['hatches'] as $h) {
                DB::table('hatch')->insert([
                    'vessel_id' => $vesselId,
                    'hatch_label' => $h['label'],
                    'hatch_length' => $h['length'],
                    'hatch_width' => $h['width'],
                    'hatch_height' => $h['height'],
                    'hatch_weight_capacity' => $h['weight_capacity'],
                    'hatch_area_capacity' => $h['area_capacity'],
                    'hatch_capacity_per_hold' => $h['capacity_per_hold'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->call(CargoItemSeeder::class);

        // Backfill floor_only, is_stackable, is_breakable flags after items are seeded.
        // floor_only + is_stackable=false: engines, motorcycles, cadavers, tractors, etc.
        $floorOnlyKeywords = [
            'Engine %',
            'Engine:%',
            'Engine block%',
            'Motorcycle%',
            'Hand tractor%',
            'Cadaver%',
            'Dog rottweiler%',
            'Transformer%',
            'Generator (denyo)%',
            'Generator (250K)%',
            'Hollow block machine%',
            'Multicab cargo box%',
            'Safety Vault%',
            'Stainless tank%',
        ];
        foreach ($floorOnlyKeywords as $pattern) {
            DB::table('cargo_item')
                ->whereRaw('cargo_item_description LIKE ?', [$pattern])
                ->update(['floor_only' => true, 'is_stackable' => false]);
        }

        // is_breakable = true: glass, TVs, fridges, monitors.
        $breakableKeywords = [
            'Glass sheets%',
            'Television%',
            'Freezer%',
            'Refrigerator%',
            'CPU/LCD%',
        ];
        foreach ($breakableKeywords as $pattern) {
            DB::table('cargo_item')
                ->whereRaw('cargo_item_description LIKE ?', [$pattern])
                ->update(['is_breakable' => true]);
        }
    }
}
