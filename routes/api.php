<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Hanoivip\Game\Controllers')->prefix('api')->group(function () {
    
    // Xem danh sách máy chủ
    Route::get('/server/list', 'DirectGameController@serverlist');
    
    // Xem danh sách chuẩn bị mở cửa, .. bảo trì .. 
    Route::get('/server/schedule', 'DirectGameController@schedule');
    
    // Mua xu nhanh ( không đăng nhập )
    Route::get('/recharge/svname/{svname}/user/{user}/coin/{coin}', 'DirectGameController@recharge');
    
    // Xem trạng thái các máy chủ
    Route::get('/server/status', 'DirectGameController@serverstatus');
    
    // Xem các máy chủ đăng nhập gần đây
    Route::get('/server/recent/user/{user}', 'DirectGameController@serverrecent');
    
    // Xem dữ liệu xếp hạng game.
    Route::get('/rank/server/{server}', 'DirectGameController@rank');

});