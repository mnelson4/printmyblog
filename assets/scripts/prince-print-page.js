// This is some Prince HTML-to-PDF converter Javascript. It's not executed by the browser, but during the
// Prince HTML-to-PDF conversion process. See https://www.princexml.com/doc/javascript/
// turn on box tracking API
Prince.trackBoxes = true;

// once the first pass of rendering is finished, let's make the "pmb-dynamic-resize" images take up the rest of the
// page they're on. Prince will then need to re-render.
Prince.registerPostLayoutFunc(function() {
    pmb_continue_image_resizing();
});

/**
 * Resizes images in blocks with CSS class "pmb-dynamic-resize".
 * Gutenberg image blocks with no alignment: the top-level block has the class and is the figure.
 * But if they're floating, the top-level block is a div which contains the figure (which floats).
 * The image's initial height effectively becomes the minimum height. The maximum height
 */
function pmb_continue_image_resizing(){
    var resized_something = false;

    // To make this more efficient, grab the first image from each section followed by a pagebreak
    if(pmb.page_per_post){
        var dynamic_resize_blocks = document.getElementsByClassName('pmb-section');
        for(var i=0; i<dynamic_resize_blocks.length; i++){
            var resized_element = pmb_resize_an_image_inside(dynamic_resize_blocks[i]);
            if(resized_element){
                resized_something = true;
            }
        }
    } else {
        if(pmb_resize_an_image_inside(document)){
            resized_something = true;
        }
    }


    if(resized_something){
        Prince.registerPostLayoutFunc(pmb_continue_image_resizing);
    }
}

/**
 * Grabs a "pmb-dynamic-resize" element inside here and resizes it and returns it.
 * (If none are found, returns null)
 * @param element
 * @return boolean
 */
function pmb_resize_an_image_inside(element){
    var dynamic_resize_blocks = element.getElementsByClassName("pmb-dynamic-resize");

    // just grab one block at a time because how the first image is resized will affect the subsequentn ones
    // and subsequent ones' telemetry is only updated after re-rendering. So do one, then get Prince to re-render, then
    // do another... etc.
    var a_dynamic_resize_block = dynamic_resize_blocks[0];
    if(typeof a_dynamic_resize_block === 'undefined'){
        return null;
    }
    // when images are floating, the block had a div (with no height) because its contents are floating
    // in that case we want to resize the figure inside the block. So check if there's a figure inside it
    var figure_is_floating = true;
    var figure_to_resize = a_dynamic_resize_block.getElementsByTagName("figure")[0];

    if( typeof figure_to_resize === 'undefined'){
        // There's no figure inside it. The figure is the top-level element in the block.
        figure_to_resize = a_dynamic_resize_block;
        figure_is_floating = false;
    }
    // For floating images we need to also set the block's width (I can't figure out how to get CSS to set the width automatically)
    // so for that we need to figure out how much the image inside the figure got resized (non-trivial if there's a caption).
    var figure_image = figure_to_resize.getElementsByTagName('img')[0];

    // If we can't find an image to resize, there's nothing to resize (which is weird but somehow happens?)
    if(typeof(figure_image) !== 'undefined') {
        var figure_image_box = figure_image.getPrinceBoxes()[0];
        var figure_image_height = figure_image_box.h;

        var figure_box = figure_to_resize.getPrinceBoxes()[0];
        var page_box = PDF.pages[figure_box.pageNum - 1];

        // don't forget to take the footnote height into account
        var footnotes_height = 0;
        for (var index in page_box['children']) {
            var box_on_page = page_box['children'][index];
            if (box_on_page['type'] === 'FOOTNOTES') {
                footnotes_height = box_on_page['h'];
            }
        }
        // page_box.y is the distance from the top of the page to the bottom margin;
        // page_box.h is the distance from the bottom margin to the top margin
        // figure_box.y is the distance from the top of the page to the bottom-left corner of the figure
        // see https://www.princexml.com/forum/post/23543/attachment/img-fill.html
        var new_figure_height = figure_box.y - (page_box.y - page_box.h) - 10 - footnotes_height;

        // calculate the maximum potential image height based on the image's dimensions and page width
        var max_height_because_of_max_width = page_box.w * figure_box.h / figure_image_box.w;

        // also gather the maximum heights from the original image

        if('pmb_resolution_y' in figure_image.attributes){
            var max_height_from_resolution_y_of_image = figure_image.attributes['pmb_resolution_y'].value;
        } else {
            var max_height_from_resolution_y_of_image = 100000;
        }

        // original_height     max_height
        // --------------   = -------------     =>   max_height = max_width  * original_height / original_width
        // original_width      max_width
        if('pmb_resolution_x' in figure_image.attributes && 'pmb_resolution_y' in figure_image.attributes){
            var max_height_from_resolution_x_of_image = page_box.w * figure_image.attributes['pmb_resolution_y'] / figure_image.attributes['pmb_resolution_x'].value;
        } else {
            var max_height_from_resolution_x_of_image = 100000;
        }

        // put a limit on how big the image can be
        // use the design's maximum image size, which was passed from PHP

        new_figure_height = Math.min(
            pmb.max_image_size,
            new_figure_height,
            max_height_because_of_max_width,
            max_height_from_resolution_y_of_image,
            max_height_from_resolution_x_of_image
        );

        // Used some grade 12 math to figure out this equation.
        var new_image_height = new_figure_height - figure_box.h + figure_image_height;
        var resize_ratio = new_image_height / figure_image_height;

        // Resize the block
        figure_to_resize.style.height = new_figure_height + "pt";
        if (figure_is_floating) {
            figure_to_resize.style.width = (figure_box.w * resize_ratio) + 'pt';
        }
    }

    // Change the class so we know we don't try to resize this block again
    a_dynamic_resize_block.className = a_dynamic_resize_block.className.replace(/pmb-dynamic-resize/g, 'pmb-dynamic-resized');
    return a_dynamic_resize_block;
}

/**
 * A debugging function, especially useful for figuring out what's on these "box" objects
 * @param obj
 * @param label
 */
function pmb_print_props(obj, label){
    Log.info(label);
    for(var prop in obj){
        var val = obj[prop];
        Log.info(prop + ':' + val);
    }
}

