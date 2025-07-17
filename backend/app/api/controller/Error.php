<?php

declare(strict_types=1);

namespace app\api\controller;

class Error
{
    public function index()
    {
        return error('接口错误');
    }
}
