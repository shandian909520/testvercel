<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\ActiveIdentCode;
use app\admin\model\BankAllName;
use app\admin\model\InComingOrder;
use app\admin\model\InComingOrderInfo;
use app\admin\model\IncomingParts;
use app\admin\model\OrderInfo;
use app\admin\model\Orders;
use app\admin\model\Users;
use app\common\lib\BaiDuApi;
use app\common\lib\ProApi;
use app\common\lib\wxApi;
use EasyAdmin\upload\Uploadfile;
use Exception;
use think\facade\Db;
use think\facade\Filesystem;
use think\facade\Log;

class InComing
{

    public function proapi()
    {
        //商户进件测试
        $order = InComingOrder::where('id', $_GET['id'])->find();
        if (empty($order) || !in_array($order->status, [0, 4])) {
            return error('订单不存在或状态错误！');
        }
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        $api = new ProApi($pf_id);
        $res =   $api->applyment($order);
        if (is_array($res)) {
            $order->save([
                'status' => 2,
                'applyment_id' => $res['applyment_id'],
            ]);
        } else {
            $order->save([
                'status' => 4,
                'error_msg' => $res,
            ]);
        }
        if (is_array($res)) {
            echo json_encode($res);
        } else {
            echo ($res);
        }

        exit;
    }
    /**
     * 商户进件开关
     */
    public function pro_status()
    {
        $pf_id = input('pf_id', 0);
        $pro_status = sysconfig('pro_config', 'pro_status' . $pf_id);
        return success('成功', compact('pro_status'));
    }

    /**
     * 商户进件套餐
     */
    public function incoming_parts()
    {
        $pf_id = input('pf_id', 0);
        $incoming_parts =   IncomingParts::where(['pf_id' => $pf_id])->field('id,rate,cost')->select();
        return success('查询成功', compact('incoming_parts'));
    }

    /**
     * 商户进件配置信息
     */
    public function incoming_config()
    {
        $subject_type = config('inComing.subject_type');
        $sales_scenes_type = config('inComing.sales_scenes_type');
        $bank_account_type = config('inComing.bank_account_type');
        $account_banks = config('inComing.account_banks');

        return success('查询成功', compact('subject_type', 'sales_scenes_type', 'bank_account_type', 'account_banks'));
    }

    /**
     * 查询支行信息
     */
    public function bank_all_name()
    {
        $list = BankAllName::where(function ($query) {
            if (input('name')) {
                $query->where('name', 'like', "%" . input('name') . "%");
            }
        })->field('name')->paginate(15);
        return success('成功', compact('list'));
    }



    /**
     * 选择商户类型
     */
    public function select_merchant_type()
    {
        if (empty(input('post.type'))) {
            return error('参数错误');
        }

        //获取未支付的订单
        $order = InComingOrder::where('user_id', request()->id)->where('status', 1)->find();
        $order_id = '';
        if (!$order) {
            $order = InComingOrder::create([
                'order_id' => '',
                'user_id' => request()->id
            ]);
        }
        //
        if (empty($order->orderInfo)) {
            $order->orderInfo()->save(['subject_type' => input('post.type')]);
        } else {
            $order->orderInfo->save(['subject_type' => input('post.type')]);
        }

        $order_id = $order->order_id;
        return success('成功', compact('order_id'));
    }



    /**
     * 填写商户主体信息
     */
    public function subject_info()
    {
        if (empty(input('post.order_id'))) return error("参数错误");
        if (empty(input('post.license_copy'))) return error("参数错误");
        if (empty(input('post.license_copy_link'))) return error("参数错误");
        if (empty(input('post.merchant_name'))) return error("参数错误");
        if (empty(input('post.license_number'))) return error("参数错误");
        if (empty(input('post.merchant_shortname'))) return error("参数错误");
        if (empty(input('post.service_phone'))) return error("参数错误");
        if (empty(input('post.settlement_id'))) return error("参数错误");
        if (empty(input('post.qualification_type'))) return error("参数错误");

        if (empty(input('post.incoming_part_id'))) return error("参数错误");
        $incoming_part = IncomingParts::where('id', input('incoming_part_id'))->find();
        if (empty($incoming_part)) {
            return error('该套餐不存在');
        }
        $order = InComingOrder::where('order_id', input('post.order_id'))->find();
        if (empty($order) || !in_array($order->status, [1, 4])) {
            return error('订单不存在或状态错误！');
        }
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        Db::startTrans();
        try {
            $order->orderInfo->save([
                'license_copy' => input('post.license_copy'),
                'license_copy_link' => input('post.license_copy_link'),
                'merchant_name' => input('post.merchant_name'),
                'license_number' => input('post.license_number'),
                'merchant_shortname' => input('post.merchant_shortname'),
                'service_phone' => input('post.service_phone'),
                'settlement_id' => input('post.settlement_id'),
                'qualification_type' => input('post.qualification_type'),
                'qualifications' => input('post.qualifications'),
                'qualifications_link' => input('post.qualifications_link'),
                'activities_id' => sysconfig('pro_config', 'pro_activities_id' . $pf_id),
                'activities_rate' => $incoming_part->rate,
            ]);
            $order->save([
                'num' => $incoming_part->cost,
                'retail_num' => sysconfig('retail_config', 'retail_status' . $pf_id) ? $incoming_part->retail_num : 0,
            ]);
            Db::commit();
            return success('成功');
        } catch (Exception $e) {
            Db::rollback();
            return error('失败');
        }
    }

