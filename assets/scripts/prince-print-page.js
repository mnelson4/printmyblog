Prince.trackBoxes = true;

Prince.registerPostLayoutFunc(function() {
    var xs = document.getElementsByClassName("pmb-dynamic-resize");
    for (var i = 0; i < xs.length; ++i) {
        var x = xs[i].getElementsByTagName("img")[0];
        var box = x.getPrinceBoxes()[0];
        var p = PDF.pages[box.pageNum-1];

        var old_height = box.h;
        var new_height = box.y - (p.y - p.h) - p.marginBottom - 10;
        Log.info('p.y' + p.y)
        Log.info('p.h' + p.h)
        Log.info('p.marginBottom' + p.marginBottom);
        Log.info('box.marginBottom' + box.marginBottom);
        var ratio = old_height / new_height;

        var new_width = p.w * ratio;

        x.style.height = new_height + "pt";
        x.style.width = new_width + "pt';"
    }
});
