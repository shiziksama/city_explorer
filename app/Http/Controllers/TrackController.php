<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrackController extends Controller
{
    public function mymap($slug){
		$user=\App\Models\User::where('slug',$slug)->firstOrFail();
		$tracks=\App\Models\Track::where('uid',$user->id)->get();
		return view('track_area',['tracks'=>$tracks]);
	}
}
