define(["jquery", "easy-admin"], function($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'users/index',
        add_url: 'users/add',
        edit_url: 'users/edit',
        delete_url: 'users/delete',
        export_url: 'users/export',
        modify_url: 'users/modify',
        // order_url: ''
    };

    var Controller = {

        index: function() {
            ea.table.render({
                init: init,
                cols: [
                    [
                        { type: 'checkbox' },
                        { field: 'id', title: 'id' },
                        { field: 'type', title: '来源', selectList: ['无', '小程序', '公众号'], search: 'select' },
                        { field: 'open_id', title: 'openid' },
                        { field: 'head', title: '头像', templet: ea.table.image },
                        { field: 'nickname', title: '昵称' },
                        { field: 'is_black', search: 'select', selectList: ["否", "是"], title: '黑名单', templet: ea.table.switch },
                        { field: 'create_time', title: '创建时间', search: 'range' },
                        {
                            width: 250,
                            title: '操作',
                            templet: function(d) {
                                let str = ''
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="users/edit?id=' + d.id + '"> 编辑</a>'
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="orders/index?user_id=' + d.id + '"> 查看订单</a>'
                                str += '<a class="layui-btn layui-bg-red layui-btn-sm" href="javaScript:void(0)" data-request="users/delete?id=' + d.id + '"> 删除</a>'
                                return str;
                            }

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