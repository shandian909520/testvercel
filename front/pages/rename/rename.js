(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/rename/rename" ], {
    "2c3f": function c3f(e, t, n) {
        "use strict";
        n.d(t, "b", function() {
            return i;
        }), n.d(t, "c", function() {
            return o;
        }), n.d(t, "a", function() {
            return a;
        });
        var a = {
            uniForms: function uniForms() {
                return Promise.all([ n.e("common/vendor"), n.e("uni_modules/uni-forms/components/uni-forms/uni-forms") ]).then(n.bind(null, "5864"));
            },
            more: function more() {
                return n.e("components/more/more").then(n.bind(null, "9df9"));
            },
            uniFormsItem: function uniFormsItem() {
                return Promise.all([ n.e("common/vendor"), n.e("uni_modules/uni-forms/components/uni-forms-item/uni-forms-item") ]).then(n.bind(null, "93b9"));
            },
            uniEasyinput: function uniEasyinput() {
                return n.e("uni_modules/uni-easyinput/components/uni-easyinput/uni-easyinput").then(n.bind(null, "6a08"));
            }
        }, i = function i() {
            var e = this.$createElement;
            this._self._c;
        }, o = [];
    },
    "3a94": function a94(e, t, n) {
        "use strict";
        var a = n("9ff6"), i = n.n(a);
        i.a;
    },
    "9ff6": function ff6(e, t, n) {},
    c63a: function c63a(e, t, n) {
        "use strict";
        (function(e) {
            var a = n("4ea4");
            Object.defineProperty(t, "__esModule", {
                value: !0
            }), t.default = void 0;
            var i = a(n("a34a")), o = a(n("c973")), r = {
                data: function data() {
                    return {
                        formData: {
                            license: "",
                            license_link: "",
                            otherDetailsmediaid: "",
                            otherDetails: "",
                            id: ""
                        },
                        formRules: {
                            xcxname: {
                                rules: [ {
                                    required: !0,
                                    errorMessage: "请输入小程序名"
                                } ]
                            },
                            license_link: {
                                rules: [ {
                                    required: !0,
                                    errorMessage: "请上传营业执照照片"
                                } ]
                            }
                        }
                    };
                },
                methods: {
                    del: function del(e) {
                        if ("license_link" == e.name_dat && (this.formData.license_link = ""), "otherDetails" == e.name_dat) {
                            var t = this.formData[e.name_dat].split(","), n = this.formData.otherDetailsmediaid.split(",");
                            t.splice(e.index, 1), n.splice(e.index, 1), this.formData[e.name_dat] = t.join(","), 
                            this.formData.otherDetailsmediaid = n.join(",");
                        }
                    },
                    submit: function submit() {
                        var t = this;
                        return (0, o.default)(i.default.mark(function n() {
                            var a;
                            return i.default.wrap(function(n) {
                                while (1) {
                                    switch (n.prev = n.next) {
                                      case 0:
                                        return e.showLoading({
                                            title: "提交中...",
                                            mask: !0
                                        }), n.next = 3, t.arrayDispose();

                                      case 3:
                                        return a = n.sent, n.next = 6, t.formData.id;

                                      case 6:
                                        return a.id = n.sent, n.next = 9, t.formData.xcxname;

                                      case 9:
                                        return a.xcxname = n.sent, n.next = 12, t.formData.license;

                                      case 12:
                                        return a.license = n.sent, n.next = 15, t.formData.license_link;

                                      case 15:
                                        a.license_link = n.sent, t.$refs.form.validate().then(function(t) {
                                            getApp().request("POST", "/api/setweappname", {
                                                token: getApp().globalData.token
                                            }, a).then(function(t) {
                                                e.hideLoading(), 1 == t.code ? (e.removeStorage({
                                                    key: "authentication_info"
                                                }), e.showToast({
                                                    title: t.message,
                                                    icon: "success",
                                                    duration: 2e3,
                                                    success: function success() {
                                                        setTimeout(function() {
                                                            e.redirectTo({
                                                                url: "../Indent/Indent?type=0&pf_id=" + getApp().globalData.platform
                                                            });
                                                        }, 2e3);
                                                    }
                                                })) : e.showModal({
                                                    title: "失败",
                                                    content: t.message
                                                });
                                            });
                                        }).catch(function(t) {
                                            e.hideLoading(), console.log("表单错误信息：", t);
                                        });

                                      case 17:
                                      case "end":
                                        return n.stop();
                                    }
                                }
                            }, n);
                        }))();
                    },
                    arrayDispose: function arrayDispose() {
                        var e = this.formData.otherDetails.split(","), t = this.formData.otherDetailsmediaid.split(","), n = {};
                        return new Promise(function(a, i) {
                            e.some(function(i, o) {
                                n["naming_other_stuff_" + (o + 1)] = t[o], n["naming_other_stuff_" + (o + 1) + "_link"] = i, 
                                e.length == o + 1 && a(n);
                            });
                        });
                    },
                    moress: function moress(t) {
                        var n = this, a = this, i = [], o = [];
                        t.path.some(function(r, s) {
                            e.uploadFile({
                                url: getApp().globalData.url + "/api/incoming/upload2",
                                filePath: r,
                                name: "file",
                                header: {
                                    token: getApp().globalData.token
                                },
                                formData: {
                                    id: n.formData.id
                                },
                                success: function success(n) {
                                    var r = JSON.parse(n.data);
                                    1 == r.code ? (r.data.url = getApp().url_htt(r.data.url), i.push(r.data.media_id), 
                                    o.push(r.data.url), t.path.length == o.length && ("license_link" == t.name_dat && (a.formData.license_link = r.data.url, 
                                    a.formData.license = r.data.media_id), "otherDetails" == t.name_dat && (a.formData.otherDetails = o.join(","), 
                                    a.formData.otherDetailsmediaid = i.join(","))), setTimeout(function() {
                                        e.hideLoading();
                                    }, 200)) : e.showToast({
                                        title: r.message,
                                        icon: "none"
                                    });
                                },
                                fail: function fail() {
                                    e.hideLoading();
                                }
                            });
                        });
                    }
                },
                onLoad: function onLoad(e) {
                    this.order_id = e.order_id, e.id && (this.formData.id = e.id);
                }
            };
            t.default = r;
        }).call(this, n("543d")["default"]);
    },
    c90e: function c90e(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("2c3f"), i = n("e1e7");
        for (var o in i) {
            [ "default" ].indexOf(o) < 0 && function(e) {
                n.d(t, e, function() {
                    return i[e];
                });
            }(o);
        }
        n("3a94");
        var r = n("f0c5"), s = Object(r["a"])(i["default"], a["b"], a["c"], !1, null, null, null, !1, a["a"], void 0);
        t["default"] = s.exports;
    },
    e039: function e039(e, t, n) {
        "use strict";
        (function(e) {
            var t = n("4ea4");
            n("4ebd");
            t(n("66fd"));
            var a = t(n("c90e"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = n, e(a.default);
        }).call(this, n("543d")["createPage"]);
    },
    e1e7: function e1e7(e, t, n) {
        "use strict";
        n.r(t);
        var a = n("c63a"), i = n.n(a);
        for (var o in a) {
            [ "default" ].indexOf(o) < 0 && function(e) {
                n.d(t, e, function() {
                    return a[e];
                });
            }(o);
        }
        t["default"] = i.a;
    }
}, [ [ "e039", "common/runtime", "common/vendor" ] ] ]);