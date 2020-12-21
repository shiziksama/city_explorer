<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function mymap($slug){
		$user=\App\Models\User::where('slug',$slug)->firstOrFail();
		$tracks=\App\Models\Track::where('uid',$user->id)->get();
		
		
		$tracks=collect([]);
		
		$track=new \App\Models\Track();
		
		$trackpoints=\App\Models\Curpoint::orderBy('timestamp','asc')->where('mid',67)
		->where('horizontal_accuracy','<',100)
		->get();
		$wkt=$trackpoints->map(function($item){
			return $item->lat.' '.$item->lng;
		})->implode(',');
		if(!empty($wkt)){
			$wkt='LINESTRING('.$wkt.')';
			$geometry=resolve('geometry');
			$w=$geometry->parseWkt($wkt);
			$track->track_original=$w->toWkb();
			$track->track_simple=$w->toWkb();
			$tracks->push($track);
		}
		
		
		
		return view('track_area',['tracks'=>$tracks]);
	}
	public function singletrack($id){
		$track=\App\Models\Track::findOrFail($id);
		$tracks=collect([$track]);
		return view('track_area',['tracks'=>$tracks]);
	}
}
