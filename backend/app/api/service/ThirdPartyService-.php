<?php

namespace app\api\service;

use app\admin\model\SystemConfig;
use app\admin\service\TriggerService;
use think\facade\Log;

class ThirdPartyService
{
    public function __construct()
    {
    }

    //获取token
    public static function get_component_access_token($pf_id)
    {
        Log::write('获取token get_component_access_token pf_id: ' . $pf_id);
        if (cache('component_access_token' . $pf_id) != '')
            return cache('component_access_token' . $pf_id);
        if (cache('ComponentVerifyTicket' . $pf_id) == '')
            return '';
        // 请求接口
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $data = [
            'component_appid' => sysconfig('app_config', 'app_id' . $pf_id),
            'component_appsecret' => sysconfig('app_config', 'app_secret' . $pf_id),
            'component_verify_ticket' => cache('ComponentVerifyTicket' . $pf_id)
        ];
        $res =  request_url($url, 'post', json_encode($data));
        // Log::info('获取component_access_token 返回结果为: {data}', ['data' => json_encode($res)]);
        if (in_array('errcode', array_keys($res))) {
            Log::error('获取component_access_token失败 返回结果为: {data}', ['data' => json_encode($res)]);
            return '';
        }
        $token = '';
        Log::write('获取get_component_access_token 结果: ' . json_encode($res, JSON_UNESCAPED_UNICODE));
        if (in_array('component_access_token', array_keys($res))) {
            cache('component_access_token' . $pf_id, $res['component_access_token'], 7200 - 300);
        }
        Log::save();
        return $res['component_access_token'];
    }

    //获取预授权码
    public function get_pre_auth_code($pf_id)
    {
        $token = $this->get_component_access_token($pf_id);
        if ($token == '') return '';
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_create_preauthcode?component_access_token=' . $token;
        $data = [
            'component_appid' => sysconfig('app_config', 'app_id' . $pf_id),
        ];
        $res = request_url($url, 'post', json_encode($data));
        if (in_array('errcode', array_keys($res))) {
            Log::error('获取pre_auth_code失败 返回结果为: {data}', ['data' => json_encode($res)]);
            return '';
        }
        if (in_array('pre_auth_code', array_keys($res))) {
            return $res['pre_auth_code'];
        }
        Log::save();
        return '';
    }

    //获取授权二维码链接
    public function get_auth_url($type, $redirect_uri, $pf_id)
    {
        Log::write('获取授权二维码链接 get_auth_url pf_id: ' . $pf_id);
        $url = '';
        $app_id = sysconfig('app_config', 'app_id' . $pf_id);
        $pre_auth_code = $this->get_pre_auth_code($pf_id);
        if (!$pre_auth_code) return '';
        if (strtoupper($type) == 'PC') {
            $url = 'https://mp.weixin.qq.com/cgi-bin/componentloginpage?'
                . "component_appid={$app_id}&pre_auth_code={$pre_auth_code}&auth_type=3&redirect_uri={$redirect_uri}";
        }
        if (strtoupper($type) == 'PHONE') {
            $url = 'https://mp.weixin.qq.com/safe/bindcomponent?'
                . "action=bindcomponent&no_scan=1&component_appid={$app_id}&pre_auth_code={$pre_auth_code}&auth_type=3&redirect_uri={$redirect_uri}#wechat_redirect";
        }
        return $url;
    }

