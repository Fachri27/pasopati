<?php

namespace App\Http\Controllers;

use App\Models\Fellowship;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function indexUser(Request $request, $locale)
    {
        $search = $request->input('search');
        $pages = Page::with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
            ->when($search, function ($query, $search) use ($locale) {
                $query->whereHas('translations', function ($q) use ($locale, $search) {
                    $q->where('locale', $locale)
                        ->where(function ($q2) use ($search) {
                            $q2->where('title', 'like', "%{$search}%")
                                ->orWhere('content', 'like', "%{$search}%");
                        });
                });
            })
            ->where('page_type', 'expose')
            ->where('status', 'active')
            ->latest()
            ->get();

        $fellowship = Fellowship::with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
            ->first();

        // Ambil semua page dengan type ngopini
        $ngopini = Page::with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
            ->where('page_type', 'ngopini')
            ->where('status', 'active')
            ->get();

        $meta = [
            'title' => __('Pasopati'),
            'description' => __('pasopati.id: Pasopati Project dirancang sebagai sebuah situs yang menampilkan informasi, data, dan analisis isu-isu kehutanan, persawitan, dan pertambangan. Situs ini fokus menyampaikan suara kritis pada tema-tema tersebut di atas, termasuk mengenai pelakunya dan kebijakan-kebijakan terkait.
            Pasopati Project didedikasikan untuk mencapai salah satu tujuan Auriga, yakni mengeliminir aksi-aksi destruktif terhadap sumberdaya alam. Situs ini dikelola oleh Auriga. Namun demikian ekspose-ekspose tertentu dalam situs ini dilakukan bersama jejaring.'),
            'image' => asset('img/image.png'),
            'type' => 'article',
        ];

        seo()->setLocale($locale)
            ->set('title', ['id' => $meta['title'], 'en' => $meta['title']])
            ->set('description', ['id' => $meta['description'], 'en' => $meta['description']])
            ->set('image', $meta['image'])
            ->set('type', $meta['type']);

        // Data untuk poster utama (artikel terbaru)
        $mainNgopini = $ngopini->first();

        // Data untuk list di kanan (artikel selain utama)
        $otherNgopini = $ngopini->skip(1)->take(3);

        // function untuk searc

        return view('front.home', compact('pages', 'mainNgopini', 'otherNgopini', 'search', 'fellowship'));
    }

    public function preview($locale, $page_type, $slug)
    {
        $page = Page::with('translations')
            ->where('page_type', $page_type)
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Page::where('id', '!=', $page->id)
            ->where('page_type', $page_type)
            ->latest()
            ->take(3)
            ->get();

        $meta = $page->getSeoData($locale);

        seo()->setLocale($locale)
            ->set('title', ['id' => $meta['title'], 'en' => $meta['title']])
            ->set('description', ['id' => $meta['description'], 'en' => $meta['description']])
            ->set('image', $meta['image'])
            ->set('type', $meta['type']);

        if ($page_type === 'ngopini') {
            return view('front.page-ngopini', compact('page', 'related'));
        } else {
            return view('front.page-expose', compact('page', 'related'));
        }
    }

    public function indexNgopini($locale)
    {
        $pages = Page::with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
            ->where('page_type', 'ngopini')
            ->where('status', 'active')
            ->get();

        $meta = [
            'title' => __('Pasopati'),
            'description' => __('pasopati.id: Pasopati Project dirancang sebagai sebuah situs yang menampilkan informasi, data, dan analisis isu-isu kehutanan, persawitan, dan pertambangan. Situs ini fokus menyampaikan suara kritis pada tema-tema tersebut di atas, termasuk mengenai pelakunya dan kebijakan-kebijakan terkait.
            Pasopati Project didedikasikan untuk mencapai salah satu tujuan Auriga, yakni mengeliminir aksi-aksi destruktif terhadap sumberdaya alam. Situs ini dikelola oleh Auriga. Namun demikian ekspose-ekspose tertentu dalam situs ini dilakukan bersama jejaring.'),
            'image' => asset('img/image.png'),
            'type' => 'article',
        ];

        seo()->setLocale($locale)
            ->set('title', ['id' => $meta['title'], 'en' => $meta['title']])
            ->set('description', ['id' => $meta['description'], 'en' => $meta['description']])
            ->set('image', $meta['image'])
            ->set('type', $meta['type']);

        return view('front.home-ngopini', compact('pages', 'locale'));
    }

    public function artikel($locale, $expose_type)
    {
        $pages = Page::with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
            ->where('expose_type', $expose_type)
            ->where('status', 'active')
            ->get();

        $meta = [
            'title' => __('Pasopati'),
            'description' => __('pasopati.id: Pasopati Project dirancang sebagai sebuah situs yang menampilkan informasi, data, dan analisis isu-isu kehutanan, persawitan, dan pertambangan. Situs ini fokus menyampaikan suara kritis pada tema-tema tersebut di atas, termasuk mengenai pelakunya dan kebijakan-kebijakan terkait.
            Pasopati Project didedikasikan untuk mencapai salah satu tujuan Auriga, yakni mengeliminir aksi-aksi destruktif terhadap sumberdaya alam. Situs ini dikelola oleh Auriga. Namun demikian ekspose-ekspose tertentu dalam situs ini dilakukan bersama jejaring.'),
            'image' => asset('img/image.png'),
            'type' => 'article',
        ];

        seo()->setLocale($locale)
            ->set('title', ['id' => $meta['title'], 'en' => $meta['title']])
            ->set('description', ['id' => $meta['description'], 'en' => $meta['description']])
            ->set('image', $meta['image'])
            ->set('type', $meta['type']);

        return view('front.show-artikel', compact('pages', 'locale', 'expose_type'));
    }
}
