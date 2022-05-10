
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
        canvas.setAttribute('height', element.attributes['height'].value);
        canvas.setAttribute('width', element.attributes['width'].value);
        var context = canvas.getContext && canvas.getContext( '2d' );
        context.drawImage(element, 0, 0);

        element.attributes['src'].value = canvas.toDataURL();
        //element.removeAttribute('srcset');
    });

}
jQuery(document).on('pmb_wrap_up', function() {
    var download_button = jQuery('#download_link');
    download_button.removeClass('pmb-disabled');
    pmb_convert_images_to_data_urls();
    jQuery('.pmb-loading').remove();

    download_button.click(function() {
        pmb_export_as_doc();
    });
});