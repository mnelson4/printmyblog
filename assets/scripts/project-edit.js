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
});
