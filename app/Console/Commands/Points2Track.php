<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Points2Track extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:points2track';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		//\App\Jobs\SendResultsJob::dispatchSync(73);
		//die();
		$res=collect(\DB::select('select max(timestamp) as s,uid,mid from curpoints group by uid,mid order by s asc limit 1'))->first();
		if(empty($res)){
			var_dump('no more points');
			return;
		}
		if($res->s>(time()-60*30)){
			var_dump('no time points');
			return;
		}
		$uid=$res->uid;
		$mid=$res->mid;
		
		$geometry=resolve('geometry');
		
		$curpoints=\App\Models\Curpoint::orderBy('timestamp','asc')->where('uid',$uid)->where('mid',$res->mid)->get();

		
		
		$min_timestamp=$curpoints->min('timestamp');
		$l_timestamp=$min_timestamp;
		$curpoints=$curpoints->filter(function($point){
			return $point->horizontal_accuracy<200;
		});
		$curpoints->transform(function($point)use(&$l_timestamp){
			$point->timefrom=$point->timestamp-$l_timestamp;
			$l_timestamp=$point->timestamp;
			if($point->horizontal_accuracy>200){
				var_dump('wrong accyray');
				die();
			}
			unset($point->timestamp);
			unset($point->mid);
			unset($point->uid);
			return $point;
		});
		$horizontal_accuracy=$curpoints->pluck('horizontal_accuracy')->toArray();
		$timefrom=$curpoints->pluck('timefrom')->toArray();
		$multiline=[];
		$line=[];
		foreach($curpoints as $curpoint){
			if($curpoint->timefrom > 300){
				$multiline[]=implode(',',$line);
				$line=[];
			}
			$line[]=$curpoint->lat.' '.$curpoint->lng;
		}
		if(!empty($line)){
			$multiline[]=implode(',',$line);
		}
		
		$wkt='MultiLineString(('.implode('),(',$multiline).'))';
		
		$w=$geometry->parseWkt($wkt);
		$track=new \App\Models\Track();
		$track->track_original=$w->toWkb();
		$track->track_simple=$w->toWkb();
		$track->simplification_version=0;
		$track->times=pack('S*', ...$timefrom);
		$track->horizontal_accuracy=pack('S*', ...$horizontal_accuracy);
		$track->uid=$uid;
		$track->date=date('Y-m-d H:i:s',$min_timestamp);
		\DB::connection('mysql')->beginTransaction();
		\DB::connection('mysql')->enableQueryLog();
		$track->save();
		\DB::delete('delete from curpoints where uid=? and mid=?',[$uid,$mid]);
		//var_dump(\DB::getQueryLog());
		\DB::commit();
		var_dump('completed|'.$mid);
		
		
		
		
    }
}
