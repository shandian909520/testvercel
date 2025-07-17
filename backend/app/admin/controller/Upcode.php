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

use app\admin\service\TriggerService;
use app\common\controller\AdminController;
use EasyAdmin\annotation\ControllerAnnotation;
use EasyAdmin\annotation\NodeAnotation;
use think\App;
use think\facade\Db;
use think\Facade\Log;

/**
 * @package app\admin\controller\Upcode
 * @ControllerAnnotation(title="上传小程序",auth=false)
 */
class Upcode extends AdminController
{

    use \app\admin\traits\Curd;

    public function __construct(App $app)
    {
        parent::__construct($app);
    }

    //检测版本更新
    private function checkversion()
    {
        $check_url = 'http://sq.xiaojiangy.cn/upcenter.php?id=40&h=' . urlencode($_SERVER['HTTP_HOST']);
        $use_version     = include root_path() . 'config/version.php';
        $r = file_get_contents($check_url);
        $r = json_decode($r, true);
        if ($r['code'] == '0') {
            if (empty($r['version'])) {
                exit('小程序版本信息错误，请联系授权提供商。');
            }
            return json($r);
        }
        $res = $r['data'];
        // $new_version = $res['version']; //版本号
        // $desc        = $res['desc'];  //版本更新描述
        return $res;
    }


    /**
     * @NodeAnotation(title="列表")
     */
    public function index()
    {
        //小程序代码包获取更新信息且更新api接口
        $getversion = $this->checkversion();
        $sourceMap = [
            "version" => $getversion['version'],
            "desc" => "",
        ];
        $this->assign("sourceMap", $sourceMap);
        return $this->fetch();
    }

    //保存小程序配置
    public function save()
    {
        $this->checkPostRequest();
        $post = $this->request->post();
        try {
            $configs = Db::name('system_config')->where('name', 'push_app_upsecret' . session('admin.id'))->find();
            if (!empty($configs)) {
                Db::name('system_config')
                    ->where('name', 'push_app_upsecret' . session('admin.id'))
                    ->update([
                        'value' => $post['push_app_upsecret' . session('admin.id')],
                    ]);
            } else {
                $pp = [
                    'name' => trim('push_app_upsecret' . session('admin.id')),
                    'group' => 'push_config',
                    'value' => $post['push_app_upsecret' . session('admin.id')]
                ];
                Db::name('system_config')->insert($pp);
            }

            TriggerService::updateSysconfig();
        } catch (\Exception $e) {
            $this->error('保存失败--' . json_encode($e->getMessage()));
        }
        $this->success('保存成功');
    }

    /**
     * @NodeAnotation(title="小程序上传代码执行node ")
     */
    function xcxUpcode()
    {
        $desc = input('desc');
        $version = input('version');
        $system_config = Db::name('system_config')->where(['name' => 'push_app_upsecret' . session('admin.id'), 'group' => 'push_config'])->find();
        $push_app_id = Db::name('system_config')->where(['name' => 'wx_app_id' . session('admin.id'), 'group' => 'wx_config'])->find();
        $privateKeyPath = $system_config['value'];
        $privateKeyPath = request()->domain() . $privateKeyPath;
        $appid = $push_app_id['value'];
        // $url = "http://fwfb.xiaojiangy.cn/api/xcxupcodesass";
        $url = "http://fwfb.xiaojiangy.cn/api/xcxupcodesasscontent";
        $data = [
            "appid" => $appid,
            "pf_id" => session('admin.id'),
            "desc" => $desc,
            "version" => $version,
            "privateKey_path" => file_get_contents($privateKeyPath),
            "url" => $_SERVER['HTTP_HOST']
        ];
        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
        if ($res['code'] != 1) {
            $this->error($res['message'], $res);
        } else {
            $this->success("ok");
        }

        exit;

        $output = [];
        $return = "";
        $this->upUploadjs($desc, $version);
        $this->upapiUrl();
        //上传
        // exec("cd " . app()->getRootPath() . "wechatmp && su root && node upload.js >> ./exec.out 2>&1", $output, $return);
        exec("cd " . app()->getRootPath() . "wechatmp && ./upload.sh > exec.out 2>&1", $output, $return);
        // exec("cd " . app()->getRootPath() . "wechatmp && su root && node -v 2>&1", $output, $return);
        $output = mb_convert_encoding($output, 'UTF-8', 'UTF-8');
        $return = mb_convert_encoding($return, 'UTF-8', 'UTF-8');
        Log::write("output" . json_encode($output));
        Log::write("return" . json_encode($return));
        if ((int)$return === 0 && empty($output)) {
            $this->success("ok");
        } else {
            $this->error(json_encode($output), [$output, $return]);
        }
    }
    //覆盖小程序 接口url
    private function upapiUrl()
    {
        $config = file_get_contents(app()->getRootPath() . 'wechatmp' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'main.js');
        $http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
        $api_url = $http_type . $_SERVER['HTTP_HOST'];
        $config = str_replace("替换当前域名", $api_url, $config);
        //设置小程序接口地址
        file_put_contents(app()->getRootPath() . 'wechatmp' . DIRECTORY_SEPARATOR . 'common' . DIRECTORY_SEPARATOR . 'main.js', $config);
    }
    //配置 上传备注 上传密钥 项目地址
    private function upUploadjs($desc, $version)
    {
        $projectPath = app()->getRootPath() . 'wechatmp';
        $system_config = Db::name('system_config')->where(['name' => 'push_app_upsecret' . session('admin.id'), 'group' => 'push_config'])->find();
        $push_app_id = Db::name('system_config')->where(['name' => 'wx_app_id' . session('admin.id'), 'group' => 'wx_config'])->find();
        $privateKeyPath = $system_config['value'];
        $privateKeyPath = app()->getRootPath() . 'public'  . $privateKeyPath;
        $appid = $push_app_id['value'];
        $tmpjs = "
        const ci = require('miniprogram-ci')
        ; (async () => {
            const project = new ci.Project({
                appid: '" . $appid . "',
                type: 'miniProgram',
                projectPath: '" . $projectPath . "',
                privateKeyPath: '" . $privateKeyPath . "',
                ignores: ['node_modules/**/*'],
            })
            const uploadResult = await ci.upload({
                project,
                version: '" . $version . "',
                desc: '" . $desc . "',
                setting: {
                    es6: true,
                },
                onProgressUpdate: console.log,
            })
            console.log(uploadResult)
        })()";
        file_put_contents(app()->getRootPath() . 'wechatmp' . DIRECTORY_SEPARATOR .  'upload.js', $tmpjs);
    }
}
