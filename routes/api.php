<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['token'])->namespace('Hanoivip\Game\Controllers')->prefix('api')->group(function () {
    Route::get('/server/list', 'GameController@serverlist');
    Route::get('/user/role', 'GameController@queryRoles');
    Route::post('/choose-role', 'WizardController@continue');
    // done, let move on
    Route::post('/wizard/role', 'WizardController@continue')->name('wizard.role.next');
});