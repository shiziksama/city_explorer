<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Tmessage extends Model
{
    use HasFactory;
	public $user=null;
	public $timestamps = false;
	protected $fillable = [
        'mid',
        'uid',
        'from',
		'update_id',
    ];
	/**
     * Set the keys for a save update query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
	 protected function setKeysForSaveQuery($query){
        $query->where('uid', '=', $this->uid);
        $query->where('mid', '=', $this->mid);
        return $query;
    }
	public static function populateFromSendedmessage($message){
		$user=User::where(['telegram_id'=>$message['chat']['id']])->first();
		$tmessage=Tmessage::firstOrNew(['mid'=>$message['message_id'],'uid'=>$user->id],['from'=>2,'update_id'=>0]);
		$tmessage->timestamp=$message['date'];
		$l=$message;
		unset($l['from']);
		unset($l['date']);
		unset($l['edit_date']);
		unset($l['chat']);
		unset($l['message_id']);
		$tmessage->message=json_encode($l);
		$tmessage->save();
		return $tmessage;
	}
	public static function populateFromWebhook($data){
		if(empty($data)){
			return false;
		}
		
		$message=!empty($data['message'])?$data['message']:$data['edited_message'];
		if($message['chat']['type']!='private')return false;//Не нужно по факту, но на всякий случай.
		
		$user=User::firstOrCreate(['telegram_id'=>$message['from']['id']],['data'=>json_encode($message['from'])]);
		$tmessage=Tmessage::firstOrNew(['mid'=>$message['message_id'],'uid'=>$user->id],['from'=>1]);
		$tmessage->user=$user;
		//file_put_contents(base_path('debug.txt'),json_encode($tmessage,JSON_PRETTY_PRINT),FILE_APPEND);
		//если новый, создать.
		//если старый, обновленный - обновить
		//если старый, но более старый, не обновлять, но отдать текущую версию
		
		
		
		$need_save=true;
		if($tmessage->exists&&$data['update_id']<=$tmessage->update_id){
			$need_save=false;
		}
		
		$tmessage->update_id=$data['update_id'];
		$tmessage->timestamp=!empty($message['edit_date'])?$message['edit_date']:$message['date'];
		
		$l=$message;
		unset($l['from']);
		unset($l['date']);
		unset($l['edit_date']);
		unset($l['chat']);
		unset($l['message_id']);
		$tmessage->message=json_encode($l);
		if($need_save){
			$tmessage->save();
		}
		return $tmessage;
	}
}
