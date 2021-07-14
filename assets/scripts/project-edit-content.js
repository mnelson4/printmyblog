jQuery(document).ready(function(){
	var choices = document.getElementById('pmb-project-choices');
	var sortable_choices = Sortable.create(
		choices,
		{
			group:{
				name: 'shared',
			},
			filter: '.no-drag',
			sort: false,
			multiDrag: true,
			selectedClass: "pmb-selected",
			animation: 150,
			multiDragKey: 'CTRL',
			onSelect: function(/**Event*/evt) {
				pmb_show_hide_actions();
			},
			// Called when an item is deselected
			onDeselect: function(/**Event*/evt) {
				pmb_show_hide_actions();
			},
		});
	jQuery('.pmb-sortable').each(function(index, element){
		pmb_create_sortable_from(element);
	});
	jQuery('#pmb-project-form').submit(function(){
		var main_sections = jQuery('#pmb-project-main-matter');
		pmb_get_sections_json_from(jQuery('#pmb-project-front-matter'),'#pmb-project-front-matter-data');
		pmb_update_heights(main_sections);
		pmb_get_sections_json_from(main_sections,'#pmb-project-main-matter-data');
		pmb_get_sections_json_from(jQuery('#pmb-project-back-matter'),'#pmb-project-back-matter-data');

		var first_item = main_sections.children('.pmb-project-item:first');
		var depth = 1;
		if(first_item.length){
			depth = first_item[0].attributes['data-height'].nodeValue;
		}
		jQuery('#pmb-project-depth').val(depth);
	});


	// filter form
	jQuery('.pmb-hide-filters').click(function(event){
		pmb_refresh_posts();
	});
	jQuery('#pmb-project-choices-search').keyup(
		jQuery.debounce(
			1000,
			() => {
				pmb_refresh_posts();
			}
		)
	);
	pmb_refresh_posts();
	jQuery('#pmb-move-up').click(function(){
		pmb_move_selected_items('up');
	});
	jQuery('#pmb-move-down').click(function(){
		pmb_move_selected_items('down');
	});
	jQuery('#pmb-add-item').click(function(event){

		var selected_items = jQuery('#pmb-project-choices .pmb-selected');

		if(selected_items.length > 0){
			pmb_add(selected_items);
		} else {
			alert(pmb_project_edit_content_data.translations.cant_add);
		}
	});

	jQuery('#pmb-remove-item').click(function(event){
		var selected_items = jQuery('.pmb-selected');

		if(selected_items.length > 0){
			selected_items.remove();
		} else {
			alert(pmb_project_edit_content_data.translations.cant_remove);
		}
	});
	// prevent submitting the form
	jQuery('.pmb-actions-column button').click(function(){
		event.preventDefault();
	});
	// prevent sortable JS's multidrag from deselecting when clicking these buttons
	jQuery('.pmb-actions-column button').on('pointerup mouseup touchend', function(event){
		event.stopPropagation();
	});
	jQuery('#pmb-expand-filters').click(function(){
		jQuery('.pmb-filters-closed').css('display','none');
		jQuery('.pmb-filters-closed-flex').css('display','none');
		jQuery('.pmb-filters-opened').css('display','block');
		jQuery( ".pmb-date" ).datepicker({
			dateFormat: 'yy-mm-dd',
			changeYear: true,
			changeMonth: true,
		});
		pmb_init_taxonomy_filters();
	});
	jQuery('.pmb-hide-filters').click(function(){
		jQuery('.pmb-filters-closed').css('display','block');
		jQuery('.pmb-filters-closed-flex').css('display','flex');
		jQuery('.pmb-filters-opened').css('display','none');
	})
	jQuery('#pmb-load-all').click(function(){
		pmb_load_all();
	})
	jQuery('#pmb-select-all').click(function(){
		jQuery('#pmb-project-choices .pmb-project-item').each(function(index,element){
			Sortable.utils.select(element);
		});
	});
	jQuery('#pmb-deselect-all').click(function(){
		jQuery('.pmb-project-item.pmb-selected').each(function(index,element){
			Sortable.utils.deselect(element);
		});
	});
});
function pmb_add(selected_items) {
	selected_items.detach().appendTo('#pmb-project-main-matter');
	// move the new items above the "drag or click here" area
	pmb_move(selected_items,'up');
	pmb_maybe_add_sortable_to(jQuery('#pmb-project-main-matter'), selected_items);
	jQuery('#pmb-project-main-matter').scrollTo(selected_items);
}
function pmb_init_taxonomy_filters(){
	jQuery('.pmb-taxonomies-select').each(function(index, element){
		var rest_base_attr = element.attributes['data-rest-base'];
		jQuery(element).select2({
			width: 'resolve',
			ajax: {
				url: pmb_project_edit_content_data.default_rest_url + '/' + rest_base_attr.value,
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
				},
				beforeSend: function ( xhr ) {
					xhr.setRequestHeader( 'X-WP-Nonce', wpApiSettings.nonce );
				},
			}
		});
	});
}

