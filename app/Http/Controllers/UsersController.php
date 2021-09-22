<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
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
		\Auth::login($user);
		return redirect('/');
	}
	public function mainpage(){
		$user = \Auth::user();
		return view('mainpage_logged',['user'=>$user]);
	}
}
