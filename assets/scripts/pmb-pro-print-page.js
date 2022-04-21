/**
 * Functions that does a bunch of standard wrap-up function calls done by pretty well all designs.
 */
function pmb_standard_print_page_wrapup(){
    pmb_remove_unsupported_content();
    pmb_fix_protocols();
    pmb_add_header_classes();
    pmb_fix_wp_videos();
    pmb_load_avada_lazy_images();
    pmb_reveal_dynamic_content();
    pmb_check_project_size('#pmb-print-page-warnings');
}
/**
 * Checks if the project is really big, in which case suggests either reducing splitting it up or reducing image quality
 * @var string warning_element_selector jQuery selector indicating where to place the warning if there is one.
 */
function pmb_check_project_size(warning_element_selector){
    //check for really, really big printouts
    var many_articles = jQuery('article').length > 200;
    // don't warn about many images if they've already reduced their quality
    var many_images = jQuery('img').length > 200 &&
        (typeof pmb_design_options == 'object' &&
            typeof pmb_design_options.image_quality === 'string' &&
            ['', 'uploaded'].includes(pmb_design_options.image_quality)
        );

    var warning_text = false;
    switch(true){
        case many_articles:
            warning_text = pmb_pro.translations.many_articles;
            break;
        case many_images:
            warning_text = pmb_pro.translations.many_images;
            break;
    }
    if(warning_text){
        var warning_element = jQuery(warning_element_selector);
        warning_element.append(warning_text);
        warning_element.show();

    }
}
/**
 * Looks at each of the types of tags provided, then replaces their external resources with a proxied-local one.
 * Useful when the conversion technology (eg html-to-pdf) can't access external resources.
 * Treats resources from whitelisted_domains as if they were local
 * eg
 * ```
 * var erc = new PmbExternalResourceCacher();
 * erc.replaceExternalImages();
 * erc.replaceIFrames();
 * ```
 * @param array html_tags
 * @param array whitelisted_external_domains
 */
function PmbExternalResourceCacher() {
    this.domains_to_not_map = pmb_pro.domains_to_not_map;
    this.external_resource_mapping = pmb_pro.external_resouce_mapping;
    this.external_resource_queue = [];

    this.replaceIFrames = function(){
        this._replace_external_resources_on('iframe','src');
    }

    this.replaceExternalImages = function(){
        this._replace_external_resources_on('img','src')
    }

    this._replace_external_resources_on = function(tag, attribute) {
        var that = this;
        jQuery(tag).each(function (index, element) {
            var treat_as_external = true;
            var remote_url = element.attributes[attribute].value;
            var remote_domain = (new URL(remote_url)).hostname;
            for (var i = 0; i < that.domains_to_not_map.length; i++) {
                if (remote_domain.indexOf(that.domains_to_not_map[i]) !== -1) {
                    treat_as_external = false;
                    break;
                }
            }
            if (! treat_as_external) {
                return;
            }
            // find if we already know the mapping
            var copy_url = that.external_resource_mapping[remote_url];
            if(copy_url !== null && copy_url !== false && typeof(copy_url) !== 'undefined'){
                that._update_element_and_map(remote_url, copy_url, element, attribute);
                return;
            }
            that.external_resource_queue.push(element);
        });

        if(that.check_done_swapping_external_resouces()){
            return;
        }

        // ok now blitz through the queue of external resouces to swap
        var element = null;
        while(element = that.external_resource_queue.shift()){
            var remote_url = element.attributes[attribute].value;
            that._fetch_and_replace_external_resource(remote_url, element, attribute);
        }
    }

    this.check_done_swapping_external_resouces = function(){
        if(this.external_resource_queue.length === 0){
            this.done_swapping_external_resources();
            return true;
        }
        return false;
    }

    this.done_swapping_external_resources = function(){
        jQuery(document).trigger('pmb_external_resouces_loaded');
    }

    /**
     * Updates the element's attribute with the copy's URL, and adds to the in-memory map (the server took care
     * of doing that on the server already).
     * @param external_url string
     * @param copy_url string
     * @param element
     * @private
     */
    this._update_element_and_map = function(external_url, copy_url, element, attribute){
        element.attributes[attribute].value = copy_url;
        this.external_resource_mapping[external_url] = copy_url;
    }

    /**
     * Sends an AJAX request to the server, so it can fetch the external resource and cache it, and reply with the
     * location of the cached resource
     * @param url
     * @param element
     * @param attribute
     * @private
     */
    this._fetch_and_replace_external_resource = function(url, element, attribute){
        var that = this;
        jQuery.post(
            pmb_pro.ajaxurl,
            {
                '_pmb_nonce': pmb_pro.pmb_nonce,
                'action': 'pmb_fetch_external_resource',
                'resource_url': url,
            },
            function(data, textStatus){
                if(data.success && typeof(data.data.copy_url) === 'string'){
                    that._update_element_and_map(url, data.data.copy_url, element, attribute);
                }
            }
        ).always(function(){
            that.check_done_swapping_external_resouces();
        });
    }


}