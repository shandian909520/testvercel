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
// | 公司决定2023年1月份对所有盗版用户进行维权诉讼，避免更大损失，请尽早转正。
// +----------------------------------------------------------------------



namespace app\admin\middleware;


use app\Request;
use CsrfVerify\drive\ThinkphpCache;
use CsrfVerify\entity\CsrfVerifyEntity;
use CsrfVerify\interfaces\CsrfVerifyInterface;
use think\facade\Session;

class CsrfMiddleware
{
    use \app\common\traits\JumpTrait;

    public function handle(Request $request, \Closure $next)
    {
        if (env('EASYADMIN.IS_CSRF', true)) {
            if (!in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {

                // 跨域校验
                $refererUrl = $request->header('REFERER', null);
                $refererInfo = parse_url($refererUrl);
                $host = $request->host(true);
                if (!isset($refererInfo['host']) || $refererInfo['host'] != $host) {
                    $this->error('当前请求不合法！');
                }

                // CSRF校验
                // @todo 兼容CK编辑器上传功能
                $ckCsrfToken = $request->post('ckCsrfToken', null);
                $data = !empty($ckCsrfToken) ? ['__token__' => $ckCsrfToken] : [];

                $check = $request->checkToken('__token__', $data);
                if (!$check) {
                    $this->error('请求验证失败，请重新刷新页面！');
                }

            }
        }
        return $next($request);
    }
}
