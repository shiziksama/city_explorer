<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendResultsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $mid; //telegram message id
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mid)
    {
        $this->mid=$mid;
    }
	public function sum($items){
		return [
			'distance'=>array_sum(array_column($items, 'distance')),
			'time'=>array_sum(array_column($items, 'time')),
			'speed'=>round(array_sum(array_column($items, 'distance'))/array_sum(array_column($items, 'time'))*3.6,2),
		];
	}
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $points=\App\Models\Curpoint::where('mid',$this->mid)->where('horizontal_accuracy','<',100)->orderBy('timestamp','asc')->get();
		$distance=0;
		//var_dump($points->toArray());
		
		$time=0;$points->max('timestamp')-$points->min('timestamp');
		$new_points=[];
		foreach($points as $k=>$point){
			if($k==0)continue;
			$new_point=[];
			$new_point['distance']=$this->distance($point->lat,$point->lng,$points[$k-1]->lat,$points[$k-1]->lng);
			$new_point['time']=$point->timestamp-$points[$k-1]->timestamp;
			$new_point['speed']=round($new_point['distance']/$new_point['time']*3.6,2);
			$new_points[]=$new_point;
		}
		$jumps=[];
		$stops=[];
		$walking=[];
		foreach($new_points as $point){
			if($point['distance']>=1000||$point['time']>300){
				$jumps[]=$point;
			}elseif($point['speed']<0.2){ 
				$stops[]=$point;
			}else{
				$walking[]=$point;
			}
		}
		$walking=($this->sum($walking));
		$jumps=($this->sum($jumps));
		$stops=($this->sum($stops));
		//var_dump($new_points);
		/*
		foreach($points as $k=>$point){
			if($k==0)continue;
			$distance+=$this->distance($point->lat,$point->lng,$points[$k-1]->lat,$points[$k-1]->lng);
		}*/
		$smessage=[];
		$smessage['text']=(string)view('messages.track_results',['walking'=>$walking,'jumps'=>$jumps,'stops'=>$stops]);
		//$smessage=['text'=>'ДИСТАНЦИЯ '.$distance.' Метров. Время.'.$time];
		
		//var_dump($smessage);
		//return;
		\App\Jobs\SendTmessageJob::dispatchSync($points->first()->uid,$smessage);
    }
	public function distance($lat1, $lng1, $lat2, $lng2){
		return ceil(12745594 * asin(sqrt(
			pow(sin(deg2rad($lat2-$lat1)/2),2)
			+
			cos(deg2rad($lat1)) *
			cos(deg2rad($lat2)) *
			pow(sin(deg2rad($lng2-$lng1)/2),2)
		)));
	}
}
