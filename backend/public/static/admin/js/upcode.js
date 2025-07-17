define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'upcode/index',
        add_url: 'upcode/add',
        edit_url: 'upcode/edit',
        delete_url: 'upcode/delete',
        export_url: 'upcode/export',
        modify_url: 'upcode/modify',
    };

    var Controller = {
        index: function () {
            $('.upcode').on('click', function () {
                ea.msg.confirm('确定上传？', function () {
                    ea.request.post({
                        url: 'xcxUpcode',
                        data: { desc: $('.layui-textarea').val(), version: $('.version').val() }
                    }, function (res) {
                        // ea.msg.success(res.msg, function () {
                        //     renderTable();
                        // });
                        if (res.code == 1) {
                            ea.msg.success(res.msg, function () {
                            })
                        } else {
                            ea.msg.error(res.msg, function () {
                            })
                        }
                    });
                });
            });

            var upload = layui.upload;
            //执行实例
            var uploadInst = upload.render({
                accept: 'file',
                exts: 'key',
                elem: '#push_app_upsecret' //绑定元素
                , url: '/ajax/uploadLocal' //上传接口
                ,
                done: function (res) {
                    if (res.msg == 'ok') {
                        $('.push_app_upsecret_class').val(res.data)
                    }
                    layer.alert(res.msg);
                    //上传完毕回调
                }
                , error: function (e) {
                    layer.alert('上传txt 错误');
                    //请求异常回调
                }
            });

            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
    };
    return Controller;
});