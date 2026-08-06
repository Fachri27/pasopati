# Integrasi webhook Deforestory (CMS pasopati → web lain)

Saat laporan di-publish di CMS pasopati (repo ini), CMS otomatis:

1. mengirim email ke **subscriber CMS sendiri** (`DeforestoryNotificationJob`), dan
2. **POST webhook** ke web lain (`DEFORESTORY_WEBHOOK_URL`) supaya web lain
   langsung memicu **queue-nya sendiri** buat mengabimi subscriber-nya sendiri
   — tanpa polling.

Dokumen ini = spesifikasi webhook yang dikirim CMS + blueprint receiver
siap-tempel untuk web lain (codebase Laravel terpisah). Web lain hanya perlu
membaca bagian **§1** (apa yang dikirim) dan **§3** (cara menerima). Bagian
**§2** hanya keterangan untuk sisi CMS.

---

## 1. Apa yang dikirim CMS ke web lain

### 1a. Permintaan HTTP

| Atribut | Nilai |
|---|---|
| Method | `POST` |
| URL | tiap nilai di `DEFORESTORY_WEBHOOK_URL` (boleh beberapa, dipisah koma) |
| Content-Type | `application/json` |
| Body | JSON (UTF-8, raw) — lihat §1c |
| Timeout sisi CMS | `DEFORESTORY_WEBHOOK_TIMEOUT` (default 10 detik) |
| Retry | `tries = 3`, `backoff = 10s` — di-retry kalau web lain balas non-2xx atau timeout |

### 1b. Headers

| Header | Isi | Wajib? |
|---|---|---|
| `X-Deforestory-Event` | jenis event. Saat ini selalu `created` (laporan baru publish) | selalu ada |
| `X-Deforestory-Delivery` | UUID pengiriman (id job) — **pakai untuk idempotensi** di penerima | selalu ada |
| `X-Deforestory-Signature` | `sha256=<HMAC_SHA256(raw_body, DEFORESTORY_WEBHOOK_SECRET)>` | **kalau secret di-set** di CMS. Kalau kosong, header ini gak dikirim. |
| `Content-Type` | `application/json` | selalu |

### 1c. Body (JSON)

```json
{
  "event": "created",
  "locale": "id",
  "case": {
    "slug": "mayawana",
    "category": "pulp",
    "year": "2021–2025"
  },
  "laporan": {
    "slug": "jejak-deforestasi-mayawana",
    "sort": 1,
    "date": "2024-11-12",
    "image": "https://pasopati.id/storage/deforestory/laporans/jejak.jpg",
    "title": "Jejak deforestasi di Mayawana",
    "desc": "Analisis spasial deforestasi di Mayawana dan keterkaitannya dengan rantai pasok grup RGE.",
    "link": "https://pasopati.id/id/deforestory/mayawana/jejak-deforestasi-mayawana",
    "translations": {
      "id": {
        "title": "Jejak deforestasi di Mayawana",
        "excerpt": "Analisis spasial deforestasi di Mayawana dan keterkaitannya dengan rantai pasok grup RGE.",
        "image": "https://pasopati.id/storage/deforestory/laporans/jejak-id.jpg",
        "link": "https://pasopati.id/id/deforestory/mayawana/jejak-deforestasi-mayawana"
      },
      "en": {
        "title": "The deforestation trail in Mayawana",
        "excerpt": "Spatial analysis of deforestation in Mayawana and its links to the RGE group supply chain.",
        "image": "https://pasopati.id/storage/deforestory/laporans/jejak-en.jpg",
        "link": "https://pasopati.id/en/deforestory/mayawana/jejak-deforestasi-mayawana"
      }
    }
  }
}
```

### 1d. Catatan penting soal payload

- **`laporan.translations`** berisi **`id` + `en`** sekaligus. Web lain cukup pilih
  `laporan.translations[<locale subscriber>]` sesuai bahasa subscriber-nya.
  Setiap locale punya `title`, `excerpt`, `image`, dan `link` (link locale-spesifik:
  `/id/...` atau `/en/...`).
