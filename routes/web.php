<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web', 'auth:web')->namespace('Hanoivip\Game\Controllers')->group(function () {
    Route::get('/server-list', 'GameController@serverlist')->name('server-list');
    Route::get('/quick-play', 'GameController@quickplay');
    Route::get('/play/svname/{svname}', 'GameController@play')->name('play');
    
    Route::get('/recharge', 'GameController@recharge')->name('recharge');
    Route::post('/recharge/result', 'GameController@doRecharge')->name('doRecharge');
    Route::get('/recharge/result-success', 'GameController@onRechargeSuccess')->name('recharge.success');
    Route::get('/recharge/result-fail', 'GameController@onRechargeFail')->name('recharge.fail');
    Route::get('/recharge/role', 'GameController@queryRoles')->name('recharge.role');
    
    Route::get('/wizard/role', 'WizardController@chooseRole')->name('wizard.role');
});