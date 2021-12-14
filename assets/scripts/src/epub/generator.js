alert('epubirfy');
const Epub = require("epub-gen");

const options = {
    title: "Alice's Adventures in Wonderland", // *Required, title of the book.
};

new Epub(options, "./pmb-ebook-test.epub").promise.then(
    () => console.log("Ebook Generated Successfully!"),
    err => console.error("Failed to generate Ebook because of ", err)
);