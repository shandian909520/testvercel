(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/index/index" ], {
    "0b1b": function b1b(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("7269"), i = n("fe06");
        for (var u in i) {
            [ "default" ].indexOf(u) < 0 && function(e) {
                n.d(t, e, function() {
                    return i[e];
                });
            }(u);
        }
        var o = n("f0c5"), r = Object(o["a"])(i["default"], a["b"], a["c"], !1, null, null, null, !1, a["a"], void 0);
        t["default"] = r.exports;
    },
    "0dd2": function dd2(e, t, n) {
        "use strict";
        (function(e) {
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.default = void 0;
            var n = {
                data: function data() {
                    return {};
                },
                methods: {},
                onLoad: function onLoad(t) {
                    if (t.scene) {
                        var n = decodeURIComponent(t.scene).split("=")[1];
                        e.setStorage({
                            key: "invite_code",
                            data: n,
                            success: function success() {
                                getApp().request("GET", "/api/index/get_pian", {}, {
                                    pf_id: getApp().globalData.platform
                                }).then(function(t) {
                                    e.setStorageSync("get_p", t.data), 1 == t.data ? e.reLaunch({
                                        url: "/pages/ke/web?pf_id=" + getApp().globalData.platform
                                    }) : e.switchTab({
                                        url: "indexs"
                                    });
                                });
                            }
                        });
                    } else getApp().request("GET", "/api/index/get_pian", {}, {
                        pf_id: getApp().globalData.platform
                    }).then(function(t) {
                        1 == t.data ? e.reLaunch({
                            url: "/pages/ke/web?pf_id=" + getApp().globalData.platform
                        }) : e.switchTab({
                            url: "indexs"
                        });
                    });
                }
            };
            t.default = n;
        }).call(this, n("543d")["default"]);
    },
    7269: function _(e, t, n) {
        "use strict";
        n.d(t, "b", function() {
            return a;
        }), n.d(t, "c", function() {
            return i;
        }), n.d(t, "a", function() {});
        var a = function a() {
            var e = this.$createElement;
            this._self._c;
        }, i = [];
    },
    f228: function f228(e, t, n) {
        "use strict";
        (function(e) {
            var t = n("4ea4");
            n("4ebd");
            t(n("66fd"));
            var a = t(n("0b1b"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, e(a.default);
        }).call(this, n("543d")["createPage"]);
    },
    fe06: function fe06(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("0dd2"), i = n.n(a);
        for (var u in a) {
            [ "default" ].indexOf(u) < 0 && function(e) {
                n.d(t, e, function() {
                    return a[e];
                });
            }(u);
        }
        t["default"] = i.a;
    }
}, [ [ "f228", "common/runtime", "common/vendor" ] ] ]);