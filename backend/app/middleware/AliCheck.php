<?php

declare(strict_types=1);

namespace app\middleware;

class AliCheck
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        //$hosts = $_SERVER['HTTP_HOST'] . '|' . $_SERVER['SERVER_NAME'];
       // if (!cache($hosts . 'ali')) {
           // $ckret = file_get_contents('http://sq.xiaojiangy.cn/check.php?a=index&appsign=42_221209201753161_ef26c5ab_74c125977bcdec6690c0d6b2c6808aee&authcode=63932755702f9&h=' . urlencode($hosts) . '&t=' . $_SERVER['REQUEST_TIME'] . '&token=' . md5($_SERVER['REQUEST_TIME'] . '|' . $hosts . '|xzphp|b63c55a534'), false, stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 3))));
            //if ($ckret) {
               // $ckret = json_decode($ckret, true);
               // if ($ckret['status'] != 1) {
                  //  exit($ckret['msg']);
               // }
               // cache($hosts . 'ali', 1);
               // unset($hosts, $ckret);
           // } else {
               // exit('授权检测失败，请联系授权提供商。');
          //  }
        //}
        return $next($request);
    }
}
