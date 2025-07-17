(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "uni_modules/XQ-GeneratePoster/components/XQ-GeneratePoster/XQ-GeneratePoster" ], {
    "016a": function a(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("30f9"), o = n("6d29");
        for (var i in o) {
            [ "default" ].indexOf(i) < 0 && function(e) {
                n.d(t, e, function() {
                    return o[e];
                });
            }(i);
        }
        var r = n("f0c5"), s = Object(r["a"])(o["default"], a["b"], a["c"], !1, null, null, null, !1, a["a"], void 0);
        t["default"] = s.exports;
    },
    "30f9": function f9(e, t, n) {
        "use strict";
        n.d(t, "b", function() {
            return a;
        }), n.d(t, "c", function() {
            return o;
        }), n.d(t, "a", function() {});
        var a = function a() {
            var e = this, t = e.$createElement;
            e._self._c;
            e._isMounted || (e.e0 = function(t) {
                e.showSaveImgWin = !0;
            });
        }, o = [];
    },
    "6d29": function d29(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("ee38"), o = n.n(a);
        for (var i in a) {
            [ "default" ].indexOf(i) < 0 && function(e) {
                n.d(t, e, function() {
                    return a[e];
                });
            }(i);
        }
        t["default"] = o.a;
    },
    ee38: function ee38(e, t, n) {
        "use strict";
        (function(e) {
            var a = n("4ea4");
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.default = void 0;
            var o = a(n("a34a")), i = a(n("c973")), r = {
                name: "XQGeneratePoster",
                data: function data() {
                    return {
                        ratio: 1,
                        ctx: null,
                        canvasToTempFilePath: null,
                        openStatus: !0,
                        share_qrcode_flag: !1,
                        showSaveImgWin: !1,
                        top: 15
                    };
                },
                methods: {
                    share_qrcode: function share_qrcode(e) {
                        e && (this.canvasToTempFilePath || this.createCanvasImage(e), this.share_qrcode_flag = !0);
                    },
                    downloadFileImg: function downloadFileImg(t) {
                        var n = this;
                        return new Promise(function(a) {
                            e.getImageInfo({
                                src: t,
                                success: function success(e) {
                                    a(e.path);
                                },
                                fail: function fail(t) {
                                    var a = n;
                                    e.showToast({
                                        title: "网络错误请重试",
                                        icon: "loading"
                                    }), setTimeout(function() {
                                        a.$emit("hide", "true");
                                    }, 1500);
                                }
                            });
                        });
                    },
                    createCanvasImage: function createCanvasImage(t) {
                        var n = this;
                        return (0, i.default)(o.default.mark(function a() {
                            var i, r;
                            return o.default.wrap(function(a) {
                                while (1) {
                                    switch (a.prev = a.next) {
                                      case 0:
                                        n.ctx || (e.showLoading({
                                            title: "生成中..."
                                        }), i = n.downloadFileImg(t.headUrl), r = "", t.bgUrl && (r = new Promise(function(a) {
                                            e.downloadFile({
                                                url: t.bgUrl,
                                                success: function success(e) {
                                                    a(e.tempFilePath);
                                                },
                                                fail: function fail(t) {
                                                    var a = n;
                                                    e.showToast({
                                                        title: "网络错误请重试",
                                                        icon: "loading"
                                                    }), setTimeout(function() {
                                                        a.$emit("hide", "true");
                                                    }, 1500);
                                                }
                                            });
                                        })), Promise.all([ i, r ]).then(function(a) {
                                            var o = e.createCanvasContext("myCanvas", n);
                                            n.ratio, n.ratio;
                                            t.bgUrl ? o.drawImage(a[1], 0, 0, 700, 1334) : (o.save(), o.translate(0, 0), o.fillStyle = t.fillStyle || "#0688ff", 
                                            o.fill(), o.restore()), o.save(), o.beginPath(), o.arc(550, 1224, 70, 0, 2 * Math.PI, !1), 
                                            o.clip(), o.drawImage(a[0], 480, 1154, 140, 140), o.restore(), o.draw(!1, function() {
                                                console.log(456), e.canvasToTempFilePath({
                                                    canvasId: "myCanvas",
                                                    width: 700,
                                                    height: 1334,
                                                    destWidth: 700,
                                                    destHeight: 1334,
                                                    success: function success(t) {
                                                        n.canvasToTempFilePath = t.tempFilePath, n.$emit("img", t.tempFilePath), n.showShareImg = !0, 
                                                        e.showToast({
                                                            title: "绘制成功"
                                                        });
                                                    },
                                                    fail: function fail(t) {
                                                        e.showToast({
                                                            title: "绘制失败"
                                                        });
                                                    },
                                                    complete: function complete() {
                                                        e.hideLoading(), e.hideToast();
                                                    }
                                                }, n);
                                            });
                                        }));

                                      case 1:
                                      case "end":
                                        return a.stop();
                                    }
                                }
                            }, a);
                        }))();
                    },
                    saveShareImg: function saveShareImg(t) {
                        var n = this;
                        e.saveImageToPhotosAlbum({
                            filePath: t,
                            success: function success() {
                                n.$emit("pop", "true");
                            },
                            fail: function fail(e) {
                                console.log(e);
                            }
                        });
                    }
                }
            };
            t.default = r;
        }).call(this, n("543d")["default"]);
    }
} ]);

(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ "uni_modules/XQ-GeneratePoster/components/XQ-GeneratePoster/XQ-GeneratePoster-create-component", {
    "uni_modules/XQ-GeneratePoster/components/XQ-GeneratePoster/XQ-GeneratePoster-create-component": function uni_modulesXQGeneratePosterComponentsXQGeneratePosterXQGeneratePosterCreateComponent(module, exports, __webpack_require__) {
        __webpack_require__("543d")["createComponent"](__webpack_require__("016a"));
    }
}, [ [ "uni_modules/XQ-GeneratePoster/components/XQ-GeneratePoster/XQ-GeneratePoster-create-component" ] ] ]);