<?php

namespace Tests\Feature;

use App\Models\Petition;
use App\Models\PetitionSignature;
use App\Models\PetitionTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PetitionExportPdfTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $editor;

    private Petition $petition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->editor = User::factory()->create(['role' => 'editor']);

        $this->petition = Petition::factory()->create();
        PetitionTranslation::factory()->create([
            'petition_id' => $this->petition->id,
            'locale' => 'id',
        ]);
    }

    public function test_unauthenticated_user_cannot_access_export_pdf(): void
    {
        $response = $this->get(route('petition.admin.export-pdf', $this->petition->id));

        $response->assertRedirect('/login');
    }

    public function test_admin_can_export_pdf_for_petition_without_signatures(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('petition.admin.export-pdf', $this->petition->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $response->content());
    }

    public function test_editor_can_export_pdf(): void
    {
        $response = $this->actingAs($this->editor)
            ->get(route('petition.admin.export-pdf', $this->petition->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pdf_does_not_include_email_of_signatories(): void
    {
        PetitionSignature::factory()->create([
            'petition_id' => $this->petition->id,
            'email' => 'secret@example.com',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('petition.admin.export-pdf', $this->petition->id));

        $this->assertStringNotContainsString('secret@example.com', $response->content());
    }

    public function test_pdf_returns_download_headers(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('petition.admin.export-pdf', $this->petition->id));

        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('.pdf', $response->headers->get('Content-Disposition') ?? '');
    }

    public function test_endpoint_returns_404_for_nonexistent_petition(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/petisi/99999/export-pdf');

        $response->assertNotFound();
    }
}
