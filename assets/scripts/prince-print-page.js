// This is some Prince HTML-to-PDF converter Javascript. It's not executed by the browser, but during the
// Prince HTML-to-PDF conversion process. See https://www.princexml.com/doc/javascript/
// turn on box tracking API
Prince.trackBoxes = true;

// once the first pass of rendering is finished, let's make the "pmb-dynamic-resize" images take up the rest of the
// page they're on. Prince will then need to re-render.
Prince.registerPostLayoutFunc(function() {

    pmb_continue_image_resizing();
});

function pmb_continue_image_resizing(){
    var elements_to_resize = document.getElementsByClassName("pmb-dynamic-resize");
    var element_to_resize = elements_to_resize[0];
    if(typeof element_to_resize === 'undefined'){
        return;
    }
    element_to_resize.className = element_to_resize.replace('pmb-dynamic-resize', '') + ' pmb-dynamic-resize-done';
    var element_box = element_to_resize.getPrinceBoxes()[0];
    var page_box = PDF.pages[element_box.pageNum-1];

    // don't forget to take the footnote height into account
    var footnotes_height = 0;
    for (var index in page_box['children']){
        var box_on_page = page_box['children'][index];
        if(box_on_page['type'] === 'FOOTNOTES'){
            footnotes_height = box_on_page['h'];
        }
    }
    Log.info('=========' + i + '=================');
    pmb_print_props(element_box, '>>> element ' + i + ' box');
    pmb_print_props(page_box, '>>> page ' + (element_box.pageNum) + ' box');
    var new_element_height = element_box.y - (page_box.y - page_box.h) - 10 - footnotes_height;
    element_to_resize.style.height = new_element_height + "pt";
    Log.info('new element height:' + new_element_height);
    Prince.registerPostLayoutFunc(pmb_continue_image_resizing);
}

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

