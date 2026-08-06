<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dokumen Petisi - {{ $title }}</title>
    <style>
        @page {
            margin: 20mm 15mm 25mm 15mm;
            footer: page-footer;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #333;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #1a5276;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header img {
            max-height: 60px;
            margin-bottom: 8px;
        }

        .header h1 {
            font-size: 16pt;
            color: #1a5276;
            margin: 5px 0;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header .subtitle {
            font-size: 9pt;
            color: #777;
        }

        .section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 12pt;
            font-weight: bold;
            color: #1a5276;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }

        .petition-title {
            font-size: 14pt;
            font-weight: bold;
            color: #222;
            margin-bottom: 5px;
        }

        .target {
            font-size: 11pt;
            color: #555;
            margin-bottom: 10px;
        }

        .target strong {
            color: #222;
        }

        .summary-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
            margin: 12px 0;
        }

        .summary-item {
            flex: 1;
            min-width: 120px;
            background: #f0f4f8;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #dce4ec;
        }

        .summary-item:last-child {
            border-right: none;
        }

        .summary-item .value {
            font-size: 16pt;
            font-weight: bold;
            color: #1a5276;
        }

        .summary-item .label {
            font-size: 7.5pt;
            color: #777;
            text-transform: uppercase;
        }

        .demands-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .demands-list li {
            counter-increment: demand-counter;
            padding: 4px 0 4px 28px;
            position: relative;
        }

        .demands-list li::before {
            content: counter(demand-counter) ".";
            position: absolute;
            left: 0;
            font-weight: bold;
            color: #1a5276;
        }

        .description-text {
            text-align: justify;
            color: #444;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 8px;
        }

        .signatures-table th {
            background: #1a5276;
            color: #fff;
            padding: 6px 8px;
            text-align: left;
            font-weight: bold;
        }

        .signatures-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #eee;
        }

        .signatures-table tr:nth-child(even) td {
            background: #f9f9f9;
        }

        .no-signatures-note {
            background: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 12px;
            text-align: center;
            font-size: 9pt;
            color: #856404;
            margin-top: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        .footer-note {
            text-align: center;
            font-size: 7pt;
            color: #aaa;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="{{ public_path('img/logo_pasopati.png') }}" alt="Logo Pasopati">
        <h1>Dokumen Petisi</h1>
        <div class="subtitle">Pasopati.id &mdash; Suara untuk Perubahan</div>
    </div>

    <div class="section">
        <div class="petition-title">{{ $title }}</div>
        <div class="target"><strong>Ditujukan kepada:</strong> {{ $petition->target_name }}</div>
    </div>

    @if ($description)
    <div class="section">
        <div class="section-title">Latar Belakang</div>
        <div class="description-text">{{ Str::limit($description, 1000) }}</div>
    </div>
    @endif

    @if (count($demands) > 0)
    <div class="section">
        <div class="section-title">Tuntutan</div>
        <ol class="demands-list" style="counter-reset: demand-counter;">
            @foreach ($demands as $demand)
                <li>{{ $demand }}</li>
            @endforeach
        </ol>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Ringkasan Statistik</div>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="value">{{ number_format($verifiedCount) }}</div>
                <div class="label">Penandatangan Terverifikasi</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $petition->goal_count > 0 ? number_format($petition->goal_count) : '&infin;' }}</div>
                <div class="label">Target</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $petition->progressPercent() }}%</div>
                <div class="label">Progress</div>
            </div>
        </div>
        <table style="width:100%; margin-top:10px; font-size:9pt;">
            <tr>
                <td style="color:#666; width:180px;"><strong>Tanggal Petisi Dibuat</strong></td>
                <td>{{ $petitionDate }}</td>
            </tr>
            <tr>
                <td style="color:#666;"><strong>Tanggal Export</strong></td>
                <td>{{ $exportDate }}</td>
            </tr>
            <tr>
                <td style="color:#666;"><strong>Status</strong></td>
                <td>{{ ucfirst($petition->status) }}</td>
            </tr>
        </table>
    </div>

    @if ($includeSignatures && $signatureChunks->isNotEmpty())
    <div class="section">
        <div class="section-title">Daftar Penandatangan ({{ number_format($verifiedCount) }})</div>

        <table class="signatures-table">
            <thead>
                <tr>
                    <th style="width:40px;">No</th>
                    <th>Nama</th>
                    <th style="width:120px;">Kota</th>
                    <th style="width:120px;">Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @php $no = 1; @endphp
                @foreach ($signatureChunks as $signature)
                    <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $signature->name }}</td>
                        <td>{{ $signature->city ?? '-' }}</td>
                        <td>{{ $signature->created_at ? $signature->created_at->locale('id')->isoFormat('D MMM YYYY') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @elseif (!$includeSignatures && $verifiedCount > 5000)
    <div class="section">
        <div class="section-title">Daftar Penandatangan</div>
        <div class="no-signatures-note">
            Petisi ini memiliki <strong>{{ number_format($verifiedCount) }}</strong> penandatangan terverifikasi.
            Jumlah terlalu besar untuk ditampilkan dalam dokumen ini.<br>
            Untuk mendapatkan data lengkap, gunakan fitur export CSV di halaman admin petisi.
        </div>
    </div>
    @elseif ($verifiedCount === 0)
    <div class="section">
        <div class="section-title">Daftar Penandatangan</div>
        <div class="no-signatures-note">Belum ada penandatangan terverifikasi untuk petisi ini.</div>
    </div>
    @endif

    <div class="footer-note">
        Dokumen ini digenerate secara otomatis dari pasopati.id pada {{ $exportDate }}
    </div>

</body>
</html>