    /**
     * 填写经营信息
     */
    public function   business_info()
    {
        Log::write(input(), 'debug_____');
        if (empty(input('post.order_id'))) return error("参数错误");
        if (empty(input('post.sales_scenes_type'))) return error("参数错误");

        $order = InComingOrder::where('order_id', input('post.order_id'))->find();
        if (empty($order) || !in_array($order->status, [1, 4])) {
            return error('订单不存在或状态错误！');
        }
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        Db::startTrans();
        try {
            $order->orderInfo->save([
                'sales_scenes_type' => input('post.sales_scenes_type'),
                'biz_store_name' => input('post.biz_store_name'),
                'biz_address_code' => input('post.biz_address_code'),
                'biz_store_address' => input('post.biz_store_address'),
                'store_entrance_pic' => input('post.store_entrance_pic'),
                'store_entrance_pic_link' => input('post.store_entrance_pic_link'),
                'indoor_pic' => input('post.indoor_pic'),
                'indoor_pic_link' => input('post.indoor_pic_link'),
                'mp_pics' => input('post.mp_pics'),
            ]);

            if (in_array('SALES_SCENES_MP', explode(',', input('post.sales_scenes_type') ?: []))) {
                if (empty(input('post.sub_type'))) {
                    $mp_appid = sysconfig('pro_config', 'pro_app_id' . $pf_id);
                } else {
                    $mp_appid = input('post.mp_appid');
                }
                $mp_pics_link = input('post.mp_pics_link');
                if (!empty($mp_pics_link)) {
                    $mp_pics_link = htmlspecialchars_decode(input('post.mp_pics_link'));
                }
                $order->orderInfo->save([
                    'mp_appid' => $mp_appid,
                    'sub_type' => input('post.sub_type'),
                    'mp_pics_link' => $mp_pics_link,
                ]);
            }
            Db::commit();
            return success('成功');
        } catch (Exception $e) {
            Db::rollback();
            return error('失败' . $e->getMessage());
        }
    }

