(global["webpackJsonp"] = global["webpackJsonp"] || []).push([ [ "pages/index/indexs" ], {
    "0eef": function eef(t, e, a) {},
    1299: function _(t, e, a) {
        "use strict";
        var i = a("367d"), n = a.n(i);
        n.a;
    },
    "1b51": function b51(t, e, a) {
        "use strict";
        a.r(e);
        var i = a("e52e"), n = a.n(i);
        for (var o in i) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                a.d(e, t, function() {
                    return i[t];
                });
            }(o);
        }
        e["default"] = n.a;
    },
    "236c": function c(t, e, a) {
        "use strict";
        (function(t) {
            var e = a("4ea4");
            a("4ebd");
            e(a("66fd"));
            var i = e(a("c156"));
            wx.__webpack_require_UNI_MP_PLUGIN__ = a, t(i.default);
        }).call(this, a("543d")["createPage"]);
    },
    "367d": function d(t, e, a) {},
    "5d3f": function d3f(t, e, a) {
        "use strict";
        var i = a("0eef"), n = a.n(i);
        n.a;
    },
    bcf2: function bcf2(t, e, a) {
        "use strict";
        a.d(e, "b", function() {
            return i;
        }), a.d(e, "c", function() {
            return n;
        }), a.d(e, "a", function() {});
        var i = function i() {
            var t = this.$createElement, e = (this._self._c, this.imgs ? this.__get_style([ this.heights ]) : null), a = this.__get_style([ this.heights ]);
            this.$mp.data = Object.assign({}, {
                $root: {
                    s0: e,
                    s1: a
                }
            });
        }, n = [];
    },
    c156: function c156(t, e, a) {
        "use strict";
        a.r(e);
        var i = a("bcf2"), n = a("1b51");
        for (var o in n) {
            [ "default" ].indexOf(o) < 0 && function(t) {
                a.d(e, t, function() {
                    return n[t];
                });
            }(o);
        }
        a("5d3f"), a("1299");
        var r = a("f0c5"), l = Object(r["a"])(n["default"], i["b"], i["c"], !1, null, null, null, !1, i["a"], void 0);
        e["default"] = l.exports;
    },
    e52e: function e52e(t, e, a) {
        "use strict";
        (function(t) {
            var i = a("4ea4");
            Object.defineProperty(e, "__esModule", {
                value: !0
            }), e.default = void 0;
            var n = i(a("a8c7")), o = {
                onShareAppMessage: function onShareAppMessage(t) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                onShareTimeline: function onShareTimeline(t) {
                    return {
                        title: this.title,
                        path: "/pages/index/index?invite_code=" + getApp().globalData.ma,
                        imageUrl: this.iamge
                    };
                },
                data: function data() {
                    return {
                        banners: "",
                        heights: "",
                        ad: "",
                        title: "",
                        iamge: "",
                        invite_code: "",
                        pro_status: "",
                        aliProStatus: "",
                        kefu: "",
                        login_one: "",
                        src: "",
                        imgs: !1,
                        registeredSwitch: 0,
                        indexTitle: ""
                    };
                },
                methods: {
                    alipayRegister: function alipayRegister() {
                        "" !== getApp().globalData.token ? t.navigateTo({
                            url: "/pages/alipayRegister/alipayRegister?pf_id=" + getApp().globalData.platform
                        }) : getApp().login();
                    },
                    adminUrl: function adminUrl() {
                        "" !== getApp().globalData.token ? t.navigateTo({
                            url: "/pages/AdministratorRegistration/AdministratorRegistration?what=index&pf_id=" + getApp().globalData.platform
                        }) : getApp().login();
                    },
                    skip: function skip(e) {
                        if ("" !== getApp().globalData.token) {
                            var a = e.currentTarget.dataset.sort;
                            switch (!0) {
                              case "firm" == a:
                                t.navigateTo({
                                    url: "../firm/firm?pf_id=" + getApp().globalData.platform
                                });
                                break;

                              case "person" == a:
                                t.navigateTo({
                                    url: "../person/person?&pf_id=" + getApp().globalData.platform
                                });
                                break;
                            }
                        } else getApp().login();
                    },
                    shops: function shops(e) {
                        if ("" !== getApp().globalData.token) {
                            var a = {
                                type: e.currentTarget.dataset.sort
                            };
                            getApp().request("POST", "/api/incoming/select_merchant_type", {
                                token: getApp().globalData.token
                            }, a).then(function(a) {
                                1 == a.code && t.navigateTo({
                                    url: "../facilitator/facilitator?order_id=" + a.data.order_id + "&type=" + e.currentTarget.dataset.sort + "&pf_id=" + getApp().globalData.platform
                                });
                            });
                        } else getApp().login();
                    },
                    swiper: function swiper(e, a, i) {
                        1 == e ? t.navigateToMiniProgram({
                            appId: this.banners[a].appid,
                            path: this.banners[a].path,
                            success: function success(t) {}
                        }) : 2 == e ? t.navigateTo({
                            url: "../ke/web?url=" + i.urls + "&pf_id=" + getApp().globalData.platform
                        }) : t.makePhoneCall({
                            phoneNumber: this.banners[a].urls
                        });
                    },
                    ke: function ke() {
                        2 == this.kefu.type ? wx.openCustomerServiceChat({
                            extInfo: {
                                url: this.kefu.url
                            },
                            corpId: this.kefu.company_id,
                            success: function success(t) {
                                console.log(t);
                            },
                            fail: function fail(t) {
                                console.log(t);
                            }
                        }) : t.navigateTo({
                            url: "../ke/ke?url=" + this.kefu.url + "&pf_id=" + getApp().globalData.platform
                        });
                    },
                    faq: function faq() {
                        t.navigateTo({
                            url: "/pages/help/help?pf_id=" + getApp().globalData.platform
                        });
                    },
                    ban_list: function ban_list(e) {
                        var a = this;
                        return new Promise(function(i, n) {
                            setTimeout(function() {
                                var n = t.createSelectorQuery().in(a).select(".test");
                                n.fields({
                                    size: !0,
                                    scrollOffset: !0
                                }, function(a) {
                                    t.getImageInfo({
                                        src: e,
                                        success: function success(t) {
                                            var e = {
                                                0: a,
                                                1: t
                                            };
                                            i(e);
                                        }
                                    });
                                }).exec();
                            }, 200);
                        });
                    }
                },
                onLoad: function onLoad(e) {
                    var a = this;
                    if (this.indexTitle = t.getStorageSync("indexTitle"), getApp().request("POST", "/api/kefu", {}, {
                        type: 1,
                        pf_id: getApp().globalData.platform
                    }).then(function(e) {
                        a.kefu = e.data, t.setStorageSync("spa_kefu", e.data);
                    }), e.invite_code) {
                        var i = e.invite_code.split("=")[1];
                        t.setStorageSync("invite_code", i);
                    }
                    getApp().request("GET", "/api/incoming/pro_status", {}, {
                        pf_id: getApp().globalData.platform
                    }).then(function(t) {
                        a.pro_status = t.data.pro_status;
                    }), getApp().request("GET", "/api/aliProStatus", {}, {
                        pf_id: getApp().globalData.platform
                    }).then(function(t) {
                        a.aliProStatus = t.data.ali_pro_status;
                    }), this.title = t.getStorageSync("title");
                    var o = t.getStorageSync("image");
                    -1 == o.indexOf("https") && (this.iamge = getApp().url_htt(o)), this.login_one = t.getStorageSync("login_one"), 
                    getApp().request("POST", "/api/banner", {}, {
                        pf_id: getApp().globalData.platform
                    }).then(function(t) {
                        if (0 == t.data.length) a.imgs = !1; else {
                            var e = "";
                            e = -1 == t.data[0].img_url.indexOf("http") ? getApp().globalData.url + t.data[0].img_url : t.data[0].img_url, 
                            a.imgs = !0, a.ban_list(e).then(function(e) {
                                var i = e[0].width / e[1].width;
                                a.heights = {
                                    height: e[1].height * i + "px"
                                };
                                for (var n = 0; n < t.data.length; n++) {
                                    -1 == t.data[n].img_url.indexOf("https") && (t.data[n].img_url = getApp().url_htt(t.data[n].img_url));
                                }
                                a.banners = t.data;
                            });
                        }
                    }), getApp().request("POST", "/api/index/xcx_pian_status", {}, {
                        pf_id: getApp().globalData.platform
                    }).then(function(t) {
                        console.log(t), 1 == t.code && (a.registeredSwitch = t.data);
                    }), this.ad = getApp().globalData.ad.ad_banner, this.invite_code = getApp().globalData.ma;
                    var r = getApp().globalData.ad.ad_popup;
                    n.default.interstitial.load(r), n.default.interstitial.show();
                },
                onShow: function onShow() {
                    this.qian = t.getStorageSync("qian"), "" != t.getStorageSync("token") && "" == t.getStorageSync("nickName") && (t.reLaunch({
                        url: "/pages/login/login"
                    }), t.showToast({
                        title: "请设置用户信息",
                        icon: "none"
                    }));
                }
            };
            e.default = o;
        }).call(this, a("543d")["default"]);
    }
}, [ [ "236c", "common/runtime", "common/vendor" ] ] ]);