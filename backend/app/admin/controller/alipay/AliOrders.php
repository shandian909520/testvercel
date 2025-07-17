<?php

namespace app\admin\controller\alipay;

use app\admin\model\AliOrders as ModelAliOrders;
use app\admin\model\AliOrdersInfo;
use app\admin\model\Region;
use app\api\service\AlipayService;
use app\common\controller\AdminController;
use app\middleware\AliCheck;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use EasyAdmin\tool\CommonTool;
use Exception;
use jianyan\excel\Excel;
use think\App;
use think\facade\Db;
use think\facade\Log;

/**
 * @ControllerAnnotation(title="ali_orders",auth=false)
 */
class AliOrders extends AdminController
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
     * @NodeAnotation(title="列表",auth=false)
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
                ->withJoin(['user'], 'left')
                ->where($where)
                ->where('ali_orders.pf_id', '=', session('admin.id'))
                // ->where('user.pid',0)
                ->count();
            $list = $this->model->withJoin(['user'], 'left')
                ->where($where)
                ->where('ali_orders.pf_id', '=', session('admin.id'))
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
     * @NodeAnotation(title="添加",auth=false)
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            try {
                $save = $this->model->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败:' . $e->getMessage());
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="编辑",auth=false)
     */
    public function edit($id)
    {
        $row = $this->model->find($id);
        empty($row) && $this->error('数据不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);
            try {
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('row', $row);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="删除",auth=false)
     */
    public function delete($id)
    {
        $this->checkPostRequest();
        $row = $this->model->whereIn('id', $id)->select();
        $row->isEmpty() && $this->error('数据不存在');
        $row->delete();
        try {
            $infoids = array_column($row->toArray(), 'id');
            $res = Db::table('ea_ali_orders_info')->whereIn('ali_orders_id', $infoids)->delete();
        } catch (\Exception $e) {
            $this->error('订单详情删除失败:' . $e->getMessage());
        }
        $res ? $this->success('删除成功') : $this->error('删除失败');
    }

    /**
     * @NodeAnotation(title="详情",auth=false)
     */
    public function detail()
    {
        $id = input('id');
        $order = $this->model->find($id);
        $orderInfo = AliOrdersInfo::where(['ali_orders_id' => $order['id']])->find();

        $alipaymccC1 = file_get_contents(public_path() . 'static' . DS . 'alipaymccC1.json');
        $alipaymccC1 = json_decode($alipaymccC1, true);
        $mcc_code = $orderInfo['mcc_code'];
        $mcc_code = explode('_', $mcc_code);
        $mcc_code1 = $mcc_code[0];
        $mcc_code2 = $mcc_code[1];
        return view('detail', compact('orderInfo', 'alipaymccC1', 'mcc_code1', 'mcc_code2', 'order'));
    }
    function updateDetail()
    {
        $id = input('id');
        $orderInfo = AliOrdersInfo::where(['id' => $id])->find();
        $order = $this->model->find($orderInfo['ali_orders_id']);
        if ($order['status'] > 5) {
            $this->error('当前状态禁止编辑');
        }
        if (request()->isAjax()) {
            $post = input('post.');
            $mcc_code = $post['mcc_code1'] . '_' . $post['mcc_code2'];
            unset($post['mcc_code1'], $post['mcc_code2'], $post['file']);
            $post['mcc_code'] = $mcc_code;
            $orderInfo->where('id', $id)->update($post);
            $this->success('更新成功');
        }
    }

    /**
     * @NodeAnotation(title="属性修改",auth=false)
     */
    public function modify()
    {
        $this->checkPostRequest();
        $post = $this->request->post();
        $rule = [
            'id|ID'    => 'require',
            'field|字段' => 'require',
            'value|值'  => 'require',
        ];
        $this->validate($post, $rule);
        $row = $this->model->find($post['id']);
        if (!$row) {
            $this->error('数据不存在');
        }
        if (!in_array($post['field'], $this->allowModifyFields)) {
            $this->error('该字段不允许修改：' . $post['field']);
        }
        try {
            $row->save([
                $post['field'] => $post['value'],
            ]);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->success('保存成功');
    }
    /**
     * @NodeAnotation(title="套餐",auth=false)
     */
    function packageList()
    {
        return $this->fetch();
    }
    /**
     * @NodeAnotation(title="设置",auth=false)
     */
    function setConfig()
    {
        $hosts = request()->domain();
        $sname = $_SERVER['SERVER_NAME'];
        $this->assign('host', $hosts);
        $this->assign('sname', $sname);
        return $this->fetch();
    }
    /**
     * @NodeAnotation(title="进件",auth=false)
     */
    function incomingParts()
    {
        //一级类目 alipaymccC1.json
        $alipaymccC1 = file_get_contents(public_path() . 'static' . DS . 'alipaymccC1.json');
        $alipaymccC1 = json_decode($alipaymccC1, true);
        $this->assign('alipaymccC1', $alipaymccC1);
        return $this->fetch();
    }
    /**
     * @NodeAnotation(title="二级类目",auth=false)
     */
    function alipaymccC2()
    {
        $c2 = input('post.c2');
        $data = ['code' => 0, 'msg' => 'error', 'data' => []];
        if (empty($c2)) {
            $data['msg'] = '参数错误';
        } else {
            $alipaymccC1 = file_get_contents(public_path() . 'static' . DS . 'alipaymccC2.json');
            $alipaymccC1 = json_decode($alipaymccC1, true);
            if (!empty($alipaymccC1[$c2])) {
                $data['code'] = 1;
                $data['data'] = $alipaymccC1[$c2];
            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
    /**
     * @NodeAnotation(title="后台进件",auth=false)
     */
    function adminIncoming()
    {
        $post = input('post.');
        $mcc_code = $post['mcc_code1'] . '_' . $post['mcc_code2'];
        $post['mcc_code'] = $mcc_code;
        Db::startTrans();


        // $order->save([
        //     'num' => $incoming_part->cost,
        //     'retail_num' => sysconfig('retail_config', 'retail_status' . $pf_id) ? $incoming_part->retail_num : 0,
        // ]);


        try {
            $order = ModelAliOrders::create([
                'order_id' => '',
                'user_id' => 1,
                'status' => 2,
                'pf_id' => session('admin.id')
            ]);
            $post['ali_orders_id'] = $order['id'];
            $res = AliOrdersInfo::create($post);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            $this->error('进件失败:' . $e->getMessage());
        }
        //创建事务
        $alipayService = new AlipayService(session('admin.id'));
        $res = $alipayService->agentCreate($post);
        Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '创建事务返回' . $order['id']);
        if ($res['code'] == 10000) {
            $order->where('id', $order['id'])->update([
                "code" => "10000",
                "msg" => "事务创建成功",
                "batch_status" => $res['batch_status'],
                "status" => 3
            ]);
            AliOrdersInfo::where(['ali_orders_id' => $order['id']])->save(['batch_no' => $res['batch_no']]);
            $post['batch_no'] = $res['batch_no'];
            $res = $alipayService->facetofaceSign($post);
            if ($res['code'] == 10000) {
                $order->where('id', $order['id'])->update([
                    "code" => "10000",
                    "msg" => "签约成功",
                    "sub_msg" => "签约成功",
                    "status" => 4
                ]);
                $res = $alipayService->agentCommonsignConfirm($res['batch_no']);
                if ($res['code'] == 10000) {
                    $order->where('id', $order['id'])->update([
                        "code" => "10000",
                        "msg" => "已提交信息确认",
                        "status" => 5
                    ]);
                } else {
                    $order->where('id', $order['id'])->update([
                        "code" => $res['code'],
                        "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                        "sub_code" => $res['sub_code'],
                        "sub_msg" => $res['sub_msg'],
                        "status" => 3,
                    ]);
                }
            } else {
                $order->where('id', $order['id'])->update([
                    "code" => $res['code'],
                    "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                    "sub_code" => $res['sub_code'],
                    "sub_msg" => $res['sub_msg'],
                ]);
            }
            Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '后台进件返回' . $order['id']);
        } else {
            $order->where('id', $order['id'])->update([
                "code" => $res['code'],
                "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                "sub_code" => $res['sub_code'],
                "sub_msg" => $res['sub_msg'],
            ]);
        }
        $msg = !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'];
        $this->success($msg, 1);
    }
    /**
     * @NodeAnotation(title="提交申请",auth=false)
     */
    public function ok($id)
    {
        // 1 未支付
        // 2 待创建事务
        // 3: 事务创建成功
        // 4: 签约成功
        // 5: 已提交事务
        // 6: 审核中
        // 7: 商户已拒绝
        // 8: 等待商家签约
        // 9: --
        $order = $this->model->where('id', $id)->find();
        if (!$order) {
            $this->error('订单状态错误');
        }
        $data = AliOrdersInfo::where(['ali_orders_id' => $id])->find();
        $alipayService = new AlipayService(session('admin.id'));
        if (empty($data['batch_no'])) {
            $res = $alipayService->agentCreate($data);
            Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '创建事务返回' . $order['id']);
            if ($res['code'] == 10000) {
                $order->where('id', $id)->update([
                    "code" => "10000",
                    "msg" => "事务创建成功",
                    "batch_status" => $res['batch_status'],
                    "status" => 3
                ]);
                AliOrdersInfo::where(['ali_orders_id' => $order['id']])->save(['batch_no' => $res['batch_no']]);
                $data['batch_no'] = $res['batch_no'];
                $res = $alipayService->facetofaceSign($data);
                if ($res['code'] == 10000) {
                    $order->where('id', $id)->update([
                        "code" => "10000",
                        "msg" => "签约成功",
                        "sub_msg" => "签约成功",
                        "status" => 4
                    ]);
                } else {
                    $order->where('id', $id)->update([
                        "code" => $res['code'],
                        "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                        "sub_code" => $res['sub_code'],
                        "sub_msg" => $res['sub_msg'],
                    ]);
                }
                Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '创建事务和签约返回' . $order['id']);
            } else {
                $order->where('id', $id)->update([
                    "code" => $res['code'],
                    "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                    "sub_code" => $res['sub_code'],
                    "sub_msg" => $res['sub_msg'],
                ]);
            }
        } else {
            if ($order['status'] == 3) {
                $res = $alipayService->facetofaceSign($data);
                Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '签约返回' . $order['id']);
                if ($res['code'] == 10000) {
                    $order->where('id', $id)->update([
                        "code" => "10000",
                        "msg" => "签约成功",
                        "status" => 4
                    ]);
                } else {
                    $order->where('id', $id)->update([
                        "code" => $res['code'],
                        "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                        "sub_code" => $res['sub_code'],
                        "sub_msg" => $res['sub_msg'],
                    ]);
                }
            } else if ($order['status'] == 4) {
                //提交确认信息
                $res = $alipayService->agentCommonsignConfirm($data['batch_no']);
                if ($res['code'] == 10000) {
                    $order->where('id', $id)->update([
                        "code" => "10000",
                        "msg" => "已提交信息确认",
                        "status" => 5
                    ]);
                } else {
                    $order->where('id', $id)->update([
                        "code" => $res['code'],
                        "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                        "sub_code" => $res['sub_code'],
                        "sub_msg" => $res['sub_msg'],
                        "status" => 3,
                    ]);
                }
            } else {
                $this->error('当前状态禁止编辑');
            }
            Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '提交确认信息返回status:' . $order['status'] . '|||order id' . $order['id']);
        }
        $msg = !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'];
        if ($res['code'] == 10000) {
            $this->success($msg);
        } else {
            $this->error($msg);
        }
    }
    /**
     * @NodeAnotation(title="查询结果",auth=false)
     */
    function checkRes()
    {
        $id = input('id');
        $order = $this->model->where('id', $id)->find();
        $alipayService = new AlipayService(session('admin.id'));
        $data = AliOrdersInfo::where(['ali_orders_id' => $id])->find();
        $res = $alipayService->agentOrderQuery($data['batch_no']);
        // 支付宝商户入驻申请单状态，申请单状态包括：
        // MERCHANT_INFO_HOLD=暂存，提交事务出现业务校验异常时，会暂存申请单信息，可以调用业务接口修正参数，并重新提交
        // MERCHANT_AUDITING=审核中，申请信息正在人工审核中
        // MERCHANT_CONFIRM=待商户确认，申请信息审核通过，等待联系人确认签约或授权
        // MERCHANT_CONFIRM_SUCCESS=商户确认成功，商户同意签约或授权
        // MERCHANT_CONFIRM_TIME_OUT=商户超时未确认，如果商户受到确认信息15天内未确认，则需要重新提交申请信息
        // MERCHANT_APPLY_ORDER_CANCELED=审核失败或商户拒绝，申请信息审核被驳回，或者商户选择拒绝签约或授权

        // order.status 5||6 查询状态
        if ($res['code'] == 10000) {
            $updateData = [];
            $errmsg = '';
            if ($res['order_status'] == 'MERCHANT_INFO_HOLD') {
                $errmsg = '修正后重新提交因：';
                $errmsg .= !empty($res['reject_reason']) ? $res['reject_reason'] : '';
                if (!empty($res['restrict_infos'][0]) && !empty($res['restrict_infos'][0]['restrict_reason'])) {
                    $errmsg .= $res['restrict_infos'][0]['restrict_reason'];
                }
                if (!empty($res['product_agent_status_infos'][0]) && !empty($res['product_agent_status_infos'][0]['reject_reason'])) {
                    $errmsg .= $res['product_agent_status_infos'][0]['reject_reason'];
                }
                $order->where('id', $id)->update([
                    "msg" => $errmsg,
                    "sub_msg" => $errmsg,
                    "status" => 3,
                ]);
            } else if ($res['order_status'] == 'MERCHANT_AUDITING') {
                $order->where('id', $id)->update([
                    "msg" => '审核中，申请信息正在人工审核中',
                    "sub_msg" => '审核中，申请信息正在人工审核中',
                    "status" => 6,
                ]);
                $errmsg = '审核中，申请信息正在人工审核中';
            } else if ($res['order_status'] == 'MERCHANT_CONFIRM') {
                $order->where('id', $id)->update([
                    "msg" => '待商户确认，申请信息审核通过，等待联系人确认签约或授权',
                    "sub_msg" => '待商户确认，申请信息审核通过，等待联系人确认签约或授权',
                    "status" => 6,
                ]);
                $errmsg = '待商户确认，申请信息审核通过，等待联系人确认签约或授权';
            } else if ($res['order_status'] == 'MERCHANT_CONFIRM_SUCCESS') {
                $order->where('id', $id)->update([
                    "msg" => '商户确认成功，商户同意签约或授权',
                    "sub_msg" => '商户确认成功，商户同意签约或授权',
                    "status" => 6,
                ]);
                $errmsg = '商户确认成功，商户同意签约或授权';
            } else if ($res['order_status'] == 'MERCHANT_CONFIRM_TIME_OUT') {
                $order->where('id', $id)->update([
                    "msg" => '商户超时未确认，如果商户受到确认信息15天内未确认，则需要重新提交申请信息',
                    "sub_msg" => '商户超时未确认，如果商户受到确认信息15天内未确认，则需要重新提交申请信息',
                    "status" => 3,
                ]);
                $errmsg = '商户超时未确认，如果商户受到确认信息15天内未确认，则需要重新提交申请信息';
            } else if ($res['order_status'] == 'MERCHANT_APPLY_ORDER_CANCELED') {
                $order->where('id', $id)->update([
                    "msg" => '审核失败或商户拒绝，申请信息审核被驳回，或者商户选择拒绝签约或授权',
                    "sub_msg" => '审核失败或商户拒绝，申请信息审核被驳回，或者商户选择拒绝签约或授权',
                    "status" => 7,
                ]);
                $errmsg = '审核失败或商户拒绝，申请信息审核被驳回，或者商户选择拒绝签约或授权';
            } else {
                $errmsg = json_encode($res, JSON_UNESCAPED_UNICODE);
            }
            // `order_no` varchar(255) DEFAULT NULL COMMENT '签约单号',
            // `confirm_url` varchar(255) DEFAULT NULL COMMENT '商户确认签约链接',
            // `merchant_pid` varchar(255) DEFAULT NULL COMMENT '商户pid',
            // `order_status` varchar(255) DEFAULT NULL COMMENT '申请单状态',
            if (!empty($res['order_no'])) {
                $updateData['order_no'] = $res['order_no'];
            }
            if (!empty($res['confirm_url'])) {
                $updateData['confirm_url'] = $res['confirm_url'];
            }
            if (!empty($res['merchant_pid'])) {
                $updateData['merchant_pid'] = $res['merchant_pid'];
            }
            if (!empty($res['order_status'])) {
                $updateData['order_status'] = $res['order_status'];
            }
            if (!empty($updateData)) {
                $order->where('id', $id)->update($updateData);
            }
            $this->success($errmsg);
        } else {
            $order->where('id', $id)->update([
                "msg" => $res['sub_msg'],
                "sub_msg" => $res['sub_msg'],
                "code" => $res['code'],
                "sub_code" => $res['sub_code'],
            ]);
            $this->error($res['sub_msg']);
        }
    }
    /**
     * @NodeAnotation(title="地区",auth=false)
     */
    function getRegion()
    {
        $pid = input('pid');
        $region = Region::where('parent_id', $pid)->select();
        $this->success('', $region);
    }
}
