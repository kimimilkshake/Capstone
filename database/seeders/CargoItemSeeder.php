<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargoItemSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = database_path('seeders/cargo_items.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("CSV file not found: {$csvFile}");
            return;
        }

        // Read all rows from CSV
        $rows = array_map('str_getcsv', file($csvFile));

        if (empty($rows)) {
            $this->command->warn("CSV file is empty.");
            return;
        }

        // Extract header and trim BOM & spaces
        $header = array_shift($rows);
        $header = array_map(function($h) {
            $h = trim($h);
            // Remove BOM if present
            $h = preg_replace('/^\x{FEFF}/u', '', $h);
            return $h;
        }, $header);

        $insertData = [];

        foreach ($rows as $lineNumber => $row) {
            // Skip completely empty rows
            if (count(array_filter($row)) === 0) {
                continue;
            }

            // Combine header and row, trim values
            $record = array_map(fn($v) => trim($v), array_combine($header, $row));

            // --- Check required IDs ---
            if (empty($record['route_category_id'])) {
                $this->command->error("Missing required route_category_id at CSV line " . ($lineNumber + 2));
                continue;
            }
            if (empty($record['cargo_category_id'])) {
                $this->command->error("Missing required cargo_category_id at CSV line " . ($lineNumber + 2));
                continue;
            }

            $routeCategoryId = intval($record['route_category_id']);
            $cargoCategoryId = intval($record['cargo_category_id']);
            $measurementUnitId = isset($record['measurement_unit_id']) && $record['measurement_unit_id'] !== ''
                ? intval($record['measurement_unit_id'])
                : null;

            // Prepare insert row
            $insertData[] = [
                'route_category_id'           => $routeCategoryId,
                'cargo_category_id'           => $cargoCategoryId,
                'measurement_unit_id'         => $measurementUnitId,
                'cargo_item_description'      => $record['cargo_item_description'] ?? null,
                'cargo_item_freight'          => isset($record['cargo_item_freight']) ? floatval($record['cargo_item_freight']) : 0,
                'cargo_item_measure_required' => $record['cargo_item_measure_required'] ?? 'No',
                'cargo_item_min_length'       => isset($record['cargo_item_min_length']) && $record['cargo_item_min_length'] !== '' ? floatval($record['cargo_item_min_length']) : null,
                'cargo_item_max_length'       => isset($record['cargo_item_max_length']) && $record['cargo_item_max_length'] !== '' ? floatval($record['cargo_item_max_length']) : null,
                'cargo_item_min_width'        => isset($record['cargo_item_min_width']) && $record['cargo_item_min_width'] !== '' ? floatval($record['cargo_item_min_width']) : null,
                'cargo_item_max_width'        => isset($record['cargo_item_max_width']) && $record['cargo_item_max_width'] !== '' ? floatval($record['cargo_item_max_width']) : null,
                'cargo_item_min_height'       => isset($record['cargo_item_min_height']) && $record['cargo_item_min_height'] !== '' ? floatval($record['cargo_item_min_height']) : null,
                'cargo_item_max_height'       => isset($record['cargo_item_max_height']) && $record['cargo_item_max_height'] !== '' ? floatval($record['cargo_item_max_height']) : null,
                'cargo_item_base_cbm'         => isset($record['cargo_item_base_cbm']) && $record['cargo_item_base_cbm'] !== '' ? floatval($record['cargo_item_base_cbm']) : null,
                'created_at'                  => now(),
                'updated_at'                  => now(),
            ];
        }

        if (!empty($insertData)) {
            foreach (array_chunk($insertData, 500) as $chunk) {
                DB::table('cargo_item')->insert($chunk);
            }
            $this->command->info(count($insertData) . " cargo items seeded successfully.");
        } else {
            $this->command->warn("No cargo items were inserted. Check CSV for missing required IDs.");
        }
    }
}