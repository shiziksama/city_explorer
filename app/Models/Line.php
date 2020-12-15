<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    use HasFactory;
	public function create($points){
		if(is_array($points)){
			$points=collect($points);
		}
		$this->coords=$points->transform(function($item){
			return ['lat'=>$item[0],'lng'=>$item['1']];
		});
	}
	public function populate_coords(){
		$coords=$this->coords;
		$log_stats='cli';//TODO
		//$woods=self::get_woods_fields($coords->pluck('lat')->min(),$coords->pluck('lng')->min(),$coords->pluck('lat')->max(),$coords->pluck('lng')->max());
		//$areas=$woods['areas'];
		//$relations=$woods['relations'];
		$areas=[];//площади, в которых не будет привязки к координатам
		$relations=[];// реляции, в которых не будет привязки к координатам
		foreach($coords as $l=>$coord){
			//echo 'get_info|'.$l.' of '.$coords->count().PHP_EOL;
			if($log_stats=='cli'&&($l % 100==0)){
				echo 'get_info|'.$l.' of '.$coords->count().PHP_EOL;
			}
			$variants=OsmApi::getNearest($coord['lat'],$coord['lng']);
		
			if(empty($variants)){
				var_dump($url);
				var_dump($coord);
				die();
				}
			$coord['variants']=$variants;
			
			$coords->put($l,$coord);
		}
		$this->coords=$coords;
	}
	public function getLines(){
		$log_stats='cli';
		$coords=$this->coords;
		
		$lines=collect($coords->first()['variants'])->map(function($item)use($coords){
			return ['line'=>collect([[$coords->first()['lng'],$coords->first()['lat']],$item['location']]),'length'=>0,'noise'=>$item['distance']];
		});

		$time=microtime(true);

		foreach($coords as $k=>$coord){
			
			if($log_stats=='cli'&&($k % 100==0)){
				echo 'get_path|'.$k.' of '.$coords->count().PHP_EOL;
			}
			if($k==0)continue;
			$last_points=$lines->map(function($item){
				return $item['line']->last();
			});
			$variants=collect($coord['variants'])->pluck('location');
			if($variants->count()==1&$last_points->count()==1){ //Небольшое ускорение, если вдруг 2 точки подряд не определились.
				$line=$lines[0];
				$line['line'][]=$coord['variants'][0]['location'];
				$lines->put(0,$line);
				continue;
			}
			$distances=OsmApi::getDistances($last_points,$variants);
			//TODO
			$new_lines=collect([]);
			foreach($variants as $variant_id=>$variant){
				$distance=99999999999999999999;//большое число
				$line_great=100;//невалидный номер
				foreach($distances as $line_number=>$line_distances){
					if(($lines[$line_number]['length']+$lines[$line_number]['noise']+$line_distances[$variant_id])<$distance){
						$distance=$lines[$line_number]['length']+$lines[$line_number]['noise']+$line_distances[$variant_id];
						$line_great=$line_number;
					}
				}
				$line=['line'=>clone($lines[$line_great]['line']),'length'=>$lines[$line_great]['length']+$distances[$line_great][$variant_id],'noise'=>$lines[$line_great]['noise']+$coord['variants'][$variant_id]['distance']/4];
				$line['line'][]=$coord['variants'][$variant_id]['location'];
				//$line['length']=$distance-$lines[$line_great]['noise'];
				$new_lines->put($variant_id,$line);
			}
			$lines=$new_lines;
		}
		$this->lines=$lines;
		return $this->lines;

	}
	public function getOneLine(){
		if(empty($this->lines)){
			$lines=$this->getLines();
		}else{
			$one_line=collect([]);
			foreach($this->lines as $line){
				$one_line->push(clone($line['line']));
			}
		}
		
				while($one_line->count()!=1){
			$one_line->map(function($item){
				$item->pop();
				return $item;
			});
			$one_line=$one_line->unique();
			
		};
		$one_line=$one_line->first();
		$this->one_line=$one_line;
		return $this->one_line;
	}
}
