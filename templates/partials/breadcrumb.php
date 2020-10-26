<?php
use \PrintMyBlog\orm\entities\Project
/**
 * @var Project|true $project
 * @var string|null $project_url
 */
?>
<div class="pmb-top-bar">
	<h1 class="pmb-breadcrumb">
		<span><?php esc_html_e('Print My Blog', 'print-my-blog');?></span>
		<?php
		if($project){
			$projects_url = admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH);
			?>
			<span><a href="<?php echo esc_attr($projects_url);?>"><?php esc_html_e('Pro Projects', 'print-my-blog');?></a></span>
			<?php
		}
		if($project instanceof Project){
		    ?>
            <span><a href="<?php echo esc_attr($project_url);?>"><?php echo $project->getWpPost()->post_title;?></a></span>
            <?php
        }
		?>
	</h1>
</div>