- **`image` per-locale** sekarang ada di `translations.<locale>.image` — tiap
  locale bisa pakai file gambar yang beda. Fallback: `translation($locale).image`
  → `laporan.image` (legacy) → `case.featured_image`. Bila tidak ada image
  per-locale, kedua locale pakai fallback yang sama. Field top-level
  `laporan.image` tetap diisi image `id` (backward compatible).
- **Field top-level** `laporan.title` / `laporan.desc` / `laporan.link` tetap
  diisi versi `id` demi **backward compatible** — receiver lama yang belum baca
  `translations` tetap jalan. Receiver baru **disarankan pakai `translations`**.
- `laporan.date` sama untuk semua locale (gak ada di `translations`). Image
  absolut URL (`https://...`) bila CMS simpan begitu, atau
  `https://pasopati.id/storage/...` bila relatif.
- `laporan.link` / `translations[*].link` menunjuk ke **laporan asli di CMS**.
  Email ke subscriber web lain sebaiknya pakai link ini (subscriber dibawa ke
  sumber asli, bukan ke salinan di web lain).
- `case.slug` bisa dipakai web lain untuk match subscriber yang subscribe
  per-kasus (kalau web lain punya konsep itu).
- `event` saat ini hanya `created`. Nanti bisa bertambah `updated`/`deleted`
  (saat ini CMS hanya dispatch saat publish pertama kali).

### 1e. Ekspektasi balasan dari web lain

- **Balas `2xx` dengan cepat** (di bawah `DEFORESTORY_WEBHOOK_TIMEOUT`, default
  10 detik). Pekerjaan berat (query DB, kirim email) harus di-**queue async**,
  BUKAN inline di handler — kalau lambat, CMS anggap gagal → retry 3x.
- Balas non-2xx atau timeout → CMS retry. Job masuk `failed_jobs` kalau tetap
  gagal setelah 3x retry.
- Body balasan bebas (CMS gak memprosesnya). Cukup `{"received": true}`.

---

## 2. Setup di sisi CMS (repo ini)

Hanya keterangan — tim web lain boleh skip.

`.env`:
```env
DEFORESTORY_WEBHOOK_URL=https://web-lain.example/webhook/deforestory
DEFORESTORY_WEBHOOK_SECRET=rahasia-bersama-yang-sama-di-kedua-web
DEFORESTORY_WEBHOOK_TIMEOUT=10
```

- Kosongkan `DEFORESTORY_WEBHOOK_URL` kalau belum dipakai → job no-op (aman).
- `DEFORESTORY_WEBHOOK_SECRET` **harus sama persis** dengan secret di receiver
  web lain agar verifikasi signature lulus.
- Job: `app/Jobs/DeforestoryWebhookJob.php`. Dispatch saat publish pertama kali:
  `app/Livewire/Deforestory/DeforestoryLaporanForm.php` (blok `$justPublished`).
- Worker CMS wajib jalan (`php artisan queue:work`) — tanpa itu job cuma numpuk
  di tabel `jobs` dan gak pernah dikirim.

---

## 3. Blueprint receiver untuk web lain (Laravel, siap tempel)

Tim web lain tempel blok di bawah ke app mereka. Asumsi: Laravel 10/11/12,
`QUEUE_CONNECTION=database` (atau redis). Sesuaikan namespace sesuai app.

### 3a. `.env` di web lain

```env
# HARUS sama persis dengan DEFORESTORY_WEBHOOK_SECRET di CMS pasopati
DEFORESTORY_WEBHOOK_SECRET=rahasia-bersama-yang-sama-di-kedua-web

# Pakai database/redis biar async (worker jalan terpisah). Jangan "sync".
QUEUE_CONNECTION=database
```

```php
// config/services.php di web lain
'deforestory' => [
    'webhook_secret' => env('DEFORESTORY_WEBHOOK_SECRET'),
],
```

### 3b. Migration — tabel subscriber web lain

```php
// database/migrations/xxxx_create_deforestory_subscribers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('deforestory_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('locale', 5)->default('id');      // 'id' atau 'en'
            $table->string('case_slug')->nullable();        // null = semua kasus
            $table->boolean('active')->default(true);
            $table->string('unsubscribe_token', 64)->unique();
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('deforestory_subscribers'); }
};
```

