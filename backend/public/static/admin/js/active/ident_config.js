define(["jquery", "easy-admin", "vue"], function($, ea, Vue) {

    var form = layui.form;

    var Controller = {
        index: function() {


            ea.listen();
        }
    };
    return Controller;
});