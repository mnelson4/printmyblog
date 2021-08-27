jQuery(document).ready(function(){
    jQuery('.pmb-duplicate').click(function(){
        return confirm(pmb_data.translations.confirm_duplicate);
    });
});