### 3c. Model

```php
// app/Models/DeforestorySubscriber.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeforestorySubscriber extends Model
{
    protected $fillable = ['email', 'locale', 'case_slug', 'active', 'unsubscribe_token', 'subscribed_at'];
    protected $casts = ['active' => 'boolean', 'subscribed_at' => 'datetime'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($s) {
            if (empty($s->unsubscribe_token)) $s->unsubscribe_token = Str::random(64);
            if (empty($s->subscribed_at))     $s->subscribed_at = now();
        });
    }

    public function scopeActive($q) { return $q->where('active', true); }
}
```

### 3d. Job queue milik web lain (yang mengirim email)

Inti dari "trigger ke queue web lain": job ini di-dispatch oleh handler webhook,
lalu dijalankan oleh **worker web lain** secara async.

```php
// app/Jobs/SendDeforestoryReportEmailJob.php
namespace App\Jobs;

use App\Mail\DeforestoryLaporanPublishedMail;
use App\Models\DeforestorySubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDeforestoryReportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public array $laporan,   // payload['laporan'] (berisi translations)
        public array $case,      // payload['case']
    ) {}

    public function handle(): void
    {
        // Subscriber web lain: semua aktif, atau yang subscribe case ini.
        $subscribers = DeforestorySubscriber::active()
            ->where(fn ($q) => $q->whereNull('case_slug')->orWhere('case_slug', $this->case['slug']))
            ->get();

        foreach ($subscribers as $subscriber) {
            // Pilih bahasa sesuai locale subscriber; fallback ke 'id'.
            $locale = $subscriber->locale;
            $t = $this->laporan['translations'][$locale]
                ?? $this->laporan['translations']['id']
                ?? [];

            // Bentuk payload per-subscriber (sudah berisi title/excerpt/link locale).
            $data = [
                'title'   => $t['title']   ?? $this->laporan['title'],
                'excerpt' => $t['excerpt'] ?? $this->laporan['desc'],
                'link'    => $t['link']    ?? $this->laporan['link'],
                'image'   => $this->laporan['image'],
                'date'    => $this->laporan['date'],
                'locale'  => $locale,
            ];

            Mail::to($subscriber->email)->queue(
                new DeforestoryLaporanPublishedMail($data, $this->case, $subscriber)
            );
        }
    }
}
```

### 3e. Mailable (email ke subscriber web lain)

```php
// app/Mail/DeforestoryLaporanPublishedMail.php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeforestoryLaporanPublishedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public array $data,        // title, excerpt, link, image, date, locale
        public array $case,         // slug, category, year
        public $subscriber = null,  // utk unsubscribe link (opsional)
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->data['locale'] === 'en'
            ? 'New report: ' . ($this->data['title'] ?? 'Deforestory')
            : 'Laporan baru: ' . ($this->data['title'] ?? 'Deforestory');

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.deforestory-laporan-published',
            with: ['d' => $this->data, 'case' => $this->case, 'sub' => $this->subscriber],
        );
    }
}
```

```blade
{{-- resources/views/emails/deforestory-laporan-published.blade.php --}}
@component('mail::message')
# {{ $d['locale'] === 'en' ? 'New report' : 'Laporan baru' }}: {{ $d['title'] }}

**{{ $case['slug'] }} · {{ $d['date'] }}**

{{ $d['excerpt'] }}

@component('mail::button', ['url' => $d['link']])
{{ $d['locale'] === 'en' ? 'Read the report' : 'Baca laporan' }}
@endcomponent

@component('mail::subcopy')
{{ $d['locale'] === 'en' ? 'You receive this because you subscribed.' : 'Anda menerima email ini karena berlangganan.' }}
@if($sub) — [unsubscribe]({{ route('unsubscribe', $sub->unsubscribe_token) }}) @endif
@endcomponent
@endcomponent
```

### 3f. Controller receiver — verifikasi signature + idempotensi + dispatch job

