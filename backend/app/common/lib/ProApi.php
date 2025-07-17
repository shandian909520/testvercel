<?php

namespace app\common\lib;

use app\admin\model\Orders;
use app\admin\model\SystemConfig;
use think\Exception;
use think\facade\Cache;
use think\facade\Log;

class ProApi
{
    private $app_id;
    private $app_secret;
    private $mchid;
    private $mchid_secret;
    private $mchid_private_key;
    private $mchid_serial_no;
    private $mchid_cert;

    private $pub_cert;
    private $pub_serial_no;
    private $pf_id;
    public function __construct($pf_id)
    {
        $this->pf_id = $pf_id;
        $this->app_id = sysconfig('pro_config', 'pro_app_id' . $this->pf_id);
        $this->app_secret = sysconfig('pro_config', 'pro_app_secret' . $this->pf_id);
        $this->mchid = sysconfig('pro_config', 'pro_mchid' . $this->pf_id);
        $this->mchid_secret = sysconfig('pro_config', 'pro_mchid_secret' . $this->pf_id);
        $this->mchid_private_key = sysconfig('pro_config', 'pro_key_pem' . $this->pf_id);
        $this->mchid_cert = sysconfig('pro_config', 'pro_cert_pem' . $this->pf_id);
        $this->mchid_serial_no = sysconfig('pro_config', 'pro_mchid_serial_no' . $this->pf_id);
        if (strpos($this->mchid_private_key, "http") !== 0) {
            $this->mchid_private_key = request()->domain() .  $this->mchid_private_key;
        }
        if (strpos($this->mchid_cert, "http") !== 0) {
            $this->mchid_cert = request()->domain() .  $this->mchid_cert;
        }
        Log::write($this->mchid_private_key, 'proapi__construct mchid_private_key');
        Log::write($this->mchid_cert, 'proapi__construct mchid_cert');
        $this->get_cert();
    }

    /**
     * 通过业务申请编号查询申请状态
     * @param string $type 类型  business_code 业务编号   applyment_id 申请单号
     * @param string $code 值
     */
    public function get_register_status($type, $code)
    {
        $url = '';
        if ($type == 'business_code') {
            $business_code = $code;
            $url = "https://api.mch.weixin.qq.com/v3/applyment4sub/applyment/business_code/{$business_code}";
        } elseif ($type == 'applyment_id') {
            $applyment_id = $code;
            $url = "https://api.mch.weixin.qq.com/v3/applyment4sub/applyment/applyment_id/{$applyment_id}";
        }
        $header = $this->sign('get', $url, '');
        $res = request_url($url, 'get', '', $header) ?: [];
        if (!in_array('code', array_keys($res))) {
            return $res;
        }
        return $res['message'];
    }

    /**
     * 查询结算账号
     */
    public function get_settlement_info($sub_mchid)
    {
        $url = "https://api.mch.weixin.qq.com/v3/apply4sub/sub_merchants/{$sub_mchid}/settlement";
        $header = $this->sign('get', $url, '');
        $res = request_url($url, 'get', '', $header);
        if (!in_array('code', array_keys($res))) {
            return $res;
        }
        return $res['message'];
    }

    /**
     * 修改结算账户
     * @param string $account_type 账户类型 ACCOUNT_TYPE_BUSINESS 对公银行账户 ACCOUNT_TYPE_PRIVATE 经营者个人银行卡
     * @param string $account_bank 开户银行
     * @param string $bank_name    开户行全称
     * @param string $account_number 银行账号
     */
    public function edit_settlement_info($sub_mchid, $account_type, $account_bank, $bank_name, $account_number)
    {
        $url = "https://api.mch.weixin.qq.com/v3/apply4sub/sub_merchants/{$sub_mchid}/modify-settlement";
        $postData = [
            'account_type' => $account_type,
            'account_bank' => $account_bank,
            'bank_name' => $bank_name,
            'account_number' => $this->encrpt($account_number),
        ];
        $header = $this->sign('post', $url, json_encode($postData, JSON_UNESCAPED_UNICODE));
        $res = request_url($url, 'post', json_encode($postData, JSON_UNESCAPED_UNICODE), $header);
        if (!in_array('code', array_keys($res))) {
            return false;
        }
        return $res['message'];
    }

