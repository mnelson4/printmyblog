<?php
/**
 * @var $form \Twine\forms\base\FormSectionProper
 * @var $form_url string
 * @var $design \PrintMyBlog\orm\entities\Design
 */
// outputs the form for the design

// save button, and textbox to make into a new design

pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => sprintf(__('Customize Design: %s', 'print-my-blog'), $design->getWpPost()->post_title),
		'current_step' => $current_step
	]
);
?>
<div><span class="pmb-help"><?php printf(__('Design supports %d layers of nested divisions', 'print-my-blog'), $design->getDesignTemplate()->getLevels());?></span></div>
<form method="POST" action="<?php echo esc_attr($form_url);?>">
    <?php echo $form->get_html_and_js();?>
    <button class="button button-primary pmb-save"><?php esc_html_e('Save', 'print-my-blog');?></button>
<!--    <button id="pmb-save-as" class="button button-primary pmb-save-as">--><?php //esc_html_e('Save As...', 'print-my-blog');?><!--</button>-->
</form>
<?php pmb_render_template('partials/project_footer.php');
