function PmbSetupPage(pmb_instance_vars, translations) {
	this.default_rest_url = pmb_instance_vars.default_rest_url;
	this.ajax_url = pmb_instance_vars.ajax_url;
	this.proxy_for = '';
	this.site_name = '';
	this.spinner = jQuery(pmb_instance_vars.spinner_selector);
	this.site_ok = jQuery(pmb_instance_vars.site_ok_selector);
	this.site_bad = jQuery(pmb_instance_vars.site_bad_selector);
	this.site_status = jQuery(pmb_instance_vars.site_status_selector);
	this.dynamic_categories = jQuery(pmb_instance_vars.dynamic_categories_selector);
	this.dynamic_categories_spinner = jQuery(pmb_instance_vars.dynamic_categories_spinner_selector);
	this.post_type_selector = pmb_instance_vars.post_type_selector;
	this.translations = translations;
	this.taxonomies = {};
	this.author = jQuery(pmb_instance_vars.author_selector);
	this.nonce = pmb_instance_vars.nonce;

	site_input = jQuery(pmb_instance_vars.site_input_selector);


	this.init = function() {
		// If WP REST API proxy is enabled, change the REST API URL depending on what they enter into the site URL.
		jQuery(site_input).keyup(
			jQuery.debounce(
				() => {
					return this.updateRestApiUrl(site_input.val());
				},
				2000
			)
		);
		// Initialize the list of taxonomies etc.
		this.updateRestApiUrl(site_input.val());

		let post_type = jQuery(this.post_type_selector + ':checked').val();
		jQuery( ".pmb-date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			changeYear: true,
			changeMonth: true,
		});

		// If they change the post type, change the taxonomies available.
		jQuery('input' + this.post_type_selector + '[type=radio]').change(() => {
			this.getTaxonomies();
		});
	};

	this.updateRestApiUrl = function(site_url) {
		if(site_url === '') {
			this.proxy_for = '';
			this.getTaxonomies();
			this.updateAuthorSelector();
		}
		this.spinner.show();
		this.site_bad.hide();
		this.site_ok.hide();

		var data = {
			'action': 'pmb_fetch_rest_api_url',
			'site': site_url
		};

		jQuery.post(this.ajax_url, data, (response) => {
				this.spinner.hide();
				if(response.success && response.data.name && response.data.proxy_for){
					if( ! response.data.is_local){
						this.proxy_for =	response.data.proxy_for;
					} else {
						this.proxy_for = '';
					}
					this.site_name = response.data.name;
					this.site_ok.show();
					this.getTaxonomies();
                    this.updateAuthorSelector();
				} else if(response.data.error && response.data.message) {
					this.reportNoRestApiUrl(response.data.message, response.data.error);
				} else {
					this.reportNoRestApiUrl(response.data.unknown_site_name, 'no_code');
				}
			},
			'json'
		).fail( (event) => {
			this.spinner.hide();
		});
	};

	this.reportNoRestApiUrl = function(error_string, code) {
		this.site_bad.show();
		this.site_status.html(error_string + ' [' + code + '] ');
	};

	this.getTaxonomies = function() {
		this.dynamic_categories_spinner.show();
		this.dynamic_categories.html('');
		var alltaxonomiesCollection = new wp.api.collections.Taxonomies();
		let data = {
			proxy_for : this.proxy_for
		};
		let post_type = jQuery(this.post_type_selector + ':checked').val();
		data.type = post_type;
		// Reset taxonomies to null, so we know it's not up-to-date.
		alltaxonomiesCollection.fetch({data:data}).done((taxonomies) => {
			this.taxonomies = taxonomies;
			this.generateTaxonomyInputs();
		});
	};
    /**
	 * Sets up the author select2 input. If this is working with WP API Proxy, the base URL may have changed,
	 * so we need to be able to call this dynamically and/or repeatedly.
     */
	this.updateAuthorSelector = function () {
		this.author.select2({
			width: '300px',
            ajax: {
                url: this.default_rest_url + '/users',
                dataType: 'json',
                // Additional AJAX parameters go here; see the end of this chapter for the full code of this example
                data: (params) => {
                    var query = {
                        _envelope:1,
						_wpnonce:this.nonce
                    };
                    if(params.term){
                        query.search=params.term;
                    }
                    if(params.page){
                        query.page=params.page;
                    }
                    if(this.proxy_for){
                        query.proxy_for = this.proxy_for;
                    }
                    return query;
                },
                processResults: (data, params) => {
                    const current_page = params.page || 1;
                    let prepared_data = {
                        results: [],
                        pagination:{
                            more:data.headers['X-WP-TotalPages'] > current_page
                        }
                    };
                    for(var i=0; i<data.body.length; i++){
                        let user = data.body[i];
                        prepared_data.results.push({
                            id:user.id,
                            text: user.name + ' (' + user.slug + ')'
                        });
                    }
                    return prepared_data;
                }
            }
		})
	};

	this.generateTaxonomyInputs = function() {
		this.dynamic_categories_spinner.hide();
		if (jQuery.isEmptyObject( this.taxonomies )){
			this.dynamic_categories.html('<tr><th scope="row">' + this.translations.no_categories + '</th></tr>');
		}
		jQuery.each(this.taxonomies, (index, taxonomy)=>{
			const slug = taxonomy.rest_base;
			this.dynamic_categories.append(
				'<tr><th scope="row"><label for="' + slug + '">' + taxonomy.name+ '</label></th><td><select id="' + slug + '" class="pmb-taxonomies-select" name="taxonomies[' + slug + '][]" multiple="multiple"></select></td></tr>'
			);
			jQuery('#'+slug).select2({
				width: 'resolve',
				ajax: {
					url: this.default_rest_url + '/' + taxonomy.rest_base,
					dataType: 'json',
					// Additional AJAX parameters go here; see the end of this chapter for the full code of this example
					data: (params) => {
						var query = {
							_envelope:1,
						};
						if(params.term){
							query.search=params.term;
						}
						if(params.page){
							query.page=params.page;
						}
						if(this.proxy_for){
							query.proxy_for = this.proxy_for;
						}
						return query;
					},
					processResults: (data, params) => {
						const current_page = params.page || 1;
						let prepared_data = {
							results: [],
							pagination:{
								more:data.headers['X-WP-TotalPages'] > current_page
							}
						};
						for(var i=0; i<data.body.length; i++){
							let term = data.body[i];
							prepared_data.results.push({
								id:term.id,
								text: term.name
							});
						}
						return prepared_data;
					}
				}
			});
		});
	};
}

var pmb = null;
jQuery(document).ready(function () {
	wp.api.loadPromise.done( function() {
		// @var object pmb_setup_page
		pmb = new PmbSetupPage(pmb_setup_page.data, pmb_setup_page.translations);
		pmb.init();
	});
});