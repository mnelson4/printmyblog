<?php
/**
 * @var $generations \PrintMyBlog\entities\ProjectGeneration[]
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $steps_to_urls array
 * @var $current_step string
 * @var $license_info null|array with keys 'expiry_date', 'remaining_credits' and 'plan_credits'
 * @var $upgrade_url string
 * @var $review_url string
 * @var $suggest_review boolean
 */

use PrintMyBlog\controllers\Admin;

pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => __('Generate Project', 'print-my-blog'),
		'current_step' => $current_step,
		'steps_to_urls' => $steps_to_urls
	]
);
do_action('project_edit_generate__under_header', $project, $generations);
?>
<?php if (is_array($license_info)){
   ?>
    <p class="pmb-credit-reminder"><?php
        printf(
            esc_html__('You have %1$s/%2$s download credits left which expire on %3$s',
                'print-my-blog'),
            '<span class="pmb-credits-remaining">' . $license_info['remaining_credits'] . '</span>',
            $license_info['plan_credits'],
            date_i18n(get_option('date_format'),rest_parse_date($license_info['expiry_date'])))
        ;?></p>
    <?php
}?>
<?php
foreach($generations as $generation){
    $base_args = [
        'ID' => $project->getWpPost()->ID,
        'action' => Admin::SLUG_ACTION_EDIT_PROJECT,
        'format' => $generation->getFormat()->slug()
    ];
	$generate_link = add_query_arg(
	    array_merge(
            $base_args,
            [
                'subaction' => Admin::SLUG_SUBACTION_PROJECT_GENERATE,
            ]
        ),
		admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	);
	$change_design_link = add_query_arg(
	        array_merge(
	                $base_args,
	        [
                'subaction' => Admin::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN
            ]
        ),
        admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
    );
	$edit_design_link = add_query_arg(
	        array_merge(
                $base_args,
                [
                    'subaction' => Admin::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN,

                ]
            ),
        admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
    );

	$format_slug = $generation->getFormat()->slug();
	?>
    <div class="pmb-generate-options-for-<?php echo esc_attr($format_slug);?>">
        <h2><?php echo $generation->getFormat()->coloredTitleAndIcon();?></h2>
        <a class="button button-primary pmb-generate pmb_spin_on_click" data-format="<?php echo esc_attr($format_slug);?>" aria-label="<?php echo esc_attr(sprintf(esc_html__('Generate %s', 'print-my-blog'), $generation->getFormat()->title()));?>"><?php
            esc_html_e('Generate', 'print-my-blog');
        ?></a>
        <a
                class="button button-secondary" href="<?php echo esc_attr($change_design_link);?>"><?php esc_html_e('Change Design', 'print-my-blog');?></a>
        <a
            class="button button-secondary" href="<?php echo esc_attr($edit_design_link);?>"><?php esc_html_e('Edit Design', 'print-my-blog');?></a>
        <?php
        ?>
        <div class="pmb-after-generation" style="display:none">
            <button class="button button-primary" href="<?php echo esc_attr($generation->getGeneratedIntermediaryFileUrl());?>"><?php printf(__('View %s Print Page', 'print-my-blog'), $generation->getFormat()->title());?></button>
        </div>
    </div>
    <br>

<?php
}
?>
<a href="<?php echo esc_url(admin_url(PMB_ADMIN_HELP_PAGE_PATH));?>"><span class="pmb-spinner-container"><span class="dashicons
                dashicons-sos"></span></span> <?php esc_html_e('Something not right? We’re happy to help!', 'print-my-blog');?></a>
<?php
pmb_render_template('partials/project_footer.php');

