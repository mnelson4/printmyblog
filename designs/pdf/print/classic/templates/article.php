<?php
/**
 * @var \PrintMyBlog\orm\entities\Project $project
 */
?>
<div <?php pmb_section_wrapper_class();?> <?php pmb_section_wrapper_id();?>>
<article <?php pmb_section_class(); ?> <?php pmb_section_id(); ?>>
    <?php $post_content = $project->getSetting('post_content');?>
    <header class="entry-header has-text-align-center<?php echo esc_attr( $entry_header_classes ); ?>">

        <div class="entry-header-inner section-inner medium">
            <?php if(in_array('title', $post_content))pmb_the_title();?>
            <?php
            if(in_array('id',$post_content)){
                ?>
                <span><?php echo $project->getWpPost()->ID;?></span>
                <?php
            }
            if(in_array('author',$post_content))the_author();

            ?>

            <div class="entry-meta">
                <span class="posted-on pmb-post-meta">
                    <?php if(in_array('published_date', $post_content))the_date();?>
                </span>
                <?php if(in_array('categories'))the_category();?>
            </div>
        </div><!-- .entry-header-inner -->
    </header><!-- .entry-header -->
	<?php
	if (in_array('featured_image',$post_content) && has_post_thumbnail() ) {
		the_post_thumbnail('full');
	}
	if(in_array('excerpt',$post_content)){
	    ?>
	    <div class="excerpt"><?php the_excerpt();?></div>
	    <?php
    }
	?>
	<?php
    if(in_array('content',$post_content))pmb_include_design_template( 'partials/content' );
	?>
</article>
<?php if(in_array('comments',$post_content))pmb_include_design_template('paritals/comments');?>
<?php // don't close wrapping div, we'll close it elsewhere