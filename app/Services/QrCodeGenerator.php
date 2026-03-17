<?php

namespace App\Services;

class QrCodeGenerator
{
    /**
     * Generate a QR code using the free QR Server API
     * 
     * @param string $data The data to encode in the QR code
     * @param int $size Size of the QR code (default 300x300)
     * @return string|null Base64 encoded PNG image or null on failure
     */
    public static function generate(string $data, int $size = 300): ?string
    {
        try {
            if (empty($data)) {
                throw new \Exception('QR code data cannot be empty');
            }

            $url = 'https://api.qrserver.com/v1/create-qr-code/';
            $params = [
                'size' => $size . 'x' . $size,
                'data' => $data,
                'format' => 'png'
            ];

            $fullUrl = $url . '?' . http_build_query($params);

            $context = stream_context_create([
                'http' => ['timeout' => 5],
                'ssl' => ['verify_peer' => false]
            ]);

            $imageData = @file_get_contents($fullUrl, false, $context);

            if ($imageData === false) {
                throw new \Exception('Failed to fetch QR code from API');
            }

            return base64_encode($imageData);

        } catch (\Exception $e) {
            \Log::error('QrCodeGenerator::generate - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate QR code and save to disk
     * 
     * @param string $data The data to encode
     * @param string|null $filename Custom filename (without extension)
     * @return string|null File path if successful, null on failure
     */
    public static function generateAndSave(string $data, ?string $filename = null): ?string
    {
        try {
            $base64 = self::generate($data);

            if (!$base64) {
                throw new \Exception('Failed to generate QR code');
            }

            $directory = storage_path('app/qr_codes');
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (!$filename) {
                $filename = 'qr_' . md5($data . time());
            }

            $filepath = $directory . DIRECTORY_SEPARATOR . $filename . '.png';
            $imageData = base64_decode($base64);
            $bytes = file_put_contents($filepath, $imageData);

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
     * Generate QR code as data URL (for use in HTML/PDF)
     * 
     * @param string $data The data to encode
     * @param int $size Size of the QR code
     * @return string Data URL string
     */
    public static function generateAsDataUrl(string $data, int $size = 300): string
    {
        $base64 = self::generate($data, $size);

        if (!$base64) {
            return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
        }

        return 'data:image/png;base64,' . $base64;
    }
}
