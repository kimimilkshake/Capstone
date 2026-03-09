<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                'measurement_unit_name' => 'inches',
                'measurement_unit_abbreviation' => 'in',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'measurement_unit_name' => 'centimeters',
                'measurement_unit_abbreviation' => 'cm',
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
                'cargo_category_name' => 'Hazardous/Dangerous Goods',
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

        // Route Code seeders
        DB::table('route_code')->insert([
            [
                'route_code_name' => 'BAYBAY',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_code_name' => 'TALIBON',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Get inserted Route Code IDs
        $baybayId = DB::table('route_code')->where('route_code_name', 'BAYBAY')->value('route_code_id');
        $talibonId = DB::table('route_code')->where('route_code_name', 'TALIBON')->value('route_code_id');

        // Route and Port seeders
        DB::table('route_port')->insert([
            [
                'route_code_id' => $baybayId,
                'route_origin' => 'Cebu',
                'route_destination' => 'Baybay',
                'port_origin_name' => 'Port of Cebu',
                'port_origin_city' => 'Cebu City',
                'port_origin_province' => 'Cebu',
                'port_destination_name' => 'Port of Baybay',
                'port_destination_city' => 'Baybay City',
                'port_destination_province' => 'Leyte',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_code_id' => $talibonId,
                'route_origin' => 'Cebu',
                'route_destination' => 'Talibon',
                'port_origin_name' => 'Port of Cebu',
                'port_origin_city' => 'Cebu City',
                'port_origin_province' => 'Cebu',
                'port_destination_name' => 'Port of Talibon',
                'port_destination_city' => 'Talibon',
                'port_destination_province' => 'Bohol',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_code_id' => $baybayId,
                'route_origin' => 'Baybay',
                'route_destination' => 'Cebu',
                'port_origin_name' => 'Port of Baybay',
                'port_origin_city' => 'Baybay City',
                'port_origin_province' => 'Leyte',
                'port_destination_name' => 'Port of Cebu',
                'port_destination_city' => 'Cebu City',
                'port_destination_province' => 'Cebu',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'route_code_id' => $talibonId,
                'route_origin' => 'Talibon',
                'route_destination' => 'Cebu',
                'port_origin_name' => 'Port of Talibon',
                'port_origin_city' => 'Talibon',
                'port_origin_province' => 'Bohol',
                'port_destination_name' => 'Port of Cebu',
                'port_destination_city' => 'Cebu City',
                'port_destination_province' => 'Cebu',
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
    }
}
