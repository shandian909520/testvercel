<?php

namespace app\api\controller;

use app\admin\model\SystemNode;
use app\admin\model\UpdateLog;
use app\admin\service\NodeService;
use app\admin\service\TriggerService;
use app\BaseController;
use Exception;
use think\App;
use think\facade\Db;
use think\facade\Log;
use util\Zip;

class Update extends BaseController
{
    public $check_url;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->check_url = 'http://sq.xiaojiangy.cn/upcenter.php?id=40&h=' . urlencode($_SERVER['HTTP_HOST']);
    }

    //检测版本更新
    public function checkversion()
    {
        $use_version     = include root_path() . 'config/version.php';
        $now_version     = $use_version['version'];
        $r = file_get_contents($this->check_url);
        $r = json_decode($r, true);

        if ($r['code'] == '0') {
            return json($r);
        }
        $res = $r['data'];
        $new_version = $res['version']; //版本号
        $desc        = $res['desc'];  //版本更新描述
        if ($new_version > $now_version) {
            return json(['code' => '1', 'msg' => '有新版更新', 'data' => $res]);
        } else {
            return json(['code' => '0', 'msg' => '您已经是最新版本']);
        }
    }

    //开始更新
    public function start_upgrade()
    {
        $use_version     = include root_path() . 'config/version.php';
        $now_version     = $use_version['version'];
        $r = file_get_contents($this->check_url);
        $r = json_decode($r, true);

        if ($r['code'] == '0') {
            return json($r);
        }

        $res         = $r['data'];

        $new_version = $res['version'];
        if ($new_version > $now_version) {
            $down_url = $res['url'];  //版本更新地址
            //开始下载
            $tempath = root_path() . 'tmp';
            $remote_fp = fopen($down_url, 'rb');
            if (!is_dir($tempath)) mkdir($tempath, 0777, true);
            $file = $tempath . '/' . date('Ymd') . '.zip';  //落地更新文件名称
            if (is_file($file)) unlink($file);
            $local_fp = fopen($file, 'wb');
            ob_flush();
            while (!feof($remote_fp)) {
                fwrite($local_fp, fread($remote_fp, 128));
            }
            fclose($remote_fp);
            fclose($local_fp);
            ob_flush();         //下载完成

            unzip($file, root_path());


            //解压完毕,执行升级sql文件
            /*
            if (file_exists(root_path() . '/data/update/update.sql')) {
                $sqls = file_get_contents(root_path() . '/data/update/update.sql');
                $sqls = explode(";\r\n", $sqls);
                foreach ($sqls as $sql) {
                    Db::query($sql);
                }
                deldir(root_path() . 'data');
            }*/
            //更新菜单 权限
            if (file_exists(root_path() . '/data/update/updateAuthMenu.sql')) {
                $sqls = file_get_contents(root_path() . '/data/update/updateAuthMenu.sql');
                $sqls = explode(";\r\n", $sqls);
                foreach ($sqls as $sql) {
                    Db::query($sql);
                }
                //重置ea_system_auth
                $system_auth_id2 = Db::query('select * from ea_system_auth where id = 2');
                if (empty($system_auth_id2)) {
                    Db::query("INSERT INTO `ea_system_auth` (`id`, `title`, `sort`, `status`, `remark`, `create_time`, `update_time`, `delete_time`) VALUES ('2', '代理', '0', '1', '代理管理员', '1667392938', '1667392938', NULL);");
                }
                // 节点表 权限表 更新
                //更新节点
                $nodeList = (new NodeService())->getNodelist();
                if (!empty($nodeList)) {
                    $model = new SystemNode();
                    try {
                        $existNodeList = $model->field('node,title,type,is_auth')->select();
                        foreach ($nodeList as $key => $vo) {
                            foreach ($existNodeList as $v) {
                                if ($vo['node'] == $v->node) {
                                    unset($nodeList[$key]);
                                    break;
                                }
                            }
                        }
                        $model->saveAll($nodeList);
                        TriggerService::updateNode();
                    } catch (Exception $e) {
                    }
                }
            }

            include root_path() . 'public/upgrade.php';
            deldir(root_path() . 'tmp');


            //更新检测版本
            $new_version_arr = array(
                'version'   => $new_version,
            );
            $str = "<?php \r\n\r\n return " . var_export($new_version_arr, true) . ";";
            file_put_contents(root_path() . 'config/version.php', $str);

            //插入更新记录
            $i = [
                'version' => $new_version,
                'desc' => $res['desc'],
                'addtime' => date('Y-m-d H:i:s')
            ];
            try {
                UpdateLog::insert($i);
            } catch (Exception $e) {
                Log::write("更新异常__" . $e->getMessage());
            }
            return json(['code' => '1', 'msg' => '更新成功']);
        } else {
            return json(['code' => '0', 'msg' => '已经是最新版本']);
        }
    }
}
