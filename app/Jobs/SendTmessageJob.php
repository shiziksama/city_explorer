<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTmessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $uid;
	protected $message;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uid,$message)
    {
		$this->uid=$uid;
		$this->message=$message;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		
		$smessage=$this->message;
		$user=\App\Models\User::find($this->uid);
		$smessage['chat_id']=$user->telegram_id;
		//$smessage['text']='sometext';
		$q= file_get_contents('https://api.telegram.org/bot1400511618:AAFhsV1xuUOfwPSzOkAmqntVgLcu63WZv80/sendMessage?' . http_build_query($smessage));
		//TODO сделать что-то если он не сработал
		$tmessage=\App\Models\Tmessage::populateFromSendedmessage(json_decode($q,true)['result']);
		
        //var_dump($this->uid);
		//var_dump($this->message);
    }
}
