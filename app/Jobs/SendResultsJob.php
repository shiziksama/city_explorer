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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $points=\App\Models\Curpoint::where('mid',$this->mid)->orderBy('timestamp','asc')->get();
		$distance=0;
		$time=$points->max('timestamp')-$points->min('timestamp');
		foreach($points as $k=>$point){
			if($k==0)continue;
			$distance+=$this->distance($point->lat,$point->lng,$points[$k-1]->lat,$points[$k-1]->lng);
		}
		//$smessage=['chat_id'=>$tmessage->user->telegram_id];
		$smessage=['text'=>'ДИСТАНЦИЯ '.$distance.' Метров. Время.'.$time.' секунд'];
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
