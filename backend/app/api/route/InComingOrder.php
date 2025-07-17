<?php

use app\middleware\ApiCheck;
use app\middleware\AuthCheck;
use app\middleware\WebApiCheck;
use think\facade\Route;


Route::group('/incoming_order', function () {
    //订单列表
    Route::post('list', 'InComingOrder/list');
    //订单详情
    Route::post('detail', 'InComingOrder/detail');
    //分销订单
    Route::post('retail_list', 'InComingOrder/retail_list');
})->middleware([WebApiCheck::class, ApiCheck::class]);



Route::miss(function () {
    return error('请求错误！');
});
