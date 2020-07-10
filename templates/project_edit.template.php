<div class="wrap">

<!--        <h2 class="nav-tab-wrapper">-->
<!--            <a href="#" class="nav-tab">Display Options</a>-->
<!--            <a href="#" class="nav-tab">Social Options</a>-->
<!--        </h2>-->
        <!-- clunky tabs: https://code.tutsplus.com/tutorials/the-wordpress-settings-api-part-5-tabbed-navigation-for-settings--wp-24971 -->
        <!-- or use https://gcostudios.com/how-to-add-jquery-ui-tabs-into-wordpress/ for jquery ui tabs -->
    <h1><?php esc_html_e('Print My Blog - Edit Project', 'event_espresso'); ?></h1>
    <div id="pmb-book-layout">
        <div id="pmb-choices-area" class="pmb-draggable-area">
            <ul id="pmb-project-choices" class="pmb-selection-list list-group">
                <?php foreach($post_options as $post){
                    ?><li class="list-group-item pmb-grabbable pmb-project-item" data-id="<?php echo esc_attr($post->ID);?>"><?php echo $post->post_title;?></li><?php
                }
                ?>
            </ul>
        </div>
        <div id="pmb-project-area" class="pmb-draggable-area">
            <ul id="pmb-project-sections" class="pmb-selection-list list-group">
            </ul>
        </div>
    </div>
    <input type="submit" id="pmb-save" value="<?php esc_html_e('Save', 'print-my-blog'); ?>">



</div>