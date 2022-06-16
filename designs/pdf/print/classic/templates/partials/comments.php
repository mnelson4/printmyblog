<div id="comments" class="comments-area">

    <?php
    // Doesn't work because comments are built within WP_Query, which assumes you never want comments
    // unless its on a singular post template. We'll need to do our own comment fetching.
    if (have_comments()) {
        ?>
        <h2 class="comments-title">
            <?php
            $comments_number = get_comments_number();
            if ('1' === $comments_number) {
                /* translators: %s: Post title. */
                printf(_x('One Reply to &ldquo;%s&rdquo;', 'comments title', 'print-my-blog'), get_the_title());
            } else {
                printf(
                /* translators: 1: Number of comments, 2: Post title. */
                    _nx(
                        '%1$s Reply to &ldquo;%2$s&rdquo;',
                        '%1$s Replies to &ldquo;%2$s&rdquo;',
                        $comments_number,
                        'comments title',
                        'print-my-blog'
                    ),
                    number_format_i18n($comments_number),
                    get_the_title()
                );
            }
            ?>
        </h2>

        <ol class="comment-list">
            <?php
            wp_list_comments(
                array(
                    'avatar_size' => 100,
                    'style' => 'ol',
                    'short_ping' => true,
                    'reply_text' => ''
                )
            );
            ?>
        </ol>
        <?php

    } else {
        echo "----------------------------no comments";
    } // Check for have_comments().
    ?>

</div><!-- #comments -->
