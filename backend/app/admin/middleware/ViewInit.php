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


use app\admin\service\ConfigService;
use app\common\constants\AdminConstant;
use think\App;
use think\facade\Request;
use think\facade\View;

/**
 * @deprecated  废弃，新版TP不支持在中间件获取控制器相关信息
 * Class ViewInit
 * @package app\admin\middleware
 */
class ViewInit
{

    public function handle(\app\Request $request, \Closure $next)
    {
        list($thisModule, $thisController, $thisAction) = [app('http')->getName(), Request::controller(), $request->action()];
        list($thisControllerArr, $jsPath) = [explode('.', $thisController), null];
        foreach ($thisControllerArr as $vo) {
            empty($jsPath) ? $jsPath = parse_name($vo) : $jsPath .= '/' . parse_name($vo);
        }
        $autoloadJs = file_exists(root_path('public')."static/{$thisModule}/js/{$jsPath}.js") ? true : false;
        $thisControllerJsPath = "{$thisModule}/js/{$jsPath}.js";
        $adminModuleName = config('app.admin_alias_name');
        $isSuperAdmin = session('admin.id') == AdminConstant::SUPER_ADMIN_ID ? true : false;
        $data = [
            'adminModuleName'      => $adminModuleName,
            'thisController'       => parse_name($thisController),
            'thisAction'           => $thisAction,
            'thisRequest'          => parse_name("{$thisModule}/{$thisController}/{$thisAction}"),
            'thisControllerJsPath' => "{$thisControllerJsPath}",
            'autoloadJs'           => $autoloadJs,
            'isSuperAdmin'         => $isSuperAdmin,
            'version'              => env('app_debug') ? time() : ConfigService::getVersion(),
        ];

        View::assign($data);
        $request->adminModuleName = $adminModuleName;
        return $next($request);
    }


}