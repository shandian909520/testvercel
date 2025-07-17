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
// | 
// +----------------------------------------------------------------------

// [ 应用入口文件 ]
namespace think;

require __DIR__ . '/../vendor/autoload.php';

// 声明全局变量
define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS . '..' . DS);

// 判断是否安装程序
if (!is_file(ROOT_PATH . 'config' . DS . 'install' . DS . 'lock' . DS . 'install.lock')) {
    exit(header("location:/install2.php"));
}
header("Access-Control-Allow-Origin: *");

//isset($_SESSION) or session_start();
//if (!isset($_SESSION['authcode']) || $_SESSION['authcode'] != '620ca4a4cf33a') {
   // $hosts = $_SERVER['HTTP_HOST'] . '|' . $_SERVER['SERVER_NAME'];
   // $ckret = file_get_contents('http://shouquan.hrbqjwl.cn/check.php?a=index&appsign=35_220216151837149_5622840f_5099eccc37f8f44bea6bd1fc3f3294ec&authcode=620ca4a4cf33a&h=' . urlencode($hosts) . '&t=' . $_SERVER['REQUEST_TIME'] . '&token=' . md5($_SERVER['REQUEST_TIME'] . '|' . $hosts . '|xzphp|b63c55a534'), false, stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 3))));
    //if ($ckret) {
      //  $ckret = json_decode($ckret, true);
       // //if ($ckret['status'] != 1 || $ckret['authcode'] != '620ca4a4cf33a') {
          //  exit($ckret['msg']);
      //  }// else {
          // // $_SESSION['authcode'] = '620ca4a4cf33a';
         //   unset($hosts, $ckret);
        //}
   // } else {
       // exit('授权检测失败，请联系授权提供商。');
  //  }
//} 
// 执行HTTP应用并响应
$http = (new App())->http;

$response = $http->run();

$response->send();

$http->end($response);
