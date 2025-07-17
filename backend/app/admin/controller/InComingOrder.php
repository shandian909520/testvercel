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

namespace app\admin\controller;

use app\admin\model\InComingOrderInfo;
use app\admin\model\OrderInfo;
use app\admin\model\Orders as ModelOrders;
use app\admin\model\Region;
use app\admin\model\Users;
use app\api\service\NoticeService;
use app\common\controller\AdminController;
use app\common\lib\ProApi;
use app\common\service\ThirdPartyService;
use app\middleware\AuthCheck;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="商户进件-订单")
 */
class InComingOrder extends AdminController
{
    // protected   $middleware = [AuthCheck::class];
    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\InComingOrder();

        $this->assign('getPayTypeList', $this->model->getPayTypeList());

        $this->assign('getStatusList', $this->model->getStatusList());
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
            if (input('user_id')) $where['user_id'] = input('user_id');
            $count = $this->model
                ->withJoin(['user', 'orderInfo'], 'left')
                ->where($where)
                ->where('user.pf_id', '=', session('admin.id'))
                // ->where('user.pid',0)
                ->count();
            $list = $this->model->withJoin(['user', 'orderInfo'], 'left')
                ->where($where)
                ->where('user.pf_id', '=', session('admin.id'))
                // ->where('user.pid',0)
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $list = $list->append(['error'])->toArray();
            foreach ($list as $k => $v) {
                if (empty($v['user']['nickname'])) {
                    $list[$k]['user']['nickname'] = '后台提交';
                }
            }
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }

        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="删除")
     */
    public function delete($id)
    {
        $this->checkPostRequest();
        $row = $this->model->whereIn('id', $id)->select();
        $row->isEmpty() && $this->error('数据不存在');
        try {
            foreach ($row as $r) {
                $r->orderInfo->delete();
            }
            $save = $row->delete();
        } catch (\Exception $e) {
            $this->error('删除失败');
        }
        $save ? $this->success('删除成功') : $this->error('删除失败');
    }




    /**
     * @NodeAnotation(title="详情")
     */
    public function detail()
    {
        $id = input('id');
        $order = InComingOrderInfo::where('incoming_id', $id)->find();
        if (request()->isAjax()) {
            $post = input('post.');
            $incomorder = $this->model->where('id', $id)->find();
            $user = Users::where(['id' => $incomorder['user_id']])->find();
            $api = new ProApi($user['pf_id']);
            //营业执照
            if (input('post.license_copy_link')) {
                $res = $api->upload_img(input('post.license_copy_link'));
                $post['license_copy'] = $res['media_id'];
            }

            //经营范围
            if (input('post.sales_scenes_type')) {
                $post['sales_scenes_type'] = implode(',', input('post.sales_scenes_type') ?: []);
            }

            //门店门头
            if (input('post.store_entrance_pic_link')) {
                $arr = array_filter(explode(',', input('post.store_entrance_pic_link')));
                $result = [];
                foreach ($arr as $v) {
                    $res = $api->upload_img($v);
                    array_push($result, $res['media_id']);
                }
                $post['store_entrance_pic'] = implode(',', $result);
            }

            //店内
            if (input('post.indoor_pic_link')) {
                $arr = array_filter(explode(',', input('post.indoor_pic_link')));
                $result = [];
                foreach ($arr as $v) {
                    $res = $api->upload_img($v);
                    array_push($result, $res['media_id']);
                }
                $post['indoor_pic'] = implode(',', $result);
            }
            //公众号页面截图
            if (input('post.mp_pics_link')) {
                $arr = array_filter(explode(',', input('post.mp_pics_link')));
                $result = [];
                foreach ($arr as $v) {
                    $res = $api->upload_img($v);
                    array_push($result, $res['media_id']);
                }
                $post['mp_pics'] = implode(',', $result);
            }


            //身份证正面
            if (input('post.id_card_copy_link')) {
                $res = $api->upload_img(input('post.id_card_copy_link'));
                $post['id_card_copy'] = $res['media_id'];
            }
            //身份证反面
            if (input('post.id_card_national_link')) {
                $res = $api->upload_img(input('post.id_card_national_link'));
                $post['id_card_national'] = $res['media_id'];
            }
            unset($post['file']);
            $int =  $order->save($post);
            $this->success('更新成功');
        }
        $region = Region::where('parent_id', 1)->append(['children'])->select();
        $sss = 'SALES_SCENES_STORE';
        $ssm = 'SALES_SCENES_MP';
        return view('detail', compact('order', 'region', 'sss', 'ssm'));
    }

    /**
     * @NodeAnotation(title="提交申请")
     */
    public function ok($id)
    {
        $order = $this->model->where('id', $id)->find();
        if (!$order || $order->status != 1) {
            $this->error('订单状态错误');
        }
        $int = 0;
        $user = Users::where(['id' => $order['user_id']])->find();
        //提交申请
        $api = new ProApi($user['pf_id']);
        $res =   $api->applyment($order);
        if (is_array($res)) {
            $int =   $order->save([
                'status' => 2,
                'applyment_id' => $res['applyment_id'],
                'error_msg' => !empty($res['message']) ? $res['message'] : '',
            ]);
        } else {
            $int =   $order->save([
                'status' => 4,
                'error_msg' => $res,
            ]);
        }
        $int ?
            $this->success('确认成功') : $this->error('确认失败');
    }
    /**
     * @NodeAnotation(title="提交申请",auth=false)
     */
    function selectService($id)
    {
        if (empty($id)) $this->error('参数错误');
        $order = $this->model->where('id', $id)
            ->append(['orderInfo', 'code'])
            ->find();
        
        if (empty($order)) {
            $this->error('订单不存在或状态错误！');
        }
        $order->orderInfo['contact_type'] = empty($order->orderInfo['contact_type']) ? "SUPER" : "LEGAL";
        if (in_array($order->status, [2, 4])) {
            if ($order->applyment_id) {
                $user = Users::where('id', $order->user_id)->find();
                $pf_id = $user['pf_id'];
                $api = new ProApi($pf_id);
                $res =  $api->get_register_status('applyment_id', $order->applyment_id);
                if (is_array($res)) {
                    if ($res['applyment_state'] == 'APPLYMENT_STATE_FINISHED') {
                        $order->save([
                            'status' => 3,
                            'error_msg' => $res['applyment_state_msg'] . 'sub_mchid:' . $res['sub_mchid']
                        ]);
                    } else {
                        $order->save([
                            'error_msg' => $res['applyment_state_msg']
                        ]);
                    }
                }
                $order->applyment_result = $res;
            } else {
                $order->applyment_result = [];
            }
        } else {
            $order->applyment_result = [];
        }
        $this->success('查询成功');
    }
}
