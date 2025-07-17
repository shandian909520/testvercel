<?php

declare(strict_types=1);

namespace app\api\controller\xcx;

use app\admin\model\OrderInfo;
use app\admin\model\Orders;
use app\api\service\ThirdPartyService;
use app\lib\wxBizMsgCrypt\Prpcrypt;
use DOMDocument;
use Exception;
use think\facade\Log;

class Authorize
{
    public function callback()
    {
        // $REQUEST_URI = explode('pf_id', $_SERVER['REQUEST_URI']);

        Log::write('Authorize callback完成地址1 _____' . json_encode($_SERVER['REQUEST_URI'], JSON_UNESCAPED_UNICODE));
        $REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
        $REQUEST_URI = $REQUEST_URI[0];
        $REQUEST_URI = explode('pf_id', $REQUEST_URI);
        Log::write('Authorize callback完成地址2 _____' . json_encode($REQUEST_URI));
        if (preg_match('/\d+/', $REQUEST_URI[count($REQUEST_URI) - 1], $pf_id)) {
            $pf_id = $pf_id[0];
        } else {
            Log::write('Authorize callback完成地址3 _____pf_id 错误');
            return;
        }
        try {
            $xml = file_get_contents("php://input");
            //解密
            $app_id = sysconfig('app_config', 'app_id' . $pf_id);
            $key = sysconfig('app_config', 'key' . $pf_id);

            $wx = new Prpcrypt($key);
            $xml_tree = new DOMDocument();
            $xml_tree->loadXML($xml);
            $array_e = $xml_tree->getElementsByTagName('Encrypt');
            $text = $array_e->item(0)->nodeValue;
            $res =  $wx->decrypt($text, $app_id);
            if ($res == '' || $res[0] != 0) {
                Log::error('授权事件接口 解密错误:错误编号：{code}', ['code' => $res]);
                return 'success';
            }
            $res_arr = xml_to_arr($res[1]);
            Log::write("全部回调 callback__" . json_encode($res_arr, JSON_UNESCAPED_UNICODE));
            if ($res_arr['InfoType'] == 'authorized') {
                if (in_array('AuthorizationCode', array_keys($res_arr))) {
                    $service = new ThirdPartyService();
                    $service->get_fast_xcx_access_token($res_arr['AuthorizationCode'], $pf_id);
                }
            }
            //是否是验证令牌
            if ($res_arr['InfoType'] == 'component_verify_ticket') {
                cache('ComponentVerifyTicket' . $pf_id, $res_arr['ComponentVerifyTicket'], 12 * 60 * 60 - 600);
            }
            //其他授权信息
            if ($res_arr['InfoType'] == 'notify_third_fasteregister') {
                Log::info(array($res_arr, 'data'));

                $msg = '';
                $data = [];
                Log::write("打印res_arr " . json_encode($res_arr, JSON_UNESCAPED_UNICODE));
                if ($res_arr['status'] == 0) {
                    $msg = '注册成功! appid:' . $res_arr['appid'];
                } else {
                    $msg = config('xcxstatus.' . $res_arr['status']);
                }
                Log::info(array($msg, 'msg'));
                $info = $res_arr['info'];
                if (in_array('taskid', array_keys($info))) {
                    $order = Orders::where('taskid', $info['taskid'])->where('status', 2)->find();
                    $order->status = 3;
                    $order->error_msg = $msg;
                    $order->faststatus = !empty($res_arr['status']) ? $res_arr['status'] : '';
                    $order->save();
                } elseif (in_array('code', array_keys($info))) {
                    $order = Orders::withJoin('info')->where([
                        'info.code' => $info['code'],
                        'name' => $info['name'], 'wx_code' => $info['legal_persona_wechat'],
                        'person_name' => $info['legal_persona_name']
                    ])->where('orders.status', 2)->order('id desc')->find();
                    $order->status = 3;
                    $order->error_msg = $msg;
                    $order->faststatus = !empty($res_arr['status']) ? $res_arr['status'] : '';
                    $order->save();
                }
                Log::write("授权回调 update order msg " . $msg);
                // Log::write('接收到 callback debug 10');
                // $url = 'http://' . $order->host . '/api/register_callback';
                // request_url($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE));
                // Log::info(array($url, 'ok'));
            }
            //试用小程序 注册成功 notify_third_fastregisterbetaapp
            if ($res_arr['InfoType'] == 'notify_third_fastregisterbetaapp') {
                Log::info(array($res_arr, 'data'));

                $msg = '';
                $data = [];
                if ($res_arr['status'] == 0) {
                    $msg = '注册成功! appid:' . $res_arr['appid'];
                } else {
                    $msg = config('weapp.' . $res_arr['status']);
                }
                Log::info(array($msg, 'msg'));
                $info = $res_arr['info'];
                if (in_array('unique_id', array_keys($info))) {
                    // $order = OrderInfo::where('taskid', $info['taskid'])->find();
                    $data = [
                        'msg' => $msg,
                        'unique_id' => $info['unique_id'],
                    ];
                    Log::info(array($data, 'postdata'));
                }

                if ($info['unique_id']) {
                    $order = Orders::where('unique_id', $info['unique_id'])->where('status', 2)->find();
                    if (!$order) {
                        return;
                    }
                }
                $order->status = 3;
                $order->faststatus = !empty($res_arr['status']) ? $res_arr['status'] : '';
                $order->appid = $res_arr['appid'];
                $order->error_msg = $msg;
                $order->save();
                // Log::write('接收到 callback debug 10');
                // $url = 'http://' . $order->host . '/api/register_callback';
                // request_url($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE));
                // Log::info(array($url, 'ok'));
                Log::write('接收到 callback debug 注册成功' . json_encode($res));
            }
            if ($res_arr['InfoType'] == 'notify_third_fastverifybetaapp') {
                Log::info(array($res_arr, 'data'));


                $msg = '';
                $data = [];
                if ($res_arr['status'] == 0) {
                    $msg = '转正确认成功! appid:' . $res_arr['appid'];
                } else {
                    $msg = config('weappr.' . $res_arr['status']);
                }
                Log::info(array($msg, 'msg'));
                $info = $res_arr['info'];
                // if (in_array('appid', array_keys($res_arr))) {
                //     // $order = OrderInfo::where('taskid', $info['taskid'])->find();
                //     $data = [
                //         'msg' => $msg,
                //         'appid' => $res_arr['appid'],
                //     ];
                //     Log::info(array($data, 'postdata'));
                // }
                Log::write('更新同步订单状态前：' . json_encode($res_arr, JSON_UNESCAPED_UNICODE));
                if ($res_arr['appid']) {
                    $order = Orders::where('appid', $res_arr['appid'])->where('status', 3)->find();
                    if (!empty($order)) {
                        $order->error_msg = $msg;
                        $order->faststatus = $res_arr['status'];
                        Log::write('更新同步订单状态', json_encode([$msg, $res_arr['status'], JSON_UNESCAPED_UNICODE]));
                    } else {
                        $order->error_msg = '订单错误 对应appid：' . $res_arr['appid'];
                        $order->faststatus = '';
                        Log::write('更新同步订单状态 error', '订单错误 对应appid：' . $res_arr['appid']);
                    }
                    $order->save();
                }
                // Log::write('接收到 callback debug 10');
                // $url = 'http://' . $order->host . '/api/register_callback';
                // request_url($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE));
                // Log::info(array($url, 'ok'));
            }


            //暂时没用到
            Log::save();
            return 'success';
        } catch (Exception $e) {
            Log::write('接收到 callback exception' . json_encode($e->getMessage()));
            return 'success';
        }
    }
}
