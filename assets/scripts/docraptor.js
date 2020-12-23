/**
 * @version 2.0.0 from originally from https://docraptor.com/docraptor-1.0.0.js
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
        //form.target = "_blank";

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

function PmbAsyncPdfCreation(
    use_printmyblog_middleman,
    authorization_data,
    doc_attrs,
    update_callback,
    success_callback,
    failure_callback
) {
    this.use_printmyblog_middleman = use_printmyblog_middleman;
    this.authorization_data = authorization_data;
    this.doc_attrs = doc_attrs;
    this.upddate_callback = update_callback;
    this.success_callback = success_callback;
    this.failure_callback = failure_callback;

    if (use_printmyblog_middleman) {
        this.base_url = authorization_data.endpoint;
        this.begin_url = this.base_url + 'licenses/' + authorization_data.license_id + '/installs/' + authorization_data.install_id + '/generate';
        this.status_url = this.base_url + 'status/';
        this.authorization_header = authorization_data.authorization_header;
    } else {
        this.base_url = 'https://docraptor.com/';
        this.begin_url = this.base_url + 'docs';
        this.status_url = this.base_url + 'status/';
        this.authorization_header = 'Basic ' + btoa(authorization_data + ':');
    }
    this.requestSettings = function(data, success_callback){
        var jquery_request_settings = {
            'method':'POST',
            'data':data,
            'success':success_callback,
            'error':(jqXhr, text_status, text_message) => {
                // response body will be JSON on success, XML on failure. Luckily, jQuery handles both
                if(typeof jqXhr.responseXML === 'object'
                    && typeof jqXhr.responseXML.children === 'object'
                        && typeof jqXhr.responseXML.children[0] === 'object'
                        && typeof jqXhr.responseXML.children[0].children === 'object'
                        && typeof jqXhr.responseXML.children[0].children[0] === 'object'){
                    var message = jqXhr.responseXML.children[0].children[0].innerHTML;
                } else if(text_message !== ''){
                    var message = text_message;
                } else {
                    var message = text_status;
                }
                console.log(jqXhr);
                this.error('Commuication error. It was: ' + message);
            }
        };
        if( this.authorization_header){
            jquery_request_settings['headers'] = {
                'Authorization': this.authorization_header
            };
        }
        return jquery_request_settings;
    }
    this.begin = function(){
        jQuery.ajax(
            this.begin_url,
            this.requestSettings(
                {doc:this.doc_attrs},
                (response) => {
                    if (typeof response.status_id !== 'undefined'){
                        // ok we've got a status ID. Let's keep pinging it until its done
                        this.status_id = response.status_id;
                        this.status_url += this.status_id;
                        // delay slightly before continuing
                        setTimeout(
                            () => {
                                this.continue()
                            },
                            1000
                        );
                    }
                }
            )
        );

    };

    this.continue = function(){
        jQuery.ajax(
            this.status_url,
            this.requestSettings(
                {},
                (response) => {
                    //expect a response like documented https://docraptor.com/documentation/api#api_async
                    if(typeof response.status !== 'undefined'){
                        switch(response.status){
                            case 'queued':
                            case 'working':
                                this.upddate_callback(response);
                                setTimeout(
                                    () => {
                                        this.continue();
                                    },
                                    2000
                                );
                                break;
                            case 'completed':
                                this.success_callback(response.download_url);
                                break;
                            case 'failed':
                                this.error(response.validation_errors);
                                break;
                        }
                    }
                }
            )
        )
    };
    this.complete = function(download_url){
        this.success_callback(download_url);
    };
    this.error = function(error_message){
        this.failure_callback(error_message);
    };
};