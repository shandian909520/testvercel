define(["jquery", "easy-admin", "vue"], function ($, ea, Vue) {

    var form = layui.form;

    var Controller = {
        index: function () {
            var app = new Vue({
                el: '#app',
                data: {
                    upload_type: upload_type
                }
            });

            form.on("radio(upload_type)", function (data) {
                app.upload_type = this.value;
            });


            layui.use('upload', function () {
                var upload = layui.upload;
                //执行实例
                var uploadInst = upload.render({
                    accept: 'file',
                    exts: 'pem',
                    elem: '#wx_key_pem' //绑定元素
                    , url: '/ajax/uploadLocal' //上传接口
                    ,
                    done: function (res) {
                        if (res.msg == 'ok') {
                            $('.wx_key_pem_class').val(res.data)
                        }
                        layer.alert(res.msg);
                        //上传完毕回调
                    }
                    , error: function (e) {
                        layer.alert('上传txt 错误');
                        //请求异常回调
                    }
                });
                var uploadInst = upload.render({
                    accept: 'file',
                    exts: 'pem',
                    elem: '#wx_cert_pem' //绑定元素
                    , url: '/ajax/uploadLocal' //上传接口
                    ,
                    done: function (res) {
                        if (res.msg == 'ok') {
                            $('.wx_cert_pem_class').val(res.data)
                        }
                        layer.alert(res.msg);
                        //上传完毕回调
                    }
                    , error: function (e) {
                        layer.alert('上传txt 错误');
                        //请求异常回调
                    }
                });

                var uploadInst = upload.render({
                    accept: 'file',
                    exts: 'pem',
                    elem: '#wx_mp_key_pem' //绑定元素
                    , url: '/ajax/uploadLocal' //上传接口
                    ,
                    done: function (res) {
                        if (res.msg == 'ok') {
                            $('.wx_mp_key_pem_class').val(res.data)
                        }
                        layer.alert(res.msg);
                        //上传完毕回调
                    }
                    , error: function (e) {
                        layer.alert('上传txt 错误');
                        //请求异常回调
                    }
                });
                var uploadInst = upload.render({
                    accept: 'file',
                    exts: 'pem',
                    elem: '#wx_mp_cert_pem' //绑定元素
                    , url: '/ajax/uploadLocal' //上传接口
                    ,
                    done: function (res) {
                        if (res.msg == 'ok') {
                            $('.wx_mp_cert_pem_class').val(res.data)
                        }
                        layer.alert(res.msg);
                        //上传完毕回调
                    }
                    , error: function (e) {
                        layer.alert('上传txt 错误');
                        //请求异常回调
                    }
                });
            });
            ea.listen();
        }
    };
    return Controller;
});