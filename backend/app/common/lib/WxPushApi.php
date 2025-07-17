<?php

namespace app\common\lib;


class WxPushApi
{
    private $app_id;
    private $app_secret;
    private $url;

    public function __construct()
    {
        $this->url = sysconfig('push_config', 'push_api_url');
        $this->app_id = sysconfig('wx_config', 'wx_app_id');
        $this->app_secret = sysconfig('wx_config', 'wx_app_secret');
    }

    /**
     * 获取授权码
     */
    public function get_wx_qr_code()
    {
        $url = $this->url . '/api/login?host=' . request()->host();
        $res = request_url($url, 'get');
        return $res;
    }

    /**
     * 是否登录
     */
    public function is_login($request_id)
    {
        $url = $this->url . '/api/is_login?host=' . request()->host() . '&request_id=' . $request_id;
        $res = request_url($url, 'get');
        if ($res['code'] == 1) {
            return $res['data']['login'];
        }
        return false;
    }

    /**
     * 是否登录
     */
    public function upload($request_id, $ident, $version, $desc)
    {
        $url = $this->url . '/api/upload';
        $postData = [
            'host' => request()->host(),
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'ident' => $ident,
            'version' => $version,
            'desc' => $desc,
            'request_id' => $request_id
        ];
        $res = request_url($url, 'post', json_encode($postData));
        return $res;
    }
}
