<?php

namespace app\api\controller;

use app\admin\model\Article as ModelArticle;

class Article
{
    public function index()
    {
        $perPage = input('get.per_page', 10);
        $pf_id = input('get.pf_id', 1);
        $article =  ModelArticle::where(['status' => 1, 'pf_id' => $pf_id])->whereNull('delete_time')->order([
            'sort' => 'asc',   'id' => 'desc'
        ])->paginate($perPage);
        foreach ($article as $v) {
            if (!empty($v['content'])) {
                $v['content'] = htmlspecialchars_decode($v['content']);
            }
        }
        return success('查询成功', $article);
    }
    public function pagreement()
    {
        $pf_id = input('get.pf_id', 1);
        $useragreement =  sysconfig('base_config', 'useragreement'.$pf_id);
        if (!empty($useragreement)) {
            $useragreement = htmlspecialchars_decode($useragreement);
            return success('用户隐私协议', $useragreement);
        } else {
            return error('暂无内容', '');
        }
    }
}
