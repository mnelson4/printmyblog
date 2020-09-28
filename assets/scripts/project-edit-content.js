// alert('ye');
jQuery(document).ready(function(){
	var choices = document.getElementById('pmb-project-choices');
	var sortable_choices = Sortable.create(
		choices,
		{
			group:{
				name: 'shared',
			},
			sort: false,
		});

	// var sections = document.getElementById('pmb-project-sections');
	// var sortable_sections = Sortable.create(
	// 	sections,
	// 	{
	// 		group:{
	// 			name: 'shared',
	// 		},
	// 		animation:150,
	// 		// fallbackOnBody: true,
	// 		// swapThreshold: 0.65
	// 	});
	jQuery('.pmb-sortable').each(function(index, element){
		pmb_create_sortable_from(element);
	});
	jQuery('#pmb-project-form').submit(function(){
		var content_data = pmb_get_contents(jQuery('#pmb-project-sections'), 0, 0);

		var pmb_items_json = JSON.stringify(content_data['items']);
		jQuery('#pmb-project-sections-data').val(pmb_items_json);
		jQuery('#pmb-project-layers-detected').val(content_data['layers_detected']);
	})
});

function pmb_create_sortable_from(element){
	var sorter = Sortable.create(
		element,
		{
			group:{
				name: 'shared',
			},
			animation:150,
			fallbackOnBody: true,
			swapThreshold: 0.25,
			onAdd: function (event) {
				var nested_level = pmb_count_level(jQuery(event.target));
				if(nested_level < pmb_project_edit_content_data.levels){
					var sortable_items = jQuery(event.item).children('.pmb-sortable-inactive');
					if(sortable_items.length){
						pmb_create_sortable_from(sortable_items[0]);
						sortable_items.removeClass('pmb-sortable-disabled');
						sortable_items.addClass('pmb-sortable');
					}
				} else {
					var sortable_items = jQuery(event.item).children('.pmb-sortable');
					if(sortable_items.length){
						sortable_items[0].sorter.destroy();
						sortable_items.removeClass('pmb-sortable');
						sortable_items.addClass('pmb-sortable-disabled');
					}
				}
			},
		});
	element.sorter = sorter;
}

function pmb_get_contents(jquery_obj, layers_detected = 0, current_layer = 0){
	var items = [];
	if( !jquery_obj || ! jquery_obj.length){
		return items;
	}
	var element = jquery_obj[0];

	// Keep track of how many layers there are
	// but only if this layer isn't empty.
	if(element.children.length) {
		current_layer++;
		if (layers_detected < current_layer) {
			layers_detected = current_layer;
		}
	}

	for(var index = 0; index < element.children.length; index++){
		var child = element.children[index];
		var child_jquery_obj = jQuery(child);
		var template_jquery_obj = child_jquery_obj.children('.pmb-project-item-header').find('select.pmb-template');
		var template = template_jquery_obj.val();
		var subs = child_jquery_obj.children('.pmb-subs ');
		var sub_content_data = pmb_get_contents(subs, layers_detected, current_layer);
		items.push([
			child.attributes['data-id'].nodeValue, // post ID
			template, // desired template
			sub_content_data['items'] // sub-items
		]);
		if(layers_detected < sub_content_data['layers_detected']){
			layers_detected = sub_content_data['layers_detected'];
		}
	}
	return {
		'items': items,
		'layers_detected': layers_detected
	}
}

function pmb_count_level(jquery_obj){
	return jquery_obj.parents('.pmb-sortable').length + 1;
}