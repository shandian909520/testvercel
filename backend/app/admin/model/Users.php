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

class Users extends TimeModel
{

    protected $name = "users";

    protected $deleteTime = false;

    public function generate_invite_code()
    {
        do {
            $invite_code = random_str(8, 3);
        } while (Users::where('invite_code', $invite_code)->find());
        $this->setAttr('invite_code', $invite_code);
        $this->save();
        return $invite_code;
    }
    public function getHeadAttr($value)
    {
        return $value  ?: '/static/admin/images/head.jpg';
    }

    public function getIsBlackList()
    {
        return ['0' => '否', '1' => '是',];
    }


    public function generate_token()
    {
        do {
            $token = random_str(32, 3);
        } while (Users::where('token', $token)->find());
        $this->setAttr('token', $token);
        $this->save();
        return $token;
    }

    /**
     * 带佣金的订单总额
     */
    public function getAllRetailNumAttr()
    {
        return Orders::where('user_id', $this->id)
            ->where('status', 3)
            ->where('pay_type', 2)
            ->sum('retail_num');
    }
}
