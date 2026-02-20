<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Page, Fellowship};
use Illuminate\Pagination\LengthAwarePaginator;

class SearchController extends Controller
{
    /**
     * Mencari artikel berdasarkan kata kunci
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\View\View
     */
    public function search(Request $request)
    {
        $locale = app()->getLocale();
        $query = trim($request->input('q') ?? $request->input('search') ?? '');
        $pageType = $request->input('page_type'); // optional: 'expose' atau 'ngopini'
        $searchType = $request->input('type'); // optional: 'fellowship' atau 'page' untuk filter spesifik
        
        // Deteksi konteks dari referer jika tidak ada parameter type
        if (!$searchType && $request->header('referer')) {
            $referer = $request->header('referer');
            if (strpos($referer, '/fellowship') !== false) {
                $searchType = 'fellowship';
            } elseif (strpos($referer, '/') !== false || strpos($referer, '/ngopini') !== false || strpos($referer, '/expose') !== false) {
                $searchType = 'page';
            }
        }
        
        // Jika query kosong, tidak menampilkan hasil
        if (empty($query)) {
            $fellowships = collect();
            $pages = collect();
            $articles = collect();
        } else {
            // Query untuk mencari artikel (Page) - hanya jika tidak mencari khusus fellowship
            $pages = collect();
            if ($searchType !== 'fellowship') {
                $pages = Page::with(['translations' => function ($q) use ($locale) {
                    $q->where('locale', $locale);
                }])
                    ->where('status', 'active')
                    ->when($pageType, function ($q) use ($pageType) {
                        $q->where('page_type', $pageType);
                    })
                    ->whereHas('translations', function ($translationQuery) use ($locale, $query) {
                        $translationQuery->where('locale', $locale)
                            ->where(function ($q2) use ($query) {
                                $q2->where('title', 'like', "%{$query}%");
                            });
                    })
                    ->orderBy('published_at', 'desc')
                    ->get()
                    ->map(function ($page) {
                        $page->type = 'page';
                        $page->sort_date = $page->published_at;
                        return $page;
                    });
            }

            // Query untuk mencari fellowship - hanya jika tidak mencari khusus page
            $fellowships = collect();
            if ($searchType !== 'page') {
                $fellowships = Fellowship::with(['translations' => function ($q) use ($locale) {
                    $q->where('locale', $locale);
                }])
                    ->where('status', 'active')
                    ->whereHas('translations', function ($translationQuery) use ($locale, $query) {
                        $translationQuery->where('locale', $locale)
                            ->where(function ($q2) use ($query) {
                                $q2->where('title', 'like', "%{$query}%");
                            });
                    })
                    ->orderBy('start_date', 'desc')
                    ->get()
                    ->map(function ($fellowship) use ($locale) {
                        $fellowship->type = 'fellowship';
                        $fellowship->sort_date = $fellowship->start_date;
                        return $fellowship;
                    });
            }

            // Urutkan fellowship dan page secara terpisah
            $fellowships = $fellowships->sortByDesc('sort_date')->values();
            $pages = $pages->sortByDesc('sort_date')->values();

            // Untuk kompatibilitas dengan view yang sudah ada, buat collection gabungan
            $articles = $fellowships->concat($pages);
        }

        // Jika request JSON (untuk API)
        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $articles->map(function ($item) use ($locale) {
                    $translation = $item->translation($locale);
                    
                    if ($item->type === 'fellowship') {
                        return [
                            'id' => $item->id,
                            'slug' => $item->slug,
                            'title' => $translation->title ?? '',
                            'excerpt' => $translation->excerpt ?? '',
                            'type' => 'fellowship',
                            'image' => $item->image ?? $translation->image ?? null,
                            'start_date' => $item->start_date,
                            'url' => route('fellowship.preview', [
                                'locale' => $locale,
                                'slug' => $item->slug
                            ]),
                        ];
                    } else {
                        return [
                            'id' => $item->id,
                            'slug' => $item->slug,
                            'title' => $translation->title ?? '',
                            'excerpt' => $translation->excerpt ?? '',
                            'content' => $translation->content ?? '',
                            'page_type' => $item->page_type,
                            'type' => 'page',
                            'featured_image' => $item->featured_image,
                            'published_at' => $item->published_at,
                            'url' => route('show-page', [
                                'locale' => $locale,
                                'page_type' => $item->page_type,
                                'slug' => $item->slug
                            ]),
                        ];
                    }
                }),
                'pagination' => [
                    'total' => ($fellowships->count() ?? 0) + ($pages->count() ?? 0),
                    'fellowships_count' => $fellowships->count() ?? 0,
                    'pages_count' => $pages->count() ?? 0,
                ],
                'query' => $query,
            ]);
        }

        // SEO Meta
        $title = $query 
            ? __('Hasil Pencarian: :query', ['query' => $query]) 
            : __('Pencarian Artikel');
        
        $totalResults = ($fellowships->count() ?? 0) + ($pages->count() ?? 0);
        $description = $query
            ? __('Ditemukan :count hasil untuk ":query"', ['count' => $totalResults, 'query' => $query])
            : __('Cari artikel dan fellowship berdasarkan kata kunci');

        seo()->setLocale($locale)
            ->set('title', ['id' => $title, 'en' => $title])
            ->set('description', ['id' => $description, 'en' => $description])
            ->set('image', asset('img/image.png'))
            ->set('type', 'website');

        // Return view untuk web
        return view('front.search-results', [
            'articles' => $articles,
            'fellowships' => $fellowships ?? collect(),
            'pages' => $pages ?? collect(),
            'query' => $query,
            'locale' => $locale,
            'pageType' => $pageType,
        ]);
    }
}
