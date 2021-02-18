<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::get('/line/user/{user_name}', 'UserController@get_line_user');

Route::post('/line/sendMessage', 'LineController@send_message');

Route::post('/line/sendUsers', 'LineController@send_to_users');

Route::post('/line/createBot', 'LineController@create_bot');

Route::post('/line/sendCustomMessage', 'LineController@send_custom_message');

Route::post('/line/sendMultiCustomMessage', 'LineController@send_multi_custom_message');

Route::post('/line/requestProfile', 'LineController@request_user_profile');