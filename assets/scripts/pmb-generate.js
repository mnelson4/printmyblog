jQuery(document).ready(function(){
   jQuery('.pmb-generate').click(function(event){
      event.preventDefault();
      var querystring_args = pmb_get_querystring_vars();
      var format_slug = event.currentTarget.getAttribute('data-format');
      var format_options_selection = jQuery('#pmb-generate-options-for-' + format_slug);
      jQuery.post(
          ajaxurl,
          {
             action: 'pmb_project_status',
             ID: querystring_args['ID'],
             format: format_slug,
          },
          (response) => {
            // could fetch more, but for now it all just happens in one request
             if (typeof(response) === 'object' && typeof(response.url) === 'string') {
                var preview_button = format_options_selection.find('.pmb-download-preview');
                preview_button.attr('data-html-url',response.url);
                preview_button.addClass('button-primary');
                format_options_selection.find('.pmb-after-generation').show();
                format_options_selection.find('.pmb-generate').hide();
                format_options_selection.find('.pmb-view-html').attr('href', response.url);
                format_options_selection.find('.pmb-previous-generation-info').hide();
             }
       });
   });
   jQuery('.pmb-download-preview').click(function(event){
      event.preventDefault();
      var design_slug = event.currentTarget.getAttribute('data-format');
      pmb_open_modal(
          '#pmb-download-preview-dialog-' + design_slug,

      );
      var html_url = event.currentTarget.getAttribute('data-html-url');

      // delay slightly so they can see the modal window
      setTimeout(
          function(){
             DocRaptor.createAndDownloadDoc("YOUR_API_KEY_HERE", {
                test: true, // test documents are free, but watermarked
                type: "pdf",
                // document_content: document.querySelector('html').innerHTML, // use this page's HTML
                // document_content: "<h1>Hello world!</h1>",               // or supply HTML directly
                document_url: this.html_url,            // or use a URL
                javascript: true,                                        // Javascript by DocRaptor
                prince_options: {
                   base_url:pmb_generate.site_url,
                   media: this.media,                                       // use screen styles instead of print styles
                   // javascript: true, // use Prince's JS, which is more error tolerant
                }
             });
          },
          1000
      );


   });
   // jQuery('.pmb-download-after-download-actual').click(function(event){
   //    event.preventDefault();
   //    var design_slug = event.currentTarget.getAttribute('data-format');
   //    pmb_open_modal(
   //        '#pmb-download-preview-dialog-' + design_slug,
   //
   //    );
   // });
});

/**
 * from https://stackoverflow.com/questions/4656843/get-querystring-from-url-using-jquery
 * @returns {[]}
 */
function pmb_get_querystring_vars()
{
   var vars = [], hash;
   var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
   for(var i = 0; i < hashes.length; i++)
   {
      hash = hashes[i].split('=');
      vars.push(hash[0]);
      vars[hash[0]] = hash[1];
   }
   return vars;
}