```php
// app/Http/Controllers/DeforestoryWebhookController.php
namespace App\Http\Controllers;

use App\Jobs\SendDeforestoryReportEmailJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DeforestoryWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $secret = config('services.deforestory.webhook_secret');

        // 1) Verifikasi signature HMAC — pakai body RAW, bukan $request->all().
        //    Signature dihitung CMS dari string JSON mentah, jadi harus sama persis.
        if ($secret) {
            $signature = $request->header('X-Deforestory-Signature', '');
            $expected   = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);
            if (! hash_equals($expected, $signature)) {
                return response()->json(['message' => 'Invalid signature'], 401);
            }
        }

        $payload  = $request->input();
        $laporan  = $payload['laporan'] ?? null;
        $case     = $payload['case']    ?? null;
        if (! $laporan || ! $case) {
            return response()->json(['message' => 'Invalid payload'], 422);
        }

        // 2) Idempotensi — cegah proses dobel kalau CMS retry (tries=3).
        //    X-Deforestory-Delivery = UUID job; unik per pengiriman.
        $delivery = $request->header('X-Deforestory-Delivery');
        $lockKey  = "deforestory-webhook:{$delivery}";
        if (! Cache::add($lockKey, 1, now()->addHours(1))) {
            // Sudah pernah diproses → balas 2xx biar CMS gak retry lagi.
            return response()->json(['received' => true, 'dedup' => true]);
        }

        // 3) TRIGGER QUEUE WEB LAIN — dispatch job ke queue-nya sendiri.
        //    Handler balas 2xx segera; pengiriman email jalan async di worker.
        SendDeforestoryReportEmailJob::dispatch($laporan, $case);

        return response()->json(['received' => true, 'queued' => true], 200);
    }
}
```

### 3g. Route + CSRF exception

```php
// routes/web.php (atau api.php)
Route::post('/webhook/deforestory', [DeforestoryWebhookController::class, 'handle']);
```

Kalau ditaruh di `routes/web.php`, kecualikan dari CSRF verification:

```php
// Laravel 11: bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'webhook/deforestory',
    ]);
})

// Laravel 10: app/Http/Middleware/VerifyCsrfToken.php
protected $except = ['webhook/deforestory'];
```

### 3h. Jalanin worker web lain

Wajib. Tanpa worker, job `SendDeforestoryReportEmailJob` cuma numpuk di tabel
`jobs` dan email gak pernah terkirim.

```bash
php artisan queue:work
# Produksi: pakai supervisor biar auto-restart.
```

### 3i. Form subscribe (opsional)

Kalau web lain mau punya form public untuk pengunjung berlangganan:

```php
public function subscribe(Request $request)
{
    $data = $request->validate([
        'email'      => 'required|email',
        'locale'      => 'in:id,en',
        'case_slug'  => 'nullable|string',
    ]);
    DeforestorySubscriber::firstOrCreate(
        ['email' => $data['email']],
        [
            'case_slug' => $data['case_slug'] ?? null,
            'locale'     => $data['locale'] ?? 'id',
            'active'     => true,
        ]
    );
    return back()->with('ok', 'Berhasil berlangganan');
}
```

---

## 4. Checklist ringkas buat tim web lain

| # | Yang harus dilakuin | Kenapa |
|---|---------------------|--------|
| 1 | Sediain route POST `/webhook/deforestory`, tanpa CSRF | Endpoint penerima |
| 2 | Verifikasi `X-Deforestory-Signature` (HMAC SHA256 body raw) | Pastikan pengirim = CMS pasopati, bukan pemalsu |
| 3 | Handle `X-Deforestory-Delivery` buat idempotensi (Cache lock) | CMS `tries=3` → bisa kirim dobel; jangan proses/email 2x |
| 4 | Dispatch job ke **queue web lain** (`SendDeforestoryReportEmailJob`) | Pekerjaan berat jangan inline; balas 2xx cepat |
| 5 | Job baca `laporan.translations[locale]` per subscriber | Email sesuai bahasa subscriber (id/en) |
| 6 | Jalanin `php artisan queue:work` di server web lain | Job dijalankan async, email benar-benar terkirim |
| 7 | Set `DEFORESTORY_WEBHOOK_SECRET` sama dengan CMS | Verifikasi signature match |
| 8 | `QUEUE_CONNECTION=database`/`redis` (bukan sync) | Async; sync = handler nunggu email kelar → timeout |

