(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "common/main" ], {
    "2f39": function f39(t, e, n) {
        "use strict";
        var a = n("7a4a"), o = n.n(a);
        o.a;
    },
    "5c5c": function c5c(t, e, n) {
        "use strict";
        (function(t) {
            var e = n("4ea4"), a = e(n("9523"));
            n("4ebd");
            var o = e(n("f50c")), r = e(n("66fd"));
            e(n("c0a4"));
            function c(t, e) {
                var n = Object.keys(t);
                if (Object.getOwnPropertySymbols) {
                    var a = Object.getOwnPropertySymbols(t);
                    e && (a = a.filter(function(e) {
                        return Object.getOwnPropertyDescriptor(t, e).enumerable;
                    })), n.push.apply(n, a);
                }
                return n;
            }
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, r.default.config.ignoredElements = [ "wx-open-launch-weapp" ], 
            r.default.config.productionTip = !1, o.default.mpType = "app";
            var i = new r.default(function(t) {
                for (var e = 1; e < arguments.length; e++) {
                    var n = null != arguments[e] ? arguments[e] : {};
                    e % 2 ? c(Object(n), !0).forEach(function(e) {
                        (0, a.default)(t, e, n[e]);
                    }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(t, Object.getOwnPropertyDescriptors(n)) : c(Object(n)).forEach(function(e) {
                        Object.defineProperty(t, e, Object.getOwnPropertyDescriptor(n, e));
                    });
                }
                return t;
            }({}, o.default));
            t(i).$mount();
        }).call(this, n("543d")["createApp"]);
    },
    "62a0": function a0(t, e, n) {
        "use strict";
        (function(t) {
            Object.defineProperty(e, "__esModule", {
                value: !0
            }), e.default = void 0;
            var n = {
                globalData: {
                    url: "https://renzheng.2vq.cn",
                    ad: {},
                    code: "",
                    token: "",
                    ma: "",
                    bgUrl: "",
                    platform: 1
                },
                onLaunch: function onLaunch() {
                    var e = this;
                    this.globalData.token = t.getStorageSync("token"), this.globalData.ma = t.getStorageSync("ma"), 
                    this.globalData.bgUrl = t.getStorageSync("bgUrl"), this.request("POST", "/api/ad", {}, {
                        pf_id: this.globalData.platform
                    }).then(function(t) {
                        e.globalData.ad = t.data;
                    }), this.request("POST", "/api/share", {}, {
                        pf_id: this.globalData.platform
                    }).then(function(e) {
                        t.setStorageSync("title", e.data.title), t.setStorageSync("image", e.data.image), 
                        t.setStorageSync("msg", e.data.msg);
                    }), this.request("POST", "/api/index_title", {}, {
                        pf_id: this.globalData.platform
                    }).then(function(e) {
                        t.setStorageSync("indexTitle", e.data);
                    });
                },
                onShow: function onShow() {
                    console.log("App Show");
                    var e = t.getUpdateManager();
                    e.onCheckForUpdate(function(t) {
                        console.log(t.hasUpdate, "版本信息的回调");
                    }), e.onUpdateReady(function(n) {
                        console.log(n, "更新提示"), t.showModal({
                            title: "更新提示",
                            content: "新版本已经准备好，是否重启应用？",
                            success: function success(t) {
                                t.confirm && e.applyUpdate();
                            }
                        });
                    }), e.onUpdateFailed(function(t) {
                        console.log(t, "新的版本下载失败");
                    });
                },
                onHide: function onHide() {
                    console.log("App Hide");
                },
                methods: {
                    url_htt: function url_htt(t) {
                        return -1 == t.indexOf("http") && (t = this.globalData.url + t), t;
                    },
                    login: function login() {
                        var e = this;
                        return new Promise(function(n, a) {
                            t.getUserProfile({
                                desc: "获取昵称和头像用于登录",
                                success: function success(t) {
                                    t = JSON.parse(t.rawData), e.register().then(function(t) {
                                        e.ma().then(function(t) {
                                            1 == t && n(1);
                                        });
                                    });
                                }
                            }), t.getProvider({
                                service: "oauth",
                                success: function success(e) {
                                    t.login({
                                        provider: e.provider[0],
                                        success: function success(e) {
                                            t.setStorageSync("code", e.code);
                                        }
                                    });
                                }
                            });
                        });
                    },
                    register: function register() {
                        var e = this;
                        return new Promise(function(n, a) {
                            var o = {
                                invite_code: t.getStorageSync("invite_code"),
                                code: t.getStorageSync("code"),
                                pf_id: e.globalData.platform
                            };
                            e.request("POST", "/api/login", {}, o).then(function(a) {
                                e.globalData.token = a.data.token.token, t.setStorageSync("token", a.data.token.token), 
                                "" == a.data.token.nickname || "微信用户" == a.data.token.nickname ? t.reLaunch({
                                    url: "/pages/login/login"
                                }) : (t.setStorageSync("nickName", a.data.token.nickname), t.setStorageSync("avatarUrl", a.data.token.head), 
                                e.loginsuccess()), n(1);
                            });
                        });
                    },
                    loginsuccess: function loginsuccess() {
                        t.showToast({
                            title: "登录成功",
                            icon: "success"
                        });
                    },
                    request: function request() {
                        var e = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : "POST", n = arguments.length > 1 ? arguments[1] : void 0, a = arguments.length > 2 && void 0 !== arguments[2] ? arguments[2] : null, o = arguments.length > 3 && void 0 !== arguments[3] ? arguments[3] : null, r = this;
                        return new Promise(function(c, i) {
                            t.request({
                                url: r.globalData.url + n,
                                data: o,
                                method: e,
                                header: a,
                                success: function success(e) {
                                    4 == e.data.code ? (t.showModal({
                                        content: e.data.message,
                                        showCancel: !1,
                                        success: function success(t) {
                                            r.login();
                                        }
                                    }), t.removeStorageSync("code"), t.removeStorageSync("nickName"), t.removeStorageSync("avatarUrl"), 
                                    t.removeStorageSync("token"), t.removeStorageSync("bgUrl"), t.removeStorageSync("ma")) : c(e.data);
                                },
                                fail: function fail(e) {
                                    t.showToast({
                                        title: "未知错误，请联系管理员",
                                        icon: "none",
                                        duration: 2e3
                                    });
                                }
                            });
                        });
                    },
                    ma: function ma() {
                        var e = this;
                        return new Promise(function(n, a) {
                            getApp().request("POST", "/api/my_invite", {
                                token: e.globalData.token
                            }, {}).then(function(a) {
                                var o = e.url_htt(a.data.img);
                                e.globalData.bgUrl = o, e.globalData.ma = a.data.invite_code, t.setStorageSync("bgUrl", o), 
                                t.setStorageSync("ma", a.data.invite_code), n(!0);
                            });
                        });
                    }
                }
            };
            e.default = n;
        }).call(this, n("543d")["default"]);
    },
    "7a4a": function a4a(t, e, n) {},
    bfd3: function bfd3(t, e, n) {
        "use strict";
        n.r(e);
        var a = n("62a0"), o = n.n(a);
        for (var r in a) {
            [ "default" ].indexOf(r) < 0 && function(t) {
                n.d(e, t, function() {
                    return a[t];
                });
            }(r);
        }
        e["default"] = o.a;
    },
    f50c: function f50c(t, e, n) {
        "use strict";
        n.r(e);
        var a = n("bfd3");
        for (var o in a) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                n.d(e, t, function() {
                    return a[t];
                });
            }(o);
        }
        n("2f39");
        var r = n("f0c5"), c = Object(r["a"])(a["default"], void 0, void 0, !1, null, null, null, !1, void 0, void 0);
        e["default"] = c.exports;
    }
}, [ [ "5c5c", "common/runtime", "common/vendor" ] ] ]);