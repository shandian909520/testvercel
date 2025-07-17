<?php

declare(strict_types=1);

namespace app\api\controller;

use think\facade\Db;
use app\admin\model\Orders;
use app\admin\model\Region;
use app\admin\model\Users;
use app\api\service\NoticeService;
use app\api\service\ThirdPartyService;
use app\common\lib\wxApi;
use app\lib\wxBizMsgCrypt\Prpcrypt;
use PHPQRCode\QRcode;
use DOMDocument;

class Index
{
    /**
     * 首页
     */
    public function index()
    {
    }

    /**
     * 客服信息
     */
    public function kefu()
    {
        $type = input('post.type');
        if (empty($type)) {
            return error('缺少参数');
        }
        $pf_id = input('pf_id');
        if ($type == 1) {
            $type = sysconfig('base_config', 'kefu_type' . $pf_id);
            if ($type == 2) {
                $company_id = sysconfig('base_config', 'kefu_company_id' . $pf_id);
                $url = sysconfig('base_config', 'kefu_url' . $pf_id);
                return success('查询成功', compact('type', 'company_id', 'url'));
            } else if ($type == 3) {
                $kefuphone = sysconfig('base_config', 'kefu_company_ids' . $pf_id);
                $imagecode = sysconfig('base_config', 'share_images' . $pf_id);
                $url = 'https://' . $_SERVER['SERVER_NAME'] . '/api2/index/kefu_page?pf_id=' . $pf_id;
                return success('查询成功', compact('type', 'kefuphone', 'imagecode', 'url'));
            } else {
                return success('查询成功', compact('type'));
            }
        } else {
            $type = sysconfig('wx_config', 'kefu_type_mp' . $pf_id);
            if ($type == 2) {
                $company_id = sysconfig('base_config', 'kefu_company_id' . $pf_id);
                $url = sysconfig('base_config', 'kefu_url' . $pf_id);
                return success('查询成功', compact('type', 'company_id', 'url'));
            } else if ($type == 3) {
                $kefuphone = sysconfig('base_config', 'kefu_company_ids' . $pf_id);
                $imagecode = sysconfig('base_config', 'share_images' . $pf_id);
                $url = 'https://' . $_SERVER['SERVER_NAME'] . '/api2/index/kefu_page?pf_id=' . $pf_id;
                return success('查询成功', compact('type', 'kefuphone', 'imagecode', 'url'));
            }
        }
    }
    //获取客服页面
    public function kefu_page()
    {
        $pf_id = input('pf_id');
        $qrlink = sysconfig('base_config', 'share_images' . $pf_id);
        $tel = sysconfig('base_config', 'kefu_company_ids' . $pf_id);
        $assign = [
            'qrlink' => $qrlink,
            'tel' => $tel,
        ];
        return view('', $assign);
    }

    /**
     * 分享信息
     */
    public function share()
    {
        $pf_id = input('pf_id');
        $title = sysconfig('base_config', 'share_title' . $pf_id);
        $image = sysconfig('base_config', 'share_image' . $pf_id);
        $msg = sysconfig('base_config', 'share_msg' . $pf_id);
        return success('查询成功', compact('title', 'image', 'msg'));
    }

    /**
     * 注册金额
     */
    public function register_amount()
    {
        $amount = 0;
        if (sysconfig('base_config', 'register_status')) {
            $amount = sysconfig('base_config', 'register_num');
        }
        return success('查询成功', compact('amount'));
    }

    /**
     * 广告配置
     */
    public function ad_config()
    {
        $pf_id = input('pf_id', 1);
        $ad_flag = sysconfig('check_name', 'check_name_status' . $pf_id);
        $ad_popup = sysconfig('ad_config', 'ad_popup' . $pf_id);
        $ad_open = sysconfig('ad_config', 'ad_open' . $pf_id);
        $ad_reward = sysconfig('ad_config', 'ad_reward' . $pf_id);
        $ad_banner = sysconfig('ad_config', 'ad_banner' . $pf_id);
        return success('查询成功', compact('ad_flag', 'ad_popup', 'ad_open', 'ad_reward', 'ad_banner'));
    }

    /**
     * 百度配置
     */
    public function baidu_config()
    {
        $baidu_status = sysconfig('baidu', 'baidu_status');
        return success('查询成功', compact('baidu_status'));
    }

