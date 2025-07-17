<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\ActiveIdentCode;
use app\admin\model\AliIncomingParts;
use app\admin\model\AliOrders;
use app\admin\model\AliOrdersInfo;
use app\admin\model\Region;
use app\admin\model\Users;
use app\api\service\AlipayService;
use app\common\lib\BaiDuApi;
use app\common\lib\wxApi;
use EasyAdmin\upload\Uploadfile;
use Exception;
use think\facade\Db;
use think\facade\Log;

class AlipayApi
{
    private $agentCommonsignConfirmError =
    [
        'INVALID_PARAMETER' => '3',
        'INVALID_PARAMETER' => '3',
        'INVALID_PARAMETER' => '3',
        'INVALID_BATCH_NO' => '3',
        'NO_APP_PERMISSION' => '3',
        'BATCH_STATUS_IS_FINAL' => '9',
        'BATCH_IS_NOT_EXIST' => '3',
        'BATCH_IS_EMPTY' => '1',
        'BUSINESS_LICENSE_NO_EMPTY' => '3',
        'FEERATE_NOT_INTERAVL' => '3',
        'BIZ_ERROR' => '3',
        'OPEN_API_SIGN_PRODUCT_NOT_SUPPORT' => '9',
        'OPEN_API_SIGN_ISV_NOT_IN_WHITELIST' => '9',
        'UNKNOWN_EXCEPTION' => '9',
        'BIZ_ERROR' => '9',
        'SYSTEM_ERROR' => '3',
        'MERCHANT_SIGN_PRODUCT_IN_AUDIT' => '3',
        'ANT_PRODUCT_CONFLICT' => '9',
        'NOT_MATCHED_SSU_OR_PS' => '3',
        'OPEN_API_SIGN_PRODUCT_NUM_TOO_BIG' => '9',
        'MERCHANT_INTERFACE_INFO_ERR' => '3',
        'ORDER_TYPE_NULL_ERROR' => '9',
        'RESTRICT_VALID_ERROR' => '9'
    ];
    private $facetofaceSignError = [
        'INVALID_PARAMETER' => '3',
        'INVALID_BATCH_NO' => '3',
        'BATCH_IS_NOT_EXIST' => '3',
        'BATCH_STATUS_IS_FINAL' => '9',
        'SYSTEM_ERROR' => '3',
        'ISV_APP_ORDER_PACKAGE_EMPTY' => '9',
        'ISV_APP_NO_ORDER_PACKAGE' => '9',
        'MERCHANT_SIGN_PRODUCT_IN_AUDIT' => '6',
        'MERCHANT_SIGN_PRODUCT_IN_FORBIDD' => '9',
        'NO_APP_PERMISSION' => '3',
        'FILE_SIZE_OUT_LIMIT' => '3',
        'FILE_SIZE_MIN_LIMIT' => '3',
        'FILE_FORMAT_IS_INVALID' => '3',
        'PRE_AUTH_INVALID_AUTH_TICKET' => '9',
        'PRE_AUTH_INVALID_AUTH_TOKEN' => '9',
        'PRE_AUTH_INVOKE_API_NOT_PERMITTE' => '9',
        'PRE_AUTH_INVALID_PACKAGE' => '9',
        'PRE_AUTH_INVALID_AUTH_APP_ID' => '9',
        'MERCHANT_NEED_FACE_CERTIFY' => '3',
        'MERCHANT_STATE_NOT_SATISFY' => '3',
        'UN_SUPPORT_ACCOUNT_CERTIFY_LEVEL' => '3',
        'UN_SUPPORT_ACCOUNT_TYPE' => '3',
        'ANT_PRODUCT_CONFLICT' => '9',
        'USER_CARD_BALANCE_PAY_CLOSED' => '3',
        'CHECK_AGDS_SELLER_ACCESS_FAILED' => '9',
        'ANT_PRODUCT_DEPENDENCY_REQUIRED' => '3',
        'FACE_TO_FACE_RATE_PARAM_ERROR' => '3',
        'RISK_ADVICE_ERROR' => '9',
        'USER_ACCOUNT_IS_BLOCK' => '9',
        'NOT_MATCHED_SSU_OR_PS' => '3',
        'BUSINESS_LICENSE_PIC_RISK' => '3',
        'SHOP_SIGN_BOARD_PIC_RISK' => '3',
        'UNKNOWN_EXCEPTION' => '3',
        'BIZ_ERROR' => '3',
        'ISV_IDENTITY_NOT_SATISFY' => '3'
    ];
    /**
     * 支付宝进件 添加订单
     */
    function aliincoming()
    {
        $post = input('post.');
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        $ali_incoming_parts_id = input('ali_incoming_parts_id');
        $aliIncomingParts = AliIncomingParts::where(['id' => $ali_incoming_parts_id, 'pf_id' => $user['pf_id']])->find();
        if (empty($aliIncomingParts)) {
            return error('套餐错误');
        }
        if (empty($post['mcc_code1']) || empty($post['mcc_code2'])) {
            return error('类目错误');
        }
        $mcc_code = $post['mcc_code1'] . '_' . $post['mcc_code2'];
        $post['rate'] = $aliIncomingParts->rate;
        $post['mcc_code'] = $mcc_code;
        if (empty($post['rate']) || $post['rate'] < 0.38 || $post['rate'] > 0.6) {
            return error('服务费率错误' . $post['rate']);
        }
        if (!empty($post['sign_and_auth']) && empty($post['rate'])) {
            return error('服务费率不能为空');
        }
        // $num = $aliIncomingParts->cost;
        if (empty($post['mcc_code1'])) {
            return error('类目1错误');
        }
        if (empty($post['mcc_code2'])) {
            return error('类目2错误');
        }

        $alipaymcc = file_get_contents(public_path() . 'static' . DS . 'alipaymccC2.json');
        $alipaymcc = json_decode($alipaymcc, true);
        if (empty($alipaymcc[$post['mcc_code1']])) {
            return error('类目错误1');
        }
        $tmpmcc1 = $alipaymcc[$post['mcc_code1']];
        $mcc_code2 = [];
        foreach ($tmpmcc1 as $k => $v) {
            if ($v['code'] == $post['mcc_code2']) {
                $mcc_code2 = $v;
                break;
            }
        }
        if (empty($mcc_code2)) {
            return error('类目错误2');
        }
        if (!empty($mcc_code2['specialqualifications']) && empty($post['special_license_pic'])) {
            return error('该类目必须上传企业特殊资质图片');
        }

        if (empty($post['account'])) {
            return error('商户账号错误');
        }
        if (empty($post['contact_name'])) {
            return error('联系人名称错误');
        }
        if (empty($post['contact_mobile'])) {
            return error('联系人手机号码错误');
        }
        Db::startTrans();
        // 1 未支付
        // 2 待创建事务
        // 3: 事务创建成功
        // 4: 签约成功
        // 5: 已提交事务
        // 6: 审核中
        // 7: 商户已拒绝
        // 8: 等待商家签约
        // 9: --
        $status = $aliIncomingParts->cost > 0 ? 1 : 2;
        try {
            $order = AliOrders::create([
                'order_id' => '',
                'user_id' => $user['id'],
                'pf_id' => $pf_id,
                'status' => $status,
                'num' => $aliIncomingParts->cost,
                'retail_num' => sysconfig('retail_config', 'retail_status' . $pf_id) ? $aliIncomingParts->retail_num : 0,
            ]);
            $post['ali_orders_id'] = $order['id'];
            $res = AliOrdersInfo::create($post);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return error('进件失败:' . $e->getMessage());
        }
        //是否要支付
        if (!empty($aliIncomingParts->cost)) {
            return success('待支付', ['status' => $status, 'order_id' => $order['order_id']]);
        }
        $res = $this->commonIncoming($order);
        return success($res['msg'], ['status' => $res['status']]);
    }
    /**
     * 再次提交
     */
    function subincoming()
    {
        $id = input('id');
        $user = Users::where('id', request()->id)->find();
        $order = AliOrders::where(['id' => $id, 'user_id' => $user['id']])->find();
        $aliOrdersInfo = AliOrdersInfo::where(['ali_orders_id' => $order['id']])->find();
        if (empty($aliOrdersInfo) || empty($order)) {
            return error('订单错误');
        }
        $status = $order['status'];
        //是否要支付
        if ($order['status'] == 1) {
            return error('待支付', ['status' => $order['status']]);
        }
        $alipayService = new AlipayService($user['pf_id']);
        if ($order['status'] == 2) {
            //创建事务
            $res = $alipayService->agentCreate($aliOrdersInfo);
            Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 'subincoming 创建事务返回' . $order['id']);
            if ($res['code'] == 10000) {
                $order->where('id', $order['id'])->update([
                    "code" => "10000",
                    "msg" => "事务创建成功",
                    "batch_status" => $res['batch_status'],
                    "status" => 3
                ]);
                $status = 3;
                AliOrdersInfo::where(['ali_orders_id' => $order['id']])->save(['batch_no' => $res['batch_no']]);
                $aliOrdersInfo['batch_no'] = $res['batch_no'];
                $res = $alipayService->facetofaceSign($aliOrdersInfo);
                if ($res['code'] == 10000) {
                    $order->where('id', $order['id'])->update([
                        "code" => "10000",
                        "msg" => "签约成功",
                        "sub_msg" => "签约成功",
                        "status" => 4
                    ]);
                    $status = 4;
                    $res = $alipayService->agentCommonsignConfirm($aliOrdersInfo['batch_no']);
                    if ($res['code'] == 10000) {
                        $order->where('id', $order['id'])->update([
                            "code" => "10000",
                            "msg" => "已提交信息确认",
                            "status" => 5
                        ]);
                        $status = 5;
                    } else {
                        $status = !empty($this->agentCommonsignConfirmError[$res['sub_code']]) ? $this->agentCommonsignConfirmError[$res['sub_code']] : '3';
                        $order->where('id', $order['id'])->update([
                            "code" => $res['code'],
                            "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                            "sub_code" => $res['sub_code'],
                            "sub_msg" => $res['sub_msg'],
                            "status" => $status,
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
                $status = !empty($this->facetofaceSignError[$res['sub_code']]) ? $this->facetofaceSignError[$res['sub_code']] : '3';
                $data = [
                    "code" => $res['code'],
                    "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                    "sub_code" => $res['sub_code'],
                    "sub_msg" => $res['sub_msg'],
                ];
                if (!empty($status)) {
                    $data['status'] = $status;
                }
                $order->where('id', $order['id'])->update($data);
            }
        } else if ($order['status'] == 3) {
            $res = $alipayService->facetofaceSign($aliOrdersInfo);
            Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '签约返回' . $order['id']);
            if ($res['code'] == 10000) {
                $order->where(['id' => $id])->update([
                    "code" => "10000",
                    "msg" => "签约成功",
                    "status" => 4
                ]);
                $status = 4;
            } else {
                $order->where(['id' => $id])->update([
                    "code" => $res['code'],
                    "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                    "sub_code" => $res['sub_code'],
                    "sub_msg" => $res['sub_msg'],
                ]);
            }
        } else if ($order['status'] == 4) {
            //提交确认信息
            $res = $alipayService->agentCommonsignConfirm($aliOrdersInfo['batch_no']);
            if ($res['code'] == 10000) {
                $order->where(['id' => $id])->update([
                    "code" => "10000",
                    "msg" => "已提交信息确认",
                    "status" => 5
                ]);
                $status = 5;
            } else {
                $order->where(['id' => $id])->update([
                    "code" => $res['code'],
                    "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                    "sub_code" => $res['sub_code'],
                    "sub_msg" => $res['sub_msg'],
                    "status" => 3,
                ]);
            }
        } else {
            return success('当前状态禁止编辑', ['status' => $status]);
        }
        $msg = !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'];
        return success($msg, ['status' => $status]);
    }


    function updateDetail()
    {
        $id = input('id');
        $order = AliOrders::where(['id' => $id])->find();
        $orderInfo = AliOrdersInfo::where(['ali_orders_id' => $id])->find();
        if (empty($orderInfo)  || empty($order)) {
            return error('订单错误');
        }
        if ($order['status'] > 5) {
            return error('当前状态禁止编辑');
        }
        $post = input('post.');
        $mcc_code = $post['mcc_code1'] . '_' . $post['mcc_code2'];
        // unset($post['mcc_code1'], $post['mcc_code2'], $post['file']);
        $data = [];
        $data['mcc_code'] = $mcc_code;
        $data['special_license_pic'] = $post['special_license_pic'] ?? '';
        // $data['rate'] = $post['rate'];
        $data['sign_and_auth'] = $post['sign_and_auth'] ?? '';
        $data['business_license_no'] = $post['business_license_no'] ?? '';
        $data['business_license_pic'] = $post['business_license_pic'] ?? '';
        $data['business_license_auth_pic'] = $post['business_license_auth_pic'] ?? '';
        $data['long_term'] = $post['long_term'] ?? '';
        $data['date_limitation'] = $post['date_limitation'] ?? '';
        $data['shop_scene_pic'] = $post['shop_scene_pic'] ?? '';
        $data['shop_sign_board_pic'] = $post['shop_sign_board_pic'] ?? '';
        $data['shop_name'] = $post['shop_name'] ?? '';
        $data['business_license_mobile'] = $post['business_license_mobile'] ?? '';
        $data['account'] = $post['account'] ?? '';
        $data['contact_name'] = $post['contact_name'] ?? '';
        $data['contact_mobile'] = $post['contact_mobile'] ?? '';
        $data['contact_email'] = $post['contact_email'] ?? '';
        $data['province_code'] = $post['province_code'] ?? '';
        $data['city_code'] = $post['city_code'] ?? '';
        $data['district_code'] = $post['district_code'] ?? '';
        $data['detail_address'] = $post['detail_address'] ?? '';
        $data['shop_address'] = $post['shop_address'] ?? '';
        // provinceCityDistrict: "湖北省-宜昌市-兴山县"
        $orderInfo->where(['id' => $orderInfo['id']])->update($data);
        return success('更新成功');
    }
    /**
     * 订单详情
     */
    function detail()
    {
        $id = input('id');
        $order = AliOrders::where(['ali_orders.id' => $id])->withJoin(['aliOrdersInfo'])->find();
        if (empty($order)) {
            return error('订单错误');
        }
        return success('更新成功', $order);
    }
    /**
     * 类目
     */
    function test()
    {
        $mcccodejson = file_get_contents(public_path() . 'static' . DS . 'alipaymcc.json');
        $mcccodejson = json_decode($mcccodejson, true);
        $categoryI = [];
        // foreach ($mcccodejson as $k => $v) {
        //     $categoryI[$v['一级类目code']] = [
        //         'code' => $v['一级类目code'],
        //         'name' => $v['一级类目名称'],
        //     ];
        // }
        //写入json
        // $categoryI
        // $mcccodejson = file_put_contents(public_path() . 'static' . DS . 'alipaymccC1.json', json_encode(array_values($categoryI), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        // $categoryII
        $categoryII = [];
        foreach ($mcccodejson as $k => $v) {
            $categoryII[$v['一级类目code']][] = [
                'code' => $v['二级类目code'],
                'name' => $v['二级类目'],
                'ontrial' => $v['适用商家'],
                'specialqualifications' => $v['特殊资质'],
                'examples' => $v['部分资质示例'],
            ];
        }
        // exit;
        // $mcccodejson = file_put_contents(public_path() . 'static' . DS . 'alipaymccC2.json', json_encode(($categoryII), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        exit;
    }
    /**
     * 取消事务
     */
    function agentCancel()
    {
        $id = input('id');
        $order = AliOrders::where(['id' => $id])->find();
        if (empty($order['pf_id'])) {
            return error('订单错误');
        }
        if (empty($order['status']) && !in_array($order['status'], [2, 3, 4])) {
            return error('订单状态 禁止取消事务');
        }
        $alipayService = new AlipayService($order['pf_id']);
        $res = $alipayService->agentCancel($order['batch_no']);
        if ($res['code'] == 10000) {
            $errmsg = !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'];
            $order->where(['id' => $id])->update([
                "msg" => $errmsg,
                "sub_msg" => $errmsg,
                "status" => 2,
            ]);
            return success('取消事务成功', ['status' => 2]);
        } else {
            return error($res['sub_msg']);
        }
    }
    /**
     * 查询
     */
    function agentOrderQuery()
    {
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        $id = input('id');
        $order = AliOrders::where(['id' => $id])->find();
        $aliOrdersInfo = AliOrdersInfo::where(['ali_orders_id' => $order['id']])->find();
        $alipayService = new AlipayService($pf_id);
        if (empty($aliOrdersInfo['batch_no'])) {
            return error('订单错误');
        }
        $res = $alipayService->agentOrderQuery($aliOrdersInfo['batch_no']);

        $status = $order['status'];
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
                $order->where(['id' => $id])->update([
                    "msg" => $errmsg,
                    "sub_msg" => $errmsg,
                    "status" => 3,
                ]);
                $status = 3;
            } else if ($res['order_status'] == 'MERCHANT_AUDITING') {
                $order->where(['id' => $id])->update([
                    "msg" => '审核中，申请信息正在人工审核中',
                    "sub_msg" => '审核中，申请信息正在人工审核中',
                    "status" => 6,
                ]);
                $status = 6;
                $errmsg = '审核中，申请信息正在人工审核中';
            } else if ($res['order_status'] == 'MERCHANT_CONFIRM') {
                $order->where(['id' => $id])->update([
                    "msg" => '待商户确认，申请信息审核通过，等待联系人确认签约或授权',
                    "sub_msg" => '待商户确认，申请信息审核通过，等待联系人确认签约或授权',
                    "status" => 6,
                ]);
                $status = 6;
                $errmsg = '待商户确认，申请信息审核通过，等待联系人确认签约或授权';
            } else if ($res['order_status'] == 'MERCHANT_CONFIRM_SUCCESS') {
                $order->where(['id' => $id])->update([
                    "msg" => '商户确认成功，商户同意签约或授权',
                    "sub_msg" => '商户确认成功，商户同意签约或授权',
                    "status" => 6,
                ]);
                $status = 6;
                $errmsg = '商户确认成功，商户同意签约或授权';
            } else if ($res['order_status'] == 'MERCHANT_CONFIRM_TIME_OUT') {
                $order->where(['id' => $id])->update([
                    "msg" => '商户超时未确认，如果商户受到确认信息15天内未确认，则需要重新提交申请信息',
                    "sub_msg" => '商户超时未确认，如果商户受到确认信息15天内未确认，则需要重新提交申请信息',
                    "status" => 3,
                ]);
                $status = 3;
                $errmsg = '商户超时未确认，如果商户受到确认信息15天内未确认，则需要重新提交申请信息';
            } else if ($res['order_status'] == 'MERCHANT_APPLY_ORDER_CANCELED') {
                $order->where(['id' => $id])->update([
                    "msg" => '审核失败或商户拒绝，申请信息审核被驳回，或者商户选择拒绝签约或授权',
                    "sub_msg" => '审核失败或商户拒绝，申请信息审核被驳回，或者商户选择拒绝签约或授权',
                    "status" => 7,
                ]);
                $status = 7;
                $errmsg = '审核失败或商户拒绝，申请信息审核被驳回，或者商户选择拒绝签约或授权';
            } else {
                $errmsg = json_encode($res, JSON_UNESCAPED_UNICODE);
            }
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
                $order->where(['id' => $id])->update($updateData);
            }
            return success($errmsg, ['status' => $status]);
        } else {
            $msg = !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'];
            $order->where(['id' => $id])->update([
                "msg" => $msg,
                "sub_msg" => $msg,
                "code" => $res['code'],
                "status" => 2,
                "sub_code" => !empty($res['sub_code']) ? $res['sub_code'] : '',
            ]);
            return error($msg, ['status' => $order['status']]);
        }
    }


    /**
     * 一级类目
     */
    function alipaymcc()
    {
        $code = input('post.code'); //c1 code
        if (empty($code)) {
            //一级类目 alipaymccC1.json
            $alipaymccC = file_get_contents(public_path() . 'static' . DS . 'alipaymccC1.json');
            $alipaymccC = json_decode($alipaymccC, true);
        } else {
            $alipaymccC = file_get_contents(public_path() . 'static' . DS . 'alipaymccC2.json');
            $alipaymccC = json_decode($alipaymccC, true);
            $alipaymccC = $alipaymccC[$code];
        }
        return success('', $alipaymccC);
    }
    /**
     * 套餐 aliIncoming_parts
     */
    function aliIncomingParts()
    {
        $pf_id = input('pf_id');
        $list = AliIncomingParts::where(['pf_id' => $pf_id])->order('id asc')->select();
        return success('', $list);
    }


    /**
     * 上传文件到本地
     */
    function uploadLocal()
    {
        $data = [
            'upload_type' => '',
            'file'        => request()->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        // empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $uploadConfig['upload_allow_type'] = 'local';
        $data['upload_type'] = 'local';
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        validate()->check($data, $rule);
        try {
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
        if ($upload['save'] == true) {
            return success($upload['msg'], ['url' => $upload['url']]);
        } else {
            return error($upload['msg']);
        }
    }
    /**
     * 支付宝 进件 识别营业执照且上传本地
     */
    public function  uploadLocalAliBaiduapi()
    {
        $data = [
            'upload_type' => '',
            'file'        => request()->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        // empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $uploadConfig['upload_allow_type'] = 'local';
        $data['upload_type'] = 'local';
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        validate()->check($data, $rule);
        try {
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
        if ($upload['save'] != true) {
            return error($upload['msg']);
        }
        $api = new BaiDuApi(session('admin.id'));
        $res = '';
        $res =  $api->get_business_pic_info(base64_encode(file_get_contents(public_path() . DIRECTORY_SEPARATOR . $upload['url'])));
        $res['url'] = $upload['url'];
        if ($res) {
            return success('识别成功', $res);
        }
        return error('识别失败' + $upload['url']);
    }



    /**
     * 卡密支付
     */
    public function code_pay()
    {
        if (!input('post.order_id') || !input('post.code')) {
            return error('参数错误');
        }
        $order_id = input('post.order_id');
        $order = AliOrders::where('order_id', $order_id)->where('user_id', request()->id)->find();
        if (!$order || $order->status != 1) {
            return error('订单状态错误');
        }
        $code = input('post.code');
        $ident_code = ActiveIdentCode::where('code', $code)->lock(true)->where('status', 0)->find();
        if (!$ident_code) {
            return error('激活码错误！');
        }

        Db::startTrans();
        try {
            //更新激活码状态
            $ident_code->where('id', $ident_code->id)->update([
                'order_id' => $order_id,
                'user_id' => request()->id,
                'status' => 1,
            ]);
            //更新订单状态
            AliOrders::where('order_id', $order_id)->update([
                'pay_type' => 3,
                'status' => 2,
            ]);
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return error('支付失败');
        }

        $res = $this->commonIncoming($order);
        return success($res['msg'], ['status' => $res['status']]);
    }


    /**
     * 微信支付
     */
    public function wx_pay()
    {
        if (!input('post.order_id')) {
            return error('参数错误');
        }

        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        try {
            $order_id = input('post.order_id');
            $order = AliOrders::where('order_id', $order_id)->where('user_id', request()->id)->find();
            if (!$order || $order->status != 1) {
                return error('订单状态错误');
            }
            $type = request()->user->type == 1 ? 'xcx' : 'mp';
            $api = new wxApi($type, $pf_id);
            $notify_url = input('server.REQUEST_SCHEME') . '://' . request()->host() . '/api/alipay/wx_notify/pf_id/' . $pf_id;
            Log::write('支付宝进件 唤起微信支付');
            $res = $api->wxPay(request()->user->open_id, $order_id, $order->num, $notify_url);
            if ($res) {
                return success('获取成功', $res);
            } else {
                return error('获取失败');
            }
        } catch (Exception $e) {
            return error('获取失败');
        }
    }
    /**
     * 微信回调
     */
    public function wx_notify()
    {

        Log::write('支付宝进件 微信支付回调');
        $post = input('post.');
        $REQUEST_URI = explode('pf_id', $_SERVER['REQUEST_URI']);
        Log::write('Incoming 微信回调 wx_notify 完成地址_____' . json_encode($REQUEST_URI));
        if (preg_match('/\d+/', $REQUEST_URI[count($REQUEST_URI) - 1], $pf_id)) {
            $pf_id = $pf_id[0];
        }
        Log::write('Incoming 微信回调 wx_notify pf_id_____' . $pf_id);
        if ($post['event_type'] == "TRANSACTION.SUCCESS") {
            $data =   $post['resource'];
            $api = new wxApi('xcx', $pf_id);
            $res =     $api->decrpt($data['ciphertext'], $data['associated_data'], $data['nonce']);
            if ($res) {
                $res = json_decode($res, true);
                if ($res['trade_state'] == 'SUCCESS') {
                    $order = AliOrders::where('order_id', $res['out_trade_no'])->find();
                    if ($order) {
                        if ($res['amount']['total'] == 100 * $order->num) {
                            //支付成功 更新订单状态
                            if (is_array($res)) {
                                $order->save([
                                    'status' => 2,
                                    // 'applyment_id' => $res['applyment_id'],
                                    'pay_type' => 2
                                ]);
                            } else {
                                $order->save([
                                    'pay_type' => 2,
                                    'error_msg' => $res,
                                ]);
                            }
                            //商户进件
                            //--------------*******************
                            try {
                                $this->commonIncoming($order);
                            } catch (Exception $e) {
                                return json_encode(['code' => 'SUCCESS', 'message' => '成功']);
                            }
                        }
                    }
                }
            }
        }
        return json_encode(['code' => 'SUCCESS', 'message' => '成功']);
    }
    /**
     * 进件
     */
    private function commonIncoming($order)
    {
        $status = $order['status'];
        //商户进件
        $aliOrdersInfo = AliOrdersInfo::where(['ali_orders_id' => $order['id']])->find();
        $alipayService = new AlipayService($order['pf_id']);
        //创建事务
        $res = $alipayService->agentCreate($aliOrdersInfo);
        Log::write(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), '卡密支付成功 subincoming 创建事务返回' . $order['id']);
        if ($res['code'] == 10000) {
            $order->where('id', $order['id'])->update([
                "code" => "10000",
                "msg" => "事务创建成功",
                "batch_status" => $res['batch_status'],
                "status" => 3
            ]);
            $status = 3;
            AliOrdersInfo::where(['ali_orders_id' => $order['id']])->save(['batch_no' => $res['batch_no']]);
            $aliOrdersInfo['batch_no'] = $res['batch_no'];
            $batch_no = $res['batch_no'];
            $res = $alipayService->facetofaceSign($aliOrdersInfo);
            if ($res['code'] == 10000) {
                $order->where('id', $order['id'])->update([
                    "code" => "10000",
                    "msg" => "签约成功",
                    "sub_msg" => "签约成功",
                    "status" => 4
                ]);
                $status = 4;
                $res = $alipayService->agentCommonsignConfirm($batch_no);
                if ($res['code'] == 10000) {
                    $order->where('id', $order['id'])->update([
                        "code" => "10000",
                        "msg" => "已提交信息确认",
                        "status" => 5
                    ]);
                    $status = 5;
                } else {
                    $status = !empty($this->agentCommonsignConfirmError[$res['sub_code']]) ? $this->agentCommonsignConfirmError[$res['sub_code']] : '3';
                    $order->where('id', $order['id'])->update([
                        "code" => $res['code'],
                        "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                        "sub_code" => $res['sub_code'],
                        "sub_msg" => $res['sub_msg'],
                        "status" => $status,
                    ]);
                }
            } else {
                $status = !empty($this->facetofaceSignError[$res['sub_code']]) ? $this->facetofaceSignError[$res['sub_code']] : '3';
                $data = [
                    "code" => $res['code'],
                    "msg" => !empty($res['sub_msg']) ? $res['sub_msg'] : $res['msg'],
                    "sub_code" => $res['sub_code'],
                    "sub_msg" => $res['sub_msg'],
                ];
                if (!empty($status)) {
                    $data['status'] = $status;
                }
                $order->where('id', $order['id'])->update($data);
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
        return ['msg' => $msg, 'status' => $status];
    }

    function aliProStatus()
    {
        $pf_id = input('pf_id');
        $ali_pro_status = sysconfig('pro_config', 'ali_pro_status' . $pf_id);
        return success('支付宝进件开关', compact('ali_pro_status'));
    }

    //网关
    function gateway(){
        return success('ok');
    }
    //回调
    function alicb(){
        return success('ok');
    }
}
