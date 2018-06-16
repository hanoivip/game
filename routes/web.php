<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web', 'auth:web' /*'auth:token'*/)->namespace('Hanoivip\Game\Controllers')->group(function () {
    
    Route::get('/server-list', 'GameController@serverlist')->name('server-list');
    Route::get('/quick-play', 'GameController@quickplay');
    Route::get('/play/svname/{svname}', 'GameController@play')->name('play');
    Route::get('/recharge', 'GameController@recharge')->name('recharge');
    Route::post('/doRecharge', 'GameController@doRecharge')->name('doRecharge');

});