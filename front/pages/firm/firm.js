(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/firm/firm" ], {
    1389: function _(e, t, n) {
        "use strict";
        n.d(t, "b", function() {
            return a;
        }), n.d(t, "c", function() {
            return i;
        }), n.d(t, "a", function() {
            return o;
        });
        var o = {
            uniForms: function uniForms() {
                return Promise.all([ n.e("common/vendor"), n.e("uni_modules/uni-forms/components/uni-forms/uni-forms") ]).then(n.bind(null, "5864"));
            },
            uniFormsItem: function uniFormsItem() {
                return Promise.all([ n.e("common/vendor"), n.e("uni_modules/uni-forms/components/uni-forms-item/uni-forms-item") ]).then(n.bind(null, "93b9"));
            },
            uniEasyinput: function uniEasyinput() {
                return n.e("uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput").then(n.bind(null, "6a08"));
            }
        }, a = function a() {
            var e = this.$createElement;
            this._self._c;
        }, i = [];
    },
    "1dc6": function dc6(e, t, n) {
        "use strict";
        (function(e) {
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.default = void 0;
            var o = {
                onShareAppMessage: function onShareAppMessage(e) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                components: {
                    lauwenSelect: function lauwenSelect() {
                        n.e("components/lauwen-select/lauwenSelect").then(function() {
                            return resolve(n("fb6a"));
                        }.bind(null, n)).catch(n.oe);
                    },
                    itemMoive: function itemMoive() {
                        n.e("component/itemMoive/itemMoive").then(function() {
                            return resolve(n("0ed4"));
                        }.bind(null, n)).catch(n.oe);
                    }
                },
                data: function data() {
                    return {
                        get_p: 0,
                        aaaa: "",
                        formData: {
                            name: "",
                            wx_code: "",
                            code_type: 18,
                            code: "",
                            person_name: ""
                        },
                        login_one: "",
                        popup: !1,
                        title: "",
                        iamge: "",
                        invite_code: "",
                        items: [ "提交流程", "常见问题" ],
                        current: 0,
                        style: {
                            borderColor: "#ACC0FF"
                        },
                        options: {
                            18: "统一社会信用代码（18位）",
                            9: "组织机构代码（9位）",
                            15: "营业执照注册号（15位）"
                        },
                        answer: [ {
                            issue: "个体户是否可以进行小程序注册？",
                            reply: "答：个体户是可以进行小程序注册的。"
                        }, {
                            issue: "法人微信怎么填？",
                            reply: "答：填写必须是法人微信号，在我的-微信号中查看。"
                        }, {
                            issue: "个体户名称怎么办？",
                            reply: "答：个体营业执照名称为空的请填写个体户+法人名称。"
                        }, {
                            issue: "为什么填对了，还提示企业不存在？",
                            reply: "答：新办的营业执照请在三个工作日后在进行提交进行认证。"
                        } ],
                        hobby: [ {
                            value: 0,
                            text: "篮球"
                        }, {
                            value: 1,
                            text: "足球"
                        }, {
                            value: 2,
                            text: "游泳"
                        } ],
                        rules: {
                            person_name: {
                                rules: [ {
                                    required: !0,
                                    errorMessage: "请输入姓名"
                                }, {
                                    minLength: 2,
                                    errorMessage: "姓名长度最少 {minLength}个字符"
                                } ]
                            },
                            wx_code: {
                                rules: [ {
                                    required: !0,
                                    errorMessage: "请输入法人微信号"
                                } ]
                            },
                            code: {
                                rules: [ {
                                    required: !0,
                                    errorMessage: "请输入企业代码"
                                } ]
                            },
                            name: {
                                rules: [ {
                                    required: !0,
                                    errorMessage: "请输入企业名称"
                                } ]
                            }
                        }
                    };
                },
                methods: {
                    getValue: function getValue(e) {
                        console.log(e);
                    },
                    change: function change(e) {
                        this.formData.code_type = e;
                    },
                    submit: function submit() {
                        var t = this;
                        this.$refs.form.validate().then(function(n) {
                            getApp().request("POST", "/api/company", {
                                token: getApp().globalData.token
                            }, t.formData).then(function(t) {
                                if (4 == t.code) return !1;
                                3 == t.data.status ? (e.showToast({
                                    title: "提交成功",
                                    icon: "none",
                                    duration: 2e3
                                }), setTimeout(function() {
                                    e.redirectTo({
                                        url: "../Indent/Indent?type=0&pf_id=" + getApp().globalData.platform
                                    });
                                }, 2e3)) : e.navigateTo({
                                    url: "../payment/payment?order_id=" + t.data.order_id + "&pf_id=" + getApp().globalData.platform
                                });
                            });
                        }).catch(function(e) {
                            console.log("表单错误信息：", e);
                        });
                    },
                    onClickItem: function onClickItem(e) {
                        this.current != e.currentIndex && (this.current = e.currentIndex);
                    },
                    HidePopup: function HidePopup(e) {
                        "ok" == e ? (getApp().login(), this.popup = !1) : this.popup = !1;
                    },
                    dfg: function dfg(t) {
                        var n = this;
                        t = t[0], e.uploadFile({
                            url: getApp().globalData.url + "/api/discren_pic",
                            filePath: t,
                            name: "file",
                            header: {
                                token: getApp().globalData.token
                            },
                            success: function success(t) {
                                if (console.log(t), e.hideLoading(), "" == t.data) return e.showToast({
                                    title: "识别失败，请重新拍摄上传",
                                    icon: "none"
                                }), !1;
                                var o = JSON.parse(t.data);
                                4 == o.code && (e.showToast({
                                    title: o.message,
                                    icon: "none",
                                    duration: 2e3
                                }), e.clearStorage()), 1 == o.code && (n.formData.code = o.data.code, n.formData.code_type = o.data.code_type, 
                                n.formData.name = o.data.name, n.formData.person_name = o.data.person_name), 0 == o.code && e.showToast({
                                    title: o.message,
                                    icon: "none",
                                    duration: 2e3
                                });
                            },
                            fail: function fail(t) {
                                n.aaaa = t, e.hideLoading(), e.showToast({
                                    title: "识别失败，请联系管理员",
                                    icon: "none"
                                });
                            }
                        });
                    },
                    image: function image() {
                        var t = this;
                        getApp().globalData.token ? e.chooseImage({
                            count: 1,
                            sizeType: [ "original", "compressed" ],
                            success: function success(n) {
                                var o = n.tempFilePaths;
                                e.showLoading({
                                    title: "图片上传识别中",
                                    mask: !0
                                }), t.dfg(o);
                            }
                        }) : this.HidePopup("ok");
                    }
                },
                onLoad: function onLoad() {
                    this.get_p = e.getStorageSync("get_p"), this.login_one = e.getStorageSync("login_one"), 
                    this.title = e.getStorageSync("title");
                    var t = e.getStorageSync("image");
                    -1 == t.indexOf("https") && (this.iamge = getApp().url_htt(t)), this.invite_code = getApp().globalData.invite_code;
                }
            };
            t.default = o;
        }).call(this, n("543d")["default"]);
    },
    "21ed": function ed(e, t, n) {
        "use strict";
        n.r(t);
        var o = n("1389"), a = n("2afb");
        for (var i in a) {
            [ "default" ].indexOf(i) < 0 && function(e) {
                n.d(t, e, function() {
                    return a[e];
                });
            }(i);
        }
        n("c6ca");
        var r = n("f0c5"), u = Object(r["a"])(a["default"], o["b"], o["c"], !1, null, null, null, !1, o["a"], void 0);
        t["default"] = u.exports;
    },
    "26f8": function f8(e, t, n) {
        "use strict";
        (function(e) {
            var t = n("4ea4");
            n("4ebd");
            t(n("66fd"));
            var o = t(n("21ed"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, e(o.default);
        }).call(this, n("543d")["createPage"]);
    },
    "2afb": function afb(e, t, n) {
        "use strict";
        n.r(t);
        var o = n("1dc6"), a = n.n(o);
        for (var i in o) {
            [ "default" ].indexOf(i) < 0 && function(e) {
                n.d(t, e, function() {
                    return o[e];
                });
            }(i);
        }
        t["default"] = a.a;
    },
    c005: function c005(e, t, n) {},
    c6ca: function c6ca(e, t, n) {
        "use strict";
        var o = n("c005"), a = n.n(o);
        a.a;
    }
}, [ [ "26f8", "common/runtime", "common/vendor" ] ] ]);