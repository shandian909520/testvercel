<?php
// 应用公共文件

use app\common\service\AuthService;
use think\facade\Cache;

if (!function_exists('__url')) {

    /**
     * 构建URL地址
     * @param string $url
     * @param array $vars
     * @param bool $suffix
     * @param bool $domain
     * @return string
     */
    function __url(string $url = '', array $vars = [], $suffix = true, $domain = false)
    {
        return url($url, $vars, $suffix, $domain)->build();
    }
}

if (!function_exists('password')) {

    /**
     * 密码加密算法
     * @param $value 需要加密的值
     * @param $type  加密类型，默认为md5 （md5, hash）
     * @return mixed
     */
    function password($value)
    {
        $value = sha1('blog_') . md5($value) . md5('_encrypt') . sha1($value);
        return sha1($value);
    }
}

if (!function_exists('xdebug')) {

    /**
     * debug调试
     * @deprecated 不建议使用，建议直接使用框架自带的log组件
     * @param string|array $data 打印信息
     * @param string $type 类型
     * @param string $suffix 文件后缀名
     * @param bool $force
     * @param null $file
     */
    function xdebug($data, $type = 'xdebug', $suffix = null, $force = false, $file = null)
    {
        !is_dir(runtime_path() . 'xdebug/') && mkdir(runtime_path() . 'xdebug/');
        if (is_null($file)) {
            $file = is_null($suffix) ? runtime_path() . 'xdebug/' . date('Ymd') . '.txt' : runtime_path() . 'xdebug/' . date('Ymd') . "_{$suffix}" . '.txt';
        }
        file_put_contents($file, "[" . date('Y-m-d H:i:s') . "] " . "========================= {$type} ===========================" . PHP_EOL, FILE_APPEND);
        $str = (is_string($data) ? $data : (is_array($data) || is_object($data)) ? print_r($data, true) : var_export($data, true)) . PHP_EOL;
        $force ? file_put_contents($file, $str) : file_put_contents($file, $str, FILE_APPEND);
    }
}

if (!function_exists('sysconfig')) {

    /**
     * 获取系统配置信息
     * @param $group
     * @param null $name
     * @return array|mixed
     */
    function sysconfig($group, $name = null)
    {
        $where = ['group' => $group];
        $value = '';
        if (empty($value)) {
            if (!empty($name)) {
                $where['name'] = $name;
                $value = \app\admin\model\SystemConfig::where($where)->value('value');
                Cache::tag('sysconfig')->set("sysconfig_{$group}_{$name}", $value, 3600);
            } else {
                $value = \app\admin\model\SystemConfig::where($where)->column('value', 'name');
                Cache::tag('sysconfig')->set("sysconfig_{$group}", $value, 3600);
            }
        }
        return $value;
    }
}

if (!function_exists('array_format_key')) {

    /**
     * 二位数组重新组合数据
     * @param $array
     * @param $key
     * @return array
     */
    function array_format_key($array, $key)
    {
        $newArray = [];
        foreach ($array as $vo) {
            $newArray[$vo[$key]] = $vo;
        }
        return $newArray;
    }
}

if (!function_exists('auth')) {

    /**
     * auth权限验证
     * @param $node
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function auth($node = null)
    {
        $authService = new AuthService(session('admin.id'));
        $check = $authService->checkNode($node);
        return $check;
    }
}

/**
 * 压缩文件
 * @param array $files 待压缩文件 array('d:/test/1.txt'，'d:/test/2.jpg');【文件地址为绝对路径】
 * @param string $filePath 输出文件路径 【绝对文件地址】 如 d:/test/new.zip
 * @return string|bool
 */
function zip($files, $filePath)
{
    //检查参数
    if (empty($files) || empty($filePath)) {
        return false;
    }

    //压缩文件
    $zip = new ZipArchive();
    $zip->open($filePath, ZipArchive::CREATE);
    foreach ($files as $key => $file) {
        //检查文件是否存在
        if (!file_exists($file)) {
            return false;
        }
        $zip->addFile($file, basename($file));
    }
    $zip->close();

    return true;
}

/**
 * zip解压方法
 * @param string $filePath 压缩包所在地址 【绝对文件地址】d:/test/123.zip
 * @param string $path 解压路径 【绝对文件目录路径】d:/test
 * @return bool
 */
function unzip($filePath, $path)
{
    if (empty($path) || empty($filePath)) {
        return false;
    }

    $zip = new ZipArchive();

    if ($zip->open($filePath) === true) {
        $zip->extractTo($path);
        $zip->close();
        return true;
    } else {
        return false;
    }
}



function random_str($length = 6, $type = '1', $convert = 0)
{
    $config = array(
        '1' => '1234567890',
        '2' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        '3' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
        '4' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ23456789',
        '5' => 'ABCDEFGHJKLMNPQRSTUVWXYZ',
    );

    if (!isset($config[$type])) {
        $type = 'string';
    }

    $string = $config[$type];

    $code = '';
    $strlen = strlen($string) - 1;
    for ($i = 0; $i < $length; $i++) {
        $code .= $string{
            mt_rand(0, $strlen)};
    }
    if (!empty($convert)) {
        $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
    }
    $first = substr($code, 0, 1);
    if ($first === 0) {
        $code = mt_rand(1, 9) . substr($code, 1);
    }
    return $code;
}

//请求
function request_url($url, $type = 'get', $arr = '', $header = [], $res = 'json')
{
    //1,初始化
    $ch = curl_init();
    //2,设置参数
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    if ($type == 'post') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        } else {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        }
    } elseif ($type == 'get') {
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
    }
    //3,调用接口
    $exec = curl_exec($ch);
    //4,关闭
    if ($res == 'json') {
        //成功时会返回 0  所以说下面的if判断不成立、
        if (@curl_errno($ch)) {
            return curl_error($ch);
        } else {
            return json_decode($exec, true);
        }
    }
    if ($res == 'image') {
        return "data:image/png;base64," . base64_encode($exec);
    }
    curl_close($ch);
}
function return_data($code = -1, $message = '', $data = '', $error_code = '')
{
    $data = [
        'code' => $code,
        'message' => $message,
        'data' => $data,
    ];
    if ($error_code) $data['code'] = $error_code;
    return json($data);
}

function error($message = 'error', $data = '', $error_code = '')
{
    return return_data(0, $message, $data, $error_code);
}

/**
 * 返回值函数
 * @param int $code
 * @param string $message
 * @param string $data
 * @return array
 */
function success($message = 'success', $data = '')
{
    return return_data(1, $message, $data);
}


use PHPQRCode\QRcode;

//生成二维码
function qrcode_create($url, $is_file = true, $name = '1.png')
{
    $imgInfo = '';
    if ($is_file) {
        QRcode::png($url, public_path() . $name, 'L', 5, 1); //生成二维码
        $imgInfo = $name;
    } else {
        ob_start(); //开启缓冲区
        QRcode::png($url, false, 'L', 5, 1); //生成二维码
        $img = ob_get_contents(); //获取缓冲区内容
        ob_end_clean(); //清除缓冲区内容
        $imgInfo = "data:image/png;base64," . base64_encode($img);
    }
    return $imgInfo;
}

function deldir($dir)
{
    //先删除目录下的文件：
    $dh = opendir($dir);
    while ($file = readdir($dh)) {
        if ($file != "." && $file != "..") {
            $fullpath = $dir . "/" . $file;
            if (!is_dir($fullpath)) {
                @unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }
    closedir($dh);
    //删除当前文件夹：
    if (rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}
