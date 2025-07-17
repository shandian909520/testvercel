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

namespace app\admin\controller\alipay;

use app\admin\model\AliOrders;
use app\common\controller\AdminController;
use app\common\lib\wxApi;
use app\middleware\AliCheck;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="支付宝进件-分销订单",auth=false)
 */
class AliOrdersDis extends AdminController
{
    protected  $middleware = [AliCheck::class];
    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\AliOrders();

        $this->assign('getPayTypeList', $this->model->getPayTypeList());

        $this->assign('getStatusList', $this->model->getStatusList());
        $this->assign('getSetailStatus', $this->model->getSetailStatus());
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->withJoin(['user'])
                ->where($where)
                ->where('retail_num', '>', '0')
                ->where('pay_type', 2)
                ->where('pid', '<>', 0)
                ->where('user.pf_id', '=', session('admin.id'))
                ->count();
            $list = $this->model->withJoin(['user', 'aliOrdersInfo'])
                ->where($where)
                ->where('retail_num', '>', '0')
                ->where('pid', '<>', 0)
                ->where('pay_type', 2)
                ->where('user.pf_id', '=', session('admin.id'))
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list->append(['puser'])->toArray(),
            ];
            return json($data);
        }
        return $this->fetch();
    }


    /**
     * @NodeAnotation(title="分销订单-payment")
     */
    public function payment()
    {
        $id = input('id');
        $order = AliOrders::where('id', $id)->find();
        if (!$order || $order->retail_status == 1) {
            $this->error('订单状态错误');
        }

        $api = new wxApi('mp', session('admin.id'));
        $res =  $api->transfers($order->fuser()->open_id, $order->order_id, $order->retail_num);
        if ($res) {
            $order->retail_status = 1;
            $order->retail_time = date('Y-m-d H:i:s');
            $order->save();
            $this->success('打款成功');
        } else {
            $order->retail_status = 2;
            $order->retail_time = date('Y-m-d H:i:s');
            $order->save();
            $this->error('打款失败');
        }
    }
}
