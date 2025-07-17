<?php
// +----------------------------------------------------------------------
// | 路由设置
// +----------------------------------------------------------------------

return [
    // 是否强制使用路由
    'url_route_must'        => true,
    // 路由是否完全匹配
    'route_complete_match'  => true,
    // 访问控制器层名称
    'controller_layer'      => 'controller',
    // 空控制器名
    'empty_controller'      => 'Error',
    // 是否使用控制器后缀
    'controller_suffix'     => false,
    // 默认的路由变量规则
    'default_route_pattern' => '[\w\.]+',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'         => false,
    // 默认控制器名
    'default_controller'    => 'Index',
    // 默认操作名
    'default_action'        => 'index',
    'default_jsonp_handler' => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'     => 'callback',
];
