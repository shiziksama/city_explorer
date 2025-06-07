<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Track;
use davidredgar\polyline\RDP;

class SimplificationController extends Controller
{
    public function get(Request $request){
                abort(404);//Раскомментировать только для тестирования
                $track_id = $request->input('track_id', 1);
                $track=Track::findOrFail($track_id);
                $next_id=Track::where('id','>',$track_id)->orderBy('id','asc')->first();
                if(empty($next_id)){
                        $next_id=Track::orderBy('id','asc')->first();
                }
                $next_id=$next_id->id;
		
		$geometry=resolve('geometry');
		$coords=collect($geometry->parseWkb($track->track_original)->toArray()['coordinates']);
		
		
                $offset = $request->input('offset', 0);
                $limit = $request->input('limit');
                if(is_null($limit)){
                        $limit = $coords->count() - $offset;
                }
                if($limit>$coords->count()-$offset){
                        $limit=$coords->count()-$offset;
                }
		$coords=$coords->slice($offset,$limit);
		$for_replace=$coords->values();
		$line=new \App\Models\Line();
		$line->create($coords);
		$line->populate_coords();
		$lines=$line->getLines();
		
		
                //$one_line=clone($lines);
                $one_line=$line->getOneLine();
                $one_line_length=$one_line->count();
                //Log::debug($one_line);
                Log::debug('One line length', ['length' => $one_line_length]);
                //$one_line = collect(RDP::RamerDouglasPeucker2d($one_line->toArray(), 0.00001));
                Log::debug('One line count', ['count' => $one_line->count()]);
                //var_dump($one_line);
		
                //$one_line_extended=collect([]);
                $one_line_extended=collect(\App\Models\OsmApi::route($one_line))->map(function($item){
                        return array_reverse($item);
                });
                Log::debug('Extended line count', ['count' => $one_line_extended->count()]);
		
                $one_line_extended = collect(RDP::RamerDouglasPeucker2d($one_line_extended->toArray(), 0.00001));
                Log::debug('Extended line simplified count', ['count' => $one_line_extended->count()]);
		
		
		$one_line=$one_line->map(function($item){
			return array_reverse($item);
		});
		
		
		$another_simplification=clone($for_replace);
		$another_simplification=$another_simplification->slice(0,$one_line_length-2);
		//var_dump($another_simplification);\
		//$mapbox_match=\App\Models\OsmApi::maptching($another_simplification);
		$mapbox_match=[];
		return view('trackfix',['track_id'=>$track_id,'offset'=>$offset,'limit'=>$limit,'coords'=>$coords,'lines'=>$lines,'one_line'=>$one_line,'one_line_length'=>$one_line_length,'for_replace'=>$for_replace,'next_id'=>$next_id,'mapbox_match'=>$mapbox_match,'one_line_extended'=>$one_line_extended]);
	}
}
