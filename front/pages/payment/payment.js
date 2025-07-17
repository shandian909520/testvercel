(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/payment/payment" ], {
    "428f": function f(t, e, i) {},
    6372: function _(t, e, i) {
        "use strict";
        i.r(e);
        var n = i("d10f"), a = i("89a7");
        for (var o in a) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                i.d(e, t, function() {
                    return a[t];
                });
            }(o);
        }
        i("fd7b");
        var r = i("f0c5"), s = Object(r["a"])(a["default"], n["b"], n["c"], !1, null, null, null, !1, n["a"], void 0);
        e["default"] = s.exports;
    },
    "89a7": function a7(t, e, i) {
        "use strict";
        i.r(e);
        var n = i("9353"), a = i.n(n);
        for (var o in n) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                i.d(e, t, function() {
                    return n[t];
                });
            }(o);
        }
        e["default"] = a.a;
    },
    9353: function _(t, e, i) {
        "use strict";
        (function(t) {
            Object.defineProperty(e, "__esModule", {
                value: !0
            }), e.default = void 0;
            var i = {
                onShareAppMessage: function onShareAppMessage(t) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                data: function data() {
                    return {
                        order_id: "",
                        price: "",
                        code_s: !0,
                        payment: "weixin",
                        code: "",
                        url: "",
                        urls: "",
                        provider: "",
                        title: "",
                        iamge: "",
                        invite_code: "",
                        code_status: !1,
                        wx_pay_status: 0
                    };
                },
                methods: {
                    payments: function payments(t) {
                        "weixin" == t.currentTarget.dataset.payment ? this.url = this.urls + "/wx_pay" : this.url = this.urls + "/code_pay", 
                        this.payment = t.currentTarget.dataset.payment;
                    },
                    shu: function shu(e, i) {
                        var n = this;
                        getApp().request("POST", this.url, {
                            token: getApp().globalData.token
                        }, e).then(function(e) {
                            if (console.log(e), t.hideLoading(), 0 == e.code) return t.showToast({
                                title: e.message,
                                icon: "none",
                                duration: 2e3
                            }), !1;
                            2 == i ? (t.showToast({
                                title: "兑换成功",
                                icon: "none",
                                duration: 2e3
                            }), setTimeout(function() {
                                t.reLaunch({
                                    url: "../Indent/Indent?type=0&pf_id=" + getApp().globalData.platform
                                });
                            }, 2e3)) : t.requestPayment({
                                provider: n.provider,
                                timeStamp: e.data.timeStamp + "",
                                nonceStr: e.data.nonceStr,
                                package: e.data.package,
                                signType: e.data.signType,
                                paySign: e.data.paySign,
                                success: function success(e) {
                                    t.showToast({
                                        title: "支付完成",
                                        icon: "none",
                                        duration: 2e3
                                    }), setTimeout(function() {
                                        t.reLaunch({
                                            url: "../Indent/Indent?type=0&pf_id=" + getApp().globalData.platform
                                        });
                                    }, 2e3);
                                },
                                fail: function fail(e) {
                                    t.showToast({
                                        title: "用户取消支付",
                                        icon: "none",
                                        duration: 2e3
                                    });
                                }
                            });
                        }).catch(function(t) {
                            console.log(t, 1111);
                        });
                    },
                    submit: function submit() {
                        var e = {};
                        if (!this.code_status && 0 == this.wx_pay_status) return t.showModal({
                            title: "提示",
                            content: "未设置支付方式,请联系客服"
                        }), !1;
                        if ("weixin" == this.payment) e = {
                            order_id: this.order_id
                        }, t.showLoading({
                            title: "支付中"
                        }), this.shu(e, 1); else {
                            if ("" == this.code) return t.showModal({
                                title: "提示",
                                content: "请输入激活码"
                            }), !1;
                            e = {
                                order_id: this.order_id,
                                code: this.code
                            }, t.showLoading({
                                title: "支付中"
                            }), this.shu(e, 2);
                        }
                    }
                },
                onLoad: function onLoad(e) {
                    var i = this;
                    1 == e.classify ? (this.urls = "/api/incoming", this.url = "/api/incoming/wx_pay", 
                    this.order_id = e.order_id, this.price = e.price) : 2 == e.classify ? (this.urls = "/api/alipay", 
                    this.url = "/api/alipay/wx_pay", this.order_id = e.order_id, this.price = e.price) : (this.urls = "/api", 
                    this.url = "/api/wx_pay", getApp().request("POST", "/api/get_order_price", {
                        token: getApp().globalData.token
                    }, {
                        order_id: e.order_id
                    }).then(function(t) {
                        i.price = t.data.price, i.order_id = e.order_id;
                    })), t.getProvider({
                        service: "payment",
                        success: function success(t) {
                            i.provider = t.provider[0];
                        },
                        fail: function fail(e) {
                            console.log(e), t.showToast({
                                title: "未知错误，请联系管理员",
                                icon: "none",
                                duration: 2e3
                            });
                        }
                    }), getApp().request("POST", "/api/code_status", {}, {
                        pf_id: getApp().globalData.platform
                    }).then(function(t) {
                        i.code_status = t.data.code_status;
                    }), getApp().request("POST", "/api/index/wx_pay_status", {}, {
                        pf_id: getApp().globalData.platform
                    }).then(function(t) {
                        i.wx_pay_status = t.data, 0 == t.data && (i.url = i.urls + "/code_pay", i.payment = "code");
                    });
                    var n = t.getStorageSync("image");
                    -1 == n.indexOf("https") && (this.iamge = getApp().url_htt(n));
                }
            };
            e.default = i;
        }).call(this, i("543d")["default"]);
    },
    d10f: function d10f(t, e, i) {
        "use strict";
        i.d(e, "b", function() {
            return a;
        }), i.d(e, "c", function() {
            return o;
        }), i.d(e, "a", function() {
            return n;
        });
        var n = {
            uniIcons: function uniIcons() {
                return Promise.all([ i.e("common/vendor"), i.e("uni_modules/uni-icons/components/uni-icons/uni-icons") ]).then(i.bind(null, "8c7f"));
            }
        }, a = function a() {
            var t = this.$createElement;
            this._self._c;
        }, o = [];
    },
    d656: function d656(t, e, i) {
        "use strict";
        (function(t) {
            var e = i("4ea4");
            i("4ebd");
            e(i("66fd"));
            var n = e(i("6372"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = i, t(n.default);
        }).call(this, i("543d")["createPage"]);
    },
    fd7b: function fd7b(t, e, i) {
        "use strict";
        var n = i("428f"), a = i.n(n);
        a.a;
    }
}, [ [ "d656", "common/runtime", "common/vendor" ] ] ]);