---

## 5. Untuk stack lain (Node/Express, Django, dsb.)

Konsep sama, beda sintaks. Yang krusial tetap:

1. **Ambil raw body** (bukan parsed), hitung `crypto.createHmac('sha256', secret).update(rawBody).digest('hex')`, bandingkan pakai **timing-safe compare** dengan `X-Deforestory-Signature` (tanpa prefix `sha256=`).
2. **Idempotensi** pake `X-Deforestory-Delivery` (simpan UUID yg udah diproses).
3. **Balas 2xx cepat**, kerja berat (email) masuk queue/worker sendiri.
4. **Pilih bahasa** dari `laporan.translations[locale]` sesuai subscriber.
5. **Secret sama** dengan CMS.

Contoh Express (ringkas):

```js
import crypto from 'crypto';
import express from 'express';
const app = express();

// WAJIB raw body untuk verifikasi signature — jangan parse JSON dulu.
app.post('/webhook/deforestory', express.raw({ type: 'application/json' }), (req, res) => {
  const secret = process.env.DEFORESTORY_WEBHOOK_SECRET;
  const raw = req.body; // Buffer
  const expected = 'sha256=' + crypto.createHmac('sha256', secret).update(raw).digest('hex');
  const got = req.header('X-Deforestory-Signature') || '';
  if (!crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(got))) {
    return res.status(401).json({ message: 'Invalid signature' });
  }

  const payload = JSON.parse(raw.toString());
  const delivery = req.header('X-Deforestory-Delivery');

  // idempotensi (pseudo — pakai Redis/DB di produksi)
  if (alreadyProcessed(delivery)) return res.json({ received: true, dedup: true });
  markProcessed(delivery);

  // dispatch ke queue web lain (BullMQ / SQS / dsb.)
  emailQueue.add('send-report', { laporan: payload.laporan, case: payload.case });

  res.json({ received: true, queued: true });
});
```

---

## 6. Uji end-to-end

### 6a. Simulasi receiver lokal (tanpa internet)

Kita sudah pernah jalanin simulasi penuh dengan "web lain" dummy di
`127.0.0.1:9000`. Hasilnya:

```
publish laporan (CMS)
  → job masuk queue CMS
  → worker CMS POST keluar
  → web lain terima + verifikasi signature ✅
  → web lain enqueue job ke queue-nya sendiri ✅
  → worker web lain kirim email ke 4 subscriber (2 id, 2 en) ✅
```

Email subscriber `id` dapet subject "Laporan baru: ...", subscriber `en`
dapet "New report: ...". Link dan excerpt sesuai locale.

### 6b. Test ke receiver asli web lain

1. CMS set `DEFORESTORY_WEBHOOK_URL` ke endpoint receiver web lain + `DEFORESTORY_WEBHOOK_SECRET` (sama).
2. Jalanin `php artisan queue:work` di CMS.
3. Publish sebuah laporan lewat admin (status `active`, pertama kali).
4. Cek di sisi web lain: webhook masuk, job masuk queue, subscriber dapat email.
5. Kalau gagal: cek `php artisan queue:failed` di CMS (webhook non-2xx/timeout) dan di web lain (email job gagal).

### 6c. Test cepat payload + signature (debug)

Untuk ngeliat payload + header asli yang dikirim CMS tanpa setup web lain,
bisa arahkan `DEFORESTORY_WEBHOOK_URL` ke request inspector pilihan Anda
(mis. requestbin lokal / server `php -S` kecil yang catat request). Jangan
pakai layanan request-bin publik untuk data produksi.

---

## 7. Referensi file di CMS (repo ini)

