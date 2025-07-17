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

use app\admin\model\OrderInfo;
use app\admin\model\Orders;
use app\common\controller\AdminController;
use app\common\lib\wxApi;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use PHPQRCode\QRcode;
use think\App;

/**
 * @ControllerAnnotation(title="分销")
 */
class Retail extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);

        $this->model = new \app\admin\model\Orders();

        $this->assign('getPayTypeList', $this->model->getPayTypeList());

        $this->assign('getStatusList', $this->model->getStatusList());
    }


    /**
     * @NodeAnotation(title="分销订单列表")
     */
    public function index()
    {
        if ($this->request->isAjax()) {
            if (input('selectFields')) {
                return $this->selectList();
            }
            list($page, $limit, $where) = $this->buildTableParames();
            $count = $this->model
                ->withJoin(['user'])
                ->where($where)
                ->where('retail_num', '>', '0')
                ->where('pay_type', 2)
                ->where('pid', '<>', 0)
                ->count();
            $list = $this->model->withJoin(['user', 'info'])
                ->where($where)
                ->where('retail_num', '>', '0')
                ->where('pid', '<>', 0)
                ->where('pay_type', 2)
                ->page($page, $limit)
                ->order($this->sort)
                ->select();
            $data = [
                'code'  => 0,
                'msg'   => '',
                'count' => $count,
                'data'  => $list->append(['puser'])->toArray(),
            ];
            return json($data);
        }
        return $this->fetch();
    }


    public function detail()
    {
        $id = input('info_id');
        $info = OrderInfo::where('id', $id)->find();
        if (request()->isAjax()) {
            $post = input('post.');
            unset($post['file']);
            $int =  $info->where('id', $id)->update($post);
            $this->success('更新成功');
        }
        return view('detail', compact('info'));
    }
    /**
     * @NodeAnotation(title="分销设置")
     */
    public function config()
    {
        // $api = new wxApi();
        // $res =  $api->get_xcx_url('', '')  ?: '请配置小程序';
        // QRcode::png($res, public_path() . 'xcx_qrcode1.png', 'H', sysconfig('retail_config', 'retail_size'));

        // $dst = imagecreatefromstring(file_get_contents(sysconfig('retail_config', 'retail_image') ?: public_path() . '/background-1.png'));
        // $src = imagecreatefromstring(file_get_contents(public_path() . '/xcx_qrcode1.png'));

        // list($src_w, $src_h) = getimagesize(public_path() . '/xcx_qrcode1.png');
        // imagecopymerge($dst, $src, sysconfig('retail_config', 'retail_x'), sysconfig('retail_config', 'retail_y'), 0, 0,  $src_w, $src_h, 100);
        // imagedestroy($src);
        // ob_start();
        // imagepng($dst); //根据需要生成相应的图片
        // imagedestroy($dst);
        // $img = ob_get_contents();
        // ob_end_clean();
        // $haibao = "data:image/png;base64," . base64_encode($img);
        $haibao = '';
        return view('config', compact('haibao'));
    }

    public function payment()
    {
        $id = input('id');
        $order = Orders::where('id', $id)->find();
        if (!$order || $order->retail_status == 1) {
            $this->error('订单状态错误');
        }

        $api = new wxApi('xcx', session('admin.id'));
        $res =  $api->transfers($order->fuser()->open_id, $order->order_id, $order->retail_num);
        if ($res) {
            $order->retail_status = 1;
            $order->retail_time = date('Y-m-d H:i:s');
            $order->save();
            $this->success('打款成功');
        } else {
            $order->retail_status = 2;
            $order->retail_time = date('Y-m-d H:i:s');
            $order->save();
            $this->error('打款失败');
        }
    }
}
