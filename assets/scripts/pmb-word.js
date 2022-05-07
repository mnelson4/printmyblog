
function pmb_export_as_doc(){
    var header = "<html xmlns:o='urn:schemas-microsoft-com:office:office' "+
        "xmlns:w='urn:schemas-microsoft-com:office:word' "+
        "xmlns='http://www.w3.org/TR/REC-html40'>"+
        "<head><meta charset='utf-8'><title>Export HTML to Word Document with JavaScript</title></head><body>";
    var footer = "</body></html>";
    var datatype = 'data:application/vnd.ms-word;charset=utf-8';
    var sourceHTML = header+document.getElementsByClassName("pmb-project-content")[0].innerHTML+footer;
    var blob = new Blob([sourceHTML], {type: "data:application/vnd.ms-word;charset=utf-8"});
    var download_button = jQuery('#download_link');
    saveAs(blob, download_button.attr('download').valueOf());
    //saveTextAs(sourceHTML, download_button.attr('download').valueOf());
}
jQuery(document).on('pmb_wrap_up', function() {
    var download_button = jQuery('#download_link');
    download_button.removeClass('pmb-disabled');
    jQuery('.pmb-loading').remove();

    download_button.click(function() {
        pmb_export_as_doc();
    });
});