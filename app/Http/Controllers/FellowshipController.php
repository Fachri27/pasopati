<?php

namespace App\Http\Controllers;

use App\Models\Fellowship;
use Illuminate\Http\Request;

class FellowshipController extends Controller
{
    public function Preview($locale, $slug) {
        $fellowship = Fellowship::with([
            'translations' => function ($q) use ($locale) {
                $q->where('locale', $locale);
            },
            'kategoris' => function ($q) use ($locale) {
                $q->wherePivot('status', 'active')
                    ->with(['translations' => function ($t) use ($locale) {
                        $t->where('locale', $locale);
                    }]);

            },
        ])
            ->where('slug', $slug)
            ->first();

        $categories = $fellowship->kategoris;

        $meta = $fellowship->getSeoData($locale);

        seo()->setLocale($locale)
            ->set('title', ['id' => $meta['title'], 'en' => $meta['title']])
            ->set('description', ['id' => $meta['description'], 'en' => $meta['description']])
            ->set('image', $meta['image'])
            ->set('type', $meta['type']);

        return view('front.page-fellowship', compact('fellowship', 'categories', 'locale'));
    }

    public function indexUser($locale, Request $request)
    {
        $search = $request->input('search');
        $fellowships = Fellowship::with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }])
            ->when($search, function($query, $search) use ($locale) {
                $query->whereHas('translations', function($q) use ($locale, $search) {
                    $q->where('locale', $locale)
                        ->where(function($q2) use ($search) {
                            $q2->where('title','like','%'.$search.'%');
                            $q2->orWhere('description','like','%'.$search.'%');
                        });
                });
                
            })
            ->where('status', 'active')
            ->orderBy('start_date', 'desc')
            ->get();
        
        $meta = [
            'title' => __('Pasopati Fellowship'),
            'description' => __('pasopati.id: Pasopati Project dirancang sebagai sebuah situs yang menampilkan informasi, data, dan analisis isu-isu kehutanan, persawitan, dan pertambangan. Situs ini fokus menyampaikan suara kritis pada tema-tema tersebut di atas, termasuk mengenai pelakunya dan kebijakan-kebijakan terkait.
            Pasopati Project didedikasikan untuk mencapai salah satu tujuan Auriga, yakni mengeliminir aksi-aksi destruktif terhadap sumberdaya alam. Situs ini dikelola oleh Auriga. Namun demikian ekspose-ekspose tertentu dalam situs ini dilakukan bersama jejaring.'),
            'image' => asset('images/image.png'),
            'type' => 'article',    
        ];

        seo()->setLocale($locale)
            ->set('title', ['id' => $meta['title'], 'en' => $meta['title']])
            ->set('description', ['id' => $meta['description'], 'en' => $meta['description']])
            ->set('image', $meta['image'])
            ->set('type', $meta['type']);

        return view('front.home-fellowship', compact('fellowships', 'locale', 'search'));
    }
}
