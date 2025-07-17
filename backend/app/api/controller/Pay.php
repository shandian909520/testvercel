<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\ActiveIdentCode;
use app\admin\model\Orders;
use app\admin\model\SystemAdmin;
use app\admin\model\Users;
use app\api\service\NoticeService;
use app\api\service\ThirdPartyService as ServiceThirdPartyService;
use app\common\lib\wxApi;
use app\common\service\ThirdPartyService;
use Exception;
use think\facade\Db;
use think\facade\Log;

class Pay
{

    /**
     * 卡密支付
     */
    public function code_pay()
    {
        if (!input('post.order_id') || !input('post.code')) {
            return error('参数错误');
        }
        $userData = Users::where('id', request()->id)->find();
        $systemadminData = SystemAdmin::where(['id' => $userData['pf_id']])->find();
        $pf_id = $userData['pf_id'];
        if (empty($systemadminData['independent'])) {
            $pf_id = 1;
        }

        $order_id = input('post.order_id');
        $order = Orders::where('order_id', $order_id)->where('user_id', request()->id)->find();
        if (!$order || $order->status != 1) {
            return error('订单状态错误');
        }

        $code = input('post.code');
        Db::startTrans();
        try {
            $ident_code = ActiveIdentCode::where('code', $code)->lock(true)->where('status', 0)->find();
            if (!$ident_code) {
                return error('激活码错误！');
            }
            //更新激活码状态
            $ident_code->where('id', $ident_code->id)->update([
                'order_id' => $order_id,
                'user_id' => request()->id,
                'status' => 1,
            ]);
            //更新订单状态
            Orders::where('order_id', $order_id)->update([
                'pay_type' => 3,
                'status' => 2,
                'order_id' => $order_id
            ]);
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
                'component_phone' => sysconfig('base_config', 'service_phone' . $pf_id)
            ];
            $service = new ServiceThirdPartyService();
            if ($order->info['type'] == 1) {
                $return_data = $service->register_persion($postData, $pf_id);
            } else if ($order->info['type'] == 2) {
                $code = ['18' => 1, '9' => 2, '15' => 3];
                $postData['code_type'] = $code[$postData['code_type']];
                $return_data = $service->register_company($postData, $pf_id);
            } else {
                //管理员注册
                $code = ['18' => 1, '9' => 2, '15' => 3];
                $postData['code_type'] = $code[$postData['code_type']];
                $postData['openid'] = $order->info['openid'];
                $postData['xcxname'] = $order->info['xcxname'];
                Log::write("试用注册 参数" . json_encode($postData));
                $return_data = $service->register_fastregisterbetaweapp($postData, $pf_id);
                Log::write("管理员注册小程序结果" . json_encode($return_data));
            }
            if ($return_data['code'] == 1) {
                if ($order->info->type == 1) {
                    $order->error_msg = '请扫码验证！';
                    $order->success_url =   $return_data['data']['authorize_url'];
                    $order->taskid =   $return_data['data']['taskid'];
                } else if ($order->info->type == 2) {
                    $order->error_msg = '请法人确认验证信息!';
                } else {
                    $order->error_msg = '等待授权中！';
                    $order->success_url =   $return_data['data']['authorize_url'];
                    $order->unique_id =   $return_data['data']['unique_id'];
                }
                NoticeService::sendMsg($order, 1, '您注册的小程序已通过，等待验证！', $pf_id);
                $order->save();
            } else {
                $order->status = 3;
                $order->error_msg = $return_data['message'];
                NoticeService::sendMsg($order, 0, '您注册的小程序未通过，错误信息：' . $return_data['message'], $pf_id);
                $order->save();
            }
            Db::commit();
            return success('支付成功');
        } catch (Exception $e) {
            Db::rollback();
            return error('支付失败');
        }
    }
    /**
     * 微信支付
     */
    public function wx_pay()
    {

        if (!input('post.order_id')) {
            return error('参数错误');
        }
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        try {
            $order_id = input('post.order_id');
            $order = Orders::where('order_id', $order_id)->where('user_id', request()->id)->find();
            if (!$order || $order->status != 1) {
                return error('订单状态错误');
            }
            $type = request()->user->type == 1 ? 'xcx' : 'mp';
            $api = new wxApi($type, $pf_id);
            $notify_url = input('server.REQUEST_SCHEME') . '://' . request()->host() . '/api/wx_notify/pf_id/' . $pf_id;
            $res = $api->wxPay(request()->user->open_id, $order_id, $order->num, $notify_url);
            if (!empty($res['package'])) {
                return success('获取成功', $res);
            } else {
                return error($res['message'], $res);
            }
        } catch (Exception $e) {
            Log::write("微信支付异常__" . json_encode($e->getMessage()));
            return error('获取失败');
        }
    }

    /**
     * 微信回调
     */
    public function wx_notify()
    {
        $post = input('post.');
        $REQUEST_URI = explode('pf_id', $_SERVER['REQUEST_URI']);
        Log::write('Pay 微信回调 wx_notify 完成地址_____' . json_encode($REQUEST_URI));
        if (preg_match('/\d+/', $REQUEST_URI[count($REQUEST_URI) - 1], $pf_id)) {
            $pf_id = $pf_id[0];
        }
        Log::write('Pay 微信回调 wx_notify pf_id_____' . $pf_id);
        if ($post['event_type'] == "TRANSACTION.SUCCESS") {
            $data =   $post['resource'];
            $api = new wxApi('xcx', $pf_id);
            $res =     $api->decrpt($data['ciphertext'], $data['associated_data'], $data['nonce']);
            if (!$res) {
                $api = new wxApi('xcx', $pf_id);
                $res =     $api->decrpt($data['ciphertext'], $data['associated_data'], $data['nonce']);
            }
            Log::write('Pay 微信回调 wx_notify 解密结果_____+++++' . $res);
            if ($res) {
                $res = json_decode($res, true);

                if ($res['trade_state'] == 'SUCCESS') {
                    $order = Orders::where('order_id', $res['out_trade_no'])->find();
                    if ($order) {
                        if (intval($res['amount']['total']) == intval(bcmul('100', $order->num))) {
                            $order->pay_type = 2;
                            $order->status = 2;
                            $order->save();
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
                                'component_phone' => sysconfig('base_config', 'service_phone' . $pf_id)
                            ];
                            $service = new ServiceThirdPartyService();
                            if ($order->info['type'] == 1) {
                                $return_data = $service->register_persion($postData, $order['pf_id']);
                            } else if ($order->info['type'] == 2) {
                                $code = ['18' => 1, '9' => 2, '15' => 3];
                                $postData['code_type'] = $code[$postData['code_type']];
                                $return_data = $service->register_company($postData, $order['pf_id']);
                            } else {
                                //管理员注册
                                $code = ['18' => 1, '9' => 2, '15' => 3];
                                $postData['code_type'] = $code[$postData['code_type']];
                                $postData['openid'] = $order->info['openid'];
                                $postData['xcxname'] = $order->info['xcxname'];
                                Log::write("试用注册 参数" . json_encode($postData));
                                $return_data = $service->register_fastregisterbetaweapp($postData, $order['pf_id']);
                                Log::write("管理员注册小程序结果" . json_encode($return_data));
                            }
                            if ($return_data['code'] == 1) {
                                if ($order->info->type == 1) {
                                    $order->error_msg = '请扫码验证！';
                                    $order->success_url =   $return_data['data']['authorize_url'];
                                    $order->taskid =   $return_data['data']['taskid'];
                                } else if ($order->info->type == 2) {
                                    $order->error_msg = '请法人确认验证信息!';
                                } else {
                                    $order->error_msg = '等待授权中！';
                                    $order->success_url =   $return_data['data']['authorize_url'];
                                    $order->unique_id =   $return_data['data']['unique_id'];
                                }
                                NoticeService::sendMsg($order, 1, '您注册的小程序已通过，等待验证！', $order['pf_id']);
                                $order->faststatus = !empty($return_data['errcode']) ? $return_data['errcode'] : '';
                                $order->save();
                            } else {
                                $order->status = 3;
                                $order->faststatus = !empty($return_data['errcode']) ? $return_data['errcode'] : '';
                                $order->error_msg = $return_data['message'];
                                NoticeService::sendMsg($order, 0, '您注册的小程序未通过，错误信息：' . $return_data['message'], $order['pf_id']);
                                $order->save();
                            }
                        }
                    }
                }
            }
        }
        return json_encode(['code' => 'SUCCESS', 'message' => '成功', 'faststatus' => !empty($return_data['errcode']) ? $return_data['errcode'] : '']);
    }

    /**
     * 注册回调
     */
    public function register_callback()
    {
        if ((!input('post.taskid') && !input('post.code')) || !input('post.msg')) {
            return error('参数错误!');
        }
        $post = input('post.');
        $order = null;
        if (input('post.taskid')) {
            $order = Orders::where('taskid', $post['taskid'])->where('status', 2)->find();
            if (!$order) {
                return;
            }
        }
        if (input('post.code')) {
            $order = Orders::withJoin('info')->where('info.code', $post['code'])->where('orders.status', 2)->find();
            if (!$order) {
                return;
            }
        }
        $order->status = 3;
        $order->error_msg = $post['msg'];
        $order->save();
        return 'success';
    }
}
