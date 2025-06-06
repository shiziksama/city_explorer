<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Redis;

class OsmApi extends Model
{
    use HasFactory;
	public static function getUrl($url){
		$content=Redis::get('url'.$url);
		//var_dump($url);
		//var_dump($content);
		if(!empty($content)){
			
			//var_dump('cached');
			//var_dump(json_decode($content,true));
			return $content;
		}
		//var_dump($url);
		$content=file_get_contents($url);
		//var_dump($content);
		Redis::set('url'.$url,$content);
		return $content;
	}
	public static function getNearest($lat,$lng,$number_points=50,$max_distance=30){
		$url='http://localhost:5000/nearest/v1/walking/'.$lng.','.$lat.'?number='.$number_points;
	
		$content=json_decode(self::getUrl($url),true);
		//var_dump($content);
		$variants=[];
		foreach($content['waypoints'] as $k=>$variant){
			//var_dumP($variant);
			if($variant['distance']>$max_distance)continue;//Нормально спозиционировало только с 29, решил округлить
			$variants[]=['location'=>$variant['location'],'distance'=>$variant['distance']];
		}
		if(empty($variants)){
			$variants[]=['location'=>[$lng,$lat],'distance'=>0];
		}
		return $variants;
	}
	public static function getDistances($last_points,$variants){ //from_points to_points
		$points=$last_points->merge($variants)->map(function($item){
			return implode(',',$item);
		})->implode(';');
		
		
		$url='http://localhost:5000/table/v1/walking/'.$points.'?sources='.$last_points->keys()->implode(';').'&destinations='.$variants->keys()->map(function($item)use($last_points){
				return $item+$last_points->count();
				})->implode(';').'&annotations=distance';
		//var_dump($url);
		$distances=json_decode(self::getUrl($url),true)['distances'];
		//var_dump($distances);
		return $distances;
		
	}
	public static function maptching($points){
		$url='https://api.mapbox.com/matching/v5/mapbox/walking/';
		$url.=$points->map(function($item){
			return implode(',',array_reverse($item));
		})->implode(';');
//		$url.='-117.17282,32.71204;-117.17288,32.71225;-117.17293,32.71244;-117.17292,32.71256;-117.17298,32.712603;-117.17314,32.71259;-117.17334,32.71254';
                $url.='?access_token='.config('services.mapbox.token');
                $url.='&geometries=geojson&tidy=true';
		$content=json_decode(self::getUrl($url),true);
		$content=$content['matchings'];
		if(count($content)==1){
			$content=reset($content);
		}else{
			var_dump($content);
			var_dump('many_matchings');
			die();
		}
		
		return (array_map('array_reverse',$content['geometry']['coordinates']));
	}
	public static function route($points_orig){
		if(count($points_orig)==1){
			return $points_orig;
		}
		$points=collect($points_orig)->slice(0,500)->map(function($item){
			//var_dump($item);
			return implode(',',$item);
		})->implode(';');
		$points_after=collect($points_orig)->slice(500)->values()->toArray();
		$url='http://localhost:5000/route/v1/walking/'.$points.'?geometries=geojson&overview=simplified';
		$result=json_decode(self::getUrl($url),true)['routes'][0]['geometry']['coordinates'];	
		$result=array_merge($result,$points_after);
		return $result;
	}
}
