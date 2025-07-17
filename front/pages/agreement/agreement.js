(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/agreement/agreement" ], {
    "6f7a": function f7a(t, e, n) {
        "use strict";
        n.r(e);
        var a = n("9fb4"), f = n("cadf");
        for (var r in f) {
            [ "default" ].indexOf(r) < 0 && function(t) {
                n.d(e, t, function() {
                    return f[t];
                });
            }(r);
        }
        n("7182");
        var u = n("f0c5"), i = Object(u["a"])(f["default"], a["b"], a["c"], !1, null, null, null, !1, a["a"], void 0);
        e["default"] = i.exports;
    },
    7182: function _(t, e, n) {
        "use strict";
        var a = n("f38f"), f = n.n(a);
        f.a;
    },
    "9fb4": function fb4(t, e, n) {
        "use strict";
        n.d(e, "b", function() {
            return a;
        }), n.d(e, "c", function() {
            return f;
        }), n.d(e, "a", function() {});
        var a = function a() {
            var t = this.$createElement;
            this._self._c;
        }, f = [];
    },
    cadf: function cadf(t, e, n) {
        "use strict";
        n.r(e);
        var a = n("fa2c"), f = n.n(a);
        for (var r in a) {
            [ "default" ].indexOf(r) < 0 && function(t) {
                n.d(e, t, function() {
                    return a[t];
                });
            }(r);
        }
        e["default"] = f.a;
    },
    f38f: function f38f(t, e, n) {},
    fa2c: function fa2c(t, e, n) {
        "use strict";
        Object.defineProperty(e, "__esModule", {
            value: !0
        }), e.default = void 0;
        var a = {
            data: function data() {
                return {
                    nodes: ""
                };
            },
            onLoad: function onLoad() {
                var t = this;
                getApp().request("GET", "/api/pagreement", {}, {
                    pf_id: getApp().globalData.platform
                }).then(function(e) {
                    var n = e.data.replace(/(style="(.*?)")|(width="(.*?)")|(height="(.*?)")/gi, 'style="width:100%;height:auto;display:block;"');
                    t.nodes = n;
                });
            },
            methods: {}
        };
        e.default = a;
    },
    fa87: function fa87(t, e, n) {
        "use strict";
        (function(t) {
            var e = n("4ea4");
            n("4ebd");
            e(n("66fd"));
            var a = e(n("6f7a"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, t(a.default);
        }).call(this, n("543d")["createPage"]);
    }
}, [ [ "fa87", "common/runtime", "common/vendor" ] ] ]);