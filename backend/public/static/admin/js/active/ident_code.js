define(["jquery", "easy-admin"], function($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'active.ident_code/index?ident_id=' + ident_id,
        add_url: 'active.ident_code/add',
        edit_url: 'active.ident_code/edit',
        delete_url: 'active.ident_code/delete',
        export_url: 'active.ident_code/export?ident_id=' + ident_id,
        modify_url: 'active.ident_code/modify',
    };

    var Controller = {

        index: function() {
            ea.table.render({
                toolbar: ['refresh',
                    'export'
                ],
                init: init,
                cols: [
                    [
                        { type: 'checkbox' },
                        { field: 'id', title: 'id' },
                        { field: 'user_id', title: '使用者' },
                        { field: 'code', title: '激活码' },
                        {
                            field: 'status',
                            search: 'select',
                            selectList: ["未使用", "已使用"],
                            title: '状态',
                            templet: function(d) {
                                if (d.status) {
                                    return `<div class='layui-bg-green'>已使用</div>`
                                } else {
                                    return `<div class='layui-bg-red'>未使用</div>`
                                }
                            }
                        },
                        { field: 'create_time', title: '创建时间' },
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