    /**
     * 提交申请单
     * 
     */
    public function applyment($order)
    {
        $url = "https://api.mch.weixin.qq.com/v3/applyment4sub/applyment/";
        $postData = [];
        //业务申请编号
        $postData['business_code'] = $order['order_id'];
        $data = $order->orderInfo;
        //超级管理员信息
        $postData['contact_info'] = [
            'contact_type' => $data['contact_type'] ? 'LEGAL' : 'SUPER', //1代表法人。0代表经办人
            'contact_name' => $this->encrpt($data['contact_name']),
            'mobile_phone' => $this->encrpt($data['mobile_phone']),
            'contact_email' => $this->encrpt($data['contact_email']),
        ];
        if (empty($data['contact_type']) || $data['contact_type'] == 0) {
            $LEGALArr = [
                'contact_id_doc_type' => "IDENTIFICATION_TYPE_IDCARD",
                'contact_id_number' => $this->encrpt($data['contact_id_number']),
                'contact_id_doc_copy' => $data['contact_id_doc_copy'],
                'contact_id_doc_copy_back' => $data['contact_id_doc_copy_back'],
                'contact_period_begin' => $data['contact_period_begin'],
                'contact_period_end' => $data['contact_period_end'],
                'business_authorization_letter' => $data['business_authorization_letter']
            ];
            $postData['contact_info'] = array_merge($postData['contact_info'], $LEGALArr);
        }

        //主体信息
        $postData['subject_info'] = [
            'subject_type' =>   $data['subject_type'],
            'business_license_info' => [
                'license_copy' => $data['license_copy'],
                'license_number' => $data['license_number'],
                'merchant_name' => $data['merchant_name'],
                'legal_person' => $data['id_card_name'],
            ],
            'identity_info' => [
                'id_doc_type' => 'IDENTIFICATION_TYPE_IDCARD',
                'id_card_info' => [
                    'id_card_copy' => $data['id_card_copy'],
                    'id_card_national' => $data['id_card_national'],
                    'id_card_name' => $this->encrpt($data['id_card_name']),
                    'id_card_number' => $this->encrpt($data['id_card_number']),
                    'id_card_address' => $this->encrpt($data['id_card_address']),
                    'card_period_begin' => $data['card_period_begin'],
                    'card_period_end' => $data['card_period_end'],
                ],
                // 'owner' => true
            ],

        ];
        if ($data['subject_type'] == 'SUBJECT_TYPE_ENTERPRISE') {
            $postData['subject_info']['identity_info']['owner'] = true;
            // $postData['subject_info']['ubo_info_list'] = [
            //     [
            //         "ubo_id_doc_address" => $this->encrpt("广东省深圳市南山区"),
            //         "ubo_id_doc_copy" => "JkiSftnTQuz9y_0yzbRXZov6CaAYlb2255Gd4XXBL12N7M0_edmVs-qqjg5J_w3Fk2shaAb7Ds-5iaOai7hpT7ZPe8N_jJMADlwAmFD-DLY",
            //         "ubo_id_doc_copy_back" => "JkiSftnTQuz9y_0yzbRXZqDDwlOMFYPTp7nG0VTNmNgVP1mhvPcFVlO59c4hiqAURja50vexMRJ0v7WGscu-_Hl72ias22m_gGmUbmJC17Q",
            //         "ubo_id_doc_name" => $this->encrpt("闫哲尉"),
            //         "ubo_id_doc_number" => $this->encrpt("232131199705011013"),
            //         "ubo_id_doc_type" => "IDENTIFICATION_TYPE_IDCARD",
            //         "ubo_period_begin" => "2016-05-06",
            //         "ubo_period_end" => "2026-05-06"
            //     ]
            // ];
        } else {
            // $postData['subject_info']['owner'] = false;
        }
        // $postData['subject_info']['owner'] = false;
        // $postData['subject_info']['owner'] = null;


        //经营资料
        $postData['business_info'] = [
            'merchant_shortname' => $data['merchant_shortname'],
            'service_phone' => $data['service_phone'],
        ];
        $postData['business_info']['sales_info']['sales_scenes_type'] = array_filter(explode(',', $data['sales_scenes_type']));
        //线下门店
        if (in_array('SALES_SCENES_STORE', array_filter(explode(',', $data['sales_scenes_type'])))) {
            $postData['business_info']['sales_info']['biz_store_info'] = [
                'biz_store_name' => $data['biz_store_name'],
                'biz_address_code' => $data['biz_address_code'],
                'biz_store_address' => $data['biz_store_address'],
                'store_entrance_pic' => array_filter(explode(',', $data['store_entrance_pic'])),
                'indoor_pic' => array_filter(explode(',', $data['indoor_pic'])),
            ];
        }
        //公众号
        if (in_array('SALES_SCENES_MP', array_filter(explode(',', $data['sales_scenes_type'])))) {
            $postData['business_info']['sales_info']['mp_info'] = [
                'mp_appid' => $data['mp_appid'],
                'mp_pics' => array_filter(explode(',', $data['mp_pics'])),
            ];
        }

        if ($data['activities_rate'] > 0) {
            //结算规则
            $postData['settlement_info'] = [
                'settlement_id' => $data['settlement_id'],
                'qualification_type' => $data['qualification_type'],
                'activities_id' => $data['activities_id'],
                'activities_rate' => $data['activities_rate'],
                'qualifications' => array_filter(explode(',', $data['qualifications']))
            ];
        } else {
            //结算规则
            $postData['settlement_info'] = [
                'settlement_id' => $data['settlement_id'],
                'qualification_type' => $data['qualification_type'],
                'qualifications' => array_filter(explode(',', $data['qualifications']))
            ];
        }


        //结算银行账户
        // var_dump($data['bank_name']);exit;
        $postData['bank_account_info'] = [
            'bank_account_type' => $data['bank_account_type'],
            'account_name' => $this->encrpt($data['account_name']),
            'bank_name' => $data['bank_name'],
            'account_bank' => $data['account_bank'],
            'bank_address_code' => $data['bank_address_code'],
            'account_number' => $this->encrpt($data['account_number']),
        ];

        $header = $this->sign('post', $url, json_encode($postData, JSON_UNESCAPED_UNICODE));
        Log::write($postData, '提交申请单-postData_____');
        $res = request_url(
            $url,
            'post',
            json_encode($postData, JSON_UNESCAPED_UNICODE),
            array_merge($header, ['Content-Type: application/json', 'Wechatpay-Serial: ' . $this->pub_serial_no])
        );
        Log::write(json_encode($res, JSON_UNESCAPED_UNICODE), '提交申请单 返回结果_____');
        if (!in_array('code', array_keys($res))) {
            return $res;
        }
        return $res['message'];
    }


