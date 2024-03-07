<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web', 'auth:web')->namespace('Hanoivip\Game\Controllers')->group(function () {
    // Flow 1: topup web coin then transfer coin
    // Need setup Recharge
    Route::get('/recharge', 'GameController@recharge')->name('recharge');
    //Route::post('/recharge/result', 'GameController@doRecharge')->name('doRecharge');
    Route::get('/recharge/result-success', 'GameController@onRechargeSuccess')->name('recharge.success');
    Route::get('/recharge/result-fail', 'GameController@onRechargeFail')->name('recharge.fail');
    //Util: server role wizard, can save for further
    Route::get('/wizard/role', 'WizardController@chooseRole')->name('wizard.role');
});

Route::middleware('web')->namespace('Hanoivip\Game\Controllers')->group(function () {
    //Route::get('/server-list', 'GameController@serverlist')->name('server-list');
    Route::get('/rank', 'GameController@webGetRank')->name('rank');
});

Route::middleware([
    'web',
    'admin'
])->namespace('Hanoivip\Game\Controllers')
->prefix('ecmin')
->group(function () {
    // recharge for myself
    Route::get('/recharge', 'AdminController@recharge')->name('ecmin.recharge');
    // recharge for other player
    Route::get('/recharge4other', 'AdminController@recharge4other')->name('ecmin.recharge.other');
    Route::post('/recharge/do', 'AdminController@doRecharge')->name('ecmin.recharge.do');
    // manage servers..
    Route::get('/server', 'AdminController@serverInfo')->name('ecmin.server');
    Route::post('/server/remove', 'AdminController@removeServer')->name('ecmin.server.remove');
    Route::post('/server/add', 'AdminController@addServer')->name('ecmin.server.add');
});