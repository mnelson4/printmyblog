<div class="haller-repeat-header-title" >
    <?php
    $image_url = $pmb_design->getSetting('publication_header_logo');
    if(! $image_url){
        $image_url = $pmb_design->getSetting('publication_logo');
    }
    if( $image_url ){
        ?>
        <img class="haller-header-logo mayer-no-resize" src="<?php echo esc_url($image_url);?>">
        <?php
    } else {
        ?>
        <?php echo $pmb_design->getSetting('publication_title'); ?>
        <?php
    }
    ?>
</div>