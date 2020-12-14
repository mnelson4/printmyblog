<?php
/**
 * @var $generations \PrintMyBlog\entities\ProjectGeneration[]
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $steps_to_urls array
 * @var $current_step string
 * @var $license_info null|array with keys 'expiry_date', 'remaining_credits' and 'plan_credits'
 * @var $upgrade_url string
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
            $license_info['remaining_credits'],
            $license_info['plan_credits'],
            date_i18n(get_option('date_format'),rest_parse_date($license_info['expiry_date'])))
        ;?></p>
    <?php
}?>
<p><?php esc_html_e('Your project is ready to be generated! Use one of the buttons below to generate it, but feel free to use the links above to tweak it.',
        'print-my-blog');?>
</p>
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
    <div id="pmb-generate-options-for-<?php echo esc_attr($format_slug);?>">
        <h2><?php echo $generation->getFormat()->title();?></h2>
        <?php
        if($generation->isGenerated()){
            if($generation->isDirty()){
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
                <button class="button button-primary pmb-generate" data-format="<?php echo esc_attr($format_slug);
                ?>"><?php esc_html_e('Regenerate', 'print-my-blog');
                ?></button>
                <?php
            } else {
                ?>
                <div class="pmb-previous-generation-info">
                    <b>
                        <?php printf(
                            esc_html__('This file was already generated %s', 'print-my-blog'),
                            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $generation->generatedTimeSql())
                        );?>
                    </b>
                </div>
                <br/>
                <a class="button pmb-generate" data-format="<?php echo esc_attr($format_slug);?>"><?php esc_html_e('Regenerate Anyway', 'print-my-blog');?></a>
                <?php
            }
        } else {
        ?>
                <a class="button button-primary pmb-generate" data-format="<?php echo esc_attr($format_slug);?>"><?php
                    esc_html_e('Generate', 'print-my-blog');
                ?></a>
        <?php
        }
        ?>
        <div class="pmb-after-generation" <?php echo ! $generation->isGenerated() ? 'style="display:none"' : '';?>>
            <a class="button pmb-download-preview <?php echo $generation->isGenerated() ? 'button-primary' : '';?>"
            data-format="<?php echo
            esc_attr
            ($format_slug);
            ?>" data-html-url="<?php echo esc_attr($generation->getGeneratedIntermediaryFileUrl());?>"><?php esc_html_e('Download Preview', 'print-my-blog');?></a>
                <a href="<?php echo esc_attr($generation->getGeneratedIntermediaryFileUrl());?>"
                class="pmb-view-html"
                   title="<?php echo esc_attr(__('View HTML', 'print-my-blog'));
                   ?>"><span  class="dashicons
                dashicons-html pmb-icon"></span></a>
                <a href="<?php echo esc_url(admin_url(PMB_ADMIN_HELP_PAGE_PATH));?>" title="<?php echo esc_html__('Get Help', 'print-my-blog');?>"><span class="dashicons
                dashicons-sos pmb-icon"></span></a>
            <div class="pmb-download-preview-dialog" style="display:none" id="pmb-download-preview-dialog-<?php echo
            esc_attr ($format_slug);?>">
                <div class="pmb-after-download-preview">
                    <p class="pmb-middle-important-text"><?php esc_html_e('Downloading Watermarked Preview File...', 'print-my-blog');?></p>

                    <div class="pmb-content-boxes">
                        <div class="pmb-content-box-wrap">
                            <div class="pmb-content-box-inner">
                                <h3><?php esc_html_e('Something Not Look Right?', 'print-my-blog'); ?></h3>
                                <a class="button button-primary" href="<?php echo esc_url(admin_url
                                (PMB_ADMIN_HELP_PAGE_PATH));?>"><?php esc_html_e('Let’s Get It Fixed!', 'print-my-blog');?></a>
                            </div>
                        </div>
                        <div class="pmb-content-box-wrap">
                            <div class="pmb-content-box-inner">
                                <h3><?php esc_html_e('Looks good?', 'print-my-blog'); ?></h3>
<?php if(! is_array($license_info) ){ ?>
    <p class="pmb-important"><?php esc_html_e('You do not have an active plan!', 'print-my-blog');?></p>
    <a href="<?php echo esc_url($upgrade_url);?>" class="button button-primary"><?php esc_html_e('View Plans',
            'print-my-blog');?></a>
    <p><?php esc_html_e('Sorry you have no more download credits.', 'print-my-blog');?></p>
<?php } elseif(! $license_info['remaining_credits']) {?>
    <a href="<?php echo esc_url($upgrade_url);?>" class="button button-primary"><?php esc_html_e('View Plan Upgrades',
            'print-my-blog');?></a>
    <p class="description"><?php esc_html_e('Or stay on your current plan and wait for your plan to renew', 'print-my-blog');
    ?></p>
<?php } else { ?>
    <a href="<?php echo esc_url($upgrade_url);?>" id="pmb-download-<?php echo esc_attr
    ($format_slug);?>"
       class="button
    button-primary pmb-reveal-before-download-actual" data-format="<?php echo
    esc_attr ($format_slug);?>"><?php esc_html_e('Download Non-Watermarked File',
            'print-my-blog');?></a>
    <p class="description"><?php printf(
            esc_html__('This will use one of your %1$s remaining credits, and is non-refundable.',
            'print-my-blog'),
        $license_info['remaining_credits']
        );?></p>
<?php } ?>
                            </div>
                        </div>
                </div>
            </div>
            <div class="pmb-before-download-dialog" style="display:none" id="pmb-before-download-dialog-<?php echo
            esc_attr
            ($generation->getFormat()->slug());?>">
                <div class="pmb-after-download-actual">
                    <h2><?php esc_html_e('Downloading File', 'print-my-blog');?></h2>
                    <p><?php esc_html_e('You have x credits left on your account. They will renew on x', 'print-my-blog');
                    ?></p>
                    <p><a href="https://wordpress.org/support/plugin/print-my-blog/reviews/#new-post"><?php esc_html_e('Please leave a review', 'print-my-blog');?></a></p>
                </div>
            </div>
        </div>
    </div>
<?php
}
pmb_render_template('partials/project_footer.php');

