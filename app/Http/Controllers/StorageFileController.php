<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StorageFileController extends Controller
{
    public function serve(string $path)
    {
        if (str_contains($path, '..')) {
            abort(403);
        }

        $fullPath = storage_path('app/public/' . $path);

        if (!file_exists($fullPath)) {
            abort(404);
        }

        return response()->file($fullPath);
    }
}
