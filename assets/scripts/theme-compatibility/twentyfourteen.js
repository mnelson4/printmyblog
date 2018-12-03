jQuery(document).ready(function(){
    jQuery(document).on('pmb_wrap_up',function(){
        // twentyfourteen hides the description when printing on Firefox, so show it again.
        jQuery('.site-description').css('display','block');
    })
});
