<?php

declare(strict_types=1);

namespace app\api\controller;

use app\admin\model\CheckName;
use app\admin\model\OrderInfo;
use app\admin\model\Orders;
use app\admin\model\Users;
use app\common\lib\BaiDuApi;
use app\common\service\ThirdPartyService;
use Exception;
use think\facade\Db;
use think\facade\Filesystem;
use app\admin\model\SystemAdmin;
use app\api\service\ThirdPartyService as ServiceThirdPartyService;
use think\facade\Log;
use EasyAdmin\upload\Uploadfile;

class Register
{
    /**
     * 个人注册资料先写
     */
    public function person()
    {
        if (!input('post.name') || !input('post.wx_code')) {
            return error('参数错误');
        }
        $userData = Users::where('id', request()->id)->find();
        $systemadminData = SystemAdmin::where(['id' => $userData['pf_id']])->find();
        $pf_id = $userData['pf_id'];
        if (empty($systemadminData['independent'])) {
            $pf_id = 1;
        }
        $data = [
            'type' => 1,
            'name' => input('post.name'),
            'wx_code' => input('post.wx_code'),
        ];
        //判断费用开关和费用
        $flag = sysconfig('base_config', 'register_status' . $pf_id);
        $num = sysconfig('base_config', 'register_num' . $userData['pf_id']);
        
        Log::write('订单金额_________________'.sysconfig('base_config', 'register_num' . $userData['pf_id']));
        if ($flag && $num > 0) {
            $order = Orders::createOrder(0,  request()->id, 1, $data, $num, $pf_id, $userData['pf_id']);
        } else {
            //当免费时
            $order = Orders::createOrder(0,  request()->id, 2, $data, 0, $pf_id, $userData['pf_id']);
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
                'component_phone' => sysconfig('base_config', 'service_phone' . $pf_id)
            ];
            $service = new ServiceThirdPartyService();
            $res = $service->register_persion($postData, $pf_id);
            Log::write("个人注册小程序 返回 结果_________________" . json_encode($res, JSON_UNESCAPED_UNICODE));
    
            if ($res['code'] == 1) {
                if ($order->info->type == 1) {
                    $order->error_msg = '请扫码验证！';
                    $order->success_url =   $res['data']['authorize_url'];
                    $order->taskid =   $res['data']['taskid'];
                } else {
                    $order->error_msg = '请法人确认验证信息!';
                }
                $order->faststatus = !empty($res['errcode']) ? $res['errcode'] : '';
                // NoticeService::sendMsg($order, 1, '您注册的小程序已通过，等待验证！',$pf_id);
                $order->save();
            } else {
                $order->status = 3;
                $order->error_msg = $res['message'];
                $order->faststatus = !empty($res['errcode']) ? $res['errcode'] : '';
                // NoticeService::sendMsg($order, 0, '您注册的小程序未通过，错误信息：' . $res['message'],$pf_id);
                $order->save();
            }
            
        }
       
