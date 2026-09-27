<?php

namespace Tests\Unit;

use App\Models\Track;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;
use Tests\TestCase;

class TrackSpatialCastTest extends TestCase
{
    private const GEOJSON = '{"type":"MultiLineString","coordinates":[[[30.5,50.4],[30.6,50.5]]]}';

    public function test_it_casts_geojson_for_spatial_storage(): void
    {
        $geometry = MultiLineString::fromJson(self::GEOJSON, Srid::WGS84);
        $track = new Track;

        $track->track_original_geo = $geometry;
        $track->track_simple_geo = $geometry;

        $this->assertInstanceOf(MultiLineString::class, $track->track_original_geo);
        $this->assertInstanceOf(MultiLineString::class, $track->track_simple_geo);
        $this->assertSame(4326, $track->track_original_geo->srid);
        $this->assertSame(json_decode(self::GEOJSON, true), $track->track_original_geo->toArray());
    }

    public function test_it_hydrates_mysql_spatial_values_without_extra_queries(): void
    {
        $geometry = MultiLineString::fromJson(self::GEOJSON, Srid::WGS84);
        $track = (new Track)->newFromBuilder([
            'id' => 1,
            'track_original_geo' => $geometry->toWkb(),
            'track_simple_geo' => $geometry->toWkb(),
        ]);

        $this->assertInstanceOf(MultiLineString::class, $track->track_original_geo);
        $this->assertSame(4326, $track->track_original_geo->srid);
        $this->assertFalse($track->isDirty());
    }

    public function test_it_returns_simple_spatial_track_in_renderer_coordinate_order(): void
    {
        $track = new Track;
        $track->track_simple_geo = MultiLineString::fromJson(self::GEOJSON, Srid::WGS84);

        $this->assertSame(
            [[[50.4, 30.5], [50.5, 30.6]]],
            $track->get_tracks(),
        );
    }
}
