<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TinyMCEController extends Controller
{
    public function uploadExternalImageLfm(Request $request)
    {
        $url = $request->url;

        if (!$url) return response()->json(['error' => 'URL empty'], 400);

        $image = Http::get($url)->body();

        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

        $filename = 'photos/' . date('Y/m/') . uniqid() . '.' . $ext;

        Storage::disk('public')->put($filename, $image);

        return [
            'url' => asset('storage/' . $filename)
        ];
    }
}
