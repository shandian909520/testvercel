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


use app\admin\model\SystemConfig;
use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use app\common\lib\WxPushApi;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * Class Config
 * @package app\admin\controller\system
 * @ControllerAnnotation(title="系统配置管理")
 */
class PushConfig extends AdminController
{

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new SystemConfig();
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        return $this->fetch();
    }


    public function push()
    {
        return view('push');
    }

    /**
     * 获取授权码
     */
    public function get_auth_code()
    {
        $service = new WxPushApi();
        $res = $service->get_wx_qr_code();
        if ($res['code']) {
            $qrcode = $res['data']['qrcode'];
            $request_id = $res['data']['request_id'];
            return success('success', compact('qrcode', 'request_id'));
        } else {
            return error($res['message']);
        }
    }

    /**
     * 是否登录
     */
    public function is_login()
    {
        if (!input('request_id')) {
            return error('参数错误!');
        }
        $service = new WxPushApi();
        $flag = $service->is_login(input('request_id'));
        return success('success', ['login' => $flag]);
    }
    /**
     * 上传到小程序
     */
    public function  upload()
    {
        if (!input('post.ident') || !input('post.version') || !input('post.desc') || !input('request_id')) {
            return error('参数错误');
        }
        $service = new WxPushApi();
        $res  =    $service->upload(input('request_id'), input('ident'), input('version'), input('desc'));
        if ($res['code']) {
            return success($res['message']);
        } else {
            return error($res['message']);
        }
    }
}
