<?php
/**
 * @var $formats \PrintMyBlog\entities\FileFormat[]
 * @var $project \PrintMyBlog\orm\entities\Project
 */
?>
<p><?php esc_html_e('Ready to see what your project looks like? There is a link below for each of your project’s formats.', 'print-my-blog');?></p>
<?php
foreach($formats as $format){
	$generate_link = add_query_arg(
		[
			PMB_PRINTPAGE_SLUG => 3,
			'project' => $project->getWpPost()->ID,
			'format' => $format->slug()
		],
		site_url()
	);
	?>
	<br><br>
	<a href="<?php echo esc_attr($generate_link);?>" class="button button-primary"><?php echo $format->title();?></a>
	<?php
}
?>

