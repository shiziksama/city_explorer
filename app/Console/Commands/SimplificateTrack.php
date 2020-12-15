<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use davidredgar\polyline\RDP;

class SimplificateTrack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:simplificate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		$simplification_version=1;
		$tracks=\App\Models\Track::where('simplification_version','<',$simplification_version)->get();
		$geometry=resolve('geometry');
		foreach($tracks as $track){
			$line=new \App\Models\Line();
			$coords=$geometry->parseWkb($track->track_original)->toArray()['coordinates'];
			//var_dump($coords);
			$line->create($coords);
			$line->populate_coords();
			$lines=$line->getLines();
			$one_line=$line->getOneLine();
			$coords=array_slice($coords,$one_line->count());
			$one_line_extended=collect(\App\Models\OsmApi::route($one_line->toArray()))->map(function($item){
				return array_reverse($item);
			});
			$one_line_extended=$one_line_extended->merge($coords)->map(function($item){
				return array_reverse($item);
			});
			//var_dump($one_line_extended);
			$one_line_extended = collect(RDP::RamerDouglasPeucker2d($one_line_extended->toArray(), 0.00001));
			$wkt=$one_line_extended->map(function($item){
				return $item[1].' '.$item[0];
			})->implode(',');
			$wkt='LINESTRING('.$wkt.')';
			$w=$geometry->parseWkt($wkt);
			//$track->track_original=$w->toWkb();
			$track->track_simple=$w->toWkb();
			$track->simplification_version=$simplification_version;
			$track->save();
		}
        return 0;
    }
}
