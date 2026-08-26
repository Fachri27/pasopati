<?php

namespace Tests\Feature;

use App\Livewire\KategoriForm;
use App\Livewire\KategoriTable;
use App\Models\Kategori;
use App\Models\KategoriTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class KategoriCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function createKategori(string $nameId, string $nameEn): Kategori
    {
        $k = Kategori::create();
        KategoriTranslation::create(['kategori_id' => $k->id, 'locale' => 'id', 'kategori_name' => $nameId]);
        KategoriTranslation::create(['kategori_id' => $k->id, 'locale' => 'en', 'kategori_name' => $nameEn]);
        return $k;
    }

    // --- Table Tests ---

    public function test_guest_cannot_access_kategori(): void
    {
        $this->get(route('kategori.index'))
            ->assertRedirect();
    }

    public function test_admin_can_view_kategori_table(): void
    {
        Livewire::actingAs($this->admin)
            ->test(KategoriTable::class)
            ->assertOk();
    }

    public function test_kategori_table_shows_data(): void
    {
        $this->createKategori('Lingkungan', 'Environment');

        Livewire::actingAs($this->admin)
            ->test(KategoriTable::class)
            ->assertSee('Lingkungan');
    }

    public function test_kategori_table_search(): void
    {
        $this->createKategori('Lingkungan', 'Environment');
        $this->createKategori('Energi', 'Energy');

        Livewire::actingAs($this->admin)
            ->test(KategoriTable::class)
            ->set('search', 'Lingkungan')
            ->assertSee('Lingkungan')
            ->assertDontSee('Energi');
    }

    public function test_admin_can_delete_kategori(): void
    {
        $k = $this->createKategori('To Delete', 'Delete Me');

        Livewire::actingAs($this->admin)
            ->test(KategoriTable::class)
            ->call('delete', $k->id);

        $this->assertDatabaseMissing('kategoris', ['id' => $k->id]);
    }

    // --- Form Tests ---

    public function test_admin_can_view_kategori_create_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(KategoriForm::class)
            ->assertOk();
    }

    public function test_admin_can_create_kategori(): void
    {
        Livewire::actingAs($this->admin)
            ->test(KategoriForm::class)
            ->set('kategori_name_id', 'Pertambangan')
            ->set('kategori_name_en', 'Mining')
            ->call('save')
            ->assertRedirect(route('kategori.index'));

        $this->assertDatabaseHas('kategori_translations', [
            'locale' => 'id',
            'kategori_name' => 'Pertambangan',
        ]);
        $this->assertDatabaseHas('kategori_translations', [
            'locale' => 'en',
            'kategori_name' => 'Mining',
        ]);
    }

    public function test_create_kategori_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(KategoriForm::class)
            ->call('save')
            ->assertHasErrors(['kategori_name_id', 'kategori_name_en']);
    }

    public function test_create_kategori_validates_max_length(): void
    {
        Livewire::actingAs($this->admin)
            ->test(KategoriForm::class)
            ->set('kategori_name_id', str_repeat('a', 101))
            ->set('kategori_name_en', 'Test')
            ->call('save')
            ->assertHasErrors(['kategori_name_id']);
    }

    public function test_admin_can_edit_kategori(): void
    {
        $k = $this->createKategori('Original', 'Original EN');

        Livewire::actingAs($this->admin)
            ->test(KategoriForm::class, ['kategoriId' => $k->id])
            ->assertSet('isEdit', true)
            ->assertSet('kategori_name_id', 'Original')
            ->set('kategori_name_id', 'Updated Name')
            ->call('save')
            ->assertRedirect(route('kategori.index'));

        $this->assertDatabaseHas('kategori_translations', [
            'kategori_id' => $k->id,
            'locale' => 'id',
            'kategori_name' => 'Updated Name',
        ]);
    }

    public function test_editor_can_access_kategori(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        Livewire::actingAs($editor)
            ->test(KategoriTable::class)
            ->assertOk();

        Livewire::actingAs($editor)
            ->test(KategoriForm::class)
            ->assertOk();
    }
}