function pmb_refresh_posts(){
	jQuery('#pmb-project-choices').html('<div class="no-drag"><div class="pmb-spinner"></div></div>');
	pmb_request_content(
		null,
		function(data){
			jQuery('#pmb-project-choices').html(data);
			pmb_setup_callbacks_on_new_options();
		}
	);
}

/**
 *
 * @param direction 'up' or 'down'
 */
function pmb_move_selected_items(direction){
	var selected = jQuery('.pmb-selected');
	if(selected.length === 0){
		alert(pmb_project_edit_content_data.translations.cant_move);
	} else {
		pmb_move(selected, direction);
	}
}

/**
 *
 * @param jquery_selection jQuery
 * @param direction 'up' or 'down'
 */
function pmb_move(jquery_selection, direction){
	if(direction === 'up'){
		// var first_item = jquery_selection.first();
		// if(first_item.is('.pmb-project-item:first-child')){
		// 	// if not nested, don't move any further
		// 	var parent_items = first_item.parents('pmb-project-item');
		// 	if(parent_items.length == 0 ){
		// 		return;
		// 	}
		// 	// if nested, keep going
		//
		// 	return;
		// }
		jquery_selection.each(function(index,item){
			var jquery_item = jQuery(item);
			var jquery_has_nested_items = jquery_item.find('.pmb-project-item');
			if(jquery_item.is('.pmb-project-item:first-child')){
				// if it's the first item already, check if we can move it up into a parent sortable
				var parent_items = jquery_item.parents('.pmb-project-item');
				if(parent_items.length !== 0 ){
					// it can be placed in a parent
					jquery_item.insertBefore(parent_items.first());
					pmb_maybe_add_sortable_to(parent_items.first().parents().first(), jquery_item);
				} else if(jquery_has_nested_items.length === 0 && jquery_item.is('#pmb-project-back-matter .pmb-project-item')){
					// if we're in the backmatter, move to main matter
					jquery_item.detach().insertBefore(jQuery('#pmb-project-main-matter').children('.pmb-drag-here'));
				} else if(jquery_has_nested_items.length === 0 && jquery_item.is('#pmb-project-main-matter .pmb-project-item')){
					// if we're in main matter, move to front matter
					jquery_item.detach().insertBefore(jQuery('#pmb-project-front-matter').children('.pmb-drag-here'));
				}
			} else {
					var prev_item = jquery_item.prev();
					var prev_nested_draggable_area = prev_item.find('.pmb-draggable-area:not(.pmb-sortable-inactive)');
					// nest it if the item being moved has no children and the destination supports it
					if(jquery_has_nested_items.length !== 0 || prev_nested_draggable_area.length === 0){
						jquery_item.detach().insertBefore(prev_item);
					} else {
						//add it just before the "drag-or-click here" element
						jquery_item.detach().insertBefore(prev_nested_draggable_area.first().children('.pmb-drag-here'));
						pmb_maybe_add_sortable_to(prev_nested_draggable_area, jquery_item);
					}
			}

		});
	} else {
		jQuery(jquery_selection.get().reverse()).each(function(index,item){
			var jquery_item = jQuery(item);
			var jquery_has_nested_items = jquery_item.find('.pmb-project-item');
			if(jquery_item.is('.pmb-project-item:nth-last-child(2)')){
				// if it's the first item already, check if we can move it up into a parent sortable
				var parent_items = jquery_item.parents('.pmb-project-item');
				if(parent_items.length !== 0 ){
					// it can be placed in a parent
					jquery_item.insertAfter(parent_items.first());
					pmb_maybe_add_sortable_to(parent_items.first().parents().first(), jquery_item);
				} else if(jquery_has_nested_items.length === 0 && jquery_item.is('#pmb-project-front-matter .pmb-project-item')){
					// if we're in the backmatter, move to main matter
					jquery_item.detach().prependTo(jQuery('#pmb-project-main-matter'));
				} else if(jquery_has_nested_items.length === 0 && jquery_item.is('#pmb-project-main-matter .pmb-project-item')){
					// if we're in main matter, move to front matter
					jquery_item.detach().prependTo(jQuery('#pmb-project-back-matter'));
				}
			} else {
				var next_item = jquery_item.next();
				var nested_draggable_area = next_item.find('.pmb-draggable-area:not(.pmb-sortable-inactive)');
				// nest it if the item being moved has no children and the destination supports it
				if (jquery_has_nested_items.length !== 0 || nested_draggable_area.length === 0) {
					jquery_item.detach().insertAfter(next_item);
				} else {
					jquery_item.detach().prependTo(nested_draggable_area.first());
					pmb_maybe_add_sortable_to(nested_draggable_area, jquery_item);
				}
			}
		});
	}
	jQuery('.pmb-project-matters').scrollTo(jquery_selection);
}

