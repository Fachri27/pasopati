<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * MOCK endpoint untuk daftar kartu kasus Deforestory.
 *
 * Ini pengganti sementara API web lain. Bentuk respons sengaja dibuat
 * mirip API eksternal umum { data: [...] } supaya tinggal ganti URL.
 * Hapus controller + route ini begitu API asli tersedia.
 */
class DeforestoryMockApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $locale = $request->query('locale', 'id');

        $cases = [
            [
                'slug' => 'mayawana',
                'category' => 'pulp',
                'year' => '2021–2025',
                'image' => 'https://images.unsplash.com/photo-1542273917363-3b1817f69a2d?auto=format&fit=crop&w=1200&q=80',
                'title_id' => 'Mayawana: jejak deforestasi di radius rantai pasok RGE',
                'title_en' => 'Mayawana: deforestation in the RGE supply-chain radius',
                'excerpt_id' => 'Analisis spasial deforestasi di Mayawana dan keterkaitannya dengan rantai pasok grup RGE.',
                'excerpt_en' => 'Spatial analysis of deforestation in Mayawana and its links to the RGE group supply chain.',
            ],
            [
                'slug' => 'pulau-laut',
                'category' => 'sawit',
                'year' => '2022–2024',
                'image' => 'https://images.unsplash.com/photo-1495107334309-fcf20504a5ab?auto=format&fit=crop&w=1200&q=80',
                'title_id' => 'Pulau Laut: sawit yang menyusup di balik hutan lindung',
                'title_en' => 'Pulau Laut: palm oil creeping behind protected forest',
                'excerpt_id' => 'Pembukaan lahan sawit di sempadan hutan lindung Pulau Laut, Kalimantan Selatan.',
                'excerpt_en' => 'Palm oil land clearing inside the protected-forest buffer of Pulau Laut, South Kalimantan.',
            ],
            [
                'slug' => 'tanah-bumbu',
                'category' => 'batubara',
                'year' => '2020–2023',
                'image' => 'https://images.unsplash.com/photo-1518709594023-6eab9bab7b23?auto=format&fit=crop&w=1200&q=80',
                'title_id' => 'Tanah Bumbu: lubang tambang dan hutan yang hilang',
                'title_en' => 'Tanah Bumbu: mining pits and the forest that disappeared',
                'excerpt_id' => 'Ekspansi tambang batubara di Tanah Bumbu menyingkap tutupan hutan dan meninggalkan lubang bekas galian.',
                'excerpt_en' => 'Coal-mining expansion in Tanah Bumbu strips forest cover and leaves abandoned pits.',
            ],
            [
                'slug' => 'ketapang',
                'category' => 'pulp',
                'year' => '2019–2024',
                'image' => 'https://images.unsplash.com/photo-1448375240586-882707db888b?auto=format&fit=crop&w=1200&q=80',
                'title_id' => 'Ketapang: kanal dan tanaman pohon tunggal',
                'title_en' => 'Ketapang: canals and single-species plantations',
                'excerpt_id' => 'Jejak kanal pengeringan lahan gambut di Ketapang dan transisi ke HTI.',
                'excerpt_en' => 'Peat-drainage canals in Ketapang and the transition to industrial plantations.',
            ],
        ];

        $data = array_map(function ($c) use ($locale) {
            return [
                'slug' => $c['slug'],
                'category' => $c['category'],
                'year' => $c['year'],
                'image' => $c['image'],
                'title' => $locale === 'en' ? $c['title_en'] : $c['title_id'],
                'excerpt' => $locale === 'en' ? $c['excerpt_en'] : $c['excerpt_id'],
            ];
        }, $cases);

        return response()->json(['data' => $data]);
    }
}