| File | Peran |
|---|---|
| `app/Jobs/DeforestoryWebhookJob.php` | Job kirim webhook keluar (POST + HMAC signature + translations id/en) |
| `app/Livewire/Deforestory/DeforestoryLaporanForm.php` | Dispatch job saat `$justPublished` |
| `config/services.php` (`deforestory_api`) | Konfigurasi secret webhook keluar + API key (auth inbound sementara dimatikan) |
| `.env` | `DEFORESTORY_WEBHOOK_URL`, `DEFORESTORY_WEBHOOK_SECRET`, `DEFORESTORY_WEBHOOK_TIMEOUT`, `DEFORESTORY_API_KEY` |

---

## 8. Webhook inbound — kartu kasus (web lain → CMS)

Bagian §1–§6 di atas = webhook **keluar** (CMS POST laporan ke web lain). Bagian ini
kebalikannya: webhook **masuk**, di mana **web lain POST daftar kartu kasus ke CMS**
supaya halaman `/deforestory` (dan admin case table) langsung dapat card terbaru
tanpa CMS nge-GET ke web lain. Sebelumnya CMS nge-GET card dari web lain (mock
`/api/deforestory-cases`); mekanisme itu sudah dihapus dan diganti push ini.

### 8a. Permintaan HTTP

| Atribut | Nilai |
|---|---|
| Method | `POST` |
| URL | `https://pasopati.id/api/deforestory/cards` |
| Content-Type | `application/json` |
| Body | JSON (UTF-8) — lihat §8c |
| Autentikasi | **Sementara DIMATIKAN** untuk testing — endpoint publik (tanpa token).
  Sebelum dipakai beneran di produksi, nyalakan lagi middleware `deforestory.api`
  (Bearer token `DEFORESTORY_API_KEY`) — lihat §8b.

### 8b. Headers

| Header | Isi | Wajib? |
|---|---|---|
| `Authorization` | `Bearer <DEFORESTORY_API_KEY>` | **saat ini tidak wajib** (auth dimatikan). Normalnya wajib (token bisa juga via `?token=`) |
| `X-Deforestory-Delivery` | UUID pengiriman — **pakai untuk idempotensi** | opsional (tanpa ini, dedup dimatikan) |
| `Content-Type` | `application/json` | selalu |

### 8c. Body (JSON) — tambah / update (upsert)

Web lain POST **card yang baru/berubah**. CMS **menambah** card by slug kalau slug
baru, atau **memperbarui** kalau slug sudah ada. Card lain yang sudah tersimpan
**tetap utuh** — endpoint ini gak menghapus card yang tidak ada di payload. Kedua
locale (`id` + `en`) dikirim sekaligus dalam satu card.

Jadi web lain cukup POST satu card tiap kali ada card baru/diubah; tidak perlu
mengirim seluruh daftar setiap kali.

```json
{
  "cards": [
    {
      "slug": "mayawana",
      "category": "pulp",
      "year": "2021–2025",
      "image_id": "https://pasopati.id/storage/deforestory/mayawana-id.jpg",
      "image_en": "https://pasopati.id/storage/deforestory/mayawana-en.jpg",
      "title_id": "Mayawana: jejak deforestasi",
      "title_en": "Mayawana: deforestation trail",
      "excerpt_id": "Analisis spasial Mayawana.",
      "excerpt_en": "Spatial analysis of Mayawana.",
      "sort": 1
    },
    {
      "slug": "pulau-laut",
      "category": "sawit",
      "year": "2022–2024",
      "image_id": "https://pasopati.id/storage/deforestory/pulau-laut-id.jpg",
      "image_en": "https://pasopati.id/storage/deforestory/pulau-laut-en.jpg",
      "title_id": "Pulau Laut: sawit di balik hutan lindung",
      "title_en": "Pulau Laut: palm oil behind protected forest",
      "excerpt_id": "Pembukaan lahan sawit Pulau Laut.",
      "excerpt_en": "Palm land clearing in Pulau Laut.",
      "sort": 2
    }
  ]
}
```

