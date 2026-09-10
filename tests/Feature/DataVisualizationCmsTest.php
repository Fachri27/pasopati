<?php

namespace Tests\Feature;

use App\Livewire\DataVisualization\DataVisualizationForm;
use App\Livewire\DataVisualization\DataVisualizationIndex;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Pembuat grafik di CMS (menu Data & Grafik).
 *
 * Editor mengetik angkanya langsung di tabel ala spreadsheet, bukan menempel
 * kode embed dari layanan luar — karena itu chart_data disimpan sebagai JSON
 * mentah dan grafiknya digambar Chart.js dari angka itu, baik di pratinjau CMS
 * maupun di halaman embed yang dibaca pembaca. Yang dijaga di sini adalah
 * perjalanan angka itu: dari tabel di form, ke kolom chart_data, sampai ke
 * halaman embed.
 */
class DataVisualizationCmsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_editor_menyusun_grafik_dari_tabel_lalu_menyimpannya(): void
    {
        Livewire::actingAs($this->admin())
            ->test(DataVisualizationForm::class)
            ->assertSet('columns', ['Kategori', 'Nilai'])
            ->set('title', 'Laju deforestasi Mayawana')
            ->set('description', 'Luas tutupan hutan yang hilang per tahun.')
            ->set('chart_type', 'column')
            ->set('rows', [['2022', '120'], ['2023', '260'], ['2024', '410']])
            ->set('top_text', 'Laju deforestasi')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('data-visualizations.index'));

        $baris = DB::table('data_visualizations')->first();

        $this->assertSame('Laju deforestasi Mayawana', $baris->title);
        $this->assertSame('column', $baris->chart_type);
        // provider 'internal' menandai grafik yang digambar sendiri, bukan
        // sematan dari layanan luar — karena itu embed_url-nya kosong.
        $this->assertSame('internal', $baris->provider);
        $this->assertNull($baris->embed_url);

        $data = json_decode($baris->chart_data, true);
        $this->assertSame(['Kategori', 'Nilai'], $data['columns']);
        $this->assertSame([['2022', '120'], ['2023', '260'], ['2024', '410']], $data['rows']);
        $this->assertSame('Laju deforestasi', $data['top_text']);
    }

    public function test_tabel_bisa_ditambah_dan_dikurangi_kolomnya(): void
    {
        Livewire::actingAs($this->admin())
            ->test(DataVisualizationForm::class)
            ->set('rows', [['2024', '120']])
            ->call('addColumn')
            ->assertCount('columns', 3)
            // Kolom baru harus ikut menambah sel di setiap baris, kalau tidak
            // validasi rows.*.size akan menolak simpanan.
            ->assertSet('rows', [['2024', '120', '']])
            ->call('addRow')
            ->assertCount('rows', 2)
            ->call('removeColumn', 2)
            ->assertCount('columns', 2)
            ->assertSet('rows.0', ['2024', '120'])
            // Kolom pertama adalah label sumbu, tidak boleh dihapus.
            ->call('removeColumn', 0)
            ->assertCount('columns', 2);
    }

    public function test_sankey_menyiapkan_tiga_kolomnya_sendiri(): void
    {
        Livewire::actingAs($this->admin())
            ->test(DataVisualizationForm::class)
            ->set('chart_type', 'sankey')
            // Sankey butuh Sumber, Tujuan, Nilai — tabel dua kolom bawaan
            // tidak cukup, jadi form menyusun ulang saat tabelnya masih kosong.
            ->assertSet('columns', ['Sumber', 'Tujuan', 'Nilai']);
    }

    public function test_menolak_jenis_grafik_yang_tidak_dikenal(): void
    {
        Livewire::actingAs($this->admin())
            ->test(DataVisualizationForm::class)
            ->set('title', 'Grafik')
            ->set('chart_type', 'gambar-kucing')
            ->call('save')
            ->assertHasErrors(['chart_type']);
    }

    public function test_form_edit_memuat_kembali_angka_yang_tersimpan(): void
    {
        $id = DB::table('data_visualizations')->insertGetId([
            'title' => 'Laju deforestasi',
            'description' => null,
            'chart_type' => 'line',
            'chart_data' => json_encode([
                'columns' => ['Tahun', 'Ha'],
                'rows' => [['2024', '120']],
                'top_text' => 'Judul atas',
                'show_legend' => false,
            ]),
            'provider' => 'internal',
            'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        Livewire::actingAs($this->admin())
            ->test(DataVisualizationForm::class, ['visualizationId' => $id])
            ->assertSet('title', 'Laju deforestasi')
            ->assertSet('chart_type', 'line')
            ->assertSet('columns', ['Tahun', 'Ha'])
            ->assertSet('rows', [['2024', '120']])
            ->assertSet('top_text', 'Judul atas')
            ->assertSet('show_legend', false);
    }

    public function test_menonaktifkan_grafik_menariknya_dari_editor_dan_pembaca(): void
    {
        $id = DB::table('data_visualizations')->insertGetId([
            'title' => 'Angka yang ditarik',
            'chart_type' => 'column',
            'chart_data' => json_encode(['columns' => ['Tahun', 'Ha'], 'rows' => [['2024', '120']]]),
            'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $admin = $this->admin();

        Livewire::actingAs($admin)
            ->test(DataVisualizationIndex::class)
            ->call('toggleStatus', $id);

        $this->assertFalse((bool) DB::table('data_visualizations')->find($id)->is_active);

        // Hilang dari dialog "+ Data/Grafik" di editor...
        $this->actingAs($admin)
            ->getJson(route('data-visualizations.options'))
            ->assertJsonCount(0, 'data');

        // ...dan ikut mati di laporan yang sudah terlanjur menyisipkannya.
        $this->get(route('data-visualizations.embed', $id))->assertNotFound();
    }
}
