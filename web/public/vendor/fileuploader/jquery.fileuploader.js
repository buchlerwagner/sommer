/**
 * fileuploader
 * Copyright (c) 2020 Innostudio.de
 * Website: https://innostudio.de/fileuploader/
 * Version: 2.2 (12-Mar-2020)
 * License: https://innostudio.de/fileuploader/documentation/#license
 */
! function(e) {
    "use strict";
    "function" == typeof define && define.amd ? define(["jquery"], e) : "undefined" != typeof exports ? module.exports = e(require("jquery")) : e(jQuery)
}(function($) {
    "use strict";
    $.fn.fileuploader = function(q) {
        return this.each(function(t, r) {
            var s = $(r),
                p = null,
                o = null,
                l = null,
                sl = [],
                n = $.extend(!0, {}, $.fn.fileuploader.defaults, q),
                f = {
                    init: function() {
                        return s.closest(".fileuploader").length || s.wrap('<div class="fileuploader"></div>'), p = s.closest(".fileuploader"), f.set("language"), f.set("attrOpts"), f.isSupported() ? (!n.beforeRender || !$.isFunction(n.beforeRender) || !1 !== n.beforeRender(p, s)) && (f.redesign(), n.files && f.files.append(n.files), f.rendered = !0, n.afterRender && $.isFunction(n.afterRender) && n.afterRender(l, p, o, s), f.disabled || f.bindUnbindEvents(!0), s.closest("form").on("reset", f.reset), void(f._itFl.length || f.reset())) : (n.onSupportError && $.isFunction(n.onSupportError) && n.onSupportError(p, s), !1)
                    },
                    bindUnbindEvents: function(e) {
                        e && f.bindUnbindEvents(!1), s[e ? "on" : "off"](f._assets.getAllEvents(), f.onEvent), n.changeInput && o !== s && o[e ? "on" : "off"]("click", f.clickHandler), n.dragDrop && n.dragDrop.container.length && (n.dragDrop.container[e ? "on" : "off"]("drag dragstart dragend dragover dragenter dragleave drop", function(e) {
                            e.preventDefault()
                        }), n.dragDrop.container[e ? "on" : "off"]("drop", f.dragDrop.onDrop), n.dragDrop.container[e ? "on" : "off"]("dragover", f.dragDrop.onDragEnter), n.dragDrop.container[e ? "on" : "off"]("dragleave", f.dragDrop.onDragLeave)), f.isUploadMode() && n.clipboardPaste && $(window)[e ? "on" : "off"]("paste", f.clipboard.paste), n.sorter && n.thumbnails && n.thumbnails._selectors.sorter && f.sorter[e ? "init" : "destroy"]()
                    },
                    redesign: function() {
                        if (o = s, n.theme && p.addClass("fileuploader-theme-" + n.theme), n.changeInput) {
                            switch ((typeof n.changeInput).toLowerCase()) {
                                case "boolean":
                                    o = $('<div class="fileuploader-input"><div class="fileuploader-input-caption"><span>' + f._assets.textParse(n.captions.feedback) + '</span></div><button type="button" class="fileuploader-input-button"><span>' + f._assets.textParse(n.captions.button) + "</span></button></div>");
                                    break;
                                case "string":
                                    " " != n.changeInput && (o = $(f._assets.textParse(n.changeInput, n)));
                                    break;
                                case "object":
                                    o = $(n.changeInput);
                                    break;
                                case "function":
                                    o = $(n.changeInput(s, p, n, f._assets.textParse))
                            }
                            s.after(o), s.css({
                                position: "absolute",
                                "z-index": "-9999",
                                height: "0",
                                width: "0",
                                padding: "0",
                                margin: "0",
                                "line-height": "0",
                                outline: "0",
                                border: "0",
                                opacity: "0"
                            })
                        }
                        n.thumbnails && f.thumbnails.create(), n.dragDrop && (n.dragDrop = "object" != typeof n.dragDrop ? {
                            container: null
                        } : n.dragDrop, n.dragDrop.container = n.dragDrop.container ? $(n.dragDrop.container) : o)
                    },
                    clickHandler: function(e) {
                        e.preventDefault(), f.clipboard._timer ? f.clipboard.clean() : s.click()
                    },
                    onEvent: function(e) {
                        switch (e.type) {
                            case "focus":
                                p && p.addClass("fileuploader-focused");
                                break;
                            case "blur":
                                p && p.removeClass("fileuploader-focused");
                                break;
                            case "change":
                                f.onChange.call(this)
                        }
                        n.listeners && $.isFunction(n.listeners[e.type]) && n.listeners[e.type].call(s, p)
                    },
                    set: function(e, t) {
                        switch (e) {
                            case "attrOpts":
                                for (var i = ["limit", "maxSize", "fileMaxSize", "extensions", "changeInput", "theme", "addMore", "listInput", "files"], r = 0; r < i.length; r++) {
                                    var a = "data-fileuploader-" + i[r];
                                    if (f._assets.hasAttr(a)) switch (i[r]) {
                                        case "changeInput":
                                        case "addMore":
                                        case "listInput":
                                            n[i[r]] = -1 < ["true", "false"].indexOf(s.attr(a)) ? "true" == s.attr(a) : s.attr(a);
                                            break;
                                        case "extensions":
                                            n[i[r]] = s.attr(a).replace(/ /g, "").split(",");
                                            break;
                                        case "files":
                                            n[i[r]] = JSON.parse(s.attr(a));
                                            break;
                                        default:
                                            n[i[r]] = s.attr(a)
                                    }
                                    s.removeAttr(a)
                                }
                                null == s.attr("disabled") && null == s.attr("readonly") && 0 !== n.limit || (f.disabled = !0), (!n.limit || n.limit && 2 <= n.limit) && (s.attr("multiple", "multiple"), n.inputNameBrackets && "[]" != s.attr("name").slice(-2) && s.attr("name", s.attr("name") + "[]")), !0 === n.listInput && (n.listInput = $('<input type="hidden" name="fileuploader-list-' + s.attr("name").replace("[]", "").split("[").pop().replace("]", "") + '">').insertBefore(s)), "string" == typeof n.listInput && 0 == $(n.listInput).length && (n.listInput = $('<input type="hidden" name="' + n.listInput + '">').insertBefore(s)), f.set("disabled", f.disabled), !n.fileMaxSize && n.maxSize && (n.fileMaxSize = n.maxSize);
                                break;
                            case "language":
                                var l = $.fn.fileuploader.languages;
                                "string" == typeof n.captions && (n.captions in l ? n.captions = l[n.captions] : n.captions = $.extend(!0, {}, $.fn.fileuploader.defaults.captions));
                                break;
                            case "disabled":
                                f.disabled = t, p[f.disabled ? "addClass" : "removeClass"]("fileuploader-disabled"), s[f.disabled ? "attr" : "removeAttr"]("disabled", "disabled"), f.rendered && f.bindUnbindEvents(!t);
                                break;
                            case "feedback":
                                t = t || f._assets.textParse(0 < f._itFl.length ? n.captions.feedback2 : n.captions.feedback, {
                                    length: f._itFl.length
                                }), $(!o.is(":file")) && o.find(".fileuploader-input-caption span").html(t);
                                break;
                            case "input":
                                var d = f._assets.copyAllAttributes($('<input type="file">'), s, !0);
                                f.bindUnbindEvents(!1), s.after(s = d).remove(), f.bindUnbindEvents(!0);
                                break;
                            case "prevInput":
                                0 < sl.length && (f.bindUnbindEvents(!1), sl[t].remove(), sl.splice(t, 1), s = sl[sl.length - 1], f.bindUnbindEvents(!0));
                                break;
                            case "nextInput":
                                d = f._assets.copyAllAttributes($('<input type="file">'), s);
                                f.bindUnbindEvents(!1), 0 < sl.length && 0 == sl[sl.length - 1].get(0).files.length ? s = sl[sl.length - 1] : (-1 == sl.indexOf(s) && sl.push(s), sl.push(d), s.after(s = d)), f.bindUnbindEvents(!0);
                                break;
                            case "listInput":
                                n.listInput && n.listInput.val(f.files.list(!0, null, !1, t))
                        }
                    },
                    onChange: function(e, t) {
                        var i = s.get(0).files;
                        if (t) {
                            if (!t.length) return f.set("input", ""), f.files.clear(), !1;
                            i = t
                        }
                        if (f.clipboard._timer && f.clipboard.clean(), !f.isDefaultMode() || (f.reset(), 0 != i.length)) {
                            if (n.beforeSelect && $.isFunction(n.beforeSelect) && 0 == n.beforeSelect(i, l, p, o, s)) return !1;
                            for (var r = 0, a = 0; a < i.length; a++) {
                                var d = i[a],
                                    u = f._itFl[f.files.add(d, "choosed")],
                                    c = f.files.check(u, i, 0 == a);
                                if (!0 === c) n.thumbnails && f.thumbnails.item(u), f.isUploadMode() && f.upload.prepare(u), n.onSelect && $.isFunction(n.onSelect) && n.onSelect(u, l, p, o, s), r++;
                                else if (f.files.remove(u, !0), c[2] || (f.isDefaultMode() && (f.set("input", ""), f.reset(), c[3] = !0), c[1] && f._assets.dialogs.alert(c[1], u, l, p, o, s)), c[3]) break
                            }
                            f.isUploadMode() && 0 < r && f.set("input", ""), f.set("feedback", null), f.isAddMoreMode() && 0 < r && f.set("nextInput"), f.set("listInput", null), n.afterSelect && $.isFunction(n.afterSelect) && n.afterSelect(l, p, o, s)
                        }
                    },
                    thumbnails: {
                        create: function() {
                            null != n.thumbnails.beforeShow && $.isFunction(n.thumbnails.beforeShow) && n.thumbnails.beforeShow(p, o, s);
                            var e = $(f._assets.textParse(n.thumbnails.box)).appendTo(n.thumbnails.boxAppendTo ? n.thumbnails.boxAppendTo : p);
                            l = e.is(n.thumbnails._selectors.list) ? e : e.find(n.thumbnails._selectors.list), n.thumbnails._selectors.popup_open && l.on("click", n.thumbnails._selectors.popup_open, function(e) {
                                e.preventDefault();
                                var t = $(this).closest(n.thumbnails._selectors.item),
                                    o = f.files.find(t);
                                o && o.popup && o.html.hasClass("file-has-popup") && f.thumbnails.popup(o)
                            }), f.isUploadMode() && n.thumbnails._selectors.start && l.on("click", n.thumbnails._selectors.start, function(e) {
                                if (e.preventDefault(), f.locked) return !1;
                                var t = $(this).closest(n.thumbnails._selectors.item),
                                    o = f.files.find(t);
                                o && f.upload.send(o, !0)
                            }), f.isUploadMode() && n.thumbnails._selectors.retry && l.on("click", n.thumbnails._selectors.retry, function(e) {
                                if (e.preventDefault(), f.locked) return !1;
                                var t = $(this).closest(n.thumbnails._selectors.item),
                                    o = f.files.find(t);
                                o && f.upload.retry(o)
                            }), n.thumbnails._selectors.rotate && l.on("click", n.thumbnails._selectors.rotate, function(e) {
                                if (e.preventDefault(), f.locked) return !1;
                                var t = $(this).closest(n.thumbnails._selectors.item),
                                    o = f.files.find(t);
                                o && o.editor && (o.editor.rotate(), o.editor.save())
                            }), n.thumbnails._selectors.remove && l.on("click", n.thumbnails._selectors.remove, function(e) {
                                if (e.preventDefault(), f.locked) return !1;

                                function t(e) {
                                    f.files.remove(i)
                                }
                                var o = $(this).closest(n.thumbnails._selectors.item),
                                    i = f.files.find(o);
                                i && (i.upload && "successful" != i.upload.status ? f.upload.cancel(i) : n.thumbnails.removeConfirmation && !i.choosed ? f._assets.dialogs.confirm(f._assets.textParse(n.captions.removeConfirmation, i), t) : t())
                            })
                        },
                        clear: function() {
                            l && l.html("")
                        },
                        item: function(t, e) {
                            t.icon = f.thumbnails.generateFileIcon(t.format, t.extension), t.image = '<div class="fileuploader-item-image"></div>', t.progressBar = f.isUploadMode() ? '<div class="fileuploader-progressbar"><div class="bar"></div></div>' : "", t.html = $(f._assets.textParse(t.appended && n.thumbnails.item2 ? n.thumbnails.item2 : n.thumbnails.item, t)), t.progressBar = t.html.find(".fileuploader-progressbar"), t.html.addClass("file-type-" + (t.format ? t.format : "no") + " file-ext-" + (t.extension ? t.extension : "no")), e ? e.replaceWith(t.html) : t.html[n.thumbnails.itemPrepend ? "prependTo" : "appendTo"](l), n.thumbnails.popup && !1 !== t.data.popup && (t.html.addClass("file-has-popup"), t.popup = {
                                open: function() {
                                    f.thumbnails.popup(t)
                                }
                            }), f.thumbnails.renderThumbnail(t), t.renderThumbnail = function(e) {
                                e && t.popup && t.popup.close && (t.popup.close(), t.popup = {
                                    open: t.popup.open
                                }), f.thumbnails.renderThumbnail(t, !0, e)
                            }, null != n.thumbnails.onItemShow && $.isFunction(n.thumbnails.onItemShow) && n.thumbnails.onItemShow(t, l, p, o, s)
                        },
                        generateFileIcon: function(e, t) {
                            var o = '<div style="${style}" class="fileuploader-item-icon${class}"><i>' + (t || "") + "</i></div>",
                                i = f._assets.textToColor(t);
                            i && (f._assets.isBrightColor(i) && (o = o.replace("${class}", " is-bright-color")), o = o.replace("${style}", "background-color: " + i));
                            return o.replace("${style}", "").replace("${class}", "")
                        },
                        renderThumbnail: function(d, e, t) {
                            function u(e) {
                                var t = $(e);
                                h.removeClass("fileuploader-no-thumbnail fileuploader-loading").html(t), d.html.hasClass("file-will-popup") && d.html.removeClass("file-will-popup").addClass("file-has-popup"), t.is("img") && t.attr("draggable", "false").on("load error", function(e) {
                                    "error" == e.type && m()
                                }), null != n.thumbnails.onImageLoaded && $.isFunction(n.thumbnails.onImageLoaded) && n.thumbnails.onImageLoaded(d, l, p, o, s)
                            }

                            function c() {
                                var e = 0;
                                if (d && -1 < f._pfrL.indexOf(d))
                                    for (f._pfrL.splice(f._pfrL.indexOf(d), 1); e < f._pfrL.length;) {
                                        if (-1 < f._itFl.indexOf(f._pfrL[e])) {
                                            setTimeout(function() {
                                                f.thumbnails.renderThumbnail(f._pfrL[e], !0)
                                            }, "image" == d.format && 1.8 < d.size / 1e6 ? 200 : 0);
                                            break
                                        }
                                        f._pfrL.splice(e, 1), e++
                                    }
                            }
                            var h = d.html.find(".fileuploader-item-image"),
                                i = d.data && (d.data.readerSkip || !1 === d.data.thumbnail),
                                m = function() {
                                    h.addClass("fileuploader-no-thumbnail"), h.removeClass("fileuploader-loading").html(d.icon), d.html.hasClass("file-will-popup") && d.html.removeClass("file-will-popup").addClass("file-has-popup"), null != n.thumbnails.onImageLoaded && $.isFunction(n.thumbnails.onImageLoaded) && n.thumbnails.onImageLoaded(d, l, p, o, s)
                                };
                            if (h.length) {
                                if (d.image = h.html("").addClass("fileuploader-loading"), (-1 < ["image", "video", "audio", "astext"].indexOf(d.format) || d.data.thumbnail) && f.isFileReaderSupported() && !i && (d.appended || n.thumbnails.startImageRenderer || e)) {
                                    if (d.html.hasClass("file-has-popup") && d.html.removeClass("file-has-popup").addClass("file-will-popup"), n.thumbnails.synchronImages && (-1 != f._pfrL.indexOf(d) || e || f._pfrL.push(d), 1 < f._pfrL.length && !e)) return;
                                    var r = function(e, t) {
                                        function o() {
                                            if (n.thumbnails.canvasImage) {
                                                var e = document.createElement("canvas");
                                                f.editor.resize(this, e, n.thumbnails.canvasImage.width ? n.thumbnails.canvasImage.width : h.width(), n.thumbnails.canvasImage.height ? n.thumbnails.canvasImage.height : h.height(), !1, !0), f._assets.isBlankCanvas(e) ? m() : u(e)
                                            } else u(this);
                                            c()
                                        }

                                        function i() {
                                            l = null, m(), c()
                                        }
                                        var r = e && e.nodeName && "img" == e.nodeName.toLowerCase(),
                                            a = r ? e.src : e,
                                            l = null;
                                        return e ? t && "image" == d.format && d.reader.node ? o.call(d.reader.node) : r ? o.call(e) : ((l = new Image).onload = o, l.onerror = i, d.data && d.data.readerCrossOrigin && l.setAttribute("crossOrigin", d.data.readerCrossOrigin), void(l.src = a)) : i()
                                    };
                                    return "string" == typeof t || "object" == typeof t ? r(t) : f.files.read(d, function() {
                                        r(d.reader.frame || (d.reader.node && "img" == d.reader.node.nodeName.toLowerCase() ? d.reader.src : null), !0)
                                    }, null, t, !0)
                                }
                                m()
                            } else c()
                        },
                        popup: function(d, r) {
                            if (!f.locked && n.thumbnails.popup && n.thumbnails._selectors.popup) {
                                var a = $(n.thumbnails.popup.container),
                                    u = a.find(".fileuploader-popup"),
                                    e = function() {
                                        function t(e) {
                                            var t = e.which || e.keyCode;
                                            27 == t && d.popup && d.popup.close && d.popup.close(), 37 != t && 39 != t || !n.thumbnails.popup.arrows || d.popup.move(37 == t ? "prev" : "next")
                                        }
                                        var e = d.popup.html || $(f._assets.textParse(n.thumbnails.popup.template, d)),
                                            i = d.popup.html !== e;
                                        u.removeClass("loading"), u.children(n.thumbnails._selectors.popup).length && ($.each(f._itFl, function(e, t) {
                                            t != d && t.popup && t.popup.close && t.popup.close(r)
                                        }), u.find(n.thumbnails._selectors.popup).remove()), e.show().appendTo(u), d.popup.html = e, d.popup.isOpened = !0, d.popup.move = function(e) {
                                            var t = f._itFl.indexOf(d),
                                                o = null,
                                                i = !1;
                                            if ("prev" == (e = n.thumbnails.itemPrepend ? "prev" == e ? "next" : "prev" : e))
                                                for (var r = t; 0 <= r; r--) {
                                                    if ((a = f._itFl[r]) != d && a.popup && a.html.hasClass("file-has-popup")) {
                                                        o = a;
                                                        break
                                                    }
                                                    0 != r || o || i || !n.thumbnails.popup.loop || (r = f._itFl.length, i = !0)
                                                } else
                                                for (r = t; r < f._itFl.length; r++) {
                                                    var a;
                                                    if ((a = f._itFl[r]) != d && a.popup && a.html.hasClass("file-has-popup")) {
                                                        o = a;
                                                        break
                                                    }
                                                    r + 1 != f._itFl.length || o || i || !n.thumbnails.popup.loop || (r = -1, i = !0)
                                                }
                                            o && f.thumbnails.popup(o, !0)
                                        }, d.popup.close = function(e) {
                                            d.popup.node && d.popup.node.pause && d.popup.node.pause(), $(window).off("keyup", t), a.css({
                                                overflow: "",
                                                width: ""
                                            }), d.popup.editor && d.popup.editor.cropper && d.popup.editor.cropper.hide(), d.popup.zoomer && d.popup.zoomer.hide(), d.popup.isOpened = !1, d.popup.html && n.thumbnails.popup.onHide && $.isFunction(n.thumbnails.popup.onHide) ? n.thumbnails.popup.onHide(d, l, p, o, s) : d.popup.html && d.popup.html.remove(), e || u.fadeOut(400, function() {
                                                u.remove()
                                            }), delete d.popup.close
                                        }, d.popup.node ? (i && e.html(e.html().replace(/\$\{reader\.node\}/, '<div class="reader-node"></div>')).find(".reader-node").html(d.popup.node), d.popup.node.controls = !0, d.popup.node.currentTime = 0, d.popup.node.play && d.popup.node.play()) : i && e.find(".fileuploader-popup-node").html('<div class="reader-node"><div class="fileuploader-popup-file-icon file-type-' + d.format + " file-ext-" + d.extension + '">' + d.icon + "</div></div>"), $(window).on("keyup", t), a.css({
                                            overflow: "hidden",
                                            width: a.innerWidth()
                                        }), d.popup.html.find('[data-action="prev"], [data-action="next"]').removeAttr("style"), d.popup.html[1 != f._itFl.length && n.thumbnails.popup.arrows ? "addClass" : "removeClass"]("fileuploader-popup-has-arrows"), n.thumbnails.popup.loop || (0 == f._itFl.indexOf(d) && d.popup.html.find('[data-action="prev"]').hide(), f._itFl.indexOf(d) == f._itFl.length - 1 && d.popup.html.find('[data-action="next"]').hide()), i && d.popup.zoomer && (d.popup.zoomer = null), f.editor.zoomer(d), d.editor && (d.popup.editor || (d.popup.editor = {}), f.editor.rotate(d, d.editor.rotation || 0, !0), d.popup.editor && d.popup.editor.cropper && (d.popup.editor.cropper.hide(!0), setTimeout(function() {
                                            f.editor.crop(d, d.editor.crop ? $.extend({}, d.editor.crop) : d.popup.editor.cropper.setDefaultData())
                                        }, 100))), d.popup.html.on("click", '[data-action="prev"]', function(e) {
                                            d.popup.move("prev")
                                        }).on("click", '[data-action="next"]', function(e) {
                                            d.popup.move("next")
                                        }).on("click", '[data-action="crop"]', function(e) {
                                            d.editor && d.editor.cropper()
                                        }).on("click", '[data-action="rotate-cw"]', function(e) {
                                            d.editor && d.editor.rotate()
                                        }).on("click", '[data-action="zoom-in"]', function(e) {
                                            d.popup.zoomer && d.popup.zoomer.zoomIn()
                                        }).on("click", '[data-action="zoom-out"]', function(e) {
                                            d.popup.zoomer && d.popup.zoomer.zoomOut()
                                        }), n.thumbnails.popup.onShow && $.isFunction(n.thumbnails.popup.onShow) && n.thumbnails.popup.onShow(d, l, p, o, s)
                                    };
                                0 == u.length && (u = $('<div class="fileuploader-popup"></div>').appendTo(a)), u.fadeIn(400).addClass("loading").find(n.thumbnails._selectors.popup).fadeOut(150), (-1 < ["image", "video", "audio", "astext"].indexOf(d.format) || -1 < ["application/pdf"].indexOf(d.type)) && !d.popup.html ? f.files.read(d, function() {
                                    d.reader.node && (d.popup.node = d.reader.node), "image" == d.format && d.reader.node ? (d.popup.node = d.reader.node.cloneNode(), d.popup.node.complete ? e() : (d.popup.node.src = "", d.popup.node.onload = d.popup.node.onerror = e, d.popup.node.src = d.reader.node.src)) : e()
                                }) : e()
                            }
                        }
                    },
                    editor: {
                        rotate: function(e, t, o) {
                            if (!(e.popup && e.popup.html && $("html").find(e.popup.html).length)) {
                                var i = e.editor.rotation || 0,
                                    n = t || i + 90;
                                return 360 <= n && (n = 0), e.popup.editor && (e.popup.editor.rotation = n), e.editor.rotation = n
                            }
                            if (e.popup.node) {
                                if (e.popup.editor.isAnimating) return;
                                e.popup.editor.isAnimating = !0;
                                var r = e.popup.html.find(".fileuploader-popup-node").find(".reader-node"),
                                    p = r.find("> img"),
                                    a = {
                                        rotation: i = e.popup.editor.rotation || 0,
                                        scale: e.popup.editor.scale || 1
                                    };
                                e.popup.editor.cropper && e.popup.editor.cropper.$template.hide(), e.popup.editor.rotation = o ? t : i + 90, e.popup.editor.scale = (r.height() / p[-1 < [90, 270].indexOf(e.popup.editor.rotation) ? "width" : "height"]()).toFixed(3), p.height() * e.popup.editor.scale > r.width() && -1 < [90, 270].indexOf(e.popup.editor.rotation) && (e.popup.editor.scale = r.height() / p.width()), 1 < e.popup.editor.scale && (e.popup.editor.scale = 1), $(a).stop().animate({
                                    rotation: e.popup.editor.rotation,
                                    scale: e.popup.editor.scale
                                }, {
                                    duration: o ? 2 : 300,
                                    easing: "swing",
                                    step: function(e, t) {
                                        var o = p.css("-webkit-transform") || p.css("-moz-transform") || p.css("transform") || "none",
                                            i = 0,
                                            n = 1,
                                            r = t.prop;
                                        if ("none" !== o) {
                                            var a = o.split("(")[1].split(")")[0].split(","),
                                                l = a[0],
                                                s = a[1];
                                            i = "rotation" == r ? e : Math.round(Math.atan2(s, l) * (180 / Math.PI)), n = "scale" == r ? e : Math.round(10 * Math.sqrt(l * l + s * s)) / 10
                                        }
                                        p.css({
                                            "-webkit-transform": "rotate(" + i + "deg) scale(" + n + ")",
                                            "-moz-transform": "rotate(" + i + "deg) scale(" + n + ")",
                                            transform: "rotate(" + i + "deg) scale(" + n + ")"
                                        })
                                    },
                                    always: function() {
                                        delete e.popup.editor.isAnimating, e.popup.editor.cropper && !o && (e.popup.editor.cropper.setDefaultData(), e.popup.editor.cropper.init("rotation"))
                                    }
                                }), 360 <= e.popup.editor.rotation && (e.popup.editor.rotation = 0), e.popup.editor.rotation != e.editor.rotation && (e.popup.editor.hasChanges = !0)
                            }
                        },
                        crop: function(w, e) {
                            if (!(w.popup && w.popup.html && $("html").find(w.popup.html).length)) return w.editor.crop = e || w.editor.crop;
                            if (w.popup.node)
                                if (w.popup.editor.cropper) e && (w.popup.editor.cropper.crop = e), w.popup.editor.cropper.init(e);
                                else {
                                    var t = w.popup.html.find(".fileuploader-popup-node .reader-node > img"),
                                        l = $('<div class="fileuploader-cropper"><div class="fileuploader-cropper-area"><div class="point point-a"></div><div class="point point-b"></div><div class="point point-c"></div><div class="point point-d"></div><div class="point point-e"></div><div class="point point-f"></div><div class="point point-g"></div><div class="point point-h"></div><div class="area-move"></div><div class="area-image"></div><div class="area-info"></div></div></div>'),
                                        o = l.find(".fileuploader-cropper-area");
                                    w.popup.editor.cropper = {
                                        $imageEl: t,
                                        $template: l,
                                        $editor: o,
                                        isCropping: !1,
                                        crop: e || null,
                                        init: function(o) {
                                            var i = w.popup.editor.cropper,
                                                e = i.$imageEl.position(),
                                                t = i.$imageEl[0].getBoundingClientRect().width,
                                                r = i.$imageEl[0].getBoundingClientRect().height,
                                                a = w.popup.editor.rotation && -1 < [90, 270].indexOf(w.popup.editor.rotation) ? w.popup.editor.scale : 1;
                                            if (i.hide(), i.crop || i.setDefaultData(), 0 == t || 0 == r) return i.hide(!0);
                                            i.isCropping || (i.$imageEl.clone().appendTo(i.$template.find(".area-image")), i.$imageEl.parent().append(l)), i.$template.hide().css({
                                                left: e.left,
                                                top: e.top,
                                                width: t,
                                                height: r
                                            }).fadeIn(150), i.$editor.hide(), clearTimeout(i._editorAnimationTimeout), i._editorAnimationTimeout = setTimeout(function() {
                                                if (delete i._editorAnimationTimeout, i.$editor.fadeIn(250), w.editor.crop && $.isPlainObject(o) && (i.resize(), i.crop.left = i.crop.left * i.crop.cfWidth * a, i.crop.width = i.crop.width * i.crop.cfWidth * a, i.crop.top = i.crop.top * i.crop.cfHeight * a, i.crop.height = i.crop.height * i.crop.cfHeight * a), n.editor.cropper && (n.editor.cropper.maxWidth || n.editor.cropper.maxHeight) && (n.editor.cropper.maxWidth && (i.crop.width = Math.min(n.editor.cropper.maxWidth * i.crop.cfWidth, i.crop.width)), n.editor.cropper.maxHeight && (i.crop.height = Math.min(n.editor.cropper.maxHeight * i.crop.cfHeight, i.crop.height)), w.editor.crop && "rotation" != o || "resize" == o || (i.crop.left = (i.$template.width() - i.crop.width) / 2, i.crop.top = (i.$template.height() - i.crop.height) / 2)), (!w.editor.crop || "rotation" == o) && n.editor.cropper && n.editor.cropper.ratio && "resize" != o) {
                                                    var e = n.editor.cropper.ratio,
                                                        t = f._assets.ratioToPx(i.crop.width, i.crop.height, e);
                                                    t && (i.crop.width = Math.min(i.crop.width, t[0]), i.crop.left = (i.$template.width() - i.crop.width) / 2, i.crop.height = Math.min(i.crop.height, t[1]), i.crop.top = (i.$template.height() - i.crop.height) / 2)
                                                }
                                                i.drawPlaceHolder(i.crop)
                                            }, 400), n.editor.cropper && n.editor.cropper.showGrid && i.$editor.addClass("has-grid"), i.$imageEl.attr("draggable", "false"), i.$template.on("mousedown touchstart", i.mousedown), $(window).on("resize", i.resize), i.isCropping = !0, w.popup.editor.hasChanges = !0
                                        },
                                        setDefaultData: function() {
                                            var e = w.popup.editor.cropper,
                                                t = e.$imageEl,
                                                o = t.width(),
                                                i = t.height(),
                                                n = w.popup.editor.rotation && -1 < [90, 270].indexOf(w.popup.editor.rotation),
                                                r = w.popup.editor.scale || 1;
                                            return e.crop = {
                                                left: 0,
                                                top: 0,
                                                width: n ? i * r : o,
                                                height: n ? o * r : i,
                                                cfWidth: o / w.reader.width,
                                                cfHeight: i / w.reader.height
                                            }, null
                                        },
                                        hide: function(e) {
                                            var t = w.popup.editor.cropper;
                                            e && (t.$template.hide(), t.$editor.hide()), t.$imageEl.attr("draggable", ""), t.$template.off("mousedown touchstart", t.mousedown), $(window).off("resize", t.resize)
                                        },
                                        resize: function(e) {
                                            var o = w.popup.editor.cropper,
                                                i = o.$imageEl;
                                            0 < i.width() && (e ? (o.$template.hide(), clearTimeout(o._resizeTimeout), o._resizeTimeout = setTimeout(function() {
                                                delete o._resizeTimeout;
                                                var e = i.width() / w.reader.width,
                                                    t = i.height() / w.reader.height;
                                                o.crop.left = o.crop.left / o.crop.cfWidth * e, o.crop.width = o.crop.width / o.crop.cfWidth * e, o.crop.top = o.crop.top / o.crop.cfHeight * t, o.crop.height = o.crop.height / o.crop.cfHeight * t, o.crop.cfWidth = e, o.crop.cfHeight = t, o.init("resize")
                                            }, 500)) : (o.crop.cfWidth = i.width() / w.reader.width, o.crop.cfHeight = i.height() / w.reader.height))
                                        },
                                        drawPlaceHolder: function(e) {
                                            var t = w.popup.editor.cropper,
                                                o = w.popup.editor.rotation || 0,
                                                i = w.popup.editor.scale || 1,
                                                n = [0, 0];
                                            e && (e = $.extend({}, e), o && (n = [180 == o || 270 == o ? -100 : 0, 90 == o || 180 == o ? -100 : 0]), t.$editor.css(e), t.setAreaInfo(), t.$editor.find(".area-image img").removeAttr("style").css({
                                                width: t.$imageEl.width(),
                                                height: t.$imageEl.height(),
                                                left: -1 * t.$editor.position().left,
                                                top: -1 * t.$editor.position().top,
                                                "-webkit-transform": "rotate(" + o + "deg) scale(" + i + ") translateX(" + n[0] + "%) translateY(" + n[1] + "%)",
                                                "-moz-transform": "rotate(" + o + "deg) scale(" + i + ") translateX(" + n[0] + "%) translateY(" + n[1] + "%)",
                                                transform: "rotate(" + o + "deg) scale(" + i + ") translateX(" + n[0] + "%) translateY(" + n[1] + "%)"
                                            }))
                                        },
                                        setAreaInfo: function(e) {
                                            var t = w.popup.editor.cropper,
                                                o = w.popup.editor.scale || 1;
                                            t.$editor.find(".area-info").html((t.isResizing || "size" == e ? ["W: " + Math.round(t.crop.width / t.crop.cfWidth / o) + "px", " ", "H: " + Math.round(t.crop.height / t.crop.cfHeight / o) + "px"] : ["X: " + Math.round(t.crop.left / t.crop.cfWidth / o) + "px", " ", "Y: " + Math.round(t.crop.top / t.crop.cfHeight / o) + "px"]).join(""))
                                        },
                                        mousedown: function(e) {
                                            function t() {
                                                r.pointData = {
                                                    el: i,
                                                    x: a.x,
                                                    y: a.y,
                                                    xEditor: a.x - r.crop.left,
                                                    yEditor: a.y - r.crop.top,
                                                    left: r.crop.left,
                                                    top: r.crop.top,
                                                    width: r.crop.width,
                                                    height: r.crop.height
                                                }, (r.isMoving || r.isResizing) && (r.setAreaInfo("size"), r.$editor.addClass("moving show-info"), $("body").css({
                                                    "-webkit-user-select": "none",
                                                    "-moz-user-select": "none",
                                                    "-ms-user-select": "none",
                                                    "user-select": "none"
                                                }), $(document).on("mousemove touchmove", r.mousemove))
                                            }
                                            var o = e.originalEvent.touches && e.originalEvent.touches[0] ? "touchstart" : "mousedown",
                                                i = $(e.target),
                                                r = w.popup.editor.cropper,
                                                a = {
                                                    x: ("mousedown" == o ? e.pageX : e.originalEvent.touches[0].pageX) - r.$template.offset().left,
                                                    y: ("mousedown" == o ? e.pageY : e.originalEvent.touches[0].pageY) - r.$template.offset().top
                                                };
                                            if (3 == e.which) return !0;
                                            w.popup.zoomer && w.popup.zoomer.hasSpacePressed || (r.isMoving = i.is(".area-move"), r.isResizing = i.is(".point"), "mousedown" == o && t(), "touchstart" == o && 1 == e.originalEvent.touches.length && ((r.isMoving || r.isResizing) && e.preventDefault(), r.isTouchLongPress = !0, setTimeout(function() {
                                                r.isTouchLongPress && (delete r.isTouchLongPress, t())
                                            }, n.thumbnails.touchDelay ? n.thumbnails.touchDelay : 0)), $(document).on("mouseup touchend", r.mouseup))
                                        },
                                        mousemove: function(e) {
                                            var t = e.originalEvent.touches && e.originalEvent.touches[0] ? "touchstart" : "mousedown",
                                                o = ($(e.target), w.popup.editor.cropper),
                                                i = {
                                                    x: ("mousedown" == t ? e.pageX : e.originalEvent.touches[0].pageX) - o.$template.offset().left,
                                                    y: ("mousedown" == t ? e.pageY : e.originalEvent.touches[0].pageY) - o.$template.offset().top
                                                };
                                            if (e.originalEvent.touches && 1 != e.originalEvent.touches.length) return o.mouseup(e);
                                            if (o.isMoving) {
                                                var r = i.x - o.pointData.xEditor,
                                                    a = i.y - o.pointData.yEditor;
                                                r + o.crop.width > o.$template.width() && (r = o.$template.width() - o.crop.width), r < 0 && (r = 0), a + o.crop.height > o.$template.height() && (a = o.$template.height() - o.crop.height), a < 0 && (a = 0), o.crop.left = r, o.crop.top = a
                                            }
                                            if (o.isResizing) {
                                                var l, s = o.pointData.el.attr("class").substr("point point-".length),
                                                    p = o.crop.left + o.crop.width,
                                                    d = o.crop.top + o.crop.height,
                                                    u = (n.editor.cropper && n.editor.cropper.minWidth || 0) * o.crop.cfWidth,
                                                    c = (n.editor.cropper && n.editor.cropper.minHeight || 0) * o.crop.cfHeight,
                                                    h = (n.editor.cropper && n.editor.cropper.maxWidth) * o.crop.cfWidth,
                                                    m = (n.editor.cropper && n.editor.cropper.maxHeight) * o.crop.cfHeight,
                                                    g = n.editor.cropper ? n.editor.cropper.ratio : null;
                                                if (u > o.$template.width() && (u = o.$template.width()), c > o.$template.height() && (c = o.$template.height()), h > o.$template.width() && (h = o.$template.width()), m > o.$template.height() && (m = o.$template.height()), ("a" == s || "b" == s || "c" == s) && !l && (o.crop.top = i.y, o.crop.top < 0 && (o.crop.top = 0), o.crop.height = d - o.crop.top, o.crop.top > o.crop.top + o.crop.height && (o.crop.top = d, o.crop.height = 0), o.crop.height < c && (o.crop.top = d - c, o.crop.height = c), o.crop.height > m && (o.crop.top = d - m, o.crop.height = m), (l = g ? f._assets.ratioToPx(o.crop.width, o.crop.height, g) : null) && (o.crop.width = l[0], "a" != s && "b" != s || (o.crop.left = Math.max(0, o.pointData.left + (o.pointData.width - o.crop.width) / ("b" == s ? 2 : 1))), o.crop.left + o.crop.width > o.$template.width()))) {
                                                    var v = o.$template.width() - o.crop.left;
                                                    o.crop.width = v, o.crop.height = v / l[2] * l[3], o.crop.top = d - o.crop.height
                                                }
                                                if (("e" == s || "f" == s || "g" == s) && !l && (o.crop.height = i.y - o.crop.top, o.crop.height + o.crop.top > o.$template.height() && (o.crop.height = o.$template.height() - o.crop.top), o.crop.height < c && (o.crop.height = c), o.crop.height > m && (o.crop.height = m), (l = g ? f._assets.ratioToPx(o.crop.width, o.crop.height, g) : null) && (o.crop.width = l[0], "f" != s && "g" != s || (o.crop.left = Math.max(0, o.pointData.left + (o.pointData.width - o.crop.width) / ("f" == s ? 2 : 1))), o.crop.left + o.crop.width > o.$template.width()))) {
                                                    v = o.$template.width() - o.crop.left;
                                                    o.crop.width = v, o.crop.height = v / l[2] * l[3]
                                                }
                                                if (("c" == s || "d" == s || "e" == s) && !l && (o.crop.width = i.x - o.crop.left, o.crop.width + o.crop.left > o.$template.width() && (o.crop.width = o.$template.width() - o.crop.left), o.crop.width < u && (o.crop.width = u), o.crop.width > h && (o.crop.width = h), (l = g ? f._assets.ratioToPx(o.crop.width, o.crop.height, g) : null) && (o.crop.height = l[1], "c" != s && "d" != s || (o.crop.top = Math.max(0, o.pointData.top + (o.pointData.height - o.crop.height) / ("d" == s ? 2 : 1))), o.crop.top + o.crop.height > o.$template.height()))) {
                                                    var b = o.$template.height() - o.crop.top;
                                                    o.crop.height = b, o.crop.width = b / l[3] * l[2]
                                                }
                                                if (("a" == s || "g" == s || "h" == s) && !l && (o.crop.left = i.x, o.crop.left > o.$template.width() && (o.crop.left = o.$template.width()), o.crop.left < 0 && (o.crop.left = 0), o.crop.width = p - o.crop.left, o.crop.left > o.crop.left + o.crop.width && (o.crop.left = p, o.crop.width = 0), o.crop.width < u && (o.crop.left = p - u, o.crop.width = u), o.crop.width > h && (o.crop.left = p - h, o.crop.width = h), (l = g ? f._assets.ratioToPx(o.crop.width, o.crop.height, g) : null) && (o.crop.height = l[1], "a" != s && "h" != s || (o.crop.top = Math.max(0, o.pointData.top + (o.pointData.height - o.crop.height) / ("h" == s ? 2 : 1))), o.crop.top + o.crop.height > o.$template.height()))) {
                                                    b = o.$template.height() - o.crop.top;
                                                    o.crop.height = b, o.crop.width = b / l[3] * l[2], o.crop.left = p - o.crop.width
                                                }
                                            }
                                            o.drawPlaceHolder(o.crop)
                                        },
                                        mouseup: function(e) {
                                            var t = w.popup.editor.cropper;
                                            0 != t.$editor.width() && 0 != t.$editor.height() || t.init(t.setDefaultData()), delete t.isTouchLongPress, delete t.isMoving, delete t.isResizing, t.$editor.removeClass("moving show-info"), $("body").css({
                                                "-webkit-user-select": "",
                                                "-moz-user-select": "",
                                                "-ms-user-select": "",
                                                "user-select": ""
                                            }), $(document).off("mousemove touchmove", t.mousemove), $(document).off("mouseup touchend", t.mouseup)
                                        }
                                    }, w.popup.editor.cropper.init()
                                }
                        },
                        resize: function(e, t, o, i, n, r) {
                            var a = t.getContext("2d"),
                                l = (o = !o && i ? i * e.width / e.height : o, i = !i && o ? o * e.height / e.width : i, e.width / e.height),
                                s = 1 <= l ? o : i * l,
                                p = l < 1 ? i : o / l;
                            r && s < o && (p *= o / s, s = o), r && p < i && (s *= i / p, p = i);
                            var d = Math.min(Math.ceil(Math.log(e.width / s) / Math.log(2)), 12);
                            if (t.width = s, t.height = p, e.width < t.width || e.height < t.height || d < 2) {
                                r || (t.width = Math.min(e.width, t.width), t.height = Math.min(e.height, t.height));
                                var u = e.width < t.width ? (t.width - e.width) / 2 : 0,
                                    c = e.height < t.height ? (t.height - e.height) / 2 : 0;
                                n || (a.fillStyle = "#fff", a.fillRect(0, 0, t.width, t.height)), a.drawImage(e, u, c, Math.min(e.width, t.width), Math.min(e.height, t.height))
                            } else {
                                var f = document.createElement("canvas"),
                                    h = f.getContext("2d"),
                                    m = 2;
                                for (f.width = e.width / m, f.height = e.height / m, h.fillStyle = "#fff", h.fillRect(0, 0, f.width, f.height), h.imageSmoothingEnabled = !1, h.imageSmoothingQuality = "high", h.drawImage(e, 0, 0, f.width, f.height); 2 < d;) {
                                    var g = m + 2,
                                        v = e.width / m,
                                        b = e.height / m;
                                    v > f.width && (v = f.width), b > f.height && (b = f.height), h.imageSmoothingEnabled = !0, h.drawImage(f, 0, 0, v, b, 0, 0, e.width / g, e.height / g), m = g, d--
                                }
                                v = e.width / m, b = e.height / m;
                                v > f.width && (v = f.width), b > f.height && (b = f.height), a.drawImage(f, 0, 0, v, b, 0, 0, s, p), f = h = null
                            }
                            a = null
                        },
                        zoomer: function(s) {
                            if (s.popup && s.popup.html && $("html").find(s.popup.html).length) {
                                if (!s.popup.zoomer) {
                                    var e = s.popup.html,
                                        p = e.find(".fileuploader-popup-node"),
                                        d = p.find(".reader-node"),
                                        l = d.find("> img").attr("draggable", "false").attr("ondragstart", "return false;");
                                    s.popup.zoomer = {
                                        html: e.find(".fileuploader-popup-zoomer"),
                                        isActive: "image" == s.format && s.popup.node && n.thumbnails.popup.zoomer,
                                        scale: 100,
                                        zoom: 100,
                                        init: function() {
                                            var e = this;
                                            if (!e.isActive || f._assets.isIE() || f._assets.isMobile()) return e.html.hide() && p.addClass("has-node-centered");
                                            e.hide(), e.resize(), $(window).on("resize", e.resize), $(window).on("keyup keydown", e.keyPress), e.html.find("input").on("input change", e.range), d.on("mousedown touchstart", e.mousedown), p.on("mousewheel DOMMouseScroll", e.scroll)
                                        },
                                        hide: function() {
                                            var e = this;
                                            $(window).off("resize", e.resize), $(window).off("keyup keydown", e.keyPress), e.html.find("input").off("input change", e.range), d.off("mousedown", e.mousedown), p.off("mousewheel DOMMouseScroll", e.scroll)
                                        },
                                        center: function(e) {
                                            var t = this,
                                                o = 0,
                                                i = 0;
                                            i = e ? (o = t.left, i = t.top, o -= (p.width() / 2 - t.left) * (d.width() / e[0] - 1), i -= (p.height() / 2 - t.top) * (d.height() / e[1] - 1), d.width() <= p.width() && (o = Math.round((p.width() - d.width()) / 2)), d.height() <= p.height() && (i = Math.round((p.height() - d.height()) / 2)), d.width() > p.width() && (0 < o ? o = 0 : o + d.width() < p.width() && (o = p.width() - d.width())), d.height() > p.height() && (0 < i ? i = 0 : i + d.height() < p.height() && (i = p.height() - d.height())), Math.min(i, 0)) : (o = Math.round((p.width() - d.width()) / 2), Math.round((p.height() - d.height()) / 2)), d.css({
                                                left: (t.left = o) + "px",
                                                top: (t.top = i) + "px",
                                                width: d.width(),
                                                height: d.height()
                                            })
                                        },
                                        resize: function() {
                                            var e = s.popup.zoomer;
                                            p.removeClass("is-zoomed"), d.removeAttr("style"), e.scale = e.getImageScale(), e.updateView()
                                        },
                                        range: function(e) {
                                            var t = s.popup.zoomer,
                                                o = $(this),
                                                i = parseFloat(o.val());
                                            if (100 <= t.scale) return e.preventDefault(), void o.val(t.scale);
                                            i < t.scale && (e.preventDefault(), i = t.scale, o.val(i)), t.updateView(i, !0)
                                        },
                                        scroll: function(e) {
                                            var t = s.popup.zoomer,
                                                o = -100;
                                            e.originalEvent && (e.originalEvent.wheelDelta && (o = e.originalEvent.wheelDelta / -40), e.originalEvent.deltaY && (o = e.originalEvent.deltaY), e.originalEvent.detail && (o = e.originalEvent.detail)), t[o < 0 ? "zoomIn" : "zoomOut"](3)
                                        },
                                        keyPress: function(e) {
                                            var t = s.popup.zoomer,
                                                o = e.type;
                                            32 == (e.keyCode || e.which) && (t.hasSpacePressed = "keydown" == o, t.hasSpacePressed && t.isZoomed() ? d.addClass("is-amoving") : d.removeClass("is-amoving"))
                                        },
                                        mousedown: function(e) {
                                            function t() {
                                                o.pointData = {
                                                    x: a.x,
                                                    y: a.y,
                                                    xTarget: a.x - o.left,
                                                    yTarget: a.y - o.top
                                                }, $("body").css({
                                                    "-webkit-user-select": "none",
                                                    "-moz-user-select": "none",
                                                    "-ms-user-select": "none",
                                                    "user-select": "none"
                                                }), d.addClass("is-moving"), $(document).on("mousemove", o.mousemove)
                                            }
                                            var o = s.popup.zoomer,
                                                i = $(e.target),
                                                r = e.originalEvent.touches && e.originalEvent.touches[0] ? "touchstart" : "mousedown",
                                                a = {
                                                    x: "mousedown" == r ? e.pageX : e.originalEvent.touches[0].pageX,
                                                    y: "mousedown" == r ? e.pageY : e.originalEvent.touches[0].pageY
                                                };
                                            1 == e.which && 100 != o.scale && o.zoom != o.scale && (o.hasSpacePressed || i[0] == l[0] || i.is(".fileuploader-cropper")) && ("mousedown" == r && t(), "touchstart" == r && (o.isTouchLongPress = !0, setTimeout(function() {
                                                o.isTouchLongPress && (delete o.isTouchLongPress, t())
                                            }, n.thumbnails.touchDelay ? n.thumbnails.touchDelay : 0)), $(document).on("mouseup touchend", o.mouseup))
                                        },
                                        mousemove: function(e) {
                                            var t = s.popup.zoomer,
                                                o = e.originalEvent.touches && e.originalEvent.touches[0] ? "touchstart" : "mousedown",
                                                i = "mousedown" == o ? e.pageX : e.originalEvent.touches[0].pageX,
                                                n = "mousedown" == o ? e.pageY : e.originalEvent.touches[0].pageY,
                                                r = i - t.pointData.xTarget,
                                                a = n - t.pointData.yTarget;
                                            0 < a && (a = 0), a < p.height() - d.height() && (a = p.height() - d.height()), d.height() < p.height() && (a = p.height() / 2 - d.height() / 2), d.width() > p.width() ? (0 < r && (r = 0), r < p.width() - d.width() && (r = p.width() - d.width())) : r = p.width() / 2 - d.width() / 2, d.css({
                                                left: (t.left = r) + "px",
                                                top: (t.top = a) + "px"
                                            })
                                        },
                                        mouseup: function(e) {
                                            var t = s.popup.zoomer;
                                            delete t.pointData, $("body").css({
                                                "-webkit-user-select": "",
                                                "-moz-user-select": "",
                                                "-ms-user-select": "",
                                                "user-select": ""
                                            }), d.removeClass("is-moving"), $(document).off("mousemove", t.mousemove), $(document).off("mouseup", t.mouseup)
                                        },
                                        zoomIn: function(e) {
                                            var t = s.popup.zoomer,
                                                o = e || 20;
                                            100 <= t.zoom || (t.zoom = Math.min(100, t.zoom + o), t.updateView(t.zoom))
                                        },
                                        zoomOut: function(e) {
                                            var t = s.popup.zoomer,
                                                o = e || 20;
                                            t.zoom <= t.scale || (t.zoom = Math.max(t.scale, t.zoom - o), t.updateView(t.zoom))
                                        },
                                        updateView: function(e, t) {
                                            var o = this,
                                                i = o.getImageSize().width / 100 * e,
                                                n = o.getImageSize().height / 100 * e,
                                                r = d.width(),
                                                a = d.height(),
                                                l = e && e != o.scale;
                                            if (!o.isActive) return o.center();
                                            l ? (p.addClass("is-zoomed"), d.addClass("is-movable").css({
                                                width: i + "px",
                                                height: n + "px",
                                                maxWidth: "none",
                                                maxHeight: "none"
                                            })) : (p.removeClass("is-zoomed"), d.removeClass("is-movable is-amoving").removeAttr("style")), o.zoom = e || o.scale, o.center(l ? [r, a, o.left, o.top] : null), o.html.find("span").html(o.zoom + "%"), t || o.html.find("input").val(o.zoom), e && s.popup.editor && s.popup.editor.cropper && s.popup.editor.cropper.resize(!0)
                                        },
                                        isZoomed: function() {
                                            return this.zoom > this.scale
                                        },
                                        getImageSize: function() {
                                            return {
                                                width: l.prop("naturalWidth"),
                                                height: l.prop("naturalHeight")
                                            }
                                        },
                                        getImageScale: function() {
                                            return Math.round(100 / (l.prop("naturalWidth") / l.width()))
                                        }
                                    }
                                }
                                s.popup.zoomer.init()
                            }
                        },
                        save: function(b, w, x, y, z) {
                            function e() {
                                if (b.reader.node) {
                                    var t = document.createElement("canvas"),
                                        i = t.getContext("2d"),
                                        e = [0, 180],
                                        r = x || b.type || "image/jpeg",
                                        a = n.editor.quality || 90,
                                        d = function(e, t) {
                                            var i = e;
                                            w && (i ? i = f._assets.dataURItoBlob(i, r) : console.error("Error: Failed to execute 'toDataURL' on 'HTMLCanvasElement': Tainted canvases may not be exported.")), !z && i && f.thumbnails.renderThumbnail(b, !0, t || e), y && y(i, b, l, p, o, s), null != n.editor.onSave && "function" == typeof n.editor.onSave && n.editor.onSave(i, b, l, p, o, s), f.set("listInput", null)
                                        };
                                    try {
                                        if (t.width = b.reader.width, t.height = b.reader.height, i.drawImage(this, 0, 0, b.reader.width, b.reader.height), void 0 !== b.editor.rotation) {
                                            b.editor.rotation = b.editor.rotation || 0, t.width = -1 < e.indexOf(b.editor.rotation) ? b.reader.width : b.reader.height, t.height = -1 < e.indexOf(b.editor.rotation) ? b.reader.height : b.reader.width;
                                            var u = b.editor.rotation * Math.PI / 180,
                                                c = .5 * t.width,
                                                h = .5 * t.height;
                                            i.clearRect(0, 0, t.width, t.height), i.translate(c, h), i.rotate(u), i.translate(.5 * -b.reader.width, .5 * -b.reader.height), i.drawImage(this, 0, 0), i.setTransform(1, 0, 0, 1, 0, 0)
                                        }
                                        if (b.editor.crop) {
                                            var m = i.getImageData(b.editor.crop.left, b.editor.crop.top, b.editor.crop.width, b.editor.crop.height);
                                            t.width = b.editor.crop.width, t.height = b.editor.crop.height, i.putImageData(m, 0, 0)
                                        }
                                        var g = t.toDataURL(r, a / 100);
                                        if (n.editor.maxWidth || n.editor.maxHeight) {
                                            var v = new Image;
                                            v.src = g, v.onload = function() {
                                                var e = document.createElement("canvas");
                                                f.editor.resize(v, e, n.editor.maxWidth, n.editor.maxHeight, !0, !1), g = e.toDataURL(r, a / 100), t = i = e = null, d(g, v)
                                            }
                                        } else t = i = null, d(g)
                                    } catch (e) {
                                        b.popup.editor = null, t = i = null, d(null)
                                    }
                                }
                            }
                            var t = b.popup && b.popup.html && $("html").find(b.popup.html).length,
                                i = new Image;
                            if (t) {
                                if (!b.popup.editor.hasChanges) return;
                                var r = b.popup.editor.scale || 1;
                                b.editor.rotation = b.popup.editor.rotation || 0, b.popup.editor.cropper && (b.editor.crop = b.popup.editor.cropper.crop, b.editor.crop.width = b.editor.crop.width / b.popup.editor.cropper.crop.cfWidth / r, b.editor.crop.left = b.editor.crop.left / b.popup.editor.cropper.crop.cfWidth / r, b.editor.crop.height = b.editor.crop.height / b.popup.editor.cropper.crop.cfHeight / r, b.editor.crop.top = b.editor.crop.top / b.popup.editor.cropper.crop.cfHeight / r)
                            }
                            f._assets.isMobile() ? (i.onload = e, i.src = b.reader.src) : b.popup.node ? e.call(b.popup.node) : b.reader.node ? e.call(b.reader.node) : b.reader.read(b, function() {
                                e.call(b.reader.node)
                            })
                        }
                    },
                    sorter: {
                        init: function() {
                            p.on("mousedown touchstart", n.thumbnails._selectors.sorter, f.sorter.mousedown)
                        },
                        destroy: function() {
                            p.off("mousedown touchstart", n.thumbnails._selectors.sorter, f.sorter.mousedown)
                        },
                        findItemAtPos: function(i) {
                            var e = f.sorter.sort,
                                t = e.items.not(e.item.html),
                                n = null;
                            return t.each(function(e, t) {
                                var o = $(t);
                                if (i.x > o.offset().left && i.x < o.offset().left + o.outerWidth() && i.y > o.offset().top && i.y < o.offset().top + o.outerHeight()) return n = o, !1
                            }), n
                        },
                        mousedown: function(e) {
                            function t() {
                                f.sorter.sort = {
                                    el: i,
                                    item: a,
                                    items: l.find(n.thumbnails._selectors.item),
                                    x: s.x,
                                    y: s.y,
                                    xItem: s.x - r.offset().left,
                                    yItem: s.y - r.offset().top,
                                    left: r.position().left,
                                    top: r.position().top,
                                    width: r.outerWidth(),
                                    height: r.outerHeight(),
                                    placeholder: n.sorter.placeholder ? $(n.sorter.placeholder) : $(a.html.get(0).cloneNode()).addClass("fileuploader-sorter-placeholder")
                                }, $("body").css({
                                    "-webkit-user-select": "none",
                                    "-moz-user-select": "none",
                                    "-ms-user-select": "none",
                                    "user-select": "none"
                                }), $(document).on("mousemove touchmove", f.sorter.mousemove)
                            }
                            var o = e.originalEvent.touches && e.originalEvent.touches[0] ? "touchstart" : "mousedown",
                                i = $(e.target),
                                r = i.closest(n.thumbnails._selectors.item),
                                a = f.files.find(r),
                                s = {
                                    x: "mousedown" != o && r.length ? e.originalEvent.touches[0].pageX : e.pageX,
                                    y: "mousedown" != o && r.length ? e.originalEvent.touches[0].pageY : e.pageY
                                };
                            return f.sorter.sort && f.sorter.mouseup(), 3 == e.which || (!a || (!(!n.sorter.selectorExclude || !i.is(n.sorter.selectorExclude) && !i.closest(n.sorter.selectorExclude).length) || (e.preventDefault(), "mousedown" == o && t(), "touchstart" == o && (f.sorter.isTouchLongPress = !0, setTimeout(function() {
                                f.sorter.isTouchLongPress && (delete f.sorter.isTouchLongPress, t())
                            }, n.thumbnails.touchDelay ? n.thumbnails.touchDelay : 0)), void $(document).on("mouseup touchend", f.sorter.mouseup))))
                        },
                        mousemove: function(e) {
                            var t = e.originalEvent.touches && e.originalEvent.touches[0] ? "touchstart" : "mousedown",
                                o = f.sorter.sort,
                                i = o.item,
                                r = l.find(n.thumbnails._selectors.item),
                                a = $(n.sorter.scrollContainer || window),
                                s = $(document).scrollLeft(),
                                p = $(document).scrollTop(),
                                d = a.scrollLeft(),
                                u = a.scrollTop(),
                                c = {
                                    x: "mousedown" == t ? e.clientX : e.originalEvent.touches[0].clientX,
                                    y: "mousedown" == t ? e.clientY : e.originalEvent.touches[0].clientY
                                };
                            e.preventDefault();
                            var h = c.x - o.xItem,
                                m = c.y - o.yItem,
                                g = c.x - (a.prop("offsetLeft") || 0),
                                v = c.y - (a.prop("offsetTop") || 0);
                            h + o.xItem > a.width() && (h = a.width() - o.xItem), h + o.xItem < 0 && (h = 0 - o.xItem), m + o.yItem > a.height() && (m = a.height() - o.yItem), m + o.yItem < 0 && (m = 0 - o.yItem), v <= 0 && a.scrollTop(u - 10), v > a.height() && a.scrollTop(u + 10), g < 0 && a.scrollLeft(d - 10), g > a.width() && a.scrollLeft(d + 10), i.html.addClass("sorting").css({
                                position: "fixed",
                                left: h,
                                top: m,
                                width: f.sorter.sort.width,
                                height: f.sorter.sort.height
                            }), l.find(o.placeholder).length || i.html.after(o.placeholder), o.placeholder.css({
                                width: f.sorter.sort.width,
                                height: f.sorter.sort.height
                            });
                            var b = f.sorter.findItemAtPos({
                                x: h + o.xItem + s,
                                y: m + o.yItem + p
                            });
                            if (b) {
                                var w = o.placeholder.offset().left != b.offset().left,
                                    x = o.placeholder.offset().top != b.offset().top;
                                if (f.sorter.sort.lastHover && f.sorter.sort.lastHover.el == b[0]) {
                                    if (x && "before" == f.sorter.sort.lastHover.direction && c.y < f.sorter.sort.lastHover.y) return;
                                    if (x && "after" == f.sorter.sort.lastHover.direction && c.y > f.sorter.sort.lastHover.y) return;
                                    if (w && "before" == f.sorter.sort.lastHover.direction && c.x < f.sorter.sort.lastHover.x) return;
                                    if (w && "after" == f.sorter.sort.lastHover.direction && c.x > f.sorter.sort.lastHover.x) return
                                }
                                var y = r.index(i.html),
                                    z = r.index(b) < y ? "before" : "after";
                                b[z](o.placeholder), b[z](i.html), f.sorter.sort.lastHover = {
                                    el: b[0],
                                    x: c.x,
                                    y: c.y,
                                    direction: z
                                }
                            }
                        },
                        mouseup: function() {
                            var e = f.sorter.sort,
                                t = e.item;
                            $("body").css({
                                "-webkit-user-select": "",
                                "-moz-user-select": "",
                                "-ms-user-select": "",
                                "user-select": ""
                            }), t.html.removeClass("sorting").css({
                                position: "",
                                left: "",
                                top: "",
                                width: "",
                                height: ""
                            }), $(document).off("mousemove touchmove", f.sorter.mousemove), $(document).off("mouseup touchend", f.sorter.mouseup), e.placeholder.remove(), delete f.sorter.sort, f.sorter.save()
                        },
                        save: function(e) {
                            var i, r = 0,
                                a = [],
                                d = [],
                                t = e ? f._itFl : n.thumbnails.itemPrepend ? l.children().get().reverse() : l.children();
                            $.each(t, function(e, t) {
                                var o = t.file ? t : f.files.find($(t));
                                if (o) {
                                    if (o.upload && !o.uploaded) return;
                                    f.rendered && o.index != r && (f._itSl && f._itSl.indexOf(o.id), 1) && (i = !0), o.index = r, a.push(o), d.push(o.id), r++
                                }
                            }), f._itSl && f._itSl.length != d.length && (i = !0), f._itSl = d, i && a.length == f._itFl.length && (f._itFl = a), e || f.set("listInput", "ignoreSorter"), i && null != n.sorter.onSort && "function" == typeof n.sorter.onSort && n.sorter.onSort(a, l, p, o, s)
                        }
                    },
                    upload: {
                        prepare: function(t, e) {
                            t.upload = {
                                url: n.upload.url,
                                data: $.extend({}, n.upload.data),
                                formData: new FormData,
                                type: n.upload.type || "POST",
                                enctype: n.upload.enctype || "multipart/form-data",
                                cache: !1,
                                contentType: !1,
                                processData: !1,
                                chunk: t.upload ? t.upload.chunk : null,
                                status: null,
                                send: function() {
                                    f.upload.send(t, !0)
                                },
                                cancel: function(e) {
                                    f.upload.cancel(t, e)
                                }
                            }, t.upload.formData.append(s.attr("name"), t.file, !!t.name && t.name), (n.upload.start || e) && f.upload.send(t, e)
                        },
                        send: function(a, e) {
                            if (a.upload) {
                                var d = function(e) {
                                        a.html && a.html.removeClass("upload-pending upload-loading upload-cancelled upload-failed upload-successful").addClass("upload-" + (e || a.upload.status))
                                    },
                                    r = function() {
                                        var e = 0;
                                        if (0 < f._pfuL.length)
                                            for (-1 < f._pfuL.indexOf(a) && f._pfuL.splice(f._pfuL.indexOf(a), 1); e < f._pfuL.length;) {
                                                if (-1 < f._itFl.indexOf(f._pfuL[e]) && f._pfuL[e].upload && !f._pfuL[e].upload.$ajax) {
                                                    f.upload.send(f._pfuL[e], !0);
                                                    break
                                                }
                                                f._pfuL.splice(e, 1), e++
                                            }
                                    };
                                if (n.upload.synchron && !a.upload.chunk)
                                    if (a.upload.status = "pending", a.html && d(), e) - 1 < f._pfuL.indexOf(a) && f._pfuL.splice(f._pfuL.indexOf(a), 1);
                                    else if (-1 == f._pfuL.indexOf(a) && f._pfuL.push(a), 1 < f._pfuL.length) return;
                                if (n.upload.chunk && a.file.slice) {
                                    var t = f._assets.toBytes(n.upload.chunk),
                                        i = Math.ceil(a.size / t, t);
                                    if (1 < i && !a.upload.chunk && (a.upload.chunk = {
                                        name: a.name,
                                        size: a.file.size,
                                        type: a.file.type,
                                        chunkSize: t,
                                        temp_name: a.name,
                                        loaded: 0,
                                        total: i,
                                        i: -1
                                    }), a.upload.chunk)
                                        if (a.upload.chunk.i++, delete a.upload.chunk.isFirst, delete a.upload.chunk.isLast, 0 == a.upload.chunk.i && (a.upload.chunk.isFirst = !0), a.upload.chunk.i == a.upload.chunk.total - 1 && (a.upload.chunk.isLast = !0), a.upload.chunk.i <= a.upload.chunk.total - 1) {
                                            var u = a.upload.chunk.i * a.upload.chunk.chunkSize,
                                                c = a.file.slice(u, u + a.upload.chunk.chunkSize);
                                            a.upload.formData = new FormData, a.upload.formData.append(s.attr("name"), c), a.upload.data._chunkedd = JSON.stringify(a.upload.chunk)
                                        } else delete a.upload.chunk
                                }
                                if (n.upload.beforeSend && $.isFunction(n.upload.beforeSend) && !1 === n.upload.beforeSend(a, l, p, o, s)) return delete a.upload.chunk, d(), void r();
                                if (p.addClass("fileuploader-is-uploading"), a.upload.$ajax && a.upload.$ajax.abort(), delete a.upload.$ajax, delete a.upload.send, a.upload.status = "loading", a.html && (n.thumbnails._selectors.start && a.html.find(n.thumbnails._selectors.start).remove(), d()), a.upload.data)
                                    for (var h in a.upload.data) a.upload.data.hasOwnProperty(h) && a.upload.formData.append(h, a.upload.data[h]);
                                a.upload.data = a.upload.formData, a.upload.xhrStartedAt = a.upload.chunk && a.upload.chunk.xhrStartedAt ? a.upload.chunk.xhrStartedAt : new Date, a.upload.xhr = function() {
                                    var e = $.ajaxSettings.xhr();
                                    return e.upload && e.upload.addEventListener("progress", function(e) {
                                        a.upload.$ajax && (a.upload.$ajax.total = a.upload.chunk ? a.upload.chunk.size : e.total), f.upload.progressHandling(e, a, a.upload.xhrStartedAt)
                                    }, !1), e
                                }, a.upload.complete = function(e, t) {
                                    if (a.upload.chunk && !a.upload.chunk.isLast && "success" == t) return f.upload.prepare(a, !0);
                                    r(), delete a.upload.xhrStartedAt;
                                    var i = !0;
                                    $.each(f._itFl, function(e, t) {
                                        t.upload && t.upload.$ajax && (i = !1)
                                    }), i && (p.removeClass("fileuploader-is-uploading"), null != n.upload.onComplete && "function" == typeof n.upload.onComplete && n.upload.onComplete(l, p, o, s, e, t))
                                }, a.upload.success = function(e, t, i) {
                                    if (!a.upload.chunk || a.upload.chunk.isLast) delete a.upload.chunk, f.upload.progressHandling(null, a, a.upload.xhrStartedAt, !0), a.uploaded = !0, delete a.upload, a.upload = {
                                        status: "successful",
                                        resend: function() {
                                            f.upload.retry(a)
                                        }
                                    }, a.html && d(), null != n.upload.onSuccess && $.isFunction(n.upload.onSuccess) && n.upload.onSuccess(e, a, l, p, o, s, t, i), f.set("listInput", null);
                                    else try {
                                        var r = JSON.parse(e);
                                        a.upload.chunk.temp_name = r.fileuploader.temp_name
                                    } catch (e) {}
                                }, a.upload.error = function(e, t, i) {
                                    a.upload.chunk && (a.upload.chunk.i = Math.max(-1, a.upload.chunk.i - 1)), a.uploaded = !1, a.upload.status = "cancelled" == a.upload.status ? a.upload.status : "failed", a.upload.retry = function() {
                                        f.upload.retry(a)
                                    }, delete a.upload.$ajax, a.html && d(), null != n.upload.onError && $.isFunction(n.upload.onError) && n.upload.onError(a, l, p, o, s, e, t, i)
                                }, a.upload.$ajax = $.ajax(a.upload)
                            }
                        },
                        cancel: function(e, t) {
                            e && e.upload && (e.upload.status = "cancelled", delete e.upload.chunk, e.upload.$ajax && e.upload.$ajax.abort(), delete e.upload.$ajax, t || f.files.remove(e))
                        },
                        retry: function(e) {
                            e && e.upload && (e.html && n.thumbnails._selectors.retry && e.html.find(n.thumbnails._selectors.retry).remove(), f.upload.prepare(e, !0))
                        },
                        progressHandling: function(e, t, i, r) {
                            if (!e && r && t.upload.$ajax && (e = {
                                total: t.upload.$ajax.total || t.size,
                                loaded: t.upload.$ajax.total || t.size,
                                lengthComputable: !0
                            }), e.lengthComputable) {
                                var a = new Date,
                                    d = e.loaded + (t.upload.chunk ? t.upload.chunk.loaded : 0),
                                    u = t.upload.chunk ? t.upload.chunk.size : e.total,
                                    c = Math.round(100 * d / u),
                                    h = t.upload.chunk && t.upload.chunk.xhrStartedAt ? t.upload.chunk.xhrStartedAt : i,
                                    m = (a.getTime() - h.getTime()) / 1e3,
                                    g = m ? d / m : 0,
                                    v = Math.max(0, u - d),
                                    b = Math.max(0, m ? v / g : 0),
                                    w = {
                                        loaded: d,
                                        loadedInFormat: f._assets.bytesToText(d),
                                        total: u,
                                        totalInFormat: f._assets.bytesToText(u),
                                        percentage: c,
                                        secondsElapsed: m,
                                        secondsElapsedInFormat: f._assets.secondsToText(m, !0),
                                        bytesPerSecond: g,
                                        bytesPerSecondInFormat: f._assets.bytesToText(g) + "/s",
                                        remainingBytes: v,
                                        remainingBytesInFormat: f._assets.bytesToText(v),
                                        secondsRemaining: b,
                                        secondsRemainingInFormat: f._assets.secondsToText(b, !0)
                                    };
                                t.upload.chunk && (t.upload.chunk.isFirst && (t.upload.chunk.xhrStartedAt = i), e.loaded != e.total || t.upload.chunk.isLast || (t.upload.chunk.loaded += Math.max(e.total, t.upload.chunk.total / t.upload.chunk.chunkSize))), 99 < w.percentage && !r && (w.percentage = 99), n.upload.onProgress && $.isFunction(n.upload.onProgress) && n.upload.onProgress(w, t, l, p, o, s)
                            }
                        }
                    },
                    dragDrop: {
                        onDragEnter: function(e) {
                            clearTimeout(f.dragDrop._timer), n.dragDrop.container.addClass("fileuploader-dragging"), f.set("feedback", f._assets.textParse(n.captions.drop)), null != n.dragDrop.onDragEnter && $.isFunction(n.dragDrop.onDragEnter) && n.dragDrop.onDragEnter(e, l, p, o, s)
                        },
                        onDragLeave: function(e) {
                            clearTimeout(f.dragDrop._timer), f.dragDrop._timer = setTimeout(function(e) {
                                if (!f.dragDrop._dragLeaveCheck(e)) return !1;
                                n.dragDrop.container.removeClass("fileuploader-dragging"), f.set("feedback", null), null != n.dragDrop.onDragLeave && $.isFunction(n.dragDrop.onDragLeave) && n.dragDrop.onDragLeave(e, l, p, o, s)
                            }, 100, e)
                        },
                        onDrop: function(e) {
                            clearTimeout(f.dragDrop._timer), n.dragDrop.container.removeClass("fileuploader-dragging"), f.set("feedback", null), e && e.originalEvent && e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files && e.originalEvent.dataTransfer.files.length && (f.isUploadMode() ? f.onChange(e, e.originalEvent.dataTransfer.files) : s.prop("files", e.originalEvent.dataTransfer.files).trigger("change")), null != n.dragDrop.onDrop && $.isFunction(n.dragDrop.onDrop) && n.dragDrop.onDrop(e, l, p, o, s)
                        },
                        _dragLeaveCheck: function(e) {
                            var t = $(e.currentTarget);
                            return !(!t.is(n.dragDrop.container) && n.dragDrop.container.find(t).length)
                        }
                    },
                    clipboard: {
                        paste: function(e) {
                            if (f._assets.isIntoView(o) && e.originalEvent.clipboardData && e.originalEvent.clipboardData.items && e.originalEvent.clipboardData.items.length) {
                                var t = e.originalEvent.clipboardData.items;
                                f.clipboard.clean();
                                for (var i = 0; i < t.length; i++)
                                    if (-1 !== t[i].type.indexOf("image") || -1 !== t[i].type.indexOf("text/uri-list")) {
                                        var r = t[i].getAsFile(),
                                            a = 1 < n.clipboardPaste ? n.clipboardPaste : 2e3;
                                        r && (r._name = f._assets.generateFileName(-1 != r.type.indexOf("/") ? r.type.split("/")[1].toString().toLowerCase() : "png", "Clipboard "), f.set("feedback", f._assets.textParse(n.captions.paste, {
                                            ms: a / 1e3
                                        })), f.clipboard._timer = setTimeout(function() {
                                            f.set("feedback", null), f.onChange(e, [r])
                                        }, a - 2))
                                    }
                            }
                        },
                        clean: function() {
                            f.clipboard._timer && (clearTimeout(f.clipboard._timer), delete f.clipboard._timer, f.set("feedback", null))
                        }
                    },
                    files: {
                        add: function(e, t) {
                            var o, r, i, a = e._name || e.name,
                                l = e.size,
                                p = f._assets.bytesToText(l),
                                d = e.type,
                                u = d ? d.split("/", 1).toString().toLowerCase() : "",
                                c = -1 != a.indexOf(".") ? a.split(".").pop().toLowerCase() : "",
                                h = a.substr(0, a.length - (-1 != a.indexOf(".") ? c.length + 1 : c.length));
                            return (i = {
                                name: a,
                                title: h,
                                size: l,
                                size2: p,
                                type: d,
                                format: u,
                                extension: c,
                                data: i = e.data || {},
                                file: e.file || e,
                                reader: {
                                    read: function(e, t, o) {
                                        return f.files.read(r, e, t, o)
                                    }
                                },
                                id: "updated" == t ? e.id : Date.now(),
                                input: "choosed" == t ? s : null,
                                html: null,
                                choosed: "choosed" == t,
                                appended: "appended" == t || "updated" == t,
                                uploaded: "uploaded" == t
                            }).data.listProps || (i.data.listProps = {}), !i.data.url && i.appended && (i.data.url = i.file), "updated" != t ? (f._itFl.push(i), o = f._itFl.length - 1, r = f._itFl[o]) : (o = f._itFl.indexOf(e), f._itFl[o] = r = i), r.remove = function() {
                                f.files.remove(r)
                            }, n.editor && "image" == u && (r.editor = {
                                rotate: !1 !== n.editor.rotation ? function(e) {
                                    f.editor.rotate(r, e)
                                } : null,
                                cropper: !1 !== n.editor.cropper ? function(e) {
                                    f.editor.crop(r, e)
                                } : null,
                                save: function(e, t, o, i) {
                                    f.editor.save(r, t, o, e, i)
                                }
                            }), e.local && (r.local = e.local), o
                        },
                        read: function(d, e, t, i, r) {
                            if (f.isFileReaderSupported() && !d.data.readerSkip) {
                                var a = new FileReader,
                                    u = window.URL || window.webkitURL,
                                    c = r && d.data.thumbnail,
                                    h = "string" != typeof d.file,
                                    m = function() {
                                        var e = d.reader._callbacks || [];
                                        d.reader._timer && (clearTimeout(d.reader._timer), delete d.reader._timer), delete d.reader._callbacks, delete d.reader._FileReader;
                                        for (var t = 0; t < e.length; t++) $.isFunction(e[t]) && e[t](d, l, p, o, s);
                                        n.onFileRead && $.isFunction(n.onFileRead) && n.onFileRead(d, l, p, o, s)
                                    };
                                if ((d.reader.src || d.reader._FileReader) && !i || (d.reader = {
                                    _FileReader: a,
                                    _callbacks: [],
                                    read: d.reader.read
                                }), d.reader.src && !i) return e && $.isFunction(e) ? e(d, l, p, o, s) : null;
                                if (e && d.reader._callbacks && (d.reader._callbacks.push(e), 1 < d.reader._callbacks.length)) return;
                                if ("astext" == d.format) a.onload = function(e) {
                                    var t = document.createElement("div");
                                    d.reader.node = t, d.reader.src = e.target.result, d.reader.length = e.target.result.length, t.innerHTML = d.reader.src.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;"), m()
                                }, a.onerror = function() {
                                    m(), d.reader = {
                                        read: d.reader.read
                                    }
                                }, h ? a.readAsText(d.file) : $.ajax({
                                    url: d.file,
                                    success: function(e) {
                                        a.onload({
                                            target: {
                                                result: e
                                            }
                                        })
                                    },
                                    error: function() {
                                        a.onerror()
                                    }
                                });
                                else if ("image" == d.format || c) {
                                    if (a.onload = function(e) {
                                        function t() {
                                            d.data && d.data.readerCrossOrigin && p.setAttribute("crossOrigin", d.data.readerCrossOrigin), p.src = e.target.result + (!d.data.readerForce && !i || h || c || -1 != e.target.result.indexOf("data:image") ? "" : (-1 == e.target.result.indexOf("?") ? "?" : "&") + "d=" + Date.now()), p.onload = function() {
                                                if (d.reader.exifOrientation) {
                                                    var e = document.createElement("canvas"),
                                                        t = e.getContext("2d"),
                                                        o = p,
                                                        i = Math.abs(d.reader.exifOrientation),
                                                        n = d.reader.exifOrientation < 0 ? d.reader.exifOrientation : 0,
                                                        r = [0, 180];
                                                    1 == i && (i = 0), e.width = o.naturalWidth, e.height = o.naturalHeight, t.drawImage(o, 0, 0), e.width = -1 < r.indexOf(i) ? o.naturalWidth : o.naturalHeight, e.height = -1 < r.indexOf(i) ? o.naturalHeight : o.naturalWidth;
                                                    var a = i * Math.PI / 180,
                                                        l = .5 * e.width,
                                                        s = .5 * e.height;
                                                    return t.clearRect(0, 0, e.width, e.height), t.translate(l, s), t.rotate(a), t.translate(.5 * -o.naturalWidth, .5 * -o.naturalHeight), n && (-1 < [-1, -180].indexOf(n) ? (t.translate(e.width, 0), t.scale(-1, 1)) : -1 < [-90, -270].indexOf(n) && (t.translate(0, e.width), t.scale(1, -1))), t.drawImage(o, 0, 0), t.setTransform(1, 0, 0, 1, 0, 0), p.src = e.toDataURL(d.type, 1), void delete d.reader.exifOrientation
                                                }
                                                d.reader.node = p, d.reader.src = p.src, d.reader.width = p.width, d.reader.height = p.height, d.reader.ratio = f._assets.pxToRatio(d.reader.width, d.reader.height), b && u.revokeObjectURL(b), m(), c && (d.reader = {
                                                    read: d.reader.read
                                                })
                                            }, p.onerror = function() {
                                                m(), d.reader = {
                                                    read: d.reader.read
                                                }
                                            }
                                        }
                                        var p = new Image;
                                        n.thumbnails.exif && d.choosed ? f._assets.getExifOrientation(d.file, function(e) {
                                            e && (d.reader.exifOrientation = e), t()
                                        }) : t()
                                    }, a.onerror = function() {
                                        m(), d.reader = {
                                            read: d.reader.read
                                        }
                                    }, !c && d.size > f._assets.toBytes(n.reader.maxSize)) return a.onerror();
                                    h ? n.thumbnails.useObjectUrl && n.thumbnails.canvasImage && u ? a.onload({
                                        target: {
                                            result: b = u.createObjectURL(d.file)
                                        }
                                    }) : a.readAsDataURL(d.file) : a.onload({
                                        target: {
                                            result: c ? d.data.thumbnail : d.file
                                        }
                                    })
                                } else if ("video" == d.format || "audio" == d.format) {
                                    var g = (v = document.createElement(d.format)).canPlayType(d.type);
                                    if (a.onerror = function() {
                                        d.reader.node = null, m(), d.reader = {
                                            read: d.reader.read
                                        }
                                    }, u && "" !== g) {
                                        if (r && !n.thumbnails.videoThumbnail) return d.reader.node = v, m(), void(d.reader = {
                                            read: d.reader.read
                                        });
                                        b = h ? u.createObjectURL(d.file) : d.file, v.onloadedmetadata = function() {
                                            d.reader.node = v, d.reader.src = v.src, d.reader.duration = v.duration, d.reader.duration2 = f._assets.secondsToText(v.duration), "video" == d.format && (d.reader.width = v.videoWidth, d.reader.height = v.videoHeight, d.reader.ratio = f._assets.pxToRatio(d.reader.width, d.reader.height))
                                        }, v.onerror = function() {
                                            m(), d.reader = {
                                                read: d.reader.read
                                            }
                                        }, v.onloadeddata = function() {
                                            if ("video" == d.format) {
                                                var e = document.createElement("canvas"),
                                                    t = e.getContext("2d");
                                                e.width = v.videoWidth, e.height = v.videoHeight, t.drawImage(v, 0, 0, e.width, e.height), d.reader.frame = f._assets.isBlankCanvas(e) ? null : e.toDataURL(), e = t = null
                                            }
                                            m()
                                        }, setTimeout(function() {
                                            d.data && d.data.readerCrossOrigin && v.setAttribute("crossOrigin", d.data.readerCrossOrigin), v.src = b + "#t=1"
                                        }, 100)
                                    } else a.onerror()
                                } else if ("application/pdf" == d.type && n.thumbnails.pdf && !t) {
                                    var v = document.createElement("iframe"),
                                        b = h ? u.createObjectURL(d.file) : d.file;
                                    (n.thumbnails.pdf.viewer || f._assets.hasPlugin("pdf")) && (v.src = (n.thumbnails.pdf.viewer || "") + b, d.reader.node = v, d.reader.src = b), m()
                                } else a.onload = function(e) {
                                    d.reader.src = e.target.result, d.reader.length = e.target.result.length, m()
                                }, a.onerror = function(e) {
                                    m(), d.reader = {
                                        read: d.reader.read
                                    }
                                }, h ? a[t || "readAsBinaryString"](d.file) : m();
                                d.reader._timer = setTimeout(a.onerror, r ? n.reader.thumbnailTimeout : n.reader.timeout)
                            } else e && e(d, l, p, o, s);
                            return null
                        },
                        list: function(r, a, e, t) {
                            var d = [];
                            return !n.sorter || e || t && "ignoreSorter" == t || f.sorter.save(!0), $.each(f._itFl, function(e, t) {
                                var o = t;
                                if (o.upload && !o.uploaded) return !0;
                                if ((a || r) && (o = (o.choosed && !o.uploaded ? "0:/" : "") + (a && null !== f.files.getItemAttr(t, a) ? f.files.getItemAttr(o, a) : o.local || o["string" == typeof o.file ? "file" : "name"])), r && (o = {
                                    file: o
                                }, t.editor && (t.editor.crop || t.editor.rotation) && (o.editor = {}, t.editor.rotation && (o.editor.rotation = t.editor.rotation), t.editor.crop && (o.editor.crop = t.editor.crop)), void 0 !== t.index && (o.index = t.index), t.data && t.data.listProps))
                                    for (var i in t.data.listProps) o[i] = t.data.listProps[i];
                                d.push(o)
                            }), d = n.onListInput && $.isFunction(n.onListInput) ? n.onListInput(d, f._itFl, n.listInput, l, p, o, s) : d, r ? JSON.stringify(d) : d
                        },
                        check: function(i, r, e) {
                            var a = ["warning", null, !1, !1];
                            if (null != n.limit && e && r.length + f._itFl.length - 1 > n.limit) return a[1] = f._assets.textParse(n.captions.errors.filesLimit), a[3] = !0, a;
                            if (null != n.maxSize && e) {
                                var d = 0;
                                if ($.each(f._itFl, function(e, t) {
                                    d += t.size
                                }), d -= i.size, $.each(r, function(e, t) {
                                    d += t.size
                                }), d > f._assets.toBytes(n.maxSize)) return a[1] = f._assets.textParse(n.captions.errors.filesSizeAll), a[3] = !0, a
                            }
                            if (null != n.onFilesCheck && $.isFunction(n.onFilesCheck) && e && !1 === n.onFilesCheck(r, n, l, p, o, s)) return a[3] = !0, a;
                            if (null != n.extensions && -1 == $.inArray(i.extension, n.extensions) && !n.extensions.filter(function(e) {
                                return i.type.length && (-1 < e.indexOf(i.type) || -1 < e.indexOf(i.format + "/*"))
                            }).length) return a[1] = f._assets.textParse(n.captions.errors.filesType, i), a;
                            if (null != n.disallowedExtensions && (-1 < $.inArray(i.extension, n.disallowedExtensions) || n.disallowedExtensions.filter(function(e) {
                                return !i.type.length || -1 < e.indexOf(i.type) || -1 < e.indexOf(i.format + "/*")
                            }).length)) return a[1] = f._assets.textParse(n.captions.errors.filesType, i), a;
                            if (null != n.fileMaxSize && i.size > f._assets.toBytes(n.fileMaxSize)) return a[1] = f._assets.textParse(n.captions.errors.fileSize, i), a;
                            if (0 == i.size && "" == i.type) return a[1] = f._assets.textParse(n.captions.errors.remoteFile, i), a;
                            if ((4096 == i.size || 64 == i.size) && "" == i.type) return a[1] = f._assets.textParse(n.captions.errors.folderUpload, i), a;
                            if (!n.skipFileNameCheck) {
                                d = !1;
                                if ($.each(f._itFl, function(e, t) {
                                    if (t != i && 1 == t.choosed && t.file && t.name == i.name) return d = !0, t.file.size != i.size || t.file.type != i.type || i.file.lastModified && t.file.lastModified && t.file.lastModified != i.file.lastModified || !(1 < r.length) ? (a[1] = f._assets.textParse(n.captions.errors.fileName, i), a[2] = !1) : a[2] = !0, !1
                                }), d) return a
                            }
                            return !0
                        },
                        append: function(e) {
                            if ((e = $.isArray(e) ? e : [e]).length) {
                                for (var t, i = 0; i < e.length; i++) f._assets.keyCompare(e[i], ["name", "file", "size", "type"]) && (t = f._itFl[f.files.add(e[i], "appended")], n.thumbnails && f.thumbnails.item(t));
                                return f.set("feedback", null), f.set("listInput", null), n.afterSelect && $.isFunction(n.afterSelect) && n.afterSelect(l, p, o, s), 1 != e.length || t
                            }
                        },
                        update: function(e, t) {
                            if (!(-1 == f._itFl.indexOf(e) || e.upload && e.upload.$ajax)) {
                                var o = e,
                                    i = f.files.add($.extend(e, t), "updated");
                                (e = f._itFl[i]).popup && e.popup.close && e.popup.close(), n.thumbnails && o.html && f.thumbnails.item(e, o.html), f.set("listInput", null)
                            }
                        },
                        find: function(o) {
                            var i = null;
                            return $.each(f._itFl, function(e, t) {
                                if (t.html && t.html.is(o)) return i = t, !1
                            }), i
                        },
                        remove: function(i, r) {
                            if (r || !n.onRemove || !$.isFunction(n.onRemove) || !1 !== n.onRemove(i, l, p, o, s)) {
                                if (i.html && (n.thumbnails.onItemRemove && $.isFunction(n.thumbnails.onItemRemove) && !r ? n.thumbnails.onItemRemove(i.html, l, p, o, s) : i.html.remove()), i.upload && i.upload.$ajax && i.upload.cancel && i.upload.cancel(!0), i.popup && i.popup.close && (i.popup.node = null, i.popup.close()), i.reader.src && (i.reader.node = null, URL.revokeObjectURL(i.reader.src)), i.input) {
                                    var a = !0;
                                    $.each(f._itFl, function(e, t) {
                                        if (i != t && (i.input == t.input || r && 1 < i.input.get(0).files.length)) return a = !1
                                    }), a && (f.isAddMoreMode() && 1 < sl.length ? (f.set("nextInput"), sl.splice(sl.indexOf(i.input), 1), i.input.remove()) : f.set("input", ""))
                                } - 1 < f._pfrL.indexOf(i) && f._pfrL.splice(f._pfrL.indexOf(i), 1), -1 < f._pfuL.indexOf(i) && f._pfuL.splice(f._pfuL.indexOf(i), 1), -1 < f._itFl.indexOf(i) && f._itFl.splice(f._itFl.indexOf(i), 1), i = null, 0 == f._itFl.length && f.reset(), f.set("feedback", null), f.set("listInput", null)
                            }
                        },
                        getItemAttr: function(e, t) {
                            var o = null;
                            return e && (void 0 !== e[t] ? o = e[t] : e.data && void 0 !== e.data[t] && (o = e.data[t])), o
                        },
                        clear: function(e) {
                            for (var t = 0; t < f._itFl.length;) {
                                var i = f._itFl[t];
                                e || !i.appended ? (i.html && i.html && f._itFl[t].html.remove(), i.upload && i.upload.$ajax && f.upload.cancel(i), f._itFl.splice(t, 1)) : t++
                            }
                            f.set("feedback", null), f.set("listInput", null), 0 == f._itFl.length && n.onEmpty && $.isFunction(n.onEmpty) && n.onEmpty(l, p, o, s)
                        }
                    },
                    reset: function(e) {
                        e && (f.clipboard._timer && f.clipboard.clean(), $.each(sl, function(e, t) {
                            t.is(s) || t.remove()
                        }), sl = [], f.set("input", "")), f._itRl = [], f._pfuL = [], f._pfrL = [], f.files.clear(e)
                    },
                    destroy: function() {
                        f.reset(!0), f.bindUnbindEvents(!1), s.closest("form").off("reset", f.reset), s.removeAttr("style"), p.before(s), delete s.get(0).FileUploader, p.remove(), p = o = l = null
                    },
                    _assets: {
                        toBytes: function(e) {
                            return 1048576 * parseInt(e)
                        },
                        bytesToText: function(e) {
                            if (0 == e) return "0 Byte";
                            var t = Math.floor(Math.log(e) / Math.log(1024)),
                                o = e / Math.pow(1024, t),
                                i = !1;
                            return 1e3 < o && t < 8 && (t += 1, o = e / Math.pow(1024, t), i = !0), o.toPrecision(i ? 2 : 3) + " " + ["Bytes", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB"][t]
                        },
                        escape: function(e) {
                            return ("" + e).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;")
                        },
                        secondsToText: function(e, t) {
                            e = parseInt(Math.round(e), 10);
                            var o = Math.floor(e / 3600),
                                i = Math.floor((e - 3600 * o) / 60),
                                n = "";
                            return (0 < o || !t) && (n += (o < 10 ? "0" : "") + o + (t ? "h " : ":")), (0 < i || !t) && (n += (i < 10 && !t ? "0" : "") + i + (t ? "m " : ":")), n += ((e = e - 3600 * o - 60 * i) < 10 && !t ? "0" : "") + e + (t ? "s" : "")
                        },
                        pxToRatio: function(e, t) {
                            var o = function(e, t) {
                                    return 0 == t ? e : o(t, e % t)
                                },
                                i = o(e, t);
                            return [e / i, t / i]
                        },
                        ratioToPx: function(e, t, o) {
                            return (o = (o + "").split(":")).length < 2 ? null : [t / o[1] * o[0], e / o[0] * o[1], o[0], o[1]]
                        },
                        hasAttr: function(e, t) {
                            var o = (t = t || s).attr(e);
                            return !(!o || void 0 === o)
                        },
                        copyAllAttributes: function(e, t) {
                            return $.each(t.get(0).attributes, function() {
                                "required" != this.name && "type" != this.name && "id" != this.name && e.attr(this.name, this.value)
                            }), t.get(0).FileUploader && (e.get(0).FileUploader = t.get(0).FileUploader), e
                        },
                        getAllEvents: function(e) {
                            e = e || s;
                            var t = [];
                            for (var o in e = e.get ? e.get(0) : e) 0 === o.indexOf("on") && t.push(o.slice(2));
                            return -1 == t.indexOf("change") && t.push("change"), t.join(" ")
                        },
                        isIntoView: function(e) {
                            var t = $(window).scrollTop(),
                                o = t + window.innerHeight,
                                i = e.offset().top,
                                n = i + e.outerHeight();
                            return t < i && n < o
                        },
                        isBlankCanvas: function(e) {
                            var t = document.createElement("canvas"),
                                o = !1;
                            t.width = e.width, t.height = e.height;
                            try {
                                o = e.toDataURL() == t.toDataURL()
                            } catch (e) {}
                            return t = null, o
                        },
                        generateFileName: function(e, t) {
                            function o(e) {
                                return e < 10 && (e = "0" + e), e
                            }
                            var i = new Date;
                            e = e ? "." + e : "";
                            return (t = t || "") + i.getFullYear() + "-" + o(i.getMonth() + 1) + "-" + o(i.getDate()) + " " + o(i.getHours()) + "-" + o(i.getMinutes()) + "-" + o(i.getSeconds()) + e
                        },
                        arrayBufferToBase64: function(e) {
                            for (var t = "", o = new Uint8Array(e), i = 0; i < o.byteLength; i++) t += String.fromCharCode(o[i]);
                            return window.btoa(t)
                        },
                        dataURItoBlob: function(e, t) {
                            for (var o = atob(e.split(",")[1]), i = e.split(",")[0].split(":")[1].split(";")[0], n = new ArrayBuffer(o.length), r = new Uint8Array(n), a = 0; a < o.length; a++) r[a] = o.charCodeAt(a);
                            var l = new DataView(n);
                            return new Blob([l.buffer], {
                                type: t || i
                            })
                        },
                        getExifOrientation: function(e, p) {
                            var t = new FileReader,
                                d = {
                                    1: 0,
                                    2: -1,
                                    3: 180,
                                    4: -180,
                                    5: -90,
                                    6: 90,
                                    7: -270,
                                    8: 270
                                };
                            t.onload = function(e) {
                                var t = new DataView(e.target.result),
                                    o = 1;
                                if (t.byteLength && 65496 == t.getUint16(0, !1))
                                    for (var i = t.byteLength, n = 2; n < i && !(t.getUint16(n + 2, !1) <= 8);) {
                                        var r = t.getUint16(n, !1);
                                        if (n += 2, 65505 == r) {
                                            if (1165519206 != t.getUint32(n += 2, !1)) break;
                                            var a, l = 18761 == t.getUint16(n += 6, !1);
                                            n += t.getUint32(n + 4, l), a = t.getUint16(n, l), n += 2;
                                            for (var s = 0; s < a; s++)
                                                if (274 == t.getUint16(n + 12 * s, l)) {
                                                    o = t.getUint16(n + 12 * s + 8, l), i = 0;
                                                    break
                                                }
                                        } else {
                                            if (65280 != (65280 & r)) break;
                                            n += t.getUint16(n, !1)
                                        }
                                    }
                                p && p(d[o] || 0)
                            }, t.onerror = function() {
                                p && p("")
                            }, t.readAsArrayBuffer(e)
                        },
                        textParse: function(text, opts, noOptions) {
                            switch (opts = noOptions ? opts || {} : $.extend({}, {
                                limit: n.limit,
                                maxSize: n.maxSize,
                                fileMaxSize: n.fileMaxSize,
                                extensions: n.extensions ? n.extensions.join(", ") : null,
                                captions: n.captions
                            }, opts), typeof text) {
                                case "string":
                                    for (var key in opts) - 1 < ["name", "file", "type", "size"].indexOf(key) && (opts[key] = f._assets.escape(opts[key]));
                                    text = text.replace(/\$\{(.*?)\}/g, function(match, a) {
                                        var a = a.replace(/ /g, ""),
                                            r = void 0 !== opts[a] && null != opts[a] ? opts[a] : "";
                                        if (-1 < ["reader.node"].indexOf(a)) return match;
                                        if (-1 < a.indexOf(".") || -1 < a.indexOf("[]")) {
                                            var x = a.substr(0, -1 < a.indexOf(".") ? a.indexOf(".") : -1 < a.indexOf("[") ? a.indexOf("[") : a.length),
                                                y = a.substring(x.length);
                                            if (opts[x]) try {
                                                r = eval('opts["' + x + '"]' + y)
                                            } catch (e) {
                                                r = ""
                                            }
                                        }
                                        return r = $.isFunction(r) ? f._assets.textParse(r) : r, r || ""
                                    });
                                    break;
                                case "function":
                                    text = f._assets.textParse(text(opts, l, p, o, s, f._assets.textParse), opts, noOptions)
                            }
                            return opts = null, text
                        },
                        textToColor: function(e) {
                            if (!e || 0 == e.length) return !1;
                            for (var t = 0, o = 0; t < e.length; o = e.charCodeAt(t++) + ((o << 5) - o));
                            t = 0;
                            for (var i = "#"; t < 3; i += ("00" + (o >> 2 * t++ & 255).toString(16)).slice(-2));
                            return i
                        },
                        isBrightColor: function(e) {
                            var t, o, i;
                            return 194 < ((i = (t = e) && t.constructor == Array && 3 == t.length ? t : (o = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(t)) ? [parseInt(o[1]), parseInt(o[2]), parseInt(o[3])] : (o = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(t)) ? [2.55 * parseFloat(o[1]), 2.55 * parseFloat(o[2]), 2.55 * parseFloat(o[3])] : (o = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(t)) ? [parseInt(o[1], 16), parseInt(o[2], 16), parseInt(o[3], 16)] : (o = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(t)) ? [parseInt(o[1] + o[1], 16), parseInt(o[2] + o[2], 16), parseInt(o[3] + o[3], 16)] : "undefined" != typeof colors ? colors[$.trim(t).toLowerCase()] : null) ? .2126 * i[0] + .7152 * i[1] + .0722 * i[2] : null)
                        },
                        keyCompare: function(e, t) {
                            for (var o = 0; o < t.length; o++)
                                if (!$.isPlainObject(e) || !e.hasOwnProperty(t[o])) throw new Error('Could not find valid *strict* attribute "' + t[o] + '" in ' + JSON.stringify(e, null, 4));
                            return !0
                        },
                        dialogs: {
                            alert: n.dialogs.alert,
                            confirm: n.dialogs.confirm
                        },
                        hasPlugin: function(e) {
                            if (navigator.plugins && navigator.plugins.length)
                                for (var t in navigator.plugins)
                                    if (navigator.plugins[t].name && -1 < navigator.plugins[t].name.toLowerCase().indexOf(e)) return !0;
                            return !1
                        },
                        isIE: function() {
                            return -1 < navigator.userAgent.indexOf("MSIE ") || -1 < navigator.userAgent.indexOf("Trident/") || -1 < navigator.userAgent.indexOf("Edge")
                        },
                        isMobile: function() {
                            return void 0 !== window.orientation || -1 !== navigator.userAgent.indexOf("IEMobile")
                        }
                    },
                    isSupported: function() {
                        return s && s.get(0).files
                    },
                    isFileReaderSupported: function() {
                        return window.File && window.FileList && window.FileReader
                    },
                    isDefaultMode: function() {
                        return !(n.upload || n.addMore && 1 != n.limit)
                    },
                    isAddMoreMode: function() {
                        return !n.upload && n.addMore && 1 != n.limit
                    },
                    isUploadMode: function() {
                        return n.upload
                    },
                    _itFl: [],
                    _pfuL: [],
                    _pfrL: [],
                    disabled: !1,
                    locked: !1,
                    rendered: !1
                };
            return n.enableApi && (s.get(0).FileUploader = {
                open: function() {
                    s.trigger("click")
                },
                getOptions: function() {
                    return n
                },
                getParentEl: function() {
                    return p
                },
                getInputEl: function() {
                    return s
                },
                getNewInputEl: function() {
                    return o
                },
                getListEl: function() {
                    return l
                },
                getListInputEl: function() {
                    return n.listInput
                },
                getFiles: function() {
                    return f._itFl
                },
                getChoosedFiles: function() {
                    return f._itFl.filter(function(e) {
                        return e.choosed
                    })
                },
                getAppendedFiles: function() {
                    return f._itFl.filter(function(e) {
                        return e.appended
                    })
                },
                getUploadedFiles: function() {
                    return f._itFl.filter(function(e) {
                        return e.uploaded
                    })
                },
                getFileList: function(e, t) {
                    return f.files.list(e, t, !0)
                },
                updateFileList: function() {
                    return f.set("listInput", null), !0
                },
                setOption: function(e, t) {
                    return n[e] = t, !0
                },
                findFile: function(e) {
                    return f.files.find(e)
                },
                add: function(e, t, o) {
                    if (!f.isUploadMode()) return !1;
                    var i;
                    if (e instanceof Blob) i = e;
                    else {
                        var n = /data:[a-z]+\/[a-z]+\;base64\,/.test(e) ? e : "data:" + t + ";base64," + btoa(e);
                        i = f._assets.dataURItoBlob(n, t)
                    }
                    return i._name = o || f._assets.generateFileName(-1 != i.type.indexOf("/") ? i.type.split("/")[1].toString().toLowerCase() : "File "), f.onChange(null, [i]), !0
                },
                append: function(e) {
                    return f.files.append(e)
                },
                update: function(e, t) {
                    return f.files.update(e, t)
                },
                remove: function(e) {
                    return e = e.jquery ? f.files.find(e) : e, -1 < f._itFl.indexOf(e) && (f.files.remove(e), !0)
                },
                uploadStart: function() {
                    var e = this.getChoosedFiles() || [];
                    if (f.isUploadMode() && 0 < e.length && !e[0].uploaded)
                        for (var t = 0; t < e.length; t++) f.upload.send(e[t])
                },
                reset: function() {
                    return f.reset(!0), !0
                },
                disable: function(e) {
                    return f.set("disabled", !0), e && (f.locked = !0), !0
                },
                enable: function() {
                    return f.set("disabled", !1), !(f.locked = !1)
                },
                destroy: function() {
                    return f.destroy(), !0
                },
                isEmpty: function() {
                    return 0 == f._itFl.length
                },
                isDisabled: function() {
                    return f.disabled
                },
                isRendered: function() {
                    return f.rendered
                },
                assets: f._assets,
                getPluginMode: function() {
                    return f.isDefaultMode() ? "default" : f.isAddMoreMode() ? "addMore" : f.isUploadMode() ? "upload" : void 0
                }
            }), f.init(), this
        })
    }, $.fileuploader = {
        getInstance: function(e) {
            var t = e.prop ? e : $(e);
            return t.length ? t.get(0).FileUploader : null
        }
    }, $.fn.fileuploader.languages = {
        cz: {
            button: function(e) {
                return "Procházet " + (1 == e.limit ? "soubor" : "soubory")
            },
            feedback: function(e) {
                return "Vyberte " + (1 == e.limit ? "soubor" : "soubory") + ", který chcete nahrát"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "vybráno souborů" : "vybrán soubor")
            },
            confirm: "Potvrdit",
            cancel: "Zrušeni",
            name: "Jméno",
            type: "Format",
            size: "Velikost",
            dimensions: "Rozměry",
            duration: "Trvání",
            crop: "Oříznout",
            rotate: "Otočit",
            sort: "Roztřídit",
            open: "Otevřít",
            download: "Stáhnout",
            remove: "Odstranit",
            drop: "Pro nahrání přetahněte soubor sem",
            paste: '<div class="fileuploader-pending-loader"></div> Vkládání souboru, klikněte zde pro zrušeni',
            removeConfirmation: "Jste si jisti, že chcete odstranit tento soubor?",
            errors: {
                filesLimit: function(e) {
                    return "Pouze ${limit} " + (1 == e.limit ? "soubor může být nahrán" : "soubory mohou byt nahrané") + "."
                },
                filesType: "Pouze ${extensions} soubory mohou byt nahrané.",
                fileSize: "${name} příliš velký! Prosím, vyberte soubor do velikosti ${fileMaxSize} MB.",
                filesSizeAll: "Vybraný soubor je příliš velký! Prosím, vyberte soubor do velikosti ${maxSize} MB.",
                fileName: "Soubor s tímto názvem  ${name} byl už vybran.",
                remoteFile: "Vzdálené soubory nejsou povoleny.",
                folderUpload: "Složky nejsou povolené."
            }
        },
        de: {
            button: function(e) {
                return (1 == e.limit ? "Datei" : "Dateien") + " durchsuchen"
            },
            feedback: function(e) {
                return (1 == e.limit ? "Datei" : "Dateien") + " zum Hochladen auswählen"
            },
            feedback2: function(e) {
                return e.length + " " + (1 == e.length ? "Datei" : "Dateien") + " ausgewählt"
            },
            confirm: "Speichern",
            cancel: "Schließen",
            name: "Name",
            type: "Typ",
            size: "Größe",
            dimensions: "Format",
            duration: "Länge",
            crop: "Crop",
            rotate: "Rotieren",
            sort: "Sortieren",
            open: "Öffnen",
            download: "Herunterladen",
            remove: "Löschen",
            drop: "Die Dateien hierher ziehen, um sie hochzuladen",
            paste: '<div class="fileuploader-pending-loader"></div> Eine Datei wird eingefügt. Klicken Sie hier zum abzubrechen',
            removeConfirmation: "Möchten Sie diese Datei wirklich löschen?",
            errors: {
                filesLimit: function(e) {
                    return "Nur ${limit} " + (1 == e.limit ? "Datei darf" : "Dateien dürfen") + " hochgeladen werden."
                },
                filesType: "Nur ${extensions} Dateien dürfen hochgeladen werden.",
                fileSize: "${name} ist zu groß! Bitte wählen Sie eine Datei bis zu ${fileMaxSize} MB.",
                filesSizeAll: "Die ausgewählten Dateien sind zu groß! Bitte wählen Sie Dateien bis zu ${maxSize} MB.",
                fileName: "Eine Datei mit demselben Namen ${name} ist bereits ausgewählt.",
                remoteFile: "Remote-Dateien sind nicht zulässig.",
                folderUpload: "Ordner sind nicht erlaubt."
            }
        },
        dk: {
            button: function(e) {
                return "Gennemse " + (1 == e.limit ? "fil" : "filer")
            },
            feedback: function(e) {
                return "Vælg " + (1 == e.limit ? "fil" : "filer") + " til upload"
            },
            feedback2: function(e) {
                return e.length + " " + (1 == e.length ? "fil" : "filer") + " er valgt"
            },
            confirm: "Bekræft",
            cancel: "Fortrydl",
            name: "Navn",
            type: "Type",
            size: "Størrelse",
            dimensions: "Dimensioner",
            duration: "Varighed’",
            crop: "Tilpas",
            rotate: "Rotér",
            sort: "Sorter",
            open: "Åben",
            download: "Hent",
            remove: "Slet",
            drop: "Drop filer her til upload",
            paste: "Overfør fil, klik her for at afbryde",
            removeConfirmation: "Er du sikker på, du ønsker at slette denne fil?",
            errors: {
                filesLimit: function(e) {
                    return "Du kan kun uploade ${limit} " + (1 == e.limit ? "fil" : "filer") + " ad gangen."
                },
                filesType: "Det er kun tilladt at uploade ${extensions} filer.",
                fileSize: "${name} er for stor! Vælg venligst en fil på højst ${fileMaxSize} MB.",
                filesSizeAll: "De valgte filer er for store! Vælg venligst filer op til ${maxSize} MB ialt.",
                fileName: "Du har allerede valgt en fil med navnet ${name}.",
                remoteFile: "Fremmede filer er ikke tilladt.",
                folderUpload: "Mapper er ikke tilladt."
            }
        },
        en: {
            button: function(e) {
                return "Browse " + (1 == e.limit ? "file" : "files")
            },
            feedback: function(e) {
                return "Choose " + (1 == e.limit ? "file" : "files") + " to upload"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? " files were" : " file was") + " chosen"
            },
            confirm: "Confirm",
            cancel: "Cancel",
            name: "Name",
            type: "Type",
            size: "Size",
            dimensions: "Dimensions",
            duration: "Duration",
            crop: "Crop",
            rotate: "Rotate",
            sort: "Sort",
            open: "Open",
            download: "Download",
            remove: "Delete",
            drop: "Drop the files here to upload",
            paste: '<div class="fileuploader-pending-loader"></div> Pasting a file, click here to cancel',
            removeConfirmation: "Are you sure you want to delete this file?",
            errors: {
                filesLimit: function(e) {
                    return "Only ${limit} " + (1 == e.limit ? "file" : "files") + " can be uploaded."
                },
                filesType: "Only ${extensions} files are allowed to be uploaded.",
                fileSize: "${name} is too large! Please choose a file up to ${fileMaxSize} MB.",
                filesSizeAll: "The chosen files are too large! Please select files up to ${maxSize} MB.",
                fileName: "A file with the same name ${name} is already selected.",
                remoteFile: "Remote files are not allowed.",
                folderUpload: "Folders are not allowed."
            }
        },
        es: {
            button: function(e) {
                return "Examinar " + (1 == e.limit ? "archivo" : "archivos")
            },
            feedback: function(e) {
                return "Selecciona " + (e.limit, "archivos") + " para subir"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "archivos seleccionados" : "archivo seleccionado")
            },
            confirm: "Guardar",
            cancel: "Anular",
            name: "Nombre",
            type: "Tipo",
            size: "Tamaño",
            dimensions: "Dimensiones",
            duration: "Duracion",
            crop: "Corta",
            rotate: "Rotar",
            sort: "Ordenar",
            open: "Abierto",
            download: "Descargar",
            remove: "Eliminar",
            drop: "Suelta los archivos aquí para subirlos",
            paste: '<div class="fileuploader-pending-loader"></div> Pegar un archivo, haga clic aquí para cancelar',
            removeConfirmation: "¿Estás seguro de que deseas eliminar este archivo?",
            errors: {
                filesLimit: function(e) {
                    return "Solo se pueden seleccionar ${limit} " + (1 == e.limit ? "archivo" : "archivos") + "."
                },
                filesType: "Solo se pueden seleccionar archivos ${extensions}.",
                fileSize: "${name} es demasiado grande! Por favor, seleccione un archivo hasta ${fileMaxSize} MB.",
                filesSizeAll: "¡Los archivos seleccionados son demasiado grandes! Por favor seleccione archivos de hasta ${maxSize} MB.",
                fileName: "Un archivo con el mismo nombre ${name} ya está seleccionado.",
                remoteFile: "Los archivos remotos no están permitidos.",
                folderUpload: "No se permiten carpetas."
            }
        },
        fr: {
            button: function(e) {
                return "Parcourir " + (1 == e.limit ? "le fichier" : "les fichiers")
            },
            feedback: function(e) {
                return "Choisir " + (1 == e.limit ? "le fichier " : "les fichiers") + " à télécharger"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "fichiers ont été choisis" : "fichier a été choisi")
            },
            confirm: "Confirmer",
            cancel: "Annuler",
            name: "Nom",
            type: "Type",
            size: "Taille",
            dimensions: "Dimensions",
            duration: "Durée",
            crop: "Recadrer",
            rotate: "Pivoter",
            sort: "Trier",
            download: "Télécharger",
            remove: "Supprimer",
            drop: "Déposez les fichiers ici pour les télécharger",
            paste: '<div class="fileuploader-pending-loader"></div> Collant un fichier, cliquez ici pour annuler.',
            removeConfirmation: "Êtes-vous sûr de vouloir supprimer ce fichier ?",
            errors: {
                filesLimit: "Seuls les fichiers ${limit} peuvent être téléchargés.",
                filesType: "Seuls les fichiers ${extensions} peuvent être téléchargés.",
                fileSize: "${name} est trop lourd, la limite est de ${fileMaxSize} MB.",
                filesSizeAll: "Les fichiers que vous avez choisis sont trop lourd, la limite totale est de ${maxSize} MB.",
                fileName: "Le fichier portant le nom ${name} est déjà sélectionné.",
                folderUpload: "Vous n'êtes pas autorisé à télécharger des dossiers."
            }
        },
        it: {
            button: function(e) {
                return "Sfoglia" + (1 == e.limit ? "il file" : "i file")
            },
            feedback: function(e) {
                return "Seleziona " + (1 == e.limit ? "file" : "i file") + " per caricare"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "i file sono scelti" : "il file è scelto")
            },
            confirm: "Conferma",
            cancel: "Cancella",
            name: "Nome",
            type: "Tipo file",
            size: "Dimensione file",
            dimensions: "Dimensioni",
            duration: "Durata",
            crop: "Taglia",
            rotate: "Ruota",
            sort: "Ordina",
            open: "Apri",
            download: "Scarica",
            remove: "Elimina",
            drop: "Posiziona il file qui per caricare",
            paste: '<div class="fileuploader-pending-loader"></div> Incolla file, clicca qui per cancellare',
            removeConfirmation: "Sei sicuro di voler eliminare il file?",
            errors: {
                filesLimit: "Solo ${limit} file possono essere caricati.",
                filesType: "Solo ${extensions} file possono essere caricati.",
                fileSize: "${name} è troppo grande! Scegli un file fino a ${fileMaxSize} MB.",
                filesSizeAll: "I file selezioni sono troppo grandi! Scegli un file fino a ${maxSize} MB.",
                fileName: "Un file con lo stesso nome ${name} è già selezionato.",
                remoteFile: "I file remoti non sono consentiti.",
                folderUpload: "Le cartelle non sono consentite."
            }
        },
        lv: {
            button: function(e) {
                return "Izvēlieties " + (1 == e.limit ? "fails" : "faili")
            },
            feedback: function(e) {
                return "Izvēliejaties " + (1 == e.limit ? "fails" : "faili") + " lejupielādēt"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "failus izvelēts" : "fails izvēlēts")
            },
            confirm: "Saglabāt",
            cancel: "Atcelt",
            name: "Vārds",
            type: "Formāts",
            size: "Izmērs",
            dimensions: "Izmēri",
            duration: "Ilgums",
            crop: "Nogriezt",
            rotate: "Pagriezt",
            sort: "Kārtot",
            open: "Atvērt",
            download: "Lejupielādēt",
            remove: "Dzēst",
            drop: "Lai augšupielādētu, velciet failus šeit",
            paste: '<div class="fileuploader-pending-loader"></div> Ievietojiet failu, noklikšķiniet šeit, lai atceltu',
            removeConfirmation: "Vai tiešām vēlaties izdzēst šo failu?",
            errors: {
                filesLimit: function(e) {
                    return "Tikai ${limit} " + (1 == e.limit ? "failu var augšupielādēt" : "failus var augšupielādēt") + "."
                },
                filesType: "Tikai ${extensions} failus var augšupielādēt.",
                fileSize: "${name} ir par lielu! Lūdzu, atlasiet failu līdz ${fileMaxSize} MB.",
                filesSizeAll: "Atlasītie faili ir pārāk lieli! Lūdzu, atlasiet failus līdz ${maxSize} MB.",
                fileName: "Fails ar tādu pašu nosaukumu ${name} jau ir atlasīts.",
                remoteFile: "Attālie faili nav atļauti.",
                folderUpload: "Mapes nav atļautas."
            }
        },
        nl: {
            button: function(e) {
                return (1 == e.limit ? "Bestand" : "Bestanden") + " kiezen"
            },
            feedback: function(e) {
                return "Kies " + (1 == e.limit ? "een bestand" : "bestanden") + " om te uploaden"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "bestanden" : "bestand") + " gekozen"
            },
            confirm: "Opslaan",
            cancel: "Annuleren",
            name: "Naam",
            type: "Type",
            size: "Grootte",
            dimensions: "Afmetingen",
            duration: "Duur",
            crop: "Uitsnijden",
            rotate: "Draaien",
            sort: "Sorteren",
            open: "Open",
            download: " Downloaden",
            remove: "Verwijderen",
            drop: "Laat de bestanden hier vallen om te uploaden",
            paste: '<div class="fileuploader-pending-loader"></div> Een bestand wordt geplakt, klik hier om te annuleren',
            removeConfirmation: "Weet u zeker dat u dit bestand wilt verwijderen?",
            errors: {
                filesLimit: function(e) {
                    return "Er " + (1 == e.limit ? "mag" : "mogen") + " slechts ${limit} " + (1 == e.limit ? "bestand" : "bestanden") + " worden geüpload."
                },
                filesType: "Alleen ${extensions} mogen worden geüpload.",
                fileSize: "${name} is te groot! Kies een bestand tot ${fileMaxSize} MB.",
                filesSizeAll: "De gekozen bestanden zijn te groot! Kies bestanden tot ${maxSize} MB.",
                fileName: "Een bestand met dezelfde naam ${name} is al gekozen.",
                remoteFile: "Externe bestanden zijn niet toegestaan.",
                folderUpload: "Mappen zijn niet toegestaan."
            }
        },
        pl: {
            button: function(e) {
                return "Wybierz " + (1 == e.limit ? "plik" : "pliki")
            },
            feedback: function(e) {
                return "Wybierz " + (1 == e.limit ? "plik" : "pliki") + " do przesłania"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "pliki zostały wybrane" : "plik został wybrany")
            },
            confirm: "Potwierdź",
            cancel: "Anuluj",
            name: "Nazwa",
            type: "Typ",
            size: "Rozmiar",
            dimensions: "Wymiary",
            duration: "Czas trwania",
            crop: "Przytnij",
            rotate: "Obróć",
            sort: "Sortuj",
            open: "Otwórz",
            download: "Pobierz",
            remove: "Usuń",
            drop: "Upuść pliki tutaj do przesłania",
            paste: '<div class="fileuploader-pending-loader"></div> Wklejając plik, kliknij tutaj, aby anulować',
            removeConfirmation: "Czy jesteś pewien, że chcesz usunąć ten plik?",
            errors: {
                filesLimit: function(e) {
                    return "Tylko ${limit} " + (1 == e.limit ? "plik" : "pliki") + " można wybrać."
                },
                filesType: "Tylko pliki ${extensions} mogą zostać pobrane.",
                fileSize: "Plik ${name} jest za duży! Proszę wybrać plik do ${fileMaxSize} MB.",
                filesSizeAll: "Wybrane pliki są za duże! Proszę wybrać pliki do  ${maxSize} MB.",
                fileName: ", Plik o tej samej nazwie ${name} już został wybrany.",
                remoteFile: "Zdalne pliki nie są dozwolone.",
                folderUpload: "Foldery nie są dozwolone."
            }
        },
        pt: {
            button: function(e) {
                return "Escolher " + (1 == e.limit ? "arquivo" : "arquivos")
            },
            feedback: function(e) {
                return "Escolha " + (1 == e.limit ? "arquivo" : "arquivos") + " a carregar"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "arquivos foram escolhidos" : "arquivo foi escolhido")
            },
            confirm: "Confirmar",
            cancel: "Cancelar",
            name: "Nome",
            type: "Tipo",
            size: "Tamanho",
            dimensions: "Dimensões",
            duration: "Duração",
            crop: "Recorte",
            rotate: "Girar",
            sort: "Ordenar",
            open: "Abrir",
            download: "Baixar",
            remove: "Excluir",
            drop: "Solte os arquivos aqui para fazer o upload",
            paste: '<div class="fileuploader-pending-loader"></div> Colando um arquivo, clique aqui para cancelar',
            removeConfirmation: "Tem certeza de que deseja excluir este arquivo?",
            errors: {
                filesLimit: function(e) {
                    return "Apenas ${limit} " + (1 == e.limit ? "arquivo a ser carregado" : "arquivos a serem carregados") + "."
                },
                filesType: "Somente arquivos ${extensions} podem ser carregados.",
                fileSize: "${name} é muito grande! Selecione um arquivo de até ${fileMaxSize} MB.",
                filesSizeAll: "Os arquivos selecionados são muito grandes! Selecione arquivos de até ${maxSize} MB.",
                fileName: "Um arquivo com o mesmo nome ${name} já está selecionado.",
                remoteFile: "Arquivos remotos não são permitidos.",
                folderUpload: "Pastas não são permitidas."
            }
        },
        ro: {
            button: function(e) {
                return "Atașează " + (1 == e.limit ? "fișier" : "fișiere")
            },
            feedback: function(e) {
                return "Selectează " + (1 == e.limit ? "fișier" : "fișiere") + " pentru încărcare"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? " fișiere" : " fișier") + " selectate"
            },
            confirm: "Confirmă",
            cancel: "Anulează",
            name: "Nume",
            type: "Tip",
            size: "Mărimea",
            dimensions: "Dimensiunea",
            duration: "Durata",
            crop: "Crop",
            rotate: "Rotire",
            sort: "Sortare",
            open: "Deschide",
            download: "Download",
            remove: "Șterge",
            drop: "Aruncați fișierele aici pentru a le încărca",
            paste: '<div class="fileuploader-pending-loader"></div> Se atașează fișier, faceți click aici pentru anulare',
            removeConfirmation: "Sigur doriți să ștergeți acest fișier?",
            errors: {
                filesLimit: function(e) {
                    return "Doar ${limit} " + (1 == e.limit ? "fișier poate fi selectat" : "fișiere pot fi selectat") + "."
                },
                filesType: "Doar fișierele ${extensions} pot fi încărcate.",
                fileSize: "${name} este prea mare! Vă rugăm să selectați un fișier până la ${fileMaxSize} MB.",
                filesSizeAll: "Fișierele selectate sunt prea mari! Vă rugăm să selectați fișiere până la ${maxSize} MB.",
                fileName: "Fișierul cu același numele ${nume} a fost deja selectat.",
                remoteFile: "Fișierele remote nu sunt permise.",
                folderUpload: "Folderele nu sunt permise."
            }
        },
        ru: {
            button: function(e) {
                return "Выбрать " + (1 == e.limit ? "файл" : "файлы")
            },
            feedback: function(e) {
                return "Выберите " + (1 == e.limit ? "файл" : "файлы") + " для загрузки"
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "файлов выбрано" : "файл выбран")
            },
            confirm: "Сохранить",
            cancel: "Отмена",
            name: "Имя",
            type: "Формат",
            size: "Размер",
            dimensions: "Размеры",
            duration: "Длительность",
            crop: "Обрезать",
            rotate: "Повернуть",
            sort: "Сортировть",
            open: "Открыть",
            download: "Скачать",
            remove: "Удалить",
            drop: "Перетащите файлы сюда для загрузки",
            paste: '<div class="fileuploader-pending-loader"></div> Вставка файла, нажмите здесь, чтобы отменить',
            removeConfirmation: "Вы уверены, что хотите удалить этот файл?",
            errors: {
                filesLimit: function(e) {
                    return "Только ${limit} " + (1 == e.limit ? "файл может быть загружен" : "файлов могут быть загружены") + "."
                },
                filesType: "Только ${extensions} файлы могут быть загружены.",
                fileSize: "${name} слишком большой! Пожалуйста, выберите файл до ${fileMaxSize} МБ.",
                filesSizeAll: "Выбранные файлы слишком большие! Пожалуйста, выберите файлы до ${maxSize} МБ.",
                fileName: "Файл с таким именем ${name} уже выбран.",
                remoteFile: "Удаленные файлы не допускаются.",
                folderUpload: "Папки не допускаются."
            }
        },
        tr: {
            button: function(e) {
                return (1 == e.limit ? "Dosya" : "Dosyaları") + " seç"
            },
            feedback: function(e) {
                return "Yüklemek istediğiniz " + (1 == e.limit ? "dosyayı" : "dosyaları") + " seçin."
            },
            feedback2: function(e) {
                return e.length + " " + (1 < e.length ? "dosyalar" : "dosya") + " seçildi."
            },
            confirm: "Onayla",
            cancel: "İptal",
            name: "İsim",
            type: "Tip",
            size: "Boyut",
            dimensions: "Boyutlar",
            duration: "Süre",
            crop: "Kırp",
            rotate: "Döndür",
            sort: "Sırala",
            open: "Aç",
            download: "İndir",
            remove: "Sil",
            drop: "Yüklemek için dosyaları buraya bırakın",
            paste: '<div class="fileuploader-pending-loader"></div> Bir dosyayı yapıştırmak veya iptal etmek için buraya tıklayın',
            removeConfirmation: "Bu dosyayı silmek istediğinizden emin misiniz?",
            errors: {
                filesLimit: function(e) {
                    return "Sadece ${limit} " + (1 == e.limit ? "dosya" : "dosyalar") + " yüklenmesine izin verilir."
                },
                filesType: "Sadece ${extensions} dosyaların yüklenmesine izin verilir.",
                fileSize: "${name} çok büyük! Lütfen ${fileMaxSize} MB'a kadar bir dosya seçin.",
                filesSizeAll: "Seçilen dosyalar çok büyük! Lütfen ${maxSize} MB'a kadar dosyaları seçin",
                fileName: "Aynı ada sahip bir dosya ${name} zaten seçilmiştir.",
                remoteFile: "Uzak dosyalara izin verilmez.",
                folderUpload: "Klasörlere izin verilmez."
            }
        }
    }, $.fn.fileuploader.defaults = {
        limit: null,
        maxSize: null,
        fileMaxSize: null,
        extensions: null,
        disallowedExtensions: null,
        changeInput: !0,
        inputNameBrackets: !0,
        theme: "default",
        thumbnails: {
            box: '<div class="fileuploader-items"><ul class="fileuploader-items-list"></ul></div>',
            boxAppendTo: null,
            item: '<li class="fileuploader-item"><div class="columns"><div class="column-thumbnail">${image}<span class="fileuploader-action-popup"></span></div><div class="column-title"><div title="${name}">${name}</div><span>${size2}</span></div><div class="column-actions"><button type="button" class="fileuploader-action fileuploader-action-remove" title="${captions.remove}"><i class="fileuploader-icon-remove"></i></a></div></div><div class="progress-bar2">${progressBar}<span></span></div></li>',
            item2: '<li class="fileuploader-item"><div class="columns"><div class="column-thumbnail">${image}<span class="fileuploader-action-popup"></span></div><div class="column-title"><a href="${file}" target="_blank"><div title="${name}">${name}</div><span>${size2}</span></a></div><div class="column-actions"><a href="${data.url}" class="fileuploader-action fileuploader-action-download" title="${captions.download}" download><i class="fileuploader-icon-download"></i></a><button type="button" class="fileuploader-action fileuploader-action-remove" title="${captions.remove}"><i class="fileuploader-icon-remove"></i></a></div></div></li>',
            popup: {
                container: "body",
                loop: !0,
                arrows: !0,
                zoomer: !0,
                template: function(e) {
                    return '<div class="fileuploader-popup-preview"><button type="button" class="fileuploader-popup-move" data-action="prev"><i class="fileuploader-icon-arrow-left"></i></button><div class="fileuploader-popup-node node-${format}">${reader.node}</div><div class="fileuploader-popup-content"><div class="fileuploader-popup-footer"><ul class="fileuploader-popup-tools">' + ("image" == e.format && e.reader.node && e.editor ? (e.editor.cropper ? '<li><button type="button" data-action="crop"><i class="fileuploader-icon-crop"></i> ${captions.crop}</button></li>' : "") + (e.editor.rotate ? '<li><button type="button" data-action="rotate-cw"><i class="fileuploader-icon-rotate"></i> ${captions.rotate}</button></li>' : "") : "") + ("image" == e.format ? '<li class="fileuploader-popup-zoomer"><button type="button" data-action="zoom-out">&minus;</button><input type="range" min="0" max="100"><button type="button" data-action="zoom-in">&plus;</button><span></span> </li>' : "") + (e.data.url ? '<li><a href="' + e.data.url + '" data-action="open" target="_blank"><i class="fileuploader-icon-external"></i> ${captions.open}</a></li>' : "") + '<li><button type="button" data-action="remove"><i class="fileuploader-icon-trash"></i> ${captions.remove}</button></li></ul></div><div class="fileuploader-popup-header"><ul class="fileuploader-popup-meta"><li><span>${captions.name}:</span><h5>${name}</h5></li><li><span>${captions.type}:</span><h5>${extension.toUpperCase()}</h5></li><li><span>${captions.size}:</span><h5>${size2}</h5></li>' + (e.reader && e.reader.width ? "<li><span>${captions.dimensions}:</span><h5>${reader.width}x${reader.height}px</h5></li>" : "") + (e.reader && e.reader.duration ? "<li><span>${captions.duration}:</span><h5>${reader.duration2}</h5></li>" : "") + '</ul><div class="fileuploader-popup-info"></div><ul class="fileuploader-popup-buttons"><li><button type="button" class="fileuploader-popup-button" data-action="cancel">${captions.cancel}</a></li>' + (e.editor ? '<li><button type="button" class="fileuploader-popup-button button-success" data-action="save">${captions.confirm}</button></li>' : "") + '</ul></div></div><button type="button" class="fileuploader-popup-move" data-action="next"><i class="fileuploader-icon-arrow-right"></i></button></div>'
                },
                onShow: function(t) {
                    t.popup.html.on("click", '[data-action="remove"]', function(e) {
                        t.popup.close(), t.remove()
                    }).on("click", '[data-action="cancel"]', function(e) {
                        t.popup.close()
                    }).on("click", '[data-action="save"]', function(e) {
                        t.editor && t.editor.save(), t.popup.close && t.popup.close()
                    })
                },
                onHide: null
            },
            itemPrepend: !1,
            removeConfirmation: !0,
            startImageRenderer: !0,
            synchronImages: !0,
            useObjectUrl: !1,
            canvasImage: !0,
            videoThumbnail: !0,
            pdf: !0,
            exif: !0,
            touchDelay: 0,
            _selectors: {
                list: ".fileuploader-items-list",
                item: ".fileuploader-item",
                start: ".fileuploader-action-start",
                retry: ".fileuploader-action-retry",
                remove: ".fileuploader-action-remove",
                sorter: ".fileuploader-action-sort",
                rotate: ".fileuploader-action-rotate",
                popup: ".fileuploader-popup-preview",
                popup_open: ".fileuploader-action-popup"
            },
            beforeShow: null,
            onItemShow: null,
            onItemRemove: function(e) {
                e.children().animate({
                    opacity: 0
                }, 200, function() {
                    setTimeout(function() {
                        e.slideUp(200, function() {
                            e.remove()
                        })
                    }, 100)
                })
            },
            onImageLoaded: null
        },
        editor: !1,
        sorter: !1,
        reader: {
            thumbnailTimeout: 5e3,
            timeout: 12e3,
            maxSize: 20
        },
        files: null,
        upload: null,
        dragDrop: !0,
        addMore: !1,
        skipFileNameCheck: !1,
        clipboardPaste: !0,
        listInput: !0,
        enableApi: !1,
        listeners: null,
        onSupportError: null,
        beforeRender: null,
        afterRender: null,
        beforeSelect: null,
        onFilesCheck: null,
        onFileRead: null,
        onSelect: null,
        afterSelect: null,
        onListInput: null,
        onRemove: null,
        onEmpty: null,
        dialogs: {
            alert: function(e) {
                return alert(e)
            },
            confirm: function(e, t) {
                confirm(e) && t()
            }
        },
        captions: $.fn.fileuploader.languages.en
    }
});