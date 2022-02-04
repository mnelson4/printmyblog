Prince.trackBoxes = true;

Prince.registerPostLayoutFunc(function() {
    var xs = document.getElementsByClassName("pmb-dynamic-resize");
    for (var i = 0; i < xs.length; ++i) {
        var x = xs[i].getElementsByTagName("img")[0];
        var box = x.getPrinceBoxes()[0];
        var p = PDF.pages[box.pageNum-1];


        var footnotes_height = 0;
        for (var index in p['children']){
            var child = p['children'][index];
            if(child['type'] === 'FOOTNOTES'){
                footnotes_height = child['h'];
            }
        }
        Log.info('footnotes height' + footnotes_height);
        var new_height = box.y - (p.y - p.h) - 10 - footnotes_height;

        x.style.height = new_height + "pt";
        // x.style.width = new_width + "pt';"
    }
});

function pmb_print_props(obj, label){
    Log.info(label)
    for(var prop in obj){
        var val = obj[prop];
        Log.info(prop + ':' + val);
    }
}

