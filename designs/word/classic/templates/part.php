<div <?php pmb_section_wrapper_class();?> <?php pmb_section_wrapper_id();?>>
    <?php
    // page break unless this is the first section
    if ($post->pmb_section->getSectionOrder() != 1){
    ?>
    <br clear=all style='mso-special-character:line-break;page-break-before:always'>
    <?php
    }
    ?>
    <article <?php pmb_section_class(); ?> <?php pmb_section_id(); ?>>

        <header class="entry-header has-text-align-center">

            <div class="entry-header-inner section-inner medium">
                <?php pmb_the_title();?>
            </div><!-- .entry-header-inner -->
        </header><!-- .entry-header -->

        <?php pmb_include_design_template( 'partials/content' );?>
    </article>
<?php // div automatically closed