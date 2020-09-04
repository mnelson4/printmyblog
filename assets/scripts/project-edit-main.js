jQuery(document).ready(function(){
    jQuery('#pmb-project-title').keyup(
        jQuery.debounce(
            2000,
            () => {
                return pmb_update_project();
            }
        )
    );
    jQuery('#pmb-project-form input').change(function(){
        pmb_update_project();
    })
});

function pmb_update_project()
{
    // send ajax
    var form_element = jQuery('#pmb-project-form');
    jQuery.post(
        form_element.attr('action'),
        form_element.serialize(),
        (response) => {
    if(response.success ){
        jQuery('#pmb-project-title-saved-status').html(pmb_project_edit.translations.saved);
    } else {
        jQuery('#pmb-project-title-saved-status').html(pmb_project_edit.translations.error);
    }
},
    'json'
).fail( (event) => {
        jQuery('#pmb-project-title-saved-status').html(pmb_project_edit.translations.error);
});
    // show success
}