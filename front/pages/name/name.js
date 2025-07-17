(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/name/name" ], {
    "0551": function _(e, t, i) {
        "use strict";
        var n = i("3cb9"), o = i.n(n);
        o.a;
    },
    "3cb9": function cb9(e, t, i) {},
    "68ff": function ff(e, t, i) {
        "use strict";
        i.r(t);
        var n = i("83a6"), o = i("ca73");
        for (var a in o) {
            [ "default" ].indexOf(a) < 0 && function(e) {
                i.d(t, e, function() {
                    return o[e];
                });
            }(a);
        }
        i("0551");
        var p = i("f0c5"), u = Object(p["a"])(o["default"], n["b"], n["c"], !1, null, null, null, !1, n["a"], void 0);
        t["default"] = u.exports;
    },
    7163: function _(e, t, i) {
        "use strict";
        (function(e) {
            var t = i("4ea4");
            i("4ebd");
            t(i("66fd"));
            var n = t(i("68ff"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = i, e(n.default);
        }).call(this, i("543d")["createPage"]);
    },
    "83a6": function a6(e, t, i) {
        "use strict";
        i.d(t, "b", function() {
            return o;
        }), i.d(t, "c", function() {
            return a;
        }), i.d(t, "a", function() {
            return n;
        });
        var n = {
            uniEasyinput: function uniEasyinput() {
                return i.e("uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput").then(i.bind(null, "6a08"));
            }
        }, o = function o() {
            var e = this.$createElement;
            this._self._c;
        }, a = [];
    },
    "887e": function e(_e, t, i) {
        "use strict";
        (function(e) {
            var n = i("4ea4");
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.default = void 0;
            var o = n(i("a8c7")), a = getApp().globalData, p = {
                components: {
                    itemMoive: function itemMoive() {
                        i.e("component/itemMoive/itemMoive").then(function() {
                            return resolve(i("0ed4"));
                        }.bind(null, i)).catch(i.oe);
                    }
                },
                onShareAppMessage: function onShareAppMessage(e) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                onShareTimeline: function onShareTimeline(e) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                data: function data() {
                    return {
                        name: "",
                        popup: {
                            title: "123",
                            body: "内容",
                            ok: "ok",
                            no: "no"
                        },
                        title: "",
                        iamge: "",
                        invite_code: ""
                    };
                },
                onLoad: function onLoad() {
                    this.login_one = e.getStorageSync("login_one"), this.title = e.getStorageSync("title");
                    var t = e.getStorageSync("image");
                    -1 == t.indexOf("https") && (this.iamge = getApp().url_htt(t)), this.invite_code = e.getStorageSync("ma");
                },
                methods: {
                    itemMoive: function itemMoive(t) {
                        this.$refs.itemMoive.close(), "ok" == t.type && (0 == t.serial ? getApp().login() : 2 == t.serial ? e.switchTab({
                            url: "../index/indexs"
                        }) : 4 == t.serial && 1 == this.ad && (e.createRewardedVideoAd({
                            adUnitId: a.ad.ad_reward
                        }), o.default.rewarded.show()));
                    },
                    submit: function submit() {
                        var e = this;
                        getApp().globalData.token ? this.name ? getApp().request("POST", "/api/check_name", {
                            token: getApp().globalData.token
                        }, {
                            name: this.name
                        }).then(function(t) {
                            1 == t.code ? (e.popup.title = t.message, e.popup.no = "继续核名", e.popup.ok = "立即抢注", 
                            e.popup.body = "", e.$refs.itemMoive.serial_one(2), e.$refs.itemMoive.open("top")) : (e.popup.title = t.message, 
                            e.popup.no = "", e.popup.ok = "确认", e.popup.body = "", e.$refs.itemMoive.serial_one(3), 
                            e.$refs.itemMoive.open("top"));
                        }) : (this.popup.title = "不可为空,请重新输入", this.popup.no = "", this.popup.ok = "确定", 
                        this.popup.body = "", this.$refs.itemMoive.serial_one(1), this.$refs.itemMoive.open("top")) : (this.popup.title = "未登录，是否登录", 
                        this.popup.no = "取消", this.popup.ok = "确定", this.popup.body = "", this.$refs.itemMoive.serial_one(0), 
                        this.$refs.itemMoive.open("top"));
                    }
                }
            };
            t.default = p;
        }).call(this, i("543d")["default"]);
    },
    ca73: function ca73(e, t, i) {
        "use strict";
        i.r(t);
        var n = i("887e"), o = i.n(n);
        for (var a in n) {
            [ "default" ].indexOf(a) < 0 && function(e) {
                i.d(t, e, function() {
                    return n[e];
                });
            }(a);
        }
        t["default"] = o.a;
    }
}, [ [ "7163", "common/runtime", "common/vendor" ] ] ]);