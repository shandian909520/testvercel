define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'orders/index' + (user_id ? '?user_id=' + user_id : ''),
        add_url: 'orders/add',
        select_url: 'order/select',
        edit_url: 'orders/edit',
        delete_url: 'orders/delete',
        export_url: 'orders/export',
        modify_url: 'orders/modify',
        setbetaweappnickname_url: 'orders/setbetaweappnickname',
    };

    var Controller = {

        index: function () {
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
                        { field: 'id', width: 65, title: 'id' },
                        { field: 'user.nickname', title: '用户昵称' },
                        { field: 'info.name', title: '注册名称' },
                        { field: 'order_id', title: '订单编号' },
                        { field: 'pay_type', search: 'select', selectList: { '0': '暂无', "1": "平台", "2": "微信", "3": "卡密" }, title: '订单类型' },
                        { field: 'num', title: '金额', search: false },
                        { field: 'status', search: 'select', selectList: { "1": "未支付", "2": "进行中", "3": "已完成" }, title: '订单状态', templet: ea.table.list },
                        { field: 'info.xcxname', title: '小程序名称' },
                        { field: 'remarks', title: '备注' },
                        {
                            field: 'info_id',
                            title: '订单详情',
                            search: false,
                            templet: function (d) {
                                return '<a class="layui-btn layui-btn-sm layui-bg-blue" href="javaScript:void(0);" data-open="orders/detail?info_id=' + d.info_id + '">详情</a>'
                            }
                        },
                        { field: 'error_msg', width: 100, title: '注册详情', search: false },
                        { field: 'create_time', width: 100, search: 'range', title: '创建时间' },
                        {
                            width: 320,
                            title: '操作',
                            templet: function (d) {
                                var str = ''
                                // if (d.status != 1) {
                                //     str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="orders/select?id=' + d.id + '"> 查询</a>'
                                // }
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="orders/edit?id=' + d.id + '"> 编辑</a>'
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="orders/re_create?id=' + d.id + '"> 再来一单</a>'
                                if (d.status == 1) {
                                    str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="orders/ok?id=' + d.id + '"> 确认付款</a>'
                                }
                                //d.appid
                                if (d.status == 3 && d.error_msg != null && d.error_msg != '' && d.error_msg.indexOf('转正成功') == -1) {
                                    str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="orders/verifybetaweapp?id=' + d.id + '"> 确认转正</a>'
                                }
                                //转正小程序更名
                                if (d.status == 3) {
                                    str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="orders/setbetaweappnickname?id=' + d.info_id + '"> 更名</a>'
                                }

                                str += '<a class="layui-btn layui-bg-red layui-btn-sm" href="javaScript:void(0)" data-request="orders/delete?id=' + d.id + '"> 删除</a>'
                                return str
                            }
                        },
                    ]
                ],
            });

            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
        detail: function () {
            ea.listen()
        },
        setbetaweappnickname: function () {
            ea.listen()
        },
    };
    return Controller;
});