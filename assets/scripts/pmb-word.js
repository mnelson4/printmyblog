
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
        "<w:Zoom>10</w:Zoom>" +
        "<w:DoNotOptimizeForBrowser/>" +
        "</w:WordDocument>" +
        "</xml>" +
        "<![endif]-->" +
        print_page_head +
        "</head><body>";
    var footer = "</body></html>";

    var sourceHTML = word_doc_head+document.getElementsByClassName("pmb-project-content")[0].innerHTML+footer;

    var blob = new Blob([sourceHTML], {type: "data:application/vnd.ms-word;charset=utf-8"});
    var download_button = jQuery('#download_link');
    saveAs(blob, download_button.attr('download').valueOf());
    //saveTextAs(sourceHTML, download_button.attr('download').valueOf());
}

function pmb_convert_images_to_data_urls(){
    // trock from https://stackoverflow.com/questions/15760764/how-to-get-base64-encoded-data-from-html-imagea
    var canvas = document.createElement( 'canvas' );



    jQuery('img').each(function(index, element){
        var original_height = element.offsetHeight;
        var original_width = element.offsetWidth;
        pmb_set_image_dimension_attributes(element,
            function(){
                canvas.setAttribute('height', element.attributes['height'].value);
                canvas.setAttribute('width', element.attributes['width'].value);
                var context = canvas.getContext && canvas.getContext( '2d' );
                context.drawImage(element, 0, 0);

                element.attributes['src'].value = canvas.toDataURL();
                element.setAttribute('height', original_height);
                element.setAttribute('width', original_width);
            });
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

jQuery(document).on('pmb_wrap_up', function() {
    var download_button = jQuery('#download_link');
    download_button.removeClass('pmb-disabled');
    pmb_replace_links_for_word();



    jQuery(document).on("pmb_external_resouces_loaded", function() {
        pmb_convert_images_to_data_urls();
        jQuery('.pmb-loading').remove();
        download_button.click(function() {
            pmb_export_as_doc();
        });
    });
    var erc = new PmbExternalResourceCacher();
    erc.replaceExternalImages();



});