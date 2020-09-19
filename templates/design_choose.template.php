<?php
/**
 * @var $designs \PrintMyBlog\orm\entities\Design[]
 * @var $chosen_design \PrintMyBlog\orm\entities\Design
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $format \PrintMyBlog\entities\FileFormat
 */

use PrintMyBlog\controllers\Admin;

?>
<div class="pmb-design-browser">


<?php
// List all designs
foreach($designs as $design){
	$active = $design->getWpPost()->ID === $chosen_design->getWpPost()->ID;
	if($active){
		$link_url = add_query_arg(
			[
				'ID' => $project->getWpPost()->ID,
				'action' => Admin::SLUG_ACTION_EDIT_PROJECT,
				'subaction' => Admin::SLUG_SUBACTION_PROJECT_CUSTOMIZE_DESIGN,
				'format' => $format->slug()
			],
			admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
		);
	} else {
		$link_url = add_query_arg(
			[
				'ID' => $project->getWpPost()->ID,
				'action' => Admin::SLUG_ACTION_EDIT_PROJECT,
				'subaction' => Admin::SLUG_SUBACTION_PROJECT_CHANGE_DESIGN,
				'format' => $format->slug(),
				'design' => $design->getWpPost()->ID
			],
			admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
		);
	}
	?>
	<div class="pmb-design <?php echo $active ? 'pmb-active' : ''?>">
		<div class="pmb-design-screenshot">

		</div>
		<div class="pmb-design-id-container">
			<h2>
				<?php
				if($active){
					printf(
						'<span>Active:</span> %s',
						$design->getWpPost()->post_title
					);
				} else {
					echo $design->getWpPost()->post_title;
				}
				?>
			</h2>
			<div class="pmb-design-actions">
				<?php
				if($active){
					?>
					<a class="button button-primary" href="<?php echo $link_url;?>"><?php esc_html_e('Customize Design', 'print-my-blog');?></a>
					<?php
				} else {
					?>
					<form method="POST" action="<?php echo $link_url;?>">
						<button class="button button-secondary" href=""><?php esc_html_e('Use This Design', 'print-my-blog');?></button>
					</form>
					<?php
				}
				?>
			</div>
		</div>
	</div>
	<?php
}
?>
</div>