define(["jquery", "easy-admin"], function($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'xcx.app_project/index',
        add_url: 'xcx.app_project/add',
        edit_url: 'xcx.app_project/edit',
        delete_url: 'xcx.app_project/delete',
        export_url: 'xcx.app_project/export',
        modify_url: 'xcx.app_project/modify',
    };

    var Controller = {

        index: function() {
            ea.table.render({
                init: init,
                toolbar: ['refresh', [{
                        text: '添加',
                        url: init.add_url,
                        method: 'open',
                        auth: 'add',
                        class: 'layui-btn layui-btn-normal layui-btn-sm',
                        icon: 'fa fa-plus ',
                        extend: 'data-full="true"',
                    }],
                    'delete', 'export'
                ],
                cols: [
                    [
                        { type: "checkbox" },
                        { field: 'id', width: 80, title: 'ID' },
                        { field: 'app_id', width: 100, title: 'appId' },
                        { field: 'app_secret', width: 100, title: 'appSecret' },
                        { field: 'status', title: '状态', width: 85, selectList: { 0: '禁用', 1: '启用' }, templet: ea.table.switch },
                        { field: 'create_time', minWidth: 80, title: '创建时间', search: 'range' },
                        {
                            width: 250,
                            title: '操作',
                            templet: ea.table.tool,
                            operat: [
                                [{
                                    text: '编辑',
                                    url: init.edit_url,
                                    method: 'open',
                                    auth: 'edit',
                                    class: 'layui-btn layui-btn-xs layui-btn-success',
                                    extend: 'data-full="true"',
                                }, ],
                                'delete'
                            ]
                        }
                    ]
                ],
            });

            ea.listen();
        },
        add: function() {
            ea.listen();
        },
        edit: function() {
            ea.listen();
        },

    };
    return Controller;
});