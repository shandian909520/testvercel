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
use think\facade\Log;

class Orders extends TimeModel
{

    protected $name = "orders";

    protected $deleteTime = false;

    public function setOrderIdAttr()
    {
        $order_id = '';
        do {
            $order_id = date('YmdHis') . random_str(8, 3);
        } while (Orders::where('order_id', $order_id)->find());
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
        return OrderInfo::where('id', $this->info_id)->find();
    }

    public static function createOrder($pay_type, $user_id, $status,  $data, $num = 0, $pf_id, $pfconfig_id)
    {
        //$pf_id 三方平台的id 如果三方平台关闭 则是超管的代理平台id
        //$pfconfig_id 用户所在平台
        Db::startTrans();
        try {
            if ($data['type'] == 1) {
                $info_id = OrderInfo::createInfo($data['type'], $data['name'], $data['wx_code']);
            } else if ($data['type'] == 2) {
                $info_id = OrderInfo::createInfo($data['type'], $data['name'], $data['wx_code'], $data['code_type'], $data['code'], $data['person_name']);
            } else {
                $info_id = OrderInfo::createInfo($data['type'], $data['name'], $data['wx_code'], $data['code_type'], $data['code'], $data['person_name'], $data['xcxname'], $data['legal_persona_idcard'], $data['openid']);
            }
            $num = sysconfig('base_config', 'register_num' . $pfconfig_id);
            if (empty($num)) {
                $num = 0;
            }
            Log::write('订单金额+++++num_________________' . $num);
            $data =     [
                'order_id' => '',
                'pay_type' => $pay_type,
                'num' => $num,
                'user_id' => $user_id,
                'status' => $status,
                'info_id' => $info_id,
                'pf_id' => $pf_id,
                'pfconfig_id' => $pfconfig_id,
                'taskid' => 0,
                'register_status' => sysconfig('base_config', 'register_status' . $pf_id) ? sysconfig('base_config', 'register_status' . $pf_id) : 0
            ];
            if (sysconfig('retail_config', 'retail_status'.$pfconfig_id)) {
                $data['retail_num'] = sysconfig('retail_config', 'retail_num' . $pfconfig_id);
            }
            $order =  Orders::create(
                $data
            );
            Db::commit();
            return $order;
        } catch (Exception $e) {
            Db::rollback();
            return $e->getMessage();
        }
    }
    function user()
    {
        return $this->hasOne(Users::class, 'id', 'user_id');
    }

    function info()
    {
        return $this->hasOne(OrderInfo::class, 'id', 'info_id');
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
    public function getErrorAttr()
    {
        return $this->error_msg;
    }
}
