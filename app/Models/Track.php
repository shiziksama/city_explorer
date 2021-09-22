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
		$arr=($geo->parseWkb($this->track_simple)->toArray());
		if($arr['type']=='MultiLineString'){
			$points=reset($arr['coordinates']);
		}else{
			$points=$arr['coordinates'];
		}

		//$points=$geo->parseWkb($this->track_simple)->toArray()['coordinates'];
		
		return (float)collect($points)->pluck(0)->filter()->min().','.collect($points)->pluck(1)->filter()->min();
	}
	public function get_jsarr(){
		ini_set( 'serialize_precision', -1 );
		$geometry=resolve('geometry');
		$r=($geometry->parseWkb($this->track_simple)->toArray()['coordinates']);
		return json_encode($r);
	}
	public function get_original_tracks(){
		$geometry=resolve('geometry');
		$s=$geometry->parseWkb($this->track_original)->toArray();
		if($s['type']=='LineString'){
			return [$s['coordinates']];
		}
		if($s['type']=='MultiLineString'){
			return $s['coordinates'];
		}
	}
	public function get_tracks(){
		$geometry=resolve('geometry');
		//var_dump($this->id);
		//var_dump($this->track_simple);
		$s=$geometry->parseWkb($this->track_simple)->toArray();
		if($s['type']=='LineString'){
			return [$s['coordinates']];
		}
		if($s['type']=='MultiLineString'){
			return $s['coordinates'];
		}
		var_dump($s);
		die();
	}
}