    /**
     * 填写法人信息
     */
    public function legal_persion_info()
    {
        if (empty(input('post.order_id'))) return error("参数错误");
        if (empty(input('post.id_card_copy'))) return error("参数错误");
        if (empty(input('post.id_card_copy_link'))) return error("参数错误");
        if (empty(input('post.id_card_national'))) return error("参数错误");
        if (empty(input('post.id_card_national_link'))) return error("参数错误");
        if (empty(input('post.id_card_name'))) return error("参数错误");
        if (empty(input('post.id_card_number'))) return error("参数错误");
        if (empty(input('post.card_period_begin'))) return error("参数错误");
        if (empty(input('post.card_period_end'))) return error("参数错误");
        if (empty(input('post.mobile_phone'))) return error("参数错误");
        if (empty(input('post.contact_email'))) return error("参数错误");
        // if (empty(input('post.contact_type'))) return error("参数错误");
        if (empty(input('post.id_card_address'))) return error("参数错误");

        $order = InComingOrder::where('order_id', input('post.order_id'))->find();
        if (empty($order) || !in_array($order->status, [1, 4])) {
            return error('订单不存在或状态错误！');
        }
        Db::startTrans();
        try {
            $order->orderInfo->save([
                'id_card_copy' => input('post.id_card_copy'),
                'id_card_copy_link' => input('post.id_card_copy_link'),
                'id_card_national' => input('post.id_card_national'),
                'id_card_national_link' => input('post.id_card_national_link'),
                'id_card_name' => input('post.id_card_name'),
                'id_card_number' => input('post.id_card_number'),
                'card_period_begin' => input('post.card_period_begin'),
                'card_period_end' => input('post.card_period_end'),

                'mobile_phone' => input('post.mobile_phone'),
                'contact_email' => input('post.contact_email'),
                'contact_type' => input('post.contact_type'),
                'contact_id_doc_copy' => input('post.contact_id_doc_copy'),


                'contact_id_doc_copy_back' => input('post.contact_id_doc_copy_back'),
                'contact_period_begin' => input('post.contact_period_begin'),
                'contact_period_end' => input('post.contact_period_end'),
                'business_authorization_letter' => input('post.business_authorization_letter'),


                'id_card_address' => input('post.id_card_address'),

                'contact_id_doc_copy_link' => input('post.contact_id_doc_copy_link'),
                'contact_id_doc_copy_back_link' => input('post.contact_id_doc_copy_back_link'),
                'business_authorization_letter_link' => input('post.business_authorization_letter_link'),
            ]);

            if (input('post.contact_own')) {
                $order->orderInfo->save([
                    'contact_name' => input('post.id_card_name'),
                    'contact_id_number' => input('post.id_card_number'),
                ]);
            } else {
                $order->orderInfo->save([
                    'contact_name' => input('post.contact_name'),
                    'contact_id_number' => input('post.contact_id_number'),
                ]);
            }
            Db::commit();
            return success('成功');
        } catch (Exception $e) {
            Db::rollback();
            return error('失败');
        }
    }

