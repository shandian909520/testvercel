<?php

use app\middleware\ApiCheck;
use think\facade\Route;


Route::any('/authorize/callback', 'xcx.authorize/callback');
Route::any('/events/callback', 'xcx.events/callback');
Route::any('/index/auth', 'xcx.index/auth');


//测试接口
Route::any('/', 'index/index');
Route::any('/test', 'index/test');
Route::any('/test1', 'index/getrid');
Route::any('/authorizer_list', 'index/authorizer_list');
Route::any('/index/get_pian', 'index/get_pian');
Route::any('/index/xcx_pian_status', 'index/get_xcx_pian_status');
Route::any('/index/wx_pay_status', 'index/get_wx_pay_status');
//首页
Route::post('/home', 'index/index');
//登录
Route::post('/login', 'login/index');
//客服
Route::post('/kefu', 'index/kefu');
//客服链接
Route::post('/kefu_page', 'index/kefu_page');
//banner
Route::post('/banner', 'index/banner');
//获取广告
Route::post('/ad', 'index/ad_config');
//分享
Route::post('/share', 'index/share');
//百度配置
Route::post('/baidu_config', 'index/baidu_config');
//卡密配置
Route::post('/code_status', 'index/code_status');
//注册金额
Route::post('/register_amount', 'index/register_amount');
//获取小程序码
Route::post('/get_xcx_qrcode', 'index/get_xcx_qrcode');

//查看核名次数
Route::post('/check_name_times', 'register/check_name_times');
//注册回调
Route::post('/register_callback', 'pay/register_callback');

//获取公众号信息
Route::post('/mp_info', 'index/mp_info');
//文章帮助
Route::get('/article', 'article/index');
//隐私协议
Route::get('/pagreement', 'article/pagreement');

//wx支付回调
Route::post('/wx_notify', 'pay/wx_notify');
Route::any('/update/checkversion', 'update/checkversion');
Route::any('/update/start_upgrade', 'update/start_upgrade');
Route::any('/xcxupcode', 'update/xcxupcode');
Route::any('/index_title', 'index/index_title');

//获取区域
Route::any('/getRegion', 'index/getRegion');

Route::group('/', function () {
    //我的
    Route::post('/getUserInfo', 'my/getUserInfo');
    //识别营业执照
    Route::post('/discren_pic', 'register/discren_pic');
    //提交个人信息
    Route::post('/person', 'register/person');
    //提交企业信息
    Route::post('/company', 'register/company');
    //核名
    Route::post('/check_name', 'register/check_name');

    //核名
    Route::post('/look_ad', 'register/look_ad');
    //获取订单金额
    Route::post('/get_order_price', 'my/get_order_num');
    //订单状态查询
    Route::post('/get_order_select', 'my/get_order_select');
    //卡密支付
    Route::post('/code_pay', 'pay/code_pay');
    //wx支付
    Route::post('/wx_pay', 'pay/wx_pay');
    //我的
    Route::post('/my', 'my/index');
    //我的团队
    Route::post('/my_team', 'my/my_team');
    //我的订单
    Route::post('/my_orders', 'my/my_orders');
    //分销订单
    Route::post('/my_retail_orders', 'my/my_retail_orders');
    //订单详情
    Route::post('/order_info', 'my/order_info');
    //我的邀请
    Route::post('/my_invite', 'my/my_invite');

    //注册试用小程序
    Route::post('/regbm', '/bmprogram/register');
    //试用小程序快速认证
    Route::post('/verbm', '/bmprogram/verfifyBetaMiniprogram');
    //更新订单信息
    Route::post('/upOrderInfo', '/bmprogram/upOrderInfo');
    //试用小程序转正
    Route::get('/verifybetaweapp', '/index/verifybetaweapp');
    //小程序更名
    Route::post('/setweappname', '/bmprogram/setbetaweappnickname');
    //更新用户头像昵称
    Route::post('/updateNameHead', '/my/updateNameHead');
    //上传头像
    Route::post('/uploadAvatar', '/my/uploadAvatar');
})->middleware(ApiCheck::class);




Route::miss(function () {
    return error('请求错误！');
});
