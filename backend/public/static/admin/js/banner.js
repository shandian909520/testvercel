define(["jquery", "easy-admin"], function($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'banner/index',
        add_url: 'banner/add',
        edit_url: 'banner/edit',
        delete_url: 'banner/delete',
        export_url: 'banner/export',
        modify_url: 'banner/modify',
        // order_url: ''
    };

    var Controller = {

        index: function() {
            ea.table.render({
                init: init,
                cols: [
                    [
                        { type: 'checkbox' },
                        { field: 'sort', title: '排序' ,search:false},
                        { field: 'img_url', title: 'banner图片', templet: ea.table.image ,search:false},
                        { field: 'type', title: '类型' ,selectList:{1:'小程序',2:'H5',3:'手机号'}},
                        { field: 'urls', title: 'H5/手机号' },
                        { field: 'appid',width:300, title: '小程序appid信息',templet:function (d){
                                var str = "";
                                if( d.appid ){
                                    console.log(d);
                                    str += "<div>APPID: "+d.appid+"</div>";
                                    str += "<div>原始id: "+d.gh_no+"</div>";
                                    str += "<div>路径: "+d.path+"</div>";
                                    str += "<div>参数: "+d.extradata+"</div>";
                                }
                                return str;
                            }
                        },

                        {
                            width: 250,
                            title: '操作',
                            templet: function(d) {
                                let str = ''
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="banner/edit?id=' + d.id + '"> 编辑</a>'
                                str += '<a class="layui-btn layui-bg-red layui-btn-sm" href="javaScript:void(0)" data-request="banner/delete?id=' + d.id + '"> 删除</a>'
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