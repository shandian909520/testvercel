(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/help/help" ], {
    "0606": function _(t, a, e) {
        "use strict";
        (function(t) {
            Object.defineProperty(a, "__esModule", {
                value: !0
            }), a.default = void 0;
            var e = {
                data: function data() {
                    return {
                        array: {},
                        page: 1,
                        per_page: 10
                    };
                },
                onLoad: function onLoad() {
                    t.removeStorageSync("help"), this.list();
                },
                onReachBottom: function onReachBottom() {
                    this.array.length % this.per_page === 0 ? (this.page = this.page + 1, this.list()) : t.showToast({
                        title: "已经到底了"
                    });
                },
                methods: {
                    help_body: function help_body(a) {
                        t.setStorageSync("help", this.array[a]), t.navigateTo({
                            url: "help_body?pf_id=" + getApp().globalData.platform
                        });
                    },
                    list: function list() {
                        var t = this;
                        getApp().request("GET", "/api/article", {}, {
                            per_page: this.per_page,
                            page: this.page,
                            pf_id: getApp().globalData.platform
                        }).then(function(a) {
                            if (t.page > 1) for (var e = 0; e < a.data.data.length; e++) {
                                t.array.push(a.data.data[e]);
                            } else t.array = a.data.data;
                        });
                    }
                }
            };
            a.default = e;
        }).call(this, e("543d")["default"]);
    },
    "102a": function a(t, _a, e) {
        "use strict";
        var n = e("6e1b"), r = e.n(n);
        r.a;
    },
    4031: function _(t, a, e) {
        "use strict";
        e.d(a, "b", function() {
            return n;
        }), e.d(a, "c", function() {
            return r;
        }), e.d(a, "a", function() {});
        var n = function n() {
            var t = this.$createElement;
            this._self._c;
        }, r = [];
    },
    "6e1b": function e1b(t, a, e) {},
    "9f2c": function f2c(t, a, e) {
        "use strict";
        e.r(a);
        var n = e("0606"), r = e.n(n);
        for (var i in n) {
            [ "default" ].indexOf(i) < 0 && function(t) {
                e.d(a, t, function() {
                    return n[t];
                });
            }(i);
        }
        a["default"] = r.a;
    },
    b6f7: function b6f7(t, a, e) {
        "use strict";
        (function(t) {
            var a = e("4ea4");
            e("4ebd");
            a(e("66fd"));
            var n = a(e("d9b6"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = e, t(n.default);
        }).call(this, e("543d")["createPage"]);
    },
    d9b6: function d9b6(t, a, e) {
        "use strict";
        e.r(a);
        var n = e("4031"), r = e("9f2c");
        for (var i in r) {
            [ "default" ].indexOf(i) < 0 && function(t) {
                e.d(a, t, function() {
                    return r[t];
                });
            }(i);
        }
        e("102a");
        var o = e("f0c5"), u = Object(o["a"])(r["default"], n["b"], n["c"], !1, null, null, null, !1, n["a"], void 0);
        a["default"] = u.exports;
    }
}, [ [ "b6f7", "common/runtime", "common/vendor" ] ] ]);