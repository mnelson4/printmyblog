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
pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => __('Generate Project', 'print-my-blog'),
		'current_step' => $current_step,
		'steps_to_urls' => $steps_to_urls
	]
);
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
	$generate_link = add_query_arg(
		[
			'ID' => $project->getWpPost()->ID,
			'action' => \PrintMyBlog\controllers\Admin::SLUG_ACTION_EDIT_PROJECT,
			'subaction' => \PrintMyBlog\controllers\Admin::SLUG_SUBACTION_PROJECT_GENERATE,
            'format' => $generation->getFormat()->slug()
		],
		admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	);
	$format_slug = $generation->getFormat()->slug();
	?>
    <div class="pmb-generate-options-for-<?php echo esc_attr($format_slug);?>">
        <h2><?php echo $generation->getFormat()->title();?></h2>
        <?php
        if($generation->isGenerated()){
            if($generation->isDirty() || $generation->isOld()){
                ?>
                <div class="pmb-previous-generation-info">
                    <b>
                        <?php printf(
                            esc_html__('The file generated %s is out-of-date.', 'print-my-blog'),
                            date_i18n(get_option('date_format'), $generation->generatedTimestamp())
                        );?>
                    </b>
                    <ul>
                        <?php foreach($generation->getDirtyReasons() as $reason){ ?>
                        <li><?php echo $reason;?></li>
                        <?php } ?>
                    </ul>
                </div>
                <br/>
                <button
                        class="button button-primary pmb-generate pmb_spin_on_click" data-format="<?php echo esc_attr($format_slug);?>"><?php esc_html_e('Regenerate', 'print-my-blog');
                ?></button>
                <?php
            } else {
                ?>
                <div class="pmb-previous-generation-info">
                    <b>
                        <?php printf(
                            esc_html__('This file was already generated %s', 'print-my-blog'),
                            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $generation->generatedTimestamp())
                        );?>
                    </b>
                </div>
                <br/>
                <button class="button pmb-generate pmb_spin_on_click" data-format="<?php echo esc_attr($format_slug);?>"><?php esc_html_e('Regenerate Anyway', 'print-my-blog');?></button>
                <?php
            }
        } else {
        ?>
                <a class="button button-primary pmb-generate pmb_spin_on_click" data-format="<?php echo esc_attr($format_slug);?>"><?php
                    esc_html_e('Generate', 'print-my-blog');
                ?></a>
        <?php
        }
        ?>
        <div class="pmb-after-generation" <?php echo ! $generation->isGenerated() || $generation->isDirty() || $generation->isOld() ? 'style="display:none"' : '';?>>
            <a class="button button-primary" href="<?php echo esc_attr($generation->getGeneratedIntermediaryFileUrl());?>"><?php printf(__('View %s Print Page', 'print-my-blog'), $generation->getFormat()->title());?></a>
        </div>
    </div>
    <br>
    <a href="<?php echo esc_url(admin_url(PMB_ADMIN_HELP_PAGE_PATH));?>"><span class="pmb-spinner-container"><span class="dashicons
                dashicons-sos pmb-icon"></span></span> <?php esc_html_e('Something not right? We’re happy to help!', 'print-my-blog');?></a>
<?php
}
pmb_render_template('partials/project_footer.php');

