<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PortSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('seeders/ports.csv');

        if (!file_exists($file)) {
            $this->command->error("ports.csv not found!");
            return;
        }

        $handle = fopen($file, 'r');

        // Get header row
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {

            $data = array_combine($header, $row);

            // Skip empty rows
            if (!$data['terminal_name'] || !$data['port_name']) {
                continue;
            }

            DB::table('ports')->insert([
                'terminal_name' => trim($data['terminal_name']),
                'port_name' => trim($data['port_name']),
                'city' => trim($data['city']),
                'province' => trim($data['province']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        fclose($handle);

        $this->command->info('Ports seeded successfully!');
    }
}