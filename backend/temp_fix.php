<?php
// 临时修复脚本 - 仅用于测试
require_once 'vendor/autoload.php';

// 初始化应用
$app = new \think\App();
$app->initialize();

use think\facade\Cache;

echo "=== 临时修复脚本 ===\n";
echo "注意: 这只是临时解决方案，根本解决方案是修复微信第三方平台配置\n\n";

$pf_id = 1;

// 检查当前状态
$ticket = Cache::get('ComponentVerifyTicket' . $pf_id);
echo "当前 ComponentVerifyTicket 状态: " . ($ticket ? '存在' : '缺失') . "\n";

if (!$ticket) {
    echo "\n由于 ComponentVerifyTicket 缺失，系统无法正常工作。\n";
    echo "请按以下步骤解决：\n\n";
    
    echo "1. 登录微信开放平台 (https://open.weixin.qq.com/)\n";
    echo "2. 进入你的第三方平台应用\n";
    echo "3. 检查开发资料配置:\n";
    echo "   - 授权事件接收URL: https://renzheng.2vq.cn/sapi/authorize/callback/pf_id/1\n";
    echo "   - 消息与事件接收URL: https://renzheng.2vq.cn/sapi/events/callback/\$APPID\$/pf_id/1\n";
    echo "   - 消息校验Token: 检查是否与系统配置一致\n";
    echo "   - 消息加解密Key: 检查是否与系统配置一致\n";
    echo "4. 保存配置后，微信会在10分钟内推送 ComponentVerifyTicket\n";
    echo "5. 推送成功后，系统即可正常工作\n\n";
    
    echo "如果配置正确但仍然收不到推送，请检查：\n";
    echo "- 服务器是否可以被外网访问\n";
    echo "- 防火墙是否阻止了微信服务器的访问\n";
    echo "- SSL证书是否正确配置\n";
}

// 显示系统配置信息
echo "\n=== 系统配置信息 ===\n";
try {
    $app_id = sysconfig('app_config', 'app_id' . $pf_id);
    $app_secret = sysconfig('app_config', 'app_secret' . $pf_id);
    $key = sysconfig('app_config', 'key' . $pf_id);
    $token = sysconfig('app_config', 'token' . $pf_id);
    
    echo "第三方平台 APP_ID: " . ($app_id ? $app_id : '未配置') . "\n";
    echo "第三方平台 APP_SECRET: " . ($app_secret ? '已配置' : '未配置') . "\n";
    echo "消息加解密Key: " . ($key ? '已配置' : '未配置') . "\n";
    echo "消息校验Token: " . ($token ? '已配置' : '未配置') . "\n";
    
    if (!$app_id || !$app_secret || !$key || !$token) {
        echo "\n警告: 系统配置不完整，请在后台完善配置\n";
    }
} catch (Exception $e) {
    echo "配置检查失败: " . $e->getMessage() . "\n";
}

echo "\n=== 监控建议 ===\n";
echo "可以通过以下方式监控 ComponentVerifyTicket 接收状态：\n";
echo "1. 查看日志文件: backend/runtime/api/log/\n";
echo "2. 搜索关键词: 'component_verify_ticket' 或 'ComponentVerifyTicket'\n";
echo "3. 正常情况下，每10分钟会有一次推送记录\n"; 