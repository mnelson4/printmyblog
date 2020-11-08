<?php
/**
 * @var $project_form \Twine\forms\base\FormSectionProper
 * @var $form_url string
 */
pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => __('Edit Project Content', 'print-my-blog'),
		'current_step' => $current_step,
		'steps_to_urls' => $steps_to_urls
	]
);
?>
<form method="POST" action="<?php echo esc_attr($form_url);?>">
    <?php echo $form->get_html_and_js();?>
    <button class="button button-primary pmb-save"><?php esc_html_e('Save', 'print-my-blog');?></button>
</form>
<?php pmb_render_template('partials/project_footer.php');