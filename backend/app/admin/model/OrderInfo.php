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

class OrderInfo extends TimeModel
{

    protected $name = "order_info";

    protected $deleteTime = false;



    public function getTypeList()
    {
        return ['1' => '个人小程序', '2' => '企业小程序',];
    }

    public function getCodeTypeList()
    {
        return ['1' => '统一社会信用代码', '2' => '组织机构代码', '3' => '营业执照注册号',];
    }

    public static function createInfo($type, $name, $wx_code, $code_type = 0, $code = '', $person_name = '', $xcxname = '', $legal_persona_idcard = '', $openid = '')
    {
        return  OrderInfo::insertGetId(
            [
                'type' => $type,
                'name' => $name,
                'wx_code' => $wx_code,
                'code_type' => $code_type,
                'code' => $code,
                'person_name' => $person_name,
                'xcxname' => $xcxname,
                'legal_persona_idcard' => $legal_persona_idcard,
                'openid' => $openid,
                'create_time' => date('Y-m-d H:i:s')
            ]
        );
    }
}
