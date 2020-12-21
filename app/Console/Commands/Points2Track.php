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
		\App\Jobs\SendResultsJob::dispatchSync(67);
		die();
		$res=collect(\DB::select('select max(timestamp) as s,uid from curpoints group by uid order by s asc limit 1'))->first();
		if($res->s>(time()-60*30)){
			return;
		}
		$uid=$res->uid;
		
		$geometry=resolve('geometry');
		
		$curpoints=\App\Models\Curpoint::orderBy('timestamp','asc')->where('uid',$uid)->get();
		$min_timestamp=$curpoints->min('timestamp');
		var_dump($min_timestamp);
		$trackpoints=collect([]);
		foreach($curpoints as $k=> $point){
			if($k!=0&&$point->timestamp-$curpoints[$k-1]->timestamp >60*15){
				break;
			}
			$trackpoints->push($point);
		}
		var_dump($trackpoints->count());
		var_dump($curpoints->count());
		//die();
		
		$wkt=$trackpoints->map(function($item){
			return $item->lat.' '.$item->lng;
		})->implode(',');
		$wkt='LINESTRING('.$wkt.')';
		$w=$geometry->parseWkt($wkt);
	
		//var_dump($w->toJson());
		//var_dump($w->toWkb());
		$track=new \App\Models\Track();
		$track->track_original=$w->toWkb();
		$track->track_simple=$w->toWkb();
		$track->simplification_version=0;
		$track->uid=$uid;
		$track->date=date('Y-m-d H:i:s',$min_timestamp);
		
		\DB::beginTransaction();
		\DB::connection()->enableQueryLog();
		$track->save();
		\DB::delete('delete from curpoints where uid=? and timestamp between ? and ?',[$uid,$trackpoints->min('timestamp'),$trackpoints->max('timestamp')]);
		var_dump(\DB::getQueryLog());
		\DB::commit();
		//\DB::rollback();
		//die();
		
		
		
		
		
    }
}
