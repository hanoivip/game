<?php
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:api'
])->namespace('Hanoivip\Game\Controllers')
    ->prefix('api')
    ->group(function () {
    Route::any('/server/list', 'GameController@serverlist');
    Route::any('/user/role', 'GameController@queryRoles');
    Route::post('/choose-role', 'WizardController@continue');
    // done, let move on
    Route::post('/wizard/role', 'WizardController@continue')->name('wizard.role.next');
    Route::any('/game/recharge', 'GameController@doRecharge');
    Route::any('/user/all-role', 'GameController@allRoles');
});