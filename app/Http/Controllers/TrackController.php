<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Models\User;

class TrackController extends Controller
{
    public function mymap($slug)
    {
        $user = User::where('slug', $slug)->firstOrFail();
        $tracks = Track::where('uid', $user->id)->get();

        return view('track_area', ['tracks' => $tracks, 'user' => $user]);
    }

    public function singletrack($id)
    {
        // return view('track_area');
        $track = Track::findOrFail($id);
        $next = Track::where('id', '>', $id)->orderBy('id', 'asc')->first();
        $tracks = collect([$track]);
        $user = User::where('slug', 'shiziksama')->firstOrFail();

        return view('track_area', ['tracks' => $tracks, 'user' => $user, 'next' => $next->id ?? null]);
    }
}
