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


use app\common\model\TimeModel;

class SystemAuth extends TimeModel
{

    protected $deleteTime = 'delete_time';

    /**
     * 根据角色ID获取授权节点
     * @param $authId
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAuthorizeNodeListByAdminId($authId)
    {
        $checkNodeList = (new SystemAuthNode())
            ->where('auth_id', $authId)
            ->column('node_id');
        $systemNode = new SystemNode();
        $nodelList = $systemNode
            ->where('is_auth', 1)
            ->field('id,node,title,type,is_auth')
            ->select()
            ->toArray();
        $newNodeList = [];
        foreach ($nodelList as $vo) {
            if ($vo['type'] == 1) {
                $vo = array_merge($vo, ['field' => 'node', 'spread' => true]);
                $vo['checked'] = false;
                $vo['title'] = "{$vo['title']}【{$vo['node']}】";
                $children = [];
                foreach ($nodelList as $v) {
                    if ($v['type'] == 2 && strpos($v['node'], $vo['node'] . '/') !== false) {
                        $v = array_merge($v, ['field' => 'node', 'spread' => true]);
                        $v['checked'] = in_array($v['id'], $checkNodeList) ? true : false;
                        $v['title'] = "{$v['title']}【{$v['node']}】";
                        $children[] = $v;
                    }
                }
                !empty($children) && $vo['children'] = $children;
                $newNodeList[] = $vo;
            }
        }
        return $newNodeList;
    }

}