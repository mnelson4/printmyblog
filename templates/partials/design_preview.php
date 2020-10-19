<?php
/**
 * @var \PrintMyBlog\orm\entities\Design $design
 */
?>
<div class="pmb-previews">
	<?php foreach($design->getPreviews() as $preview_data){
	    ?>
        <img class="pmb-preview" src="<?php echo esc_attr($preview_data['url']);?>" alt="<?php echo esc_attr($preview_data['desc']);?>">
    <?php
	}
	?>
</div>
