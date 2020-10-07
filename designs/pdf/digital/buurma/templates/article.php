<div <?php pmb_section_wrapper_class();?> <?php pmb_section_wrapper_id();?>>
<article <?php pmb_section_class(); ?> <?php pmb_section_id(); ?>>

    <header class="entry-header has-text-align-center">

        <div class="entry-header-inner section-inner medium">
            <?php pmb_the_title();?>
            <div class="entry-meta">
                <span class="posted-on pmb-post-meta">
                    <?php the_date();?>
                </span>
            </div>
        </div><!-- .entry-header-inner -->
    </header><!-- .entry-header -->

	<?php pmb_include_design_template( 'partials/content.php' );?>
</article>
<?php // don't close wrapping div, we'll close it elsewhere