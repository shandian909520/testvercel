(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "uni_modules/uni-badge/components/uni-badge/uni-badge" ], {
    "30aa": function aa(t, e, i) {},
    "56cb": function cb(t, e, i) {
        "use strict";
        i.r(e);
        var n = i("9bef"), u = i("5f7a");
        for (var o in u) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                i.d(e, t, function() {
                    return u[t];
                });
            }(o);
        }
        i("c439");
        var a = i("f0c5"), r = Object(a["a"])(u["default"], n["b"], n["c"], !1, null, "184a83f4", null, !1, n["a"], void 0);
        e["default"] = r.exports;
    },
    "5f7a": function f7a(t, e, i) {
        "use strict";
        i.r(e);
        var n = i("e0bb"), u = i.n(n);
        for (var o in n) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                i.d(e, t, function() {
                    return n[t];
                });
            }(o);
        }
        e["default"] = u.a;
    },
    "9bef": function bef(t, e, i) {
        "use strict";
        i.d(e, "b", function() {
            return n;
        }), i.d(e, "c", function() {
            return u;
        }), i.d(e, "a", function() {});
        var n = function n() {
            var t = this.$createElement, e = (this._self._c, this.text ? this.__get_style([ this.badgeWidth, this.positionStyle, this.customStyle, this.dotStyle ]) : null);
            this.$mp.data = Object.assign({}, {
                $root: {
                    s0: e
                }
            });
        }, u = [];
    },
    c439: function c439(t, e, i) {
        "use strict";
        var n = i("30aa"), u = i.n(n);
        u.a;
    },
    e0bb: function e0bb(t, e, i) {
        "use strict";
        Object.defineProperty(e, "__esModule", {
            value: !0
        }), e.default = void 0;
        var n = {
            name: "UniBadge",
            emits: [ "click" ],
            props: {
                type: {
                    type: String,
                    default: "error"
                },
                inverted: {
                    type: Boolean,
                    default: !1
                },
                isDot: {
                    type: Boolean,
                    default: !1
                },
                maxNum: {
                    type: Number,
                    default: 99
                },
                absolute: {
                    type: String,
                    default: ""
                },
                offset: {
                    type: Array,
                    default: function _default() {
                        return [ 0, 0 ];
                    }
                },
                text: {
                    type: [ String, Number ],
                    default: ""
                },
                size: {
                    type: String,
                    default: "small"
                },
                customStyle: {
                    type: Object,
                    default: function _default() {
                        return {};
                    }
                }
            },
            data: function data() {
                return {};
            },
            computed: {
                width: function width() {
                    return 8 * String(this.text).length + 12;
                },
                classNames: function classNames() {
                    var t = this.inverted, e = this.type, i = this.size, n = this.absolute;
                    return [ t ? "uni-badge--" + e + "-inverted" : "", "uni-badge--" + e, "uni-badge--" + i, n ? "uni-badge--absolute" : "" ].join(" ");
                },
                positionStyle: function positionStyle() {
                    if (!this.absolute) return {};
                    var t = this.width / 2, e = 10;
                    this.isDot && (t = 5, e = 5);
                    var i = "".concat(-t + this.offset[0], "px"), n = "".concat(-e + this.offset[1], "px"), u = {
                        rightTop: {
                            right: i,
                            top: n
                        },
                        rightBottom: {
                            right: i,
                            bottom: n
                        },
                        leftBottom: {
                            left: i,
                            bottom: n
                        },
                        leftTop: {
                            left: i,
                            top: n
                        }
                    }, o = u[this.absolute];
                    return o || u["rightTop"];
                },
                badgeWidth: function badgeWidth() {
                    return {
                        width: "".concat(this.width, "px")
                    };
                },
                dotStyle: function dotStyle() {
                    return this.isDot ? {
                        width: "10px",
                        height: "10px",
                        borderRadius: "10px"
                    } : {};
                },
                displayValue: function displayValue() {
                    var t = this.isDot, e = this.text, i = this.maxNum;
                    return t ? "" : Number(e) > i ? "".concat(i, "+") : e;
                }
            },
            methods: {
                onClick: function onClick() {
                    this.$emit("click");
                }
            }
        };
        e.default = n;
    }
} ]);

(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ "uni_modules/uni-badge/components/uni-badge/uni-badge-create-component", {
    "uni_modules/uni-badge/components/uni-badge/uni-badge-create-component": function uni_modulesUniBadgeComponentsUniBadgeUniBadgeCreateComponent(module, exports, __webpack_require__) {
        __webpack_require__("543d")["createComponent"](__webpack_require__("56cb"));
    }
}, [ [ "uni_modules/uni-badge/components/uni-badge/uni-badge-create-component" ] ] ]);