    /*获取首页banner*/
    public function banner()
    {

        $pf_id = input('post.pf_id', 1);
        $banner = Db::name('banner')
            ->where([['id', '>', 0]])
            ->where('pf_id', '=', $pf_id)
            ->order('sort desc')
            ->select()
            ->toArray();
        if ($banner) {
            foreach ($banner as $k => &$v) {
                $v['extradata_info'] = '';
                if (intval($v['type']) == 1 && $v['extradata']) {
                    $data = explode('|', $v['extradata']);
                    $data = array_values(array_filter($data));

                    if ($data) {
                        $v['extradata_info'] .= '?';
                        foreach ($data as $kk => $vv) {
                            $str = str_replace(':', '=', $vv);
                            $v['extradata_info'] .= $str . "&";
                        }
                        $v['extradata_info'] = trim(trim($v['extradata_info'], '&'));
                    }
                }
            }
            unset($v);
        }

        return json(['code' => '1', 'msg' => '成功', 'data' => $banner]);
    }
    /**
     * 获取卡密开关
     */
    public function code_status()
    {
        $pf_id = input('pf_id', 1);
        $code_status = sysconfig('active_ident', 'active_ident_status' . $pf_id) ? true : false;
        return success('查询成功', compact('code_status'));
    }


    /**
     * 获取小程序码
     */
    public function get_xcx_qrcode()
    {
        $pf_id = input('pf_id');
        $api = new wxApi('xcx', $pf_id);
        if (!input('post.page') || !input('post.scene') || !input('post.width')) {
            return error('参数错误!');
        }
        $image =  $api->get_xcx_qrcode(input('post.page'), input('post.scene'), input('width'));
        if ($image) {
            return success('获取成功', compact('image'));
        }
        return error('获取失败');
    }



    public function mp_info()
    {
        $url = input('post.url');
        $pf_id = input('post.pf_id', 1);
        if (empty($url)) return error('参数错误');
        $api = new wxApi('mp', $pf_id);
        $res =   $api->getJsapiSign($url);

        return success('成功', $res);
    }


    /**
     * 测试接口
     */
    public function test()
    {
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
        // $order = Orders::createOrder(0,  request()->id, 2, $data);
    }
    function authorizer_list()
    {
        $user = Users::where('id', request()->id)->find();
        $pf_id = $user['pf_id'];
        $service = new ThirdPartyService();
        $res = $service->get_api_get_authorizer_list($pf_id);
        echo json_encode($res);
        exit;
    }
    public function getrid()
    {
        $rid  = input('rid');
        $service = new ThirdPartyService();
        // $res = $service->getRid($rid);
        // echo json_encode($res);
        exit;
    }

    /**
     *获取过审配置信息
     */
    public function get_pian()
    {
        $pf_id = input('pf_id');
        $pian_status = sysconfig('base_config', 'pian_status' . $pf_id);
        return success('成功', 0);
    }
    /**
     *小程序注册开关
     */
    public function get_xcx_pian_status()
    {
        $pf_id = input('pf_id');
        $pian_status = sysconfig('base_config', 'xcx_pian_status' . $pf_id);
        return success('成功', intval($pian_status));
    }
    /**
     *微信支付开关
     */
    public function get_wx_pay_status()
    {
        $pf_id = input('pf_id');
        $pian_status = sysconfig('base_config', 'wx_pay_status' . $pf_id);
        return success('成功', intval($pian_status));
    }

    /**
     * 获取区域
     */
    function getRegion()
    {
        $pid = input('pid', 1);
        $region = Region::where('parent_id', $pid)->select();
        return success('地区', $region);
    }
    /**
     * 前端 首页四个标题
     */
    function index_title()
    {
        $pf_id = input('pf_id');
        $index1_title = sysconfig('base_config', 'index1_title' . $pf_id);
        $index2_title = sysconfig('base_config', 'index2_title' . $pf_id);
        $index3_title = sysconfig('base_config', 'index3_title' . $pf_id);
        $index4_title = sysconfig('base_config', 'index4_title' . $pf_id);
        $data = [
            'index1_title' => $index1_title,
            'index2_title' => $index2_title,
            'index3_title' => $index3_title,
            'index4_title' => $index4_title,
        ];
        return success('标题', $data);
    }
}
