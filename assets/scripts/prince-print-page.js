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
    var dynamic_resize_blocks = document.getElementsByClassName("pmb-dynamic-resize");
    var a_dynamic_resize_block = dynamic_resize_blocks[0];
    if(typeof a_dynamic_resize_block === 'undefined'){
        return;
    }
    // when images are floating, the block had a div (with no height) because its contents are floating
    // in that case we want to resize the figure inside the block. So check if there's a figure inside it
    var figure_is_floating = true;
    var figure_to_resize = a_dynamic_resize_block.getElementsByTagName("figure")[0];

    if( typeof figure_to_resize === 'undefined'){
        // There's no figure inside it. The figure is the block.
        figure_to_resize = a_dynamic_resize_block;
        figure_is_floating = false;
    }
    var figure_image = figure_to_resize.getElementsByTagName('img')[0];
    var figure_image_height = figure_image.getPrinceBoxes()[0].h;

    var figure_caption = figure_to_resize.getElementsByTagName['figcaption'][0];
    var figure_caption_height = figure_caption.getPrinceBoxes()[0].h;

    var figure_box = figure_to_resize.getPrinceBoxes()[0];
    var page_box = PDF.pages[figure_box.pageNum-1];

    // don't forget to take the footnote height into account
    var footnotes_height = 0;
    for (var index in page_box['children']){
        var box_on_page = page_box['children'][index];
        if(box_on_page['type'] === 'FOOTNOTES'){
            footnotes_height = box_on_page['h'];
        }
    }
    var new_figure_height = figure_box.y - (page_box.y - page_box.h) - 10 - footnotes_height;
    pmb_print_props(figure_box,'>>>>>element box');
    var new_image_height = new_figure_height - figure_box.h + figure_image_height;
    var resize_ratio = new_image_height / figure_image_height;
    figure_to_resize.style.height = new_figure_height + "pt";
    Log.info('figure is floating: ' + figure_is_floating + '. Resize ratio is ' + resize_ratio + '. Old figure h:' + figure_box.h + ', New figure h:' + new_figure_height + '. Old image h:' + figure_image_height + ', New image h:' + new_image_height);
    if(figure_is_floating) {
        figure_to_resize.style.width = (figure_box.w * resize_ratio) + 'pt';
    }
    a_dynamic_resize_block.className = a_dynamic_resize_block.className.replace('pmb-dynamic-resize', 'pmb-dynamic-resized');

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

