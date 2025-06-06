(function (b) {
    "function" === typeof define && define.amd ? define(["jquery", "datatables.net", "datatables.net-buttons"], function (d) {
        return b(d, window, document)
    }) : "object" === typeof exports ? module.exports = function (d, h) {
        d || (d = window);
        h && h.fn.dataTable || (h = require("datatables.net")(d, h).$);
        h.fn.dataTable.Buttons || require("datatables.net-buttons")(d, h);
        return b(h, d, d.document)
    } : b(jQuery, window, document)
})(function (b, d, h, y) {
    var u = b.fn.dataTable,
        n = h.createElement("a"),
        v = function (a) {
            n.href = a;
            a = n.host; -
            1 === a.indexOf("/") && 0 !== n.pathname.indexOf("/") && (a += "/");
            return n.protocol + "//" + a + n.pathname + n.search
        };
    u.ext.buttons.print = {
        className: "buttons-print",
        text: function (a) {
            return a.i18n("buttons.print", "Print")
        },
        action: function (a, e, p, k) {
            a = e.buttons.exportData(b.extend({
                decodeEntities: !1
            }, k.exportOptions));
            p = e.buttons.exportInfo(k);
            var w = e.columns(k.exportOptions.columns).flatten().map(function (f) {
                    return e.settings()[0].aoColumns[e.column(f).index()].sClass
                }).toArray(),
                r = function (f, g) {
                    for (var x = "<tr>", l = 0, z = f.length; l < z; l++) x +=
                        "<" + g + " " + (w[l] ? 'class="' + w[l] + '"' : "") + ">" + (null === f[l] || f[l] === y ? "" : f[l]) + "</" + g + ">";
                    return x + "</tr>"
                },
                m = '<table class="' + e.table().node().className + '">';
            k.header && (m += "<thead>" + r(a.header, "th") + "</thead>");
            m += "<tbody>";
            for (var t = 0, A = a.body.length; t < A; t++) m += r(a.body[t], "td");
            m += "</tbody>";
            k.footer && a.footer && (m += "<tfoot>" + r(a.footer, "th") + "</tfoot>");
            m += "</table>";
            var c = d.open("", "");
            if (c) {
                c.document.close();
                var q = "<title>" + p.title + "</title>";
                b("style, link").each(function () {
                    var f = q,
                        g = b(this).clone()[0];
                    "link" === g.nodeName.toLowerCase() && (g.href = v(g.href));
                    q = f + g.outerHTML
                });
                try {
                    c.document.head.innerHTML = q
                } catch (f) {
                    b(c.document.head).html(q)
                }
                c.document.body.innerHTML = "<h5>" + p.title + "</h5><div>" + (p.messageTop || "") + "</div>" + m + "<div>" + (p.messageBottom || "") + "</div>";
                b(c.document.body).addClass("dt-print-view");
                b("img", c.document.body).each(function (f, g) {
                    g.setAttribute("src", v(g.getAttribute("src")))
                });
                // Personalização da impressão

                b(c.document.body).append("<div>" + document.getElementById('printTitle').textContent + "</div>");
               
                b(c.document.body).append('<hr style="margin-top: 90px;"><div style="text-align: center;">Assinatura</div>');
                 

                k.customize && k.customize(c, k, e);
                a = function () {
                    k.autoPrint && (c.print(), c.close())
                };
                navigator.userAgent.match(/Trident\/\d.\d/) ?
                    a() : c.setTimeout(a, 1E3)
            } else e.buttons.info(e.i18n("buttons.printErrorTitle", "Unable to open print view"), e.i18n("buttons.printErrorMsg", "Please allow popups in your browser for this site to be able to view the print view."), 5E3)
        },
        title: "*",
        messageTop: "*",
        messageBottom: "*",
        exportOptions: {},
        header: !0,
        footer: !1,
        autoPrint: !0,
        customize: null
    };
    return u.Buttons
});
