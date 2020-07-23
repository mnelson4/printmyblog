// Ask the server if the print-page content
function PmbProLoadingPage(pmb_instance_vars, translations) {
	this.working = False;

	/**
	 * Initializes variables and begins fetching taxonomies, then gets started fetching posts/pages.
	 * @function
	 */
	this.initialize = function () {
		alert('load PDF!');
	};

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