define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'admin_incoming_parts/index',
        add_url: 'admin_incoming_parts/add',
        edit_url: 'admin_incoming_parts/edit',
        delete_url: 'admin_incoming_parts/delete',
        export_url: 'admin_incoming_parts/export',
        modify_url: 'admin_incoming_parts/modify',
    };
    var form = layui.form;
    var element = layui.element;
    function formcarddate(datestr) {
        if (datestr.length == 8) {
            return datestr.substr(0, 4) + '-' + datestr.substr(4, 2) + '-' + datestr.substr(6)
        } else {
            return ''
        }
    }

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    { type: 'checkbox' },
                    { field: 'id', title: 'id' },
                    { field: 'name', title: 'name' },
                    { field: 'value', title: 'value' },
                    { width: 250, title: '操作', templet: ea.table.tool },
                ]],
            });

            var chtml1 = '<dd lay-value="" class="layui-select-tips layui-this">直接选择</dd>';
            var chtml2 = '<option value="" >直接选择</option>';

            for (const i in company_trad) {
                chtml1 += '<dd lay-value="' + company_trad[i].settlement_id + '" qualifications="' + company_trad[i].qualifications + '" special_qualifications="' + company_trad[i].special_qualifications + '">' + company_trad[i].qualification_type + '</dd>';
                chtml2 += '<option value="' + company_trad[i].settlement_id + '" qualifications="' + company_trad[i].qualifications + '" special_qualifications="' + company_trad[i].special_qualifications + '">' + company_trad[i].qualification_type + '</option>';
            }
            var phtml1 = '<dd lay-value="" class="layui-select-tips layui-this">直接选择</dd>';
            var phtml2 = '<option value="" >直接选择</option>';

            for (const i in persion_trad) {
                phtml1 += '<dd lay-value="' + persion_trad[i].settlement_id + '" qualifications="' + persion_trad[i].qualifications + '" special_qualifications="' + persion_trad[i].special_qualifications + '">' + persion_trad[i].qualification_type + '</dd>';
                phtml2 += '<option value="' + persion_trad[i].settlement_id + '" qualifications="' + persion_trad[i].qualifications + '" special_qualifications="' + persion_trad[i].special_qualifications + '">' + persion_trad[i].qualification_type + '</option>';
            }
            $('.oncstep').on('click', function () {
                element.tabChange('docDemoTabBrief', 'tab' + $(this).data('stab'));
            })
            layui.use(['layer', 'form'], function () {
                $("#persion_trad").next().children().eq(1).html(phtml2);
                $("#persion_trad").html(phtml2);
                form.render();
            });
            layui.use('laydate', function () {
                var laydate = layui.laydate;

                //执行一个laydate实例
                laydate.render({
                    elem: '#card_period_begin'
                });
                laydate.render({
                    elem: '#card_period_end'
                });
                laydate.render({
                    elem: '#contact_period_begin'
                });
                laydate.render({
                    elem: '#contact_period_end'
                });
            });

            form.on('radio(subject_type)', function (data) {
                if (data.elem.value == 'SUBJECT_TYPE_INDIVIDUAL') {
                    $("#persion_trad").next().children().eq(1).html(phtml1);
                    $("#persion_trad").html(phtml2);
                    $('input:radio[name=bank_account_type][value=BANK_ACCOUNT_TYPE_PERSONAL]').prop('checked', 'checked')
                    $('input:radio[name=bank_account_type][value=BANK_ACCOUNT_TYPE_PERSONAL]').removeAttr('disabled')
                } else {
                    $("#persion_trad").next().children().eq(1).html(chtml1);
                    $("#persion_trad").html(chtml2);
                    $('input:radio[name=bank_account_type][value=BANK_ACCOUNT_TYPE_CORPORATE]').prop('checked', 'checked')
                    $('input:radio[name=bank_account_type][value=BANK_ACCOUNT_TYPE_PERSONAL]').attr('disabled', 'disabled')
                }
                form.render();
            });
            form.on('radio(id_card_type)', function (data) {
                if (data.elem.value == '0') {
                    $(".card_period_end").show();
                } else {
                    $(".card_period_end").hide();
                }
                form.render();

            });
            form.on('radio(contact_type)', function (data) {
                if (data.elem.value == '0') {
                    $('.contact_type_1').hide()
                } else {
                    $('.contact_type_1').show()
                }
                form.render();

            });
            form.on('checkbox(sales_scenes_type)', function (data) {
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
            form.on('select(persion_trad)', function (data) {
                $('input[name=qualification_type]').val($(data.elem).find("option:selected").html())
                let qualifications = $(data.elem).find("option:selected").attr("qualifications");
                let special_qualifications = $(data.elem).find("option:selected").attr("special_qualifications");
                if (qualifications == '否') {
                    $('.qualifications_link').hide()
                } else {
                    $('.qualifications_link').show()
                    $('.special_qualifications').html(special_qualifications)
                }
            });
            form.on('select(sub_type)', function (data) {
                if (data.value == 1) {
                    $('.mp_appid').show()
                } else {
                    $('.mp_appid').hide()
                }
            });

            all_region[0].children.forEach((el, index) => {
                if (index == 0) {
                    $('#citys').append('<option selected value=' + el.region + '>' + el.title + '</option>');
                } else {
                    $('#citys').append('<option value=' + el.region + '>' + el.title + '</option>');
                }
            })

            all_region[0].children.forEach((el, index) => {
                if (index == 0) {
                    $('#b-citys').append('<option selected value=' + el.region + '>' + el.title + '</option>');
                } else {
                    $('#b-citys').append('<option value=' + el.region + '>' + el.title + '</option>');
                }
            })

            let citys
            form.on('select(provice)', function (data) {
                citys = all_region.filter(el => {
                    return el.region == data.value
                })
                $('#citys').html('')
                citys[0].children.forEach(el => {
                    $('#citys').append('<option value=' + el.region + '>' + el.title + '</option>');
                });
                form.render();
            });

            let b_citys
            form.on('select(b_provice)', function (data) {
                b_citys = all_region.filter(el => {
                    return el.region == data.value
                })
                $('#b-citys').html('')
                b_citys[0].children.forEach(el => {
                    $('#b-citys').append('<option value=' + el.region + '>' + el.title + '</option>');
                });
                form.render();
            });


            var upload = layui.upload;
            //执行实例
            var uploadInst = upload.render({
                elem: '#business_pic', //绑定元素
                url: 'get_business_info', //上传接口
                done: function (res) {
                    if (res.code) {
                        layer.msg(res.msg || res.message, {
                            icon: 1
                        })
                        $('input[name=merchant_name]').val(res.data.name)
                        $('input[name=license_number]').val(res.data.code)
                        $('input[name=id_card_name]').val(res.data.person_name)
                        $('input[name=license_copy_link]').val(res.data.url)
                        $('.lclimg').attr('src', res.data.url)
                        $('.lclimg').show()
                        form.render()
                    } else {
                        layer.msg(res.msg || res.message, {
                            icon: 2
                        })
                    }
                },
                error: function (err) { },

            });
            upload.render({
                elem: '#id_card_copy_link',
                url: 'get_idcard_info',
                done: function (res) {
                    if (res.code) {
                        layer.msg(res.msg || res.message, {
                            icon: 1
                        })
                        $('input[name=id_card_copy_link]').val(res.data.url)
                        $('input[name=id_card_name]').val(res.data.name)
                        $('input[name=id_card_number]').val(res.data.idcard)
                        $('input[name=id_card_address]').val(res.data.id_card_address)
                        $botimg = '<ul id="bing-store_entrance_pic_link" class="layui-input-block id_card_copy_link_thum layuimini-upload-show">\
                        <li><a><img src="'+ res.data.url + '" data-image="">\
                        </a></li></ul>'
                        if ($('.id_card_copy_link_thum').length > 0) {
                            $('.id_card_copy_link_thum').remove()
                        }
                        $('.id_card_copy_link').after($botimg)
                        form.render()
                    } else {
                        layer.msg(res.msg || res.message, {
                            icon: 2
                        })
                    }
                },
                error: function (err) { },
            });
            upload.render({
                elem: '#id_card_national_link',
                url: 'get_idcard_info',
                done: function (res) {
                    if (res.code) {
                        layer.msg(res.msg || res.message, {
                            icon: 1
                        })
                        $('input[name=id_card_national_link]').val(res.data.url)
                        $('input[name=card_period_begin]').val(formcarddate(res.data.start_time))
                        $('input[name=card_period_end]').val(formcarddate(res.data.end_time))
                        $botimg = '<ul id="bing-store_entrance_pic_link" class="layui-input-block id_card_national_link_thum layuimini-upload-show">\
                        <li><a><img src="'+ res.data.url + '" data-image="">\
                        </a></li></ul>'
                        if ($('.id_card_national_link_thum').length > 0) {
                            $('.id_card_national_link_thum').remove()
                        }
                        $('.id_card_national_link').after($botimg)
                        form.render()
                    } else {
                        layer.msg(res.msg || res.message, {
                            icon: 2
                        })
                    }
                },
                error: function (err) { },
            });
            upload.render({
                elem: '#contact_id_doc_copy_link',
                url: 'get_idcard_info',
                done: function (res) {
                    if (res.code) {
                        layer.msg(res.msg || res.message, {
                            icon: 1
                        })
                        $('input[name=contact_id_doc_copy_link]').val(res.data.url)
                        $('input[name=contact_name]').val((res.data.name))
                        $('input[name=contact_id_number]').val((res.data.idcard))
                        $botimg = '<ul id="bing-store_entrance_pic_link" class="layui-input-block contact_id_doc_copy_link_thum layuimini-upload-show">\
                        <li><a><img src="'+ res.data.url + '" data-image="">\
                        </a></li></ul>'
                        if ($('.contact_id_doc_copy_link_thum').length > 0) {
                            $('.contact_id_doc_copy_link_thum').remove()
                        }
                        $('.contact_id_doc_copy_link').after($botimg)
                        form.render()
                    } else {
                        layer.msg(res.msg || res.message, {
                            icon: 2
                        })
                    }
                },
                error: function (err) { },
            });
            upload.render({
                elem: '#contact_id_doc_copy_back_link',
                url: 'get_idcard_info',
                done: function (res) {
                    if (res.code) {
                        layer.msg(res.msg || res.message, {
                            icon: 1
                        })
                        $('input[name=contact_id_doc_copy_back_link]').val(res.data.url)
                        $('input[name=contact_period_begin]').val(formcarddate(res.data.start_time))
                        $('input[name=contact_period_end]').val(formcarddate(res.data.end_time))
                        $botimg = '<ul id="bing-store_entrance_pic_link" class="layui-input-block contact_id_doc_copy_back_link_thum layuimini-upload-show">\
                        <li><a><img src="'+ res.data.url + '" data-image="">\
                        </a></li></ul>'
                        if ($('.contact_id_doc_copy_back_link_thum').length > 0) {
                            $('.contact_id_doc_copy_back_link_thum').remove()
                        }
                        $('.contact_id_doc_copy_back_link').after($botimg)
                        form.render()
                    } else {
                        layer.msg(res.msg || res.message, {
                            icon: 2
                        })
                    }
                },
                error: function (err) { },
            });

            form.render();
            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
        ttt: function () {
            ea.listen()
        }
    };
    return Controller;
});