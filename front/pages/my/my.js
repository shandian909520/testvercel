(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/my/my" ], {
    "06bc": function bc(e, t, n) {
        "use strict";
        (function(e) {
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.default = void 0;
            var n = {
                onShareAppMessage: function onShareAppMessage(e) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                data: function data() {
                    return {
                        option: {
                            headUrl: "",
                            bgUrl: "",
                            fillStyle: "#0688ff"
                        },
                        name: "",
                        asd: "",
                        user: {
                            head: "",
                            nickname: ""
                        },
                        height: "",
                        state: "",
                        aa: "",
                        canvasToTempFilePath: "",
                        title: "",
                        iamge: "",
                        ma: "",
                        kefu: {},
                        login_one: "",
                        modal_qr: !1,
                        url: ""
                    };
                },
                onShow: function onShow() {
                    this.title = e.getStorageSync("title");
                    var t = e.getStorageSync("image");
                    -1 == t.indexOf("https") && (this.iamge = getApp().url_htt(t)), this.option.bgUrl = getApp().globalData.bgUrl;
                },
                onLoad: function onLoad() {
                    var t = this;
                    this.login_one = e.getStorageSync("login_one"), this.option.bgUrl = getApp().globalData.bgUrl, 
                    e.getSystemInfo({
                        success: function success(e) {
                            t.aa = e.platform, t.state = e.statusBarHeight, t.height = 88;
                        }
                    }), getApp().request("POST", "/api/kefu", {}, {
                        type: 1,
                        pf_id: getApp().globalData.platform
                    }).then(function(n) {
                        t.kefu = n.data, e.setStorageSync("spa_kefu", n.data);
                    }), "" !== getApp().globalData.token && this.array();
                },
                methods: {
                    userSet: function userSet() {
                        e.navigateTo({
                            url: "/pages/login/login"
                        }), f;
                    },
                    guan: function guan() {
                        e.showTabBar(), this.$refs.popup.close();
                    },
                    share_qrcode: function share_qrcode() {
                        var t = this;
                        if ("" == getApp().globalData.token) return e.showToast({
                            title: "请立即登录",
                            icon: "none",
                            duration: 2e3
                        }), !1;
                        this.$refs.popup.open("bottom"), e.hideTabBar(), getApp().request("POST", "/api/get_xcx_qrcode", {}, {
                            page: "pages/index/index",
                            scene: "invite_code=" + getApp().globalData.ma,
                            width: "60",
                            pf_id: getApp().globalData.platform
                        }).then(function(n) {
                            console.log(getApp().globalData.ma);
                            var a = e.getFileSystemManager(), o = wx.env.USER_DATA_PATH + "/img.jpg";
                            a.writeFile({
                                filePath: o,
                                data: n.data.image.slice(22),
                                encoding: "base64",
                                success: function success(e) {
                                    t.option.headUrl = o, t.$refs.draw.share_qrcode(t.option);
                                },
                                fail: function fail(e) {
                                    console.log(e);
                                }
                            });
                        });
                    },
                    bao: function bao(e) {
                        this.$refs.draw.saveShareImg(this.canvasToTempFilePath);
                    },
                    popup: function popup(t) {
                        t && (e.showTabBar(), this.$refs.popup.close());
                    },
                    hide: function hide() {
                        e.showTabBar(), this.$refs.popup.close(), e.showToast({
                            title: "绘制失败",
                            icon: "none"
                        });
                    },
                    bao_val: function bao_val(e) {
                        this.canvasToTempFilePath = e;
                    },
                    login: function login() {
                        var e = this;
                        getApp().login().then(function(t) {
                            e.array();
                        });
                    },
                    distribution: function distribution() {
                        e.navigateTo({
                            url: "../distribution/distribution?pf_id=" + getApp().globalData.platform
                        });
                    },
                    Indent: function Indent(t) {
                        var n = t.currentTarget.dataset.index;
                        e.navigateTo({
                            url: "../Indent/Indent?type=" + n + "&pf_id=" + getApp().globalData.platform
                        });
                    },
                    subordinate: function subordinate() {
                        e.navigateTo({
                            url: "../subordinate/subordinate?&pf_id=" + getApp().globalData.platform
                        });
                    },
                    help: function help() {
                        e.navigateTo({
                            url: "../help/help?pf_id=" + getApp().globalData.platform
                        });
                    },
                    array: function array() {
                        this.user.head = e.getStorageSync("avatarUrl"), this.user.nickname = e.getStorageSync("nickName"), 
                        this.option.bgUrl = getApp().globalData.bgUrl;
                    },
                    tui: function tui() {
                        var t = this;
                        e.showModal({
                            title: "确定退出登录",
                            success: function success(n) {
                                n.confirm ? (e.removeStorageSync("nickName"), e.removeStorageSync("avatarUrl"), 
                                e.removeStorageSync("token"), e.removeStorageSync("bgUrl"), t.user.nickname = "", 
                                t.user.head = "") : n.cancel && console.log("用户点击取消");
                            }
                        });
                    },
                    service: function service() {
                        2 == this.kefu.type ? wx.openCustomerServiceChat({
                            extInfo: {
                                url: this.kefu.url
                            },
                            corpId: this.kefu.company_id,
                            success: function success(e) {
                                console.log(e);
                            },
                            fail: function fail(e) {
                                console.log(e);
                            }
                        }) : e.navigateTo({
                            url: "../ke/ke?url=" + this.kefu.url + "&pf_id=" + getApp().globalData.platform
                        });
                    }
                }
            };
            t.default = n;
        }).call(this, n("543d")["default"]);
    },
    "2fa2": function fa2(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("3913"), o = n("cbad");
        for (var i in o) {
            [ "default" ].indexOf(i) < 0 && function(e) {
                n.d(t, e, function() {
                    return o[e];
                });
            }(i);
        }
        n("511b");
        var r = n("f0c5"), u = Object(r["a"])(o["default"], a["b"], a["c"], !1, null, null, null, !1, a["a"], void 0);
        t["default"] = u.exports;
    },
    3913: function _(e, t, n) {
        "use strict";
        n.d(t, "b", function() {
            return o;
        }), n.d(t, "c", function() {
            return i;
        }), n.d(t, "a", function() {
            return a;
        });
        var a = {
            uniPopup: function uniPopup() {
                return n.e("uni_modules/uni-popup/components/uni-popup/uni-popup").then(n.bind(null, "b624"));
            },
            XQGeneratePoster: function XQGeneratePoster() {
                return Promise.all([ n.e("common/vendor"), n.e("uni_modules/XQ-GeneratePoster/components/XQ-GeneratePoster/XQ-GeneratePoster") ]).then(n.bind(null, "016a"));
            },
            uniIcons: function uniIcons() {
                return Promise.all([ n.e("common/vendor"), n.e("uni_modules/uni-icons/components/uni-icons/uni-icons") ]).then(n.bind(null, "8c7f"));
            }
        }, o = function o() {
            var e = this, t = e.$createElement;
            e._self._c;
            e._isMounted || (e.e0 = function(t) {
                !e.user.head && e.login();
            });
        }, i = [];
    },
    "481c": function c(e, t, n) {
        "use strict";
        (function(e) {
            var t = n("4ea4");
            n("4ebd");
            t(n("66fd"));
            var a = t(n("2fa2"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, e(a.default);
        }).call(this, n("543d")["createPage"]);
    },
    "511b": function b(e, t, n) {
        "use strict";
        var a = n("620d"), o = n.n(a);
        o.a;
    },
    "620d": function d(e, t, n) {},
    cbad: function cbad(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("06bc"), o = n.n(a);
        for (var i in a) {
            [ "default" ].indexOf(i) < 0 && function(e) {
                n.d(t, e, function() {
                    return a[e];
                });
            }(i);
        }
        t["default"] = o.a;
    }
}, [ [ "481c", "common/runtime", "common/vendor" ] ] ]);