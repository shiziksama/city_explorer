<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Tmessage extends Model
{
    use HasFactory;
	public $user=null;
	public $timestamps = false;
	public function populateFromWebhook($data){
		if(empty($data)){
			return false;
		}
		$message=!empty($data['message'])?$data['message']:$data['edited_message'];
		$this->user=User::firstOrCreate(['telegram_id'=>$message['from']['id']],['data'=>json_encode($message['from'])]);
		file_put_contents(base_path('debug.txt'),json_encode($this->user,JSON_PRETTY_PRINT),FILE_APPEND);
		$this->uid=$this->user->id;
		$this->timestamp=!empty($message['edit_date'])?$message['edit_date']:$message['date'];
		$this->from=1;
		$this->update_id=$data['update_id'];
		$this->mid=$message['message_id'];
		$l=$message;
		unset($l['from']);
		unset($l['date']);
		$this->message=json_encode($l);
		$this->save();

	}
}
