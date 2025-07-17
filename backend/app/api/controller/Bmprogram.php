<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\OrderInfo;
use app\admin\model\Orders;
use app\admin\model\SystemAdmin;
use app\admin\model\Users;
use app\api\service\NoticeService;
use app\api\service\ThirdPartyService;
use think\Exception;
use think\facade\Db;
use think\facade\Log;

/**
 * 注册试用 快速认证 小程序
 * 
 */
class Bmprogram
{

    /**
     * 注册试用小程序
     * https://developers.weixin.qq.com/doc/oplatform/openApi/OpenApiDoc/register-management/fast-regist-beta/registerBetaMiniprogram.html
     * 
     */
    public function register()
    {
        $post = input('post.');
        if (
            !input('post.name') || !input('post.wx_code') || !input('post.person_name')
            || !input('post.code_type') || !input('post.code') || !input('post.xcxname') || !input('legal_persona_idcard')
        ) {
            return error('参数错误');
        }
        if (!isset($post['register_status'])) {
            $post['register_status'] = sysconfig('base_config', 'register_status');
        }
        $userData = Users::where('id', request()->id)->find();
        $systemadminData = SystemAdmin::where(['id' => $userData['pf_id']])->find();
        $pf_id = $userData['pf_id'];
        if (empty($systemadminData['independent'])) {
            $pf_id = 1;
        }
        $flag = sysconfig('base_config', 'register_status' . $pf_id);
        $num = sysconfig('base_config', 'register_num' . $pf_id);
        $post['type'] = 3;
        $post['openid'] = request()->user->open_id;
        if ($flag && $num > 0) {
            $order =   Orders::createOrder(0, request()->id,  1, $post, $num, $pf_id, $userData['pf_id']);
        } else {
            //当免费时
            $order = Orders::createOrder(0,  request()->id, 2, $post, 0, $pf_id, $userData['pf_id']);
            $code = ['18' => 1, '9' => 2, '15' => 3];
            $postData = [
                'host' => request()->host(),
                'order_id' => $order->order_id,
                'type' => $order->info['type'],
                'name' => $order->info['name'],
                'code_type' => $order->info['code_type'],
                'code' => $order->info['code'],
                'wx_code' => $order->info['wx_code'],
                'person_name' => $order->info['person_name'],
                'auth_code' => '',
                'component_phone' => sysconfig('base_config', 'service_phone')
            ];
            $postData['code_type'] = $code[$postData['code_type']];
            $postData['openid'] = request()->user->open_id;
            $postData['xcxname'] = $post['xcxname'];
            Log::write("试用注册 参数" . json_encode($postData));
            $service = new ThirdPartyService();
            $res = $service->register_fastregisterbetaweapp($postData, $pf_id);
            Log::write("管理员注册小程序结果" . json_encode($res));
            if ($res['code'] == 1) {
                $order->error_msg = '小程序创建成功!';
                $order->success_url =   $res['data']['authorize_url'];
                $order->unique_id =   $res['data']['unique_id'];
                NoticeService::sendMsg($order, 1, '您注册的小程序已通过，等待验证！', $pf_id);
                $order->save();
            } else {
                $order->status = 3;
                $order->error_msg = $res['message'];
                NoticeService::sendMsg($order, 0, '您注册的小程序未通过，错误信息：' . $res['message'], $pf_id);
                $order->save();
            }
        }
        return success('提交成功', ['order_id' => $order->order_id, 'status' => $order->status]);
    }

