<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Token;

class TrackgetStravaSingle implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $token_id;
	protected $track_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token_id,$track_id)
    {
        $this->token_id=$token_id;
		$this->track_id=$track_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		//var_dump($this->track_id);
		$token=Token::find($this->token_id);
		
		$headers = array('Authorization: Bearer '.$token->access_token);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$url='https://www.strava.com/api/v3/activities/'.$this->track_id;
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = json_decode(curl_exec($curl),true);
		//var_dump($response['map']['polyline']);
		$geometry=resolve('geometry');
		$points = \Polyline::decode($response['map']['polyline']);
		$points=array_chunk($points,2);
		
		//
		$points_backup=$points;
		
		$points=array_map(function($item){
			return implode(' ',$item);
		},$points);
		$points_backup=array_map(function($item){
			return ['lat'=>$item[0],'lng'=>$item[1]];
		},$points_backup);
		$points=array_values(array_filter($points));
		$points=implode(',',$points);
		$w=$geometry->parseWkt('MultiLineString(('.$points.'))');
			
		$track=new \App\Models\Track();
		$track->track_original=$w->toWkb();
		$track->track_simple=$w->toWkb();
        $track->remove_big_lines();
		$track->simplification_version=255;
		$track->external_id='strava_'.$this->track_id;
		$track->uid=$token->user_id;
		$date=new \DateTime($response['start_date_local']);
		$track->date=$date->format('Y-m-d H:i:s');
		if(\DB::table('tracks')->where('external_id',$track->external_id)->count()==0){
			$track->save();
		}
		\App\Jobs\RemoveTilesJob::dispatch($token->user_id,$points_backup)->onQueue('tiles');
		

		
        //
    }
}
