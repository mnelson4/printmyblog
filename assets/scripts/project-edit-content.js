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
		var sections = jQuery('#pmb-project-sections');
		pmb_update_heights(sections);
		var items = pmb_get_contents(sections, 1);

		var pmb_items_json = JSON.stringify(items);
		jQuery('#pmb-project-sections-data').val(pmb_items_json);
		var first_item = sections.children('.pmb-project-item:first');
		var depth = 0;
		if(first_item.length){
			depth = first_item[0].attributes['data-height'].nodeValue;
		}
		jQuery('#pmb-project-depth').val(depth);
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

function pmb_get_contents(jquery_obj, current_depth = 1){
	var items = [];
	if( !jquery_obj || ! jquery_obj.length){
		return items;
	}
	var element = jquery_obj[0];

	for(var index = 0; index < element.children.length; index++){
		var child = element.children[index];
		var child_jquery_obj = jQuery(child);
		var template_jquery_obj = child_jquery_obj.children('.pmb-project-item-header').find('select.pmb-template');
		var template = template_jquery_obj.val();
		var subs = child_jquery_obj.children('.pmb-subs ');
		items.push([
			child.attributes['data-id'].nodeValue, // post ID
			template, // desired template
			child.attributes['data-height'].nodeValue, // node height
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
		element.setAttribute('data-height', 0);
		jQuery(element).parents('.pmb-project-item').each(function(index, element){
			element.setAttribute('data-height',index+1);
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
	return jquery_obj.parents('.pmb-sortable').length + 1;
}