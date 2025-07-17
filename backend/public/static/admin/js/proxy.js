define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'proxy/index',
        add_url: 'proxy/add',
        edit_url: 'proxy/edit',
        delete_url: 'proxy/delete',
        modify_url: 'proxy/modify',
        export_url: 'proxy/export',
        password_url: 'proxy/password',
    };

    var Controller = {

        index: function () {
            var util = layui.util;
            ea.table.render({
                init: init,
                cols: [[
                    { type: "checkbox" },
                    { field: 'id', width: 80, title: 'ID' },
                    { field: 'uname', minWidth: 80, title: '名称' },
                    { field: 'username', minWidth: 80, title: '账户' },
                    // { field: 'head_img', minWidth: 80, title: '头像', search: false, templet: ea.table.image },
                    // {field: 'phone', minWidth: 80, title: '手机'},
                    // {field: 'login_num', minWidth: 80, title: '登录次数'},
                    // {field: 'remark', minWidth: 80, title: '备注信息'},
                    // {field: 'status', title: '状态', width: 85, search: 'select', selectList: {0: '禁用', 1: '启用'}, templet: ea.table.switch},
                    { field: 'independent', title: '三方开关', width: 85, search: 'select', selectList: { 0: '禁用', 1: '启用' }, templet: ea.table.switch },
                    { field: 'create_time', minWidth: 80, title: '创建时间', search: 'range' },
                    { field: 'end_time', minWidth: 80, title: '到期时间', search: 'time', templet: function (d) { return d.end_time>0?util.toDateString(d.end_time * 1000):'永久'; }, edit: true },
                    {
                        width: 250,
                        title: '操作',
                        templet: ea.table.tool,
                        operat: [
                            'edit',
                            'delete'
                        ]
                    }
                ]],
            });

            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
        password: function () {
            ea.listen();
        }
    };
    return Controller;
});