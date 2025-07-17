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



namespace app\admin\service;


use think\facade\Cache;

class TriggerService
{

    /**
     * 更新菜单缓存
     * @param null $adminId
     * @return bool
     */
    public static function updateMenu($adminId = null)
    {
        if(empty($adminId)){
            Cache::tag('initAdmin')->clear();
        }else{
            Cache::delete('initAdmin_' . $adminId);
        }
        return true;
    }

    /**
     * 更新节点缓存
     * @param null $adminId
     * @return bool
     */
    public static function updateNode($adminId = null)
    {
        if(empty($adminId)){
            Cache::tag('authNode')->clear();
        }else{
            Cache::delete('allAuthNode_' . $adminId);
        }
        return true;
    }

    /**
     * 更新系统设置缓存
     * @return bool
     */
    public static function updateSysconfig()
    {
        Cache::tag('sysconfig')->clear();
        return true;
    }

}