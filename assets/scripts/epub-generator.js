const streamSaver = window.streamSaver

/**
 * @var pmb_pro object
 * @param blob
 * @returns {Promise<unknown>}
 */


function blobToBase64(blob) {
    return new Promise((resolve, _) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.readAsDataURL(blob);
    });
}

/**
 * Creates the ePub file from the current page's HTML and enables the download link when it's done.
 */
function pmb_create_epub(){
    var epub_options = {
        title: pmb_pro.title,
        author:pmb_pro.authors,
        verbose: true,
        ignoreFailedDownloads:true,
        prependChapterTitles:false,
        numberChaptersInTOC:false,
        cover:pmb_pro.cover,
        css:pmb_pro.css,
        version: parseInt(pmb_pro.version)
    }
    var sections = [];
    var found_toc = false;
    var toc_title_found = null;
    jQuery('.pmb-section').each(function(index,element){
        var chapter_data = {
            content:element.outerHTML,
            beforeToc: ! found_toc,
            filename: pmb_hyperlink_to_filename(element.id)
        };

        var jqelement = jQuery(element);
        var section_title = jqelement.find('.pmb-title');
        var spaces_to_add = parseInt(jqelement.data('depth'), 10);
        var spaces = '';
        for(var i=0; i < spaces_to_add; i++){
            spaces += '- ';
        }

        if(section_title.length){
            chapter_data.title = spaces + section_title.text();
            chapter_data.excludeFromToc = false;
        } else {
            chapter_data.excludeFromToc = true;
        }
        if(jqelement.find('div.pmb-toc').length > 0){
            found_toc = true;
            toc_title_found = section_title.text();
            // don't add the TOC page to the book. epub-gen-memory.js adds it automatically
            return;
        }
        sections.push(chapter_data);
    });
    epub_options.tocInTOC = found_toc;
    if(found_toc){
        epub_options.tocTitle = toc_title_found;
    }

    const epub = epubGen.default;
    (async () => {
        var epub_blob = await epub(epub_options, sections);
        var download_button = jQuery('#download_link');
        download_button.removeClass('pmb-disabled');
        jQuery('.pmb-loading').hide();
        if(document.location.protocol == 'https:'){
            var readableStream = epub_blob.stream()

            streamSaver.mitm = 'https://printmy.blog/wp-content/streamsaver/mitm.html';
            var fileStream = streamSaver.createWriteStream(download_button.attr('download').valueOf(), {
                size: epub_blob.size // Makes the percentage visible in the download
            });
            // more optimized pipe version
            // (Safari may have pipeTo but it's useless without the WritableStream)
            if (window.WritableStream && readableStream.pipeTo) {
                return readableStream.pipeTo(fileStream)
                    .then(() => console.log('done writing'))
            }

            // Write (pipe) manually
            window.writer = fileStream.getWriter()

            var reader = readableStream.getReader()
            var pump = function() {
                reader.read().then(function(res){
                    return res.done
                        ? writer.close()
                        : writer.write(res.value).then(pump);
                });
            }

            pump();
        } else {
            saveAs(epub_blob, download_button.attr('download').valueOf());
            //download_link.href = await blobToBase64(epub_blob);
        }
    })();
}

/**
 * Callbacks that listen for document.pmb_doc_conversion_requested should set them to TRUE immediately, otherwise
 * we'll assume no callback was set and so we'll just proceed with converting the file.
 * @type {boolean}
 */
var pmb_doc_conversion_request_handled = false;
jQuery(document).on('ready', function(){
    var download_button = jQuery('#download_link');
    download_button.removeClass('pmb-disabled');
    jQuery('.pmb-loading').hide();
    download_button.click(function(){
        download_button.addClass('pmb-disabled');
        jQuery('.pmb-loading').show();
        jQuery(document).trigger('pmb_doc_conversion_requested');
        // trigger document.pmb_wrap_up for legacy code.
        jQuery(document).trigger('pmb_wrap_up');
        // wait for the design to call document.pmb_doc_conversion_ready (and to set pmb_doc_conversion_request_handled
        // to true)  before proceeding with converting HTML to ePub
        jQuery(document).on('pmb_doc_conversion_ready', function(){
            pmb_create_epub();
        });
        // as a backup, in case the design didn't listen for document.pmb_doc_conversion_requested just go ahead and execute it.
        setTimeout(
            function(){
                if(! pmb_doc_conversion_request_handled){
                    pmb_create_epub();
                }
            },
            3000
        )
    });
});


// print-page-beautifier-functions.js's pmb_replace_internal_links_with_page_refs_and_footnotes has similar logic
function pmb_replace_internal_links_with_epub_file_links(){
    _pmb_for_each_hyperlink(
        function(a, id_url, id_selector){

            // find that section's title
            var section_element = jQuery(id_selector);
            // deduce its filename
            var filename = pmb_hyperlink_to_filename(section_element.attr('id')) + '.xhtml';
            // replace with a hyperlink to that
            a.attr('href',filename);
        },
        function(a){
            // leave external hyperlinks alone
        }
    )
}

/**
 * Convert a hyperlink into a valid filename
 * @param hyperlink
 * @returns {*}
 */
function pmb_hyperlink_to_filename(hyperlink){
    return hyperlink.replaceAll('https://','').replaceAll('http://','').replaceAll('/','-').replaceAll(':','').replaceAll('.','-').replaceAll('?','-').replaceAll('#','-').replaceAll('&','-');
}

/**
 * iBooks expects an alt tags when zooming in on images, so set it using the title attribute or caption
 */
function pmb_add_alt_tags(){
    jQuery('img').each(function(index, element){
        if(element.hasAttribute('alt') && element.attributes['alt'].value !== ''){
            return;
        }
        var new_alt = '';
        var has_title = false;
        var has_cap = false;
        if(element.hasAttribute('title')){
            has_title = true;
            var title = element.attributes['title'].value;
        }
        var jqe = jQuery(element);
        var captions = jqe.siblings('figcaption');
        if(captions.length > 0){
            has_cap = true;
            var caption = captions[0].innerText;
        }
        if(has_title && has_cap){
            new_alt = title + ': ' + caption;
        } else if (has_title){
            new_alt = title;
        } else if (has_cap){
            new_alt = caption;
        }

        element.setAttribute('alt',new_alt);
    });
}