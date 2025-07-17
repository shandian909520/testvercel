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

use app\admin\model\BackUp;
use app\admin\model\OrderInfo;
use app\admin\model\Orders;
use app\admin\model\SystemAdmin;
use app\admin\model\SystemQuick;
use app\admin\model\UpdateLog;
use app\admin\model\Users;
use app\api\controller\Bmprogram;
use app\api\service\NoticeService;
use app\api\service\ThirdPartyService;
use app\common\controller\AdminController;
use app\common\lib\BaiDuApi;
use app\common\lib\wxApi;
use app\common\service\ThirdPartyService as ServiceThirdPartyService;
use Exception;
use PHPQRCode\QRcode;
use think\App;
use think\facade\Db;
use think\facade\Env;
use think\facade\Filesystem;
use think\facade\Log;
use EasyAdmin\annotation\ControllerAnnotation;



/**
 * Class Index
 * @package app\admin\controller
 * @ControllerAnnotation(title="后台首页")
 */
class Index extends AdminController
{

    protected $noLogin = ['repass'];
    protected $noAuth = ['repass'];
    public function repass()
    {
        if (!file_exists(public_path() . '/repass.key')) {
            $this->error('重置密码key错误，请先登录后台', [], __url('admin/login/index'));
        }
        $repassKey = file_get_contents(public_path() . '/repass.key');
        if ($this->request->isPost()) {
            if (empty($repassKey) || $repassKey != 1) {
                $this->error('重置密码key错误');
            }
            $password = $this->request->post('password');
            $repassword = $this->request->post('repassword');
            if (empty($password) || empty($repassword)) {
                $this->error('密码格式错误');
            }
            if ($password != $repassword) {
                $this->error('两次密码不一致');
            }
            $admin = SystemAdmin::where(['username' => 'admin'])->find();
            if (empty($admin)) {
                $this->error('管理员错误');
            }

            try {
                $admin->password = password($password);
                $save = $admin->save();
                // $save = $row
                //     ->allowField(['head_img', 'phone', 'remark', 'update_time'])
                //     ->save($post);
            } catch (\Exception $e) {
                $this->error('重置密码失败');
            }
            $save ? $this->success('重置密码成功 请重新登录', [], __url('admin/login/index')) : $this->error('重置密码失败');
        } else {
            if (!empty($repassKey) && $repassKey == 1) {
                return $this->fetch();
            } else {
                $this->error('重置密码key错误，请先登录后台', [], __url('admin/login/index'));
            }
        }
    }
    /**
     * 后台主页
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        // include root_path() . 'public/upgrade.php';
        return $this->fetch('', [
            'admin' => session('admin'),
        ]);
    }

    /**
     * @NodeAnotation(title="首页")
     */
    public function welcome()
    {
        //总用户
        $data['user_count'] = Users::where('id', '>', 0)->where('pf_id', '=', session('admin.id'))->count();
        //付款订单数
        $data['wx_orders'] = Orders::where('pay_type', 2)->where('status', 3)->where('pf_id', '=', session('admin.id'))->count();
        //卡密订单数
        $data['code_orders'] = Orders::where('pay_type', 3)->where('status', 3)->where('pf_id', '=', session('admin.id'))->count();
        //分销订单数
        $data['retail_count'] = Orders::withJoin(['user'], 'left')->where('user.pid', '<>', '0')->where('user.pf_id', '=', session('admin.id'))
            ->where('orders.pf_id', '=', session('admin.id'))
            ->where('retail_num', '>', 0)->where('pay_type', 2)->where('status', 3)->count();
        //分销佣金
        $data['retail_num'] = Orders::withJoin(['user'], 'left')->where('user.pid', '<>', '0')->where('user.pf_id', '=', session('admin.id'))
            ->where('orders.pf_id', '=', session('admin.id'))
            ->where('retail_num', '>', 0)->where('pay_type', 2)->where('status', 3)->sum('retail_num');
        //平台收益
        $data['system_num'] = Orders::withJoin(['user'], 'left')->where('user.pid', '<>', '0')->where('user.pf_id', '=', session('admin.id'))
            ->where('orders.pf_id', '=', session('admin.id'))
            ->where('retail_num', '>', 0)->where('pay_type', 2)->where('status', 3)->sum(Db::raw('num - retail_num'));
        //平台注册数
        $data['ok_orders'] = Orders::where('status', 3)->where('pf_id', '=', session('admin.id'))->where('id', '>', 0)->count();

        $api = new wxApi('xcx', session('admin.id'));
        $data['xcx_url'] = $api->get_xcx_url('', 'pf_id=' . session('admin.id')) ?: '请配置小程序';

        $data['xcx_img'] = $api->get_xcx_qrcode('pages/index/index', 'scene=1&pf_id=' . session('admin.id'));

        $data['mp_url'] = 'https://' . $_SERVER['SERVER_NAME'] . '/H5/#/?pf_id=' . session('admin.id');
        $data['mp_img'] = $this->qrcode($data['mp_url']);
        $this->assign('data', $data);
        return $this->fetch();
    }

