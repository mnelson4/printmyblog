<?php
pmb_render_template(
    'partials/breadcrumb.php',
    [
            'project' => true,
            'project_url' => null
    ]
);
?>
<div class="wrap">
    <h1 class="wp-heading-inline"><?php esc_html_e('Projects', 'event_espresso'); ?></h1>
    <form class="pmb-inline-form" action="<?php echo esc_attr($add_new_url);?>" method="post">
        <button class="page-title-action">Add New</button>
    </form>
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