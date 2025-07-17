(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/person/person" ], {
    "057c": function c(e, n, t) {
        "use strict";
        t.d(n, "b", function() {
            return o;
        }), t.d(n, "c", function() {
            return r;
        }), t.d(n, "a", function() {
            return i;
        });
        var i = {
            uniForms: function uniForms() {
                return Promise.all([ t.e("common/vendor"), t.e("uni_modules/uni-forms/components/uni-forms/uni-forms") ]).then(t.bind(null, "5864"));
            },
            uniFormsItem: function uniFormsItem() {
                return Promise.all([ t.e("common/vendor"), t.e("uni_modules/uni-forms/components/uni-forms-item/uni-forms-item") ]).then(t.bind(null, "93b9"));
            },
            uniEasyinput: function uniEasyinput() {
                return t.e("uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput").then(t.bind(null, "6a08"));
            }
        }, o = function o() {
            var e = this.$createElement;
            this._self._c;
        }, r = [];
    },
    "47af": function af(e, n, t) {
        "use strict";
        (function(e) {
            Object.defineProperty(n, "__esModule", {
                value: !0
            }), n.default = void 0;
            var i = {
                onShareAppMessage: function onShareAppMessage(e) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                components: {
                    lauwenSelect: function lauwenSelect() {
                        t.e("components/lauwen-select/lauwenSelect").then(function() {
                            return resolve(t("fb6a"));
                        }.bind(null, t)).catch(t.oe);
                    },
                    itemMoive: function itemMoive() {
                        t.e("component/itemMoive/itemMoive").then(function() {
                            return resolve(t("0ed4"));
                        }.bind(null, t)).catch(t.oe);
                    }
                },
                data: function data() {
                    return {
                        get_p: 0,
                        formData: {},
                        login_one: "",
                        title: "",
                        iamge: "",
                        invite_code: "",
                        items: [ "提交流程", "常见问题" ],
                        current: 0,
                        style: {
                            borderColor: "#ACC0FF"
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
                        rules: {
                            name: {
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
                                    errorMessage: "请输入微信号"
                                } ]
                            }
                        }
                    };
                },
                methods: {
                    submit: function submit() {
                        var n = this;
                        this.$refs.form.validate().then(function(t) {
                            getApp().request("POST", "/api/person", {
                                token: getApp().globalData.token
                            }, n.formData).then(function(n) {
                                if (4 == n.code) return !1;
                                3 == n.data.status ? (e.showToast({
                                    title: "提交成功",
                                    icon: "none",
                                    duration: 2e3
                                }), setTimeout(function() {
                                    e.redirectTo({
                                        url: "../Indent/Indent?type=0&pf_id=" + getApp().globalData.platform
                                    });
                                }, 2e3)) : e.navigateTo({
                                    url: "../payment/payment?order_id=" + n.data.order_id + "&pf_id=" + getApp().globalData.platform
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
                    }
                },
                onLoad: function onLoad() {
                    this.get_p = e.getStorageSync("get_p"), this.login_one = e.getStorageSync("login_one"), 
                    this.title = e.getStorageSync("title");
                    var n = e.getStorageSync("image");
                    -1 == n.indexOf("https") && (this.iamge = getApp().url_htt(n)), this.invite_code = getApp().globalData.invite_code;
                }
            };
            n.default = i;
        }).call(this, t("543d")["default"]);
    },
    "584b": function b(e, n, t) {
        "use strict";
        (function(e) {
            var n = t("4ea4");
            t("4ebd");
            n(t("66fd"));
            var i = n(t("6695"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = t, e(i.default);
        }).call(this, t("543d")["createPage"]);
    },
    6695: function _(e, n, t) {
        "use strict";
        t.r(n);
        var i = t("057c"), o = t("8529");
        for (var r in o) {
            [ "default" ].indexOf(r) < 0 && function(e) {
                t.d(n, e, function() {
                    return o[e];
                });
            }(r);
        }
        t("6df8");
        var u = t("f0c5"), a = Object(u["a"])(o["default"], i["b"], i["c"], !1, null, null, null, !1, i["a"], void 0);
        n["default"] = a.exports;
    },
    "6df8": function df8(e, n, t) {
        "use strict";
        var i = t("d7c0"), o = t.n(i);
        o.a;
    },
    8529: function _(e, n, t) {
        "use strict";
        t.r(n);
        var i = t("47af"), o = t.n(i);
        for (var r in i) {
            [ "default" ].indexOf(r) < 0 && function(e) {
                t.d(n, e, function() {
                    return i[e];
                });
            }(r);
        }
        n["default"] = o.a;
    },
    d7c0: function d7c0(e, n, t) {}
}, [ [ "584b", "common/runtime", "common/vendor" ] ] ]);