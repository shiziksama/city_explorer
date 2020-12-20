<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapRendererController extends Controller
{
	public function point_between($point,$lat_from,$lat_to,$lng_from,$lng_to){
		//var_dump($point);
		//var_dump($point[0]>$lat_from&&$point[0]<$lat_to);
		//var_dump($point[1]>$lng_from&&$point[1]<$lng_to);
		
		return $point[0]>$lat_from&&$point[0]<$lat_to&&$point[1]>$lng_from&&$point[1]<$lng_to;
	}
	public function computeOutCode($point,$lat_from,$lat_to,$lng_from,$lng_to){
		$result=0;
		if($point[0]<$lat_from){
			$result = $result |1;
		}
		if($point[0]>$lat_to){
			$result = $result |2;
		}
		if($point[1]<$lng_from){
			$result = $result |4;
		}
		if($point[1]>$lng_to){
			$result = $result |8;
		}
		return $result;
	}
	public function get_tracks($tracks,$lat_from,$lat_to,$lng_from,$lng_to){
		$new_tracks=collect([]);
		foreach($tracks as $track){
			$points_numbers=[];
			foreach($track as $k=>$point){
				$points_numbers[$k]=$this->computeOutCode($point,$lat_from,$lat_to,$lng_from,$lng_to);
			}
			$new_track=collect([]);
			//var_dump($points_numbers);
			foreach($points_numbers as $k=>$number){
				if($k==0)continue;
				
				
				if(($number & $points_numbers[$k-1])==0||$number==0||$points_numbers[$k-1]==0){ //Значит эта линия пересекает.
					$new_track->push($track[$k-1]);
					if($k==count($points_numbers)-1){
						$new_track->push($track[$k]);
						$new_tracks->push($new_track);
						$new_track=collect([]);//нужно занулить, чтобы он не добавился после списка
					}
					continue;
				}
				if(($number & $points_numbers[$k])!==0){ //значит не пересекает. Хватит. добавляем предыдущую?
					if($new_track->isNotEmpty()){
						$new_track->push($track[$k-1]);
						$new_tracks->push($new_track);
						$new_track=collect([]);
					}
					continue;
				}
				
				
			}
			if($new_track->isNotEmpty()){
				$new_tracks->push($new_track);
			}
			//var_dump($new_tracks);
			return $new_tracks;
			//var_dump($points_numbers);
		}
	}
    public function user_overlay($uid,$zoom,$x,$y){
		$tracks = \Cache::remember('tracks'.$uid, 3600, function () {
			return \App\Models\Track::where('uid',1)->get();
		});
		//$tracks=\App\Models\Track::where('uid',$uid)->get();
		$geo=resolve('geometry');
		$lines=collect([]);
		$items_count=pow(2,$zoom);
		
		$lng_deg_per_item=360/$items_count;
		$lng_from=-180+$x*$lng_deg_per_item;
		$lng_to=-180+($x+1)*$lng_deg_per_item;
		
		$lat_deg_per_item=(85.0511*2)/$items_count;
		$lat_to=rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
		$lat_from=rad2deg(atan(sinh(pi() * (1 - 2 * ($y+1) / $items_count))));
		//var_dump($lng_from,$lng_to);
		//var_dump($lat_from,$lat_to);
		$super_tracks=collect([]);
		foreach($tracks as $k=>$track){
			$line=$geo->parseWkb($track->track_simple)->toArray()['coordinates'];
			$result_tracks=$this->get_tracks([$line],$lat_from,$lat_to,$lng_from,$lng_to);
			//var_dump($result_tracks);
			$super_tracks=$super_tracks->merge($result_tracks);
			//var_dump($result_tracks->toArray());
			//break;
		}
		//var_dump($super_tracks);
		$lines=$super_tracks->map(function($item)use($lng_from,$lat_from,$lng_to,$lat_to){
			$item=$item->toArray();
			$item=array_map(function($item)use($lng_from,$lat_from,$lng_to,$lat_to){
				//var_dump($item);
				$l['y']=512-round(($item[0]-$lat_from)*512/($lat_to-$lat_from));
				$l['x']=round(($item[1]-$lng_from)*512/($lng_to-$lng_from));
				return $l;
			},$item);
		//var_dump($item);
			return $item;
			
		});
		//var_dump($lines);
		//die();
		$map = new \Imagick();
		$map->newImage(512, 512,new \ImagickPixel('transparent'));
		//$map->setBackgroundColor();
		$map->setImageFormat("png");
		$draw = new \ImagickDraw();
		//$draw->setFillAlpha(0);
		$draw->setStrokeColor(new \ImagickPixel('rgba(255, 0, 0, 0.5)'));
		$draw->setStrokeWidth(20);
		$draw->setStrokeLineCap(\Imagick::LINECAP_BUTT);// КОнец линии делает квадратным, потому что другой конец все портит
		$draw->setStrokeLineJoin(\Imagick::LINEJOIN_ROUND);// склейку в полилиниях деляем скругленной по фану.
		$draw->setFillColor(new \ImagickPixel('transparent'));
		
		//$lines = array_chunk($lines[4], ceil(count($lines[4]) / 3));
		
		foreach($lines as $k=>$line){
			$draw->polyline (array_merge($line,array_reverse($line)));// линия идет в обе стороны, чтобы не было даже возможности нарисовать область внутри
			//break;
		}
		$map->drawImage($draw);
		return response($map->getImageBlob())->header('Content-type','image/png');
		return $map->getImageBlob();
		//var_dump($lines);
		//var_dump('some');
	}
}
