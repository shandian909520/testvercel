<?php

namespace app\api\service;

use app\admin\model\Users;
use app\common\lib\wxApi;
use think\exception\ValidateException;

class UserLoginService
{

    public static function login($pf_id)
    {
        $type = input('post.type');
        if (empty($type)) $type = 1;
        $token = '';
        switch ($type) {
            case 1:
                $token =  UserLoginService::xcx_login($pf_id);
                break;
            case 2:
                $token = UserLoginService::mp_login($pf_id);
                break;
        }
        return $token;
    }


    /**
     * 小程序登录
     */
    public static function xcx_login($pf_id)
    {
        $service  = new wxApi('xcx', $pf_id);
        $res = $service->get_open_id(input('post.code'));
        if (empty($res)) throw new ValidateException('code 错误');
        $open_id = $res['open_id'];

        $user = Users::where('open_id', $open_id)->where('type', 1)->find();
        if (empty($user)) {
            $pid = 0;
            if (input('post.invite_code')) {
                $pid = Users::where('invite_code', input('post.invite_code'))->value('id');
            }
            $user = Users::create([
                'open_id' => $open_id,
                'type' => 1,
                'invite_code' => '',
                'nickname' => '',
                'head' => 'z',
                'pid' => $pid ?: 0,
                'pf_id' => $pf_id,
                'check_name_times' => sysconfig('check_name', 'check_name_times' . $pf_id)
            ]);
            $user->generate_invite_code();
        }
        $token =  $user->generate_token();
        return ['token' => $token, 'nickname' => $user['nickname'], 'head' => $user['head']];
    }

    /**
     * 公众号登录
     */
    public  static function mp_login($pf_id)
    {
        if (empty(input('post.code'))) throw new ValidateException('请先获取授权');

        $service  = new wxApi('mp', $pf_id);
        $res = $service->get_open_id(input('post.code'));
        if (empty($res)) throw new ValidateException('code 错误');
        $open_id = $res['open_id'];

        $userinfo = $service->get_user_info($res['open_id'], $res['access_token']);

        $user = Users::where('open_id', $open_id)->where('type', 2)->find();
        if ($user) {
            $user->nickname = $userinfo['nickname'];
            $user->head = $userinfo['headimgurl'];
            $user->save();
        } else {
            $pid = 0;
            if (input('post.invite_code')) {
                $pid = Users::where('invite_code', input('post.invite_code'))->value('id');
            }
            $user = Users::create([
                'open_id' => $open_id,
                'type' => 2,
                'invite_code' => '',
                'nickname' => $userinfo['nickname'],
                'head' =>  $userinfo['headimgurl'],
                'pid' => $pid ?: 0,
                'pf_id' => $pf_id,
                'check_name_times' => sysconfig('check_name', 'check_name_times')
            ]);
            $user->generate_invite_code();
        }
        $token =  $user->generate_token();
        # code...
        return $token;
    }
}
