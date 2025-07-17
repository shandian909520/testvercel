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


use app\admin\model\SystemAdmin;
use app\common\controller\AdminController;
use think\captcha\facade\Captcha;
use think\facade\Env;

/**
 * Class Login
 * @package app\admin\controller
 */
class Login extends AdminController
{

    /**
     * 初始化方法
     */
    public function initialize()
    {
        parent::initialize();
        $action = $this->request->action();
        if (!empty(session('admin')) && !in_array($action, ['out'])) {
            $adminModuleName = config('app.admin_alias_name');
            $this->success('已登录，无需再次登录', [], __url("@{$adminModuleName}"));
        }
    }

    /**
     * 用户登录
     * @return string
     * @throws \Exception
     */
    public function index()
    {
        $captcha = Env::get('easyadmin.captcha', 1);
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [
                'username|用户名'      => 'require',
                'password|密码'       => 'require',
                'keep_login|是否保持登录' => 'require',
            ];
            $captcha == 1 && $rule['captcha|验证码'] = 'require|captcha';
            $this->validate($post, $rule);
            $admin = SystemAdmin::where(['username' => $post['username']])->find();
            if (empty($admin)) {
                $this->error('用户不存在');
            }
            if (password($post['password']) != $admin->password) {
                $this->error('密码输入有误');
            }
            if ($admin->status == 0) {
                $this->error('账号已被禁用');
            }
            if ($admin->id != 1 && time() > $admin->end_time && $admin->end_time > 0) {
                $this->error('代理已过期');
            }
            $admin->login_num += 1;
            $admin->save();
            $admin = $admin->toArray();
            unset($admin['password']);
            $admin['expire_time'] = $post['keep_login'] == 1 ? true : time() + 7200;
            session('admin', $admin);
            $this->success('登录成功');
        }
        $this->assign('captcha', $captcha);
        $this->assign('demo', $this->isDemo);
        return $this->fetch();
    }

    /**
     * 用户退出
     * @return mixed
     */
    public function out()
    {
        isset($_SESSION) or session_start();
        if (!empty($_SESSION['authcode'])) {
            unset($_SESSION['authcode']);
        }
        session('admin', null);
        $this->success('退出登录成功');
    }

    /**
     * 验证码
     * @return \think\Response
     */
    public function captcha()
    {
        return Captcha::create();
    }
}
