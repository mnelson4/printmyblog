<?php
/**
 * @var \PrintMyBlog\orm\entities\Project $pmb_project
 * @var PrintMyBlog\orm\entities\Design $pmb_design
 */
?>
<div <?php pmb_section_wrapper_class();?> <?php pmb_section_wrapper_id();?>>
    <article <?php pmb_section_class(); ?> <?php pmb_section_id(); ?>>
		<?php $post_content = $pmb_design->getSetting('post_content');?>
        <header class="entry-header has-text-align-center">

            <div class="entry-header-inner section-inner medium">
				<?php if(in_array('title', $post_content))pmb_the_title();?>
                <div class="entry-meta">
					<?php
					if(in_array('id',$post_content)){
						?>
                        <span><?php printf(__('ID:%s', 'print-my-blog'), get_the_ID());?></span>
						<?php
					}
					if(in_array('author',$post_content)){
						?>
                        <span><?php printf(__('By %s', 'print-my-blog'), get_the_author());?></span>
						<?php
					}
					if(in_array('published_date', $post_content)){
						?>
                        <span class="posted-on pmb-post-meta">
                        <?php the_date();?>
                    </span>
						<?php
					}
					if(in_array('categories', $post_content)){
						the_category(',');
					}

					if(in_array('url', $post_content)){
						?>
                        <div><span class="pmb-url"><a href="<?php echo esc_attr(get_the_permalink());?>"<?php the_permalink();?></span></div>
						<?php
					}?>
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
<?php // if(in_array('comments',$post_content))pmb_include_design_template('partials/comments');?>
<?php // don't close wrapping div, we'll close it elsewhere