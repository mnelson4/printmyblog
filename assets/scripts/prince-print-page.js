Prince.trackBoxes = true;

Prince.registerPostLayoutFunc(function() {
    // var xs = document.getElementsByClassName("pmb-dynamic-resize");
    for (var i = 0; i < xs.length; ++i) {
        var x = xs[i].getElementsByTagName("img")[0];
        var box = x.getPrinceBoxes()[0];
        var p = PDF.pages[box.pageNum-1];
        var h = box.y - (p.y - p.h) - 10;
        x.style.height = h + "pt";
    }
});
