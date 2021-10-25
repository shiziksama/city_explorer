<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ParseFileJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $filename;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filename)
    {
		$this->filename=$filename;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
	public function removeLine($coords,$user_id){
		foreach(array_chunk($coords,100) as $k=>$points){
			$points=array_map(function($point){
				return ['lat'=>$point[0],'lng'=>$point[1]];
			},$points);
			\App\Jobs\RemoveTilesJob::dispatch($user_id,$points)->onQueue('tiles');
		}
	}
    public function handle()
    {
		$user_id=1;//shiziksama
		$ext=(pathinfo($this->filename,PATHINFO_EXTENSION));
		$g=resolve('geometry');
		if($ext=='kml'){
			$w=$g->parseKml(Storage::disk('local')->get($this->filename));
			$arr=$w->toArray();
			$w=null;
			if($arr['type']=='MultiLineString'){
				foreach($arr['coordinates'] as $k=>$coords){
					$arr['coordinates'][$k]=array_map('array_reverse',$arr['coordinates'][$k]);
				}
			}else{
				$arr['coordinates']=array_map('array_reverse',$arr['coordinates']);
			}
			$w=$g->parseJson(json_encode($arr));
		}
		if(empty($w))return;
		$date=new \DateTime();
		
		$track=new \App\Models\Track();
		$track->track_original=$w->toWkb();
		$track->track_simple=$w->toWkb();
		$track->simplification_version=255;
		$track->external_id=$user_id.'|'.date('Y-m-d H').'|'.$this->filename;
		$track->uid=$user_id;//shiziksama
		$track->date=$date->format('Y-m-d H:i:s');
		if(\DB::table('tracks')->where('external_id',$track->external_id)->count()==0){
			$track->save();
		}

		$arr=$w->toArray();
		$coords=$arr['coordinates'];
		if($arr['type']=='MultiLineString'){
			foreach($coords as $coord){
				$this->removeLine($coord,$user_id);
			}
		}else{
			$this->removeLine($coords,$user_id);
		}
		
    }
}
