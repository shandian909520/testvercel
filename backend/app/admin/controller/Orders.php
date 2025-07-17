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

use app\admin\model\OrderInfo;
use app\admin\model\Orders as ModelOrders;
use app\api\controller\Bmprogram;
use app\api\service\NoticeService;
use app\api\service\ThirdPartyService as ServiceThirdPartyService;
use app\common\controller\AdminController;
use app\common\lib\BaiDuApi;
use app\common\lib\ProApi;
use app\common\lib\wxApi;
use app\common\service\ThirdPartyService;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\Log;

/**
 * @ControllerAnnotation(title="订单")
 */
class Orders extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\Orders();

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
                ->withJoin(['user', 'info'], 'left')
                ->where($where)
                ->where('orders.pfconfig_id', '=', session('admin.id'))
                // ->where('user.pid',0)
                ->count();

            $list = $this->model->withJoin(['user', 'info'], 'left')
                ->where($where)
                // ->where('user.pid',0)
                ->where('orders.pfconfig_id', '=', session('admin.id'))
                ->page($page, $limit)
                ->order($this->sort)
                ->select();

            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list->append(['error'])->toArray(),
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
                $r->info->delete();
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
        $id = input('info_id');
        $info = OrderInfo::where('id', $id)->find();

        if (request()->isAjax()) {
            $post = input('post.');
            unset($post['file']);
            unset($post['error_msg']);
            unset($post['remarks']);
            $int =  $info->where('id', $id)->update($post);
            $this->success('更新成功');
        }

        $order = ModelOrders::where('info_id', $id)->find();

        return view('detail', compact('info', 'order'));
    }


    /**
     * @NodeAnotation(title="再来一单")
     */
    public function re_create($id)
    {
        $order = $this->model->where('id', $id)->find();
        if (!$order) {
            $this->error('该订单不存在');
        }
        $info = $order->info->toArray();
        unset($info['id']);
        $int =  ModelOrders::createOrder(0, $order->user_id, 1, $info, $order->num, $order['pf_id'], $order['pfconfig_id']);
        $int ?
            $this->success('创建成功') : $this->error('创建失败');
    }

    /**
     * @NodeAnotation(title="select")
     */
    public function select($id)
    {

        $order = $this->model->where('id', $id)->find();

        $o_info = Db::name('order_info')->where('id', $order->info_id)->find();

        $service = new ThirdPartyService($order['pf_id']);

        if ($o_info && $o_info['type'] == 1) {
            $data = $service->get_xcx_process($order->taskid);
            $res = $this->model->where('id', $id)->update(['error_msg' => $data['message']]);
            $this->success('查询成功');
        }

        if ($o_info && $o_info['type'] == 2) {
            $this->success('企业注册无需查询');
        }


        // 
    }
    /**
     * @NodeAnotation(title="确认付款")
     */
    public function ok($id)
    {
        $order = $this->model->where('id', $id)->find();
        if (!$order || $order->status != 1) {
            $this->error('订单状态错误');
        }
        $order->status = 2;
        $int =   $order->save();



        $postData = [
            'host' => request()->host(),
            'order_id' => $order->order_id,
            'type' => $order->info['type'],
            'name' => $order->info['name'],
            'code_type' => $order->info['code_type'],
            'code' => $order->info['code'],
            'wx_code' => $order->info['wx_code'],
            'person_name' => $order->info['person_name'],
            'auth_code' => '',
            'component_phone' => sysconfig('base_config', 'service_phone' . $order['pf_id'])
        ];
        $service = new ServiceThirdPartyService();
        if ($order->info['type'] == 1) {
            $res = $service->register_persion($postData, $order['pf_id']);
        } else if ($order->info['type'] == 2) {
            $code = ['18' => 1, '9' => 2, '15' => 3];
            $postData['code_type'] = $code[$postData['code_type']];
            $res = $service->register_company($postData, $order['pf_id']);
        } else {
            //管理员注册
            $code = ['18' => 1, '9' => 2, '15' => 3];
            $postData['code_type'] = $code[$postData['code_type']];
            $postData['openid'] = $order->info['openid'];
            $postData['xcxname'] = $order->info['xcxname'];
            Log::write("试用注册 参数" . json_encode($postData));
            $res = $service->register_fastregisterbetaweapp($postData, $order['pf_id']);
            Log::write("管理员注册小程序结果" . json_encode($res));
        }

        if ($res['code'] == 1) {
            if ($order->info->type == 1) {
                $order->error_msg = '请扫码验证！';
                $order->success_url =   $res['data']['authorize_url'];
                $order->taskid =   $res['data']['taskid'];
            } else if ($order->info->type == 2) {
                $order->error_msg = '请法人确认验证信息!';
            } else {
                $order->error_msg = '小程序创建成功!';
                $order->faststatus = $res['data']['errcode'];
                $order->success_url =   $res['data']['authorize_url'];
                $order->unique_id =   $res['data']['unique_id'];
            }
            NoticeService::sendMsg($order, 1, '您注册的小程序已通过，等待验证！', session('admin.id'));
            $order->save();
        } else {
            $order->status = 3;
            $order->error_msg = $res['message'];
            NoticeService::sendMsg($order, 0, '您注册的小程序未通过，错误信息：' . $res['message'], session('admin.id'));
            if ($order->info->type == 3) {
                $order->faststatus = $res['errcode'];
            }
            $order->save();
        }
        $int ?
            $this->success($res['message']) : $this->error($res['message']);
    }

    /**
     * @NodeAnotation(title="确认转正")
     */
    function verifybetaweapp($id)
    {
        $order = $this->model->where('id', $id)->find();
        if (!$order || $order->status != 3) {
            $this->error('订单状态错误');
        }
        //试用小程序转正接口
        $postData = [
            'enterprise_name' => $order->info['name'],
            'code' => $order->info['code'],
            'code_type' => $order->info['code_type'],
            'legal_persona_wechat' => $order->info['wx_code'],
            'legal_persona_name' => $order->info['person_name'],
            'legal_persona_idcard' => $order->info['legal_persona_idcard'],
            'component_phone' => sysconfig('base_config', 'service_phone'),
            'appid' => $order['appid'],
        ];
        $service = new ServiceThirdPartyService();
        $res = $service->register_verifybetaweapp($postData, $order['pf_id']);
        $order->error_msg = $res['message'];
        $order->save();
        if ($res['code'] == 1) {
            $this->success($res['message']);
        } else {
            $this->error($res['message']);
        }
    }
    /**
     * @NodeAnotation(title="get_business_info")
     */
    public function  get_business_info()
    {
        $file = request()->file('file');
        if (!$file) {
            return error('请上传营业执照！');
        }
        $saveName = Filesystem::disk('public')->putFile('/upload', $file, 'uniqid');
        $api = new BaiDuApi(session('admin.id'));
        $res =  $api->get_business_pic_info(base64_encode(file_get_contents(public_path() . '/' . $saveName)));
        @unlink(public_path() . '/' . $saveName);
        if ($res) {
            return success('识别成功', $res);
        }
        return error('识别失败');
    }

    /**
     * @NodeAnotation(title="小程序更名")
     */
    function setbetaweappnickname($id)
    {
        $info = OrderInfo::where('id', $id)->find();
        empty($info) && $this->error('数据不存在');
        $order = $this->model->where('info_id', $id)->find();
        if (empty($order) || empty($order['appid'])) {
            $this->error('appid 错误', $order);
        }
        if ($this->request->isPost()) {
            $post = input('post.');

            $wxApi = new wxApi('mp', $order['pf_id']);
            $res = $wxApi->media_upload(input('post.license_link'), $order['appid']);
            if (input('post.license_link')) {
                $res = $wxApi->media_upload(input('post.license_link'), $order['appid']);
                $post['license'] = $res['media_id'];
            }
            if (input('post.naming_other_stuff_1_link')) {
                $res = $wxApi->media_upload(input('post.naming_other_stuff_1_link'), $order['appid']);
                $post['naming_other_stuff_1'] = $res['media_id'];
            }
            if (input('post.naming_other_stuff_2_link')) {
                $res = $wxApi->media_upload(input('post.naming_other_stuff_2_link'), $order['appid']);
                $post['naming_other_stuff_2'] = $res['media_id'];
            }
            if (input('post.naming_other_stuff_3_link')) {
                $res = $wxApi->media_upload(input('post.naming_other_stuff_3_link'), $order['appid']);
                $post['naming_other_stuff_3'] = $res['media_id'];
            }
            if (input('post.naming_other_stuff_4_link')) {
                $res = $wxApi->media_upload(input('post.naming_other_stuff_4_link'), $order['appid']);
                $post['naming_other_stuff_4'] = $res['media_id'];
            }
            if (input('post.naming_other_stuff_5_link')) {
                $res = $wxApi->media_upload(input('post.naming_other_stuff_5_link'), $order['appid']);
                $post['naming_other_stuff_5'] = $res['media_id'];
            }
            Log::write('___here' . json_encode($post, JSON_UNESCAPED_UNICODE));


            $service = new ServiceThirdPartyService();
            //是否设置了原始gh id 未设置 则设置
            if (empty($order['gh_id'])) {
                $authorizerInfo = $service->api_get_authorizer_info($order['appid'], $order['pf_id']);
                if (!empty($authorizerInfo['authorizer_info']['user_name'])) {
                    $order->save(['gh_id' => $authorizerInfo['authorizer_info']['user_name']]);
                } else {
                    $this->error('原始id 错误');
                }
            }

            try {
                $save = $info->save($post);
            } catch (\Exception $e) {
                $this->error('更名失败' . $e->getMessage());
            }
            $skdata = [
                'appid' => $order['appid'],
                'nick_name' => $post['xcxname'],
                'license' => $post['license'],
                'naming_other_stuff_1' => !empty($post['naming_other_stuff_1']) ? $post['naming_other_stuff_1'] : '',
                'naming_other_stuff_2' => !empty($post['naming_other_stuff_2']) ? $post['naming_other_stuff_2'] : '',
                'naming_other_stuff_3' => !empty($post['naming_other_stuff_3']) ? $post['naming_other_stuff_3'] : '',
                'naming_other_stuff_4' => !empty($post['naming_other_stuff_4']) ? $post['naming_other_stuff_4'] : '',
                'naming_other_stuff_5' => !empty($post['naming_other_stuff_5']) ? $post['naming_other_stuff_5'] : '',
            ];
            $res = $service->setnickname($skdata, $order['pf_id']);
            try {
                Log::write('___开始更新 error_msg：' . $res['message']);
                $save = $order->save(['error_msg' => $res['message']]);
                Log::write('___开始更新结果 error_msg：' . $save);
            } catch (\Exception $e) {
                $this->error('更名失败' . $e->getMessage());
            }
            $save ? $this->success($res['message']) : $this->error($res['message']);
        }
        $this->assign('row', $info);
        return $this->fetch();
    }
}
