<?php

declare(strict_types=1);

namespace app\api2\controller;

use think\facade\Db;
use app\admin\model\Orders;
use app\api\service\NoticeService;
use app\common\lib\wxApi;
use PHPQRCode\QRcode;

class Index
{

    //获取客服页面
    public function kefu_page()
    {
        $pf_id = input('pf_id');
        if (preg_match('/\d+/', $pf_id, $pf_id)) {
            $pf_id = $pf_id[0];
        }
        $qrlink = sysconfig('base_config', 'share_images' . $pf_id);
        $tel = sysconfig('base_config', 'kefu_company_ids' . $pf_id);
        $assign = [
            'qrlink' => $qrlink . '?v=' . rand(),
            'tel' => $tel,
        ];

        return view('', $assign);
    }
}