    //获取小程序access_token
    public function get_xcx_access_token($type, $param, $verifybetaweapp = false, $pf_id)
    {
        if ($type == 'auth') {
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=' . $this->get_component_access_token($pf_id);
            $data = [
                'component_appid' => sysconfig('app_config', 'app_id' . $pf_id),
                'authorization_code' => $param,
            ];
            Log::write('获取小程序 access_token 地址: ' . $url);
            Log::write('获取小程序 access_token 参数: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
            $res = request_url($url, 'post', json_encode($data));
            // Log::info('获取小程序 access_token 返回结果为: {data}', ['data' => json_encode($res)]);
            if (in_array('errcode', array_keys($res))) {
                Log::error('获取小程序 access_token 返回结果为: {data}', ['data' => json_encode($res)]);
                return '';
            }
            Log::write('获取小程序 authorization_info res__________here: ' . json_encode($res, JSON_UNESCAPED_UNICODE));
            if (in_array('authorization_info', array_keys($res))) {
                $xcx_app_id = $res['authorization_info']['authorizer_appid'];
                $xcx_access_token =  $res['authorization_info']['authorizer_access_token'];
                $xcx_refresh_token =  $res['authorization_info']['authorizer_refresh_token'];
                cache('xcx_' . $xcx_app_id . '_access_token' . $pf_id, $xcx_access_token, 7200 - 300);
                $dbxcxappid = SystemConfig::where('name', 'xcx_app_id' . $pf_id)->find();
                if (empty($dbxcxappid)) {
                    SystemConfig::create([
                        'name'  => 'xcx_app_id' . $pf_id,
                        'group'  => 'xcx_config',
                        'value'  => $xcx_app_id,
                        'remark'  => 'xcx_app_id',
                    ]);
                } else {
                    SystemConfig::where('name', 'xcx_app_id' . $pf_id)->update(['value' => $xcx_app_id]);
                }
                $dbxcx_refresh_token = SystemConfig::where('name', 'xcx_refresh_token' . $pf_id)->find();
                if (empty($dbxcx_refresh_token)) {
                    SystemConfig::create([
                        'name'  => 'xcx_refresh_token' . $pf_id,
                        'group'  => 'xcx_config',
                        'value'  => $xcx_refresh_token,
                        'remark'  => 'xcx_refresh_token',
                    ]);
                } else {
                    SystemConfig::where('name', 'xcx_refresh_token' . $pf_id)->update(['value' => $xcx_refresh_token]);
                }
                TriggerService::updateSysconfig();
                $this->get_xcx_info($pf_id);
                return $res['authorization_info']['authorizer_access_token'];
            }
        } else {
            if (cache('xcx_' . $param . '_access_token' . $pf_id)) return  cache('xcx_' . $param . '_access_token' . $pf_id);
            if ($verifybetaweapp) {
                // $refresh_token = 'refreshtoken@@@ykVTaIYzUkxM5LTZ1--_0RJSxp9FyAD88s9qk1FPGSY';
                $refresh_token = cache('xcx_rt_' . $param . '_access_token' . $pf_id);
            } else {
                $refresh_token =  sysconfig('xcx_config', 'xcx_refresh_token' . $pf_id);
            }
            if (!$refresh_token) {
                Log::write('api_get_authorizer_info 获取 authorizer_refresh_token pf_id参数' . $pf_id);
                Log::write('api_get_authorizer_info 获取 authorizer_refresh_token 参数' . json_encode($param, JSON_UNESCAPED_UNICODE));
                $getapai = $this->api_get_authorizer_info($param, $pf_id);
                if (empty($getapai['authorization_info']) || empty($getapai['authorization_info']['authorizer_refresh_token'])) {
                    Log::write('api_get_authorizer_info 获取 authorizer_refresh_token 错误' . json_encode($getapai, JSON_UNESCAPED_UNICODE));
                    return '';
                }
                $refresh_token = $getapai['authorization_info']['authorizer_refresh_token'];
                cache('xcx_rt_' . $param . '_access_token' . $pf_id, $refresh_token, 7200 - 300);
            };
            $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $this->get_component_access_token($pf_id);
            $data = [
                'component_appid' => sysconfig('app_config', 'app_id' . $pf_id),
                'authorizer_appid' => $param,
                'authorizer_refresh_token' => $refresh_token
            ];
            $res = request_url($url, 'post', json_encode($data));
            Log::info('获取小程序 access_token 返回结果为: {data}', ['data' => json_encode($res)]);
            if (in_array('errcode', array_keys($res))) {
                Log::error('获取小程序 access_token 返回结果为: {data}', ['data' => json_encode($res)]);
                return '';
            }
            if (in_array('authorizer_access_token', array_keys($res))) {
                cache('xcx_' . $param . '_access_token' . $pf_id, $res['authorizer_access_token'], 7200 - 300);
                return $res['authorizer_access_token'];
            }
        }
        return '';
    }
    //试用小程序授权码获取授权信息
    public function get_fast_xcx_access_token($param, $pf_id)
    {
        Log::write('获取试用小程序授权码获取授权信息: ' . $param);
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_query_auth?component_access_token=' . $this->get_component_access_token($pf_id);
        $data = [
            'component_appid' => sysconfig('app_config', 'app_id' . $pf_id),
            'authorization_code' => $param,
        ];
        Log::write('获取试用小程序授权码获取授权信息 请求地址: ' . $url);
        Log::write('获取试用小程序授权码获取授权信息 请求参数: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
        $res = request_url($url, 'post', json_encode($data));
        Log::write('获取试用小程序授权码获取授权信息 请求结果: ' . json_encode($res, JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            Log::write('获取试用小程序授权码获取授权信息 缓存失败1');
            Log::error('get_fast_xcx_access_token 获取小程序 access_token 返回结果为: {data}', ['data' => json_encode($res)]);
            return '';
        }
        if (in_array('authorization_info', array_keys($res))) {
            Log::write('获取试用小程序授权码获取授权信息 缓存成功');
            $xcx_app_id = $res['authorization_info']['authorizer_appid'];
            $xcx_access_token =  $res['authorization_info']['authorizer_access_token'];
            $xcx_refresh_token =  $res['authorization_info']['authorizer_refresh_token'];
            cache('xcx_' . $xcx_app_id . '_access_token' . $pf_id, $xcx_access_token, 7200 - 300);
            cache('xcx_rt_' . $xcx_app_id . '_access_token' . $pf_id, $xcx_refresh_token, 7200 - 300);
        }
        Log::write('获取试用小程序授权码获取授权信息 缓存失败2');
        return '';
    }
    //获取小程序信息
    public function get_xcx_info($pf_id)
    {
        $res =    json_decode(sysconfig('xcx_config', 'xcx_info' . $pf_id), true) ?: [];
        if (count($res) < 1 || $res['appid'] != sysconfig('xcx_config', 'xcx_app_id' . $pf_id)) {
            $xcx_access_token = $this->get_xcx_access_token('id', sysconfig('xcx_config', 'xcx_app_id' . $pf_id), false, $pf_id);
            $url  = 'https://api.weixin.qq.com/cgi-bin/account/getaccountbasicinfo?access_token=' . $xcx_access_token;
            $res = request_url($url, 'get');
        }
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {



                $dbxcx_info = SystemConfig::where('name', 'xcx_info' . $pf_id)->find();
                if (empty($dbxcx_info)) {
                    SystemConfig::create([
                        'name'  => 'xcx_info' . $pf_id,
                        'group'  => 'xcx_config',
                        'value'  => json_encode($res),
                        'remark'  => 'xcx_info',
                    ]);
                } else {
                    SystemConfig::where('name', 'xcx_info' . $pf_id)->update(['value' => json_encode($res)]);
                }
                $data = [
                    'app_id' => $res['appid'],
                    'name' => $res['nickname'],
                ];
                TriggerService::updateSysconfig();
                return $data;
            }
        }
        return [];
    }


    //核名
    public function check_xcx_name($name, $pf_id)
    {
        $xcx_access_token = $this->get_xcx_access_token('id', sysconfig('xcx_config', 'xcx_app_id' . $pf_id), false, $pf_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/wxverify/checkwxverifynickname?access_token=' . $xcx_access_token;
        $data = [
            'nick_name' => $name
        ];

        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return ['code' => 1, 'message' => '小程序名称可用！'];
            }
            return ['code' => 0, 'message' => config('wx.' . $res['errcode'])];
        }
    }


    //注册个人小程序
    public function register_persion($data, $pf_id)
    {
        $url = 'https://api.weixin.qq.com/wxa/component/fastregisterpersonalweapp?action=create&component_access_token=' . $this->get_component_access_token($pf_id);
        $postData = [
            'idname' => $data['name'],
            'wxuser' => $data['wx_code'],
            'component_phone' => $data['component_phone']
        ];
        Log::write("个人注册小程序 参数" . json_encode($postData, JSON_UNESCAPED_UNICODE));
        $res = request_url($url, 'post', json_encode($postData, JSON_UNESCAPED_UNICODE));
        Log::write("个人注册小程序 结果" . json_encode($res, JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return ['code' => 1, 'data' => $res, 'message' => '请扫码验证！', 'errcode' => $res['errcode']];
            }
            return ['code' => 0, 'message' => config('returncode.' . $res['errcode']), 'errcode' => $res['errcode']];
        }
    }

    //注册企业小程序
    public function register_company($data, $pf_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/fastregisterweapp?action=create&component_access_token=' . $this->get_component_access_token($pf_id);
        $postData = [
            'name' => $data['name'],
            'code_type' => $data['code_type'],
            'code' => $data['code'],
            'legal_persona_wechat' => $data['wx_code'],
            'legal_persona_name' => $data['person_name'],
            "component_phone" => $data['component_phone']
        ];
        Log::write("企业注册小程序 参数" . json_encode($postData, JSON_UNESCAPED_UNICODE));
        $res = request_url($url, 'post', json_encode($postData, JSON_UNESCAPED_UNICODE));
        Log::write("企业注册小程序 结果" . json_encode($res, JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return ['code' => 1, 'data' => $res, 'message' => '请法人确认验证信息', 'errcode' => $res['errcode']];
            }
            return ['code' => 0, 'message' => config('returncode.' . $res['errcode']), 'errcode' => $res['errcode']];
        }
    }
    //管理员注册小程序
    public function register_fastregisterbetaweapp($data, $pf_id)
    {
        $url = 'https://api.weixin.qq.com/wxa/component/fastregisterbetaweapp?access_token=' . $this->get_component_access_token($pf_id);
        $data = [
            'name' => $data['xcxname'],
            "openid" => $data['openid'],
        ];
        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return ['code' => 1, 'data' => $res, 'message' => '请求成功',];
            }
            return ['code' => 0, 'message' => config('weapp.' . $res['errcode']), 'errcode' => $res['errcode']];
        }
    }
    //试用小程序 转正
    public function register_verifybetaweapp($data, $pf_id)
    {
        Log::write('__转正init参数' . json_encode($data));
        $codeTypeForm = ['18' => 1, '15' => '3', '9' => '2'];
        $data['code_type'] = $codeTypeForm[$data['code_type']];
        $url = 'https://api.weixin.qq.com/wxa/verifybetaweapp?access_token=' . $this->get_xcx_access_token('id', $data['appid'], true, $pf_id);
        $data['code_type'] = (int)$data['code_type'];
        $qdata['access_token'] = $this->get_xcx_access_token('id', $data['appid'], true, $pf_id); //授权回来的 appid
        $appid = $data['appid'];
        unset($data['appid']);
        $qdata['verify_info'] = $data;
        $res = request_url($url, 'post', json_encode($qdata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        Log::write('转正结果__' . json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return ['code' => 1, 'data' => $res, 'message' => '已提交转正申请' . $appid, 'errcode' => $res['errcode']];
            }
            return ['code' => 0, 'message' => config('weappr.' . $res['errcode']), 'errcode' => $res['errcode']];
        }
    }
    /**
     * 试用小程序更名
     */
    function setbetaweappnickname($data, $pf_id)
    {
        Log::write('__更名init参数' . json_encode($data));
        $url = 'https://api.weixin.qq.com/wxa/setbetaweappnickname?access_token=' . $this->get_xcx_access_token('id', $data['appid'], true, $pf_id);
        $res = request_url($url, 'post', json_encode(['name' => $data['name']], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        Log::write('更名结果__' . json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $errArr = [
            '40001' => 'access_token 无效',
            '86011' => '名称命中了关键字；像小程序、微信、腾讯等以及知名品牌关键字。请更换名字后重试。	请更换名字后重试。',
            '91020' => '小程序注册类型不正确，该接口仅适用于试用小程序调用	小程序注册类型不正确，该接口仅适用于试用小程序调用',
            '41001' => '缺少 access_token 参数'
        ];

        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return ['code' => 1, 'data' => $res, 'message' => '更名成功'];
            }
            return ['code' => 0, 'message' => !empty($errArr[$res['errcode']]) ? $errArr[$res['errcode']] : $res['errmsg'], 'errcode' => $res['errcode']];
        }
    }
    /**
     * 试用小程序转正后 更名
     * 
     */
    function setnickname($data, $pf_id)
    {
        $url = 'https://api.weixin.qq.com/wxa/setnickname?access_token=' . $this->get_xcx_access_token('id', $data['appid'], true, $pf_id);
        unset($data['appid']);
        if (empty($data['naming_other_stuff_1'])) {
            unset($data['naming_other_stuff_1']);
        }
        if (empty($data['naming_other_stuff_2'])) {
            unset($data['naming_other_stuff_2']);
        }
        if (empty($data['naming_other_stuff_3'])) {
            unset($data['naming_other_stuff_3']);
        }
        if (empty($data['naming_other_stuff_4'])) {
            unset($data['naming_other_stuff_4']);
        }
        if (empty($data['naming_other_stuff_5'])) {
            unset($data['naming_other_stuff_5']);
        }
        Log::write('__试用小程序转正后更名init参数' . json_encode($data));
        $res = request_url($url, 'post', json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        Log::write('试用小程序转正后更名结果__' . json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            $errmsg = !empty(config('returncode.' . $res['errcode'])) ? config('returncode.' . $res['errcode']) : $res['errmsg'];
            $wording = !empty($res['wording']) ? $res['wording'] : '';
            $audit_id = !empty($res['audit_id']) ? $res['audit_id'] : '';
            if (empty($audit_id)) {
                if ($res['errcode'] == 0) {
                    return ['code' => 1, 'data' => $res, 'message' => '更名成功'];
                } else {
                    return ['code' => 0, 'message' => $errmsg . '|wording=' . $wording . '|audit_id' . $audit_id, 'errcode' => $res['errcode']];
                }
            } else {
                return $this->api_wxa_querynickname($data['appid'], $audit_id, $pf_id);
            }
        }
    }
    public function api_wxa_querynickname($appid, $audit_id, $pf_id)
    {
        $url = 'https://api.weixin.qq.com/wxa/api_wxa_querynickname?access_token=' . $this->get_xcx_access_token('id', $appid, true, $pf_id);
        $res = request_url($url, 'post', json_encode(['audit_id' => $audit_id], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        Log::write('查询改名审核状态__' . json_encode($res, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if (in_array('errcode', array_keys($res))) {
            if ($res['errcode'] == 0) {
                return ['code' => 1, 'data' => $res, 'message' => '更名成功'];
            } else {
                $errmsg = !empty(config('returncode.' . $res['errcode'])) ? config('returncode.' . $res['errcode']) : $res['errmsg'];
                return ['code' => 0, 'message' =>  $res['audit_stat'] . ',' . $errmsg];
            }
        }
    }

    public function get_xcx_process($taskid, $pf_id)
    {
        $url = "https://api.weixin.qq.com/wxa/component/fastregisterpersonalweapp?action=query&component_access_token=" . $this->get_component_access_token($pf_id);
        $postData = [
            'taskid' => $taskid
        ];
        $res = request_url($url, 'post', json_encode($postData, JSON_UNESCAPED_UNICODE));
        Log::info(json_encode($res));
        Log::save();

        if (in_array('errcode', array_keys($res))) {
            return ['code' => config('xcx_status.' . $res['status']), 'message' => $res['errmsg']];
        }
    }
    function getRid($rid, $pf_id)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/openapi/rid/get?access_token=' . $this->get_component_access_token($pf_id);
        $res = request_url($url, 'post', json_encode(['rid' => $rid]));
        return $res;
    }
    function get_api_get_authorizer_list($pf_id)
    {
        $postData = [
            'access_token' => $this->get_component_access_token($pf_id),
            'component_appid' => sysconfig('app_config', 'app_id' . $pf_id),
            'offset' => 0,
            'count' => 500,
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_list?access_token=' . $this->get_component_access_token($pf_id);
        return request_url($url, 'post', json_encode($postData));
    }
    /**
     * 获取授权账号详情
     */
    function api_get_authorizer_info($authorizer_appid = 0, $pf_id)
    {
        $postData = [
            'component_appid' => sysconfig('app_config', 'app_id' . $pf_id),
            'authorizer_appid' => $authorizer_appid,
        ];
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_get_authorizer_info?access_token=' . $this->get_component_access_token($pf_id);
        return request_url($url, 'post', json_encode($postData));
    }
}
