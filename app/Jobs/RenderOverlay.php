<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenderOverlay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $zoom; //telegram message id
	protected $x; //telegram message id
	protected $y; //telegram message id
	public $tries = 500;
	public $timeout = 3600;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($zoom,$x,$y)
    {
        $this->zoom=$zoom;
        $this->x=$x;
        $this->y=$y;
        //
    }
    /**
     * Execute the job.
     *
     * @return void
     */
	
	public function lines_filter($pre_lines){
		//var_dump($elements);		die();
		$lines=['undefined'=>[],'bikelane'=>[],'foot'=>[],'bicycle_undefined'=>[],'great_foot'=>[],'great'=>[]];
		$points=[];
		//$lines=[];
		foreach($pre_lines as $pre_line){
			$tg=!empty($pre_line['tags']['lbroad'])?$pre_line['tags']['lbroad']:'undefined';
			$lines[$tg][]=$pre_line['points'];
		}
		return $lines;
	}

	public function get_length($item){
		$length=0;
		foreach($item as $k=>$point){
			if($k==0)continue;
			$length+=abs($point['x']-$item[$k-1]['x'])+abs($point['y']-$item[$k-1]['y']);
		}
		return $length;
	}
    public function handle()
    {
        $zoom=$this->zoom;
		$x=$this->x;
		$y=$this->y;
		//if($zoom<10) return'';
		$items_count=pow(2,$zoom);
		$lng_deg_per_item=360/$items_count;
		$lng_from=-180+$x*$lng_deg_per_item;
		$lng_to=-180+($x+1)*$lng_deg_per_item;
		
		$lat_deg_per_item=(85.0511*2)/$items_count;
		$lat_to=rad2deg(atan(sinh(pi() * (1 - 2 * $y / $items_count))));
		$lat_from=rad2deg(atan(sinh(pi() * (1 - 2 * ($y+1) / $items_count))));

		$lbroads=new \App\Lbroads();
		//if(php_sapi_name()=='cli'){var_dump('beforelines|time:'.time());}
		$lines=$lbroads->get_lines($zoom,$x,$y);
		//if(php_sapi_name()=='cli'){var_dump('afterlines|time:'.time());}

		$lines=array_map([$lbroads,'add_lbroads_tags'],$lines);
		//if(php_sapi_name()=='cli'){var_dump('addedtags|time:'.time());}
		$lines_all=$this->lines_filter($lines);
		//if(php_sapi_name()=='cli'){var_dump('filter_lines|time:'.time());}
		$map = new \Imagick();
		$map->newImage(512, 512,new \ImagickPixel('transparent'));
		$map->setImageFormat("png");
		//$roads=
		
		foreach($lines_all as $type=>$lines){
			$lines_comp=[];
			foreach($lines as $item){
				$item=array_map(function($item)use($lng_from,$lat_from,$lng_to,$lat_to){
					//var_dump($item);
					$l['y']=512-round(($item['lat']-$lat_from)*512/($lat_to-$lat_from));
					$l['x']=round(($item['lng']-$lng_from)*512/($lng_to-$lng_from));
					return $l;
				},$item);
				
				//var_dump($item);
				if($this->get_length($item)>2){
				//die();
					$lines_comp[]=$item;
				}
			}
			
			$colors=['great'=>'125,0,125','bicycle_undefined'=>'255,0,0','bikelane'=>'0,0,255','undefined'=>'0,0,0','foot'=>'40,252,3','great_foot'=>'19,130,0'];
			$color=$colors[$type];
			$draw = new \ImagickDraw();
			//$draw->setFillAlpha(0);
			$draw->setStrokeWidth(8);
			$draw->setStrokeLineCap(\Imagick::LINECAP_BUTT);// КОнец линии делает квадратным, потому что другой конец все портит
			$draw->setStrokeLineJoin(\Imagick::LINEJOIN_ROUND);// склейку в полилиниях деляем скругленной по фану.
			$draw->setFillColor(new \ImagickPixel('transparent'));
			$draw->setStrokeColor(new \ImagickPixel('rgba('.$color.', 1)'));
			foreach($lines_comp as $line){
				$draw->polyline (array_merge($line,array_reverse($line)));// линия идет в обе стороны, чтобы не было даже возможности нарисовать область внутри
			}

			$map->drawImage($draw);
		}
		if(php_sapi_name()=='cli'){var_dump('drawed_all|time:'.time());}
		$imagefile=$map->getImageBlob();
		if(array_sum(array_map('count',$lines_all))!=0){
			$imagefile=$map->getImageBlob();
		}else{
			$imagefile='';
			$imagefile=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAgAAAAIAAQMAAADOtka5AAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAADZJREFUeNrtwQEBAAAAgqD+r26IwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA4g6CAAABAfU3XgAAAABJRU5ErkJggg==');
		}
		if(php_sapi_name()=='cli'){var_dump('putfile|time:'.time());}
			
		$file_path=base_path('lb_overlay/'.$zoom.'/'.$x.'/'.$y.'.png');
		if(php_sapi_name()=='cli'){var_dump('completed|time:'.time());}
		$dirname=pathinfo($file_path,PATHINFO_DIRNAME);
		if(!is_dir($dirname)){
			mkdir($dirname,0755,true);
		}
		file_put_contents($file_path,$imagefile);
		if(php_sapi_name()=='cli'){var_dump('end|time:'.time());}
    }
}
