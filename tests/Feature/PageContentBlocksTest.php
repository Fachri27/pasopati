<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageContentBlocksTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_save_and_retrieve_content_blocks()
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $blocks = [
            ['type' => 'paragraph', 'data' => ['html' => '<p>Halo dunia</p>']],
            ['type' => 'event_info_box', 'data' => [
                'format' => 'Online',
                'date' => '2026-07-15',
                'time' => '14:00 WIB',
                'venue' => 'Zoom',
                'registration_links' => [['day' => 'Hari 1', 'url' => 'https://example.com']],
                'notes' => 'Gratis',
            ]],
            ['type' => 'quote', 'data' => ['text' => 'Kutipan', 'source' => 'Narasumber']],
        ];

        $page = Page::create([
            'slug' => 'test-blocks',
            'page_type' => 'expose',
            'type' => 'default',
            'status' => 'draft',
            'user_id' => auth()->id(),
        ]);

        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'Test',
            'content' => '<p>Legacy</p>',
            'content_blocks' => $blocks,
        ]);

        $translation = $page->translations()->where('locale', 'id')->first();
        $this->assertIsArray($translation->content_blocks);
        $this->assertCount(3, $translation->content_blocks);
        $this->assertEquals('paragraph', $translation->content_blocks[0]['type']);
        $this->assertEquals('<p>Halo dunia</p>', $translation->content_blocks[0]['data']['html']);
        $this->assertEquals('Online', $translation->content_blocks[1]['data']['format']);
        $this->assertEquals('Kutipan', $translation->content_blocks[2]['data']['text']);
    }

    public function test_plain_text_extracts_from_content_blocks()
    {
        $page = Page::create([
            'slug' => 'test-plaintext',
            'page_type' => 'expose',
            'type' => 'default',
            'status' => 'draft',
        ]);

        $translation = PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'Test',
            'content_blocks' => [
                ['type' => 'paragraph', 'data' => ['html' => '<p>Paragraf <b>tebal</b></p>']],
                ['type' => 'quote', 'data' => ['text' => 'Kutipan penting', 'source' => 'Sumber']],
                ['type' => 'event_info_box', 'data' => ['format' => 'Online', 'notes' => 'Catatan acara']],
            ],
        ]);

        $text = $translation->plainText();
        $this->assertStringContainsString('Paragraf', $text);
        $this->assertStringContainsString('Kutipan penting', $text);
        $this->assertStringContainsString('Catatan acara', $text);
        $this->assertStringContainsString('Online', $text);
    }

    public function test_frontend_renders_blocks_instead_of_fallback_content()
    {
        $page = Page::create([
            'slug' => 'test-render-blocks',
            'page_type' => 'expose',
            'type' => 'default',
            'status' => 'active',
            'published_at' => now(),
        ]);

        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'Test Expose',
            'excerpt' => 'Excerpt',
            'content' => '<p>Fallback content</p>',
            'content_blocks' => [
                ['type' => 'paragraph', 'data' => ['html' => '<p>Block content</p>']],
            ],
        ]);

        $response = $this->get(route('show-page', [
            'locale' => 'id',
            'page_type' => 'expose',
            'slug' => $page->slug,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Block content');
        $response->assertDontSee('Fallback content');
    }
}
