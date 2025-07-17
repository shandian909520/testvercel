define(["jquery", "easy-admin", "vue"], function ($, ea, Vue) {

    var form = layui.form;

    var Controller = {
        index: function () {

            var app = new Vue({
                el: '#app',
                data: {}
            });

            layui.use('upload', function () {
                var upload = layui.upload;
                console.log('upload', upload)
                //执行实例
                var uploadInst = upload.render({
                    accept: 'file',
                    exts: 'txt',
                    elem: '#independenttxtbtn' //绑定元素
                    , url: '/ajax/uploadTxt' //上传接口
                    ,
                    done: function (res) {
                        if (res.msg == 'ok') {
                            $('.independenttxtvalue').val(res.data)
                        }
                        layer.alert(res.msg);
                        //上传完毕回调
                        console.log(res)
                    }
                    , error: function (e) {
                        layer.alert('上传txt 错误');
                        console.log(e)
                        //请求异常回调
                    }
                });
            });

            ea.listen();
        }

    };
    return Controller;
});