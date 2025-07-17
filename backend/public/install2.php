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
//检测php版本 7.2以上
//echo PHP_OS ;DIE;
//检测 文件夹是否可写 /public  /runtime
//检测curl

ini_set('display_errors', 'On');
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use think\facade\Db;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/topthink/framework/src/helper.php';

define('DS', DIRECTORY_SEPARATOR);
define('ROOT_PATH', __DIR__ . DS . '..' . DS);
define('INSTALL_PATH', ROOT_PATH . 'config' . DS . 'install' . DS);
define('CONFIG_PATH', ROOT_PATH . 'config' . DS);

$currentHost = ($_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . '/';


function checkConnect()
{
    try {
        Db::query("select version()");
    } catch (\Exception $e) {
        return false;
    }
    return true;
}

function checkDatabase($database)
{
    $check = Db::query("SELECT * FROM information_schema.schemata WHERE schema_name='{$database}'");
    if (empty($check)) {
        return false;
    } else {
        return true;
    }
}

function createDatabase($database)
{
    try {
        Db::execute("CREATE DATABASE IF NOT EXISTS `{$database}` DEFAULT CHARACTER SET utf8");
    } catch (\Exception $e) {
        return false;
    }
    return true;
}

function parseSql($sql = '', $to, $from)
{
    list($pure_sql, $comment) = [[], false];
    $sql = explode("\n", trim(str_replace(["\r\n", "\r"], "\n", $sql)));
    foreach ($sql as $key => $line) {
        if ($line == '') {
            continue;
        }
        if (preg_match("/^(#|--)/", $line)) {
            continue;
        }
        if (preg_match("/^\/\*(.*?)\*\//", $line)) {
            continue;
        }
        if (substr($line, 0, 2) == '/*') {
            $comment = true;
            continue;
        }
        if (substr($line, -2) == '*/') {
            $comment = false;
            continue;
        }
        if ($comment) {
            continue;
        }
        if ($from != '') {
            $line = str_replace('`' . $from, '`' . $to, $line);
        }
        if ($line == 'BEGIN;' || $line == 'COMMIT;') {
            continue;
        }
        array_push($pure_sql, $line);
    }
    //$pure_sql = implode($pure_sql, "\n");
    $pure_sql = implode("\n", $pure_sql);
    $pure_sql = explode(";\n", $pure_sql);
    return $pure_sql;
}

function install($username, $password, $config, $adminUrl)
{
    $sqlPath = file_get_contents(INSTALL_PATH . 'sql' . DS . 'install.sql');
    $sqlArray = parseSql($sqlPath, $config['prefix'], 'ea_');
    Db::startTrans();
    try {
        foreach ($sqlArray as $vo) {
            Db::connect('install')->execute($vo);
        }


        // 处理安装文件
        !is_dir(INSTALL_PATH) && @mkdir(INSTALL_PATH);
        !is_dir(INSTALL_PATH . 'lock' . DS) && @mkdir(INSTALL_PATH . 'lock' . DS);

        @file_put_contents(CONFIG_PATH . 'app.php', getAppConfig('admin'));
        @file_put_contents(CONFIG_PATH . 'database.php', getDatabaseConfig($config));
        Db::commit();
    } catch (\Exception $e) {
        Db::rollback();
        return $e->getMessage();
    }
    return true;
}

function password($value)
{
    $value = sha1('blog_') . md5($value) . md5('_encrypt') . sha1($value);
    return sha1($value);
}

function getAppConfig($admin)
{
    $config = <<<EOT
<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------

use think\\facade\Env;

return [
    // 应用地址
    'app_host'         => Env::get('app.host', ''),
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否启用事件
    'with_event'       => true,
    // 开启应用快速访问
    'app_express'      => true,
    // 默认应用
    'default_app'      => 'admin',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',
    // 应用映射（自动多应用模式有效）
    'app_map'          => [
        Env::get('easyadmin.admin', '{$admin}') => 'admin',
        'api'=>'api',
        'sapi'=>'api',
    ],
    // 后台别名
    'admin_alias_name' => Env::get('easyadmin.admin', '{$admin}'),
    // 域名绑定（自动多应用模式有效）
    'domain_bind'      => [],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => ['common'],
    // 异常页面的模板文件
    'exception_tmpl'   => Env::get('app_debug') == 1 ? app()->getThinkPath() . 'tpl/think_exception.tpl' : app()->getBasePath() . 'common' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'think_exception.tpl',
    // 跳转页面的成功模板文件
    'dispatch_success_tmpl'   => app()->getBasePath() . 'common' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
    // 跳转页面的失败模板文件
    'dispatch_error_tmpl'   => app()->getBasePath() . 'common' . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR . 'dispatch_jump.tpl',
    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
    // 静态资源上传到OSS前缀
    'oss_static_prefix'   => Env::get('easyadmin.oss_static_prefix', 'static_easyadmin'),
];

EOT;
    return $config;
}

function getDatabaseConfig($data)
{
    $config = <<<EOT
<?php
use think\\facade\Env;

return [
    // 默认使用的数据库连接配置
    'default'         => Env::get('database.driver', 'mysql'),

    // 自定义时间查询规则
    'time_query_rule' => [],

    // 自动写入时间戳字段
    // true为自动识别类型 false关闭
    // 字符串则明确指定时间字段类型 支持 int timestamp datetime date
    'auto_timestamp'  => true,

    // 时间字段取出后的默认时间格式
    'datetime_format' => 'Y-m-d H:i:s',

    // 数据库连接配置信息
    'connections'     => [
        'mysql' => [
            // 数据库类型
            'type'              => Env::get('database.type', 'mysql'),
            // 服务器地址
            'hostname'          => Env::get('database.hostname', '{$data['hostname']}'),
            // 数据库名
            'database'          => Env::get('database.database', '{$data['database']}'),
            // 用户名
            'username'          => Env::get('database.username', '{$data['username']}'),
            // 密码
            'password'          => Env::get('database.password', '{$data['password']}'),
            // 端口
            'hostport'          => Env::get('database.hostport', '{$data['hostport']}'),
            // 数据库连接参数
            'params'            => [],
            // 数据库编码默认采用utf8
            'charset'           => Env::get('database.charset', 'utf8'),
            // 数据库表前缀
            'prefix'            => Env::get('database.prefix', '{$data['prefix']}'),

            // 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
            'deploy'            => 0,
            // 数据库读写是否分离 主从式有效
            'rw_separate'       => false,
            // 读写分离后 主服务器数量
            'master_num'        => 1,
            // 指定从服务器序号
            'slave_no'          => '',
            // 是否严格检查字段是否存在
            'fields_strict'     => true,
            // 是否需要断线重连
            'break_reconnect'   => false,
            // 监听SQL
            'trigger_sql'       => true,
            // 开启字段缓存
            'fields_cache'      => false,
            // 字段缓存路径
            'schema_cache_path' => app()->getRuntimePath() . 'schema' . DIRECTORY_SEPARATOR,
        ],

        // 更多的数据库配置信息
    ],
];

EOT;
    return $config;
}

$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
if ($step == 1) {

    $curl_status = extension_loaded('curl');
    //检测pdo_mysql
    $pdo_mysql_status = extension_loaded('pdo_mysql');

    //  /public 文件夹可写检测
    $public_write_status  = is_writable(dirname(__DIR__) . '/public');
    $runtime_write_status = is_writable(dirname(__DIR__) . '/runtime');
}
if ($step == 3) {
    $host   = trim($_REQUEST['host']);
    $database = trim($_REQUEST['dbname']);
    $dbuser = trim($_REQUEST['dbuser']);
    $dbpass = trim($_REQUEST['dbpass']);

    // DB类初始化
    $config = [
        'type'     => 'mysql',
        'hostname' => $host,
        'username' => $dbuser,
        'password' => $dbpass,
        'hostport' => 3306,
        'charset'  => 'utf8',
        'prefix'   => 'ea_',
        'debug'    => true,
    ];
    file_put_contents('./install.txt', json_encode(array_merge($config, ['database' => $database])));
    Db::setConfig([
        'default'     => 'mysql',
        'connections' => [
            'mysql'   => $config,
            'install' => array_merge($config, ['database' => $database]),
        ],
    ]);

    // 检测数据库连接
    if (!checkConnect()) {
        exit("<div><p>数据库连接失败!</p><a href='/install2.php?step=2'>返回</a></div>");
    }

    // 创建数据库
    createDatabase($database);
    // 导入sql语句等等
    $install = install($username, $password, array_merge($config, ['database' => $database]), $adminUrl);
    if ($install !== true) {
        exit("<div><p>'系统安装失败：' . $install.'</p><a href='/install2.php?step=2'>返回</a></div>");
    }
    sleep(1);
    header('location:/install2.php?step=4');
    exit;
}


if ($step == 99) {
    $username = trim($_REQUEST['username']);
    $password = trim($_REQUEST['password']);
    $password2 = trim($_REQUEST['password2']);
    if (empty($username) || empty($password) || empty($password2)) {
        echo "<div><p>请填写完整</p><a href='/install2.php?step=4'>返回</a></div>";
        die;
    }
    if ($password2 != $password) {
        echo "<div><p>两次密码不对</p><a href='/install2.php?step=4'>返回</a></div>";
        die;
    }
    $config = json_decode(file_get_contents('install.txt'), true);
    Db::setConfig([
        'default'     => 'mysql',
        'connections' => [
            'mysql'   => $config,
            'install' => $config,
        ],
    ]);
    Db::connect('install')
        ->name('system_admin')
        ->where('id', 1)
        ->delete();
    Db::connect('install')
        ->name('system_admin')
        ->insert([
            'id'          => 1,
            'username'    => $username,
            'head_img'    => '/static/admin/images/head.jpg',
            'password'    => password($password),
            'create_time' => time(),
        ]);
    @unlink('install.txt');
    @file_put_contents(INSTALL_PATH . 'lock' . DS . 'install.lock', date('Y-m-d H:i:s'));
    header('location:/install2.php?step=5');
    exit;
}

?>

<!DOCTYPE html>
<html lang="zh">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/install/css/dlyz.css">
    <title>安装检测</title>
</head>

<body>
    <div id="zt">
        <div class="left">
            <div class="xcjt">
                <!-- <img src="/install/images/xcjt.png">
            <div>
                <p class="p1">信巢集团</p>
                <p class="p2">
                    &nbsp;XIN&nbsp;&nbsp;&nbsp;
                    CHAO&nbsp;&nbsp;&nbsp;
                    JI&nbsp;&nbsp;&nbsp;
                    TUAN</p>
            </div> -->
                <img src="/install/images/xclogo.png" style="width: 12vw;height: auto;left: -1vw;position: relative;">
            </div>
            <div class="leftx">
                <div class="ljdt" style="top: 8.5vw;">
                    <div class="ljdtjd"></div>
                </div>
                <div class="left3 <?php if ($step == 1) { ?>jd<?php } ?>">
                    <img src="/install/images/wwc.png" class="i1"><img src="/install/images/ywc.png" class="i2">
                    环境监测
                </div>
                <div class="left4 <?php if ($step == 2) { ?>jd<?php } ?>">
                    <img src="/install/images/wwc.png" class="i1"><img src="/install/images/ywc.png" class="i2">
                    数据参数
                </div>
                <div class="left6 <?php if ($step == 4) { ?>jd<?php } ?>">
                    <img src="/install/images/wwc.png" class="i1"><img src="/install/images/ywc.png" class="i2">
                    登录设置
                </div>
                <div class="left7 <?php if ($step == 5) { ?>jd<?php } ?>">
                    <img src="/install/images/wwc.png" class="i1"><img src="/install/images/ywc.png" class="i2">
                    安装完成
                </div>
            </div>
        </div>
        <div class="right">
            <div id="ceng" style="display: none;"></div>
            <?php if ($step == 1) { ?>
                <div class="hjjc" style="display: flex;">
                    <img src="/install/images/hjjc.png">
                    <div class="jccw" style="">
                        <div class="jc1">
                            <p>1. 是否符合linux系统</p>
                            <div>
                                <?php if (PHP_OS != 'Linux') { ?>
                                    <img src="/install/images/false.png">
                                    <p class="cw">不符合</p>
                                <?php } else { ?>
                                    <img src="/install/images/true.png">
                                    <p class="zq">符合</p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="jc2">
                            <p>2. php版本是否为7.3</p>
                            <div>
                                <?php if (PHP_VERSION >= '7.3.0') { ?>
                                    <img src="/install/images/true.png">
                                    <p class="zq">符合</p>
                                <?php } else { ?>
                                    <img src="/install/images/false.png">
                                    <p class="cw">不符合</p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="jc2">
                            <p>3. Curl扩展是否开启</p>
                            <div>
                                <?php if ($curl_status) { ?>
                                    <img src="/install/images/true.png">
                                    <p class="zq">符合</p>
                                <?php } else { ?>
                                    <img src="/install/images/false.png">
                                    <p class="cw">不符合</p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="jc2">
                            <p>4. Pdo_mysql扩展</p>
                            <div>
                                <?php if ($pdo_mysql_status) { ?>
                                    <img src="/install/images/true.png">
                                    <p class="zq">符合</p>
                                <?php } else { ?>
                                    <img src="/install/images/false.png">
                                    <p class="cw">不符合</p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="jc2">
                            <p>5. public文件夹可写检测</p>
                            <div>
                                <?php if ($public_write_status) { ?>
                                    <img src="/install/images/true.png">
                                    <p class="zq">可写</p>
                                <?php } else { ?>
                                    <img src="/install/images/false.png">
                                    <p class="cw">不可写</p>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="jc2">
                            <p>6. runtime文件夹可写检测</p>
                            <div>
                                <?php if ($runtime_write_status) { ?>
                                    <img src="/install/images/true.png">
                                    <p class="zq">可写</p>
                                <?php } else { ?>
                                    <img src="/install/images/false.png">
                                    <p class="cw">不可写</p>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="xyb2">
                        <a href="/install2.php?step=2"><button style="cursor: pointer">下一步</button></a>
                    </div>
                </div>
            <?php } ?>

            <?php if ($step == 2) { ?>
                <form action="/install2.php" method="get" onsubmit="return subinstall2()">
                    <div class="sjcs" style="">
                        <img src="/install/images/sjcs.png" alt="">
                        <input type="hidden" name="step" value="3">
                        <div>
                            <p>数据库地址</p>
                            <input type="text" value="127.0.0.1" name="host" class="input6">
                        </div>

                        <div>
                            <p>数据库名称</p>
                            <input type="text" name="dbname" class="input4">
                        </div>

                        <div class="sjcsdiv">
                            <p>数据库账号</p>
                            <input type="text" name="dbuser" class="input3">
                        </div>
                        <div>
                            <p>数据库密码</p>
                            <input type="text" name="dbpass" class="input5">
                        </div>
                        <div class="xyb3">
                            <button type="submit">下一步</button>
                        </div>
                    </div>
                </form>
            <?php } ?>

            <?php if ($step == 4) { ?>
                <form action="/install2.php" method="get">
                    <div class="dlsz">
                        <input type="hidden" name="step" value="99">
                        <img src="/install/images/zh.png" alt="">
                        <div>
                            <p>设置帐号</p>
                            <input type="text" name="username" value="" class="input7">
                        </div>
                        <div>
                            <p>填写密码</p>
                            <input type="password" name="password" value="" class="input8">
                        </div>
                        <div>
                            <p>再次填写密码</p>
                            <input type="password" name="password2" value="" class="input9">
                        </div>
                        <div class="xyb4">
                            <button type="submit">下一步</button>
                        </div>
                    </div>
                </form>

            <?php } ?>

            <?php if ($step == 5) { ?>
                <div class="azcg">
                    <img src="/install/images/wc.png" alt="">
                    <div class="azcgt">安装成功</div>
                    <div class="xyb5">
                        <a href="/" style="cursor: pointer"> <button>进入后台</button></a>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</body>
<script src="/install/js/jquery.min.js"></script>
<script>
    $(function() {


    });
    let sub2 = true

    function subinstall2() {
        if(!sub2){
            return false
        }
        sub2 = false
        return true
    }
</script>

</html>