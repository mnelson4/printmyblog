<div <?php pmb_section_wrapper_class();?> <?php pmb_section_wrapper_id();?>>
	<article <?php pmb_section_class('pmb-single-column'); ?> <?php pmb_section_id(); ?>>
        <?php if (has_post_thumbnail()) {
        ?>
        <figure class="post-thumbnail">
            <?php the_post_thumbnail('full', ['class' => 'alignnone pmb-featured-image','loading' => 'eager']); ?>
            <?php if (wp_get_attachment_caption(get_post_thumbnail_id())) : ?>
                <figcaption
                        class="wp-caption-text"><?php echo wp_kses_post(wp_get_attachment_caption(get_post_thumbnail_id())); ?></figcaption>
            <?php endif; ?>
        </figure>
        <?php
        }
        ?>
        <header class="entry-header has-text-align-center">
			<div class="entry-header-inner section-inner medium">
				<?php pmb_the_title();?>
			</div>
		</header>
		<?php pmb_include_design_template( 'partials/content' );?>
	</article>
