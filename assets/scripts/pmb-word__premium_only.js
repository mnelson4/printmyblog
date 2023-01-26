/**
 * Called when download button clicked and all the design's processing is complete.
 */
function pmb_prepare_and_export_doc(){
    pmb_replace_links_for_word();
    pmb_inline_css();
    // word doesn't know how to handle figcaptions (it shows them inline) so replace with paragraphs
    jQuery('figcaption').replaceWith(function () {
        return "<p>" + jQuery(this).text() + "</p>";
    });
    var dataurl_converter = new PmbImgToDataUrls(
        function () {
            jQuery('.pmb-loading').remove();
            pmb_limit_img_widths();
            download_button.removeClass('pmb-disabled');
            pmb_export_as_doc();
        }
    );
    dataurl_converter.convert();
}

/**
 * Should really just be called by pmb_prepare_and_export_doc.
 */
function pmb_export_as_doc(){
    var print_page_head_jq = jQuery('head');
    print_page_head_jq.find('script').remove();
    var word_doc_head = "<html xmlns:o='urn:schemas-microsoft-com:office:office' "+
        "xmlns:w='urn:schemas-microsoft-com:office:word' "+
        "xmlns='http://www.w3.org/TR/REC-html40'>"+
        "<head><meta charset='utf-8'>" +
        // https://www.codeproject.com/Articles/7341/Dynamically-generate-a-MS-Word-document-using-HTML
        "<!--[if gte mso 9]>" +
        "<xml>" +
        "<w:WordDocument>" +
        "<w:View>Print</w:View>" +
        "<w:Zoom>10</w:Zoom>" +
        "<w:DoNotOptimizeForBrowser/>" +
        "</w:WordDocument>" +
        "</xml>" +
        "<![endif]-->" +
        "<style> <!-- " +
"@page" +
"{" +
    "size: 21cm 29.7cm;  /* A4 */" +
    "margin: 2cm 2cm 2cm 2cm; /* Margins: 2 cm on each side */"+
    "mso-page-orientation: portrait;" +
"}"+
"-->" +
        "</style>" +
        print_page_head_jq.html() +
        "</head><body>";
    var footer = "</body></html>";

    var body_jq= jQuery('.pmb-project-content');
    body_jq.find('noscript').remove();
    var sourceHTML = word_doc_head+body_jq.html()+footer;

    var blob = new Blob([sourceHTML], {type: "data:application/vnd.ms-word;charset=utf-8"});
    var download_button = jQuery('#download_link');
    saveAs(blob, download_button.attr('download').valueOf());
}

/**
 * Word ignores CSS (from my testing) but does respect the height and width attributes. Limit them to the page's width
 */
function pmb_limit_img_widths(){
    jQuery('img').each(function(index, element){
        var old_width = element.getAttribute('width');
        var new_width = Math.min(old_width, 642); //about page width
        var old_height = element.getAttribute('height');
        var ratio = new_width / old_width;
        var new_height = old_height * ratio;
        // double-check its not bigger than the page
        if( new_height > 870 ) { // 9about page height
            new_height = 870;
            ratio = new_height / old_height;
            new_width = old_width * ratio;
        }
        if(element.hasAttribute('width')){
            element.setAttribute('width', new_width);
        }
        if(element.hasAttribute('height')){
            element.setAttribute('height', new_height);
        }
    });
}

/**
 * v1: just converts internal hyperlinks to anchor links
 */
function pmb_replace_links_for_word(external_link_policy, internal_link_policy)
{
    // epub-generator.js's pmb_replace_internal_links_with_epub_file_links has similar logic
    _pmb_for_each_hyperlink(
        // internal hyperlinks
        function(a, id_url){
            switch(internal_link_policy){
                case 'leave':
                    // only add the footnote if the link isn't just the URL spelled out.
                    if(a.attr('href') !== a.html().trim()) {
                        a.attr('href',id_url);
                    }
                    break;
                case 'remove':
                    a.contents().unwrap();
                    break;
                case 'leave_external':
                // otherwise, leave alone
            }
        },
        // external hyperlinks
        function(a){
            switch(external_link_policy){
                case 'remove':
                    a.contents().unwrap();
                    break;
                case 'leave':
                default:
                // otherwise, leave alone
            }
        }
    );
}

/**
 * converts all images to dataurls for easy inclusion in word docs
 * @param finished_callback called when done all conversions
 * @constructor
 */
function PmbImgToDataUrls(finished_callback) {
    this.pending = [];
    this.finished_callback = finished_callback;
    this.canvas = null;

    /**
     * starts converting images to dataurls
     */
    this.convert = function(){
        // trock from https://stackoverflow.com/questions/15760764/how-to-get-base64-encoded-data-from-html-imagea
        this.canvas = document.createElement( 'canvas' );
        var that = this;
        this.enqueue();
        that.checkFinished();
    }

    /**
     * First enqueues all the images we need to convert.
     */
    this.enqueue = function(){
        var that = this;
        jQuery('img').each(function(index, element){
            that.pending.push(element);
        });
    }

    /**
     * Grab an enqueued image and convert it, then see if we're done.
     */
    this.continueConvertingImages = function(){
        var that = this;
        var element = this.pending.pop();
        var original_height = element.offsetHeight;
        var original_width = element.offsetWidth;
        pmb_set_image_dimension_attributes(
            element,
            function(){
                that.canvas.setAttribute('height', element.naturalHeight);
                that.canvas.setAttribute('width', element.naturalWidth);
                var context = that.canvas.getContext && that.canvas.getContext( '2d' );
                try{
                    context.drawImage(element, 0, 0);
                    element.src = that.canvas.toDataURL();
                    element.setAttribute('height', original_height);
                    element.setAttribute('width', original_width);
                }catch(e){
                    console.log('PMB could not convert image ' + element.src + ' to dataUrl');
                }
                that.checkFinished();
            },
            function(){
                that.checkFinished();
            }
        );
    }

    /**
     * If we've converted all the enqueued images, stop and do the wrap-up callback; otherwise keep converting images.
     * @private
     */
    this.checkFinished = function(){
        if(this.pending.length <= 0){
            this.finished_callback();
        } else {
            this.continueConvertingImages();
        }
    }
}
/**
 * Callbacks that listen for document.pmb_doc_conversion_requested should set them to TRUE immediately, otherwise
 * we'll assume no callback was set and so we'll just proceed with converting the file.
 * @type {boolean}
 */
var pmb_doc_conversion_request_handled = false;
jQuery(document).on('ready', function() {
    var download_button = jQuery('#download_link');
    download_button.removeClass('pmb-disabled');
    jQuery('.pmb-loading').hide();
    download_button.click(function () {
        download_button.addClass('pmb-disabled');
        jQuery('.pmb-loading').show();
        jQuery(document).trigger('pmb_doc_conversion_requested');
        // trigger document.pmb_wrap_up for legacy code.
        jQuery(document).trigger('pmb_wrap_up');

        jQuery(document).on('pmb_doc_conversion_ready', function () {
            pmb_prepare_and_export_doc();
        });
        // as a backup, in case the design didn't listen for document.pmb_doc_conversion_requested just go ahead and execute it.
        setTimeout(
            function () {
                if (!pmb_doc_conversion_request_handled) {
                    pmb_export_as_doc();
                }
            },
            3000
        )
    });
});