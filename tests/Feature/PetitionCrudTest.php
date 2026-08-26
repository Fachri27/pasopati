<?php

namespace Tests\Feature;

use App\Livewire\Petition\PetitionForm;
use App\Livewire\Petition\PetitionTable;
use App\Models\Petition;
use App\Models\PetitionSignature;
use App\Models\PetitionTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PetitionCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('id');
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    private function createPetition(string $titleId, string $titleEn, string $status = 'active', int $goal = 1000): Petition
    {
        $p = Petition::factory()->create([
            'status' => $status,
            'goal_count' => $goal,
            'user_id' => $this->admin->id,
        ]);
        PetitionTranslation::create([
            'petition_id' => $p->id,
            'locale' => 'id',
            'title' => $titleId,
            'description' => 'Deskripsi '.$titleId,
        ]);
        PetitionTranslation::create([
            'petition_id' => $p->id,
            'locale' => 'en',
            'title' => $titleEn,
            'description' => 'Description '.$titleEn,
        ]);
        return $p;
    }

    // --- Table Tests ---

    public function test_guest_cannot_access_petition_admin(): void
    {
        $this->get(route('petition.admin.index'))
            ->assertRedirect();
    }

    public function test_admin_can_view_petition_table(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionTable::class)
            ->assertOk();
    }

    public function test_petition_table_shows_data(): void
    {
        $this->createPetition('Hentikan Deforestasi', 'Stop Deforestation');

        Livewire::actingAs($this->admin)
            ->test(PetitionTable::class)
            ->assertSee('Hentikan Deforestasi');
    }

    public function test_petition_table_search(): void
    {
        $this->createPetition('Hentikan Deforestasi', 'Stop Deforestation');
        $this->createPetition('Selamatkan Hutan', 'Save Forest');

        Livewire::actingAs($this->admin)
            ->test(PetitionTable::class)
            ->set('search', 'Deforestasi')
            ->assertSee('Hentikan Deforestasi')
            ->assertDontSee('Selamatkan Hutan');
    }

    public function test_petition_table_filter_by_status(): void
    {
        $this->createPetition('Active Petition', 'Active Pet', 'active');
        $this->createPetition('Draft Petition', 'Draft Pet', 'draft');

        Livewire::actingAs($this->admin)
            ->test(PetitionTable::class)
            ->set('filterStatus', 'active')
            ->assertSee('Active Petition')
            ->assertDontSee('Draft Petition');
    }

    public function test_admin_can_delete_petition(): void
    {
        $p = $this->createPetition('To Delete', 'Delete Me');

        Livewire::actingAs($this->admin)
            ->test(PetitionTable::class)
            ->call('delete', $p->id);

        $this->assertDatabaseMissing('petitions', ['id' => $p->id]);
    }

    public function test_delete_petition_also_deletes_signatures(): void
    {
        $p = $this->createPetition('With Signatures', 'With Sigs');
        PetitionSignature::create([
            'petition_id' => $p->id,
            'name' => 'John',
            'email' => 'john@test.com',
            'ip_address' => '127.0.0.1',
            'is_verified' => true,
        ]);

        Livewire::actingAs($this->admin)
            ->test(PetitionTable::class)
            ->call('delete', $p->id);

        $this->assertDatabaseMissing('petition_signatures', ['petition_id' => $p->id]);
    }

    // --- Form Tests ---

    public function test_admin_can_view_petition_create_form(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->assertOk();
    }

    public function test_admin_can_create_petition(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->set('title_id', 'Petisi Lingkungan')
            ->set('title_en', 'Environmental Petition')
            ->set('target_name', 'PT Minang')
            ->set('goal_count', 1000)
            ->set('status', 'active')
            ->call('save')
            ->assertRedirect(route('petition.admin.index'));

        $this->assertDatabaseHas('petitions', [
            'slug' => 'petisi-lingkungan',
            'status' => 'active',
            'goal_count' => 1000,
        ]);
        $this->assertDatabaseHas('petition_translations', [
            'locale' => 'id',
            'title' => 'Petisi Lingkungan',
        ]);
    }

    public function test_create_petition_validates_required_fields(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->call('save')
            ->assertHasErrors(['title_id', 'title_en', 'target_name', 'goal_count']);
    }

    public function test_create_petition_validates_goal_count(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->set('title_id', 'Test')
            ->set('title_en', 'Test')
            ->set('target_name', 'Test')
            ->set('goal_count', 0)
            ->set('status', 'draft')
            ->call('save')
            ->assertHasErrors(['goal_count']);
    }

    public function test_create_petition_validates_status(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->set('title_id', 'Test')
            ->set('title_en', 'Test')
            ->set('target_name', 'Test')
            ->set('goal_count', 100)
            ->set('status', 'invalid')
            ->call('save')
            ->assertHasErrors(['status']);
    }

    public function test_petition_slug_auto_generated(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->set('title_id', 'Petisi Khusus Spesial!')
            ->set('title_en', 'Special Petition')
            ->set('target_name', 'PT Test')
            ->set('goal_count', 500)
            ->set('status', 'draft')
            ->call('save')
            ->assertRedirect();

        $this->assertDatabaseHas('petitions', [
            'slug' => 'petisi-khusus-spesial',
        ]);
    }

    public function test_admin_can_edit_petition(): void
    {
        $p = $this->createPetition('Original Title', 'Original EN', 'draft');

        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class, ['petitionId' => $p->id])
            ->assertSet('title_id', 'Original Title')
            ->set('title_id', 'Updated Title')
            ->set('status', 'active')
            ->call('save')
            ->assertRedirect(route('petition.admin.index'));

        $this->assertDatabaseHas('petition_translations', [
            'petition_id' => $p->id,
            'locale' => 'id',
            'title' => 'Updated Title',
        ]);
    }

    public function test_petition_demand_management(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->set('demandInput', 'Hentikan deforestasi')
            ->call('addDemand')
            ->assertSet('demands', ['Hentikan deforestasi'])
            ->set('demandInput', 'Tutup tambang ilegal')
            ->call('addDemand')
            ->assertSet('demands', ['Hentikan deforestasi', 'Tutup tambang ilegal']);
    }

    public function test_petition_remove_demand(): void
    {
        Livewire::actingAs($this->admin)
            ->test(PetitionForm::class)
            ->set('demands', ['Demand 1', 'Demand 2', 'Demand 3'])
            ->call('removeDemand', 1)
            ->assertSet('demands', ['Demand 1', 'Demand 3']);
    }

    public function test_editor_can_access_petition(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        Livewire::actingAs($editor)
            ->test(PetitionTable::class)
            ->assertOk();

        Livewire::actingAs($editor)
            ->test(PetitionForm::class)
            ->assertOk();
    }
}