function pmb_move_into(jquery_selection_to_move, jquery_selection_to_move_into, direction){
	if(direction === 'up'){
		// look for any children
	}
}

/**
 * Loads more posts from the website for selectin content
 */
function pmb_load_more(page, load_more){
	if(typeof(load_more) === 'undefined'){
		load_more = false;
	}
	pmb_request_content(
		{'page':page},
		function(data)
		{
			jQuery('.pmb-show-more').remove()
			jQuery('#pmb-project-choices').append(data);
			var load_more_button = jQuery('.pmb-show-more');
			if(load_more_button.length !== 0 && load_more){
				pmb_load_more(++page, true);
			} else {
				pmb_setup_callbacks_on_new_options();
			}
		},
	);
}

function pmb_request_content(extra_params, callback){
	var form = jQuery("#pmb-filter-form");
	var data = form.serialize();
	if(! jQuery('#pmb-show-included').is(':checked')){
		var exclude = [];
		jQuery('.pmb-project-matters .pmb-project-item').each(function(index, element) {
			exclude.push(jQuery(element).attr('data-id'));
		});
		data += '&exclude=' + exclude.join(',');
	}

	if(typeof(extra_params) === 'object'){
		for(var key in extra_params){
			data += '&' + key + '=' + extra_params[key];
		}
	}

	jQuery.ajax({
		type: form.attr('method'),
		url: form.attr('action'),
		data: data, // serializes the form's elements.
		success: callback
	});
}

function pmb_load_all(){
	var button = jQuery('.load-more-button');
	button.prop('class','pmb-spinner');
	button.html('');
	jQuery('#pmb-project-choices .pmb-project-item').remove();
	var page = 1;
	pmb_load_more(1, true);
}