    /**
     * 填写银行信息
     */
    public function bank_info()
    {
        if (empty(input('post.order_id'))) return error("参数错误");
        if (empty(input('post.bank_account_type'))) return error("参数错误");
        if (empty(input('post.account_name'))) return error("参数错误");

        if (input('post.account_name') == '其他银行') {

            if (empty(input('post.bank_name'))) {
                return error("参数错误");
            }
        }
        if (empty(input('post.account_bank'))) return error("参数错误");
        if (empty(input('post.bank_address_code'))) return error("参数错误");
        if (empty(input('post.account_number'))) return error("参数错误");

        $order = InComingOrder::where('order_id', input('post.order_id'))->find();
        if (empty($order) || !in_array($order->status, [1, 4])) {
            return error('订单不存在或状态错误！');
        }

        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        Db::startTrans();
        try {

            $order->orderInfo->save([
                'bank_account_type' => input('post.bank_account_type'),
                'account_name' => input('post.account_name'),
                'account_bank' => input('post.account_bank'),
                'bank_address_code' => input('post.bank_address_code'),
                'account_number' => input('post.account_number'),
                'bank_name' => input('post.bank_name'),
            ]);
            if ($order->num == 0 || $order->status == 4) {
                //商户进件
                $api = new ProApi($pf_id);
                $res =   $api->applyment($order);
                if (is_array($res)) {
                    $order->save([
                        'status' => 2,
                        'applyment_id' => $res['applyment_id'],
                    ]);
                } else {
                    $order->save([
                        'status' => 4,
                        'error_msg' => $res,
                    ]);
                }
            }
            $order_id = $order->order_id;
            $status = $order->status;
            Db::commit();
            return success('成功', compact('order_id', 'status'));
        } catch (Exception $e) {
            Db::rollback();
            return error('失败');
        }
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
        $order = InComingOrder::where('order_id', $order_id)->where('user_id', request()->id)->find();
        if (!$order || $order->status != 1) {
            return error('订单状态错误');
        }

        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        $code = input('post.code');
        Db::startTrans();
        try {
            $ident_code = ActiveIdentCode::where('code', $code)->lock(true)->where('status', 0)->find();
            if (!$ident_code) {
                return error('激活码错误！');
            }
            //更新激活码状态
            $ident_code->where('id', $ident_code->id)->update([
                'order_id' => $order_id,
                'user_id' => request()->id,
                'status' => 1,
            ]);
            //更新订单状态
            InComingOrder::where('order_id', $order_id)->update([
                'pay_type' => 3,
                'status' => 2,
            ]);
            //商户进件
            $api = new ProApi($pf_id);
            $res =   $api->applyment($order);
            if (is_array($res)) {
                $order->save([
                    'status' => 2,
                    'applyment_id' => $res['applyment_id'],
                ]);
            } else {
                $order->save([
                    'status' => 4,
                    'error_msg' => $res,
                ]);
            }
            Db::commit();
            return success('支付成功');
        } catch (Exception $e) {
            Db::rollback();
            return error('支付失败');
        }
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
            $order = InComingOrder::where('order_id', $order_id)->where('user_id', request()->id)->find();
            if (!$order || $order->status != 1) {
                return error('订单状态错误');
            }
            $type = request()->user->type == 1 ? 'xcx' : 'mp';
            $api = new wxApi($type, $pf_id);
            $notify_url = input('server.REQUEST_SCHEME') . '://' . request()->host() . '/api/incoming/wx_notify/pf_id/' . $pf_id;
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
                    $order = InComingOrder::where('order_id', $res['out_trade_no'])->find();
                    if ($order) {
                        if ($res['amount']['total'] == 100 * $order->num) {
                            //商户进件
                            $api = new ProApi($pf_id);
                            $res =   $api->applyment($order);
                            if (is_array($res)) {
                                $order->save([
                                    'status' => 2,
                                    'applyment_id' => $res['applyment_id'],
                                    'pay_type' => 2
                                ]);
                            } else {
                                $order->save([
                                    'pay_type' => 2,
                                    'status' => 4,
                                    'error_msg' => $res,
                                ]);
                            }
                        }
                    }
                }
            }
        }
        return json_encode(['code' => 'SUCCESS', 'message' => '成功']);
    }




    //上传图片
    public function upload_img()
    {
        $data = [
            'upload_type' => input('post.upload_type'),
            'file'        => request()->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        // $uploadConfig['upload_allow_size'] = 1024 * 1024 * 10;
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];

        try {
            validate()->check($data, $rule);

            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return     error($e->getMessage());
        }
        if ($upload['save'] == true) {
            $httpstr = substr($upload['url'], 0, 4);
            if ($httpstr != 'http') {
                $upload['url'] = request()->domain() . $upload['url'];
            }
            $user = Users::where('id', request()->id)->find();
            $pf_id = $user['pf_id'];
            $url = $upload['url'];
            $api = new ProApi($pf_id);
            $res = $api->upload_img($url);

            if (!is_array($res)) {
                return error('上传失败');
            }
            $media_id = $res['media_id'];
            return  success($upload['msg'], compact('url', 'media_id'));
        } else {
            return  error($upload['msg']);
        }
    }


    function upload_img2()
    {
        $id = input('post.id');
        $order = Orders::where('id', $id)->find();
        if (empty($order) || empty($order['appid'])) {
            return error('appid 错误', $order);
        }
        $data = [
            'upload_type' => input('post.upload_type'),
            'file'        => request()->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        // $uploadConfig['upload_allow_size'] = 1024 * 1024 * 10;
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];

        try {
            validate()->check($data, $rule);

            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return     error($e->getMessage());
        }

        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        if ($upload['save'] == true) {
            $httpstr = substr($upload['url'], 0, 4);
            if ($httpstr != 'http') {
                $upload['url'] = request()->domain() . $upload['url'];
            }
            $url = $upload['url'];
            $wxApi = new wxApi('mp', $pf_id);
            $res = $wxApi->media_upload($url, $order['appid']);

            if (!is_array($res)) {
                return error('上传失败');
            }
            $media_id = $res['media_id'];
            return  success($upload['msg'], compact('url', 'media_id'));
        } else {
            return  error($upload['msg']);
        }
    }


    //获取身份证信息
    public function idcard_info()
    {
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        if (!sysconfig('baidu', 'baidu_status' . $pf_id)) {
            return error('识别开关未打开');
        }

        $file = request()->file('file');
        if (!$file) {
            return error('请上传身份证正面');
        }

        $data = [
            'upload_type' => input('post.upload_type'),
            'file'        => $file,
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        // $uploadConfig['upload_allow_size'] = 1024 * 1024 * 10;
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];



        try {
            validate()->check($data, $rule);

            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return     error($e->getMessage());
        }

        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];


        if ($upload['save'] == true) {
            $api = new BaiDuApi($pf_id);
            $httpstr = substr($upload['url'], 0, 4);
            if ($httpstr != 'http') {
                $upload['url'] = request()->domain() . $upload['url'];
            }
            $res =  $api->get_idcard_info(base64_encode(file_get_contents($upload['url'])));
            if ($res) {
                return success('识别成功', $res);
            }
        } else {
            return  error($upload['msg']);
        }
        return error('识别失败');
    }
    //经营类目 入住结算规则、行业属性及特殊资质
    public function gettlement_list()
    {
        //['settlement_id' => 'settlement_id(结算规则ID)','qualifications'=>'qualifications(特殊资质)','settlement_rate'=>'settlement_rate(结算费率)','industry_id'=>'industry_id(行业id)','subject_type'=>'subject_type(主体类型)','qualification_type'=>'qualification_type(所属行业名称)'],
        $res = [
            ['settlement_id' => '703', 'subject_type' => '小微', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务业务、餐饮、零售、交通出行等实体业务', 'industry_id' => '/', 'qualification_type' => '行业名称', 'qualifications' => '/', 'special_qualifications' => '/'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '1', 'qualification_type' => '餐饮', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为餐饮业态，建议提供：《食品经营许可证》或《餐饮服务许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '2', 'qualification_type' => '电商平台', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '3', 'qualification_type' => '零售', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质，若涉及烟草售卖，需提供《烟草专卖零售许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '4', 'qualification_type' => '食品生鲜', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为食品业态，建议提供：《食品经营许可证》或《食品生产许可证》或供销协议+合作方资质'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '7', 'qualification_type' => '咨询/娱乐票务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '10', 'qualification_type' => '房产中介', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '11', 'qualification_type' => '宠物医院', 'qualifications' => '是', 'special_qualifications' => '《动物诊疗许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '12', 'qualification_type' => '共享服务', 'qualifications' => '是', 'special_qualifications' => '需提供资金监管协议。协议要求：1、主体与商业银行签订；2、内容针对交易资金使用和偿付进行监管；3、协议须在有效期内；'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '13', 'qualification_type' => '休闲娱乐/旅游服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '14', 'qualification_type' => '游艺厅/KTV', 'qualifications' => '是', 'special_qualifications' => '《娱乐场所经营许可证》或《文化经营许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '15', 'qualification_type' => '网吧', 'qualifications' => '是', 'special_qualifications' => '《网络文化经营许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '16', 'qualification_type' => '院线影城', 'qualifications' => '是', 'special_qualifications' => '《电影放映经营许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '17', 'qualification_type' => '演出赛事', 'qualifications' => '是', 'special_qualifications' => '《营业性演出许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '18', 'qualification_type' => '居民生活服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '19', 'qualification_type' => '景区/酒店', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '21', 'qualification_type' => '铁路客运', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '26', 'qualification_type' => '机票/票务代理', 'qualifications' => '是', 'special_qualifications' => '《航空公司营业执照》或《航空公司机票代理资格证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '31', 'qualification_type' => '培训机构', 'qualifications' => '是', 'special_qualifications' => '若贵司具备以下资质，建议提供：1、《办学许可证》或相关批文2、驾校培训，提供有“驾驶员培训”项目的《道路运输经营许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '34', 'qualification_type' => '保健器械/医疗器械/非处方药品', 'qualifications' => '是', 'special_qualifications' => '互联网售药提供《互联网药品信息服务资格证书》+《药品经营许可证》；线下门店卖药提供《药品经营许可证》；医疗器械提供《医疗器械经营企业许可证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '35', 'qualification_type' => '私立/民营医院/诊所', 'qualifications' => '是', 'special_qualifications' => '《医疗机构执业许可证》中医诊所提供《中医诊所备案证》'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '40', 'qualification_type' => '其他缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '719', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '56', 'qualification_type' => '停车缴费', 'qualifications' => '否', 'special_qualifications' => '请提供停车收费资质'],
            ['settlement_id' => '720', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '通讯业务', 'industry_id' => '8', 'qualification_type' => '婚介平台/就业信息平台/其他信息服务平台', 'qualifications' => '否', 'special_qualifications' => '婚介平台：《增值电信业务经营许可证》或备案 就业信息平台：《人力资源许可证》+《增值电信业务经营许可证》（“信息服务业务”字样）'],
            ['settlement_id' => '720', 'subject_type' => '个体户', 'settlement_rate' => '0.6', 'desc' => '通讯业务', 'industry_id' => '38', 'qualification_type' => '虚拟充值', 'qualifications' => '是', 'special_qualifications' => '1）自营虚拟充值业务：提供相关自营资质、与主体一致的资金监管协议等；2）他营虚拟充值业务：官方授权及合作证明以及官方所持有的自营资质、与主体一致的收费证明及资金监管协议等；3）如涉及到电信运营商、宽带收费等线上充值业务，请提供《基础电信业务经营许可证》或《增值电信业务经营许可证》；'],
            ['settlement_id' => '721', 'subject_type' => '个体户', 'settlement_rate' => '0.3', 'desc' => '加油', 'industry_id' => '5', 'qualification_type' => '快递', 'qualifications' => '是', 'special_qualifications' => '快递《快递业务经营许可证》'],
            ['settlement_id' => '721', 'subject_type' => '个体户', 'settlement_rate' => '0.3', 'desc' => '加油', 'industry_id' => '6', 'qualification_type' => '物流', 'qualifications' => '是', 'special_qualifications' => '物流《道路运输经营许可证》；从事网络货运的，提供以下三个资质《增值电信业务许可证》《三级信息系统安全等级保护备案证明》《道路运输经营许可证》；'],
            ['settlement_id' => '721', 'subject_type' => '个体户', 'settlement_rate' => '0.3', 'desc' => '加油', 'industry_id' => '20', 'qualification_type' => '加油/加气', 'qualifications' => '是', 'special_qualifications' => '成品油零售请提供《成品油批发经营批准证书》或《成品油仓储经营批准证书》或《成品油零售经营批准证书》，其中一个即可。成品油批发或仓储则需传经营范围含有“成品油批发”或“成品油仓储”字样的营业执照；汽车加气站请提供《燃气经营许可证》，证件经营类别为“燃气汽车加气站”等字样'],
            ['settlement_id' => '790', 'subject_type' => '个体户', 'settlement_rate' => '0.2', 'desc' => '民生缴费', 'industry_id' => '41', 'qualification_type' => '水电煤气缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '27', 'qualification_type' => '在线图书/视频/音乐', 'qualifications' => '是', 'special_qualifications' => '以下选其一：《互联网出版许可证》、《网络出版服务许可证》、《网络文化经营许可证》、《信息网络传播视听节目许可证》'],
            ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '28', 'qualification_type' => '门户论坛/网络广告及推广/软件开发/其他互联网服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '29', 'qualification_type' => '游戏', 'qualifications' => '是', 'special_qualifications' => '请提供有效期内的游戏版号（《网络游戏电子出版物审批》）'],
            ['settlement_id' => '746', 'subject_type' => '个体户', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '30', 'qualification_type' => '网络直播', 'qualifications' => '是', 'special_qualifications' => '需提供《网络文化经营许可证》，且许可证的经营场景应当明确包括网络表演，PC/wap网站需要有ICP备案'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '1', 'qualification_type' => '餐饮', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为餐饮业态，建议提供：《食品经营许可证》或《餐饮服务许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '2', 'qualification_type' => '电商平台', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '3', 'qualification_type' => '零售', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质，若涉及烟草售卖，需提供《烟草专卖零售许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '4', 'qualification_type' => '食品生鲜', 'qualifications' => '否', 'special_qualifications' => '选填，若贵司具备以下资质，主体为食品业态，建议提供：《食品经营许可证》或《食品生产许可证》或供销协议+合作方资质'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '7', 'qualification_type' => '咨询/娱乐票务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '9', 'qualification_type' => '房地产', 'qualifications' => '是', 'special_qualifications' => '房地产开发商提供以下五个资质：《建设用地规划许可证》《建设工程规划许可证》《建筑工程施工许可证》《国有土地使用证》《商品房预售许可证》；'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '10', 'qualification_type' => '房产中介', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '11', 'qualification_type' => '宠物医院', 'qualifications' => '是', 'special_qualifications' => '《动物诊疗许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '12', 'qualification_type' => '共享服务', 'qualifications' => '是', 'special_qualifications' => '需提供资金监管协议。协议要求：1、主体与商业银行签订；2、内容针对交易资金使用和偿付进行监管；3、协议须在有效期内；4、结算账户须与资金监管账户一致。'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '13', 'qualification_type' => '休闲娱乐/旅游服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '14', 'qualification_type' => '游艺厅/KTV', 'qualifications' => '是', 'special_qualifications' => '《娱乐场所经营许可证》或《文化经营许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '15', 'qualification_type' => '网吧', 'qualifications' => '是', 'special_qualifications' => '《网络文化经营许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '16', 'qualification_type' => '院线影城', 'qualifications' => '是', 'special_qualifications' => '《电影放映经营许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '17', 'qualification_type' => '演出赛事', 'qualifications' => '是', 'special_qualifications' => '《营业性演出许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '18', 'qualification_type' => '居民生活服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '19', 'qualification_type' => '景区/酒店', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '21', 'qualification_type' => '铁路客运', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '22', 'qualification_type' => '高速公路收费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '23', 'qualification_type' => '城市公共交通', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '24', 'qualification_type' => '船舶/海运服务', 'qualifications' => '是', 'special_qualifications' => '《港口经营许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '25', 'qualification_type' => '旅行社', 'qualifications' => '是', 'special_qualifications' => '《旅行社业务经营许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '26', 'qualification_type' => '机票/票务代理', 'qualifications' => '是', 'special_qualifications' => '《航空公司营业执照》或《航空公司机票代理资格证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '31', 'qualification_type' => '培训机构', 'qualifications' => '是', 'special_qualifications' => '若贵司具备以下资质，建议提供：1、《办学许可证》或相关批文2、驾校培训，提供有“驾驶员培训”项目的《道路运输经营许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '34', 'qualification_type' => '保健器械/医疗器械/非处方药品', 'qualifications' => '是', 'special_qualifications' => '互联网售药提供《互联网药品信息服务资格证书》+《药品经营许可证》；线下门店卖药提供《药品经营许可证》；医疗器械提供《医疗器械经营企业许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '35', 'qualification_type' => '私立/民营医院/诊所', 'qualifications' => '是', 'special_qualifications' => '《医疗机构执业许可证》中医诊所提供《中医诊所备案证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '39', 'qualification_type' => '有线电视缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '40', 'qualification_type' => '其他缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）拍卖：《拍卖经营批准证书》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '47', 'qualification_type' => '文物经营/文物复制品销售', 'qualifications' => '否', 'special_qualifications' => '选填，若销售文物，需提供《文物经营许可证》'],
            ['settlement_id' => '716', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上交易场所或信息服务的业务、通讯业务、财经类业务及其他平台服务、餐饮、零售、交通出行等实体业务', 'industry_id' => '56', 'qualification_type' => '停车缴费', 'qualifications' => '否', 'special_qualifications' => '请提供停车收费资质'],
            ['settlement_id' => '715', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '保险服务', 'industry_id' => '44', 'qualification_type' => '保险业务', 'qualifications' => '是', 'special_qualifications' => '保险公司提供《经营保险业务许可证》《保险业务法人登记证书》，其他公司提供相关资质'],
            ['settlement_id' => '807', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '典当', 'industry_id' => '48', 'qualification_type' => '典当', 'qualifications' => '是', 'special_qualifications' => '典当：《典当经营许可证》'],
            ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '8', 'qualification_type' => '婚介平台/就业信息平台/其他信息服务平台', 'qualifications' => '否', 'special_qualifications' => '婚介平台：《增值电信业务经营许可证》或备案 就业信息平台：《人力资源许可证》+《增值电信业务经营许可证》（“信息服务业务”字样）'],
            ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '38', 'qualification_type' => '虚拟充值', 'qualifications' => '是', 'special_qualifications' => '1）自营虚拟充值业务：提供相关自营资质、与主体一致的资金监管协议等；2）他营虚拟充值业务：官方授权及合作证明以及官方所持有的自营资质、与主体一致的收费证明及资金监管协议等；3）如涉及到电信运营商、宽带收费等线上充值业务，请提供《基础电信业务经营许可证》或《增值电信业务经营许可证》；'],
            ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '43', 'qualification_type' => '财经/股票类资讯', 'qualifications' => '否', 'special_qualifications' => '若有具体的荐股行为，需资质《证券投资咨询业务资格证书》'],
            ['settlement_id' => '728', 'subject_type' => '企业', 'settlement_rate' => '0.6', 'desc' => '提供网上信息服务的业务、通讯业务', 'industry_id' => '45', 'qualification_type' => '互联网募捐信息平台', 'qualifications' => '是', 'special_qualifications' => '必须符合并提供“慈善组织互联网募捐信息平台公告”截图，且必须提供资金监管协议。'],
            ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '27', 'qualification_type' => '在线图书/视频/音乐', 'qualifications' => '是', 'special_qualifications' => '以下选其一：《互联网出版许可证》、《网络出版服务许可证》、《网络文化经营许可证》、《信息网络传播视听节目许可证》'],
            ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '28', 'qualification_type' => '门户论坛/网络广告及推广/软件开发/其他互联网服务', 'qualifications' => '否', 'special_qualifications' => '无需特殊资质'],
            ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '29', 'qualification_type' => '游戏', 'qualifications' => '是', 'special_qualifications' => '请提供有效期内的游戏版号（《网络游戏电子出版物审批》）'],
            ['settlement_id' => '711', 'subject_type' => '企业', 'settlement_rate' => '1.0', 'desc' => '游戏、在线音视频等虚拟业务', 'industry_id' => '30', 'qualification_type' => '网络直播', 'qualifications' => '是', 'special_qualifications' => '需提供《网络文化经营许可证》，且许可证的经营范围应当明确包括网络表演，PC/wap网站需要有ICP备案'],
            ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '5', 'qualification_type' => '快递', 'qualifications' => '是', 'special_qualifications' => '快递《快递业务经营许可证》'],
            ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '6', 'qualification_type' => '物流', 'qualifications' => '是', 'special_qualifications' => '物流《道路运输经营许可证》；从事网络货运的，提供以下三个资质《增值电信业务许可证》《三级信息系统安全等级保护备案证明》《道路运输经营许可证》；'],
            ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '20', 'qualification_type' => '加油/加气', 'qualifications' => '是', 'special_qualifications' => '成品油零售请提供《成品油批发经营批准证书》或《成品油仓储经营批准证书》或《成品油零售经营批准证书》，其中一个即可。成品油批发或仓储则需传经营范围含有“成品油批发”或“成品油仓储”字样的营业执照；汽车加气站请提供《燃气经营许可证》，证件经营类别为“燃气汽车加气站”等字样'],
            ['settlement_id' => '717', 'subject_type' => '企业', 'settlement_rate' => '0.3', 'desc' => '民办学历教育、加油、物流快递服务', 'industry_id' => '33', 'qualification_type' => '民办学校（非全国高等学校）', 'qualifications' => '是', 'special_qualifications' => '民办非公立院校需提供《办学许可证》'],
            ['settlement_id' => '730', 'subject_type' => '企业', 'settlement_rate' => '0.2', 'desc' => '民生缴费', 'industry_id' => '41', 'qualification_type' => '水电煤气缴费', 'qualifications' => '是', 'special_qualifications' => '收费授权证明文件（如授权证明书或合同）'],
            ['settlement_id' => '808', 'subject_type' => '企业', 'settlement_rate' => '0.2', 'desc' => '银行信贷还款', 'industry_id' => '60', 'qualification_type' => '银行还款', 'qualifications' => '是', 'special_qualifications' => '1、银行业提供银监会颁发的《金融许可证》；2、提供盖章版本的补充说明，模板参考：https://kf.qq.com/faq/220415FFf6FV220415ErmAfy.html'],
            ['settlement_id' => '718', 'subject_type' => '企业', 'settlement_rate' => '0.2', 'desc' => '信贷还款', 'industry_id' => '46', 'qualification_type' => '信用还款', 'qualifications' => '是', 'special_qualifications' => '【消费金融】：《营业执照》公司名称含“消费金融”提交以下任一资料：1、《金融许可证》；2、开业期地方银保监局批复文件。【汽车金融】：《营业执照》公司名称含“汽车金融”提交以下任一资料：1、《金融许可证》；2、银保监会关于同意开展汽车金融业务的批复。【小额贷款】：《营业执照》公司名称含“小额贷款”提交以下任一资料：1、《小额贷款公司经营许可证》；2、地方金融监督管理局“小额贷款”行政许可文件。【商业保理】：《营业执照》公司名称含“商业保理”，提交以下任一资料：1、《商业保理经营许可证》；2、地方金融监督管理局批复文件。【融资租赁（实物类）】：《营业执照》公司名称含“融资租赁”，请提供“全国融资租赁企业管理信息系统”备案截图。【信托】：《营业执照》公司名称含“信托”，提交以下任一资料：1、《金融许可证》；2、中国银保监会“信托”批复文件。【融资担保】：《营业执照》公司名称含“融资担保”，提交以下任一资料：1、《融资性担保机构经营许可证》；2、《融资担保业务经营许可证》；3、地方金融监督管理局批复文件。'],
            ['settlement_id' => '739', 'subject_type' => '企业', 'settlement_rate' => '0', 'desc' => '民办大学、缴纳党费', 'industry_id' => '32', 'qualification_type' => '民办大学及院校', 'qualifications' => '是', 'special_qualifications' => '民办非公立院校需提供《办学许可证》'],
            ['settlement_id' => '739', 'subject_type' => '企业', 'settlement_rate' => '0', 'desc' => '民办大学、缴纳党费', 'industry_id' => '54', 'qualification_type' => '党费', 'qualifications' => '是', 'special_qualifications' => '1、党费专户开户许可证或结算账户申请书或银行提供的专户证明 2、党委成立文件/党委书记任命文件']
        ];
        return success('结算规则id 列表', $res);
    }
}
