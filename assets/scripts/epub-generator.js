function blobToBase64(blob) {
    return new Promise((resolve, _) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.readAsDataURL(blob);
    });
}
jQuery(document).ready(function(){
    var epub_options = {
        title: 'test',
        verbose: true,
        ignoreFailedDownloads:true,
        prependChapterTitles:false,
        numberChaptersInTOC:false,
    }
    var sections = [];
    var found_toc = false;
    jQuery('.pmb-section').each(function(index,element){
        var chapter_data = {content:element.outerHTML, beforeToc: ! found_toc};

        var jqelement = jQuery(element);
        var section_title = jqelement.find('.pmb-title');


        if(section_title.length){
            chapter_data.title = section_title.text();
            chapter_data.excludeFromToc = false;
        } else {
            chapter_data.excludeFromToc = true;
        }
        if(element.id.indexOf('pmb-toc') !== -1){
            found_toc = true;
            // don't add the TOC page to the book. epub-gen-memory.js adds it automatically
            return;
        }
        sections.push(chapter_data);
    });
    epub_options.tocInTOC = found_toc;

    const epub = epubGen.default;
    (async () => {
        const content = await epub(epub_options, sections);
        download_link.href = await blobToBase64(content);
    })();
});
