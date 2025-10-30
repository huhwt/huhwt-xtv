/*! pagemap v1.4.0 - https://larsjung.de/pagemap/ */
/*! EW.H - MOD 2024 - a little bit of reengeneering: revive function names, add some functionality */
!function(e, t) {
    "object" == typeof exports && "object" == typeof module ? module.exports = black() : "function" == typeof define && define.amd ? define("pagemap", [], t) : "object" == typeof exports ? exports.pagemap = t() : e.pagemap = t()
}("undefined" != typeof self ? self : this, function() {
    return r = {},
    o.m = n = [function(e, t, n) {
            (function(global) {
                e.exports = function(canvas, e) {
                    function black(e) {
                        return "rgba(0,0,0,".concat(e / 100, ")")
                    }
                    function _listener(t, n, e, r) {
                        return e.split(/\s+/).forEach(function(e) {
                            return t[n](e, r)
                        })
                    }
                    function ev_on(e, t, n) {
                        return _listener(e, "addEventListener", t, n)
                    }
                    function ev_off(e, t, n) {
                        return _listener(e, "removeEventListener", t, n)
                    }
                    function RECT(e, t, n, r) {
                        return {
                            x: e,
                            y: t,
                            w: n,
                            h: r
                        }
                    }
                    function rect_rel_to(e, t) {
                        var n = 1 < arguments.length && void 0 !== t ? t : {
                            x: 0,
                            y: 0
                        };
                        return RECT(e.x - n.x, e.y - n.y, e.w, e.h)
                    }
                    function el_get_offset(e) {
                        var t = e.getBoundingClientRect();
                        return {
                            x: t.left + WIN.pageXOffset,
                            y: t.top + WIN.pageYOffset
                        }
                    }
                    function rect_of_viewport(e) {
                        var t = el_get_offset(e)
                        , n = t.x
                        , r = t.y;
                        return RECT(n + e.clientLeft, r + e.clientTop, e.clientWidth, e.clientHeight)
                    }
                    function rect_of_content(e) {
                        var t = el_get_offset(e)
                        , n = t.x
                        , r = t.y;
                        return RECT(n + e.clientLeft - e.scrollLeft, r + e.clientTop - e.scrollTop, e.scrollWidth, e.scrollHeight)
                    }
                    function rect_of_doc() {
                        return RECT(0, 0, DOC_EL.scrollWidth, DOC_EL.scrollHeight)
                    }
                    function rect_of_win() {
                        return RECT(WIN.pageXOffset, WIN.pageYOffset, DOC_EL.clientWidth, DOC_EL.clientHeight)
                    }
                    function draw_rect(e, fst) {
                        fst && (CTX.fillStyle = fst,
                                CTX.beginPath(),
                                CTX.rect(e.x, e.y, e.w, e.h),
                                CTX.fill()
                                )
                    }
                    function draw_box(e) {
                        CTX.fillRect(e.x, e.y, e.w, e.h);
                    }
                    function draw_box(e) {
                        CTX.fillRect(e.x, e.y, e.w, e.h);
                    }
                    function draw_stroke(e, fst) {
                        fst && (CTX.strokeStyle = fst,
                                CTX.strokeRect(e.x, e.y, e.w, e.h)
                                )
                        }
                   function draw() {
                        var e, t, n, r, o, i, stx, st;
                        m = VIEWPORT ? rect_of_content(VIEWPORT)
                                    : rect_of_doc();
                        x = VIEWPORT ? rect_of_viewport(VIEWPORT) 
                                    : rect_of_win();
                        scaleXY = calc_scale(m.w, m.h);
                        rescaleXY = 1 / scaleXY;
                        o = m.w * scaleXY;
                        i = m.h * scaleXY;
                        canvas.width = o;
                        canvas.height = i;
                        canvas.style.width = "".concat(o, "px");
                        canvas.style.height = "".concat(i, "px");
                        CTX.setTransform(1, 0, 0, 1, 0, 0);
                        CTX.clearRect(0, 0, canvas.width, canvas.height);
                        CTX.scale(scaleXY, scaleXY);
                        draw_rect(rect_rel_to(m, m), SETTINGS.back);
                        apply_styles(SETTINGS.styles);
                        CTX.lineWidth = 8;
                        draw_stroke(rect_rel_to(x, m), is_drag ? SETTINGS.drag : SETTINGS.view);
                        CTX.lineWidth = 1;
                        draw_rect(rect_rel_to(x, m), SETTINGS.lens);
                    }
                    function apply_styles(st) {
                        Object.keys(st).forEach(function(stk) {
                            var _s, stv = st[stk], fst = stv[0], mst = stv[1];
                            _s = stk,
                            CTX.fillStyle = fst,
                            Array.from((VIEWPORT || DOC).querySelectorAll(_s))
                                .forEach(function(el) {
                                    let _e, n, r, o;
                                    if (!el.classList.contains('not-visible')) {
                                        if (el.checkVisibility({visibilityProperty: true})) {
                                            draw_box(rect_rel_to((n = el_get_offset(_e = el),
                                                r = n.x,
                                                o = n.y,
                                                RECT(r, o, el.offsetWidth, el.offsetHeight)), m)
                                            )
                                        }
                                    }
                            })
                            if (mst) {
                                CTX.fillStyle = mst,
                                Array.from((VIEWPORT || DOC).querySelectorAll(_s))
                                .forEach(function(el) {
                                    let _e, n, sx, sye, sw, sh, sy;
                                    if (!el.classList.contains('not-visible')) {
                                        if (el.checkVisibility({visibilityProperty: true})) {
                                            draw_box(rect_rel_to((n = el_get_offset(_e = el),
                                                sx = 0,
                                                sy = n.y + el.offsetHeight/2,
                                                sye = sy - 3,
                                                sy = (sye < n.y) ? sy = sye : sye = sye,
                                                sw = canvas.width * rescaleXY, sh = 4 * rescaleXY,
                                                RECT(sx, sy, sw, sh)), m)
                                            )
                                        }
                                    }
                                })
                            }
                        })
                    }
                    function settings(e) {
                        return Object.assign({
                            viewport: null,
                            // Caveat: Shapes are drawn on top of each other on the canvas...
                            // We want to set special markers for special shapes, so the most significant ones must be last in the order
                            styles: {
                                "div.tv_box.def": [ "rgba(0,0,0,0.3)", null ],
                                "div.tv_box.selectedID": [ "rgb(241, 241, 11)", "rgb(241, 241, 11)" ],
                                "div.tv_box.selectedGL": [ "rgb(203, 11, 241)", null ],
                                "div.tv_box.selectedGL.selectedID": [ "rgb(148, 191, 19)", "rgb(148, 191, 19)" ],
                                "div.tv_box.rootPerson": [ "rgba(108, 155, 242, 0.8)", "rgb(108, 155, 242)" ],
                            },
                            back: black(2),
                            view: black(64),
                            drag: black(80),
                            lens: "rgba(240, 240, 77, 0.2)",
                            interval: null
                            }, e)
                    }
                    function on_drag(e) {
                        e.preventDefault();
                        var t = rect_of_viewport(canvas)
                        , n = (e.pageX - t.x) / scaleXY - x.w * w
                        , r = (e.pageY - t.y) / scaleXY - x.h * j;
                        VIEWPORT ? (VIEWPORT.scrollLeft = n,
                                    VIEWPORT.scrollTop = r)
                                : WIN.scrollTo(n, r);
                        draw()
                    }
                    function on_drag_end(e) {
                        is_drag = false,
                        canvas.style.cursor = "pointer",
                        BODY.style.cursor = "auto",
                        ev_off(WIN, "mousemove", on_drag),
                        ev_off(WIN, "mouseup", on_drag_end),
                        on_drag(e)
                    }
                    function on_drag_start(e) {
                        is_drag = true;
                        var t = rect_of_viewport(canvas)
                        , n = rect_rel_to(x, m);
                        w = ((e.pageX - t.x) / scaleXY - n.x) / n.w,
                        j = ((e.pageY - t.y) / scaleXY - n.y) / n.h,
                        (w < 0 || 1 < w || j < 0 || 1 < j) && (j = w = .5),
                        canvas.style.cursor = "crosshair",
                        BODY.style.cursor = "crosshair",
                        ev_on(WIN, "mousemove", on_drag),
                        ev_on(WIN, "mouseup", on_drag_end),
                        on_drag(e)
                    }
                    var g, v, m, x, scaleXY, rescaleXY, w, j,
                    WIN = global.window, DOC = WIN.document, DOC_EL = DOC.documentElement, BODY = DOC.querySelector("body"),
                    CTX = canvas.getContext("2d"),
                    SETTINGS = settings(e),
                    calc_scale = (g = canvas.clientWidth, v = canvas.clientHeight,
                                function(e, t) {
                                    return Math.min(g / e, v / t)
                                }),
                    VIEWPORT = SETTINGS.viewport,
                    is_drag = false;
                    return canvas.style.cursor = "pointer",
                        ev_on(canvas, "mousedown", on_drag_start),
                        ev_on(VIEWPORT || WIN, "load resize scroll", draw),
                        0 < SETTINGS.interval && setInterval(function() {
                            return draw()
                        }, SETTINGS.interval),
                        draw(),
                        { redraw: draw }
                }
            }
            ).call(this, n(1))
        }
        , function(e, t) {
            var n;
            n = function() {
                return this
            }();
            try {
                n = n || new Function("return this")()
            } catch (e) {
                "object" == typeof window && (n = window)
            }
            e.exports = n
        }
    ],
    o.c = r,
    o.d = function(e, t, n) {
        o.o(e, t) || Object.defineProperty(e, t, {
            enumerable: !0,
            get: n
        })
    }
    ,
    o.r = function(e) {
        "undefined" != typeof Symbol && Symbol.toStringTag && Object.defineProperty(e, Symbol.toStringTag, {
            value: "Module"
        }),
        Object.defineProperty(e, "__esModule", {
            value: !0
        })
    }
    ,
    o.t = function(t, e) {
        if (1 & e && (t = o(t)),
        8 & e)
            return t;
        if (4 & e && "object" == typeof t && t && t.__esModule)
            return t;
        var n = Object.create(null);
        if (o.r(n),
        Object.defineProperty(n, "default", {
            enumerable: !0,
            value: t
        }),
        2 & e && "string" != typeof t)
            for (var r in t)
                o.d(n, r, function(e) {
                    return t[e]
                }
                .bind(null, r));
        return n
    }
    ,
    o.n = function(e) {
        var t = e && e.__esModule ? function() {
            return e.default
        }
        : function() {
            return e
        }
        ;
        return o.d(t, "a", t),
        t
    }
    ,
    o.o = function(e, t) {
        return Object.prototype.hasOwnProperty.call(e, t)
    }
    ,
    o.p = "",
    o(o.s = 0);
    function o(e) {
        if (r[e])
            return r[e].exports;
        var t = r[e] = {
            i: e,
            l: !1,
            exports: {}
        };
        return n[e].call(t.exports, t, t.exports, o),
        t.l = !0,
        t.exports
    }
    var n, r
});
