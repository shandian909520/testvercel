<?php

declare(strict_types=1);

namespace app\middleware;

use app\admin\model\Users;

class ApiCheck
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return 
     */
    public function handle($request, \Closure $next)
    {
        $token = request()->header('token');
        if (!$token) {
            return error('登录失效,请重新登录', '', 4);
        }
        $user = Users::where('token', $token)->find();
        if (!$user) {
            return error('登录失效,请重新登录', '', 4);
        }
        $request->user = $user;
        $request->id = $user->id;

        return $next($request);
    }
}
