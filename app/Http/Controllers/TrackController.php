<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function mymap($slug){
		$user=\App\Models\User::where('slug',$slug)->firstOrFail();
		//$tracks=\App\Models\Track::where('uid',$user->id)->get();
		
		
		$tracks=collect([]);
		
		$track=new \App\Models\Track();
		
		$trackpoints=\App\Models\Curpoint::orderBy('timestamp','asc')
		->where('horizontal_accuracy','<',500)
		->where('mid',115)
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
		
		
		
		return view('track_area',['tracks'=>$tracks,'user'=>$user]);
	}
	public function singletrack($id){
		//return view('track_area');
		$track=\App\Models\Track::findOrFail($id);
		$tracks=collect([$track]);
		$user=\App\Models\User::where('slug','shiziksama')->firstOrFail();
		return view('track_area',['tracks'=>$tracks,'user'=>$user]);
	}
}
