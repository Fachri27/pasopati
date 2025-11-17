<?php

namespace App\Services;

class SeoService
{
    protected array $meta = [
        'title' => [
            'id' => 'Pasopati',
            'en' => 'Pasopati',
        ],
        'description' => [
            'id' => 'pasopati.id: Pasopati Project dirancang sebagai sebuah situs yang menampilkan informasi, data, dan analisis isu-isu kehutanan, persawitan, dan pertambangan. Situs ini fokus menyampaikan suara kritis pada tema-tema tersebut di atas, termasuk mengenai pelakunya dan kebijakan-kebijakan terkait.

            Pasopati Project didedikasikan untuk mencapai salah satu tujuan Auriga, yakni mengeliminir aksi-aksi destruktif terhadap sumberdaya alam. Situs ini dikelola oleh Auriga.   Namun demikian ekspose-ekspose tertentu dalam situs ini dilakukan bersama jejaring.',
            'en' => '
                Pasopati Project is designed as a platform that presents information, data, and analysis on issues related to forestry, palm oil, and mining. The site focuses on delivering critical perspectives on these themes, including the actors involved and the relevant policies.

                Pasopati Project is dedicated to achieving one of Auriga’s goals: eliminating destructive actions against natural resources. The site is managed by Auriga; however, certain exposures and features on the site are produced in collaboration with its network partners.
            ',
        ],
        'image' => '/images/default-og.jpg',
        'type' => 'website',
    ];

    protected string $locale = 'id';

    public function setLocale(string $locale): static
    {
        $this->locale = in_array($locale, ['id', 'en']) ? $locale : 'id';

        return $this;
    }

    public function set(string $key, $value)
    {
        $this->meta[$key] = $value;

        return $this;
    }

    public function get(string $key)
    {
        $val = $this->meta[$key] ?? null;
        if (is_array($val)) {
            return $val[$this->locale] ?? reset($val);
        }

        return $val;
    }

    public function all()
    {
        return [
            'title' => $this->get('title'),
            'description' => $this->get('description'),
            'image' => $this->get('image'),
            'type' => $this->get('type'),
            'locale' => $this->locale,
        ];
    }
}
