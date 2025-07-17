define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'alipay.ali_incoming_parts/index',
        add_url: 'alipay.ali_incoming_parts/add',
        edit_url: 'alipay.ali_incoming_parts/edit',
        delete_url: 'alipay.ali_incoming_parts/delete',
        export_url: 'alipay.ali_incoming_parts/export',
        modify_url: 'alipay.ali_incoming_parts/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    { type: 'checkbox' },
                    { field: 'id', title: '序号' },
                    { field: 'rate', title: '费率(%)' },
                    { field: 'cost', title: '费用' },
                    { field: 'retail_num', title: '分销金额' },
                    { field: 'create_time', title: '创建时间' },
                    { width: 250, title: '操作', templet: ea.table.tool },
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