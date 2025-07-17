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
use app\admin\model\SystemUploadfile;
use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use app\common\lib\BaiDuApi;
use app\common\service\MenuService;
use EasyAdmin\upload\Uploadfile;
use think\db\Query;
use think\facade\Cache;
use think\facade\Db;

class Ajax extends AdminController
{

    /**
     * 初始化后台接口地址
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function initAdmin()
    {
        $cacheData = Cache::get('initAdmin_' . session('admin.id'));
        if (!empty($cacheData)) {
            return json($cacheData);
        }
        $menuService = new MenuService(session('admin.id'));
        $menuInfo = $menuService->getMenuTree();
        if (session('admin.id') != 1) {
            array_unshift($menuInfo[0]['child'], [
                'href' => '/admin/index/welcome.html',
                'icon' => 'fa fa-home',
                'id' => '227',
                'pid' => '99999999',
                'target' => '_self',
                'title' => '后台首页'
            ]);
        }
        // echo session('admin.independent');exit;
        if (!session('admin.independent') && session('admin.id') != 1) {
            foreach ($menuInfo[0]['child'] as $k => $v) {
                if ($v['id'] == 282) {
                    unset($menuInfo[0]['child'][$k]);
                }
            }
        }
        $data = [
            'logoInfo' => [
                'title' => sysconfig('site', 'logo_title'),
                'image' => sysconfig('site', 'logo_image'),
                'href'  => __url('index/index'),
            ],
            'homeInfo' => $menuService->getHomeInfo(),
            'menuInfo' => $menuInfo,
        ];
        Cache::tag('initAdmin')->set('initAdmin_' . session('admin.id'), $data);
        return json($data);
    }

    /**
     * 清理缓存接口
     */
    public function clearCache()
    {
        Cache::clear();
        $this->success('清理缓存成功');
    }

    /**
     * 上传文件
     */
    public function upload()
    {
        $this->checkPostRequest();
        $data = [
            'upload_type' => $this->request->post('upload_type'),
            'file'        => $this->request->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        // $uploadConfig['upload_allow_size'] = 1024 * 1024 * 10;
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        $this->validate($data, $rule);
        try {
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        if ($upload['save'] == true) {
            $this->success($upload['msg'], ['url' => $upload['url']]);
        } else {
            $this->error($upload['msg']);
        }
    }
    /**
     * 上传文件
     */
    public function uploadTxt()
    {
        $this->checkPostRequest();
        $data = [
            'upload_type' => $this->request->post('upload_type'),
            'file'        => $this->request->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = 'txt';
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $uploadConfig['upload_allow_size'] = 1024 * 1024 * 10;
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        $this->validate($data, $rule);
        $filename = $_FILES['file']['name'];
        if (empty($filename)) {
            $this->error('上传文件错误');
        }
        move_uploaded_file($_FILES["file"]["tmp_name"], public_path() . $filename);
        // $this->success('ok', request()->domain() . '/' . $filename);
        $this->success('ok', $filename);
    }

    /**
     * 上传文件
     */
    public function uploadLocal()
    {
        $this->checkPostRequest();
        $data = [
            'upload_type' => $this->request->post('upload_type'),
            'file'        => $this->request->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = 'doc,gif,ico,icon,jpg,mp3,mp4,p12,pem,png,rar,jpeg,key';
        $data['upload_type'] = 'local';
        $uploadConfig['upload_allow_size'] = 1024 * 1024 * 10;
        $uploadConfig['upload_allow_type'] = 'local';
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        $this->validate($data, $rule);
        $filename = $_FILES['file']['name'];
        if (empty($filename)) {
            $this->error('上传文件错误');
        }
        $tmpname = explode('.', $filename);
        $tmpname = $tmpname[count($tmpname) - 1];
        $tmpname = '/configfile/' . mt_rand(1000, 9999) . '-' . time() . '.' . $tmpname;
        move_uploaded_file($_FILES["file"]["tmp_name"], public_path() . $tmpname);
        // $returnname = request()->domain() . $tmpname;
        // $returnname = public_path() . $tmpname;
        $this->success('ok', $tmpname);
    }
    /**
     * 支付宝进件 上传图片
     */
    public function uploadLocalAli()
    {
        $data = [
            'upload_type' => '',
            'file'        => request()->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        // empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $data['upload_type'] = 'local';
        $uploadConfig['upload_allow_type'] = 'local';
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        validate()->check($data, $rule);
        try {
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
        if ($upload['save'] == true) {
            $this->success($upload['msg'], ['url' => $upload['url']]);
        } else {
            $this->error($upload['msg']);
        }
    }

    /**
     * 支付宝 进件 识别营业执照且上传本地
     */
    public function  uploadLocalAliBaiduapi()
    {
        $data = [
            'upload_type' => '',
            'file'        => request()->file('file'),
        ];
        $uploadConfig = sysconfig('upload');
        $uploadConfig['upload_allow_ext'] = $uploadConfig['upload_allow_ext'] . ",key";
        // empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $uploadConfig['upload_allow_type'] = 'local';
        $data['upload_type'] = 'local';
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        validate()->check($data, $rule);
        try {
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            return error($e->getMessage());
        }
        if ($upload['save'] != true) {
            return error($upload['msg']);
        }
        $api = new BaiDuApi(session('admin.id'));
        $res = '';
        $res =  $api->get_business_pic_info(base64_encode(file_get_contents(public_path() . DIRECTORY_SEPARATOR . $upload['url'])));
        $res['url'] = $upload['url'];
        if ($res) {
            $this->success('识别成功', $res);
        }
        $this->error('识别失败' + $upload['url']);
    }


    /**
     * 上传图片至编辑器
     * @return \think\response\Json
     */
    public function uploadEditor()
    {
        $this->checkPostRequest();
        $data = [
            'upload_type' => $this->request->post('upload_type'),
            'file'        => $this->request->file('upload'),
        ];
        $uploadConfig = sysconfig('upload');
        empty($data['upload_type']) && $data['upload_type'] = $uploadConfig['upload_type'];
        $rule = [
            'upload_type|指定上传类型有误' => "in:{$uploadConfig['upload_allow_type']}",
            'file|文件'              => "require|file|fileExt:{$uploadConfig['upload_allow_ext']}|fileSize:{$uploadConfig['upload_allow_size']}",
        ];
        $this->validate($data, $rule);
        try {
            //上传图片值编辑器标志editor
            $uploadConfig['editor'] = 1;
            $upload = Uploadfile::instance()
                ->setUploadType($data['upload_type'])
                ->setUploadConfig($uploadConfig)
                ->setFile($data['file'])
                ->save();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        if ($upload['save'] == true) {
            return json([
                'error'    => [
                    'message' => '上传成功',
                    'number'  => 201,
                ],
                'fileName' => '',
                'uploaded' => 1,
                'url'      => $upload['url'],
            ]);
        } else {
            $this->error($upload['msg']);
        }
    }

    /**
     * 获取上传文件列表
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUploadFiles()
    {
        $get = $this->request->get();
        $page = isset($get['page']) && !empty($get['page']) ? $get['page'] : 1;
        $limit = isset($get['limit']) && !empty($get['limit']) ? $get['limit'] : 10;
        $title = isset($get['title']) && !empty($get['title']) ? $get['title'] : null;
        $this->model = new SystemUploadfile();
        $count = $this->model
            ->where(function (Query $query) use ($title) {
                !empty($title) && $query->where('original_name', 'like', "%{$title}%");
            })
            ->count();
        $list = $this->model
            ->where(function (Query $query) use ($title) {
                !empty($title) && $query->where('original_name', 'like', "%{$title}%");
            })
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
}