function pmb_setup_callbacks_on_new_options(){
	// enable or disable the load more button, depending on whether there's any more to load
	var load_more_button = jQuery('.load-more-button');
	if(load_more_button.length === 0){
		jQuery('#pmb-load-all').addClass('disabled');
	} else {
		jQuery('#pmb-load-all').removeClass('disabled');
		load_more_button.click(function(event){
			event.preventDefault();
			var button = jQuery(this);
			var page = button.attr('data-page');
			button.prop('class','pmb-spinner');
			button.html('');
			pmb_load_more(page);
		});
	}

	jQuery(".pmb-add-material").click(function(event) {
		var add_button = this;
		event.preventDefault();
		jQuery('#pmb-add-print-materials-dialogue').dialog({
			'dialogClass'   : 'wp-dialog',
			'modal'         : true,
			'autoOpen'      : true,
			'closeOnEscape' : true,
			'closeText' :'',
			'buttons'       : [
				{
					"text": "Create",
					'class':'button button-primary',
					'click': function () {
						pmb_add_print_material_submit(add_button, this);
					},
				},
				{
					"text" : "Cancel",
					'class':'button',
					'click': function() {
						jQuery(this).dialog('close');
					}
				},
			],
			'width': "500px",
			open: function(event, ui)
			{
				var _this = jQuery(this);
				jQuery('.ui-widget-overlay').bind('click', function()
				{
					_this.dialog('close');
				});
			}
		})
	});
	// function from pmb-general.js
	pmb_setup_item_options();
}

function pmb_add_print_material_submit(add_button, submit_button){
	jQuery.post(
		ajaxurl,
		{
			'action': 'pmb_add_print_material',
			'_nonce': _wpnonce.value,
			'title': jQuery('#pmb-print-material-title').val(),
			'project': jQuery('#pmb-print-material-project').val()
		},
		function(response){
			var sortable = jQuery(add_button).closest('.pmb-sortable')
			var new_item = jQuery(response.data.html)
			new_item.appendTo(sortable);
			pmb_setup_callbacks_on_new_options();
			pmb_maybe_add_sortable_to(sortable,new_item);
			jQuery(submit_button).dialog('close');
			jQuery('#pmb-print-material-title').val('');
		},
		'json'
	).error(function(){
		alert(pmb_project_edit_content_data.translations.insert_error);
	});
}

/**
 * Creates a string of JSON from the data from the sortable items and stuff it into the specified input.
 * @param items_selector string
 * @param input_selector string
 */
function pmb_get_sections_json_from(selection,input_selector){
	var items = pmb_get_contents(selection, 0);
	var json = JSON.stringify(items);
	jQuery(input_selector).val(json);
}

function pmb_create_sortable_from(element){
	var sorter = Sortable.create(
		element,
		{
			group:{
				name: 'shared',
			},
			filter: '.no-drag',
			animation:150,
			fallbackOnBody: true,
			swapThreshold: .80,
			onAdd: function (event) {
				var items = [];
				if(event.items.length){
					items = event.items;
				} else {
					items = [event.item];
				}
				pmb_maybe_add_sortable_to(jQuery(event.target), jQuery(items));
			},
			onSelect: function(/**Event*/evt) {
				pmb_show_hide_actions();
			},
			// Called when an item is deselected
			onDeselect: function(/**Event*/evt) {
				pmb_show_hide_actions();
			},
			multiDrag: true,
			selectedClass: "pmb-selected",
			animation: 150,
			multiDragKey: 'CTRL'
		});
	element.sorter = sorter;
}

/**
 *
 * @param sortable_selection sortable they were added to
 * @param item_selections new items being added
 */
function pmb_maybe_add_sortable_to(sortable_selection, item_selections){
	for(var i=0; i<item_selections.length;i++){
		pmb_maybe_add_sortable_to_item(sortable_selection, jQuery(item_selections[i]));
	}
}

