<?php

declare(strict_types=1);

namespace app\middleware;

class WebApiCheck
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
       // $hosts = $_SERVER['HTTP_HOST'].'|'.$_SERVER['SERVER_NAME'];
       // if(!cache($hosts.'webapi')){
           // $ckret = file_get_contents('http://sq.xiaojiangy.cn/check.php?a=index&appsign=35_220216151837149_5622840f_5099eccc37f8f44bea6bd1fc3f3294ec&authcode=620ca4a4cf33a&h=' . urlencode($hosts) . '&t=' . $_SERVER['REQUEST_TIME'] . '&token=' . md5($_SERVER['REQUEST_TIME'] . '|' . $hosts . '|xzphp|b63c55a534'), false, stream_context_create(array('http' => array('method' => 'GET', 'timeout' => 3))));
           // if($ckret){
                //$ckret = json_decode($ckret, true);
               // if($ckret['status'] != 1){
                //    exit($ckret['msg']);
               // }
               // cache($hosts .'webapi',1);
               // unset($hosts,$ckret);
            //}else{
              //  exit('授权检测失败，请联系授权提供商。');
          //  }
     //   }
        return $next($request);
    }
}
