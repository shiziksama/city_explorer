<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function mymap($slug){
		$user=\App\Models\User::where('slug',$slug)->firstOrFail();
                $tracks=\App\Models\Track::where('uid',$user->id)->get();

                return view('track_area',['tracks'=>$tracks,'user'=>$user]);
	}
	public function singletrack($id){
		//return view('track_area');
		$track=\App\Models\Track::findOrFail($id);
        $next=\App\Models\Track::where('id','>',$id)->orderBy('id','asc')->first();
		$tracks=collect([$track]);
		$user=\App\Models\User::where('slug','shiziksama')->firstOrFail();
		return view('track_area',['tracks'=>$tracks,'user'=>$user,'next'=>$next->id??null]);
	}
}
