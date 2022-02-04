Prince.trackBoxes = true;

Prince.registerPostLayoutFunc(function() {
    var xs = document.getElementsByClassName("pmb-dynamic-resize");
    for (var i = 0; i < xs.length; ++i) {
        var x = xs[i].getElementsByTagName("img")[0];
        var box = x.getPrinceBoxes()[0];
        var p = PDF.pages[box.pageNum-1];

        var old_height = p.h;
        var new_height = box.y - (p.y - p.h) - 10;
        var ratio = old_height / new_height;

        var old_width = p.w;
        var new_width = p.w * ratio;

        x.style.height = new_height + "pt";
        x.style.width = new_width + "pt';"
    }
});