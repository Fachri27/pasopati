<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Komunikasi dengan GeoServer untuk pencarian lokasi.
 *
 * Data lokasi dibaca LANGSUNG dari database PostGIS (koneksi `geo`) yang juga
 * menjadi sumber layer WFS di GeoServer — bukan lewat HTTP WFS. Keuntungannya:
 * data identik, tidak bergantung pada uptime/URL GeoServer, kredensial hanya
 * ada di .env (DB_*_GEO), dan bisa pakai PostGIS (ST_AsGeoJSON) secara native.
 *
 * Query setara dengan:
 *   DB::connection('geo')
 *       ->table('proteus."POLITICAL_LEVEL_6_dissolved"')
 *       ->select('NAME', 'latitude', 'longtitude')
 *       ->where('NAME', 'ILIKE', '%q%')
 *       ->limit(20)
 *       ->get();
 *
 * Seluruh kegagalan (DB mati, tabel hilang, koneksi timeout, dll) ditangkap di
 * sini dan dikembalikan sebagai hasil kosong — aplikasi tidak pernah crash.
 */
class GeoServerService
{
    /**
     * Baca konfigurasi setiap kali dipanggil (bukan snapshot di constructor)
     * supaya perubahan config runtime (mis. saat test) langsung berefek.
     *
     * @return array<string, mixed>
     */
    protected function config(): array
    {
        return config('services.geoserver', []);
    }

    /**
     * Cari lokasi dari layer PostGIS berdasarkan teks pencarian.
     *
     * @return array<int, array{id: int|string, name: string, latitude: float, longitude: float}>
     */
    public function searchLocations(string $query, ?int $limit = null): array
    {
        $query = trim($query);
        $table = $this->config()['table'] ?? null;

        if ($table === '' || $table === null || $query === '' || mb_strlen($query) < 2) {
            return [];
        }

        $limit = $limit ?? (int) ($this->config()['limit'] ?? 20);
        $searchColumns = array_values(array_filter((array) ($this->config()['search_properties'] ?? [])));
        $searchColumns = $searchColumns !== [] ? $searchColumns : [$this->nameColumn()];

        try {
            $rows = DB::connection('geo')
                ->table(DB::raw($this->quoteTable($table)))
                ->selectRaw($this->selectForList())
                ->where(function ($queryBuilder) use ($query, $searchColumns) {
                    foreach ($searchColumns as $column) {
                        $queryBuilder->orWhere($column, 'ilike', "%{$query}%");
                    }
                })
                ->orderBy($this->nameColumn())
                ->limit($limit)
                ->get();

            return $rows->map(fn (object $row) => $this->mapRow($row, false))->all();
        } catch (Throwable $e) {
            Log::warning('GeoServerService::searchLocations gagal.', [
                'query' => $query,
                'table' => $table,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Ambil satu lokasi lengkap (termasuk geometry GeoJSON) berdasarkan id.
     */
    public function getLocation(int|string $id): ?array
    {
        $table = $this->config()['table'] ?? null;

        if ($table === '' || $table === null) {
            return null;
        }

        try {
            $row = DB::connection('geo')
                ->table(DB::raw($this->quoteTable($table)))
                ->selectRaw($this->selectForDetail())
                ->where('id', $id)
                ->first();

            return $row ? $this->mapRow($row, true) : null;
        } catch (Throwable $e) {
            Log::warning('GeoServerService::getLocation gagal.', [
                'id' => $id,
                'table' => $table,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @return array{id: int|string, name: string, latitude: float, longitude: float, geometry?: array|null}
     */
    protected function mapRow(object $row, bool $withGeometry): array
    {
        $name = trim((string) $row->name);
        $label = isset($row->label) ? trim((string) $row->label) : '';

        if ($label !== '' && mb_strpos($name, $label) === false) {
            $name = trim($name.', '.$label);
        }

        $result = [
            'id' => $row->id,
            'name' => $name,
            'latitude' => round((float) $row->latitude, 7),
            'longitude' => round((float) $row->longitude, 7),
        ];

        if ($withGeometry) {
            $result['geometry'] = $row->geometry
                ? json_decode($row->geometry, true)
                : null;
        }

        return $result;
    }

    protected function selectForList(): string
    {
        $parts = [
            'id',
            $this->quote($this->nameColumn()).' AS "name"',
        ];

        if ($this->labelColumn() !== null) {
            $parts[] = $this->quote($this->labelColumn()).' AS "label"';
        }

        if ($this->latitudeColumn() && $this->longitudeColumn()) {
            $parts[] = $this->quote($this->latitudeColumn()).' AS latitude';
            $parts[] = $this->quote($this->longitudeColumn()).' AS longitude';
        } else {
            $centroid = 'ST_Centroid(ST_Transform('.$this->quote($this->geometryColumn()).', '.$this->srid().'))';
            $parts[] = 'ST_Y('.$centroid.') AS latitude';
            $parts[] = 'ST_X('.$centroid.') AS longitude';
        }

        return implode(', ', $parts);
    }

    protected function selectForDetail(): string
    {
        return $this->selectForList()
            .', ST_AsGeoJSON(ST_Transform('.$this->quote($this->geometryColumn()).', '.$this->srid().')) AS geometry';
    }

    protected function nameColumn(): string
    {
        return (string) ($this->config()['name_property'] ?? 'name');
    }

    protected function labelColumn(): ?string
    {
        $label = (string) ($this->config()['label_property'] ?? '');

        return $label !== '' ? $label : null;
    }

    protected function latitudeColumn(): ?string
    {
        $column = (string) ($this->config()['latitude_column'] ?? '');

        return $column !== '' ? $column : null;
    }

    protected function longitudeColumn(): ?string
    {
        $column = (string) ($this->config()['longitude_column'] ?? '');

        return $column !== '' ? $column : null;
    }

    protected function geometryColumn(): string
    {
        return (string) ($this->config()['geometry_column'] ?? 'geom');
    }

    protected function srid(): int
    {
        return (int) ($this->config()['geometry_srid'] ?? 4326);
    }

    protected function quote(string $identifier): string
    {
        return '"'.str_replace('"', '', $identifier).'"';
    }

    protected function quoteTable(string $table): string
    {
        return implode('.', array_map(
            fn (string $part) => $this->quote($part),
            explode('.', $table)
        ));
    }
}
