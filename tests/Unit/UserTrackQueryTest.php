<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Tile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTrackQueryTest extends TestCase
{
    public function test_it_only_fetches_tracks_intersecting_the_requested_bounds(): void
    {
        $user = new User;
        $user->id = 42;
        $tile = new Tile(1, 1, 0);

        $queries = DB::connection()->pretend(
            fn () => $user->getTracks($tile),
        );

        $this->assertCount(1, $queries);
        $query = $queries[0];

        $this->assertStringContainsString('select "id", "track_simple_geo" from "tracks"', $query['query']);
        $this->assertStringContainsString('ST_INTERSECTS("track_simple_geo", ST_GeomFromText(\'POLYGON((0 0, 180 0, 180 85.051128779807, 0 85.051128779807, 0 0))\', 4326))', $query['query']);
        $this->assertSame([42], $query['bindings']);
    }
}