        return success('提交成功', ['order_id' => $order->order_id, 'status' => $order->status, 'faststatus' => !empty($res['errcode']) ? $res['errcode'] : '']);
    }

    /**
     * 企业注册
     */
    public function company()
    {
        if (!input('post.name') || !input('post.wx_code') || !input('post.code_type') || !input('post.code') || !input('post.person_name')) {
            return error('参数错误');
        }
        if (strlen(input('post.code')) != input('post.code_type')) {
            return error('企业代码错误！');
        }
        $userData = Users::where('id', request()->id)->find();
        $systemadminData = SystemAdmin::where(['id' => $userData['pf_id']])->find();
        $pf_id = $userData['pf_id'];
        if (empty($systemadminData['independent'])) {
            $pf_id = 1;
        }
        $data = [
            'type' => 2,
            'name' => input('post.name'),
            'code_type' => input('post.code_type'),
            'code' => input('post.code'),
            'wx_code' => input('post.wx_code'),
            'person_name' => input('post.person_name'),
        ];
        //判断费用开关和费用
        $flag = sysconfig('base_config', 'register_status' . $pf_id);
        $num = sysconfig('base_config', 'register_num' . $pf_id);
        if ($flag && $num > 0) {
            $order = Orders::createOrder(0,  request()->id, 1, $data, $num, $pf_id, $userData['pf_id']);
        } else {
            //当免费时
            $order = Orders::createOrder(0,  request()->id, 2, $data, 0, $pf_id, $userData['pf_id']);
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
                'component_phone' => sysconfig('base_config', 'service_phone' . $pf_id)
            ];
            $service = new ServiceThirdPartyService();
            $code = ['18' => 1, '9' => 2, '15' => 3];
            $postData['code_type'] = $code[$postData['code_type']];
            $res = $service->register_company($postData, $pf_id);

            if ($res['code'] == 1) {
                if ($order->info->type == 1) {
                    $order->error_msg = '请扫码验证！';
                    $order->success_url =   $res['data']['authorize_url'];
                    $order->taskid =   $res['data']['taskid'];
                } else {
                    $order->error_msg = '请法人确认验证信息!';
                }
                $order->faststatus = !empty($res['errcode']) ? $res['errcode'] : '';
                // NoticeService::sendMsg($order, 1, '您注册的小程序已通过，等待验证！',$pf_id);
                $order->save();
            } else {
                $order->status = 3;
                $order->faststatus = !empty($res['errcode']) ? $res['errcode'] : '';
                $order->error_msg = $res['message'];
                // NoticeService::sendMsg($order, 0, '您注册的小程序未通过，错误信息：' . $res['message'],$pf_id);
                $order->save();
            }
        }

        return success('提交成功', ['order_id' => $order->order_id, 'status' => $order->status, 'faststatus' => !empty($res['errcode']) ? $res['errcode'] : '']);
    }

    /**
     * 识别营业执照接口
     */
    public function discren_pic()
    {
        $pf_id = Users::where('id', request()->id)->find();
        $pf_id = $pf_id['pf_id'];
        if (!sysconfig('baidu', 'baidu_status' . $pf_id)) {
            return error('识别开关未打开');
        }

        $file = request()->file('file');


        if (!$file) {
            return error('请上传营业执照！');
        }


        $data = [
            'upload_type' => input('post.upload_type'),
            'file'        => $file,
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
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
            $api = new BaiDuApi($pf_id);
            Log::write('upload识别 '.json_encode($upload,JSON_UNESCAPED_UNICODE));
            $res =  $api->get_business_pic_info(base64_encode(file_get_contents($upload['url'])));
            if ($res) {
                return success('识别成功', $res);
            }
        } else {
            return error('识别失败');
        }
    }

    /**
     * 核名
     */
    public function check_name()
    {
        if (!input('post.name')) {
            return error('请输入名称');
        }
        $userData = Users::where('id', request()->id)->find();
        $systemadminData = SystemAdmin::where(['id' => $userData['pf_id']])->find();
        $pf_id = $userData['pf_id'];
        if (empty($systemadminData['independent'])) {
            $pf_id = 1;
        }
        Db::startTrans();
        try {
            CheckName::create([
                'user_id' => request()->id,
                'name' => input('post.name')
            ]);
            //判断核名条件 
            //1、核名开关 
            //2、核名次数不为0
            // $num = sysconfig('check_name', 'check_name_times');
            // if ($num > 0) {
            //     if (request()->user->check_name_times <= 0) {
            //         return error('可核名次数不足', '', 3);
            //     }
            //     request()->user->check_name_times = request()->user->check_name_times - 1;
            //     request()->user->save();
            // }
            Db::commit();
        } catch (Exception $e) {
            Db::rollback();
            return error('接口错误');
        }
        $name = input('post.name');
        $service = new ServiceThirdPartyService();
        $res =   $service->check_xcx_name($name, $pf_id);
        // $service = new ThirdPartyService();
        // $res =  $service->check_name(input('post.name'));
        return json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 核管理员注册
     */
    public function admin_user()
    {
        if (!input('post.username') || !input('post.password') || !input('post.repassword')) {
            return error('参数错误');
        }
        if (input('post.password') != input('post.repassword')) {
            return error('两次密码输入不一致');
        }
        $phone = input('post.phone');
        $user = SystemAdmin::where('username', input('post.username'))->count();
        if ($user > 0) {
            return error('用户已存在');
        }

        $data = [
            'username' => input('post.username'),
            'password' => password(input('post.password')),
            'phone' => $phone,
        ];

        $res = SystemAdmin::insert($data);
        if ($res) {
            return success('注册成功');
        }
    }

    /**
     * 核名次数
     */
    public function check_name_times()
    {
        $times = 0;
        //1、如果用户登录，则返回用户的可核名次数
        //2、如果用户未登录，则返回系统默认次数
        $pf_id = Users::where('id', request()->id)->find();
        $pf_id = $pf_id['pf_id'];
        if (request()->header('token')) {
            $user = Users::where('token', request()->header('token'))->find();
            if ($user) {
                $times = $user->check_name_times;
            }
        } else {
            $times = sysconfig('check_name', 'check_name_times1' . $pf_id);
        }
        $flag = false;
        if (sysconfig('check_name', 'check_name_times1' . $pf_id)) {
            $flag = true;
        }
        return success('查询成功', compact('flag', 'times'));
    }

    /**
     * 观看完广告增加次数
     */
    public function look_ad()
    {

        request()->user->check_name_times =  request()->user->check_name_times + 1;
        request()->user->save();

        return success('成功');
    }
}
