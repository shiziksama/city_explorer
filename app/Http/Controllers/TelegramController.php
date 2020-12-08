<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramController extends Controller
{
	public function webhook(){
		file_put_contents(base_path('debug.txt'),'some',FILE_APPEND);
		$content = file_get_contents("php://input");
		
		$content = json_decode($content, true);
		file_put_contents(base_path('debug.txt'),json_encode($content,JSON_PRETTY_PRINT),FILE_APPEND);
		$tmessage=new \App\Models\Tmessage();
		$tmessage->populateFromWebhook($content);
		$message=json_decode($tmessage->message,true);
		
		
		if(!empty($message['location'])){
			$s=\App\Models\Curpoint::create(['lng'=>$message['location']['longitude'],'lat'=>$message['location']['latitude'],'timestamp'=>$tmessage->timestamp,'uid'=>$tmessage->user->telegram_id]);
		}
		if(false){
			$smessage=['chat_id'=>$tmessage->user->telegram_id];
			$smessage['text']='sometext';
			$q= file_get_contents('https://api.telegram.org/bot1400511618:AAFhsV1xuUOfwPSzOkAmqntVgLcu63WZv80/sendMessage?' . http_build_query($smessage));
		}
		
	}
    //
}
