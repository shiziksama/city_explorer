<?php
// Example webhook setup URL:
// https://api.telegram.org/bot<token>/setWebhook?url=https://tracks.lamastravels.in.ua/telegramwebhook
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class TelegramController extends Controller{
        public $bot_token;

        public function __construct()
        {
                $this->bot_token = config('services.telegram.bot_token');
        }
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
		//var_dump('some');
		//file_put_contents(base_path('debug.txt'),json_encode($message,JSON_PRETTY_PRINT).'message'.PHP_EOL,FILE_APPEND);
		if(!empty($message['document'])){
			$docs=json_decode(file_get_contents('https://api.telegram.org/bot'.($this->bot_token).'/getFile?file_id='.$message['document']['file_id']),true)['result'];
			$file=file_get_contents('https://api.telegram.org/file/bot'.($this->bot_token).'/'.$docs['file_path']);
			Storage::disk('local')->put($message['document']['file_name'], $file);
			\App\Jobs\ParseFileJob::dispatch($message['document']['file_name'])->onQueue('parsers');
			
		}
               // Previously location points were stored for track recording.
               // This functionality has been removed.
		if(false){
			$smessage=['chat_id'=>$tmessage->user->telegram_id];
			$smessage['text']='sometext';
			//$q= file_get_contents('https://api.telegram.org/bot1400511618:AAFhsV1xuUOfwPSzOkAmqntVgLcu63WZv80/sendMessage?' . http_build_query($smessage));
			$q= file_get_contents('https://api.telegram.org/bot1400511618:AAFh0OzXWRYXcp-ztVDtQLz_qH2nadFj9p4/sendMessage?' . http_build_query($smessage));
		}
		
	}
    //
}
