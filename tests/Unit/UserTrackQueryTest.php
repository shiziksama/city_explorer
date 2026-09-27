<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTrackQueryTest extends TestCase
{
    public function test_it_only_fetches_tracks_intersecting_the_requested_bounds(): void
    {
        $user = new User;
        $user->id = 42;

        $queries = DB::connection()->pretend(
            fn () => $user->getTracks(50.0, 30.0, 51.0, 31.0),
        );

        $this->assertCount(1, $queries);
        $query = $queries[0];

        $this->assertStringContainsString('select "id", "track_simple_geo" from "tracks"', $query['query']);
        $this->assertStringContainsString('ST_INTERSECTS("track_simple_geo", ST_GeomFromText(\'POLYGON((30 50, 31 50, 31 51, 30 51, 30 50))\', 4326))', $query['query']);
        $this->assertSame([42], $query['bindings']);
    }
}
