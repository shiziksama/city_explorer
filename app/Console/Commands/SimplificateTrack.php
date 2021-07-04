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
	public function distance($lat1, $lng1, $lat2, $lng2){
		return ceil(12745594 * asin(sqrt(
			pow(sin(deg2rad($lat2-$lat1)/2),2)
			+
			cos(deg2rad($lat1)) *
			cos(deg2rad($lat2)) *
			pow(sin(deg2rad($lng2-$lng1)/2),2)
		)));
	}
	public function simplificate_coords($coords){
		$coord_lines=[];
		$temp_line=[];
		foreach($coords as $k=>$coord){
			if($k==0){
				$temp_line[]=$coord;
				continue;
			}
			
			$d=$this->distance($coord[0],$coord[1],$coords[$k-1][0],$coords[$k-1][1]);
			if($d>1000){
				$coord_lines[]=$temp_line;
				$temp_line=[];
				
			}
			$temp_line[]=$coord;
		}
		if(!empty($temp_line)){
			$coord_lines[]=$temp_line;
		}
		if(count($coord_lines)>1){
			$new_coords=[];
			foreach($coord_lines as $k=>$coords){
				$new_coords=array_merge($new_coords,$this->simplificate_coords($coords));
			}
			return $coord_lines;
		}
		$coords=reset($coord_lines);
		$line=new \App\Models\Line();
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
		$one_line_extended = collect(RDP::RamerDouglasPeucker2d($one_line_extended->toArray(), 0.00001));
		
		return [$one_line_extended->map(function($item){
			return array_values(array_reverse($item));
		})->toArray()];
	}
    public function handle()
    {
		$simplification_version=1;
		$tracks=\App\Models\Track::where('simplification_version','<',$simplification_version)->limit(1)->get();
		$geometry=resolve('geometry');
		foreach($tracks as $track){
			var_dump($track->id);
			$line=new \App\Models\Line();
			
			$geo=($geometry->parseWkb($track->track_original)->toArray());
			if($geo['type']=='MultiLineString'){
				$coords=$geo['coordinates'];
			}else{
				$coords=[$geo['coordinates']];
			}
			$new_coords=[];
			foreach($coords as $id=>$line){
				$new_coords=array_merge($new_coords,$this->simplificate_coords($line));
			}
			//var_dumP($new_coords);
			$multiline=collect($new_coords)->map(function($item){
				return collect($item)->map(function($item2){
					//var_dump($item2);
					return $item2[0].' '.$item2[1];
				})->implode(',');
			})->toArray();
			$wkt='MultiLineString(('.implode('),(',$multiline).'))';
		
			$w=$geometry->parseWkt($wkt);
			$track->track_simple=$w->toWkb();
			$track->simplification_version=$simplification_version;
			$track->save();
			var_dump('simplificated');
		}
        return 0;
    }
}
