<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Token;
use App\Mail\LoginLink;

class UsersController extends Controller{
	public function login_page(){
		return view('users.login');
	}
	public function login_page_post(){
		var_dump('some');
		$user=User::where('email',$_POST['email'])->firstOrFail();
		$user->code=bin2hex(random_bytes(16));
		$user->save();
		\Mail::to($user->email)->send(new LoginLink($user->code));
	}
	public function login_with_code($code){
		$user=User::where('code',$code)->firstOrFail();
		$user->code='';
		$user->save();
		\Auth::login($user,true);
		return redirect('/');
	}
	public function profile(){
		$user = \Auth::user();
		return view('profile',['user'=>$user]);
	}
	public function mainpage(){
		return view('mainpage_guest');
	}
	public function connect($provider_name){
		//сделать для разных провайдеров.
		/*
		//Установка redirect_url для underarmour
		$data = ['grant_type'=> 'client_credentials', 'client_id'=> config('services.underarmour.key'), 'client_secret'=> config('services.underarmour.secret')];
		$url = 'https://api.ua.com/v7.1/oauth2/access_token/';
		$headers = array('Content-Type: application/x-www-form-urlencoded',	'Api-Key: '.config('services.underarmour.key'));
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($curl);
		curl_close($curl);
		$token=json_decode($response,true)['access_token'];
		
		//https://developer.underarmour.com/updatekey/
		//https://developer.underarmour.com/updatekey/
		
		
		$data = ['callback_uri'=> 'https://tracks.lamastravels.in.ua/connect/underamour', 'application_title'=> 'CityExplorer'];
		$url = "https://api.ua.com/v7.2/api_client/".config('services.underarmour.key')."/";
		$headers = array('Authorization: Bearer '.$token,'Api-Key: '.config('services.underarmour.key'));
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
		curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($curl);
		curl_close($curl);
		var_dump(json_decode($response,true));
		
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		$response = curl_exec($curl);
		curl_close($curl);
		var_dump(json_decode($response,true));
		
		die();	*/
		

		$provider = new \Spacebib\OAuth2\Client\Provider\UnderArmour([
			'clientId'          => config('services.underarmour.key'),
			'clientSecret'      => config('services.underarmour.secret'),
			'redirectUri'       => 'https://tracks.lamastravels.in.ua/connect/underamour',
		]);
		//var_dump(\Session::get('oauth2state'.$provider_name));
			
		if (!isset($_GET['code'])) {
			// If we don't have an authorization code then get one
			$authUrl = $provider->getAuthorizationUrl();
			var_dump($provider->getState());
			\Session::put('oauth2state'.$provider_name,$provider->getState());
			var_dump(\Session::get('oauth2state'.$provider_name));
			//return response('some')->header("Refresh", "5;url=/connect/underamour"); //redirect('https://tracks.lamastravels.in.ua/connect/underamour',10);
			return redirect($authUrl);
			// Check given state against previously stored one to mitigate CSRF attack
		} elseif (empty($_GET['state']) || ($_GET['state'] !== \Session::get('oauth2state'.$provider_name))) {
			//svar_dump(\Session::get('oauth2state'.$provider_name));
			return redirect('https://tracks.lamastravels.in.ua/connect/underamour');
		} else {

			// Try to get an access token (using the authorization code grant)
			$token = $provider->getAccessToken('authorization_code', [
				'code' => $_GET['code']
			]);
			$token_t=new Token;
			$token_t->user_id=\Auth::id();
			$token_t->service=$provider_name;
			$token_t->access_token=$token->getToken();
			$token_t->refresh_token=$token->getRefreshToken();
			$token_t->expires_time=$token->getExpires();
			$token_t->save();
			\App\Jobs\TrackgetUnderArmour::dispatch($token_t->id)->onQueue('parsers');
			return redirect('/profile');
		}
	}
}