Catatan:
- `slug` wajib & unik (jadi kunci upsert). Sisanya nullable.
- `sort` opsional (default 0) → dipakai urutan kartu di index.
- **`image_id` / `image_en`** — image per-locale (absolut URL `https://...`).
  Kedua locale bisa pakai file gambar yang beda. Bila salah satu kosong, konsumen
  fallback ke field `image_id`. (Kolom legacy tunggal `image` sudah dihapus.)
- Key `cards` juga menerima `data` sebagai alias (`{ "data": [...] }`).

### 8d. Ekspektasi balasan dari CMS

- Sukses → `200 {"received": true, "stored": <N>, "notified": <M>}` (N = jumlah card
  yang ditambah/diubah; M = jumlah card BARU yang memicu email subscriber).
- Token salah / hilang → saat ini **tidak error** (auth dimatikan). Normalnya `401 {"message":"Unauthorized"}`.
- Payload tidak valid (tidak ada `cards` / tidak ada slug) → `422`.
- Kirim ulang dengan `X-Deforestory-Delivery` sama → `200 {"received": true, "dedup": true}`
  (tidak diproses ulang). Gunakan UUID berbeda tiap pengiriman baru.

Catatan: **penghapusan card tidak ditangani** endpoint ini. Kalau perlu hapus card
dari CMS, tambahkan field `event=deleted` per card (atau endpoint DELETE terpisah).

### 8d-bis. Notifikasi subscriber (card BARU → email)

Tiap card **BARU** (slug belum pernah ada di DB) yang tersimpan lewat endpoint ini
memicu `DeforestoryCardNotificationJob` (queue async) → mengirim email
`DeforestoryCardMail` ke subscriber CMS aktif **type `all`**. Card yang di-update
(slug sudah ada) **tidak** memicu email (biar gak spam subscriber tiap perubahan).
Subscriber type `case` gak di-email lewat jalur ini (mereka di-email lewat jalur
laporan-publish, `DeforestoryNotificationJob`).

**Konsekuensi operasional:** job async → butuh worker `php artisan queue:work`
jalan di CMS (sama kayak webhook keluar). Tanpa worker, job numpuk di tabel `jobs`
dan email gak terkirim. Lihat §3a (worker wajib jalan).

Email `DeforestoryCardMail` pakai data card (judul/excerpt per locale, link ke
halaman kasus, link unsubscribe) — berbeda dari `DeforestoryUpdateMail` (yang
berbasis `DeforestoryCase` + dipicu saat laporan publish). Dua jalur independent.

### 8e. Contoh kirim dari web lain

**Laravel:**
```php
$key = config('services.deforestory_api.key'); // DEFORESTORY_API_KEY, sama dgn CMS

Http::withToken($key) // → Authorization: Bearer <key>
    ->withHeaders(['X-Deforestory-Delivery' => (string) Str::uuid()])
    ->post('https://pasopati.id/api/deforestory/cards', ['cards' => $cards]);
```

