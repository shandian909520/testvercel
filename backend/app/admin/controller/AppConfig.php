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
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Db;

/**
 * Class AppConfig
 * @ControllerAnnotation(title="参数配置")
 */
class AppConfig extends AdminController
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
        $kefu_type = Db::name('system_config')->where('name', 'kefu_type')->find();
        if (!$kefu_type) {
            $post = [
                'name' => 'kefu_type',
                'group' => 'base_config',
                'value' => 1,
            ];
            Db::name('system_config')->insert($post);
        } else {
            if (intval($kefu_type['value']) <= 0) {
                $post = [
                    'value' => 1,
                ];
                Db::name('system_config')->where('name', 'kefu_type')->update($post);
            }
        }


        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="保存")
     */
    public function save()
    {
        $this->checkPostRequest();
        $post = $this->request->post();
        $group = '';
        if (isset($post['now_group'])) {
            $group = trim($post['now_group']);
            unset($post['now_group']);
        }

        try {
            foreach ($post as $key => $val) {
                $configs = Db::name('system_config')->where('name', $key)->find();
                if ($configs) {
                    $this->model
                        ->where('name', $key)
                        ->update([
                            'value' => $val,
                        ]);
                } else {
                    $pp = [
                        'name' => trim($key),
                        'group' => $group,
                        'value' => $val
                    ];

                    Db::name('system_config')->insert($pp);
                }
            }
            TriggerService::updateMenu();
            TriggerService::updateSysconfig();
        } catch (\Exception $e) {
            $this->error('保存失败');
        }
        $this->success('保存成功');
    }
}
