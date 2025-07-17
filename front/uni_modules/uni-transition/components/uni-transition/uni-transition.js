(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "uni_modules/uni-transition/components/uni-transition/uni-transition" ], {
    "4a64": function a64(t, n, i) {
        "use strict";
        var e = i("4ea4");
        Object.defineProperty(n, "__esModule", {
            value: !0
        }), n.default = void 0;
        var a = e(i("448a")), o = e(i("7037")), r = e(i("9523")), s = i("8415");
        function c(t, n) {
            var i = Object.keys(t);
            if (Object.getOwnPropertySymbols) {
                var e = Object.getOwnPropertySymbols(t);
                n && (e = e.filter(function(n) {
                    return Object.getOwnPropertyDescriptor(t, n).enumerable;
                })), i.push.apply(i, e);
            }
            return i;
        }
        function u(t) {
            for (var n = 1; n < arguments.length; n++) {
                var i = null != arguments[n] ? arguments[n] : {};
                n % 2 ? c(Object(i), !0).forEach(function(n) {
                    (0, r.default)(t, n, i[n]);
                }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(t, Object.getOwnPropertyDescriptors(i)) : c(Object(i)).forEach(function(n) {
                    Object.defineProperty(t, n, Object.getOwnPropertyDescriptor(i, n));
                });
            }
            return t;
        }
        var f = {
            name: "uniTransition",
            emits: [ "click", "change" ],
            props: {
                show: {
                    type: Boolean,
                    default: !1
                },
                modeClass: {
                    type: [ Array, String ],
                    default: function _default() {
                        return "fade";
                    }
                },
                duration: {
                    type: Number,
                    default: 300
                },
                styles: {
                    type: Object,
                    default: function _default() {
                        return {};
                    }
                },
                customClass: {
                    type: String,
                    default: ""
                }
            },
            data: function data() {
                return {
                    isShow: !1,
                    transform: "",
                    opacity: 1,
                    animationData: {},
                    durationTime: 300,
                    config: {}
                };
            },
            watch: {
                show: {
                    handler: function handler(t) {
                        t ? this.open() : this.isShow && this.close();
                    },
                    immediate: !0
                }
            },
            computed: {
                stylesObject: function stylesObject() {
                    var t = u(u({}, this.styles), {}, {
                        "transition-duration": this.duration / 1e3 + "s"
                    }), n = "";
                    for (var i in t) {
                        var e = this.toLine(i);
                        n += e + ":" + t[i] + ";";
                    }
                    return n;
                },
                transformStyles: function transformStyles() {
                    return "transform:" + this.transform + ";opacity:" + this.opacity + ";" + this.stylesObject;
                }
            },
            created: function created() {
                this.config = {
                    duration: this.duration,
                    timingFunction: "ease",
                    transformOrigin: "50% 50%",
                    delay: 0
                }, this.durationTime = this.duration;
            },
            methods: {
                init: function init() {
                    var t = arguments.length > 0 && void 0 !== arguments[0] ? arguments[0] : {};
                    t.duration && (this.durationTime = t.duration), this.animation = (0, s.createAnimation)(Object.assign(this.config, t), this);
                },
                onClick: function onClick() {
                    this.$emit("click", {
                        detail: this.isShow
                    });
                },
                step: function step(t) {
                    var n = arguments.length > 1 && void 0 !== arguments[1] ? arguments[1] : {};
                    if (this.animation) {
                        for (var i in t) {
                            try {
                                var e;
                                if ("object" === (0, o.default)(t[i])) (e = this.animation)[i].apply(e, (0, a.default)(t[i])); else this.animation[i](t[i]);
                            } catch (r) {
                                console.error("方法 ".concat(i, " 不存在"));
                            }
                        }
                        return this.animation.step(n), this;
                    }
                },
                run: function run(t) {
                    this.animation && this.animation.run(t);
                },
                open: function open() {
                    var t = this;
                    clearTimeout(this.timer), this.transform = "", this.isShow = !0;
                    var n = this.styleInit(!1), i = n.opacity, e = n.transform;
                    "undefined" !== typeof i && (this.opacity = i), this.transform = e, this.$nextTick(function() {
                        t.timer = setTimeout(function() {
                            t.animation = (0, s.createAnimation)(t.config, t), t.tranfromInit(!1).step(), t.animation.run(), 
                            t.$emit("change", {
                                detail: t.isShow
                            });
                        }, 20);
                    });
                },
                close: function close(t) {
                    var n = this;
                    this.animation && this.tranfromInit(!0).step().run(function() {
                        n.isShow = !1, n.animationData = null, n.animation = null;
                        var t = n.styleInit(!1), i = t.opacity, e = t.transform;
                        n.opacity = i || 1, n.transform = e, n.$emit("change", {
                            detail: n.isShow
                        });
                    });
                },
                styleInit: function styleInit(t) {
                    var n = this, i = {
                        transform: ""
                    }, e = function e(t, _e) {
                        "fade" === _e ? i.opacity = n.animationType(t)[_e] : i.transform += n.animationType(t)[_e] + " ";
                    };
                    return "string" === typeof this.modeClass ? e(t, this.modeClass) : this.modeClass.forEach(function(n) {
                        e(t, n);
                    }), i;
                },
                tranfromInit: function tranfromInit(t) {
                    var n = this, i = function i(t, _i) {
                        var e = null;
                        "fade" === _i ? e = t ? 0 : 1 : (e = t ? "-100%" : "0", "zoom-in" === _i && (e = t ? .8 : 1), 
                        "zoom-out" === _i && (e = t ? 1.2 : 1), "slide-right" === _i && (e = t ? "100%" : "0"), 
                        "slide-bottom" === _i && (e = t ? "100%" : "0")), n.animation[n.animationMode()[_i]](e);
                    };
                    return "string" === typeof this.modeClass ? i(t, this.modeClass) : this.modeClass.forEach(function(n) {
                        i(t, n);
                    }), this.animation;
                },
                animationType: function animationType(t) {
                    return {
                        fade: t ? 1 : 0,
                        "slide-top": "translateY(".concat(t ? "0" : "-100%", ")"),
                        "slide-right": "translateX(".concat(t ? "0" : "100%", ")"),
                        "slide-bottom": "translateY(".concat(t ? "0" : "100%", ")"),
                        "slide-left": "translateX(".concat(t ? "0" : "-100%", ")"),
                        "zoom-in": "scaleX(".concat(t ? 1 : .8, ") scaleY(").concat(t ? 1 : .8, ")"),
                        "zoom-out": "scaleX(".concat(t ? 1 : 1.2, ") scaleY(").concat(t ? 1 : 1.2, ")")
                    };
                },
                animationMode: function animationMode() {
                    return {
                        fade: "opacity",
                        "slide-top": "translateY",
                        "slide-right": "translateX",
                        "slide-bottom": "translateY",
                        "slide-left": "translateX",
                        "zoom-in": "scale",
                        "zoom-out": "scale"
                    };
                },
                toLine: function toLine(t) {
                    return t.replace(/([A-Z])/g, "-$1").toLowerCase();
                }
            }
        };
        n.default = f;
    },
    a776: function a776(t, n, i) {
        "use strict";
        i.d(n, "b", function() {
            return e;
        }), i.d(n, "c", function() {
            return a;
        }), i.d(n, "a", function() {});
        var e = function e() {
            var t = this.$createElement;
            this._self._c;
        }, a = [];
    },
    d22b: function d22b(t, n, i) {
        "use strict";
        i.r(n);
        var e = i("4a64"), a = i.n(e);
        for (var o in e) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                i.d(n, t, function() {
                    return e[t];
                });
            }(o);
        }
        n["default"] = a.a;
    },
    e8b8: function e8b8(t, n, i) {
        "use strict";
        i.r(n);
        var e = i("a776"), a = i("d22b");
        for (var o in a) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                i.d(n, t, function() {
                    return a[t];
                });
            }(o);
        }
        var r = i("f0c5"), s = Object(r["a"])(a["default"], e["b"], e["c"], !1, null, null, null, !1, e["a"], void 0);
        n["default"] = s.exports;
    }
} ]);

(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ "uni_modules/uni-transition/components/uni-transition/uni-transition-create-component", {
    "uni_modules/uni-transition/components/uni-transition/uni-transition-create-component": function uni_modulesUniTransitionComponentsUniTransitionUniTransitionCreateComponent(module, exports, __webpack_require__) {
        __webpack_require__("543d")["createComponent"](__webpack_require__("e8b8"));
    }
}, [ [ "uni_modules/uni-transition/components/uni-transition/uni-transition-create-component" ] ] ]);