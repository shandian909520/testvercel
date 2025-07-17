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



namespace app\admin\model;



use app\common\constants\MenuConstant;
use app\common\model\TimeModel;

class SystemMenu extends TimeModel
{

    protected $deleteTime = 'delete_time';

    public function getPidMenuList()
    {
        $list        = $this->field('id,pid,title')
            ->where([
                ['pid', '<>', MenuConstant::HOME_PID],
                ['status', '=', 1],
            ])
            ->select()
            ->toArray();
        $pidMenuList = $this->buildPidMenu(0, $list);
        $pidMenuList = array_merge([[
            'id'    => 0,
            'pid'   => 0,
            'title' => '顶级菜单',
        ]], $pidMenuList);
        return $pidMenuList;
    }

    protected function buildPidMenu($pid, $list, $level = 0)
    {
        $newList = [];
        foreach ($list as $vo) {
            if ($vo['pid'] == $pid) {
                $level++;
                foreach ($newList as $v) {
                    if ($vo['pid'] == $v['pid'] && isset($v['level'])) {
                        $level = $v['level'];
                        break;
                    }
                }
                $vo['level'] = $level;
                if ($level > 1) {
                    $repeatString = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    $markString   = str_repeat("{$repeatString}├{$repeatString}", $level - 1);
                    $vo['title']  = $markString . $vo['title'];
                }
                $newList[] = $vo;
                $childList = $this->buildPidMenu($vo['id'], $list, $level);
                !empty($childList) && $newList = array_merge($newList, $childList);
            }

        }
        return $newList;
    }

}