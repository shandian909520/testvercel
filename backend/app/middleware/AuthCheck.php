<?php

declare(strict_types=1);

namespace app\middleware;

class AuthCheck
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
        //
        //$hosts = $_SERVER['HTTP_HOST'].'|'.$_SERVER['SERVER_NAME'];
       // if(!cache($hosts.'auth')){
           // $ckret = file_get_contents('http://sq.xiaojiangy.cn/check.php?a=index&appsign=40_221102213032934_8873fc6c_1974480a68accb3e8dfdb66cf06a6cd6&authcode=636270d54352b&h='.urlencode($hosts).'&t='.$_SERVER['REQUEST_TIME'].'&token='.md5($_SERVER['REQUEST_TIME'].'|'.$hosts.'|xzphp|b63c55a534'));
            //if($ckret){
               // $ckret = json_decode($ckret, true);
               // if($ckret['status'] != 1){
                    ///exit($ckret['msg']);
              //  }
               // cache($hosts .'auth',1);
             //   unset($hosts,$ckret);
           // }else{
             //   exit('授权检测失败，请联系授权提供商。');
           // }
      //  }
        return $next($request);
    }
}
