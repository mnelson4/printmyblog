/**
 * @version 1.0.0 from https://docraptor.com/docraptor-1.0.0.js
 * @type {{createAndDownloadDoc: Window.DocRaptor.createAndDownloadDoc}}
 */
window.DocRaptor = {
    // Creates an HTML form with doc_attrs set, submits it. If successful
    // this will force the browser to download a file. On failure it shows
    // the DocRaptor error directly.
    createAndDownloadDoc: function(api_key, doc_attrs) {
        // jQuery.post(
        //     "https://docraptor.com/docs",
        //     [
        //         'user_credentials':api_key,
        //         'doc':doc_attrs
        //     ],
        //
        // )
        //
        var makeFormElement = function(name, value) {
            var element = document.createElement("textarea")
            element.name = name
            element.value = value
            return element
        }

        var form = document.createElement("form");
        form.action = "https://docraptor.com/docs";
        form.method = "post";
        form.style.display = "none";
        form.target = "_blank";

        form.appendChild(makeFormElement("user_credentials", api_key));

        for (var key in doc_attrs) {
            if (key == "prince_options") {
                for (var option in doc_attrs.prince_options) {
                    form.appendChild(makeFormElement("doc[prince_options][" + option + "]", doc_attrs.prince_options[option]));
                }
            } else {
                form.appendChild(makeFormElement("doc[" + key + "]", doc_attrs[key]));
            }
        }

        document.body.appendChild(form);
        form.submit()
    }
}