<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;
	public $timestamps = false;

	public function getpoint(){
		$geo=resolve('geometry');
		$points=$geo->parseWkb($this->track_simple)->toArray()['coordinates'];
		return (float)collect($points)->pluck(0)->filter()->min().','.collect($points)->pluck(1)->filter()->min();
	}
	public function get_jsarr(){
		ini_set( 'serialize_precision', -1 );
		$geometry=resolve('geometry');
		$r=($geometry->parseWkb($this->track_simple)->toArray()['coordinates']);
		return json_encode($r);
	}
}
