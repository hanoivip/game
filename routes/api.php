<?php
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:api'
])->namespace('Hanoivip\Game\Controllers')
    ->prefix('api')
    ->group(function () {
    Route::any('/server/list', 'GameController@serverlist')->name('game.servers');
    Route::any('/user/role', 'GameController@queryRoles');
    //Wizard Roles old
    //Route::post('/choose-role', 'WizardController@continue');
    //Route::post('/wizard/role', 'WizardController@continue')->name('wizard.role.next');
    Route::any('/game/recharge', 'GameController@doRecharge')->name('game.recharge');
    Route::any('/user/all-role', 'GameController@queryRoles')->name('game.roles');
    // payment callback
    Route::any('/purchase/callback', 'NewFlow@rechargeDone');
    // payment history
    Route::any('/payment/history', 'NewFlow@history');
});