// Ask the server if the print-page content
function PmbProLoadingPage(pmb_instance_vars, translations) {
	this.working = false;
	this.instance_vars = pmb_instance_vars;
	this.translations = translations;
	this.html_url = null;

	/**
	 * Initializes variables and begins fetching taxonomies, then gets started fetching posts/pages.
	 * @function
	 */
	this.initialize = function () {
		this.checkHtmlPageStatus();
	};

	this.checkHtmlPageStatus = function() {
		jQuery.post(
			this.instance_vars.status_url,
			[],
			(response) => {
				if (typeof(response) === 'object' && typeof(response.url) === 'string'){
					// window.location.replace(response.url);
					this.html_url = response.url;
					this.finished();
				} else {
					alert(this.translations.error);
				}
			},
			'json'
		).fail( () => {
				alert(this.translations.error);
			}
		);
		this.working = true;
	};
	this.finished = function(){
		jQuery('.pmb-print-ready').show();
		jQuery('.pmb-loading-content').hide();
		jQuery('#pmb-view-html').attr('href',this.html_url);
		jQuery('#pmb-download-pdf').click(
			() => {
				this.generatePdf();
			}
		);
	}
	this.generatePdf = function(){
		DocRaptor.createAndDownloadDoc("YOUR_API_KEY_HERE", {
			test: true, // test documents are free, but watermarked
			type: "pdf",
			// document_content: document.querySelector('html').innerHTML, // use this page's HTML
			// document_content: "<h1>Hello world!</h1>",               // or supply HTML directly
			document_url: this.html_url,            // or use a URL
			// javascript: true,                                        // enable JavaScript processing
			// prince_options: {
			//   media: "screen",                                       // use screen styles instead of print styles
			// }
		})
	}

	/**
	 * Converts text response, which we hope to mostly be JSON, into a Javascript object.
	 * Meant to be used by jQuery's 'text json' converters
	 * @return object
	 */
	this.jsonConverter = function() {
		let new_result = result;
		// Sometimes other plugins echo out junk before the start of the real JSON response.
		// So we need to chop off all that extra stuff.
		do{
			// Find the first spot that could be the beginning of valid JSON...
			var start_of_json = Math.min(
				new_result.indexOf('{'),
				new_result.indexOf('['),
				new_result.indexOf('true'),
				new_result.indexOf('false'),
				new_result.indexOf('"')
			);
			// Remove everything before it...
			new_result = new_result.substring(start_of_json);
			try{
				// Try to parse it...
				let i = jQuery.parseJSON(new_result);
				// If that didn't have an error, great. We found valid JSON!
				return i;
			}catch(error){
				// There was an error parsing that substring. So let's chop off some more and keep hunting for valid JSON.
				// Chop off the character that made this look like it could be valid JSON, and then continue iterating...
				new_result = new_result.substring(1);
			}
		}while(start_of_json !== false);
		// Never found any valid JSON. Throw the error.
		throw "No JSON found in AJAX response using custom JSON parser.";
	}
}


var pmb_pro_load = null;
jQuery(document).ready(function () {
	pmb_pro_load = new PmbProLoadingPage(
		pmb_load_data.data,
		pmb_load_data.i18n
	);
	// I know I'll add babel.js someday. But for now, if there's an error initializing (probably because of a
	// Javascript syntax error, or the REST API isn't working) let the user know.
	setTimeout(function(){
			if(! pmb_pro_load.working){
				alert(pmb_load_data.i18n.init_error);
			}
		},
		30000);
	pmb_pro_load.initialize();
});