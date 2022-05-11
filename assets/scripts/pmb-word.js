
function pmb_export_as_doc(){
    var print_page_head = document.getElementsByTagName("head")[0].innerHTML;
    var word_doc_head = "<html xmlns:o='urn:schemas-microsoft-com:office:office' "+
        "xmlns:w='urn:schemas-microsoft-com:office:word' "+
        "xmlns='http://www.w3.org/TR/REC-html40'>"+
        "<head><meta charset='utf-8'>" +
        // https://www.codeproject.com/Articles/7341/Dynamically-generate-a-MS-Word-document-using-HTML
        "<!--[if gte mso 9]>" +
        "<xml>" +
        "<w:WordDocument>" +
        "<w:View>Print</w:View>" +
        "<w:Zoom>100</w:Zoom>" +
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
        print_page_head +
        "</head><body>";
    var footer = "</body></html>";

    var sourceHTML = word_doc_head+document.getElementsByClassName("pmb-project-content")[0].innerHTML+footer;

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
        var new_width = Math.min(old_width, 642);
        var ratio = new_width / old_width;
        if(element.hasAttribute('width')){
            element.setAttribute('width', new_width);
        }
        if(element.hasAttribute('height')){
            element.setAttribute('height', element.getAttribute('height') * ratio);
        }
    });
}

/**
 * v1: just converts internal hyperlinks to anchor links
 */
function pmb_replace_links_for_word()
{
    // epub-generator.js's pmb_replace_internal_links_with_epub_file_links has similar logic
    _pmb_for_each_hyperlink(
        // internal hyperlinks
        function(a, id_url, id_selector){
            // v1: convert internal hyperlinks to anchor links
            // switch(internal_link_policy){
            //     case 'footnote':
                    // only add the footnote if the link isn't just the URL spelled out.
                    if(a.attr('href') !== a.html().trim()) {
                        a.attr('href',id_url);
                    }
            //         break;
            //     case 'leave':
            //         a.attr('href',id_url);
            //         break;
            //     case 'remove':
            //         a.contents().unwrap();
            //         break;
            //     // otherwise, leave alone
            // }
        },
        // external hyperlinks
        function(a){
            // v1 just always leave external hyperlinks
            // switch(external_link_policy){
            //     case 'footnote':
            //         // only add the footnote if the link isn't just the URL spelled out.
            //         var link_text = a.html().trim();
            //         var href = a.attr('href');
            //         var matches = [href, href.replace('https://',''), href.replace('http://',''), href.replace('//',''), href.replace('mailto:','')];
            //         if(matches.indexOf(link_text) === -1){
            //             a.after('<span class="pmb-footnote">' + pre_external_footnote  + a.attr('href') + post_external_footnote + '</span>');
            //         }
            //         break;
            //     case 'remove':
            //         a.contents().unwrap();
            //         break;
            //     // otherwise, leave alone
            // }
        }
    );
}

/**
 * converts all images to dataurls for easy inclusion in word docs
 * @param finished_callback called when done all conversions
 * @constructor
 */
function PmbImgToDataUrls(finished_callback) {
    this.pending = 0;
    this.finished_callback = finished_callback;

    /**
     * starts converting images to dataurls
     */
    this.convert = function(){
        // trock from https://stackoverflow.com/questions/15760764/how-to-get-base64-encoded-data-from-html-imagea
        var canvas = document.createElement( 'canvas' );
        var that = this;
        jQuery('img').each(function(index, element){
            var original_height = element.offsetHeight;
            var original_width = element.offsetWidth;
            that.pending++;
            pmb_set_image_dimension_attributes(element,
                function(){
                    canvas.setAttribute('height', element.attributes['height'].value);
                    canvas.setAttribute('width', element.attributes['width'].value);
                    var context = canvas.getContext && canvas.getContext( '2d' );
                    context.drawImage(element, 0, 0);

                    element.src = canvas.toDataURL();
                    element.setAttribute('height', original_height);
                    element.setAttribute('width', original_width);
                    that.pending--;
                    that.checkFinished();
                });
        });
        that.checkFinished();
    }

    /**
     * @private
     */
    this.checkFinished = function(){
        if(this.pending <= 0){
            this.finished_callback();
        }
    }
}

jQuery(document).on('pmb_wrap_up', function() {
    var download_button = jQuery('#download_link');

    pmb_replace_links_for_word();

    jQuery(document).on("pmb_external_resouces_loaded", function() {
        var dataurl_converter = new PmbImgToDataUrls(
            function(){
                jQuery('.pmb-loading').remove();
                pmb_limit_img_widths();
                download_button.removeClass('pmb-disabled');
                download_button.click(function() {
                    pmb_export_as_doc();
                });
            }
        );
        dataurl_converter.convert();
    });
    var erc = new PmbExternalResourceCacher();
    erc.replaceExternalImages();
});