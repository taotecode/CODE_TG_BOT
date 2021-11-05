<?php
// +----------------------------------------------------------------------
// | 路由设置
// +----------------------------------------------------------------------
use think\facade\Route;

Route::group('/', function () {
    Route::rule('get_share_code/:type', 'ShareCode/getList');


    Route::rule('receiveMessages','Telegram/receiveMessages');

})->ext('json');