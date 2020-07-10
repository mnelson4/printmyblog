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

	var sections = document.getElementById('pmb-project-sections');
	var sortable_sections = Sortable.create(
		sections,
		{
			group:{
				name: 'shared',
			},
			animation:150,
		});
	jQuery('#pmb-save').click(function(){
		var pmb_items = [];
		jQuery('#pmb-project-area .pmb-project-item').each(function(index, element){
				pmb_items.push(element.attributes['data-id'].nodeValue);
		});
		var pmb_items_json = JSON.stringify(pmb_items);

		console.log(pmb_items_json);
	})
});
