<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Token;
use App\Models\TrackGetter;

class TrackgetStrava implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $token_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token_id)
    {
        $this->token_id=$token_id;
    }
	public function refreshToken($token){
		$provider = new \League\OAuth2\Client\Provider\Strava([
				'clientId'     => config('services.strava.client_id'),
				'clientSecret' => config('services.strava.client_secret'),
				'redirectUri'  => config('services.strava.redirect_uri'),
			]);
		$newAccessToken = $provider->getAccessToken('refresh_token', [
			'refresh_token' => $token->refresh_token,
		]);
		$token->access_token=$newAccessToken->getToken();
		$token->refresh_token=$newAccessToken->getRefreshToken();
		$token->expires_time=$newAccessToken->getExpires();
		$token->save();
		return $token;
	}
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		var_dump('strava token');
        //
		$token=Token::find($this->token_id);
		if($token->expires_time<time()){
			var_dump('refresh');
			$token=$this->refreshToken($token);
		}
		$getter = TrackGetter::firstOrNew([
			'user_id' => $token->user_id,
			'service'=>$token->service
		]);
		$enddate=$getter->getData('enddate');
		var_dump($enddate);
		
		if(empty($enddate)){
			$options=[];
			$enddate='1990-01-01T00:00:00Z';
		}else{
			$s=new \DateTime($enddate);
			$options=['after'=>$s->format('U')];
		}
		$options['page']=1;
		//$options['per_page']=2;
		
		
		

		$headers = array('Authorization: Bearer '.$token->access_token);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$url='https://www.strava.com/api/v3/athlete/activities?';
		curl_setopt($curl, CURLOPT_URL, $url.http_build_query($options));
		$response = json_decode(curl_exec($curl),true);

		while(!empty($response)){
			var_dump(json_encode($options));
			foreach($response as $activity){
				var_dump($activity);
				if($activity['start_date_local']>$enddate){
					$enddate=$activity['start_date_local'];
				}
				var_dump('added activity');
				\App\Jobs\TrackgetStravaSingle::dispatch($token->id,$activity['id'])->onQueue('parsers');
				
			}
			$options['page']++;
			curl_setopt($curl, CURLOPT_URL, $url.http_build_query($options));
			$response = json_decode(curl_exec($curl),true);
		}
		$getter->setData('enddate',$enddate);
		$getter->save();
		return;
		die();

		if(empty($user_id)){
			var_dump('no_user_id');
			$url = "https://api.ua.com/v7.1/user/self/";
			curl_setopt($curl, CURLOPT_URL, $url);
			$response = curl_exec($curl);
			$user_id=(json_decode($response,true)['id']);
			$getter->setData('user_id',$user_id);
			$getter->save();
		}
		$start_after=$getter->getData('start_after');
		if(empty($start_after)){
			$start_after='1980-01-01T08:50:20+00:00';
		}
		$query=['user'=>$user_id,'order_by'=>'start_datetime','started_after'=>$start_after];
		$url = "https://api.ua.com/v7.1/workout/?".http_build_query($query);
		curl_setopt($curl, CURLOPT_URL, $url);
		$response = curl_exec($curl);
		var_dump($response);	
		$geometry=resolve('geometry');
		
		foreach(json_decode($response,true)['_embedded']['workouts'] as $workout){
			if(empty($workout['_links']['route']))continue;//не записан трек, просто тренировка
			//var_dump('activity|'.$workout['_links']['activity_type'][0]['id']);
			$url='https://api.ua.com/v7.1/route/'.$workout['_links']['route'][0]['id'].'/?field_set=detailed';
			curl_setopt($curl, CURLOPT_URL, $url);
			$points=json_decode(curl_exec($curl),true)['points'];
			$points_backup=$points;
			$points=array_map(function($item){
				if(empty($item['lat']))return '';
				return $item['lat'].' '.$item['lng'];
			},$points);
			$points=array_values(array_filter($points));
			$points=implode(',',$points);
			$w=$geometry->parseWkt('MultiLineString(('.$points.'))');
			
			$track=new \App\Models\Track();
			$track->track_original=$w->toWkb();
			$track->track_simple=$w->toWkb();
			$track->simplification_version=255;
			$track->external_id='underarmour_'.$workout['_links']['self'][0]['id'];
			//var_dump($track->external_id.'|'.$workout['start_datetime']);
			$track->uid=$token->user_id;
			$date=new \DateTime($workout['start_datetime']);
			$track->date=$date->format('Y-m-d H:i:s');
			if(\DB::table('tracks')->where('external_id',$track->external_id)->count()==0){
				$track->save();
			}
			$getter->setData('start_after',$workout['start_datetime']);
			$getter->save();
			\App\Jobs\RemoveTilesJob::dispatch($token->user_id,$points_backup)->onQueue('tiles');
		}
		curl_close($curl);
		var_dump(count(json_decode($response,true)['_embedded']['workouts']));
    }
}
