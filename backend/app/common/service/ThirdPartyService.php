<?php



namespace app\common\service;

use think\facade\Log;

class ThirdPartyService
{

    private $url;
    private $code;
    private $pf_id;

    public function __construct($pf_id)
    {
        $this->pf_id = $pf_id;
        $this->url = sysconfig('api_auth', 'auth_url' . $this->pf_id);
        $this->code = sysconfig('api_auth', 'auth_code' . $this->pf_id);
    }

    //核名
    public function check_name($name)
    {
        $url = $this->url . '/sapi/index/check_name';
        $postData = [
            'name' => $name,
            'auth_code' => $this->code,
            'component_phone' => sysconfig('base_config', 'service_phone' . $this->pf_id)
        ];
        $res = request_url($url, 'post', json_encode($postData, JSON_UNESCAPED_UNICODE));
        return  $res;
    }

    //注册小程序
    public function register($data, $order_id)
    {
        $url = $this->url . '/sapi/index/register';
        $postData = [
            'host' => request()->host(),
            'order_id' => $order_id,
            'type' => $data['type'],
            'name' => $data['name'],
            'code_type' => $data['code_type'],
            'code' => $data['code'],
            'wx_code' => $data['wx_code'],
            'person_name' => $data['person_name'],
            'auth_code' => $this->code,
            'component_phone' => sysconfig('base_config', 'service_phone' . $this->pf_id)
        ];
        $res = request_url($url, 'post', json_encode($postData, JSON_UNESCAPED_UNICODE));
        return $res;
    }

    /**
     * 查询任务状态
     */
    public function get_xcx_process($taskid)
    {

        $url = $this->url . '/sapi/index/get_xcx_process';

        $postData = [
            'auth_code' => $this->code,
            'taskid' => $taskid,
        ];
        Log::write("查询任务状态地址" . $url);
        Log::write("查询任务状态参数" . json_encode($postData, JSON_UNESCAPED_UNICODE));

        $res = request_url($url, 'post', json_encode($postData, JSON_UNESCAPED_UNICODE));

        Log::write("查询任务状态结果" . json_encode($res, JSON_UNESCAPED_UNICODE));

        return $res;
    }
}
