<?php

namespace Tests\Unit;

use App\Http\Controllers\MapRendererController;
use App\Models\Track;
use App\Support\Tile;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
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

    public function test_it_returns_the_typed_simple_spatial_geometry(): void
    {
        $geometry = MultiLineString::fromJson(self::GEOJSON, Srid::WGS84);
        $track = new Track;
        $track->track_simple_geo = $geometry;

        $result = $track->get_tracks();

        $this->assertInstanceOf(MultiLineString::class, $result);
        $this->assertSame($geometry, $result);
        $this->assertSame(json_decode(self::GEOJSON, true), $result->toArray());
    }

    public function test_renderer_segments_keep_spatial_point_types(): void
    {
        $geometry = MultiLineString::fromJson(self::GEOJSON, Srid::WGS84);

        $segments = (new MapRendererController)->extractVisibleSegments($geometry, new Tile(1, 1, 0));

        $this->assertCount(1, $segments);
        $this->assertInstanceOf(LineString::class, $segments->first());
        $this->assertContainsOnlyInstancesOf(Point::class, $segments->first()->getGeometries());
    }
}
