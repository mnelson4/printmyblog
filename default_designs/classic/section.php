<article <?php post_class('pmb-section'); ?> id="<?php the_permalink(); ?>">

    <header class="entry-header has-text-align-center<?php echo esc_attr( $entry_header_classes ); ?>">

        <div class="entry-header-inner section-inner medium">
            <?php the_title( '<h1 class="entry-title">', '</h1>' );?>

            <div class="entry-categories">
                <span class="screen-reader-text"><?php _e( 'Categories', 'print-my-blog' ); ?></span>
                <div class="entry-categories-inner">
			        <?php the_category( ' ' ); ?>
                </div><!-- .entry-categories-inner -->
            </div><!-- .entry-categories -->
            <div class="entry-meta">
                <span class="posted-on pmb-post-meta">
                    <?php the_date();?>
                </span>
            </div>
        </div><!-- .entry-header-inner -->
    </header><!-- .entry-header -->

    <div class="post-inner">
        <?php
        if ( has_post_thumbnail() ) {
            the_post_thumbnail('large');
        }
        ?>
        <div class="entry-content post-content">
            <?php the_content();?>
        </div><!-- .entry-content -->

    </div><!-- .post-inner -->
</article>
