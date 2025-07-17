<?php
// +----------------------------------------------------------------------
// | 小程序注册服务商助手 
// +----------------------------------------------------------------------
// | 版权所有  晓江云计算有限公司 
// +----------------------------------------------------------------------
// | 官方网站：https://www.xiaojiangy.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | 联系方式: 13163426222 <sc@xiaojiany.com>
// +----------------------------------------------------------------------
// | 系统已获取您的域名和ip信息，本系统未经授权严禁使用，盗版必究。
// +----------------------------------------------------------------------
// | 公司决定2023年1月份对所有盗版用户进行维权诉讼，避免更大损失，请尽早转正。
// +----------------------------------------------------------------------

namespace app\admin\model;

use app\common\model\TimeModel;

class InComingOrder extends TimeModel
{

    protected $name = "incoming_order";

    protected $deleteTime = false;

    public function setOrderIdAttr()
    {
        $order_id = '';
        do {
            $order_id = date('YmdHis') . random_str(8, 3);
        } while (InComingOrder::where('order_id', $order_id)->find());
        return $order_id;
    }

    public function getPayTypeList()
    {
        return ['0' => '暂无', '1' => '平台', '2' => '微信', '3' => '卡密',];
    }

    public function getStatusList()
    {
        return ['1' => '未支付', '2' => '进行中', '3' => '已完成',];
    }

    public function getPayTypeNameAttr()
    {
        return $this->getPayTypeList()[$this->pay_type];
    }

    public function getStatusNameAttr()
    {
        return $this->getStatusList()[$this->status];
    }

    
    public function getInfosAttr()
    {
        return InComingOrderInfo::where('id', $this->info_id)->find();
    }

    


    function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }

    function orderInfo()
    {
        return $this->hasOne(InComingOrderInfo::class, 'incoming_id');
    }

    public function orderShortInfo()
    {
        return $this->hasOne(InComingOrderInfo::class, 'incoming_id')->field('activities_rate,subject_type,contact_name,id_card_name,merchant_name,subject_type');
    }

    function getPuserAttr()
    {
        return Users::where('id', $this->user->pid)->value('nickname') ?: '无';
    }

    public function fuser()
    {
        return Users::where('id', $this->user->pid)->find();
    }

    public function getCodeAttr()
    {
        return ActiveIdentCode::where('order_id', $this->order_id)->value('code');
    }
}
