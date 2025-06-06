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

class TrackgetUnderArmour implements ShouldQueue
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

    /**
     * Execute the job.
     *
     * @return void
     */
	public function refreshToken($token){
		$provider = new \Spacebib\OAuth2\Client\Provider\UnderArmour([
			'clientId'          => config('services.underarmour.key'),
			'clientSecret'      => config('services.underarmour.secret'),
			'redirectUri'       => 'https://tracks.lamastravels.in.ua/connect/underamour',
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
    public function handle()
    {
		//https://api.mapmyfitness.com/v7.1/workout/?user=184233997&order_by=-start_datetime
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
		$user_id=($getter->getData('user_id'));

		$headers = array('Authorization: Be	arer '.$token->access_token,'Api-Key: '.config('services.underarmour.key'));
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);


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
		if($response=='{}'){
			return;//empty response - something is wrong
		}
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
		if(count(json_decode($response,true)['_embedded']['workouts'])!=0){
			\App\Jobs\TrackgetUnderArmour::dispatch($token->id)->onQueue('parsers');
		}
    }
}
