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

use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;

/**
 * @ControllerAnnotation(title="banner管理")
 */
class Banner extends AdminController
{
    protected $sort = [
        'sort' => 'desc',
    ];

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\Banner();
    }
    /**
     * @NodeAnotation(title="banner列表")
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->where($where)
                ->where('pf_id', '=', session('admin.id'))
                ->count();
            $list = $this->model
                ->where($where)
                ->where('pf_id', '=', session('admin.id'))
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list,
            ];
            return json($data);
        }
        return $this->fetch();
    }
    /**
     * @NodeAnotation(title="kefu_page")
     */
    public function kefu_page()
    {

        $qrlink = sysconfig('base_config', 'share_images' . session('admin.id'));
        $tel = sysconfig('base_config', 'kefu_company_ids' . session('admin.id'));
        $assign = [
            'qrlink' => $qrlink,
            'tel' => $tel,
        ];
        return view('', $assign);
    }
    /**
     * @NodeAnotation(title="添加")
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);

            if (intval($post['type']) == 1 && empty(trim($post['appid']))) {
                $this->error('小程序请填写appid');
            }
            if (intval($post['type']) == 1 && empty(trim($post['gh_no']))) {
                $this->error('小程序请填写appid');
            }

            if ((intval($post['type']) == 2 || intval($post['type']) == 3) && empty(trim($post['urls']))) {
                $this->error('请填写H5连接或手机号信息');
            }
            try {
                $post['pf_id'] = session('admin.id');
                $save = $this->model->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败:' . $e->getMessage());
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        return $this->fetch();
    }


    /**
     * @NodeAnotation(title="编辑")
     */
    public function edit($id)
    {
        $row = $this->model->find($id);
        empty($row) && $this->error('数据不存在');
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [];
            $this->validate($post, $rule);


            if (intval($post['type']) == 1 && empty(trim($post['appid']))) {
                $this->error('小程序请填写appid');
            }

            if (intval($post['type']) == 1 && empty(trim($post['gh_no']))) {
                $this->error('小程序请填写原始id');
            }
            if ((intval($post['type']) == 2 || intval($post['type']) == 3) && empty(trim($post['urls']))) {
                $this->error('请填写H5连接或手机号信息');
            }

            try {
                $save = $row->save($post);
            } catch (\Exception $e) {
                $this->error('保存失败');
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        $this->assign('row', $row);
        return $this->fetch();
    }
}
