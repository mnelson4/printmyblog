<?php
pmb_render_template(
    'partials/pro_header.php'
);
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Pro Print ― Projects', 'event_espresso'); ?></h1>
        <a href="<?php echo esc_attr($add_new_url);?>" class="page-title-action"><?php esc_html_e('Start New Project', 'print-my-blog');?></a>
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