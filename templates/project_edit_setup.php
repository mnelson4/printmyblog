<?php
use PrintMyBlog\controllers\Admin;
/**
 * @var $form \Twine\forms\base\FormSectionProper
 */
pmb_render_template(
        'partials/project_header.php',
        [
                'project' => $project,
                'page_title' => __('Edit Project', 'print-my-blog'),
                'show_back' => false
        ]
);
?>
    <form id="pmb-project-form" method="POST" action="">
        <?php wp_nonce_field( 'pmb-project-edit' );?>
        <?php echo $form->get_html_and_js();?>
        <input type="submit" value="<?php esc_html_e('Submit & Proceed', 'print-my-blog');?>">
    </form>
<?php pmb_render_template('partials/project_footer.php');