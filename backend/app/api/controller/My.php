<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\Orders;
use app\admin\model\Users;
use app\api\service\ThirdPartyService as ServiceThirdPartyService;
use app\common\lib\wxApi;
use PHPQRCode\QRcode;
use app\common\service\ThirdPartyService;
use think\facade\Log;
use EasyAdmin\upload\Uploadfile;

class My
{
    /**
     * 我的
     */
    public function index()
    {
        $ids = Users::where('pid', request()->id)->column('id');

        $retail_num = Orders::whereIn('user_id', $ids)
            ->where('status', 3)
            ->where('pay_type', 2)
            ->where('retail_num', '>', 0)
            ->sum('retail_num');

        return success('查询成功', compact('retail_num'));
    }
    /**
     * 更新用户头像个昵称
     */
    public function updateNameHead()
    {
        $user = Users::where('id', request()->id)->find();
        $update = [];
        if (!empty(input('post.nickname'))) {
            $update['nickname'] = input('post.nickname');
        }
        if (!empty(input('post.head'))) {
            $update['head'] = input('post.head');
        }
        if (!empty($update)) {
            Users::where('id', request()->id)->update($update);
        }
        $user = Users::where('id', request()->id)->find();
        return success('用户信息', compact('user'));
    }

    /**
     * 我的团队
     */
    public function my_team()
    {
        $users = Users::where('pid', request()->id)
            ->field('id,nickname,head,create_time')
            ->select();
        $users = $users->append(['all_retail_num'])->toArray();
        return success('success', compact('users'));
    }

    /**
     * 我的订单
     */
    public function my_orders()
    {
        $orders = Orders::where(function ($query) {
            if (input('type')) {
                $query->where('status', input('type'));
            }
            $query->where('user_id', request()->id);
        })
            ->field('id,order_id,status,pay_type,num,create_time,info_id')
            ->select();
        $orders = $orders->append(['pay_type_name', 'status_name', 'info', 'code'])->toArray();
        return success('success', compact('orders'));
    }

    /**
     * 我的分销订单
     */
    public function my_retail_orders()
    {
        //
        $ids = Users::where('pid', request()->id)->column('id');
        //总收益
        $retail_num = Orders::whereIn('user_id', $ids)
            ->where('status', 3)
            ->where('pay_type', 2)
            ->where('retail_num', '>', 0)
            ->sum('retail_num');
        $orders = Orders::where(function ($query) use ($ids) {
            if (input('type') == 2) {
                $query->where('retail_status', '<>', 1);
            } else {
                if (input('type')) {
                    $query->where('retail_status', input('type'));
                }
            }
            $query->whereIn('user_id', $ids);
            $query->where('status', 3);
            $query->where('pay_type', 2);
            $query->where('retail_num', '>', 0);
        })
            ->field('id,order_id,retail_status,user_id,retail_num,create_time,info_id')
            ->select();
        foreach ($orders as $order) {
            $order['register_type'] = $order->info->getTypeList()[$order->info->type];
            $order['user_nickname'] = Users::where('id', $order->user_id)->value('nickname') ?: '用户';
            $order['user_head'] = Users::where('id', $order->user_id)->value('head') ?: '用户';
            unset($order['info']);
        }
        return success('success', compact('orders', 'retail_num'));
    }
    /**
     * 订单详情
     */
    public function order_info()
    {
        $id = input('order_id');
        $order = Orders::where('order_id', $id)
            ->where('user_id', request()->id)
            ->field('id,order_id,status,pay_type,num,create_time,info_id,success_url,error_msg,faststatus')
            ->find();
        if (!$order) {
            return error('该订单不存在!');
        }
        if ($order->success_url) {
            $order->success_url = qrcode_create($order->success_url, false);
        }
        $order = $order->append(['pay_type_name', 'status_name', 'info', 'code'])->toArray();
        return success('success', compact('order'));
    }

    //订单状态更新
    public function get_order_select()
    {
        $id = input('order_id');

        $order = Orders::where('order_id', $id)
            ->find();
        if (empty($id) || empty($order)) {
            return error('订单号错误,或订单不存在');
        }
        Log::write("订单状态更新get_order_select" . json_encode($order, JSON_UNESCAPED_UNICODE));
        $service = new ServiceThirdPartyService();
        $data = $service->get_xcx_process($order->taskid, $order->pf_id);
        Log::write("订单状态更新get_order_select 结果" . json_encode($data, JSON_UNESCAPED_UNICODE));
        if (!empty($data)) {
            $res = Orders::where('id', $id)->update(['error_msg' => $data['message']]);
        }
        return success('查询成功');
    }
    /**
     * 获取订单金额
     */
    public function get_order_num()
    {
        if (!input('post.order_id')) {
            return error('参数错误');
        }
        $order = Orders::where('order_id', input('post.order_id'))
            ->where('user_id', request()->id)
            ->find();
        if (!$order) {
            return error('订单号错误,或订单不存在');
        }
        return success('success', ['price' => $order->num]);
    }

    /**
     * 邀请好友
     */
    public function my_invite()
    {

        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        $invite_code = request()->user->invite_code;
        $img = sysconfig('retail_config', 'retail_image' . $pf_id) ?:  '/background-1.png';
        return success('success', compact('img', 'invite_code'));
    }

    /**
     * 获取用户信息
     */
    public function getUserInfo()
    {
        $res = [
            'nickname' => request()->user->nickname,
            'head' => request()->user->head,
        ];
        return success('成功', $res);
    }

    function uploadAvatar()
    {
        $data = [
            'upload_type' => '',
            'file'        => request()->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        validate()->check($data, $rule);
        try {
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
        if ($upload['save'] == true) {
            return success($upload['msg'], ['url' => $upload['url']]);
        } else {
            return error($upload['msg']);
        }
    }
}
