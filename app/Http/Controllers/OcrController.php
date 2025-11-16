<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OcrController extends Controller
{
    public function parseImage(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:1024', // 1MB limit (free plan)
        ]);

        $file = $request->file('file');
        $apiKey = 'K86796854488957'; // ✅ your real key
        $apiUrl = 'https://api.ocr.space/parse/image';

        $response = Http::asMultipart()->withHeaders([
            'apikey' => $apiKey,
        ])->post($apiUrl, [
                    [
                        'name' => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $file->getClientOriginalName(),
                    ],
                    [
                        'name' => 'language',
                        'contents' => 'eng',
                    ],
                    [
                        'name' => 'OCREngine',
                        'contents' => '2',
                    ],
                ]);

        $data = $response->json();

        if (isset($data['ParsedResults'][0]['ParsedText'])) {
            return response()->json([
                'text' => $data['ParsedResults'][0]['ParsedText'],
            ]);
        }

        return response()->json(['error' => 'OCR failed. Try again.'], 500);
    }
}
