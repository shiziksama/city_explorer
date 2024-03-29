<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curpoint extends Model
{
    use HasFactory;
	public $timestamps = false;
	protected $fillable = [
        'lat',
        'lng',
        'timestamp',
        'uid',
		'mid',
		'horizontal_accuracy',
    ];
}
