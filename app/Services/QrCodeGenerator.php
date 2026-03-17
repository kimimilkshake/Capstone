<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeGenerator
{
    /**
     * Generate a QR code SVG locally using simplesoftwareio/simple-qrcode (no imagick/GD needed)
     *
     * @param string $data The data to encode in the QR code
     * @param int $size Size of the QR code (default 300x300)
     * @return string|null Raw SVG string or null on failure
     */
    public static function generate(string $data, int $size = 300): ?string
    {
        try {
            if (empty($data)) {
                throw new \Exception('QR code data cannot be empty');
            }

            $svgContent = (string) QrCode::format('svg')->size($size)->generate($data);

            if (!$svgContent) {
                throw new \Exception('QR code generation returned empty result');
            }

            return $svgContent;

        } catch (\Exception $e) {
            \Log::error('QrCodeGenerator::generate - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate QR code and save to disk as SVG
     *
     * @param string $data The data to encode
     * @param string|null $filename Custom filename (without extension)
     * @return string|null File path if successful, null on failure
     */
    public static function generateAndSave(string $data, ?string $filename = null): ?string
    {
        try {
            $svgContent = self::generate($data);

            if (!$svgContent) {
                throw new \Exception('Failed to generate QR code');
            }

            $directory = storage_path('app/qr_codes');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (!$filename) {
                $filename = 'qr_' . md5($data . time());
            }

            $filepath = $directory . DIRECTORY_SEPARATOR . $filename . '.svg';
            $bytes = file_put_contents($filepath, $svgContent);

            if ($bytes === false) {
                throw new \Exception('Failed to save QR code to disk');
            }

            return $filepath;

        } catch (\Exception $e) {
            \Log::error('QrCodeGenerator::generateAndSave - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate QR code as SVG data URL (for use in HTML/PDF)
     *
     * @param string $data The data to encode
     * @param int $size Size of the QR code
     * @return string Data URL string
     */
    public static function generateAsDataUrl(string $data, int $size = 300): string
    {
        $svgContent = self::generate($data, $size);

        if (!$svgContent) {
            // 1x1 transparent PNG fallback
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        }

        return 'data:image/svg+xml;base64,' . base64_encode($svgContent);
    }

    /**
     * Generate QR code and store in database + disk
     *
     * @param string $bookingRef The booking reference
     * @param int $passengerId The passenger ID
     * @param string $qrData The data to encode in QR
     * @param string|null $filename Custom filename (without extension)
     * @return \App\Models\QrCode|null QrCode model if successful
     */
    public static function generateAndStore($bookingRef, $passengerId, $qrData, ?string $filename = null): ?\App\Models\QrCode
    {
        try {
            // First check if QR code already exists
            $existing = \App\Models\QrCode::where('booking_ref_no', $bookingRef)
                ->where('passenger_id', $passengerId)
                ->first();

            if ($existing) {
                // Normalize the path
                $normalizedPath = str_replace('/', DIRECTORY_SEPARATOR, $existing->qr_code_path);
                $normalizedPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedPath);

                if (file_exists($normalizedPath)) {
                    \Log::info('QrCodeGenerator::generateAndStore - Using existing QR code for booking ' . $bookingRef . ', passenger ' . $passengerId);
                    return $existing;
                }
            }

            // Generate and save to disk
            $filepath = self::generateAndSave($qrData, $filename);

            if (!$filepath) {
                throw new \Exception('Failed to generate and save QR code to disk');
            }

            // Update or create in database
            $qrCode = \App\Models\QrCode::updateOrCreate(
                [
                    'booking_ref_no' => $bookingRef,
                    'passenger_id' => $passengerId
                ],
                [
                    'qr_data' => $qrData,
                    'qr_code_path' => $filepath
                ]
            );

            return $qrCode;

        } catch (\Exception $e) {
            \Log::error('QrCodeGenerator::generateAndStore - ' . $e->getMessage());
            return null;
        }
    }
}
