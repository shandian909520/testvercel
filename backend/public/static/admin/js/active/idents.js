define(["jquery", "easy-admin"], function($, ea) {
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'active.idents/index',
        add_url: 'active.idents/add',
        edit_url: 'active.idents/edit',
        delete_url: 'active.idents/delete',
        export_url: 'active.idents/export',
        modify_url: 'active.idents/modify',
    };

    var Controller = {

        index: function() {
            ea.table.render({
                init: init,
                cols: [
                    [
                        { type: 'checkbox' },
                        { field: 'id', title: 'id' },
                        { field: 'name', title: '名称' },
                        { field: 'ident', title: '标识' },
                        { field: 'num', title: '总数量', search: false },
                        { field: 'use_num', title: '已使用数量', search: false },
                        {
                            title: '激活码列表',
                            search: false,
                            templet: `<div><button class='layui-btn layui-btn-sm' data-open='active.ident_code/index?ident_id={{d.id}}' data-full='true')'>打开</button></div>`
                        },
                        { field: 'create_time', search: 'range', title: '创建时间' },
                        {
                            width: 100,
                            title: '操作',
                            templet: ea.table.tool,
                            operat: [
                                'delete'
                            ]
                        },
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