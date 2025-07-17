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

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller\xcx;

use app\admin\model\SystemConfig;
use app\admin\model\Users;
use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use app\middleware\ThCheck;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use app\api\service\ThirdPartyService;
use think\App;

/**
 * Class AppConfig
 * @ControllerAnnotation(title="第三方平台设置")
 */
class AppConfig extends AdminController
{
    protected $middleware = [ThCheck::class];

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
        if (!session('admin.independent') && session('admin.id') != 1) {
            exit('未开启三方');
        }
        $service = new ThirdPartyService();
        $auth_url =  $service->get_auth_url('pc', url('/sapi/index/auth?pf_id=' . session('admin.id'), [], false, request()->host()), session('admin.id'));
        $info = $service->get_xcx_info(session('admin.id'));
        $this->assign('auth_url', $auth_url);
        $this->assign('info', $info);
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="保存")
     */
    public function save()
    {
        $this->checkPostRequest();
        $post = $this->request->post();
        try {
            foreach ($post as $key => $val) {
                $this->model
                    ->where('name', $key)
                    ->update([
                        'value' => $val,
                    ]);
            }
            TriggerService::updateSysconfig();
        } catch (\Exception $e) {
            $this->error('保存失败');
        }
        $this->success('保存成功');
    }
}
