<?php

namespace app\api\service;

use app\common\lib\wxApi;

class NoticeService
{
    public static function sendMsg($order, $flag, $text, $pf_id)
    {
        if (sysconfig('notice_config', 'notice_admin_status')) {
            $api = new wxApi('xcx', $pf_id);
            $open_ids = array_filter(explode('|', sysconfig('notice_config', 'notice_person' . $pf_id)));
            //给管理员发送
            foreach ($open_ids as $open_id) {
                $api->send_notice($open_id, $flag, '用户' . $order->user->nickname . ':' . $text);
            }
            //给用户发送
            if ($order->user) {
                if (sysconfig('notice_config', 'notice_success_status' . $pf_id) && $flag) {
                    $api->send_notice($order->user->open_id, $flag, $text);
                }
                if (sysconfig('notice_config', 'notice_error_status' . $pf_id) && !$flag) {
                    $api->send_notice($order->user->open_id, $flag, $text);
                }
            }
        }
    }
}
