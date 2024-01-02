<?php
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:api'
])->namespace('Hanoivip\Game\Controllers')
    ->prefix('api')
    ->group(function () {
    Route::any('/server/list', 'GameController@serverlist')->name('game.servers');
    Route::middleware('cacheByUser:60')->any('/user/role', 'GameController@queryRoles');
    Route::middleware('lockByUser:10,5')->any('/game/recharge', 'GameController@doRecharge')->name('game.recharge');
    Route::middleware('cacheByUser:60')->any('/user/all-role', 'GameController@queryRoles')->name('game.roles');
    // payment callback
    //Route::any('/purchase/callback', 'NewFlow@rechargeDone');
    // payment history
    //Route::any('/payment/history', 'NewFlow@history');
    Route::any('/wizard/save', 'WizardController@saveDefaultRole')->name('wizard.save');
});
    
Route::namespace('Hanoivip\Game\Controllers')
->prefix('api')
->group(function () {
    // 1 day cache
    Route::middleware('cache:1440')->any('/game/rank', 'GameController@getRank')->name('game.rank');
});