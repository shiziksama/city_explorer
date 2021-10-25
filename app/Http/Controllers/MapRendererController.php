<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MapRendererController extends Controller
{
	public function point_between($point,$lat_from,$lat_to,$lng_from,$lng_to){
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
		$tracks=array_values($tracks);
		//var_dump($tracks);
		foreach($tracks as $track){
			$points_numbers=[];
			foreach($track as $k=>$point){
				$points_numbers[$k]=$this->computeOutCode($point,$lat_from,$lat_to,$lng_from,$lng_to);
			}
			//var_dump($point)
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
			//var_dump($new_tracks);
			
			//var_dump($points_numbers);
		}
		return $new_tracks;
	}

    public function user_overlay($uid,$zoom,$x,$y){
		
		//$tracks = \App\Models\Track::where('uid',1)->get();
		$user=\App\Models\User::findOrFail($uid);
		
		//$tracks=\App\Models\Track::where('uid',$uid)->get();
		//$tracks = collect([\App\Models\Track::find(21)]);
	
		$geo=resolve('geometry');
		$lines=collect([]);
		$items_count=pow(2,$zoom);
		
		$lng_deg_per_item=360/$items_count;
		$lng_from=-180+$x*$lng_deg_per_item;
		$lng_to=-180+($x+1)*$lng_deg_per_item;
		
		$lat_deg_per_item=(85.0511*2)/$items_count;
		$lat_to=rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
		$lat_from=rad2deg(atan(sinh(pi() * (1 - 2 * ($y+1) / $items_count))));

		$map = new \Imagick();
		$map->newImage(512, 512,new \ImagickPixel('transparent'));
		//$map->setBackgroundColor();
		$map->setImageFormat("png");
		$draw = new \ImagickDraw();
		//$draw->setFillAlpha(0);
		$draw->setStrokeColor(new \ImagickPixel('rgba(255, 0, 0, 0.8)'));
		if($zoom>=14){
			$draw->setStrokeWidth(20);
		}elseif($zoom>=13){
			$draw->setStrokeWidth(15);
		}elseif($zoom>=11){
			$draw->setStrokeWidth(5);
		}elseif($zoom>=7){
			$draw->setStrokeWidth(4);
		}else{
			$draw->setStrokeWidth(2);
		}
		
		$draw->setStrokeLineCap(\Imagick::LINECAP_BUTT);// КОнец линии делает квадратным, потому что другой конец все портит
		$draw->setStrokeLineJoin(\Imagick::LINEJOIN_ROUND);// склейку в полилиниях деляем скругленной по фану.
		$draw->setFillColor(new \ImagickPixel('transparent'));
		
		
		$tracks = $user->getTracks($lat_from,$lng_from,$lng_from,$lng_to);
		$has_tracks=false;
		foreach($tracks as $k=>$track){
			$lines=$track->get_tracks();
			$result_tracks=$this->get_tracks($lines,$lat_from,$lat_to,$lng_from,$lng_to);
			foreach($result_tracks as $item){
				$has_tracks=true;
				$item=$item->toArray();
				$line=array_map(function($item)use($lng_from,$lat_from,$lng_to,$lat_to,$items_count,$y){
					$l['y']=(1 - log(tan(deg2rad($item[0])) + 1 / cos(deg2rad($item[0]))) / pi()) /2 * $items_count;
					$l['y']-=$y;
					$l['y']=512*$l['y'];
					$l['x']=round(($item[1]-$lng_from)*512/($lng_to-$lng_from));
					return $l;
				},$item);
				$draw->polyline(array_merge($line,array_reverse($line)));// линия идет в обе стороны, чтобы не было даже возможности нарисовать область внутри
			}
		}
		
		if($has_tracks){
			$map->drawImage($draw);
			$imagefile=$map->getImageBlob();
		}else{
			$imagefile=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAgAAAAIAAQMAAADOtka5AAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAADZJREFUeNrtwQEBAAAAgqD+r26IwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4g6CAAABAfU3XgAAAABJRU5ErkJggg==');
		}
			
		$file_path=base_path('map_overlay/'.$uid.'/'.$zoom.'/'.$x.'/'.$y.'.png');
		$dirname=pathinfo($file_path,PATHINFO_DIRNAME);
		if(!is_dir($dirname)){
			mkdir($dirname,0755,true);
		}
		//var_dump('ss');
		file_put_contents($file_path,$imagefile);
		return response($imagefile)->header('Content-type','image/png');
		//return $map->getImageBlob();
		//var_dump($lines);
		//var_dump('some');
	}

}
