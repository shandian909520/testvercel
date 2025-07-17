define(["jquery", "easy-admin"], function($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'alipay.ali_orders_dis/index',
        add_url: 'alipay.ali_orders_dis/add',
        edit_url: 'alipay.ali_orders_dis/edit',
        delete_url: 'alipay.ali_orders_dis/delete',
        export_url: 'alipay.ali_orders_dis/export',
        modify_url: 'alipay.ali_orders_dis/modify',
    };

    var Controller = {

        index: function() {
            ea.table.render({
                init: init,
                toolbar: [
                    'refresh',
                    'delete',
                    'export'
                ],
                cols: [
                    [
                        { type: 'checkbox' },
                        { field: 'id', title: 'id' },
                        { field: 'user.nickname', title: '付款人' },
                        { field: 'puser', title: '推荐人' },
                        { field: 'order_id', title: '订单号' },
                        { field: 'retail_num', title: '佣金', search: false },
                        { field: 'retail_status', search: 'select', selectList: { '0': '无', "1": "成功", "2": "失败", }, title: '打款状态', templet: ea.table.list },
                        { field: 'retail_time', title: '打款时间' },
                        {
                            width: 250,
                            title: '操作',
                            templet: function(d) {
                                if (d.retail_status == 0) {
                                    return '<a href="javaScript:void(0);" class="layui-btn layui-btn-sm layui-bg-blue" data-request="alipay.ali_orders_dis/payment?id=' + d.id + '">打款</a>'
                                }
                                if (d.retail_status == 2) {
                                    return '<a href="javaScript:void(0);" class="layui-btn layui-btn-sm layui-bg-red" data-request="alipay.ali_orders_dis/payment?id=' + d.id + ' ">重新打款</a>'
                                }
                                return '<div></div>'
                            },

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
        detail: function() {
            ea.listen()
        },
        config: function() {
            ea.listen()
        }
    };
    return Controller;
});