(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/when/when" ], {
    "0019": function _(t, e, a) {
        "use strict";
        var n = a("0bab"), i = a.n(n);
        i.a;
    },
    "0bab": function bab(t, e, a) {},
    "0db8": function db8(t, e, a) {
        "use strict";
        (function(t) {
            Object.defineProperty(e, "__esModule", {
                value: !0
            }), e.default = void 0;
            var a = {
                onShareAppMessage: function onShareAppMessage(t) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                data: function data() {
                    return {
                        onloadOption: "",
                        kefu: "",
                        array: "",
                        src: "",
                        options: {
                            18: "统一社会信用代码（18位）",
                            9: "组织机构代码（9位）",
                            15: "营业执照注册号（15位）"
                        },
                        option: [ {
                            title: ""
                        }, {
                            title: "注册通过"
                        } ],
                        title: "",
                        iamge: "",
                        invite_code: "",
                        data: !1,
                        class_s: "",
                        allTheHidden: !0,
                        hiddenAuthentication: !0,
                        modifyTheHidden: !0,
                        contactTheCustomerService: !0
                    };
                },
                watch: {
                    array: function array(t) {
                        t.info.xcxname && -1 == [ "3", "0", "89251", "89252", "89249", "1007", "91021", "41001" ].indexOf(t.faststatus) ? this.allTheHidden = !0 : this.allTheHidden = !1;
                        -1 == [ "100004", "101", "102", "103", "104", "1000", "89248", "86004", "1004", "61070", "1001", "1002", "61069", "86019" ].indexOf(t.faststatus) ? this.hiddenAuthentication = !0 : this.hiddenAuthentication = !1;
                        var e = [ "100001", "100002", "100003", "1003", "1005" ];
                        -1 == e.indexOf(t.faststatus) ? this.modifyTheHidden = !0 : this.modifyTheHidden = !1;
                        -1 == e.indexOf(t.faststatus) ? this.contactTheCustomerService = !0 : this.contactTheCustomerService = !1;
                    }
                },
                onLoad: function onLoad(e) {
                    var a = this;
                    this.onloadOption = e, this.particulars(e), this.title = t.getStorageSync("title");
                    var n = t.getStorageSync("image");
                    -1 == n.indexOf("https") && (this.iamge = getApp().url_htt(n)), this.invite_code = getApp().globalData.invite_code, 
                    getApp().request("POST", "/api/kefu", {}, {
                        type: 1,
                        pf_id: getApp().globalData.platform
                    }).then(function(e) {
                        a.kefu = e.data, t.setStorageSync("spa_kefu", e.data);
                    });
                },
                methods: {
                    rename: function rename() {
                        t.redirectTo({
                            url: "/pages/rename/rename?id=" + this.array.id + "&pf_id=" + getApp().globalData.platform
                        });
                    },
                    particulars: function particulars(t) {
                        var e = this, a = t;
                        this.class_s = t.class, 1 == t.class ? 1 == t.type ? getApp().request("POST", "/api/get_order_select", {
                            token: getApp().globalData.token
                        }, a).then(function(t) {
                            e.order(a);
                        }) : this.order(a) : getApp().request("POST", "/api/incoming_order/detail", {
                            token: getApp().globalData.token
                        }, a).then(function(t) {
                            e.data = !0, e.array = t.data, e.option[0].title = e.array.applyment_result.audit_detail[0].reject_reason;
                        });
                    },
                    order: function order(t) {
                        var e = this;
                        getApp().request("POST", "/api/order_info", {
                            token: getApp().globalData.token
                        }, t).then(function(t) {
                            e.array = t.data.order, e.data = !0, e.option[0].title = e.array.error_msg, e.src = "../../static/MP-WEIXIN/" + t.data.order.status + ".png";
                        });
                    },
                    pay: function pay() {
                        t.navigateTo({
                            url: "../payment/payment?order_id=" + this.array.order_id + "&pf_id=" + getApp().globalData.platform
                        });
                    },
                    fastAuthentication: function fastAuthentication(e) {
                        var a = this;
                        t.showLoading({
                            title: "提交中...",
                            mask: !0
                        }), getApp().request("POST", "/api/verbm", {
                            token: getApp().globalData.token
                        }, {
                            id: e
                        }).then(function(e) {
                            t.hideLoading(), console.log(e), 1 == e.code ? t.showModal({
                                title: "提示",
                                content: e.message,
                                success: function success() {
                                    a.particulars(a.onloadOption);
                                }
                            }) : t.showModal({
                                title: "错误提示",
                                content: e.message,
                                success: function success() {
                                    a.particulars(a.onloadOption);
                                }
                            });
                        });
                    },
                    amend: function amend(e, a) {
                        t.setStorage({
                            key: "authentication_info",
                            data: {
                                info: e,
                                id: a
                            },
                            success: function success() {
                                t.navigateTo({
                                    url: "/pages/AdministratorRegistration/AdministratorRegistration?what=when&pf_id=" + getApp().globalData.platform
                                });
                            }
                        });
                    },
                    service: function service() {
                        2 == this.kefu.type ? wx.openCustomerServiceChat({
                            extInfo: {
                                url: this.kefu.url
                            },
                            corpId: this.kefu.company_id,
                            success: function success(t) {
                                console.log(t);
                            },
                            fail: function fail(t) {
                                console.log(t);
                            }
                        }) : t.navigateTo({
                            url: "../ke/ke?url=" + this.kefu.url + "&pf_id=" + getApp().globalData.platform
                        });
                    }
                }
            };
            e.default = a;
        }).call(this, a("543d")["default"]);
    },
    "1a35": function a35(t, e, a) {
        "use strict";
        a.r(e);
        var n = a("0db8"), i = a.n(n);
        for (var o in n) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                a.d(e, t, function() {
                    return n[t];
                });
            }(o);
        }
        e["default"] = i.a;
    },
    b047: function b047(t, e, a) {
        "use strict";
        a.d(e, "b", function() {
            return i;
        }), a.d(e, "c", function() {
            return o;
        }), a.d(e, "a", function() {
            return n;
        });
        var n = {
            uniIcons: function uniIcons() {
                return Promise.all([ a.e("common/vendor"), a.e("uni_modules/uni-icons/components/uni-icons/uni-icons") ]).then(a.bind(null, "8c7f"));
            }
        }, i = function i() {
            var t = this.$createElement;
            this._self._c;
        }, o = [];
    },
    b70e: function b70e(t, e, a) {
        "use strict";
        (function(t) {
            var e = a("4ea4");
            a("4ebd");
            e(a("66fd"));
            var n = e(a("ec87"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = a, t(n.default);
        }).call(this, a("543d")["createPage"]);
    },
    ec87: function ec87(t, e, a) {
        "use strict";
        a.r(e);
        var n = a("b047"), i = a("1a35");
        for (var o in i) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                a.d(e, t, function() {
                    return i[t];
                });
            }(o);
        }
        a("0019");
        var r = a("f0c5"), s = Object(r["a"])(i["default"], n["b"], n["c"], !1, null, null, null, !1, n["a"], void 0);
        e["default"] = s.exports;
    }
}, [ [ "b70e", "common/runtime", "common/vendor" ] ] ]);