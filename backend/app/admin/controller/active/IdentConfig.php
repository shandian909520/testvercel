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



namespace app\admin\controller\active;


use app\admin\model\SystemConfig;
use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * Class Config
 * @ControllerAnnotation(title="卡密设置")
 */
class IdentConfig extends AdminController
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
        $this->assign('active_ident_status', sysconfig('active_ident', 'active_ident_status' . session('admin.id')));
        $this->assign('active_ident_status_name',  'active_ident_status' . session('admin.id'));
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="保存")
     */
    public function save()
    {
        // $this->checkPostRequest();
        // $post = $this->request->post();
        // try {
        //     foreach ($post as $key => $val) {
        //         $this->model
        //             ->where('name', $key . session('admin.id'))
        //             ->update([
        //                 'value' => $val,
        //             ]);
        //     }
        //     TriggerService::updateMenu();
        //     TriggerService::updateSysconfig();
        // } catch (\Exception $e) {
        //     $this->error('保存失败');
        // }
        // $this->success('保存成功');
    }
}
