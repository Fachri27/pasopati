<?php

namespace Tests\Feature;

use App\Livewire\Pages\PageForm;
use App\Livewire\Pages\PageTable;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArtikelCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_guest_cannot_access_page_table(): void
    {
        $this->get(route('pages.index'))
            ->assertRedirect();
    }

    public function test_admin_can_view_page_table(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PageTable::class)
            ->assertOk();
    }

    public function test_admin_sees_existing_pages(): void
    {
        $page = Page::factory()->create();
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'Artikel Pertama',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PageTable::class)
            ->assertSee('Artikel Pertama');
    }

    public function test_page_table_search(): void
    {
        $page = Page::factory()->create();
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'Investigasi Deforestasi',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PageTable::class)
            ->set('search', 'Investigasi')
            ->assertSee('Investigasi Deforestasi');
    }

    public function test_page_table_search_excludes_unrelated(): void
    {
        $page1 = Page::factory()->create();
        PageTranslation::create([
            'page_id' => $page1->id,
            'locale' => 'id',
            'title' => 'Judul A',
        ]);
        $page2 = Page::factory()->create();
        PageTranslation::create([
            'page_id' => $page2->id,
            'locale' => 'id',
            'title' => 'Judul B',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PageTable::class)
            ->set('search', 'Judul A')
            ->assertSee('Judul A')
            ->assertDontSee('Judul B');
    }

    public function test_page_table_filter_by_status(): void
    {
        $active = Page::factory()->create(['status' => 'active']);
        PageTranslation::create([
            'page_id' => $active->id,
            'locale' => 'id',
            'title' => 'Active Page',
        ]);
        $draft = Page::factory()->create(['status' => 'draft']);
        PageTranslation::create([
            'page_id' => $draft->id,
            'locale' => 'id',
            'title' => 'Draft Page',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PageTable::class)
            ->set('status', 'active')
            ->assertSee('Active Page')
            ->assertDontSee('Draft Page');
    }

    public function test_admin_can_delete_page(): void
    {
        $page = Page::factory()->create();
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'To Delete',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PageTable::class)
            ->call('delete', $page->id);

        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    // --- PageForm Tests ---

    public function test_admin_can_view_page_create_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PageForm::class)
            ->assertOk();
    }

    public function test_admin_can_create_page(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PageForm::class)
            ->set('title_id', 'Artikel Baru Indonesia')
            ->set('title_en', 'New Article English')
            ->set('page_type', 'expose')
            ->set('type', 'default')
            ->set('status', 'active')
            ->set('published_at', '2026-08-01')
            ->call('save')
            ->assertRedirect(route('pages.index'));

        $this->assertDatabaseHas('pages', [
            'slug' => 'artikel-baru-indonesia',
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('page_translations', [
            'locale' => 'id',
            'title' => 'Artikel Baru Indonesia',
        ]);
    }

    public function test_create_page_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PageForm::class)
            ->call('save')
            ->assertHasErrors(['title_id', 'title_en']);
    }

    public function test_create_page_validates_status(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PageForm::class)
            ->set('title_id', 'Test')
            ->set('title_en', 'Test')
            ->set('page_type', 'expose')
            ->set('type', 'default')
            ->set('status', 'invalid_status')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    public function test_create_page_validates_page_type(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PageForm::class)
            ->set('title_id', 'Test')
            ->set('title_en', 'Test')
            ->set('page_type', 'invalid')
            ->set('type', 'default')
            ->set('status', 'draft')
            ->call('save')
            ->assertHasErrors(['page_type']);
    }

    public function test_admin_can_edit_page(): void
    {
        $page = Page::factory()->create(['status' => 'draft']);
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'Original Title',
        ]);
        PageTranslation::create([
            'page_id' => $page->id,
            'locale' => 'en',
            'title' => 'Original EN',
        ]);

        Livewire::actingAs($this->admin)
            ->test(PageForm::class, ['pageId' => $page->id])
            ->assertSet('title_id', 'Original Title')
            ->set('title_id', 'Updated Title')
            ->set('status', 'active')
            ->call('save')
            ->assertRedirect(route('pages.index'));

        $this->assertDatabaseHas('page_translations', [
            'page_id' => $page->id,
            'locale' => 'id',
            'title' => 'Updated Title',
        ]);
    }

    public function test_page_slug_is_auto_generated(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PageForm::class)
            ->set('title_id', 'Judul Dengan Spesial Karakter!@#')
            ->set('title_en', 'Special Chars Title')
            ->set('page_type', 'expose')
            ->set('type', 'default')
            ->set('status', 'draft')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('pages', [
            'slug' => 'judul-dengan-spesial-karakter-at',
        ]);
    }

    public function test_editor_can_access_page_crud(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        Livewire::actingAs($editor)
            ->test(PageTable::class)
            ->assertOk();

        Livewire::actingAs($editor)
            ->test(PageForm::class)
            ->assertOk();
    }
}
