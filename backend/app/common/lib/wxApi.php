<?php

namespace app\common\lib;

use app\admin\model\Orders;
use app\api\service\ThirdPartyService;
use CURLFile;
use think\Exception;
use think\facade\Log;

class wxApi
{
    private  $type;
    private $app_id;
    private $app_secret;
    private $mchid;
    private $mchid_secret;
    private $mchid_private_key;
    private $mchid_serial_no;
    private $mchid_cert;
    private $pf_id;
    public function __construct($type = 'xcx', $pf_id)
    {
        $this->type = $type;
        $this->pf_id = $pf_id;

        switch ($type) {
            case 'xcx':
                $this->app_id = sysconfig('wx_config', 'wx_app_id' . $this->pf_id);
                $this->app_secret = sysconfig('wx_config', 'wx_app_secret' . $this->pf_id);
                $this->mchid = sysconfig('wx_config', 'wx_mchid' . $this->pf_id);
                $this->mchid_secret = sysconfig('wx_config', 'wx_mchid_secret' . $this->pf_id);
                $this->mchid_private_key = sysconfig('wx_config', 'wx_key_pem' . $this->pf_id);
                $this->mchid_cert = sysconfig('wx_config', 'wx_cert_pem' . $this->pf_id);
                $this->mchid_serial_no = sysconfig('wx_config', 'wx_mchid_serial_no' . $this->pf_id);
                break;
            case 'mp':
                $this->app_id = sysconfig('wx_config', 'wx_mp_app_id' . $this->pf_id);
                $this->app_secret = sysconfig('wx_config', 'wx_mp_app_secret' . $this->pf_id);
                $this->mchid = sysconfig('wx_config', 'wx_mp_mchid' . $this->pf_id);
                $this->mchid_secret = sysconfig('wx_config', 'wx_mp_mchid_secret' . $this->pf_id);
                $this->mchid_private_key = sysconfig('wx_config', 'wx_mp_key_pem' . $this->pf_id);
                $this->mchid_cert = sysconfig('wx_config', 'wx_mp_cert_pem' . $this->pf_id);
                $this->mchid_serial_no = sysconfig('wx_config', 'wx_mp_mchid_serial_no' . $this->pf_id);
                break;
        }
        if (strpos($this->mchid_private_key, "http") !== 0) {
            $this->mchid_private_key = request()->domain() . '/' . $this->mchid_private_key;
        }
        if (strpos($this->mchid_cert, "http") !== 0) {
            $this->mchid_cert = request()->domain() . '/' . $this->mchid_cert;
        }
    }

    /**
     * 获取用户openid
     */
    public function get_open_id($code)
    {
        $url = '';
        if ($this->type == 'xcx') {
            $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->app_id}"
                . "&secret={$this->app_secret}&js_code={$code}&grant_type=authorization_code";
        }

