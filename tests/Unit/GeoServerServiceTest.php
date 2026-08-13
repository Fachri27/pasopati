<?php

namespace Tests\Unit;

use App\Services\GeoServerService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class GeoServerServiceTest extends TestCase
{
    protected GeoServerService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new GeoServerService;
    }

    public function test_search_returns_empty_array_for_blank_query(): void
    {
        $this->assertSame([], $this->service->searchLocations(''));
        $this->assertSame([], $this->service->searchLocations('   '));
    }

    public function test_search_requires_minimum_two_characters(): void
    {
        $this->assertSame([], $this->service->searchLocations('b'));
    }

    public function test_search_returns_empty_when_table_is_not_configured(): void
    {
        config(['services.geoserver.table' => null]);

        $this->assertSame([], $this->service->searchLocations('bandung'));
    }

    public function test_get_location_returns_null_when_table_is_not_configured(): void
    {
        config(['services.geoserver.table' => null]);

        $this->assertNull($this->service->getLocation(1));
    }

    public function test_search_degrades_gracefully_when_geo_database_is_down(): void
    {
        DB::shouldReceive('connection')->andThrow(new RuntimeException('geo db down'));

        $this->assertSame([], $this->service->searchLocations('bandung'));
    }

    public function test_get_location_degrades_gracefully_when_geo_database_is_down(): void
    {
        DB::shouldReceive('connection')->andThrow(new RuntimeException('geo db down'));

        $this->assertNull($this->service->getLocation(66068));
    }
}
