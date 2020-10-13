<?php

use PrintMyBlog\orm\entities\Project;
/**
 * @var Project $project
 * @var bool $show_back
 */
if($project instanceof Project){
	$project_url = add_query_arg(
		[
			'ID' =>  $project->getWpPost()->ID,
			'action' => 'edit',
			'subaction' => 'main'
		],
		admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
	);
} else {
    $project_url = '';
}
pmb_render_template(
        'partials/breadcrumb.php',
    [
            'project' => $project,
            'project_url' => $project_url
    ]
);
?>
<div class="wrap nosubsub">
    <h1><?php echo $page_title;?></h1>
    <div class="pmb-nav">
        <?php if($show_back){?>
            <span><a href="<?php echo esc_attr($project_url);?>">&laquo;<?php esc_html_e('Back', 'print-my-blog');?></a></span>
        <?php } ?>
    </div>
    <?php //div will be closed by project_footer.phps

