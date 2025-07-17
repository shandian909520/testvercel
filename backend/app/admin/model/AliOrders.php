<?php

namespace app\admin\model;

use app\common\model\TimeModel;

class AliOrders extends TimeModel
{

    protected $name = "ali_orders";

    protected $deleteTime = false;
    public function setOrderIdAttr()
    {
        $order_id = '';
        do {
            $order_id = date('YmdHis') . random_str(8, 3);
        } while (AliOrders::where('order_id', $order_id)->find());
        return $order_id;
    }


    public function getPayTypeList()
    {
        return ['1' => '平台', '2' => '微信', '3' => '卡密',];
    }


    // 1 未支付
    // 2 待创建事务
    // 3: 事务创建成功
    // 4: 签约成功
    // 5: 已提交事务
    // 6: 审核中
    // 7: 商户已拒绝
    // 8: 等待商家签约
    // 9: --
    public function getStatusList()
    {
        return [
            '1' => '未支付', '2' => '待创建事务', '3' => '事务创建成功',
            '4' => '签约成功', '5' => '已提交事务', '6' => '审核中',
            '7' => '商户已拒绝', '8' => '等待商家签约', '9' => '其他',
        ];
    }
    public function getSetailStatus()
    {
        return ['0' => '无', '1' => '成功', '2' => '失败',];
    }
    function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }
    function aliOrdersInfo()
    {
        return $this->hasOne(AliOrdersInfo::class,  'ali_orders_id');
    }
    public function getCodeAttr()
    {
        return ActiveIdentCode::where('order_id', $this->order_id)->value('code');
    }
}