    private function get_cert()
    {

        $url = 'https://api.mch.weixin.qq.com/v3/certificates';
        //获取头部验证签名信息
        $header = $this->sign('get', $url, '');
        $header = array_merge(
            $header,
            [
                'Content-Type: application/json',
            ]
        );
        Log::write('获取头部验证签名信息请求 url', $url);
        Log::write('获取头部验证签名信息请求 header', json_encode($header, JSON_UNESCAPED_UNICODE));
        $res = request_url($url, 'get', '', $header);
        Log::write('获取头部验证签名信息请求 res', json_encode($res, JSON_UNESCAPED_UNICODE));
        if (in_array('code', array_keys($res))) {
            return '';
        }
        $this->pub_serial_no = $res['data'][0]['serial_no'];
        $encrypt_certificate = $res['data'][0]['encrypt_certificate'];
        Log::write('获取头部验证签名信息', json_encode([$encrypt_certificate['ciphertext'], $encrypt_certificate['associated_data'], $encrypt_certificate['nonce']]));
        $result = $this->decrpt($encrypt_certificate['ciphertext'], $encrypt_certificate['associated_data'], $encrypt_certificate['nonce']);
        Log::write('获取头部验证签名信息 result', $result);
        $this->pub_cert = $result;
    }

    /**
     * 上传图片
     */
    public function upload_img($file_path)
    {
        $url = 'https://api.mch.weixin.qq.com/v3/merchant/media/upload';
        if (strpos($file_path, "http") !== 0) {
            $file_path = request()->domain() . '/' . $file_path;
        }
        // $filepath 图片地址
        $imginfo     = pathinfo($file_path);
        $picturedata = file_get_contents($file_path);
        $sign        = hash('sha256', $picturedata);
        $meta        = [
            "filename" => $imginfo['basename'],
            "sha256"   => $sign,
        ];

        $boundary = 'qjwl7derenufded'; //分割符号
        $boundarystr = "--{$boundary}\r\n";
        // $out是post的内容
        $out = $boundarystr;
        $out .= 'Content-Disposition: form-data; name="meta"' . "\r\n";
        $out .= 'Content-Type: application/json; charset=UTF-8' . "\r\n";
        $out .= "\r\n";
        $filestr = json_encode($meta);
        $out .= "" . $filestr . "\r\n";
        $out .=  $boundarystr;
        $out .= 'Content-Disposition: form-data; name="file"; filename="' . $imginfo['basename'] . '"' . "\n";
        $out .= 'Content-Type: image/jpeg;' . "\r\n";
        $out .= "\r\n";
        $out .= $picturedata . "\r\n";
        $out .= "--{$boundary}--\r\n";

        $header = $this->sign('post', $url, $filestr);

        $res = request_url($url, 'post', $out, array_merge($header, ["Content-Type: multipart/form-data;boundary=" . $boundary]));
        if (!in_array('code', array_keys($res))) {
            return $res;
        }
        return $res['message'];
    }



