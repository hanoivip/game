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
    // server role wizard
    Route::get('/wizard/role', 'WizardController@chooseRole')->name('wizard.role');
    // recharge new flow
    Route::get('/newrecharge', 'NewFlow@startWizard')->name('newrecharge');
    Route::get('/newrecharge/shop', 'NewFlow@showShop')->name('newrecharge.shop');
    Route::any('/newrecharge/do', 'NewFlow@recharge')->name('newrecharge.do');
    Route::any('/newrecharge/done', 'NewFlow@rechargeDone')->name('newrecharge.done');
    Route::any('/newrecharge/refresh', 'NewFlow@query')->name('newrecharge.refresh');
    Route::get('/newhistory', 'NewFlow@history')->name('newhistory');
});

Route::middleware([
    'web',
    'admin'
])->namespace('Hanoivip\Game\Controllers')
->prefix('ecmin')
->group(function () {
    // New flow history
    Route::post('/newrecharge/history', 'Admin@history')->name('ecmin.newrecharge.history');
});