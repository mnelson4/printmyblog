<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Print My Blog - Projects', 'event_espresso'); ?></h1> <a href="<?php echo $add_new_url;?>" class="page-title-action">Add New</a>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <form method="post">
                        <?php
                        $table->prepare_items();
                        $table->display(); ?>
                    </form>
                </div>
            </div>
        </div>
        <br class="clear">
    </div>
</div>