    /**
     * 解密
     */
    public function decrpt($ciphertext, $associated_data, $nonce)
    {
        Log::write($ciphertext, 'debug_______1');
        $ciphertext = base64_decode($ciphertext);
        Log::write($ciphertext, 'debug_______2');
        // openssl (PHP >= 7.1 support AEAD)
        if (PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods())) {
            $ctext = substr($ciphertext, 0, -16);
            $authTag = substr($ciphertext, -16);
            Log::write($ctext, 'proapi-debug_______3');
            Log::write($authTag, 'proapi-debug_______4');
            Log::write($this->mchid_secret, 'proapi-debug_______5');
            Log::write($nonce, 'proapi-debug_______6');
            Log::write($authTag, 'proapi-debug_______7');
            Log::write($associated_data, 'proapi-debug_______8');
            $res = openssl_decrypt(
                $ctext,
                'aes-256-gcm',
                $this->mchid_secret,
                OPENSSL_RAW_DATA,
                $nonce,
                $authTag,
                $associated_data
            );
            Log::write('proapi____微信支付解密desrpt res ' . $res);
            return $res;
        }
        Log::write('proapi____ 返回空：' . PHP_VERSION_ID >= 70100 && in_array('aes-256-gcm', \openssl_get_cipher_methods()));
        return '';
    }
    /**
     * 加密
     */
    public    function encrpt($str)
    {
        $public_key = $this->pub_cert;
        $encrypted = '';
        Log::write('加密encrpy 参数__ ' . json_encode([$str, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING], JSON_UNESCAPED_UNICODE));
        if (openssl_public_encrypt($str, $encrypted, $public_key, OPENSSL_PKCS1_OAEP_PADDING)) {
            //base64编码 
            $sign = base64_encode($encrypted);
            Log::write('加密encrpy 异常 encrypt base64_encode ' . $sign);
        } else {
            Log::write('加密encrpy 异常 encrypt failed');
            throw new Exception('encrypt failed');
        }
        return $sign;
    }

    function getUrlFile($url)
    {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        //在需要用户检测的网页里需要增加下面两行
        $contents = curl_exec($ch);
        curl_close($ch);
        return  $contents;
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


        $getmchid_private_key = file_get_contents($this->mchid_private_key);
        Log::write($getmchid_private_key, '签名sign 获取的远程private_key');
        openssl_sign($message, $raw_sign, $getmchid_private_key, 'sha256WithRSAEncryption');
        Log::write($raw_sign, '签名sign 获取的远程private_key chunk_split 处理后 sign base64 前');
        $sign = base64_encode($raw_sign);
        Log::write($sign, '签名sign 获取的远程private_key chunk_split 处理后 sign base64 后');
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
            "User-Agent: " . $this->mchid,
            // 'Accept: application/json',
        ];
    }
}
