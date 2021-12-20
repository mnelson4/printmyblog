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
    }
    var sections = [];
    jQuery('.pmb-section').each(function(index,element){
        sections.push({
            content:element.outerHTML
        });
    });
    const epub = epubGen.default;
    (async () => {
        const content = await epub(epub_options, sections);
        download_link.href = await blobToBase64(content);
    })();
});
