<?php
//https://api.telegram.org/bot1400511618:AAFh0OzXWRYXcp-ztVDtQLz_qH2nadFj9p4/setWebhook?url=https://tracks.lamastravels.in.ua/telegramwebhook
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TelegramController extends Controller
{
	public function webhook(){
		file_put_contents(base_path('debug.txt'),'some',FILE_APPEND);
		$content = file_get_contents("php://input");
		
		$content = json_decode($content, true);
		file_put_contents(base_path('debug.txt'),json_encode($content,JSON_PRETTY_PRINT).PHP_EOL,FILE_APPEND);
		//$tmessage=new \App\Models\Tmessage();
		$tmessage=\App\Models\Tmessage::populateFromWebhook($content);
		if(empty($tmessage)){
			file_put_contents(base_path('debug.txt'),'no tmessage',FILE_APPEND);
			return;
		}
		file_put_contents(base_path('debug.txt'),json_encode($tmessage,JSON_PRETTY_PRINT).'tmessage'.PHP_EOL,FILE_APPEND);
		
		$message=json_decode($tmessage->message,true);
		var_dump('some');
		file_put_contents(base_path('debug.txt'),json_encode($message,JSON_PRETTY_PRINT).'message'.PHP_EOL,FILE_APPEND);
		if(!empty($message['location'])){
			$s=\App\Models\Curpoint::create([
						'lng'=>$message['location']['longitude'],
						'lat'=>$message['location']['latitude'],
						'timestamp'=>$tmessage->timestamp,
						'uid'=>$tmessage->uid,
						'mid'=>$tmessage->mid,
						'horizontal_accuracy'=>$message['location']['horizontal_accuracy'] ?? 2000,//Больше, чем они могут прислать
						]);
			file_put_contents(base_path('debug.txt'),json_encode($s,JSON_PRETTY_PRINT),FILE_APPEND);
			
			if(empty($message['location']['live_period'])){
				file_put_contents(base_path('debug.txt'),'need send data',FILE_APPEND);
				\App\Jobs\SendResultsJob::dispatchSync($tmessage->mid);
				//точка не лайв. Надо обработать и сообщить о длине и времени трека.
			}
		}
		if(false){
			$smessage=['chat_id'=>$tmessage->user->telegram_id];
			$smessage['text']='sometext';
			//$q= file_get_contents('https://api.telegram.org/bot1400511618:AAFhsV1xuUOfwPSzOkAmqntVgLcu63WZv80/sendMessage?' . http_build_query($smessage));
			$q= file_get_contents('https://api.telegram.org/bot1400511618:AAFh0OzXWRYXcp-ztVDtQLz_qH2nadFj9p4/sendMessage?' . http_build_query($smessage));
		}
		
	}
    //
}
