<?php

declare(strict_types=1);

namespace app\api\controller\xcx;

// use app\admin\model\AuthCode;
use app\admin\model\OrderInfo;
// use app\api\middleware\ApiCheck;
use app\api\service\ThirdPartyService;
use app\lib\wxBizMsgCrypt\Prpcrypt;
use DOMDocument;
use think\facade\Log;

class Index
{
    // protected $middleware = [
    //     ApiCheck::class    => ['except'   => ['auth']],
    // ];
    //授权测试url
    // public function index()
    // {
    //     $service = new ThirdPartyService();
    //     $url =  $service->get_auth_url('pc', url('/sapi/index/auth', [], false, request()->host()));
    //     return view('index', compact('url'));
    // }

    function index()
    {
        echo 123;
        exit;
        $xml = file_get_contents("php://input");
        // $xml = file_get_contents("php://input");
        $xml = '<ToUserName><![CDATA[gh_a554464acadb]]></ToUserName><Encrypt><![CDATA[NeupnQAkaepoF/KOZ8+JcuTNJmI4pmmC72/xFlV4+Rjc/Cs5aR5W0FbYX8He7HWbAL5qxv5pJSPXzRqN8/Ut1pd+xFrE5mDp91mcdyzPbzQ2EI8a+dzJLg+jGkzeeZuLr4ycfitRarK4Em1L4T+cdaq9Zt6SBHyKRkiKpdPyuSei+SdtEwTEVpGE0im+//30BTv0iJBJQxU5V6VQzB2akv1Wo32Al3q5yRAaIYypEvUlcyw74ufK9qN7f0KK9XgnOhcxg7tsD8NOWXPPeDRgqKx5xCiVaS86os/9DSACbP5unq/QjX37Z2XidXbYhEYJyYoe4pFThf/yOfVXWPbZJmQBd25+xOZf4BTuDrp+Xyj5FXGt0VjTbA1hkh5ZDho681y9N50nwLmXNb/jnYIy7AYYBPvt9LjHCYNrfDNy0DSa7xwM2WI4b2Mni38fPnCtFRuPl/4Wy5aAJ2lZXFKJ1v9Bv10dLMA7yWCJ9zr0NFyFH0EmOh2tQWJ+XCU8X24H]]>';
        //解密
        $app_id = sysconfig('app_config', 'app_id');
        $key = sysconfig('app_config', 'key');

        $wx = new Prpcrypt($key);
        $xml_tree = new DOMDocument();
        $xml_tree->loadXML($xml);
        $array_e = $xml_tree->getElementsByTagName('Encrypt');
        $text = $array_e->item(0)->nodeValue;
        $res =  $wx->decrypt($text, $app_id);
        echo json_encode($res);
        exit;
    }

    //授权回调页面
    public function auth()
    {
        $auth_code = input('auth_code');
        $pf_id = input('pf_id');
        if (!$auth_code) {
            return error('请求错误！');
        }
        Log::write("授权回调页面 auth_code: " . $auth_code);
        $service = new ThirdPartyService();
        $res =  $service->get_xcx_access_token('auth', $auth_code, false, $pf_id);
        return view('auth');
    }

    //核名接口
    public function check_name()
    {

        // if (!input('auth_code')) {
        //     return error('授权码错误');
        // }

        $name = input('name');
        if (!$name) {
            return error('请输入名称');
        }
        $service = new ThirdPartyService();
        $res = $service->check_xcx_name($name);

        return $res;
    }

    //注册小程序
    public function register()
    {

        $post = input('post.');
        // if (!input('post.auth_code')) {
        //     return error('授权码错误');
        // }
        if (input('post.type')) {
            $service = new ThirdPartyService();
            if ($post['type'] == 1) {
                if (!input('post.order_id') || !input('post.host') ||    !input('post.name') || !input('post.wx_code')  || !input('post.component_phone')) {
                    return error('参数错误');
                }
                $res = $service->register_persion($post);
            } else {
                if (
                    !input('post.order_id') || !input('post.host') ||
                    !input('post.name') || !input('post.wx_code') || !input('post.person_name')
                    || !input('post.code_type') || !input('post.code') || !input('post.component_phone')
                ) {
                    return error('参数错误');
                }
                $code = ['18' => 1, '9' => 2, '15' => 3];
                $post['code_type'] = $code[$post['code_type']];
                $res = $service->register_company($post);
            }
            $return_res_data = json_decode($res, true)['data'];
            OrderInfo::create(
                array_merge($post, ['taskid' => in_array('taskid', array_keys($return_res_data)) ? $return_res_data['taskid'] : ''])
            );
            return $res;
        }
        return error('请输入注册信息！');
    }

    /**
     * 查询注册小程序进度
     */
    public function get_xcx_process()
    {
        if (!input('post.taskid')) {
            return error('参数错误');
        }
        $service = new ThirdPartyService();
        $res =     $service->get_xcx_process(input('post.taskid'));
        return $res;
    }
}
