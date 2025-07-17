<?php

namespace app\common\lib;


class BaiDuApi
{
    private $key;
    private $secret;
    private $pf_id;

    public function __construct($pf_id)
    {
        $this->pf_id = $pf_id;
        $this->key = sysconfig('baidu', 'baidu_api_key' . $this->pf_id);
        $this->secret = sysconfig('baidu', 'baidu_api_secret' . $this->pf_id);
    }

    //获取百度access_token
    public function get_access_token()
    {
        if (cache('baidu_access_token')) return cache('baidu_access_token');
        $url = "https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials"
            . "&client_id={$this->key}"
            . "&client_secret={$this->secret}";

        $res = request_url($url, 'get');

        if (in_array('error', array_keys($res))) {
            return '';
        } else {
            cache('baidu_access_token', $res['access_token'], intval($res['expires_in']) - 300);
            return $res['access_token'];
        }
        return '';
    }

    //调用营业执照识别
    public function get_business_pic_info($base64pic)
    {
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/business_license?access_token=' . $this->get_access_token();
        $data = [
            'image' => $base64pic,
        ];
        $res = request_url($url, 'post', $data);
        if (in_array('error_code', array_keys($res))) {
            return false;
        }
        $code = @$res['words_result']['社会信用代码']['words'] ?: (@$res['words_result']['组织机构代码']['words'] ?: @$res['words_result']['营业执照注册号']['words']);
        return [
            'code_type' => strlen($code),
            'name' => $res['words_result']['单位名称']['words'],
            'code' => $code,
            'person_name' => $res['words_result']['法人']['words'],
        ];
    }

    public function get_idcard_info($base64pic)
    {
        $url = 'https://aip.baidubce.com/rest/2.0/ocr/v1/idcard?access_token=' . $this->get_access_token();
        $data = [
            'image' => $base64pic,
            'id_card_side' => 'front',
        ];
        $res = request_url($url, 'post', $data);
        if (in_array('error_code', array_keys($res))) {
            return false;
        }
        if ($res['image_status'] == 'normal') {

            return [
                'name' => $res['words_result']['姓名']['words'],
                'idcard' => $res['words_result']['公民身份号码']['words'],
                'id_card_address' => $res['words_result']['住址']['words'],
            ];
        } elseif ($res['image_status'] == 'reversed_side') {
            return [
                'start_time' => $res['words_result']['签发日期']['words'],
                'end_time' => $res['words_result']['失效日期']['words']
            ];
        }
        return [];
    }
}
