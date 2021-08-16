<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\SimplificationController;
use App\Http\Controllers\MapRendererController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::any('/telegramwebhook',[TelegramController::class,'webhook']);
Route::get('/{userslug}',[TrackController::class,'mymap']);
Route::get('/tracks/{id}',[TrackController::class,'singletrack']);

Route::get('/simplificator',[SimplificationController::class,'get']);

Route::get('/map_overlay/{uid}/{z}/{x}/{y}.png',[MapRendererController::class,'user_overlay']);



//https://api.telegram.org/bot1400511618:AAFhsV1xuUOfwPSzOkAmqntVgLcu63WZv80/setWebhook?url=https://tracks.lamastravels.in.ua/telegramwebhook
//