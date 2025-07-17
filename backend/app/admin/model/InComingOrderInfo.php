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
use Exception;
use think\facade\Db;

class InComingOrderInfo extends TimeModel
{

    protected $name = "incoming_order_info";

    protected $deleteTime = false;

    public function getbizAddressCodeAttr($value)
    {
        return $value ?: '110000';
    }

    public function getbankAddressCodeAttr($value)
    {
        return $value ?: '110000';
    }
    public function bank_region()
    {
        $region =   Region::where('region', $this->bank_address_code)->find();
        $regions = Region::where('parent_id', $region->parent_id)->select();
        return ['parent_id' => $region->parent_id, 'regions' => $regions];
    }

    public function biz_region()
    {
        $region =   Region::where('region', $this->biz_address_code)->find();
        $regions = Region::where('parent_id', $region->parent_id)->select();
        return ['parent_id' => $region->parent_id, 'regions' => $regions];
    }
}
