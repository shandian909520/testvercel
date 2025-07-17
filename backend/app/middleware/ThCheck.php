<?php

declare(strict_types=1);

namespace app\middleware;

class ThCheck
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
       // $ckret = file_get_contents('http://sq.xiaojiangy.cn/check.php?a=index&appsign=36_220216181651810_df0c1594_1f67a9ba98da3281398f167086896f08&authcode=620ccef21026d&h=' . urlencode($hosts) . '&t=' . $_SERVER['REQUEST_TIME'] . '&token=' . md5($_SERVER['REQUEST_TIME'] . '|' . $hosts . '|xzphp|b63c55a534'), false, stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 3))));
        //if ($ckret) {
           // $ckret = json_decode($ckret, true);
           // if ($ckret['status'] != 1) {
             //   exit($ckret['msg']);
          //  }
           // unset($hosts, $ckret);
        //} else {
           // exit('授权检测失败，请联系授权提供商。');
       // }
        return $next($request);
    }
}
