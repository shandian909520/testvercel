<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\Users;
use app\api\service\UserLoginService;
use app\common\lib\wxApi;

class Login
{
    /**
     * 登录
     */
    public function index()
    {

        $pf_if = input('pf_id', 0);
        $token = UserLoginService::login($pf_if);
        return success('登录成功', compact('token'));
    }
}
