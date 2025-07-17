(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/help/help_body" ], {
    "48e5": function e5(t, e, n) {
        "use strict";
        (function(t) {
            Object.defineProperty(e, "__esModule", {
                value: !0
            }), e.default = void 0;
            var n = {
                data: function data() {
                    return {
                        array: ""
                    };
                },
                onLoad: function onLoad() {
                    var e = t.getStorageSync("help");
                    e.content = e.content.replace(/\s+style="[^"]*"/g, ""), e.content = e.content.replace(/<img/g, '<img style="max-width:100%"'), 
                    this.array = e;
                }
            };
            e.default = n;
        }).call(this, n("543d")["default"]);
    },
    "94bc": function bc(t, e, n) {
        "use strict";
        (function(t) {
            var e = n("4ea4");
            n("4ebd");
            e(n("66fd"));
            var a = e(n("b663"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, t(a.default);
        }).call(this, n("543d")["createPage"]);
    },
    b663: function b663(t, e, n) {
        "use strict";
        n.r(e);
        var a = n("c5d6"), c = n("d7f6");
        for (var r in c) {
            [ "default" ].indexOf(r) < 0 && function(t) {
                n.d(e, t, function() {
                    return c[t];
                });
            }(r);
        }
        var u = n("f0c5"), o = Object(u["a"])(c["default"], a["b"], a["c"], !1, null, null, null, !1, a["a"], void 0);
        e["default"] = o.exports;
    },
    c5d6: function c5d6(t, e, n) {
        "use strict";
        n.d(e, "b", function() {
            return a;
        }), n.d(e, "c", function() {
            return c;
        }), n.d(e, "a", function() {});
        var a = function a() {
            var t = this.$createElement;
            this._self._c;
        }, c = [];
    },
    d7f6: function d7f6(t, e, n) {
        "use strict";
        n.r(e);
        var a = n("48e5"), c = n.n(a);
        for (var r in a) {
            [ "default" ].indexOf(r) < 0 && function(t) {
                n.d(e, t, function() {
                    return a[t];
                });
            }(r);
        }
        e["default"] = c.a;
    }
}, [ [ "94bc", "common/runtime", "common/vendor" ] ] ]);