**Express (Node):**
```js
await fetch('https://pasopati.id/api/deforestory/cards', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${process.env.DEFORESTORY_API_KEY}`,
    'X-Deforestory-Delivery': crypto.randomUUID(),
  },
  body: JSON.stringify({ cards }),
});
```

### 8f. Setup di sisi CMS

Tidak perlu konfigurasi tambahan untuk testing — **auth sementara dimatikan**, jadi
endpoint terima POST tanpa token. Sebelum dipakai beneran di produksi, nyalakan lagi
`->middleware('deforestory.api')` di `routes/api.php`, pastikan `DEFORESTORY_API_KEY`
di `.env` CMS terisi, dan kasih nilai itu ke web lain.

Tidak perlu CSRF exception — endpoint ada di `routes/api.php` (middleware `api`,
tanpa CSRF).

### 8g. Referensi file inbound di CMS

| File | Peran |
|---|---|
| `app/Http/Controllers/Api/DeforestoryCardWebhookController.php` | Idempotensi + upsert (tambah/update, gak hapus) card by slug; dispatch notifikasi untuk card baru |
| `app/Jobs/DeforestoryCardNotificationJob.php` | Job queue: email subscriber type `all` saat card baru masuk (via `DeforestoryCardMail`) |
| `app/Mail/DeforestoryCardMail.php` | Mailable email "kasus baru" berbasis data card |
| `resources/views/emails/deforestory-card.blade.php` | Template email "kasus baru" card |
| `app/Models/DeforestoryCard.php` | Model card lokal + `toCardArray($locale)` (image per-locale `image_id`/`image_en`) |
| `app/Services/DeforestoryApiService.php` | Baca card dari tabel lokal (getCases / cardBySlug) |
| `database/migrations/2026_08_06_000001_create_deforestory_cards_table.php` | Tabel `deforestory_cards` |
| `database/migrations/2026_08_06_000003_add_image_id_en_to_deforestory_cards_table.php` | Tambah `image_id`/`image_en` per-locale ke card |
| `database/migrations/2026_08_06_000004_drop_image_from_deforestory_cards_table.php` | Hapus kolom legacy `image` dari card (sekarang per-locale) |
| `routes/api.php` | `POST /api/deforestory/cards` + `PUT|PATCH /api/deforestory/cards/{slug}` (middleware `api`; `deforestory.api` sementara dimatikan) |
---

## 9. Endpoint GET sindikasi (web lain GET data dari CMS)

Read-only endpoints buat web lain mengambil data case + laporan dari CMS. Semua di
prefix `/api/deforestory`, **wajib Bearer token** `DEFORESTORY_API_KEY` (header
`Authorization: Bearer <key>` atau `?token=<key>`), terima `?locale=id|en` (default `id`).
Diatur di `routes/api.php` group `deforestory.api` → `DeforestoryApiController`.

| Method | Endpoint | Untuk apa |
|---|---|---|
| GET | `/api/deforestory/cases` | daftar kasus aktif |
| GET | `/api/deforestory/cases/{slug}` | satu kasus + daftar laporannya |
| GET | `/api/deforestory/cases/{slug}/laporan` | daftar laporan satu kasus |
| GET | `/api/deforestory/cases/{slug}/laporan/latest` | laporan terbaru satu kasus |
| GET | `/api/deforestory/cases/{slug}/laporan/{laporanSlug}` | satu laporan (satu locale) |
| GET | `/api/deforestory/cases/{slug}/laporan/{laporanSlug}/translations` | **satu laporan + translations id & en + image per-locale** |
| GET | `/api/deforestory/queue-length` | jumlah job pending di queue CMS |

### 9a. `/translations` — laporan dua bahasa sekali GET

Balikin satu laporan lengkap dengan **translations id & en sekaligus**, termasuk
**`image` per-locale** — tiap locale bisa pakai file gambar yang beda. Shape mirror
payload webhook keluar (`DeforestoryWebhookJob`), jadi web lain bisa pakai logic yang
sama.

```http
GET /api/deforestory/cases/mayawana/laporan/jejak-deforestasi-mayawana/translations
Authorization: Bearer <DEFORESTORY_API_KEY>
```
```json
{
  "data": {
    "slug": "jejak-deforestasi-mayawana",
    "sort": 1,
    "date": "2024-11-12",
    "image": "https://.../laporans/...-id.jpg",
    "case": { "slug": "mayawana", "category": "pulp", "year": "2021–2025" },
    "translations": {
      "id": { "title": "...", "excerpt": "...", "image": "https://.../laporans/...-id.jpg", "link": "https://.../id/deforestory/mayawana/..." },
      "en": { "title": "...", "excerpt": "...", "image": "https://.../laporans/...-en.jpg", "link": "https://.../en/deforestory/mayawana/..." }
    }
  }
}
```
404 kalau case/laporan gak aktif. Catatan soal image:
- **Image per-locale** disimpan di `deforestory_laporan_translations.image` (kolom
  per locale). `translations.id.image` dan `translations.en.image` **bisa beda**.
- **Fallback** saat baca image per-locale: `translation($locale).image` →
  `laporan.image` (legacy, `deforestory_laporans.image`) → `case.featured_image`.
  Bila tidak ada image per-locale, kedua locale pakai fallback yang sama.
- Field top-level `data.image` = image `id` (backward compat).
