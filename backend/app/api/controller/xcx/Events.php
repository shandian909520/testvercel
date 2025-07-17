<?php

declare(strict_types=1);

namespace app\api\controller\xcx;

use app\admin\model\Orders;
use app\lib\wxBizMsgCrypt\Prpcrypt;
use DOMDocument;
use think\facade\Log;

class Events
{
    public function callback()
    {
        Log::write('Events callback完成地址1 _____' . json_encode($_SERVER['REQUEST_URI'], JSON_UNESCAPED_UNICODE));
        $REQUEST_URI = explode('?', $_SERVER['REQUEST_URI']);
        $REQUEST_URI = $REQUEST_URI[0];
        $REQUEST_URI = explode('pf_id', $REQUEST_URI);
        Log::write('Events callback完成地址2 _____' . json_encode($REQUEST_URI));
        if (preg_match('/\d+/', $REQUEST_URI[count($REQUEST_URI) - 1], $pf_id)) {
            $pf_id = $pf_id[0];
        } else {
            Log::write('Events callback完成地址3 _____pf_id 错误');
            return;
        }
        $xml = file_get_contents("php://input");
        // Log::write('eventCallback_xml___' . json_encode($xml));
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
            // Log::write('授权事件接口 解密错误:错误编号：{code}', ['code' => $res]);
            return 'success';
        }
        $res_arr = xml_to_arr($res[1]);
        if ($res_arr['MsgType'] == 'event') {
            if ($res_arr['Event'] == 'wxa_nickname_audit') {
                if (!empty($res_arr['ToUserName'])) {
                    $order = Orders::where('gh_id', $res_arr['ToUserName'])->find();
                    if (!empty($order)) {
                        if (!empty($res_arr['ret']) && $res_arr['ret'] == 3) {
                            $order->save(['error_msg' => '审核成功']);
                        } else {
                            $order->save(['error_msg' => '审核失败：' . $res_arr['reason']]);
                        }
                    }
                }
            }
        }

        Log::write('消息与事件接收推送解密结果' . json_encode($res_arr, JSON_UNESCAPED_UNICODE));
        //暂时没用到
        return 'success';
    }
}