function pmb_maybe_add_sortable_to_item(sortable_selection, item_selection){
	var nested_level = pmb_count_level(sortable_selection);
	var jquery_obj = sortable_selection;

	var max_levels = jquery_obj.attr('data-max-nesting');
	// If that target didn't know the max nesting level, ask the root.
	if( ! max_levels){
		max_levels = jquery_obj.parents('.pmb-sortable-root').attr('data-max-nesting');
	}
	if(nested_level < max_levels){
		var sortable_items = item_selection.children('.pmb-sortable-inactive, .pmb-sortable');
		if(sortable_items.length){
			pmb_create_sortable_from(sortable_items[0]);
			sortable_items.removeClass('pmb-sortable-inactive');
			sortable_items.addClass('pmb-sortable');
		}
	} else {
		var sortable_items = item_selection.children('.pmb-sortable');
		if(sortable_items.length){
			if(typeof sortable_items[0].sorter !== 'undefined'){
				sortable_items[0].sorter.destroy();
			}
			sortable_items.removeClass('pmb-sortable');
			sortable_items.addClass('pmb-sortable-inactive');
		}
	}
}

function pmb_get_contents(jquery_obj, current_depth = 1){
	var items = [];
	if( !jquery_obj || ! jquery_obj.length){
		return items;
	}
	var element = jquery_obj[0];

	for(var index = 0; index < element.children.length; index++){
		var child = element.children[index];
		if(typeof(child.attributes['data-id']) === 'undefined'){
			continue;
		}
		var child_jquery_obj = jQuery(child);
		var template_jquery_obj = child_jquery_obj.children('.pmb-project-item-header').find('select.pmb-template');
		var template = template_jquery_obj.val();
		var subs = child_jquery_obj.children('.pmb-subs ');
		items.push([
			child_jquery_obj.data('id'),//child.attributes['data-id'].nodeValue, // post ID
			template, // desired template
			child_jquery_obj.data('height'),//child.attributes['data-height'].nodeValue, // node height
			current_depth, // node depth
			pmb_get_contents(subs, current_depth + 1) // sub-items
		]);
	}
	return items;
}

function pmb_update_heights(jquery_obj){
	// select absolutely all items then filter out ones with children
	var leaf_nodes = jquery_obj.find('.pmb-project-item').filter(function(index, element){
		// only add it if it has children
		if(jQuery(element).find('.pmb-project-item').length){
			return false;
		}
		return true;
	});
	leaf_nodes.each(function(index, element){
		jQuery(element).data('height', 0);
		var height_to_set = 0;
		jQuery(element).parents('.pmb-project-item').each(function(index, element){
			height_to_set++;
			var parent = jQuery(element);
			if(parent.data('height') === undefined || parent.data('height') < height_to_set){
				parent.data('height', height_to_set);//.setAttribute('data-height',++height_to_set);
			}
		});
		// var parent_item = element.parentElement.parentElement;
		// parent_item.setAttribute('data-height', 1);
		// var grand_parent_item = parent_item.parentElement.parentElement;
		// grand_parent_item.setAttribute('data-height', 2);
		// var great_grand_parent_item = grand_parent_item.parentElement.parentElement;
		// grand_parent_item.setAttribute('data-height', 3);
	});
}

function pmb_count_level(jquery_obj){
	return jquery_obj.parents('.pmb-sortable').length;
}

function pmb_setup_item_options(){
	jQuery('.pmb-project-item-header').hover(function(){
		var that = jQuery(this);
		jQuery('.pmb-project-item-options').css('display','none');
		var options_area = that.children('.pmb-project-item-options');
		options_area.css('display','block');
		that.mouseleave( function(e) {
			if(e.target.tagName.toLowerCase() != "select") {
				options_area.css('display','none');
			}
		});
	});
	jQuery('.pmb-project-choices-column .pmb-project-item-header').dblclick(function(){
		pmb_add(jQuery(this).closest('.pmb-project-item'));
	});
}
function pmb_show_hide_actions(){
	var selected_items = jQuery('.pmb-selected').length;
	if(selected_items){
		jQuery('.pmb-actions-column').css('visibility','visible');
	} else {
		jQuery('.pmb-actions-column').css('visibility','hidden');
	}
}
// https://stackoverflow.com/a/18927969/1493883
jQuery.fn.scrollTo = function(elem) {
	jQuery(this).scrollTop(jQuery(this).scrollTop() - jQuery(this).offset().top + jQuery(elem).offset().top - 100);
	return this;
};