    public function qrcode($url, $level = 3, $size = 4)
    {
        require '../vendor/phpqrcode/phpqrcode.php';



        $error = intval($level);
        $Size = intval($size);
        $object = new QRcode;

        $object->png($url, '1.png', $error, $size, 2);

        $image =  file_get_contents('1.png');

        $imgInfo = 'data:png;base64,' . chunk_split(base64_encode($image)); //转base64


        return $imgInfo;
    }

    /**
     * 修改管理员信息
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editAdmin()
    {
        $id = session('admin.id');
        $row = (new SystemAdmin())
            ->withoutField('password')
            ->find($id);
        empty($row) && $this->error('用户信息不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $this->isDemo && $this->error('演示环境下不允许修改');
            $rule = [];
            $this->validate($post, $rule);
            try {
                $save = $row
                    ->allowField(['head_img', 'phone', 'remark', 'update_time'])
                    ->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('row', $row);
        return $this->fetch();
    }

    /**
     * 修改密码
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function editPassword()
    {
        $id = session('admin.id');
        $row = (new SystemAdmin())
            ->withoutField('password')
            ->find($id);
        if (!$row) {
            $this->error('用户信息不存在');
        }
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $this->isDemo && $this->error('演示环境下不允许修改');
            $rule = [
                'password|登录密码'       => 'require',
                'password_again|确认密码' => 'require',
            ];
            $this->validate($post, $rule);
            if ($post['password'] != $post['password_again']) {
                $this->error('两次密码输入不一致');
            }

            try {
                $save = $row->save([
                    'password' => password($post['password']),
                ]);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            if ($save) {
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
        $this->assign('row', $row);
        return $this->fetch();
    }

    public function check_name()
    {
        $pf_id = session('admin.independent') ? session('admin.id') : 1;
        if (request()->isAjax()) {
            $name = input('post.name');
            $service = new ThirdPartyService();
            $res =   $service->check_xcx_name($name, $pf_id);
            if ($res['code'] == 1) {
                $this->success('该名称未被使用');
            } else {
                $this->error('错误:' . $res['message']);
            }
        }
        return $this->fetch();
    }

    public function xcx_register()
    {

        $pf_id = session('admin.independent') ? session('admin.id') : 1;
        if (request()->isAjax()) {
            $post = input('post.');
            if ($post['type'] == 1) {
                if (!input('post.name') || !input('post.wx_code')) {
                    return error('参数错误');
                }
            } else if ($post['type'] == 2) {
                if (
                    !input('post.name') || !input('post.wx_code') || !input('post.person_name')
                    || !input('post.code_type') || !input('post.code')
                ) {
                    return error('参数错误');
                }
            } else {
                if (
                    !input('post.name') || !input('post.wx_code') || !input('post.person_name')
                    || !input('post.code_type') || !input('post.code') || !input('post.openid') || !input('post.xcxname') || !input('legal_persona_idcard')
                ) {
                    return error('参数错误');
                }
            }
            if (!isset($post['register_status'])) {
                $post['register_status'] = sysconfig('base_config', 'register_status' . $pf_id);
            }
            Db::startTrans();
            try {
                $order =   Orders::createOrder(1, 0,  2, $post, 0, $pf_id, session('admin.id'));
                //todo:小程序注册
                // $service = new ThirdPartyService();
                // $res =     $service->register($order->info, $order->order_id);
                // $res =     $service->register($order->info, $order->order_id);

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
                $service = new ThirdPartyService();
                if ($order->info['type'] == 1) {
                    $res = $service->register_persion($postData, $pf_id);
                } else if ($order->info['type'] == 2) {
                    $code = ['18' => 1, '9' => 2, '15' => 3];
                    $postData['code_type'] = $code[$postData['code_type']];
                    $res = $service->register_company($postData, $pf_id);
                } else {
                    //管理员注册
                    $code = ['18' => 1, '9' => 2, '15' => 3];
                    $postData['code_type'] = $code[$postData['code_type']];
                    $postData['openid'] = $post['openid'];
                    $postData['xcxname'] = $post['xcxname'];
                    Log::write("试用注册 参数" . json_encode($postData));
                    $res = $service->register_fastregisterbetaweapp($postData, $pf_id);
                    Log::write("管理员注册小程序结果" . json_encode($res));
                }
                Log::write("后台xcx_register结果" . json_encode($res));
                if ($res['code'] == 1) {
                    if ($order->info->type == 1) {
                        $order->error_msg = '请扫码验证！';
                        $order->success_url =   $res['data']['authorize_url'];
                        $order->taskid =   $res['data']['taskid'];
                    } else if ($order->info->type == 2) {
                        $order->error_msg = $res['message'];
                    } else {
                        $order->error_msg = $res['message'];
                        $order->success_url =   $res['data']['authorize_url'];
                        $order->unique_id =   $res['data']['unique_id'];
                    }
                    $order->faststatus = !empty($res['errcode']) ? $res['errcode'] : '';
                    NoticeService::sendMsg($order, 1, '您注册的小程序已通过，等待验证！', $pf_id);
                    $order->save();
                } else {
                    $order->status = 3;
                    $order->error_msg = $res['message'];
                    NoticeService::sendMsg($order, 0, '您注册的小程序未通过，错误信息：' . $res['message'], $pf_id);
                    $order->faststatus = !empty($res['errcode']) ? $res['errcode'] : '';
                    $order->save();
                }
                // OrderInfo::create(
                //     array_merge($post, ['taskid' => in_array('taskid', array_keys($return_res_data)) ? $return_res_data['taskid'] : ''])
                // );
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                Log::write("错误异常" . json_encode($e->getMessage()));
                $this->error('提交失败');
            }
            $this->success('提交成功');
        }
        return $this->fetch();
    }

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
     * 授权
     */
    public function shouquan()
    {
        $domain = $_SERVER['SERVER_NAME'];
        $url = "http://sq.xiaojiangy.cn/getauth_info.php?id=40&h={$domain}";
        $res = file_get_contents($url);
        $r   = json_decode($res, true);
        $data = [];
        if (isset($r['data'])) {
            $data = $r['data'];
            $data['ip'] = $_SERVER['SERVER_ADDR'];
        }
        return view('', ['data' => $data]);
    }

