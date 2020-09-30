<div class="<?php pmb_section_wrapper_class();?>" id="<?php echo esc_attr($post->post_name);?>-wrapper">

    <article <?php pmb_section_class(); ?> id="<?php the_permalink(); ?>">

        <header class="entry-header has-text-align-center<?php echo esc_attr( $entry_header_classes ); ?>">

            <div class="entry-header-inner section-inner medium">
                <?php the_title( '<h1 class="entry-title">', '</h1>' );?>
            </div><!-- .entry-header-inner -->
        </header><!-- .entry-header -->

        <?php pmb_include_design_template( 'partials/content.php' );?>
    </article>
<?php // div automatically closed