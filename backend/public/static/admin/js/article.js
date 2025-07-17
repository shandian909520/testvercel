define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'article/index',
        add_url: 'article/add',
        edit_url: 'article/edit',
        delete_url: 'article/delete',
        export_url: 'article/export',
        modify_url: 'article/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},                    {field: 'id', title: 'id'},                    {field: 'sort', title: '排序', edit: 'text'},                    {field: 'title', title: '标题'},                    {field: 'create_time', title: '创建时间'},                    {field: 'status', search: 'select', selectList: ["禁用","启用"], title: '状态', templet: ea.table.switch},                    {width: 250, title: '操作', templet: ea.table.tool},
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
    };
    return Controller;
});