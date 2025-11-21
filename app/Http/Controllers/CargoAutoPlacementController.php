<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CargoAutoPlacementController extends Controller
{
    /**
     * Show the placement form
     */
    public function show()
    {
        return view('authorized.cargoautoplacement');
    }

    /**
     * Process cargo placement request using 3DBinPacking API.
     */
    public function place(Request $request)
    {
        $data = $request->validate([
            'hatch_width' => 'required|numeric',
            'hatch_height' => 'required|numeric',
            'hatch_depth' => 'required|numeric',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|string',
            'items.*.w' => 'required|numeric',
            'items.*.h' => 'required|numeric',
            'items.*.d' => 'required|numeric',
            'items.*.q' => 'nullable|integer|min:1',
        ]);

        $username = config('services.3dbin.username') ?? env('3DBIN_USERNAME');
        $apiKey   = config('services.3dbin.api_key') ?? env('3DBIN_API_KEY');

        if (empty($username) || empty($apiKey)) {
            return back()
                ->withErrors(['api_credentials' => '3DBinPacking credentials are not configured.'])
                ->withInput();
        }

        $payload = [
            'username'  => $username,
            'api_key'   => $apiKey,
            'container' => [
                'w' => (float) $data['hatch_width'],
                'h' => (float) $data['hatch_height'],
                'd' => (float) $data['hatch_depth'],
            ],
            'items' => array_map(function ($it) {
                return [
                    'id' => (string) $it['id'],
                    'w'  => (float) $it['w'],
                    'h'  => (float) $it['h'],
                    'd'  => (float) $it['d'],
                    'q'  => isset($it['q']) ? (int) $it['q'] : 1,
                ];
            }, $data['items']),
        ];

        $endpoint = 'https://global-api.3dbinpacking.com/packer/fillContainer';

        try {
            $response = Http::timeout(15)
                ->acceptJson()
                ->post($endpoint, $payload);
        } catch (\Exception $e) {
            Log::error('3DBinPacking request failed', [
                'exception' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return back()
                ->withErrors(['api' => 'Unable to reach 3DBinPacking API. Please try again later.'])
                ->withInput();
        }

        if ($response->failed()) {
            $apiMessage = null;
            try {
                $body = $response->json();
                if (is_array($body) && isset($body['error'])) {
                    $apiMessage = $body['error'];
                } elseif (is_array($body) && isset($body['message'])) {
                    $apiMessage = $body['message'];
                }
            } catch (\Exception $e) {
                // ignore parse errors
            }

            $errorMsg = $apiMessage ?? '3DBinPacking API request failed with status ' . $response->status();

            Log::warning('3DBinPacking API returned an error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return back()
                ->withErrors(['api' => $errorMsg])
                ->withInput();
        }

        try {
            $result = $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to parse 3DBinPacking response JSON', [
                'exception' => $e->getMessage(),
                'raw' => $response->body(),
            ]);

            return back()
                ->withErrors(['api' => 'Invalid response from 3DBinPacking API.'])
                ->withInput();
        }

        return back()->with('result', $result);
    }

    /**
 * Add an empty item row and redirect back with input preserved.
 * Prevents adding multiple empty rows in a row.
 */
public function addRow(Request $request)
{
    // Validate minimal structure so old() is preserved
    $request->validate([
        'hatch_width'  => 'nullable',
        'hatch_height' => 'nullable',
        'hatch_depth'  => 'nullable',
        'items'        => 'nullable|array',
    ]);

    // Get posted items and normalize indexes
    $items = array_values($request->input('items', []));

    // Helper: check if an item is "empty" (all relevant fields blank)
    $isEmptyItem = function ($it) {
        if (!is_array($it)) return true;
        $id = trim((string) ($it['id'] ?? ''));
        $w  = trim((string) ($it['w'] ?? ''));
        $h  = trim((string) ($it['h'] ?? ''));
        $d  = trim((string) ($it['d'] ?? ''));
        $q  = trim((string) ($it['q'] ?? ''));
        // consider qty empty if blank; if user typed 0 it's invalid by validation anyway
        return $id === '' && $w === '' && $h === '' && $d === '' && $q === '';
    };

    // Only append a new empty row if the last item is not already empty
    $append = true;
    if (!empty($items)) {
        $last = end($items);
        if ($isEmptyItem($last)) {
            $append = false;
        }
    }

    if ($append) {
        $items[] = ['id' => '', 'w' => '', 'h' => '', 'd' => '', 'q' => 1];
    }

    // Build a minimal input payload to store in session old() — avoid using $request->all()
    $input = [
        'hatch_width'  => $request->input('hatch_width'),
        'hatch_height' => $request->input('hatch_height'),
        'hatch_depth'  => $request->input('hatch_depth'),
        'items'        => $items,
    ];

    // Redirect back to the form route with the prepared input
    return redirect()->route('cargo.placement')->withInput($input);
}


    /**
 * Remove an item row by index and redirect back with input preserved.
 */
public function removeRow(Request $request)
{
    $request->validate([
        'items' => 'nullable|array',
        'remove_index' => 'required|integer|min:0',
    ]);

    $items = array_values($request->input('items', []));
    $index = (int) $request->input('remove_index');

    if (isset($items[$index])) {
        array_splice($items, $index, 1);
    }

    // Do NOT force an empty row when all items are removed.
    // Let the form show zero rows; user can click Add Item to create rows.

    $input = [
        'hatch_width'  => $request->input('hatch_width'),
        'hatch_height' => $request->input('hatch_height'),
        'hatch_depth'  => $request->input('hatch_depth'),
        'items'        => $items,
    ];

    return redirect()->route('cargo.placement')->withInput($input);
}
}
