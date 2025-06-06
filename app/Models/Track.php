<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    use HasFactory;
	public $timestamps = false;
    public function distance($lat1, $lng1, $lat2, $lng2){
		return ceil(12745594 * asin(sqrt(
			pow(sin(deg2rad($lat2-$lat1)/2),2)
			+
			cos(deg2rad($lat1)) *
			cos(deg2rad($lat2)) *
			pow(sin(deg2rad($lng2-$lng1)/2),2)
		)));
	}
    public function parse_line($coords){
        $coord_lines=[];
		$temp_line=[];
		foreach($coords as $k=>$coord){
			if($k==0){
				$temp_line[]=$coord;
				continue;
			}
			
			$d=$this->distance($coord[0],$coord[1],$coords[$k-1][0],$coords[$k-1][1]);
			if($d>200){
                if(count($temp_line)>1){
                    $coord_lines[]=$temp_line;
                }
				$temp_line=[];
				
			}
			$temp_line[]=$coord;
		}
		if(!empty($temp_line)&&count($temp_line)>1){
			$coord_lines[]=$temp_line;
		}
        return $coord_lines; 
    }
    public function remove_big_lines(){
        $geometry=resolve('geometry');
        $arr=($geometry->parseWkb($this->track_simple)->toArray());
        $lines=[];
        if($arr['type']=='MultiLineString'){
            foreach($arr['coordinates'] as $line){
                $lines=array_merge($lines,$this->parse_line($line));
            }
        }
        $arr['coordinates']=$lines;
        $w=$geometry->parseGeoJson(json_encode($arr));
        $this->track_simple=$w->toWkb();
    }
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
