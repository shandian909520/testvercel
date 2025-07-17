<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\AliOrders;
use app\admin\model\InComingOrder as ModelInComingOrder;
use app\admin\model\Users;
use app\common\lib\ProApi;

class InComingOrder
{
    /**
     * 列表
     */
    public function list()
    {
        $type = input('type'); //0：全部，1：未支付，2：进行中，3：已完成
        $wechat = input('wechat'); //1：微信订单列表，2:支付宝订单列表
        if (!empty($wechat) && $wechat == 2) {
            $list = AliOrders::where('user_id', request()->id)->where(
                function ($query) use ($type) {
                    if ($type == 1) {
                        $query->where(['ea_ali_orders.status' => $type]);
                    }
                    if ($type == 2) {
                        $query->whereIn('ea_ali_orders.status', '2,3,4,5,6,8');
                    }
                    if ($type == 3) {
                        $query->whereIn('ea_ali_orders.status', '7,9');
                    }
                }
            )->field('id,order_id,num,status,user_id,pay_type,create_time,sub_msg')->append(['aliOrdersInfo', 'code'])->select();
        } else {
            $list = ModelInComingOrder::where('user_id', request()->id)->where(
                function ($query) use ($type) {
                    if (!empty($type)) {
                        $query->where(['ea_incoming_order.status' => $type]);
                    }
                }
            )->field('id,order_id,num,status,user_id,pay_type,create_time,error_msg')->append(['orderShortInfo', 'code'])->select();
        }

        return success('成功', compact('list'));
    }

    /**
     * 详情
     */
    public function detail()
    {
        if (empty(input('post.order_id'))) return error('参数错误');

        $order = ModelInComingOrder::where('order_id', input('post.order_id'))
            ->where('user_id', request()->id)
            ->append(['orderInfo', 'code'])
            ->find();
        if (empty($order)) {
            return error('订单不存在或状态错误！');
        }
        $order->orderInfo['contact_type'] = empty($order->orderInfo['contact_type']) ? "SUPER" : "LEGAL";
        if (in_array($order->status, [2, 4])) {
            if ($order->applyment_id) {
                $user = Users::where('id', request()->id)->find();
                $pf_id = $user['pf_id'];
                $api = new ProApi($pf_id);
                $res =  $api->get_register_status('applyment_id', $order->applyment_id);
                if (is_array($res)) {
                    if ($res['applyment_state'] == 'APPLYMENT_STATE_FINISHED') {
                        $order->save([
                            'status' => 3,
                            'error_msg' => $res['applyment_state_msg'] . 'sub_mchid:' . $res['sub_mchid']
                        ]);
                    } else {
                        $order->save([
                            'error_msg' => $res['applyment_state_msg']
                        ]);
                    }
                }
                $order->applyment_result = $res;
            } else {
                $order->applyment_result = [];
            }
        } else {
            $order->applyment_result = [];
        }
        return success('成功', $order);
    }

    /**
     * 我的分销订单
     */
    public function retail_list()
    {
        //
        $ids = Users::where('pid', request()->id)->column('id');
        //总收益
        $retail_num = ModelInComingOrder::whereIn('user_id', $ids)
            ->where('status', 3)
            ->where('pay_type', 2)
            ->where('retail_num', '>', 0)
            ->sum('retail_num');
        $orders = ModelInComingOrder::where(function ($query) use ($ids) {
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
            ->field('id,order_id,retail_status,user_id,retail_num,create_time')
            ->select();
        foreach ($orders as $order) {
            $order['register_type'] = config('inComing.subject_type')[$order->orderShortInfo->subject_type];
            $order['user_nickname'] = Users::where('id', $order->user_id)->value('nickname') ?: '用户';
            $order['user_head'] = Users::where('id', $order->user_id)->value('head') ?: '用户';
            unset($order['info']);
        }
        return success('success', compact('orders', 'retail_num'));
    }
}
