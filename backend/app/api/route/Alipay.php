<?php

use app\middleware\ApiCheck;
use think\facade\Route;
use app\middleware\AuthCheck;
use app\middleware\WebApiCheck;

Route::any('gateway','alipay_api/gateway');
Route::any('alicb','alipay_api/alicb');
//获取类目
Route::any('/alipaymcc','alipay_api/alipaymcc');
Route::any('/aliIncomingParts','alipay_api/aliIncomingParts');
//进件开关
Route::any('/aliProStatus','alipay_api/aliProStatus');


Route::group('/alipay', function () {
    //微信回调
    Route::post('wx_notify', 'alipay_api/wx_notify');
})->middleware([WebApiCheck::class]);


Route::group('/alipay', function () {
    Route::any('test', 'alipay_api/test');
    //签约当面付产品 提交订单
    Route::post('aliincoming', 'alipay_api/aliincoming');
    //更新订单信息
    Route::post('updateDetail', 'alipay_api/updateDetail');
    //再次提交
    Route::post('subincoming', 'alipay_api/subincoming');
    //取消事务
    Route::post('agentCancel', 'alipay_api/agentCancel');
    //查询产品签约状态
    Route::post('agentOrderQuery', 'alipay_api/agentOrderQuery');
    //识别营业执照
    Route::post('uploadLocalAliBaiduapi', 'alipay_api/uploadLocalAliBaiduapi');
    //上传文件到本地
    Route::post('uploadLocal', 'alipay_api/uploadLocal');
    //卡密支付
    Route::post('code_pay', 'alipay_api/code_pay');
    Route::post('wx_pay', 'alipay_api/wx_pay');
    // 详情接口
    Route::post('detail', 'alipay_api/detail');



})->middleware([ApiCheck::class]);



// Route::group('/incoming', function () {
//     //识别营业执照
//     Route::post('idcard_info', 'InComing/idcard_info');
//     //选择主体类型
//     Route::post('select_merchant_type', 'InComing/select_merchant_type');
//     //填写主体信息
//     Route::post('subject_info', 'InComing/subject_info');
//     //填写经营信息
//     Route::post('business_info', 'InComing/business_info');
//     //填写法人信息
//     Route::post('legal_persion_info', 'InComing/legal_persion_info');
//     //填写银行信息
//     Route::post('bank_info', 'InComing/bank_info');

//     //卡密支付
//     Route::post('code_pay', 'InComing/code_pay');
//     //微信支付
//     Route::post('wx_pay', 'InComing/wx_pay');

//     //上传图片
//     Route::post('upload', 'InComing/upload_img');
//     //公众平台上传
//     Route::post('upload2', 'InComing/upload_img2');
// })->middleware([AuthCheck::class, ApiCheck::class]);




Route::miss(function () {
    return error('请求错误！');
});
