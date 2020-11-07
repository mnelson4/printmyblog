<?php
/**
 * @var $generations \PrintMyBlog\entities\ProjectGeneration[]
 * @var $project \PrintMyBlog\orm\entities\Project
 */
pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => __('Generate Project', 'print-my-blog'),
		'current_step' => $current_step
	]
);
?>
<p><?php esc_html_e('Ready to see what your project looks like? There is a link below for each of your project’s formats.', 'print-my-blog');?></p>
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
	)
	?>
	<h2><?php echo $generation->getFormat()->title();?></h2>
    <?php
    if($generation->isGenerated()){
        if($generation->isDirty()){
            ?>
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
            <br/>
            <form class="pmb-inline-form" method="POST" action="<?php echo esc_attr($generate_link);?>">
                <button class="button button-primary"><?php esc_html_e('Regenerate', 'print-my-blog');?></button>
            </form>
            <a href="<?php echo esc_attr($generation->getGeneratedIntermediaryFileUrl());?>" class="button"><?php esc_html_e('Download Out-of-date File', 'print-my-blog');?></a>
            <?php
        } else {
            ?>
            <b>
                <?php printf(
                    esc_html__('This file was already generated %s', 'print-my-blog'),
		            date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $generation->generatedTimeSql())
                );?>
            </b>
            <br/>
            <form class="pmb-inline-form" method="POST" action="<?php echo esc_attr($generate_link);?>">
                <button class="button"><?php esc_html_e('Regenerate Anyway', 'print-my-blog');?></button>
            </form>
            <a href="<?php echo esc_attr($generation->getGeneratedIntermediaryFileUrl());?>" class="button button-primary"><?php esc_html_e('Download', 'print-my-blog');?></a>
            <?php
        }
    } else {
    ?>
        <form class="pmb-inline-form" method="POST" action="<?php echo esc_attr($generate_link);?>">
            <button class="button button-primary"><?php esc_html_e('Generate', 'print-my-blog');?></button>
        </form>
	<?php
    }
}
pmb_render_template('partials/project_footer.php');

