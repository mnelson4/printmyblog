<?php
/**
 * @var \PrintMyBlog\orm\entities\Project $pmb_project
 * @var PrintMyBlog\orm\entities\Design $pmb_design
 */
?>
<div <?php pmb_section_wrapper_class(); ?> <?php pmb_section_wrapper_id(); ?>>
    <article <?php pmb_section_class(); ?> <?php pmb_section_id(); ?>>
        <header class="entry-header has-text-align-center">

            <div class="entry-header-inner section-inner medium">
                <?php
                if (pmb_design_uses('title', true)) {
                    pmb_the_title();
                }
                ?>
                <div class="entry-meta">
                    <?php
                    if (pmb_design_uses('id', false)) {
                        ?>
                        <span><?php
                            // translators: %s: ID
                            echo esc_html(sprintf(__('ID:%s', 'print-my-blog'), get_the_ID()));
                            ?></span>
                        <?php
                    }
                    if (pmb_design_uses('author', false)) {
                        ?>
                        <span><?php
                            // translators: %s: author name
                            echo sprintf(__('By %s', 'print-my-blog'), get_the_author());
                            ?></span>
                        <?php
                    }
                    if (pmb_design_uses('published_date', false)) {
                        ?>
                        <span class="posted-on pmb-post-meta">
                        <?php echo get_the_date(); ?>
                    </span>
                        <?php
                    }
                    if (pmb_design_uses('categories', false)) {
                        echo wp_strip_all_tags(get_the_category_list(','));
                    }

                    if (pmb_design_uses('url', false)) {
                        ?>
                        <div><span class="pmb-url"><a class="pmb-leave-link" href="<?php echo esc_url(get_permalink()); ?>"><?php echo get_permalink(); ?></a></span>
                        </div>
                        <?php
                    } ?>
                </div>
            </div><!-- .entry-header-inner -->
        </header><!-- .entry-header -->
        <?php
        if (pmb_design_uses('featured_image', true) && has_post_thumbnail()) {
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
        if (pmb_design_uses('excerpt', false)) {
            ?>
            <div class="excerpt"><?php the_excerpt(); ?></div>
            <?php
        }
        ?>
        <?php
        if (pmb_design_uses('meta', false)) {
            pmb_the_meta();
        }

        if (pmb_design_uses('content', true)) {
            pmb_include_design_template('partials/content');
        }
        ?>
    </article>
<?php // don't close wrapping div, we'll close it elsewhere