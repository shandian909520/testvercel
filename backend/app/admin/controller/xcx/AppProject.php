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


namespace app\admin\controller\xcx;

use app\admin\model\AppProject as ModelAppProject;
use app\admin\traits\Curd;
use app\api\service\ThirdPartyService;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * Class AppProject
 * @ControllerAnnotation(title="小程序列表")
 */
class AppProject extends AdminController
{

    use Curd;

    protected $relationSearch = true;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->model = new ModelAppProject();
    }

    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        $pf_id = session('admin.id');
        if (!session('admin.independent')) {
            $pf_id = 1;
        }
        $service = new ThirdPartyService();
        $auth_url =  $service->get_auth_url('pc', url('/sapi/index/auth?pf_id=' . $pf_id, [], false, request()->host()), $pf_id);
        $info = $service->get_xcx_info($pf_id);
        return view('index', compact('auth_url', 'info'));
    }
}
