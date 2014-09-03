var demo = function(){
    var lines = text.split("\n");
    var reProcessedPortion = new RegExp("(^\\s*?<|^[^<]?\{|^[^\{]*?)(.+)(>\\s*?$|[^>]*?$|\}\\s*?$|[^\}]*?$)");
    var reOpenBrackets = new RegExp("(<|\{)", "g");
    var reCloseBrackets = new RegExp("(>|//\})([^\r\n])", "g");
    for (var i = 0; i < lines.length; i++) {
        var mToProcess = lines[i].match(reProcessedPortion);
        if (mToProcess != null && mToProcess.length > 3) { // The line starts with whitespaces and ends with whitespaces
            lines[i] = mToProcess[1]
                + mToProcess[2].replace(reOpenBrackets,"\n$&").replace(reCloseBrackets, "$1\n$2")
                + mToProcess[3];
            continue;
        }
    }
}
tinymce.PluginManager.add("thin", function (e) {
    function o() {
        var str = e.getContent({
            source_view: !0
        });
        console.log(e);
        str = str.replace(new RegExp('(<br class="br" />)+','g'), '\n');
        e.windowManager.open({
            title: "Source code",
            body: {
                type: "textbox",
                name: "code",
                multiline: !0,
                minWidth: e.getParam("code_dialog_width", 600),
                minHeight: e.getParam("code_dialog_height", Math.min(tinymce.DOM.getViewPort().h - 200, 500)),
                value: str,
                spellcheck: !1,
                style: "direction: ltr; text-align: left"
            },
            onSubmit: function (o) {
                e.focus(), e.undoManager.transact(function () {
                    var str = o.data.code;
                    e.setContent(str);
                }), e.selection.setCursorLocation(), e.nodeChanged()
            }
        })
    }
    e.addCommand("mceCodeEditor", o), e.addButton("thin", {
        icon: "code",
        tooltip: "Source code",
        onclick: o
    }), e.addMenuItem("thin", {
        icon: "code",
        text: "Source code",
        context: "tools",
        onclick: o
    })
});
