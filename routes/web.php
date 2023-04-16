<?php

use Illuminate\Support\Facades\Route;

Route::middleware('web', 'auth:web')->namespace('Hanoivip\Game\Controllers')->group(function () {
    //Route::get('/server-list', 'GameController@serverlist')->name('server-list');
    //Route::get('/quick-play', 'GameController@quickplay');
    //Route::get('/play/svname/{svname}', 'GameController@play')->name('play');
    // Flow 1: topup indirect, then buying item
    Route::get('/recharge', 'GameController@recharge')->name('recharge');
    //Route::post('/recharge/result', 'GameController@doRecharge')->name('doRecharge');
    //Route::get('/recharge/result-success', 'GameController@onRechargeSuccess')->name('recharge.success');
    //Route::get('/recharge/result-fail', 'GameController@onRechargeFail')->name('recharge.fail');
    //Route::any('/recharge/role', 'GameController@webQueryRoles')->name('game.roles');
    // server role wizard
    // Route::get('/wizard/role', 'WizardController@chooseRole')->name('wizard.role');
    // Flow 2: topup direct, know what user will buy
    //Route::get('/newrecharge', 'NewFlow@startWizard')->name('newrecharge');
    Route::get('/newrecharge', 'NewFlow@start')->name('newrecharge');
    Route::any('/newrecharge/shop', 'NewFlow@showShop')->name('newrecharge.shop');
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
    // Module index
    Route::get('/newrecharge', 'Admin@index')->name('ecmin.newrecharge');
    // New flow history
    Route::post('/newrecharge/history', 'Admin@history')->name('ecmin.newrecharge.history');
    // Stats
    Route::get('/newrecharge/stat', 'Admin@stats')->name('ecmin.newrecharge.stats');
    Route::any('/newrecharge/stat/today', 'Admin@today')->name('ecmin.newrecharge.statsToday');
    Route::any('/newrecharge/stat/month', 'Admin@thisMonth')->name('ecmin.newrecharge.statsMonth');
    Route::post('/newrecharge/stat', 'Admin@statByTime')->name('ecmin.newrecharge.statsByTime');
    // Search
    Route::any('/newrecharge/receipt', 'Admin@viewReceipt')->name('ecmin.newrecharge.receipt');
    // Admin trigger a receipt callback
    Route::post('/newrecharge/receipt/retry', 'Admin@retry')->name('ecmin.newrecharge.receipt.retry');
});