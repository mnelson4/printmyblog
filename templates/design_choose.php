<?php
/**
 * @var $designs \PrintMyBlog\orm\entities\Design[]
 * @var $chosen_design \PrintMyBlog\orm\entities\Design
 * @var $project \PrintMyBlog\orm\entities\Project
 * @var $format \PrintMyBlog\entities\FileFormat
 * @var $steps_to_urls array
 * @var $current_step string
 */

use PrintMyBlog\controllers\Admin;
use \PrintMyBlog\entities\DesignTemplate;

pmb_render_template(
	'partials/project_header.php',
	[
		'project' => $project,
		'page_title' => sprintf(
		        __('Choose %s Design', 'print-my-blog'),
		        $format->title()
        ),
		'current_step' => $current_step,
        'steps_to_urls' => $steps_to_urls
	]
);
?>
<div class="pmb-design-browser">


<?php
// List all designs
foreach($designs as $design){
	$active = $design->getWpPost()->ID === $chosen_design->getWpPost()->ID;
	?>
	<div class="pmb-design <?php echo $active ? 'pmb-active' : ''?>">
       <div class="pmb-design-details-opener" data-design-slug="<?php echo esc_attr($design->getWpPost()->post_name);?>">
                <div class="pmb-design-screenshot">
                <?php echo pmb_design_preview($design);?>
                </div>
                <span class="more-details"><?php esc_html_e('Design Details', 'print-my-blog');?></span>
            </div>
		<div class="pmb-design-id-container">
            <div class="pmb-actions pmb-design-actions">
                <form method="POST" action="" id="pmb-design-form-<?php echo esc_attr($design->getWpPost()->post_name);?>">
                    <input type="hidden" name="design" value="<?php echo esc_attr($design->getWpPost()->ID);?>">
                    <button class="button button-primary"><?php esc_html_e('Use This Design', 'print-my-blog');?></button>
                </form>
            </div>
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
	    </div>
        <div class="pmb-details-content-container" id="pmb-design-details-<?php echo esc_attr($design->getWpPost()->post_name);?>">
            <div class="pmb-details-content">
                <?php echo pmb_design_preview($design);?>
                <h1><?php echo $design->getWpPost()->post_title;?></h1>
                <table class="pmb-details-support-table">
                    <tr>
                        <th><?php esc_html_e('Template', 'print-my-blog');?></th>
                        <td><?php echo $design->getDesignTemplate()->getTitle();?></td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e('Supports', 'print-my-blog');?></th>
                        <td>
                            <ul class="pmb-list"><?php
		                        if($design->getDesignTemplate()->supports(DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER)){
			                        echo '<li>' . __('front matter', 'print-my-blog') . '</li>';
		                        }
		                        echo '<li>';
		                        printf(
			                        _n(
				                        '%1$s layer of nesting',
				                        '%1$s layers of nesting',
				                        $design->getDesignTemplate()->getLevels(),
				                        'print-my-blog'
			                        ),
			                        $design->getDesignTemplate()->getLevels()
		                        );
		                        echo ' ';
		                        if($design->getDesignTemplate()->getLevels() > 0 ){
			                        for($i=0; $i < $design->getDesignTemplate()->getLevels(); $i++){
				                        echo '(' . sprintf(
						                        __('each %1$s can put in a %2$s', 'print-my-blog'),
						                        $design->getDesignTemplate()->divisionLabelSingular($i),
						                        $design->getDesignTemplate()->divisionLabelSingular($i+1)
					                        ) . ')';
			                        }
		                        } else {
			                        echo __('(no parts)', 'print-my-blog');
		                        }
		                        echo '</li>';
		                        if($design->getDesignTemplate()->supports(DesignTemplate::IMPLIED_DIVISION_FRONT_MATTER)){
			                        echo '<li>' . __('back matter', 'print-my-blog') . '</li>';
		                        }
		                        ?>
                            </ul>
                        </td>
                    </tr>
                </table>
                <?php echo $design->getWpPost()->post_content;?>
            </div>
        </div>
	</div>
	<?php
}
?>
</div>
<?php pmb_render_template('partials/project_footer.php');