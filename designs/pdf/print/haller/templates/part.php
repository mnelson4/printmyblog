<div <?php pmb_section_wrapper_class(); ?> <?php pmb_section_wrapper_id(); ?>>

    <article <?php pmb_section_class(); ?> <?php pmb_section_id(); ?>>

        <header class="entry-header has-text-align-center">
            <?php
            if (pmb_design_uses('featured_image', true) && has_post_thumbnail()) {
            ?>
            <figure class="post-thumbnail pmb-wide">
                <?php the_post_thumbnail('full', ['class' => 'alignnone pmb-featured-image','loading' => 'eager']); ?>
                <?php if (wp_get_attachment_caption(get_post_thumbnail_id())) : ?>
                    <figcaption
                            class="wp-caption-text"><?php echo wp_kses_post(wp_get_attachment_caption(get_post_thumbnail_id())); ?></figcaption>
                <?php endif; ?>
            </figure>
            <?php
            }
            ?>
            <div class="entry-header-inner section-inner medium">
                <?php pmb_the_title(); ?>
            </div><!-- .entry-header-inner -->
        </header><!-- .entry-header -->

        <?php pmb_include_design_template('partials/content'); ?>
    </article>
<?php // div automatically closed
