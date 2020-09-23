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
		Sortable.create(
			element,
			{
				group:{
					name: 'shared',
				},
				animation:150,
				fallbackOnBody: true,
				swapThreshold: 0.25
			});
	});
	jQuery('#pmb-project-form').submit(function(){
		var pmb_items = pmb_get_contents(jQuery('#pmb-project-sections'));
		// jQuery('#pmb-project-sections .pmb-project-item').each(function(index, element){
		// 		pmb_items.push(element.attributes['data-id'].nodeValue);
		// });
		var pmb_items_json = JSON.stringify(pmb_items);
		jQuery('#pmb-project-sections-data').val(pmb_items_json);
	})
});

function pmb_get_contents(jquery_obj){
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
			template, // desired template type
			pmb_get_contents(subs) // sub-items
		]);
	}
	return items;
}
