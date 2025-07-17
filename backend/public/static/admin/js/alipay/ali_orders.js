define(["jquery", "easy-admin"], function ($, ea) {
    var form = layui.form;
    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'alipay.ali_orders/index',
        add_url: 'alipay.ali_orders/add',
        edit_url: 'alipay.ali_orders/edit',
        delete_url: 'alipay.ali_orders/delete',
        export_url: 'alipay.ali_orders/export',
        modify_url: 'alipay.ali_orders/modify',
    };
    function renderSelect(ele, code, edit) {
        if (code) {
            ele.length = 0;
        }
        ea.request.post({
            url: '/admin/alipay.ali_orders/alipaymccC2',
            data: { c2: code }
        }, function (res) {
            if (res.code == 1) {
                // ea.msg.success(res.msg, function () {
                // })
                if (res.data) {
                    let data = res.data
                    data.forEach((item, index) => {
                        var option = new Option(item.name, item.code);
                        ele.append(option);
                    });
                    //渲染一下表单
                    if (edit) {
                        $('#mcc_code2').val(mcc_code2);
                    }
                    form.render();
                } else {
                    $('.mcc_code2').empty()
                    form.render('select');
                    ea.msg.error('错误', function () {
                    })
                }
            } else {
                $('.mcc_code2').empty()
                form.render('select');
                ea.msg.error(res.msg, function () {
                })
            }
        });
    }
    function getregion(pid, cb) {
        ea.request.post({
            url: '/admin/alipay.ali_orders/getRegion',
            data: { pid: pid }
        }, function (res) {
            if (res.code == 1) {
                cb(res.data)
                // data.forEach((item, index) => {
                //     var option = new Option(item.name, item.code);
                //     ele.append(option);
                // });
            }
        });
    }

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                toolbar: [
                    'refresh',
                    'delete',
                    'export'
                ],
                // 1 未支付
                // 2 待创建事务
                // 3: 事务创建成功
                // 4: 签约成功
                // 5: 已提交事务
                // 6: 审核中
                // 7: 商户已拒绝
                // 8: 等待商家签约
                // 9: --
                cols: [[
                    { type: 'checkbox' },
                    { field: 'id', title: 'id' },
                    { field: 'order_id', title: '订单号' },
                    { field: 'user.nickname', title: '用户' },
                    { field: 'pay_type', search: 'select', selectList: { "1": "平台", "2": "微信", "3": "卡密" }, title: '支付类型' },
                    { field: 'num', title: '金额' },
                    {
                        field: 'status', search: 'select', selectList: {
                            "1": "未支付", "2": "待创建事务", "3": "事务创建成功",
                            "4": "签约成功", "5": "已提交事务", "6": "审核中",
                            "7": "商户已拒绝", "8": "等待商家签约", "9": "其他",
                        }, title: '订单状态'
                    },
                    {
                        field: 'info_id',
                        title: '订单详情',
                        search: false,
                        templet: function (d) {
                            return '<a class="layui-btn layui-btn-sm layui-bg-blue" href="javaScript:void(0);" data-open="alipay.ali_orders/detail?id=' + d.id + '">详情</a>'
                        }
                    },
                    { field: 'sub_msg', title: '错误信息' },
                    { field: 'create_time', title: '创建时间' },
                    { field: 'retail_status', title: '分销-打款状态', selectList: { "0": "无", "1": "成功", "2": "失败" } },
                    { field: 'retail_num', title: '分销-佣金' },
                    { field: 'retail_time', title: '分销-打款时间' },
                    { field: 'applyment_id', title: '微信支付申请单号' },
                    {
                        width: 250,
                        title: '操作',
                        templet: function (d) {
                            var str = ''
                            if (d.status < 6) {
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-open="alipay.ali_orders/edit?id=' + d.id + '"> 编辑</a>'
                            }
                            if (d.status == 2) {
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="alipay.ali_orders/ok?id=' + d.id + '"> 创建事务</a>'
                            }
                            if (d.status == 3) {
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="alipay.ali_orders/ok?id=' + d.id + '"> 提交签约</a>'
                            }
                            if (d.status == 4) {
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="alipay.ali_orders/ok?id=' + d.id + '"> 提交信息确认</a>'
                            }
                            if (d.status == 5 || d.status == 6) {
                                str += '<a class="layui-btn layui-bg-blue layui-btn-sm" href="javaScript:void(0)" data-request="alipay.ali_orders/checkRes?id=' + d.id + '"> 查询结果</a>'
                            }
                            str += '<a class="layui-btn layui-bg-red layui-btn-sm" href="javaScript:void(0)" data-request="alipay.ali_orders/delete?id=' + d.id + '"> 删除</a>'
                            return str
                        }
                    },
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
        setConfig: function () {
            ea.listen();
        },
        incomingParts: function () {
            var upload = layui.upload;

            getregion(1, function (data) {
                console.log('data', data)
                data.forEach((item, index) => {
                    var option = new Option(item.title, item.region);
                    $('.province_code').append(option);
                });
                form.render();
            })

            form.on('select(province_code)', function (data) {
                getregion(data.value, function (data) {
                    console.log('data', data)
                    $('.city_code').empty()
                    $('.district_code').empty()
                    data.forEach((item, index) => {
                        var option = new Option(item.title, item.region);
                        $('.city_code').append(option);
                    });
                    form.render();
                })
            });

            form.on('select(city_code)', function (data) {
                getregion(data.value, function (data) {
                    console.log('data', data)
                    $('.district_code').empty()
                    data.forEach((item, index) => {
                        var option = new Option(item.title, item.region);
                        $('.district_code').append(option);
                    });
                    form.render();
                })
            });

            // upload.render({
            //     accept: 'file',
            //     elem: '#specialLicensePic_class' //绑定元素
            //     , url: '/ajax/uploadLocal' //上传接口
            //     ,
            //     done: function (res) {
            //         if (res.msg == 'ok') {
            //             $('.specialLicensePic_class').val(res.data)
            //         }
            //         layer.alert(res.msg);
            //         //上传完毕回调
            //     }
            //     , error: function (e) {
            //         layer.alert('上传txt 错误');
            //         //请求异常回调
            //     }
            // });
            upload.render({
                elem: '#business_license_pic', //绑定元素
                url: '/admin/ajax/uploadLocalAliBaiduapi', //上传接口
                done: function (res) {
                    if (res.code) {
                        layer.msg(res.msg || res.message, {
                            icon: 1
                        })
                        $('.business_license_pic').attr('src', res.data.url)
                        $('input[name=business_license_no]').val(res.data.code)
                        form.render()
                    } else {
                        layer.msg(res.msg || res.message, {
                            icon: 2
                        })
                    }
                },
                error: function (err) { },

            });


            form.on('select(mcc_code1)', function (data) {
                if (data.value) {
                    renderSelect($('.mcc_code2')[0], data.value);
                } else {
                    $('.mcc_code2').empty()
                    form.render('select');
                }
            });
            ea.listen();
        },
        detail: function () {
            var upload = layui.upload;
            upload.render({
                elem: '#business_license_pic', //绑定元素
                url: '/admin/ajax/uploadLocalAliBaiduapi', //上传接口
                done: function (res) {
                    if (res.code) {
                        layer.msg(res.msg || res.message, {
                            icon: 1
                        })
                        $('.business_license_pic').attr('src', res.data.url)
                        $('input[name=business_license_no]').val(res.data.code)
                        form.render()
                    } else {
                        layer.msg(res.msg || res.message, {
                            icon: 2
                        })
                    }
                },
                error: function (err) { },

            });








            if (mcc_code1) {
                renderSelect($('.mcc_code2')[0], mcc_code1, 'edit');
            }
            form.on('select(mcc_code1)', function (data) {
                if (data.value) {
                    renderSelect($('.mcc_code2')[0], data.value);
                } else {
                    $('.mcc_code2').empty()
                    form.render('select');
                }
            });


            getregion(1, function (data) {
                // order
                data.forEach((item, index) => {
                    var option = new Option(item.title, item.region);
                    $('.province_code').append(option);
                });
                if (def_province_code) {
                    $('.province_code').val(def_province_code)
                }
                form.render();
            })
            if (def_city_code) {
                getregion(def_province_code, function (data) {
                    // order
                    data.forEach((item, index) => {
                        var option = new Option(item.title, item.region);
                        $('.city_code').append(option);
                    });
                    $('.city_code').val(def_city_code)
                    form.render();
                })
            }
            if (def_district_code) {
                getregion(def_city_code, function (data) {
                    // order
                    data.forEach((item, index) => {
                        var option = new Option(item.title, item.region);
                        $('.district_code').append(option);
                    });
                    $('.district_code').val(def_district_code)
                    form.render();
                })
            }

            form.on('select(province_code)', function (data) {
                getregion(data.value, function (data) {
                    console.log('data', data)
                    $('.city_code').empty()
                    $('.district_code').empty()
                    data.forEach((item, index) => {
                        var option = new Option(item.title, item.region);
                        $('.city_code').append(option);
                    });
                    if (def_city_code) {
                        $('.city_code').val(def_city_code)
                    }
                    form.render();
                })
            });

            form.on('select(city_code)', function (data) {
                getregion(data.value, function (data) {
                    console.log('data', data)
                    $('.district_code').empty()
                    data.forEach((item, index) => {
                        var option = new Option(item.title, item.region);
                        $('.district_code').append(option);
                    });
                    if (def_district_code) {
                        $('.district_code').val(def_district_code)
                    }
                    form.render();
                })
            });
            ea.listen()
        },
    };
    return Controller;
});