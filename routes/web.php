<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TelegramController;
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
Route::any('telegramwebhook',[TelegramController::class,'webhook']);

//https://api.telegram.org/bot1400511618:AAFhsV1xuUOfwPSzOkAmqntVgLcu63WZv80/setWebhook?url=https://tracks.lamastravels.in.ua/telegramwebhook
//