        if ($this->type == 'mp') {
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?"
                . "appid={$this->app_id}&secret={$this->app_secret}&code={$code}&grant_type=authorization_code";
        }
        Log::info('获取用户openid 地址：{data}', ['data' => $url]);
        Log::save();
        $res = request_url($url, 'get');
        Log::info('获取用户openid 返回数据：{data}', ['data' => json_encode($res, JSON_UNESCAPED_UNICODE)]);
        Log::save();
        if (!in_array('errcode', array_keys($res))) {
            if ($this->type == 'xcx') {
                return ['open_id' => $res['openid']];
            }
            return ['open_id' => $res['openid'], 'access_token' => $res['access_token']];
        }
        return '';
    }

    /**
     * 获取 access_token
     */
    public function get_access_token()
    {

        if (cache('wx_' . $this->type . '_access_token' . $this->pf_id)) return cache('wx_' . $this->type . '_access_token' . $this->pf_id);
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->app_id}&secret={$this->app_secret}";
        $res = request_url($url, 'get');
        // Log::info(json_encode($res));
        // Log::save();
        if (!in_array('errcode', array_keys($res))) {
            cache('wx_' . $this->type . '_access_token' . $this->pf_id, $res['access_token'], 3600 - 300);
            return $res['access_token'];
        }
        return '';
    }

    /**
     * 公众号新增临时素材
     * https://developers.weixin.qq.com/doc/offiaccount/Asset_Management/New_temporary_materials.html
     * type 图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     */
    public function media_upload($real_path)
    {
        if (strpos($real_path, "http") !== 0) {
            $real_path = request()->domain() . '/' . $real_path;
        }
        // authorizer_access_token
        $thirdPartyService = new ThirdPartyService();
        $get_access_token = $thirdPartyService->get_component_access_token($this->pf_id);
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$get_access_token}&type=image";
        $ch1 = curl_init();
        $realFilePath = public_path() . 'upload/' . mt_rand(100, 9999) . time() . '.jpg';
        file_put_contents($realFilePath, file_get_contents($real_path));
        $file_data = ['media' => new \CURLFile($realFilePath, 'image/png')];

        curl_setopt($ch1, CURLOPT_URL, $url);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($file_data)) {
            curl_setopt($ch1, CURLOPT_POST, 1);
            curl_setopt($ch1, CURLOPT_POSTFIELDS, $file_data);
        }
        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch1);
        Log::write($result, '__media_upload_DEBUG__1');
        if (curl_errno($ch1)) {
            throw new Exception('curl falied. Error Info: ' . curl_error($ch1));
        }
        curl_close($ch1);
        unlink($realFilePath);
        return json_decode($result, true);
    }


    public function getJsapiTicket()
    {
        if (cache('wx_mp_jsapi_ticket' . $this->pf_id)) return cache('wx_mp_jsapi_ticket' . $this->pf_id);
        if (!$this->get_access_token()) {
            return '';
        }

        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$this->get_access_token()}&type=jsapi";
        $res = request_url($url);
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                cache('wx_mp_jsapi_ticket' . $this->pf_id, $res['ticket'], 7200 - 300);
                return $res['ticket'];
            }
        }
        return '';
    }


    public function getJsapiSign($url)
    {
        $timestamp = time();
        $noncestr = random_str(16, 2);
        $jsapi_ticket = $this->getJsapiTicket();

        $sign = "jsapi_ticket={$jsapi_ticket}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";

        $sign = sha1($sign);
        return [
            'timestamp' => $timestamp,
            'nonceStr' => $noncestr,
            'signature' => $sign,
            'appId' => $this->app_id
        ];
    }

    /**
     * 获取小程序url
     */
    public function get_xcx_url($path, $query, $env = 'release')
    {
        if (!$this->get_access_token()) {
            return '';
        }
        $url = 'https://api.weixin.qq.com/wxa/generate_urllink?access_token=' . $this->get_access_token();
        $data = [
            'path' => $path,
            'query' => $query,
            'env_version' => $env
        ];
        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_SLASHES));
        Log::info(json_encode($res));
        Log::save();
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return $res['url_link'];
            }
        }
        return '请先发布小程序！';
    }

    /**
     * 获取用户信息
     */
    public function get_user_info($open_id, $access_token)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
        $res = request_url($url, 'get', []);
        if (!in_array('errcode', array_keys($res))) {
            return $res;
        }
        return '';
    }

    /**
     * 获取小程序码
     */
    public function get_xcx_qrcode($path = 'pages/index/index', $scene = 'scene=1', $width = 430)
    {
        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $this->get_access_token();
        $data = [
            'scene' => $scene,
            'page' => $path,
            'width' => $width
        ];
        Log::write(json_encode($data, JSON_UNESCAPED_SLASHES), '获取小程序码参数');
        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_SLASHES), [], 'image');
        if (is_array($res)) {
            return '请先发布小程序！';
        }
        return $res;
    }

    /**
     * 获取公众号码
     */
    public function get_mp_qrcode()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create=access_token" . $this->get_access_token();

        $qrcode =  '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}';

        $result =  request_url($url, 'post', $qrcode);
        var_dump($result);
        exit;

        // var_dump($result);exit;

        return $result;
    }


    protected function http_post_data($url, $data_string)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length: ' . strlen($data_string)
            )
        );
        ob_start();
        curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('curl falied. Error Info: ' . curl_error($ch));
        }
        $return_content = ob_get_contents();
        ob_end_clean();
        $return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        return array($return_code, $return_content);
    }
    /**
     * 给用户发送消息
     */
    public function send_notice($open_id, $flag, $text)
    {
        if (!$this->get_access_token()) {
            return '';
        }
        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $this->get_access_token();
        $str = $flag ? '成功' : '失败';
        $data = [
            'touser' => $open_id,
            'template_id' => sysconfig('notice_config', 'notice_template_id'),
            'data' => [
                'thing4' => ['value' => $str],
                'thing5' => ['value' => $text],
            ]
        ];
        Log::info(json_encode($data));
        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE));
        // Log::info(json_encode($res));
        // Log::save();
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 微信支付
     */
    public function wxPay($open_id, $order_id, $amount, $notify_url = null)
    {
        if (!$this->get_access_token()) {
            return '';
        }
        $url = 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi';
        $post_data = [
            'appid' => $this->app_id,
            'mchid' => $this->mchid,
            'description' => '小程序注册费用',
            'out_trade_no' => $order_id,
            // 'notify_url' => $notify_url ?: input('server.REQUEST_SCHEME') . '://' . request()->host() . '/api/wx_notify',
            // 'notify_url' => input('server.REQUEST_SCHEME') . '://' . request()->host() . '/api/wx_notify/pf_id/' . $notify_url,
            'notify_url' => $notify_url,
            'amount' => [
                'total' => intval(bcmul($amount, 100)),
            ], 'payer' => [
                'openid' => $open_id
            ]
        ];
        Log::write('wxPay微信支付 支付参数' . json_encode($post_data, JSON_UNESCAPED_UNICODE));
        $header = $this->sign('post', $url, json_encode($post_data));
        $res = request_url($url, 'post', json_encode($post_data), $header);

        // Log::info(json_encode($res, JSON_UNESCAPED_UNICODE));
        // Log::save();
        if (in_array('prepay_id', array_keys($res))) {
            return $this->pay_sign($res['prepay_id']);
        }
        if (!empty($res['code']) && !empty($res['message'])) {
            return ['code' => $res['code'], 'message' => $res['message']];
        }
        return ['code' => 0, 'message' => '支付失败'];
    }
    /**
     * 解密
     */
    public function decrpt($ciphertext, $associated_data, $nonce)
    {

        Log::write($ciphertext, 'wxapi-debug_______1');
        $ciphertext = base64_decode($ciphertext);
        Log::write($ciphertext, 'wxapi-debug_______2');

        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -16);
            $authTag = substr($ciphertext, -16);
            Log::write('微信支付解密desrpt 参数 ' . json_encode([
                $ctext,
                'aes-256-gcm',
                $this->mchid_secret,
                OPENSSL_RAW_DATA,
                $nonce,
                $authTag,
                $associated_data
            ], JSON_UNESCAPED_UNICODE));

            Log::write($ctext, 'wxapi-debug_______3');
            Log::write($authTag, 'wxapi-debug_______4');
            Log::write($this->mchid_secret, 'wxapi-debug_______5');
            Log::write($nonce, 'wxapi-debug_______6');
            Log::write($authTag, 'wxapi-debug_______7');
            Log::write($associated_data, 'wxapi-debug_______8');


            $res = openssl_decrypt(
                $ctext,
                'aes-256-gcm',
                $this->mchid_secret,
                OPENSSL_RAW_DATA,
                $nonce,
                $authTag,
                $associated_data
            );
            Log::write('微信支付解密desrpt res ' . $res);
            return $res;
        }
        return '';
    }


    //签名
    public function sign($method, $url, $body, $nonce = '', $timestamp = '')
    {
        if (!$nonce) $nonce = random_str(16, 2);
        if (!$timestamp) $timestamp = time();
        $url_parts = parse_url($url);
        $canonical_url = ($url_parts['path'] . (!empty($url_parts['query']) ? "?${url_parts['query']}" : ""));
        $message =  strtoupper($method) . "\n" .
            $canonical_url . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            $body . "\n";

        openssl_sign($message, $raw_sign, file_get_contents($this->mchid_private_key), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);

        $schema = 'WECHATPAY2-SHA256-RSA2048';
        $token = sprintf(
            'mchid="%s",nonce_str="%s",timestamp="%d",serial_no="%s",signature="%s"',
            $this->mchid,
            $nonce,
            $timestamp,
            $this->mchid_serial_no,
            $sign
        );
        return [
            'Authorization: ' . $schema . ' ' . $token,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36',
            'Content-Type: application/json'
        ];
    }
    /**
     * 调起支付信息
     */
    public function pay_sign($prepay_id, $timestamp = '', $nonce = '')
    {
        if (!$nonce) $nonce = random_str(16, 2);
        if (!$timestamp) $timestamp = time();

        $message =  $this->app_id . "\n" .
            $timestamp . "\n" .
            $nonce . "\n" .
            'prepay_id=' . $prepay_id . "\n";
        openssl_sign($message, $raw_sign, file_get_contents($this->mchid_private_key), 'sha256WithRSAEncryption');
        $sign = base64_encode($raw_sign);
        return [
            'timeStamp' => $timestamp,
            'nonceStr' => $nonce,
            'package' => 'prepay_id=' . $prepay_id,
            'signType' => 'RSA',
            'paySign' => $sign,
        ];
    }
    /**
     * 付款
     */
    public function transfers($open_id, $order_id, $amount, $nonce = '')
    {
        if (!$nonce) $nonce = random_str(16, 2);

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';

        $data = [
            'mch_appid' => $this->app_id,
            'mchid' => $this->mchid,
            'nonce_str' => $nonce,
            'partner_trade_no' => $order_id,
            'openid' => $open_id,
            'check_name' => 'NO_CHECK',
            'amount' => 100 * $amount,
            'desc' => '佣金'
        ];

        $secrect_key = $this->mchid_secret;
        $data = array_filter($data);
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $str .= 'key=' . $secrect_key;
        $data['sign'] = md5($str);
        $xml = $this->arraytoxml($data);
        $res = $this->curl_post_ssl($url, $xml);
        $res = $this->xmltoarray($res);
        // Log::info(json_encode($res, JSON_UNESCAPED_UNICODE));
        // Log::save();
        if (!$res) return false;
        if (in_array('return_code', array_keys($res))) {
            if ($res['result_code'] == 'SUCCESS') {
                return true;
            }
        }
        return false;
    }
    /**
     * 给用户发红包
     * 公众号才可以用
     */
    public function sendredpack($open_id, $order_id, $amount, $nonce = '')
    {
        if (!$nonce) $nonce = random_str(16, 2);

        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/sendredpack';

        $data = [
            'nonce_str' => $nonce,
            'mch_billno' => $order_id,
            'mch_id' => $this->mchid,
            'wxappid' => $this->app_id,
            'send_name' => '测试',
            're_openid' => $open_id,
            'total_amount' => 100 * $amount,
            'total_num' => 1,
            'wishing' => '测试1',
            'client_ip' => input('server.ip'),
            'act_name' => '测试1',
            'remark' => '测试2'
        ];

        $secrect_key = $this->mchid_secret;
        $data = array_filter($data);
        ksort($data);
        $str = '';
        foreach ($data as $k => $v) {
            $str .= $k . '=' . $v . '&';
        }
        $str .= 'key=' . $secrect_key;
        $data['sign'] = md5($str);
        $xml = $this->arraytoxml($data);
        $res = $this->curl_post_ssl($url, $xml);
        $res = $this->xmltoarray($res);
        Log::info(json_encode($res, JSON_UNESCAPED_UNICODE));
        Log::save();
    }



    /**
     * [xmltoarray xml格式转换为数组]
     * @param [type] $xml [xml]
     * @return [type]  [xml 转化为array]
     */
    function xmltoarray($xml)
    {
        //禁止引用外部xml实体 
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }
    /**
     * [arraytoxml 将数组转换成xml格式（简单方法）:]
     * @param [type] $data [数组]
     * @return [type]  [array 转 xml]
     */
    function arraytoxml($data)
    {
        $str = '<xml>';
        foreach ($data as $k => $v) {
            $str .= '<' . $k . '>' . $v . '</' . $k . '>';
        }
        $str .= '</xml>';
        return $str;
    }
    /**
     * [curl_post_ssl 发送curl_post数据]
     * @param [type] $url  [发送地址]
     * @param [type] $xmldata [发送文件格式]
     * @param [type] $second [设置执行最长秒数]
     * @param [type] $aHeader [设置头部]
     * @return [type]   [description]
     */
    function curl_post_ssl($url, $xmldata, $second = 30, $aHeader = array())
    {
        $ch = curl_init(); //初始化curl

        curl_setopt($ch, CURLOPT_TIMEOUT, $second); //设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url); //抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM'); //证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, public_path() . parse_url($this->mchid_cert)['path']); //证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM'); //CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, public_path() . parse_url($this->mchid_private_key)['path']); //证书位置
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader); //设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xmldata); //全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch); //执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }

    /**
     * 令牌（component_access_token）是第三方平台接口的调用凭据
     * component_access_token
     * https://api.weixin.qq.com/cgi-bin/component/api_component_token
     * 
     * component_verify_ticket 授权事件接收URL 中持续获取
     * https://developers.weixin.qq.com/doc/oplatform/Third-party_Platforms/2.0/api/ThirdParty/token/component_verify_ticket.html
     */
    function get_component_access_token()
    {
        if (cache('xcx_' . $this->type . '_com_access_token' . $this->pf_id)) return cache('xcx_' . $this->type . '_com_access_token' . $this->pf_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $data = [
            'component_appid' => $this->app_id,
            'component_appsecret' => $this->app_secret,
            'component_verify_ticket' => "component_verify_ticket"
        ];
        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_SLASHES));


        // Log::info(json_encode($res));
        // Log::save();
        if (!in_array('errcode', array_keys($res))) {
            cache('xcx_' . $this->type . '_com_access_token' . $this->pf_id, $res['component_access_token'], 7200 - 300);
            return $res['component_access_token'];
        }
        return '';
    }
}
