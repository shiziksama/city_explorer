<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackGetter extends Model
{
	protected $fillable = ['user_id','service'];
	public $timestamps=false;
	public function getData($index){
		$data=json_decode($this->data,true);
		return $data[$index]??'';
	}
	public function setData($index,$value){
		$data=json_decode($this->data,true);
		$data[$index]=$value;
		$this->data=json_encode($data);
	}
}
