<?php

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

Route::group(['as'=>'autohac::', 'prefix'=>'autohac'], function() {
	Route::match(array('GET', 'POST'), '', 'AutohacController@getPostHome');
	Route::get('privacy', 'AutohacController@getPrivacy');
	Route::post('kik', 'AutohacController@postKik');
	Route::post('telegram', 'AutohacController@postTelegram');
});

