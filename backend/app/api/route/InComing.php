<?php

use app\middleware\ApiCheck;
use think\facade\Route;
use app\middleware\AuthCheck;
use app\middleware\WebApiCheck;

Route::group('/incoming', function () {
    //商户进件开关
    Route::get('pro_status', 'InComing/pro_status');
    //商户进件套餐
    Route::get('incoming_parts', 'InComing/incoming_parts');
    //商户进件套餐
    Route::get('incoming_config', 'InComing/incoming_config');
    //银行支行全称查询
    Route::get('bank_all_name', 'InComing/bank_all_name');
    //微信回调
    Route::post('wx_notify', 'InComing/wx_notify');
    //经营类目 入住结算规则、行业属性及特殊资质
    Route::get('settlement_list', 'InComing/gettlement_list');
    Route::get('test1', 'InComing/proapi');
    Route::get('gettlement_list', 'InComing/gettlement_list');
})->middleware([WebApiCheck::class]);



Route::group('/incoming', function () {
    //识别营业执照
    Route::post('idcard_info', 'InComing/idcard_info');
    //选择主体类型
    Route::post('select_merchant_type', 'InComing/select_merchant_type');
    //填写主体信息
    Route::post('subject_info', 'InComing/subject_info');
    //填写经营信息
    Route::post('business_info', 'InComing/business_info');
    //填写法人信息
    Route::post('legal_persion_info', 'InComing/legal_persion_info');
    //填写银行信息
    Route::post('bank_info', 'InComing/bank_info');

    //卡密支付
    Route::post('code_pay', 'InComing/code_pay');
    //微信支付
    Route::post('wx_pay', 'InComing/wx_pay');

    //上传图片
    Route::post('upload', 'InComing/upload_img');
    //公众平台上传
    Route::post('upload2', 'InComing/upload_img2');
})->middleware([WebApiCheck::class, ApiCheck::class]);




Route::miss(function () {
    return error('请求错误！');
});
