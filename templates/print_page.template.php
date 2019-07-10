<?php
/**
 * The header for our printing
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class('pmb-print-page'); ?>>
<div class="pmb-waiting-message-fullpage pmb-extra-content">
    <div class="pmb-waiting-message-outer-container">
        <div class="pmb-window-buttons">
            <span class="pmb-cancel-button">
                <a href="javascript:history.back();">❌
                <?php esc_html_e('Cancel', 'print-my-blog');?>
                </a>
            </span>
        </div>
        <div class="pmb-help">
            <span class="pmb-help-ask"><?php printf(
                    __('What do you think? %1$s', 'print-my-blog'),
                    '<a id="pmb-help-love" href="javascript:pmb_help_show(\'pmb-help-love-text\');" title="'
                    . __('Love it (shows feedback options)', 'print-my-blog')
                    . '">😍</a> <a id="pmb-help-happy" href="javascript:pmb_help_show(\'pmb-help-happy-text\');")" title="'
                    . __('Like it (shows feedback options)', 'print-my-blog')
                    . '">😃</a> <a id="pmb-help-sad" href="javascript:pmb_help_show(\'pmb-help-sad-text\');" title="'
                    . __('Don’t like something (shows feedback options)', 'print-my-blog')
                    . '")>☹️</a>'
                );?>
            </span>
            <span class="pmb-help-love-text" style="display:none"><?php printf(
                    __('Great! %1$sFYI you can sponsor%2$s or %3$sreview%2$s it.', 'print-my-blog'),
                    '<a href="https://opencollective.com/print-my-blog" target="_blank" title="' . __('Sponsor development (opens in new tab)', 'print-my-blog') . '">',
                    '</a>',
                    '<a href="https://wordpress.org/support/plugin/print-my-blog/reviews/?filter=5" target="_blank" title="' . __('Plugin Reviews (opens in new tab)', 'print-my-blog') . '">'
                );?></span>
            <span class="pmb-help-happy-text" style="display:none"><?php printf(
                    __('Nice! %1$sPlease leave a review%2$s.', 'print-my-blog'),
                    '<a href="https://wordpress.org/support/plugin/print-my-blog/reviews/?filter=5" target="_blank" title="' . __('Plugin Reviews (opens in new tab)', 'print-my-blog') . '">',
                    '</a>'
            );?></span>
            <span class="pmb-help-sad-text" style="display:none"><?php printf(
                    __('That’s disappointing. %1$sPlease tell us how to improve.%2$s', 'print-my-blog'),
                    '<a href="https://wordpress.org/support/plugin/print-my-blog/" target="_blank" title="' . __('Plugin support forum (opens in new tab)', 'print-my-blog') . '">',
                    '</a>'
                    );?></span>
        </div>
        <div class="pmb-waiting-area">
            <h1 id='pmb-in-progress-h1' class="pmb-waiting-h1"><?php _e('Initializing','print-my-blog' );?></h1>
        </div>
        <div class="pmb-print-ready" style="visibility:hidden">
            <input type="submit" onclick="window.print()" value="<?php esc_attr_e('Print','print-my-blog' );?>"/>
            <a
                    target="_blank"
                    href="https://wordpress.org/plugins/print-my-blog/#how%20do%20i%20create%20a%20pdf%20using%20print%20my%20blog%3F"
                    title="<?php esc_html_e('opens in new tab','print-my-blog' );?>"
            >
                <?php esc_html_e('How do I print to PDF?','print-my-blog' );?>
            </a>
        </div>
        <div class="pmb-posts-placeholder pmb-extra-content">
            <div class="pmb-spinner-container">
                <div class="pmb-spinner"></div>
            </div>
            <p class="pmb-status"><span class="pmb-posts-count"></span></p>
        </div>
    </div>
</div>

<div class="pmb-posts">
    <div class="pmb-posts-header">
        <h1 class="site-title"><?php echo $pmb_site_name;?></h1>
        <p class="site-description"><?php echo $pmb_site_description;?></p>
        <?php
        if( $pmb_printout_meta) {
            // If they specified an after date, show it
            if($pmb_after_date && $pmb_before_date){
                $date_range_string = sprintf('between %1$s and %2$s,', $pmb_after_date, $pmb_before_date);
            } elseif( $pmb_after_date && ! $pmb_before_date){
                $date_range_string = sprintf(esc_html__('after %1$s,', 'print-my-blog'), $pmb_after_date);
            } elseif( ! $pmb_after_date && $pmb_before_date){
                $date_range_string = sprintf(esc_html__('before %1$s,', 'print-my-blog'), $pmb_before_date);
            } else {
                $date_range_string = '';
            }

            // Figure out taxonomy filters used and how to display them.
            $taxonomy_filters_strings = array();
            foreach($pmb_taxonomy_filters as $taxonomy_filter){
                $taxonomy = $taxonomy_filter['taxonomy'];
                $terms = $taxonomy_filter['terms'];
                $taxonomy_filters_strings[] = sprintf(
                    esc_html__('%1$s: %2$s', 'print-my-blog'),
                    count($terms) > 1 ? $taxonomy->labels->name : $taxonomy->labels->singular_name,
                    implode(', ', $terms)
                );

            }
            $taxonomy_filters_string = count($taxonomy_filters_strings) ?
                sprintf(
                        // @translators: $s, the categories and terms used to filter this print-out,
                        //  eg "Category: WordPress, Blogging; Term: Computers, Headphones".
                    esc_html__('with %s,', 'print-my-blog'),
                    implode('; ', $taxonomy_filters_strings)
                ) :
                '';
            $content_description =  $pmb_post_type . ' ' . $date_range_string . ' ' . $taxonomy_filters_string;

            ?><p class="pmb-printout-meta">
            <?php
            printf(
                esc_html__('%1$s from %2$s.', 'print-my-blog'),
                $content_description,
                $pmb_site_url
            );?> <?php
            printf(
                esc_html__('Printed on %1$s using %2$sPrint My Blog%3$s', 'print-my-blog'),
                date_i18n(get_option('date_format')),
                '<a href="https://wordpress.org/plugins/print-my-blog/">',
                '</a>'
            );
            ?></p>
        <?php
        }
        ?>
    </div>
    <div class="pmb-posts-body">

    </div>
</div>
<?php wp_footer();?>

</body>
</html>