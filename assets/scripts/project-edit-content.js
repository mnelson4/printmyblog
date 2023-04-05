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
			forceFallback: true,
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
		} else {
			// they haven't entered any body. Let's double-check they're really done.
			if(! window.confirm(pmb_project_edit_content_data.translations.empty_content_warning)){
				return false;
			}
		}
		jQuery('#pmb-project-depth').val(depth);
	});


	// filter form
	jQuery('.pmb-hide-filters').click(function(event){
		pmb_refresh_posts();
	});
	var pmb_inputting_search = false;
	jQuery('#pmb-project-choices-search').keyup(
		jQuery.debounce(
			1000,
			function(e){
				// don't do it on tab
				if(e.keyCode != 9){
					pmb_refresh_posts();
				}
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
		pmb_remove(selected_items);
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
	jQuery('#pmb-select-all').click(function(){
		pmb_load_all();
	});
	jQuery('#pmb-deselect-all').click(pmb_deselect_all);
	jQuery(document).keyup(function(e) {
		// console.log(e.which);
		var selected = jQuery('.pmb-selected');
		var target = jQuery(e.target);
		if(e.which == 8 || e.which == 46){
			// delete or backspace
			if(selected.length){
				pmb_remove(selected);
			}
		} else if(e.which == 40){
			// down arrow
			pmb_move(selected,'down')
		} else if(e.which == 38){
			// up arrow
			pmb_move(selected,'up')
		} else if(e.which == 39 || (e.which == 13 && target.hasClass('pmb-project-item'))){
			// right arrow or enter
			pmb_add(selected);
		} else if(e.which == 32 && target.hasClass('pmb-project-item')){
			// spacebar
			if(target.hasClass('pmb-selected')){
				Sortable.utils.deselect(e.target);
				target.find('.pmb-project-item-options').removeClass('pmb-show-options');
			} else {
				Sortable.utils.select(e.target);
				target.find('.pmb-project-item-options').addClass('pmb-show-options');
			}
			pmb_show_hide_actions();
		} else if(e.which == 27){
			pmb_deselect_all();
		}
	});
	jQuery(document).keydown(function(e){
		var target = jQuery(e.target);
		if((target.hasClass('pmb-project-item') && e.which == 32) || [38, 39].indexOf(e.which) !== -1){
			// spcacebar
			e.preventDefault();
		}
	})
});

/**
 * Makes nothing selected.
 */
function pmb_deselect_all(){
	jQuery('.pmb-project-item.pmb-selected').each(function(index,element){
		Sortable.utils.deselect(element);
	});
	jQuery('.pmb-project-item-options.pmb-show-options').removeClass('pmb-show-options');
	pmb_show_hide_actions();
}

function pmb_remove(selected_items){
	if(selected_items.length > 0){
		selected_items.remove();
		pmb_show_hide_actions();
	} else {
		alert(pmb_project_edit_content_data.translations.cant_remove);
	}
}
function pmb_add(selected_items) {
	selected_items.detach().appendTo('#pmb-project-main-matter');
	// move the new items above the "drag or click here" area
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
					// if we're in the backmatter, move to body
					jquery_item.detach().insertBefore(jQuery('#pmb-project-main-matter').children('.pmb-drag-here'));
					pmb_maybe_add_sortable_to(jQuery('#pmb-project-main-matter'), jquery_item);
				} else if(jquery_has_nested_items.length === 0 && jquery_item.is('#pmb-project-main-matter .pmb-project-item')){
					// if we're in body, move to front matter
					jquery_item.detach().insertBefore(jQuery('#pmb-project-front-matter').children('.pmb-drag-here'));
					pmb_maybe_add_sortable_to(jQuery('#pmb-project-front-matter'), jquery_item);
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
					// if we're in the backmatter, move to body
					jquery_item.detach().prependTo(jQuery('#pmb-project-main-matter'));
					pmb_maybe_add_sortable_to(jQuery('#pmb-project-main-matter'), jquery_item);
				} else if(jquery_has_nested_items.length === 0 && jquery_item.is('#pmb-project-main-matter .pmb-project-item')){
					// if we're in body, move to front matter
					jquery_item.detach().prependTo(jQuery('#pmb-project-back-matter'));
					pmb_maybe_add_sortable_to(jQuery('#pmb-project-front-matter'), jquery_item);
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
function pmb_load_more(page, select_all){
	if(typeof(select_all) === 'undefined'){
		select_all = false;
	}
	pmb_request_content(
		{'page':page},
		function(data)
		{
			jQuery('.pmb-show-more').remove()
			jQuery('#pmb-project-choices').append(data);
			var load_more_button = jQuery('.pmb-show-more');
			if(select_all){
				// check if we need to load more
				if(load_more_button.length === 0){
					pmb_select_all();
				} else {
					pmb_load_more(++page, true);
				}
			} else {
				pmb_setup_callbacks_on_new_options();
			}
		},
	);
}

function pmb_select_all(){
	pmb_setup_callbacks_on_new_options();
	jQuery('#pmb-project-choices .pmb-project-item').each(function(index,element){
		Sortable.utils.select(element);
	});
	pmb_show_hide_actions();
	jQuery('#pmb-select-all .pmb-spinner-container').remove();
}

function pmb_request_content(extra_params, callback){
	var form = jQuery("#pmb-filter-form");
	var data = form.serialize();
	data += '&_wpnonce=' + jQuery('#_wpnonce').val();
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
	if(button.length === 0){
		pmb_select_all();
	} else {
		button.prop('class','pmb-spinner');
		button.html('');
		var page = button.attr('data-page');
		pmb_load_more(page, true);
	}

}


function pmb_setup_callbacks_on_new_options(){
	// enable or disable the load more button, depending on whether there's any more to load
	var load_more_button = jQuery('.load-more-button');
	if(load_more_button.length !== 0){
		load_more_button.click(function(event){
			event.preventDefault();
			var button = jQuery(this);
			var page = button.attr('data-page');
			button.prop('class','pmb-spinner');
			button.html('');
			pmb_load_more(page);
		});
		var input = document.getElementById("pmb-load-more");
		input.addEventListener("keyup", function(event) {
			// Number 13 is the "Enter" key on the keyboard
			if (event.keyCode === 13 || event.keyCore === 32) {
				// Cancel the default action, if needed
				event.preventDefault();
				// Trigger the button element with a click
				document.getElementById("pmb-load-more").click();
			}
		});
	}

	jQuery(".pmb-add-material").click(function(event) {
		var add_button = this;
		event.preventDefault();
		pmb_deselect_all();
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
			'_wpnonce': _wpnonce.value,
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
			forceFallback: true,
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
	pmb_ensure_drag_here_at_bottom();
	for(var i=0; i<item_selections.length;i++){
		pmb_maybe_add_sortable_to_item(sortable_selection, jQuery(item_selections[i]));
	}
}

function pmb_ensure_drag_here_at_bottom(){
	jQuery('.pmb-drag-here').each(function(){
		var element = jQuery(this);
		var parent = element.parent();
		element.detach().appendTo(parent);
	})
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
			// remove sub-items because they're too far nested now.
			item_selection.find('.pmb-sortable .pmb-project-item').remove();
			// and hide the sorter on these now-leaf nodes
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
		// ensure no other options areas are showing
		jQuery('.pmb-project-item-options').removeClass('pmb-show-options');
		var options_area = that.children('.pmb-project-item-options');
		options_area.addClass('pmb-show-options')
		that.mouseleave( function(e) {
			// don't hide when using the select dropdown
			if(e.target.tagName.toLowerCase() != "select") {
				options_area.removeClass('pmb-show-options');
			}
		});
	});
	jQuery('.pmb-project-choices-column .pmb-project-item-header').dblclick(function(){
		pmb_add(jQuery(this).closest('.pmb-project-item'));
	});
	jQuery('.pmb-add-item').click(function(event){
		var selection = jQuery(event.currentTarget);
		var parent_draggable_items = selection.parents('.pmb-project-item');
		pmb_add(parent_draggable_items);
	});

	jQuery('.pmb-remove-item').click(function(event){
		var selection = jQuery(event.currentTarget);
		var parent_draggable_items = selection.closest('.pmb-project-item');
		pmb_remove(parent_draggable_items);
	});
	// remove any previous listeners for the post duplicating button
	jQuery('.pmb-duplicate-post-button').off();
	// ok now setup the post duplicating buttons
	jQuery('.pmb-duplicate-post-button').click(function(event){
		var clicked_jq_obj = jQuery(event.currentTarget);
		var id = clicked_jq_obj.data('id');
		jQuery.post(
			ajaxurl,
			{
				'action': 'pmb_duplicate_print_material',
				'_wpnonce': _wpnonce.value,
				'id': id,
				'project': jQuery('#pmb-print-material-project').val()
			},
			function(response){
				var sortable = jQuery(clicked_jq_obj).closest('.pmb-sortable')
				var clicked_item = jQuery(clicked_jq_obj).closest('.pmb-project-item')
				var new_item = jQuery(response.data.html)
				clicked_item.replaceWith(new_item);
				// new_item.insertAfter(clicked_item);
				pmb_setup_callbacks_on_new_options();
				pmb_maybe_add_sortable_to(sortable,new_item);
			},
			'json'
		).error(function(){
			alert(pmb_project_edit_content_data.translations.duplicate_error);
		});
	});
	jQuery('.pmb-duplicate-post-button').keypress(function(event) {
		// Number 13 is the "Enter" key on the keyboard
		if (event.keyCode === 13 || event.keyCore === 32) {
			// Cancel the default action, if needed
			event.preventDefault();
			// Trigger the button element with a click
			event.currentTarget.click();
		}
	});
}
function pmb_show_hide_actions(){
	var selected_items = jQuery('.pmb-selected').length;
	if(selected_items){
		jQuery('.pmb-actions-column').css('visibility','visible');
		jQuery('#pmb-use-ctrl-key').css('visibility','visible');
	} else {
		jQuery('.pmb-actions-column').css('visibility','hidden');
		jQuery('#pmb-use-ctrl-key').css('visibility','hidden');
	}
}
// https://stackoverflow.com/a/18927969/1493883
jQuery.fn.scrollTo = function(elem) {
	jQuery(this).scrollTop(jQuery(this).scrollTop() - jQuery(this).offset().top + jQuery(elem).offset().top - 100);
	return this;
};