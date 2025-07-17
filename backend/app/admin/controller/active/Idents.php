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

use app\admin\model\ActiveIdentCode;
use app\admin\model\ActiveIdents;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Db;

/**
 * @ControllerAnnotation(title="卡密")
 */
class Idents extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\ActiveIdents();
    }


    /**
     * @NodeAnotation(title="卡密-列表")
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
                'data'  => $list->append(['use_num'])->toArray(),
            ];
            return json($data);
        }
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="卡密-添加")
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $post = $this->request->post();
            $rule = [
                'name' => ['require'],
                'ident' => ['require', 'regex' => '/^[A-Za-z]+/'],
                'num' => ['require', 'between' => '1,99999999']
            ];

            $this->validate($post, $rule);
            Db::startTrans();
            try {
                $post['pf_id'] = session('admin.id');
                $save = $this->model->save($post);
                $this->model->generate_code($this->model->id, $post['ident'], $post['num']);
                DB::commit();
            } catch (\Exception $e) {
                $this->error('保存失败:' . $e->getMessage());
                Db::rollback();
            }
            $save ? $this->success('保存成功') : $this->error('保存失败');
        }
        return $this->fetch();
    }

    /**
     * @NodeAnotation(title="删除")
     */
    public function delete($id)
    {
        $this->checkPostRequest();
        $row = $this->model->whereIn('id', $id)->select();
        $row->isEmpty() && $this->error('数据不存在');
        Db::startTrans();
        try {
            $save = $row->delete();
            ActiveIdentCode::where('ident_id', $id)->delete();
            Db::commit();
        } catch (\Exception $e) {
            $this->error('删除失败');
            Db::rollback();
        }
        $save ? $this->success('删除成功') : $this->error('删除失败');
    }
}