    /**
     * 确认转正接口
     */
    function verfifyBetaMiniprogram()
    {
        $id = input('post.id');
        $order = Orders::where('id', $id)->find();
        if (empty($order) || $order->status != 3 || (!empty($order->error_msg && strpos($order->error_msg, '转正成功') !== false))) {
            return error('订单状态错误!');
        }
        $userData = Users::where('id', request()->id)->find();
        $systemadminData = SystemAdmin::where(['id' => $userData['pf_id']])->find();
        $pf_id = $userData['pf_id'];
        if (empty($systemadminData['independent'])) {
            $pf_id = 1;
        }
        if ($order->status == 3) {
            //试用小程序转正接口
            $postData = [
                'enterprise_name' => $order->info['name'],
                'code' => $order->info['code'],
                'code_type' => $order->info['code_type'],
                'legal_persona_wechat' => $order->info['wx_code'],
                'legal_persona_name' => $order->info['person_name'],
                'legal_persona_idcard' => $order->info['legal_persona_idcard'],
                'component_phone' => sysconfig('base_config', 'service_phone'),
                'appid' => $order['appid'],
            ];
            $service = new ThirdPartyService();
            $res = $service->register_verifybetaweapp($postData, $pf_id);
            Log::write('转正返回结果' . json_encode($res, JSON_UNESCAPED_UNICODE));
            if ($res['code'] == 1) {
                Orders::where('id', $id)->update(['error_msg' => $res['message'], 'faststatus' => '']);
                return success($res['message']);
            } else {
                Orders::where('id', $id)->update(['error_msg' => $res['message'], 'faststatus' => $res['errcode']]);
                return error($res['message']);
            }
        } else {
            return error('订单状态非注册成功或状态非完成');
        }
    }
    /**
     * 小程序更名
     */
    function setbetaweappnickname()
    {
        $post = input('post.');
        if (empty($post['id']) || empty($post['xcxname'])) {
            return error('参数错误');
        }
        $order = Orders::where('id', $post['id'])->find();
        if (empty($order) || empty($order['appid'])) {
            return error('appid错误');
        }
        $userData = Users::where('id', request()->id)->find();
        $systemadminData = SystemAdmin::where(['id' => $userData['pf_id']])->find();
        $pf_id = $userData['pf_id'];
        if (empty($systemadminData['independent'])) {
            $pf_id = 1;
        }
        $service = new ThirdPartyService();
        //是否设置了原始gh id 未设置 则设置
        if (empty($order['gh_id'])) {
            $authorizerInfo = $service->api_get_authorizer_info($order['appid'], $pf_id);
            if (!empty($authorizerInfo['authorizer_info']['user_name'])) {
                $order->save(['gh_id' => $authorizerInfo['authorizer_info']['user_name']]);
            } else {
                return error('原始id 错误');
            }
        }
        try {
            $info = OrderInfo::where('id', $order['info_id'])->find();
            $info->save($post);
        } catch (\Exception $e) {
            return error('更名失败' . $e->getMessage());
        }
        $skdata = [
            'appid' => $order['appid'],
            'nick_name' => $post['xcxname'],
            'license' => $post['license'],
            'naming_other_stuff_1' => !empty($post['naming_other_stuff_1']) ? $post['naming_other_stuff_1'] : '',
            'naming_other_stuff_2' => !empty($post['naming_other_stuff_2']) ? $post['naming_other_stuff_2'] : '',
            'naming_other_stuff_3' => !empty($post['naming_other_stuff_3']) ? $post['naming_other_stuff_3'] : '',
            'naming_other_stuff_4' => !empty($post['naming_other_stuff_4']) ? $post['naming_other_stuff_4'] : '',
            'naming_other_stuff_5' => !empty($post['naming_other_stuff_5']) ? $post['naming_other_stuff_5'] : '',
        ];
        $res = $service->setnickname($skdata, $pf_id);
        $order->save(['error_msg' => $res['message']]);
        if ($res['code'] == 1) {
            try {
                $order->save(['xcxname' => $post['xcxname']]);
            } catch (\Exception $e) {
                return error('更名失败' . $e->getMessage());
            }
            return success($res['message']);
        } else {
            return error($res['message']);
        }
    }
    /**
     * 更新订单信息
     */
    public function upOrderInfo()
    {
        $post = input('post.');
        $id = $post['id'];
        if (empty($id)) {
            return error('订单错误');
        }
        $update = [];
        if (!empty($post['name'])) {
            $update['name'] = $post['name'];
        }
        if (!empty($post['code_type'])) {
            $update['code_type'] = $post['code_type'];
        }
        if (!empty($post['code'])) {
            $update['code'] = $post['code'];
        }
        if (!empty($post['wx_code'])) {
            $update['wx_code'] = $post['wx_code'];
        }
        if (!empty($post['person_name'])) {
            $update['person_name'] = $post['person_name'];
        }
        if (!empty($post['xcxname'])) {
            $update['xcxname'] = $post['xcxname'];
        }
        if (!empty($post['legal_persona_idcard'])) {
            $update['legal_persona_idcard'] = $post['legal_persona_idcard'];
        }
        if (!empty($update)) {
            $res = OrderInfo::where('id', $id)->update($update);
            if ($res) {
                return success('转正成功');
            }
        }
        return error('修改错误');
        // `name`  '名称',
        // `code_type`  '企业代码类型 {select} (1:统一社会信用代码（18 位）,2:组织机构代码（9 位）,3:营业执照注册号（15 位）)',
        // `code`  '企业代码',
        // `wx_code`  '微信号|法人微信号',
        // `person_name`  '法人姓名',
        // `legal_persona_idcard`  '法人身份证',

    }
}
