// This is some Prince HTML-to-PDF converter Javascript. It's not executed by the browser, but during the
// Prince HTML-to-PDF conversion process. See https://www.princexml.com/doc/javascript/
// turn on box tracking API
Prince.trackBoxes = true;

// once the first pass of rendering is finished, let's make the "pmb-dynamic-resize" images take up the rest of the
// page they're on. Prince will then need to re-render.
Prince.registerPostLayoutFunc(function() {

    var image_wrapper = document.getElementsByClassName("pmb-dynamic-resize");
    for (var i = 0; i < image_wrapper.length; ++i) {

        // get the image's size, and how far it is from the bottom of the page
        // then stretch it down to there.
        var image_element = image_wrapper[i].getElementsByTagName("img")[0];
        var image_box = image_element.getPrinceBoxes()[0];
        var page_box = PDF.pages[image_box.pageNum-1];

        // don't forget to take the footnote height into account
        var footnotes_height = 0;
        for (var index in page_box['children']){
            var box_on_page = page_box['children'][index];
            if(box_on_page['type'] === 'FOOTNOTES'){
                footnotes_height = box_on_page['h'];
            }
        }
        var new_image_height = image_box.y - (page_box.y - page_box.h) - 10 - footnotes_height;
        image_element.style.height = new_image_height + "pt";
    }
});

/**
 * A debugging function, especially useful for figuring out what's on these "box" objects
 * @param obj
 * @param label
 */
function pmb_print_props(obj, label){
    Log.info(label)
    for(var prop in obj){
        var val = obj[prop];
        Log.info(prop + ':' + val);
    }
}

