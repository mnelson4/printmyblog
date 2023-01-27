/**
 * Functions that does a bunch of standard wrap-up function calls done by pretty well all designs.
 */
function pmb_standard_print_page_wrapup(){
    pmb_remove_unsupported_content();
    pmb_fix_protocols();
    pmb_add_header_classes();
    pmb_fix_wp_videos();
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
 * Useful when the conversion technology (eg html-to-epub) can't access external resources.
 * Treats resources from whitelisted_domains as if they were local.
 * Triggers document.pmb_external_resouces_loaded when finished.
 * eg
 * ```
 * var erc = new PmbExternalResourceCacher();
 * erc.replaceExternalImages();
 * ```
 * @param array html_tags
 * @param array whitelisted_external_domains
 */
function PmbExternalResourceCacher() {
    this.domains_to_not_map = pmb_pro.domains_to_not_map;
    this.external_resource_mapping = pmb_pro.external_resouce_mapping;
    this.external_resources_to_cache = [];
    this.resources_pending_caching_count = 0;

    this.replaceIFrames = function(){
        this._replace_external_resources_on('iframe','src');
    }

    this.replaceExternalImages = function(){
        this._replace_external_resources_on('img','src')
    }

    this._replace_external_resources_on = function(tag, attribute) {
        var that = this;
        jQuery(tag).each(function (index, element) {
            var remote_url = element.attributes[attribute].value;
            try{
                var remote_domain = (new URL(remote_url)).hostname;
            }catch(error){
                //invalid URL probably; continue
                return;
            }
            // double-check the image needed to be cached anyway (because it's external)
            for (var i in that.domains_to_not_map) {
                if (remote_domain.indexOf(that.domains_to_not_map[i]) !== -1) {
                    // it's not an external image, no need to replace with a cached version
                    return;
                }
            }
            // find if we already know the mapping
            var copy_url = that.external_resource_mapping[remote_url];
            if(typeof(copy_url) !== 'undefined' && copy_url !== null){
                that.resources_pending_caching_count++;
                var success_callback = function(cloned_element){
                    that.external_resource_mapping[remote_url] = copy_url;
                    console.log('PMB swapped "' + remote_url + '" for "' + copy_url + '"');
                    that.resources_pending_caching_count--;
                    that.check_done_swapping_external_resouces();
                };
                var error_callback = function(cloned_element){
                    console.log('PMB ERROR  swapping "' + remote_url + '" for "' + copy_url + '"');
                    setTimeout(
                        function(){
                            that._update_element_and_map(remote_url, copy_url, element, attribute,
                                success_callback,
                                function(){
                                    // error'd twice, give up.
                                    console.log('PMB gave up swapping "' + remote_url + '" for "' + copy_url + '"');
                                    that.resources_pending_caching_count--;
                                    that.check_done_swapping_external_resouces();
                                }
                            );
                        },
                        500
                    );
                };
                that._update_element_and_map(remote_url, copy_url, element, attribute,
                    success_callback,
                    error_callback
                );
                return;
            }
            that.external_resources_to_cache.push(element);
        });

        if(that.check_done_swapping_external_resouces()){
            return;
        }
        this.continue_caching_external_resources(attribute);
    }

    this.continue_caching_external_resources = function(attribute){
        var element = this.external_resources_to_cache.shift();
        if( ! element){
            return;
        }
        var remote_url = element.attributes[attribute].value;
        this._fetch_and_replace_external_resource(remote_url, element, attribute);
        var that = this;
        // don't overrun the server all at once
        setTimeout(
            function(){
                that.continue_caching_external_resources(attribute);
            },
            500
        );
    }

    this.check_done_swapping_external_resouces = function(){
        if(this.resources_pending_caching_count === 0 && this.external_resources_to_cache.length === 0){
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
     * @param onload_callback
     * @param function onerror_callback
     * @private
     */
    this._update_element_and_map = function(external_url, copy_url, element, attribute, onload_callback, onerror_callback){
        if(element.hasAttribute('srcset')){
            element.removeAttribute('srcset');
        }
        if(element.hasAttribute('sizes')){
            element.removeAttribute('sizes');
        }
        element.attributes[attribute].value = copy_url;
        if(attribute === 'src'){
            element.src = copy_url;
        }
        var clone = element.cloneNode();
        element.replaceWith(clone);
        if(typeof(onload_callback) === 'function'){
            clone.onload = function(){
                onload_callback(clone)
            };
        }
        if(typeof(onerror_callback) === 'function'){
            clone.onerror = function(){
                onerror_callback(clone);
            }
        }

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
        this.resources_pending_caching_count++;
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
                    //we will need for the image to reload, so undo decrementing that count
                    that.resources_pending_caching_count++;
                    that._update_element_and_map(url, data.data.copy_url, element, attribute,
                        function(){
                            that.resources_pending_caching_count--;
                            that.check_done_swapping_external_resouces();
                        },
                        function(){
                            that.resources_pending_caching_count--;
                            that.check_done_swapping_external_resouces();
                        });
                }
            }
        ).always(function(){
            that.resources_pending_caching_count--;
            that.check_done_swapping_external_resouces();
        });
    }


}

/**
 * All designs and formats should do these things as soon as the page is loaded.
 * Stuff that might need to wait for other plugins' JS to run should listen for the document.pmb_doc_conversion_requested event.
 */
jQuery(document).ready(function(){
    pmb_prevent_lazy_loading();
    // Don't just hide screen-only content, remove it entirely so there's no chance of it messing up the document.
    jQuery('.pmb-screen-only').remove();
});

/**
 * It turns out we can't rely on the order in which document.ready and window.load are triggered (usually document.ready
 * is first, but it turns out to be random if images are lazy-loaded).
 * So code that wants to wait for them to both complete can instead listen for document.pmb_document_done_and_window_loaded.
 * Code that wants to run on either of them that runs first can run on pmb_document_done_or_window_loaded
 */
var pmb_window_load_fired = false;
var pmb_done_document_ready_tasks = false;

jQuery(document).ready(function(){
    pmb_done_document_ready_tasks = true;
    if( pmb_window_load_fired){
        jQuery(document).trigger('pmb_document_ready_and_window_loaded');
    } else {
        jQuery(document).trigger('pmb_document_ready_or_window_loaded');
    }
});

jQuery(window).on("load", function() {
    pmb_window_load_fired = true;
    if( pmb_done_document_ready_tasks){
        jQuery(document).trigger('pmb_document_ready_and_window_loaded');
    } else {
        jQuery(document).trigger('pmb_document_ready_or_window_loaded');
    }
});