<?php

namespace App\Helpers;

/**
 * COT Plan Utility Helper
 *
 * This file contains helper methods to read and work with vessel COT plan files.
 *
 * System Flow:
 * 1. Check cot_plan_index.json for vessel list
 * 2. Find vessel_id in index
 * 3. Get the specific file path from index
 * 4. Load and return data from specific vessel file
 *
 * Usage Examples:
 *   $vesselData = CotPlanHelper::getVesselPlan(1);
 *   $accommodations = CotPlanHelper::getAccommodations(1);
 */

class CotPlanHelper
{
    /**
     * Get the path to the COT plan index file
     */
    private static function getIndexPath()
    {
        return storage_path('cot_plan/cot_plan_index.json');
    }

    /**
     * Get all vessels from the index
     *
     * @return array|null - Array of vessel entries or null if index not found
     */
    public static function getVesselIndex()
    {
        $indexPath = self::getIndexPath();

        if (!file_exists($indexPath)) {
            return null;
        }

        $json = file_get_contents($indexPath);
        $data = json_decode($json, true);

        return $data['vessels'] ?? null;
    }

    /**
     * Find vessel in index by ID
     *
     * @param int $vesselId - The vessel ID
     * @return array|null - Vessel index entry or null if not found
     */
    public static function findVesselInIndex($vesselId)
    {
        $vessels = self::getVesselIndex();

        if (!$vessels) {
            return null;
        }

        foreach ($vessels as $vessel) {
            if ($vessel['vessel_id'] == $vesselId) {
                return $vessel;
            }
        }

        return null;
    }

    /**
     * Get specific vessel file path from index
     *
     * @param int $vesselId - The vessel ID
     * @return string|null - Full path to vessel file or null
     */
    private static function getVesselFilePath($vesselId)
    {
        $vesselEntry = self::findVesselInIndex($vesselId);

        if (!$vesselEntry || !isset($vesselEntry['file'])) {
            return null;
        }

        return storage_path('cot_plan/' . $vesselEntry['file']);
    }

    /**
     * Get full vessel data including all accommodations
     * Checks index first, then loads specific vessel file
     *
     * @param int $vesselId - The vessel ID
     * @return array|null - Decoded JSON data or null if file not found
     */
    public static function getVesselPlan($vesselId)
    {
        // Step 1: Check if vessel exists in index
        $vesselEntry = self::findVesselInIndex($vesselId);

        if (!$vesselEntry) {
            return null;
        }

        // Step 2: Get the specific vessel file path
        $filePath = self::getVesselFilePath($vesselId);

        if (!$filePath || !file_exists($filePath)) {
            return null;
        }

        // Step 3: Load and return specific vessel file
        $json = file_get_contents($filePath);
        return json_decode($json, true);
    }

    /**
     * Get only accommodations for a vessel
     *
     * @param int $vesselId - The vessel ID
     * @return array - Array of accommodations or empty array
     */
    public static function getAccommodations($vesselId)
    {
        $data = self::getVesselPlan($vesselId);
        return $data['accommodations'] ?? [];
    }

    /**
     * Get specific accommodation details
     *
     * @param int $vesselId - The vessel ID
     * @param int $accommodationId - The accommodation ID
     * @return array|null - Accommodation data or null if not found
     */
    public static function getAccommodation($vesselId, $accommodationId)
    {
        $accommodations = self::getAccommodations($vesselId);

        foreach ($accommodations as $accommodation) {
            if ($accommodation['accommodation_id'] == $accommodationId) {
                return $accommodation;
            }
        }

        return null;
    }

    /**
     * Get vessel basic info (name, code, etc.) from index
     *
     * @param int $vesselId - The vessel ID
     * @return array|null - Vessel info or null
     */
    public static function getVesselInfo($vesselId)
    {
        $vesselEntry = self::findVesselInIndex($vesselId);

        if (!$vesselEntry) {
            return null;
        }

        return [
            'vessel_id' => $vesselEntry['vessel_id'],
            'vessel_name' => $vesselEntry['vessel_name'],
            'vessel_code' => $vesselEntry['vessel_code'],
        ];
    }

    /**
     * Check if accommodation exists
     *
     * @param int $vesselId - The vessel ID
     * @param int $accommodationId - The accommodation ID
     * @return bool
     */
    public static function accommodationExists($vesselId, $accommodationId)
    {
        return self::getAccommodation($vesselId, $accommodationId) !== null;
    }

    /**
     * Get list of all vessel IDs from index
     *
     * @return array - Array of vessel IDs
     */
    public static function getAllVesselIds()
    {
        $vessels = self::getVesselIndex();

        if (!$vessels) {
            return [];
        }

        $vesselIds = [];
        foreach ($vessels as $vessel) {
            $vesselIds[] = $vessel['vessel_id'];
        }

        sort($vesselIds);
        return $vesselIds;
    }

    /**
     * Get all vessels from index with basic info
     *
     * @return array - Array of all vessel entries from index
     */
    public static function getAllVessels()
    {
        return self::getVesselIndex() ?? [];
    }
}
?>