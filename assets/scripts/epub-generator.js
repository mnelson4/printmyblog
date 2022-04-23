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
jQuery(document).on('pmb_wrap_up', function(){
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
        if(element.id.indexOf('pmb-toc') !== -1){
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
        const blob = await epub(epub_options, sections);
        jQuery('#download_link').removeClass('pmb-disabled');
        jQuery('.pmb-loading').remove();

        const readableStream = blob.stream()

        // more optimized pipe version
        // (Safari may have pipeTo but it's useless without the WritableStream)
        if (window.WritableStream && readableStream.pipeTo) {
            return readableStream.pipeTo(fileStream)
                .then(() => console.log('done writing'))
        }

        // Write (pipe) manually
        window.writer = fileStream.getWriter()

        const reader = readableStream.getReader()
        const pump = () => reader.read()
            .then(res => res.done
                ? writer.close()
                : writer.write(res.value).then(pump))



        // const uInt8 = new TextEncoder().encode('StreamSaver is awesome')
        //
        // // streamSaver.createWriteStream() returns a writable byte stream
        // // The WritableStream only accepts Uint8Array chunks
        // // (no other typed arrays, arrayBuffers or strings are allowed)
        // const fileStream = streamSaver.createWriteStream('filename.txt', {
        //     size: uInt8.byteLength, // (optional filesize) Will show progress
        //     writableStrategy: undefined, // (optional)
        //     readableStrategy: undefined  // (optional)
        // });
        jQuery('#download_link').click(function(){
            pump();
            // const writer = fileStream.getWriter()
            // writer.write(uInt8)
            // writer.close()
        });
    })();
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
    return hyperlink.replaceAll('http','').replaceAll('/','-').replaceAll(':','').replaceAll('.','-');
}