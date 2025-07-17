define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'in_coming_order/index' + (user_id ? '?user_id=' + user_id : ''),
        add_url: 'in_coming_order/add',
        edit_url: 'in_coming_order/edit',
        delete_url: 'in_coming_order/delete',
        export_url: 'in_coming_order/export',
        modify_url: 'in_coming_order/modify',
    };
    var form = layui.form;
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
                        { field: 'id', title: '序号' },
                        { field: 'user.nickname', title: '用户昵称' },
                        { field: 'order_id', title: '订单编号' },
                        { field: 'orderInfo.merchant_name', title: '商户名称' },
                        { field: 'pay_type', search: 'select', selectList: { '0': '暂无', "1": "平台", "2": "微信", "3": "卡密" }, title: '订单类型' },
                        { field: 'num', title: '金额', search: false },
                        { field: 'status', search: 'select', selectList: { "1": "未支付", "2": "进行中", "3": "已完成", '4': '重新提交' }, title: '订单状态', templet: ea.table.list },
                        {
                            field: 'info_id',
                            title: '订单详情',
                            search: false,
                            templet: function (d) {
                                return '<a class="layui-btn layui-btn-sm layui-bg-blue" href="javaScript:void(0);" data-open="in_coming_order/detail?id=' + d.id + '">详情</a>'
                            }
                        },
                        { field: 'error_msg', title: '申请描述', search: false },
                        { field: 'create_time', title: '创建时间', search: 'range' },
                        {
                            width: 280,
                            title: '操作',
                            templet: function (d) {
                                var str = ''
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="in_coming_order/edit?id=' + d.id + '"> 编辑</a>'
                                if (d.status == 1 || d.status == 4) {
                                    str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="in_coming_order/ok?id=' + d.id + '"> 提交申请</a>'
                                }

                                if(d.status == 2 || d.status == 4){
                                    str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="in_coming_order/selectService?id=' + d.id + '"> 查询</a>'
                                }
                                str += '<a class="layui-btn layui-bg-red layui-btn-sm" href="javaScript:void(0)" data-request="in_coming_order/delete?id=' + d.id + '"> 删除</a>'
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

            layui.use(['layer', 'form'], function () {
                var form = layui.form;
                form.on('checkbox', function (data) {
                    if (data.value == 'SALES_SCENES_MP' && data.elem.checked) {
                        $('.ssm').show()
                    }
                    if (data.value == 'SALES_SCENES_MP' && !data.elem.checked) {
                        $('.ssm').hide()
                    }
                    if (data.value == 'SALES_SCENES_STORE' && data.elem.checked) {
                        $('.sss').show()
                    }
                    if (data.value == 'SALES_SCENES_STORE' && !data.elem.checked) {
                        $('.sss').hide()
                    }
                });
            })
            ea.listen()
        },

    };
    return Controller;
});