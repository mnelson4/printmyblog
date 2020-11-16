<?php
use PrintMyBlog\controllers\Admin;
/**
 * @var $form \Twine\forms\base\FormSection
 */
pmb_render_template(
        'partials/project_header.php',
        [
                'project' => $project,
                'page_title' => __('Edit Project', 'print-my-blog'),
                'current_step' => $current_step,
                'steps_to_urls' => $steps_to_urls
        ]
);
?>
    <form id="pmb-project-form" method="POST" action="">
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <?php echo $form->getHtmlAndJs();?>
        <input type="submit" class="button button-primary" value="<?php esc_html_e('Submit & Proceed', 'print-my-blog');?>">
    </form>
<?php pmb_render_template('partials/project_footer.php');