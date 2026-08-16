<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Services\GeoService;
use Illuminate\Console\Command;
use MatanYadaev\EloquentSpatial\Enums\Srid;
use MatanYadaev\EloquentSpatial\Objects\MultiLineString;

class MigrateTracksToSpatial extends Command
{
    protected $signature = 'tracks:migrate-spatial {--dry-run}';

    protected $description = 'Convert legacy track coordinates to spatial columns';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $geometry = resolve('geometry');
        $tracks = Track::whereNull('track_original_geo')
            ->whereNull('track_simple_geo')
            ->get();

        foreach ($tracks as $track) {
            $original = $geometry->parseWkb($track->track_original)->toJson();
            $simple = $geometry->parseWkb($track->track_simple)->toJson();

            $track->track_original_geo = MultiLineString::fromJson(
                GeoService::oldformatToMultiline($original), Srid::WGS84,
            );
            $track->track_simple_geo = MultiLineString::fromJson(
                GeoService::oldformatToMultiline($simple), Srid::WGS84,
            );

            if (! $dryRun) {
                $track->save();
            }
        }

        $this->info(($dryRun ? 'Validated' : 'Migrated')." tracks: {$tracks->count()}");

        return self::SUCCESS;
    }
}
