window.PensoPay = window.PensoPay || {}, window.PensoPay.Embedded = window.PensoPay.Embedded || {}, function (e) {
    var t = e.PensoPay.Embedded;
    t.CardDetails = function (e) {
        var t = this, n = function () {
            t.debug = !1, t.selectedPaymentMethods = null, t.cardNumber = "", t.expiration = ["", ""], t.cvd = "", t.bin = null, t.paymentMethod = null, t.cardNumberValid = !1, t.expirationValid = !1, t.cvdValid = !1, t.valid = !1, t.callbacks = {}
        };
        t.setCardNumber = function (e) {
            t.cardNumber !== e && (t.cardNumber = e, r())
        }, t.setExpiration = function (e) {
            t.expiration.toString() !== e.toString() && (t.expiration = e, i())
        }, t.setCvd = function (e) {
            t.cvd !== e && (t.cvd = e, o())
        }, t.on = function (e, n) {
            t.callbacks[e] || (t.callbacks[e] = []), t.callbacks[e].push(n)
        };
        var a = function (e, n) {
            if (t.callbacks[e]) {
                var a = t.callbacks[e];
                a.forEach(function (e) {
                    e(n)
                })
            }
        }, r = function () {
            x("New cardNumber:", t.cardNumber), a("cardNumberChanged", t.cardNumber), f();
            var e = g();
            e !== t.bin && (t.bin = e, d())
        }, i = function () {
            x("New expiration", t.expiration), a("expirationChanged", t.expiration), m()
        }, o = function () {
            x("New CVD", t.cvd), a("cvdChanged", t.cvd), h()
        }, d = function () {
            x("New bin:", t.bin), a("binChanged", t.bin), null !== t.paymentMethod && (t.paymentMethod = null, u()), t.bin && y(t.bin, function (e) {
                e = E(e), b(e) ? (t.paymentMethod = e, u()) : (f(), a("paymentMethodInvalid", e))
            })
        }, u = function () {
            x("New paymentMethod:", t.paymentMethod), a("paymentMethodChanged", t.paymentMethod), f(), h()
        }, s = function () {
            x("Card number is now " + (t.cardNumberValid ? "valid" : "invalid")), a("cardNumberValidChanged", t.cardNumberValid), v()
        }, c = function () {
            x("Expiration date is now " + (t.expirationValid ? "valid" : "invalid")), a("expirationValidChanged", t.expirationValid), v()
        }, l = function () {
            x("CVD is now " + (t.cvdValid ? "valid" : "invalid")), a("cvdValidChanged", t.cvdValid), v()
        }, p = function () {
            x("CardDetails are now " + (t.valid ? "valid" : "invalid")), a("validChanged", t.valid)
        }, f = function () {
            var e = function () {
                return null === t.paymentMethod ? !1 : t.paymentMethod.cardnumber_length[0] > t.cardNumber.length || t.cardNumber.length > t.paymentMethod.cardnumber_length[1] ? !1 : !!b(t.paymentMethod)
            };
            valid = e(), valid !== t.cardNumberValid && (t.cardNumberValid = valid, s())
        }, m = function () {
            var e = function (e, t) {
                var n = new Date, a = n.getMonth() + 1, r = parseInt((n.getFullYear() + "").substr(2, 2), 10);
                return e.match(/^[0-1]{1}[0-9]{1}$/) && t.match(/^[0-9]{2}$/) ? (t = parseInt(t, 10), e = parseInt(e, 10), r > t ? !1 : t === r && a > e ? !1 : !(1 > e || e > 12)) : !1
            }, n = t.expiration[0], a = t.expiration[1];
            valid = e(n, a), t.expirationValid !== valid && (t.expirationValid = valid, c())
        }, h = function () {
            var e = function (e) {
                return t.paymentMethod ? t.paymentMethod.cvd_length ? e.match(/^[0-9]+$/) ? t.paymentMethod.cvd_length === t.cvd.length : !1 : !0 : !1
            };
            valid = e(t.cvd), t.cvdValid !== valid && (t.cvdValid = valid, l())
        }, v = function () {
            valid = t.cardNumberValid && t.expirationValid && t.cvdValid, t.valid !== valid && (t.valid = valid, p())
        }, g = function () {
            return t.cardNumber.length >= 6 ? t.cardNumber.substr(0, 6) : null
        }, y = function (t, n, a) {
            a = a || function () {
            };
            var r = new XMLHttpRequest;
            r.open("GET", e + "/payment-methods?bin=" + t), r.timeout = 3e4, r.setRequestHeader("Accept", "application/json"), r.onreadystatechange = function () {
                if (4 === r.readyState) {
                    if (200 !== r.status) return a(r.responseText || r.statusText);
                    var e = JSON.parse(r.responseText);
                    n(e)
                }
            }, r.send(null)
        }, b = function (e) {
            if (!t.selectedPaymentMethods) return !0;
            var n = e.brand, a = e.country_alpha2, r = t.selectedPaymentMethods.indexOf(n) >= 0;
            return (r = r || t.selectedPaymentMethods.indexOf("3d-" + n) >= 0) ? t.lock ? (a && (a = a.toLowerCase()), valid = !1, lockTokens = t.lock.split(","), lockTokens.forEach(function (t) {
                var r = t.match(/^(!)?(.*?)(?:-([a-z]{2}))?$/);
                if (r) {
                    var i = "!" === r[1], o = r[2], d = r[3], u = o === n || o === "3d-" + n, s = !a || !d || a === d;
                    u && s && (valid = !i, x("Matching payment method and token.", t, e))
                }
            }), valid || x("Did not match payment method with lock.", e, t.lock), valid) : !0 : (x("Denying payment method. Is not in list of selected payment methods:", e), !1)
        }, E = function (e) {
            var t = e[0];
            return 2 === e.length && (t = e.filter(function (e) {
                return "visa" === e.brand
            })[0]), t
        }, x = function () {
            return t.debug ? console.log.apply(console, arguments) : void 0
        };
        n()
    }
}(window, document), function (e) {
    var t = e.PensoPay.Embedded.Card = {}, n = {apiUrl: "/card", testmode: !1}, a = function (e, t) {
        return Array(Math.max(t - String(e).length + 1, 0)).join(0) + e
    }, r = function () {
        var e = -1;
        if ("Microsoft Internet Explorer" === navigator.appName) {
            var t = navigator.userAgent, n = new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})");
            null !== n.exec(t) && (e = parseFloat(RegExp.$1))
        }
        return -1 !== e && 10 > e
    }, i = function (e, n, a) {
        var r = new XMLHttpRequest;
        r.open("GET", e), r.timeout = 3e4, r.setRequestHeader("Accept", "application/json"), r.onreadystatechange = function () {
            if (4 == r.readyState) {
                if (r.status > 399) return a(r.responseText || r.statusText);
                var o = JSON.parse(r.responseText);
                "ok" == o.status ? o.done ? n(new t.Token(o.data)) : setTimeout(function () {
                    i(e, n, a)
                }, 5e3) : a(o.qp_status_msg || o.message, o)
            }
        }, r.send(null)
    };
    t.createToken = function (e, t) {
        for (var o in n) n.hasOwnProperty(o) && !t.hasOwnProperty(o) && (t[o] = n[o]);
        if (r()) throw"IE8 AND IE9 not supported";
        var d = new XMLHttpRequest;
        d.open("POST", t.base_url + "/embedded/v2/cards"), d.timeout = 3e4, d.onreadystatechange = function () {
            if (4 == d.readyState) {
                if (d.status > 399) return t.failure(d.statusText, d.responseText);
                var e = JSON.parse(d.responseText);
                "ok" === e.status ? i(e.poll_url, t.success, t.failure) : t.failure(e.message, e)
            }
        }, d.setRequestHeader("Accept", "application/json");
        var u = new FormData;
        u.append("merchant_id", e.merchant_id), u.append("agreement_id", e.agreement_id), u.append("card[number]", e.cardnumber), u.append("card[year]", a(e.year, 2)), u.append("card[month]", a(e.month, 2)), u.append("card[cvd]", e.cvd), d.send(u)
    }, t.Token = function (e) {
        for (var t in e) e.hasOwnProperty(t) && (this[t] = e[t])
    }
}(window, document), function (e) {
    var t = e.PensoPay.Embedded;
    t.Fee = function (e, t) {
        var n = this;
        n.base_url = e, n.session_id = t
    }, t.Fee.prototype.get = function (e, t, n, a) {
        var r = this, i = new XMLHttpRequest;
        i.open("POST", r.base_url + "/calculate_fee"), i.timeout = 3e4, i.setRequestHeader("Accept", "application/json"), i.onreadystatechange = function () {
            if (4 === i.readyState) {
                if (200 !== i.status) return a(i.status, i.responseText || i.statusText);
                var e = JSON.parse(i.responseText);
                n(e)
            }
        };
        var o = new FormData;
        o.append("card_number", e), o.append("session_id", r.session_id), t && "" !== t && o.append("acquirer", t), i.send(o)
    }
}(window, document), function (e) {
    var t = e.PensoPay.Embedded, n = function (e, t) {
        return Array(Math.max(t - String(e).length + 1, 0)).join(0) + e
    }, a = function (e, t, n) {
        var r = new XMLHttpRequest;
        r.open("GET", e), r.timeout = 3e4, r.setRequestHeader("Accept", "application/json"), r.onreadystatechange = function () {
            if (4 === r.readyState) {
                if (r.status > 399) return n(r.status, r.responseText || r.statusText);
                var i = JSON.parse(r.responseText);
                "ok" === i.status ? i.done ? t(i.qp_status_code, i.qp_status_msg) : setTimeout(function () {
                    a(e, t, n)
                }, 5e3) : n(i.qp_status_code, i.qp_status_msg)
            }
        }, r.send(null)
    };
    t.Link = function (e, t) {
        var n = this;
        n.initialized = !1, n.base_url = e;
        var a = t.match(/\/(cards|payments|subscriptions)\/([a-fA-F0-9]+)$/);
        n.type = a[1], n.token = a[2]
    }, t.Link.prototype.init = function (e, t) {
        var n = this;
        n.get(function (t) {
            n.session = t.session_id, n.data = t.link, n.initialized = !0, e(t)
        }, t)
    }, t.Link.prototype.authorize = function (e, t, r, i) {
        var o = this;
        if (!o.initialized) return setTimeout(function () {
            o.authorize(e, t, r, i)
        }, 100);
        var d = new XMLHttpRequest, u = o.base_url + "/embedded/v2/" + o.session + "/authorize", s = function (e) {
            "3d-secure" === e.status ? i(e, s) : "ok" === e.status ? a(e.poll_url, t, r) : r(e.message, e)
        };
        d.open("POST", u), d.timeout = 3e4, d.onreadystatechange = function () {
            if (4 === d.readyState) {
                if (d.status > 399) return r(d.statusText, d.responseText);
                var e = JSON.parse(d.responseText);
                s(e)
            }
        }, d.setRequestHeader("Accept", "application/json");
        var c = new FormData;
        c.append("card[number]", e.cardnumber), c.append("card[year]", n(e.year, 2)), c.append("card[month]", n(e.month, 2)), c.append("card[cvd]", e.cvd), e.hasOwnProperty("force_3d") && c.append("card[force_3d]", e.force_3d), d.send(c)
    }, t.Link.prototype.get = function (e, t) {
        var n = this, a = n.base_url + "/embedded/v2/" + n.type + "/" + n.token, r = new XMLHttpRequest;
        r.open("GET", a), r.timeout = 3e4, r.onreadystatechange = function () {
            if (4 === r.readyState) {
                var n = r.responseText;
                return r.getResponseHeader("Content-Type") && "application/json" === r.getResponseHeader("Content-Type") && (n = JSON.parse(r.responseText)), 200 !== r.status ? t(r.statusText, n) : void e(n)
            }
        }, r.setRequestHeader("Accept", "application/json"), r.send()
    }, t.Link.prototype.fee = function (e, n, a) {
        var r = this;
        if (r.data.auto_fee) {
            var i = new t.Fee(r.base_url, r.session);
            i.get(e, "clearhaus", n, a)
        }
    }
}(window, document), function (e, t) {
    var n = e.PensoPay.Embedded, a = {}, r = function (e) {
        return e.trim ? e.trim() : e.replace(new RegExp("s", "gu"), "")
    }, i = function () {
        var e = -1;
        if ("Microsoft Internet Explorer" === navigator.appName) {
            var t = navigator.userAgent, n = new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})");
            null !== n.exec(t) && (e = parseFloat(RegExp.$1))
        }
        return -1 !== e && 10 > e
    };
    n.Form = function (e, a) {
        if (i()) throw"IE8 AND IE9 not supported";
        if ("string" == typeof e && (e = t.querySelector(e)), !(this instanceof n.Form)) return new n.Form(e, a);
        var r = this;
        if ("object" != typeof a) throw"Invalid or missing PensoPay.Embedded.Form config";
        if (a.hasOwnProperty("base_url") && 0 !== a.base_url.length || (a.base_url = r.base_url), a.hasOwnProperty("payment_link")) r.link = new n.Link(a.base_url, a.payment_link); else {
            if (!a.hasOwnProperty("merchant_id") || 0 === a.merchant_id.length) throw" PensoPay.Embedded.Form merchant_id config is missing";
            if (!a.hasOwnProperty("agreement_id") || 0 === a.agreement_id.length) throw" PensoPay.Embedded.Form agreement_id config is missing"
        }
        r.form = e, r.eventListeners = {}, r.config = r.parseConfig(a), r.cardDetails = new n.CardDetails(a.base_url), r.completed = !1, r.verifyForm(), r.initEvents(), r.link ? r.link.init(function (e) {
            r.cardDetails.selectedPaymentMethods = e.payment_methods, r.fireEvent("init", r, e.link)
        }, function (e, t) {
            r.fireEvent("failure", r, "invalid_payment_link", t)
        }) : r.fireEvent("init", r, {})
    }, n.Form.prototype.parseConfig = function (e) {
        for (var t in a) a.hasOwnProperty(t) && !e.hasOwnProperty(t) && (e[t] = a[t]);
        return e
    }, n.Form.prototype.verifyForm = function () {
        var e = this, t = e.getCardnumberField(), n = e.getExpirationField(), a = e.getExpMonthField(),
            r = e.getExpYearField(), i = e.getCVDField();
        if (null === t) throw'Unable to find form input field with data-pensopay="cardnumber"';
        if (t.getAttribute("name")) throw"Card number field must NOT have a name attribute";
        if (null === n && (null === a || null === r)) throw'Unable to find form input field(s) with data-pensopay="expiration" or data-pensopay="exp-month" and data-pensopay="exp-year"';
        if (n && n.getAttribute("name")) throw"Expiration date field must NOT have a name attribute";
        if (a && a.getAttribute("name")) throw"exp-month field must NOT have a name attribute";
        if (r && r.getAttribute("name")) throw"exp-year field must NOT have a name attribute";
        if (null === i) throw'Unable to find form input field with data-pensopay="cvd"';
        if (i.getAttribute("name")) throw"CVD field must NOT have a name attribute"
    }, n.Form.prototype.initEvents = function () {
        var e = this, t = function () {
            var t = e.getExpiration();
            t ? e.cardDetails.setExpiration([t.month, t.year]) : e.cardDetails.setExpiration(["", ""])
        }, n = function (n) {
            13 === n.keyCode && (t(), e.form.submit())
        };
        ["init", "success", "failure", "beforeCreate", "brandChanged", "cardnumberChanged", "expirationChanged", "cvdChanged", "feeChanged", "paymentMethodChanged", "validChanged", "before3DSecure", "after3DSecure"].forEach(function (t) {
            e.config.hasOwnProperty(t) && e.on(t, e.config[t])
        }), e.getCardnumberField().addEventListener("keyup", function () {
            e.cardDetails.setCardNumber(e.getCardnumber())
        });
        var a;
        (a = e.getExpirationField()) && (a.addEventListener("blur", t), a.addEventListener("keyup", n)), (a = e.getExpMonthField()) && a.addEventListener("blur", t), (a = e.getExpYearField()) && (a.addEventListener("blur", t), a.addEventListener("keyup", n)), e.getCVDField().addEventListener("keyup", function () {
            e.cardDetails.setCvd(e.getCVD())
        }), e.cardDetails.on("paymentMethodChanged", function (t) {
            if (e.fireEvent("paymentMethodChanged", t) !== !1 && e.fireEvent("brandChanged", t && t.brand) !== !1) {
                var n = e.getCVDField();
                null === t || null !== t.cvd_length && 0 !== t.cvd_length ? n.removeAttribute("disabled") : (n.value = "", n.setAttribute("disabled", "")), t && e.link && e.link.fee(e.getCardnumber(), function (t) {
                    t.success ? e.fireEvent("feeChanged", e, t.fee, t.total) : e.fireEvent("failure", e, "fee", "No fee available")
                }, function (t, n) {
                    e.fireEvent("failure", e, "fee", n)
                })
            }
        }), e.cardDetails.on("cardNumberValidChanged", function (t) {
            e.fireEvent("cardnumberChanged", e, t)
        }), e.cardDetails.on("expirationValidChanged", function (t) {
            e.fireEvent("expirationChanged", e, t)
        }), e.cardDetails.on("cvdValidChanged", function (t) {
            e.fireEvent("cvdChanged", e, t)
        }), e.cardDetails.on("validChanged", function (t) {
            t || e.fireEvent("failure", e, "validation", "invalid", e.getInvalidFields()), e.fireEvent("validChanged", e, t, e.getInvalidFields())
        }), e.cardDetails.on("paymentMethodInvalid", function () {
            e.fireEvent("failure", e, "validation", "payment-method", ["cardnumber"])
        }), e.form.addEventListener("submit", function (t) {
            if (e.completed) return !0;
            if (t.preventDefault(), e.cardDetails.valid) {
                if (e.config.beforeCreate(e) === !1) return;
                e.link ? e.paymentLinkPay() : e.tokenizeCard()
            } else e.fireEvent("failure", e, "validation", "invalid", e.getInvalidFields())
        })
    }, n.Form.prototype.paymentLinkPay = function () {
        var n = this, a = n.getExpiration(), r = n.link,
            i = {cardnumber: n.getCardnumber(), month: a.month, year: a.year, cvd: n.getCVD()}, o = function (e, a, s) {
                if ("30100" == e) return i.force_3d = "1", void r.authorize(i, o, d, u);
                var c = t.createElement("INPUT");
                c.setAttribute("name", "qp_status_code"), c.setAttribute("value", e), c.setAttribute("type", "hidden"), n.form.appendChild(c), n.completed = !0, n.fireEvent("success", n, {
                    status: e,
                    message: a,
                    raw: s
                }) !== !1 && n.form.submit()
            }, d = function (e, t) {
                n.fireEvent("failure", n, "authorize", t, {status: e})
            }, u = function (a, r) {
                if (n.fireEvent("before3DSecure", n, a)) {
                    var i = '<html><head></head><body onload="document.getElementById(\'secure_3d_form\').submit()"><form id="secure_3d_form" action="' + a.mpi_url + '" method="post">';
                    for (var o in a.form) a.form.hasOwnProperty(o) && (i += '<input type="hidden" name="' + o + '" value="' + a.form[o] + '" />');
                    i += "</form></body></html>";
                    var d = t.createElement("IFRAME");
                    d.setAttribute("id", "pensopay_3dsecure_frame"), d.setAttribute("srcdoc", i), d.setAttribute("width", "402px"), d.setAttribute("height", "404px"), n.form.parentNode.insertBefore(d, n.form);
                    var u = function (t) {
                        n.fireEvent("after3DSecure", n, t.data), n.form.parentNode.removeChild(d), e.removeEventListener("message", u, !1), r(t.data)
                    };
                    e.addEventListener("message", u, !1)
                }
            };
        r.authorize(i, o, d, u)
    }, n.Form.prototype.tokenizeCard = function () {
        var e = this, a = e.getExpiration();
        n.Card.createToken({
            merchant_id: e.config.merchant_id,
            agreement_id: e.config.agreement_id,
            cardnumber: e.getCardnumber(),
            month: a.month,
            year: a.year,
            cvd: e.getCVD()
        }, {
            base_url: e.config.base_url, success: function (n) {
                var a = t.createElement("INPUT");
                a.setAttribute("name", "card_token"), a.setAttribute("value", n.token), a.setAttribute("type", "hidden"), e.form.appendChild(a), e.completed = !0, e.fireEvent("success", e, n) !== !1 && e.form.submit()
            }, failure: function (t, n) {
                e.fireEvent("failure", e, "authorize", t, n)
            }
        })
    }, n.Form.prototype.on = function (e, t) {
        var n = this, a = n.eventListeners;
        a.hasOwnProperty(e) || (a[e] = []), a[e].push(t)
    }, n.Form.prototype.fireEvent = function (e) {
        var t = this, n = !0, a = Array.prototype.slice.call(arguments).slice(1);
        if (t.eventListeners.hasOwnProperty(e)) for (var r = t.eventListeners[e], i = 0; i < r.length && (n = n && r[i].apply(t, a) !== !1, n); i++) ;
        return n
    }, n.Form.prototype.getCardnumberField = function () {
        return this.form.querySelector("input[data-pensopay=cardnumber]")
    }, n.Form.prototype.getCardnumber = function () {
        return this.getCardnumberField().value.replace(/ /g, "")
    }, n.Form.prototype.getExpirationField = function () {
        return this.form.querySelector("input[data-pensopay=expiration]")
    }, n.Form.prototype.getExpMonthField = function () {
        return this.form.querySelector("input[data-pensopay=exp-month]")
    }, n.Form.prototype.getExpYearField = function () {
        return this.form.querySelector("input[data-pensopay=exp-year]")
    }, n.Form.prototype.getExpiration = function () {
        var e, t = this.getExpirationField(), n = this.getExpYearField(), a = this.getExpMonthField();
        if (n && a) {
            var r = a.value, i = n.value;
            return 0 === r.length || 0 === i.length ? null : {month: a.value, year: n.value}
        }
        return e = t.value.replace(/[^0-9]/g, ""), 4 !== e.length ? null : {month: e.slice(0, 2), year: e.slice(2, 4)}
    }, n.Form.prototype.getCVDField = function () {
        return this.form.querySelector("input[data-pensopay=cvd]")
    }, n.Form.prototype.getCVD = function () {
        return r(this.getCVDField().value)
    }, n.Form.prototype.getInvalidFields = function () {
        var e = this, t = [];
        return e.cardDetails.cardNumberValid || t.push("cardnumber"), e.cardDetails.expirationValid || (e.getExpMonthField() && e.getExpYearField() ? (t.push("exp-month"), t.push("exp-year")) : t.push("expiration")), e.cardDetails.cvdValid || t.push("cvd"), t
    }
}(window, document), function () {
    var e = "https://payment.quickpay.net";
    PensoPay.Embedded.Form.prototype.base_url = e
}(window, document);