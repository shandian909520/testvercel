(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/distribution/distribution" ], {
    "06b8": function b8(t, e, n) {
        "use strict";
        (function(t) {
            var e = n("4ea4");
            n("4ebd");
            e(n("66fd"));
            var i = e(n("ce61"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, t(i.default);
        }).call(this, n("543d")["createPage"]);
    },
    "0e40": function e40(t, e, n) {},
    "36e0": function e0(t, e, n) {
        "use strict";
        n.d(e, "b", function() {
            return a;
        }), n.d(e, "c", function() {
            return r;
        }), n.d(e, "a", function() {
            return i;
        });
        var i = {
            uniSegmentedControl: function uniSegmentedControl() {
                return n.e("uni_modules/uni-segmented-control/components/uni-segmented-control/uni-segmented-control").then(n.bind(null, "00b6"));
            }
        }, a = function a() {
            var t = this.$createElement;
            this._self._c;
        }, r = [];
    },
    4072: function _(t, e, n) {
        "use strict";
        n.r(e);
        var i = n("e70f"), a = n.n(i);
        for (var r in i) {
            [ "default" ].indexOf(r) < 0 && function(t) {
                n.d(e, t, function() {
                    return i[t];
                });
            }(r);
        }
        e["default"] = a.a;
    },
    4803: function _(t, e, n) {
        "use strict";
        var i = n("0e40"), a = n.n(i);
        a.a;
    },
    ce61: function ce61(t, e, n) {
        "use strict";
        n.r(e);
        var i = n("36e0"), a = n("4072");
        for (var r in a) {
            [ "default" ].indexOf(r) < 0 && function(t) {
                n.d(e, t, function() {
                    return a[t];
                });
            }(r);
        }
        n("4803");
        var o = n("f0c5"), u = Object(o["a"])(a["default"], i["b"], i["c"], !1, null, null, null, !1, i["a"], void 0);
        e["default"] = u.exports;
    },
    e70f: function e70f(t, e, n) {
        "use strict";
        (function(t) {
            Object.defineProperty(e, "__esModule", {
                value: !0
            }), e.default = void 0;
            var n = {
                onShareAppMessage: function onShareAppMessage(t) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                data: function data() {
                    return {
                        retail_num: "",
                        items: [ "全部", "已打款", "未打款" ],
                        current: 0,
                        aa: "",
                        array: [],
                        title: "",
                        iamge: "",
                        invite_code: ""
                    };
                },
                onLoad: function onLoad() {
                    var e = this;
                    this.distribution_data(0), getApp().request("POST", "/api/my", {
                        token: getApp().globalData.token
                    }).then(function(t) {
                        e.retail_num = t.data.retail_num;
                    }), this.title = t.getStorageSync("title");
                    var n = t.getStorageSync("image");
                    -1 == n.indexOf("https") && (this.iamge = getApp().url_htt(n)), this.invite_code = getApp().globalData.invite_code;
                },
                methods: {
                    onClickItem: function onClickItem(t) {
                        this.distribution_data(t.currentIndex);
                    },
                    distribution_data: function distribution_data(t) {
                        var e = this;
                        getApp().request("POST", "/api/my_retail_orders", {
                            token: getApp().globalData.token
                        }, {
                            type: t
                        }).then(function(t) {
                            e.array = t.data.orders;
                        });
                    }
                }
            };
            e.default = n;
        }).call(this, n("543d")["default"]);
    }
}, [ [ "06b8", "common/runtime", "common/vendor" ] ] ]);