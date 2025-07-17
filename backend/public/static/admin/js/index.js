define(["jquery", "easy-admin", "echarts", "echarts-theme", "miniAdmin", "miniTab",], function ($, ea, echarts, undefined, miniAdmin, miniTab) {

    var form = layui.form;
    var Controller = {
        index: function () {
            var options = {
                iniUrl: ea.url('ajax/initAdmin'), // 初始化接口
                clearUrl: ea.url("ajax/clearCache"), // 缓存清理接口
                urlHashLocation: true, // 是否打开hash定位
                bgColorDefault: false, // 主题默认配置
                multiModule: true, // 是否开启多模块
                menuChildOpen: false, // 是否默认展开菜单
                loadingTime: 0, // 初始化加载时间
                pageAnim: true, // iframe窗口动画
                maxTabNum: 20, // 最大的tab打开数量
            };
            miniAdmin.render(options);

            $('.login-out').on("click", function () {
                ea.request.get({
                    url: 'login/out',
                    prefix: true,
                }, function (res) {
                    ea.msg.success(res.msg, function () {
                        window.location = ea.url('login/index');
                    })
                });
            });
        },
        welcome: function () {
            ea.listen();
        },
        check_name: function () {
            ea.listen()
        },
        xcx_register: function () {

            ea.listen()
        },
        editAdmin: function () {
            ea.listen();
        },
        editPassword: function () {
            ea.listen();
        },
        repass: function () {
            form.verify({
                confirmPass: function (val) {
                    if ($('input[name=password]').val() !== val) {
                        return '两次密码不一致'
                    }
                }
            })
            ea.listen(function (data) {
                data['keep_login'] = $('.icon-nocheck').hasClass('icon-check') ? 1 : 0;
                return data;
            }, function (res) {
                ea.msg.success(res.msg, function () {
                    window.location = ea.url('login');
                })
            }, function (res) {
                ea.msg.error(res.msg, function () {
                    $('#refreshCaptcha').trigger("click");
                });
            })
        }
    };
    return Controller;
});