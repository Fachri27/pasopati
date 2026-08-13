<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExposeEudrDummySeeder extends Seeder
{
    public function run(): void
    {
        $slug = 'pasca-laporan-risky-business-perubahan-kini-pada-perdagangan-kayu-indonesia-eu-membuktikan-pentingnya-eudr';

        $pageId = DB::table('pages')->insertGetId([
            'slug' => $slug,
            'type' => 'parallax',
            'page_type' => 'expose',
            'featured_image' => null,
            'expose_type' => json_encode(['deforestasi', 'pulp']),
            'published_at' => Carbon::now()->subDays(2),
            'status' => 'active',
            'source_type' => 'manual',
            'source_file' => null,
            'user_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ID translation
        DB::table('page_translations')->insert([
            'page_id' => $pageId,
            'locale' => 'id',
            'title' => 'Pasca laporan Risky Business: Perubahan kini pada perdagangan kayu Indonesia-EU membuktikan pentingnya EUDR',
            'excerpt' => 'Regulasi ini berpeluang menghadirkan perbaikan yang dibutuhkan guna menghentikan hilangnya hutan alam tersisa.',
            'content' => '<p>Konten lama — gunakan content_blocks.</p>',
            'content_blocks' => json_encode([
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<ul>
<li>Pada Oktober 2025, Earthsight dan Auriga menerbitkan laporan berjudul <em>Risky Business</em> yang mengungkap bagaimana korporasi kayu Indonesia yang menyerap kayu hasil pembabatan habitat orangutan di Kalimantan, turut menyuplai produk kayu kerasnya ke pasar Uni Eropa.</li>
<li>Sejak saat itu, sejumlah perusahaan besar yang disebutkan dalam laporan tersebut telah mengambil langkah-langkah signifikan untuk menyingkirkan \'kayu hasil deforestasi\' dari rantai pasokan mereka—sebuah manuver untuk mematuhi Peraturan Uni Eropa tentang Deforestasi (EUDR), yang akan menutup akses pasar bagi impor komoditas yang terkait dengan pembukaan hutan atau praktik ilegal mulai Desember 2026.</li>
<li>Penelitian terbaru ini menunjukkan bahwa EUDR telah memberikan dampak yang sangat besar dalam mewujudkan perdagangan kayu Uni Eropa yang lebih berkelanjutan, serta menegaskan bahwa banyak perusahaan Uni Eropa telah siap menghadapi penerapannya.</li>
<li>Namun, perusahaan-perusahaan di Uni Eropa tetap melanjutkan impor beresiko sangat tinggi, termasuk dari produsen kayu lapis asal Indonesia yang pada 2024 memperoleh hampir seluruh kayu tropisnya dari kegiatan deforestasi di Kalimantan.</li>
<li>Hal ini dengan jelas menegaskan mengapa EUDR sangat diperlukan: pasar tetap mempertahankan permintaan dan perdagangan produk-produk hasil deforestasi, meskipun perusahaan-perusahaan secara individual telah meningkatkan upaya mereka.</li>
</ul>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Penelitian terbaru dari Earthsight dan Auriga menemukan bahwa beberapa aktor utama dalam perdagangan kayu antara Indonesia dan Uni Eropa telah mengambil langkah-langkah signifikan untuk membersihkan rantai pasokan mereka serta menghentikan penggunaan kayu yang terkait dengan deforestasi, setelah laporan kami berjudul <a href="https://www.earthsight.org.uk/risky-business">Risky Business</a> menyoroti perdagangan kayu yang dihasilkan melalui perusakan hutan-hutan berharga. Temuan ini menunjukkan bahwa Peraturan Uni Eropa tentang Deforestasi (EUDR) memiliki potensi besar untuk mendorong produksi komoditas yang lebih berkelanjutan di seluruh dunia.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Namun, melalui analisis kami, kami mengidentifikasi perusahaan-perusahaan Indonesia yang terus menggunakan kayu hasil deforestasi di kawasan konsesi perkebunan kelapa sawit dan hutan tanaman industri, serta mengekspor produk kayu tersebut ke Uni Eropa. Pada 2025, salah satu perusahaan membeli berton-ton kayu hasil deforestasi di Kalimantan dan menjual triplek kayu keras kepada sebuah perusahaan Italia yang memasok hampir semua produsen kendaraan rekreasi (RV) besar di Eropa.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Hal ini dengan jelas menegaskan mengapa EUDR sangat diperlukan. Mulai Desember 2026, peraturan baru ini akan mewajibkan para importir komoditas berisiko deforestasi, seperti minyak sawit, karet, kopi, dan kayu, untuk melacak barang hingga ke titik asalnya serta memastikan bahwa barang tersebut tidak berasal dari lahan yang mengalami deforestasi setelah 2020. Peraturan ini juga akan melarang importir memasukkan kayu yang dihasilkan melalui \'konversi\' hutan alam menjadi penggunaan lahan lain, seperti perkebunan yang memasok industri minyak sawit atau pulp dan kertas, ke dalam pasar Uni Eropa.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Penelitian terbaru ini memperingatkan semua importir kayu asal Indonesia di Uni Eropa bahwa selama mereka membeli dari perusahaan yang mengolah kayu hasil deforestasi, mereka menempatkan rantai pasokan mereka pada risiko tinggi tidak memenuhi persyaratan EUDR di masa mendatang, yang dapat mengakibatkan sanksi berat, termasuk denda hingga 4 persen dari total omzet tahunan di seluruh Uni Eropa.</p>',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'src' => 'pages/default-image.jpg',
                        'caption' => 'Kayu gelondongan dari hutan alam yang ditebang di kawasan konsesi kelapa sawit PT Bina Sarana Sawit Utama di Kalimantan Tengah, Kalimantan, November 2024. Label kuning tersebut menandakan bahwa kayu gelondongan tersebut telah terdaftar secara sah dalam sistem penelusuran kayu Indonesia © Auriga / Earthsight',
                        'alignment' => 'center',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<h2>Dampak langkah positif dari EUDR</h2>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Laporan <a href="https://www.earthsight.org.uk/risky-business">Risky Business</a> yang diterbitkan oleh Earthsight dan Auriga Nusantara pada Oktober 2025, menyoroti salah satu rantai perdagangan kayu paling tidak berkelanjutan di dunia. Laporan tersebut mengungkap bagaimana kayu gelondongan yang dihasilkan melalui penghancuran permanen hutan alam di Kalimantan, termasuk habitat orangutan Kalimantan yang terancam punah, lalu dibeli oleh perusahaan pengolahan kayu besar di Indonesia dan dijual kembali ke pasar internasional, termasuk Uni Eropa.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Banyak aktor kunci yang diidentifikasi dalam Risky Business telah mengambil tindakan penting untuk mengurangi ketergantungan mereka pada \'kayu hasil deforestasi\' ini, yang merupakan contoh nyata dampak positif EUDR, bahkan sebelum peraturan tersebut diberlakukan.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Pada tahun 2025, PT Korindo Ariabima Sari menjadi eksportir kayu terbesar kedua dari Indonesia ke Uni Eropa. Pada 2024, perusahaan itu membeli 14.497 meter kubik kayu hasil deforestasi, yang merepresentasikan 8 persen dari total pasokan kayunya, dari pembukaan lahan di konsesi PT Indosubur Sukses Makmur, di ujung timur Kalimantan. Pada tahun itu, hutan alam seluas Central Park di New York, atau tiga kali luas Hyde Park di London, dihancurkan di dalam konsesi tersebut untuk membuka lahan perkebunan kayu.</p>',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'PT Korindo Ariabima Sari berkomitmen untuk memastikan bahwa seluruh rantai pasokan kayu kami bebas dari deforestasi. Kami terus memperkuat sistem pengadaan kami agar transparan, bertanggung jawab, dan selaras dengan peraturan global, termasuk EUDR.',
                        'source' => 'Kim Young Man, Direktur Utama PT Korindo Ariabima Sari',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>PT Kayu Multiguna Indonesia, eksportir kayu terbesar keempat dari Indonesia ke Uni Eropa pada 2025, pada tahun sebelumnya memperoleh 9 persen bahan baku kayunya dari deforestasi di Kalimantan, termasuk dari konsesi kelapa sawit PT Bina Sarana Sawit Utama.</p>',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'src' => 'pages/default-image.jpg',
                        'caption' => 'Pembukaan hutan di konsesi perusahaan perkebunan kelapa sawit PT Bina Sarana Sawit Utama di Kalimantan Tengah, Kalimantan, November 2024. © Auriga / Earthsight',
                        'alignment' => 'center',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Sementara itu di Uni Eropa, Fepco International, importir kayu lapis Indonesia terbesar di Eropa, telah menerapkan rekomendasi dalam laporan Risky Business untuk menghentikan kerja sama dengan pemasok mana pun yang menggunakan kayu hasil deforestasi. Perusahaan tersebut kini mencantumkan klausul dalam kontraknya dengan para pemasok, yang mewajibkan mereka untuk berkomitmen tidak menggunakan kayu hasil deforestasi.</p>',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'Bahkan jika bahan tersebut tidak digunakan untuk produk yang diproduksi untuk kami, kami akan menghentikan kerja sama tersebut semata-mata karena kami tidak ingin dikaitkan dengan pemasok yang terlibat dalam deforestasi.',
                        'source' => 'Alexander de Groot, Direktur Utama Fepco International',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<h2>Risiko dari deforestasi yang terus berlanjut</h2>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Meskipun telah terjadi kemajuan ini, beberapa perusahaan di Uni Eropa tetap mengimpor kayu yang terkait dengan deforestasi di Indonesia. Analisis terbaru Earthsight terhadap catatan perdagangan Indonesia pada 2025 mengidentifikasi impor dari pemasok berisiko tinggi yang sangat bergantung pada kayu hasil deforestasi.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Pada 2024, PT Kayu Lapis Asli Murni (PT KLAM) memperoleh 87 persen kayu bulat hutan alamnya, setara 17.853 meter kubik (m³), dari pembukaan hutan di kawasan konsesi PT Indosubur Sukses Makmur (PT ISM) di Kalimantan Timur. Perusahaan Italia Maller Srl — produsen panel berbahan dasar kayu untuk rumah mobil, motorhome, campervan, dan furniture — tercatat mengimpor 273 m³ kayu lapis meranti dari PT KLAM pada 2025.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Meranti merupakan jenis pohon kayu keras yang hanya tumbuh di hutan alam. Industri banyak menggunakan papan lapis meranti, yang juga dikenal sebagai lauan, dalam pembuatan kendaraan rekreasi (RV). Sebuah majalah industri karavan melaporkan bahwa Maller Srl memasok panel berbahan dasar kayu kepada hampir semua produsen kendaraan rekreasi (RV) di Eropa.</p>',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'src' => 'pages/default-image.jpg',
                        'caption' => 'Deforestasi di kawasan konsesi PT Indosubur Sukses Makmur, Kalimantan Timur, Februari 2024–November 2025. Sumber gambar: Sentinel-2 melalui Copernicus Browser',
                        'alignment' => 'full',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<h2>Banyak perusahaan Uni Eropa menyatakan mereka siap menerapkan EUDR</h2>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>EUDR, yang disahkan pada 2023 dan semula dijadwalkan mulai berlaku pada Desember 2024, telah dua kali ditunda. Peraturan tersebut juga dilemahkan dalam revisi pada 2025. Komisi Uni Eropa kini telah ditugaskan untuk melakukan "peninjauan penyederhanaan" terhadap undang-undang tersebut.</p>',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'Singkatnya, kami sudah siap, dan siap menyesuaikan sumber pasokan kami sesuai kebutuhan untuk memastikan bahwa semua yang kami peroleh telah dipastikan sesuai persyaratan.',
                        'source' => 'Alexander de Groot, Fepco International',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Temuan kami menunjukkan betapa besarnya dampak EUDR dalam mendorong pergeseran menuju penerapan uji tuntas dan keberlanjutan di sektor kayu. Namun, temuan ini juga menegaskan bahwa perdagangan produk-produk hasil deforestasi masih terus berlangsung. Sampai EUDR berlaku sepenuhnya, perusahaan-perusahaan Uni Eropa masih akan terus mengimpor produk-produk yang terkait dengan perusakan ekosistem hutan berharga di luar negeri.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p><strong>Rekomendasi:</strong></p>
<p><strong>Para importir kayu dari Uni Eropa sebaiknya:</strong></p>
<ul>
<li>Menuntut korporasi kayu Indonesia yang ingin memasok kayu ke pasar Uni Eropa agar menghentikan sepenuhnya serapan kayu yang berasal dari hasil deforestasi.</li>
<li>Melakukan pemeriksaan segera untuk melacak titik tebang hingga ke tingkat konsesi; mengurai rantai pasok antaranya; serta mengidentifikasi jejak deforestasi di konsesi-konsesi tersebut sejak Desember 2020.</li>
<li>Mencabut kontrak dengan pemasok kayu Indonesia yang terbukti memasok kayu hasil deforestasi.</li>
</ul>
<p><strong>Komisi Eropa, Parlemen, dan Dewan seharusnya:</strong></p>
<ul>
<li>Menolak segala upaya penundaan atau pelemahan EUDR.</li>
<li>Mendorong sepenuhnya implementasi EUDR pada akhir 2026.</li>
</ul>
<p><strong>Perusahaan kayu Indonesia harus:</strong></p>
<ul>
<li>Menghentikan pembelian kayu yang berasal dari konversi hutan alam.</li>
<li>Menyatakan penolakan mereka terhadap kayu hasil deforestasi secara terbuka.</li>
</ul>',
                    ],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // EN translation
        DB::table('page_translations')->insert([
            'page_id' => $pageId,
            'locale' => 'en',
            'title' => 'After the Risky Business report: Changes in Indonesia-EU timber trade prove the importance of EUDR',
            'excerpt' => 'This regulation has the potential to bring much-needed improvements to stop the loss of remaining natural forests.',
            'content' => '<p>Legacy content — use content_blocks.</p>',
            'content_blocks' => json_encode([
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<ul>
<li>In October 2025, Earthsight and Auriga published a report titled <em>Risky Business</em> revealing how Indonesian timber corporations absorbing wood from orangutan habitat clearing in Kalimantan also supply hardwood products to the EU market.</li>
<li>Since then, several major companies named in the report have taken significant steps to eliminate \'deforestation timber\' from their supply chains — a maneuver to comply with the EU Deforestation Regulation (EUDR).</li>
<li>This latest research shows that EUDR has had a tremendous impact in creating more sustainable EU timber trade.</li>
<li>However, EU companies continue to import high-risk products, including from an Indonesian plywood producer that obtained nearly all its tropical timber from deforestation in Kalimantan in 2024.</li>
</ul>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Recent research by Earthsight and Auriga found that several key actors in the Indonesia-EU timber trade have taken significant steps to clean up their supply chains and stop using deforestation-linked timber, following our report <a href="https://www.earthsight.org.uk/risky-business">Risky Business</a> highlighting the trade in timber from the destruction of precious forests.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>The EUDR, adopted in 2023, will require importers of deforestation-risk commodities such as palm oil, rubber, coffee, and timber to trace goods back to their point of origin and ensure they did not come from land deforested after 2020. This regulation will ban importers from bringing timber produced through \'conversion\' of natural forests into other land uses into the EU market.</p>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<h2>Positive impact of EUDR</h2>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Many key actors identified in Risky Business have taken important steps to reduce their reliance on deforestation timber — a concrete example of EUDR\'s positive impact, even before the regulation has taken full effect.</p>',
                    ],
                ],
                [
                    'type' => 'quote',
                    'data' => [
                        'text' => 'PT Korindo Ariabima Sari is committed to ensuring that our entire timber supply chain is free from deforestation. We continue to strengthen our procurement system to be transparent, responsible, and aligned with global regulations, including EUDR.',
                        'source' => 'Kim Young Man, President Director of PT Korindo Ariabima Sari',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<h2>Ongoing deforestation risks</h2>',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Despite this progress, some EU companies continue to import deforestation-linked timber from Indonesia. In 2024, PT Kayu Lapis Asli Murni (PT KLAM) obtained 87 percent of its natural forest logs from forest clearing in the concession of PT Indosubur Sukses Makmur in East Kalimantan.</p>',
                    ],
                ],
                [
                    'type' => 'image',
                    'data' => [
                        'src' => 'pages/default-image.jpg',
                        'caption' => 'Deforestation in the concession of PT Indosubur Sukses Makmur, East Kalimantan, February 2024–November 2025. Source: Sentinel-2 via Copernicus Browser',
                        'alignment' => 'full',
                    ],
                ],
                [
                    'type' => 'paragraph',
                    'data' => [
                        'html' => '<p>Our findings show the enormous impact EUDR has in driving a shift towards due diligence and sustainability in the timber sector. However, they also confirm that trade in deforestation-linked products continues. Until EUDR is fully enforced, EU companies will continue importing products linked to the destruction of precious forest ecosystems abroad.</p>',
                    ],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info("EUDR dummy article created: /id/expose/{$slug}");
    }
}
