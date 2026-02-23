public function loadMoreArticles(Request $request)
{
    $offset = (int) $request->input('offset', 0);
    $limit = (int) $request->input('limit', 6);
    $locale = app()->getLocale();

    $pages = Page::with(['translations' => function ($q) use ($locale) {
        $q->where('locale', $locale);
    }])
        ->where('status', 'active')
        ->orderBy('published_at', 'desc')
        ->skip($offset)
        ->take($limit)
        ->get();

    if ($pages->isEmpty()) {
        return response('');
    }

    return view('front.components.card-list', [
        'pages' => $pages,
        'limit' => $limit,
        'offset' => $offset,
    ])->render();
}
