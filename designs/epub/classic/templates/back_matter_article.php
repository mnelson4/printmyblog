<div <?php pmb_section_wrapper_class();?> <?php pmb_section_wrapper_id();?>>
    <article <?php pmb_section_class('pmb-back-matter-article'); ?> <?php pmb_section_id(); ?>>
        <header class="entry-header has-text-align-center">
            <div class="entry-header-inner section-inner medium">
				<?php pmb_the_title();?>
            </div>
        </header>
		<?php pmb_include_design_template( 'partials/content' );?>
    </article>
