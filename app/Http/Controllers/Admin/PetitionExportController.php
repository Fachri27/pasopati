<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Petition;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PetitionExportController extends Controller
{
    public function exportPdf(Request $request, $petitionId)
    {
        $petition = Petition::with('translations')->findOrFail($petitionId);

        $locale = app()->getLocale();
        $title = $petition->translation($locale)?->title ?? $petition->slug;

        $verifiedCount = $petition->verifiedSignatures()->count();

        $includeSignatures = true;
        $signatureChunks = collect();

        if ($verifiedCount > 5000 && !$request->boolean('include_all')) {
            $includeSignatures = false;
        } elseif ($verifiedCount > 0) {
            $signatureChunks = $petition->verifiedSignatures()
                ->select('name', 'city', 'created_at')
                ->orderBy('created_at', 'desc')
                ->lazy(500);
        }

        $description = strip_tags($petition->translation($locale)?->description ?? '');
        $demands = is_array($petition->demands) ? $petition->demands : [];

        $exportDate = now()->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB';
        $petitionDate = $petition->created_at->locale('id')->isoFormat('D MMMM YYYY');

        $filename = Str::slug($title) . '-dokumen-petisi.pdf';

        $pdf = Pdf::loadView('pdf.petition-export', compact(
            'petition',
            'title',
            'description',
            'demands',
            'verifiedCount',
            'includeSignatures',
            'signatureChunks',
            'exportDate',
            'petitionDate'
        ));

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($filename);
    }
}
