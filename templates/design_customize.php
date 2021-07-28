<?php
/**
 * @var $form \Twine\forms\base\FormSection
 * @var $form_url string
 * @var $design \PrintMyBlog\orm\entities\Design
 * @var $steps_to_urls array
 * @var $current_step string
 */
// outputs the form for the design

// save button, and textbox to make into a new design

pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => sprintf(
            __('Customize %s Design: %s', 'print-my-blog'),
                $design->getDesignTemplate()->getFormat()->coloredTitleAndIcon(),
                $design->getWpPost()->post_title
        ),
		'current_step' => $current_step,
		'steps_to_urls' => $steps_to_urls
	]
);
?>
<div class="pmb-right-help"><span class="dashicons dashicons-admin-site-alt3"></span><a target="_blank" href="<?php echo esc_url($design->getDesignTemplate()->getDocs());?>"><?php printf(esc_html__('Read %s Documentation Online', 'print-my-blog'), $design->getDesignTemplate()->getTitle());?></a></div>
<form method="POST" action="<?php echo esc_attr($form_url);?>">
    <?php echo $form->getHtmlAndJs();?>
    <button class="button button-primary pmb-save"><?php esc_html_e('Save & Proceed', 'print-my-blog');?></button>
<!--    <button id="pmb-save-as" class="button button-primary pmb-save-as">--><?php //esc_html_e('Save As...', 'print-my-blog');?><!--</button>-->
</form>
<?php pmb_render_template('partials/project_footer.php');