    public function beifen()
    {
        $list = BackUp::where([['id', '>', 0]])->order('id desc')->select()->toArray();
        if ($list) {
            foreach ($list as $k => &$v) {
                $basename = pathinfo($v['path'])['basename'];
                $url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] . '/backup/' . $basename;
                $v['down'] = $url;
            }
        }
        $assign = [
            'list' => $list
        ];
        return view('', $assign);
    }


    //本分数据库
    public function backup()
    {
        header("Content-type:text/html;charset=utf-8");
        $dbconfig = include root_path() . 'config/database.php';
        $config = $dbconfig['connections']['mysql'];
        //配置信息
        $cfg_dbhost = $config['hostname'];
        $cfg_dbname = $config['database'];
        $cfg_dbuser = $config['username'];
        $cfg_dbpwd  = $config['password'];
        $cfg_db_language = 'utf8';
        $path = public_path() . 'backup';
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        Users::where('id', '0')->update(['id' => 1]);
        $to_file_name = date('Y_m_d') . '_' . md5(date('His') . uniqid()) . ".sql";
        $link = mysqli_connect($cfg_dbhost, $cfg_dbuser, $cfg_dbpwd);
        $file_name = $path . '/' . $to_file_name;
        mysqli_select_db($link, $cfg_dbname);
        //选择编码
        mysqli_query($link, "set names " . $cfg_db_language);
        $result = mysqli_query($link, 'show tables');
        $table_list = array();
        while ($db = mysqli_fetch_row($result)) {
            $table_list[] = $db[0];
        }
        $info = "\r\n";
        file_put_contents($file_name, $info, FILE_APPEND);

        foreach ($table_list as $val) {
            $sql = "show create table " . $val;
            $res = mysqli_query($link, $sql);
            $row = mysqli_fetch_array($res);


            $info = "\r\n";
            $info .= "DROP TABLE IF EXISTS `" . $val . "`;\r\n";
            $sqlStr = $info . $row[1] . ";\r\n\r\n";

            //追加到文件
            file_put_contents($file_name, $sqlStr, FILE_APPEND);
            //释放资源
            mysqli_free_result($res);
        }

        //将数据插入sql文件中
        //将每个表的数据导出到文件
        foreach ($table_list as $val) {
            $sql = "select * from " . $val;

            $res = mysqli_query($link, $sql);
            //如果表中没有数据，则继续下一张表
            if (mysqli_num_rows($res) < 1) continue;
            //
            $info = "\r\n";

            file_put_contents($file_name, $info, FILE_APPEND);
            //读取数据
            while ($row = mysqli_fetch_row($res)) {
                $sqlStr = "INSERT INTO `" . $val . "` VALUES (";
                foreach ($row as $zd) {
                    $sqlStr .= "'" . $zd . "', ";
                }
                //去掉最后一个逗号和空格
                $sqlStr = substr($sqlStr, 0, strlen($sqlStr) - 2);
                $sqlStr .= ");\r\n";
                file_put_contents($file_name, $sqlStr, FILE_APPEND);
            }
            //释放资源
            mysqli_free_result($res);
            file_put_contents($file_name, "\r\n", FILE_APPEND);
        }
        file_put_contents($file_name, "UPDATE ea_users set id = 0 where id =1;\r\n", FILE_APPEND);
        $i = [
            'size' => ceil(filesize($file_name) / 1024),
            'path' => $file_name,
            'addtime' => date('Y-m-d H:i:s')
        ];
        BackUp::insert($i);

        $return = [
            'code' => '1',
            'msg' => '备份成功',
            'path' => $file_name
        ];
        return json($return);
    }

    //删除数据库备份
    public function delsql()
    {
        $id = input('id');
        $backup = BackUp::where([['id', '=', $id]])->find();
        $path = $backup->path;
        $r = $backup->delete();
        if ($r) {
            @unlink($path);
            return json(['code' => '1', 'msg' => '删除成功']);
        }
        return json(['code' => '0', 'msg' => '删除失败']);
    }


    //恢复
    public function xiufu()
    {
        $id = input('id');
        $info = BackUp::where([['id', '=', $id]])->find();
        if (!$info) return json(['code' => '0', 'msg' => '无此信息']);
        if (!is_file($info['path'])) return json(['code' => '0', 'msg' => '无备份文件']);

        $content = file_get_contents($info['path']);
        $sql_data  = explode(";\r\n", $content);
        //执行sql语句
        foreach ($sql_data as $_value) {
            if (trim($_value)) {
                Db::query(trim($_value) . ';');
            }
        }
        Db::query('update ea_system_admin set delete_time = null;');
        Db::query('update ea_system_menu set delete_time = null;');
        Db::query('update ea_system_auth set delete_time = null;');
        return json(['code' => '1', 'msg' => '恢复成功']);
    }

    /**
     * 云更新
     */
    public function cloudupdate()
    {
        //获取当前版本号
        $use_version = include root_path() . 'config/version.php';
        $version = $use_version['version'];
        //更新历史记录
        $log = UpdateLog::where([['id', '>', 0]])->order('id desc')->select();
        $assign = [
            'version' => $version,
            'log' => $log
        ];
        return view('', $assign);
    }
}
