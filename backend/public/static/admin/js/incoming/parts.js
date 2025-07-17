define(["jquery", "easy-admin"], function($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'incoming.parts/index',
        add_url: 'incoming.parts/add',
        edit_url: 'incoming.parts/edit',
        delete_url: 'incoming.parts/delete',
        export_url: 'incoming.parts/export',
        modify_url: 'incoming.parts/modify',
    };

    var Controller = {

        index: function() {
            ea.table.render({
                init: init,
                cols: [
                    [
                        { type: 'checkbox' },
                        { field: 'id', title: '序号' },
                        { field: 'rate', title: '费率(%)' },
                        { field: 'cost', title: '费用' },
                        { field: 'retail_num', title: '分销金额' },
                        { field: 'create_time', title: '创建时间' },
                        { width: 250, title: '操作', templet: ea.table.tool },
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
        config: function() {
            ea.listen()
        }
    };
    return Controller;
});