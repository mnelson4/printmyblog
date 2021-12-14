// https://stackoverflow.com/a/18650249/
alert('other epub gen');
function blobToBase64(blob) {
    return new Promise((resolve, _) => {
        const reader = new FileReader();
        reader.onloadend = () => resolve(reader.result);
        reader.readAsDataURL(blob);
    });
}

const chapters = [{
    content: '<html><img><h1 rand>Test</h1><IMG src="https://raw.githubusercontent.com/cpiber/epub-gen-memory/master/demo_preview.png"/><main></html>'
}];
const epub = epubGen.default;
(async () => {
    const content = await epub({ title: 'test', verbose: true }, chapters);
    link.href = await blobToBase64(content);

    const content2 = await epub({ title: 'test', verbose: true, version: 2 }, chapters);
    link2.href = await blobToBase64(content2);
})();