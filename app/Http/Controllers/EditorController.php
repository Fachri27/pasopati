<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorController extends Controller
{
    public function uploadEditorImage(Request $request)
    {
        $image = $request->image;

        if (!$image) {
            return response()->json(['error' => 'No image found'], 422);
        }

        // nama file unik
        $fileName = 'editor_' . time() . '.png';

        // simpan ke storage/app/public/photos
        Storage::disk('public')->put('photos/' . $fileName, base64_decode($image));

        return response()->json([
            'url' => asset('storage/photos/' . $fileName)
        ]);
    }
}

