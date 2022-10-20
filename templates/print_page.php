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
if(apply_filters('pmb-print-page-treat-as-single', true)){
    $wp_query->is_home = false;$wp_query->is_single = true;
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <meta name="robots" content="noindex,nofollow">

    <?php wp_head(); ?>
</head>

<body class="<?php echo str_replace('has-sidebar', '', implode(' ',get_body_class('pmb-print-page pmb-format-' . $pmb_format))); ?>">
<!-- Print My Blog Version <?php echo PMB_VERSION;?>-->
<div class="pmb-waiting-message-fullpage pmb-extra-content">
    <div class="pmb-waiting-message-outer-container">
        <div class="pmb-window-buttons pmb-top-left pmb-small-instructions">
            <span class="pmb-loading-content">
                <a href="javascript:history.back();">❌
                    <?php esc_html_e('Cancel', 'print-my-blog'); ?>
                </a>
            </span>
            <span class="pmb-print-ready">
                <a href="javascript:history.back();">✅
                    <?php esc_html_e('Return', 'print-my-blog'); ?>
                </a>
            </span>
        </div>
        <?php if(is_user_logged_in()){?>
            <div class="pmb-help pmb-top">
            <span class="pmb-help-ask"><?php printf(
                // translators: 1: a bunch of HTML for emoji buttons
                    __('What do you think? %1$s', 'print-my-blog'),
                    '<a id="pmb-help-love" href="javascript:pmb_help_show(\'pmb-help-happy-text\');" title="'
                    . __('Love it (shows feedback options)', 'print-my-blog')
                    . '">😃</a> <a id="pmb-help-sad" href="javascript:pmb_help_show(\'pmb-help-sad-text\');" title="'
                    . __('Don’t like something (shows feedback options)', 'print-my-blog')
                    . '")>☹️</a>'
                ); ?>
            </span>
                <span class="pmb-help-happy-text" style="display:none"><?php printf(
                    // translators: 1: opening link tag, 2: closing link tag
                        __('Nice! %1$sPlease leave a review%2$s.', 'print-my-blog'),
                        '<a href="https://wordpress.org/support/plugin/print-my-blog/reviews/" target="_blank" title="' . __('Plugin Reviews (opens in new tab)', 'print-my-blog') . '">',
                        '</a>'
                    ); ?></span>
                <span class="pmb-help-sad-text" style="display:none"><?php printf(
                    // translators: 1: opening link tag, 2: closing link tag.
                        __('That’s disappointing. %1$sPlease tell us how to improve.%2$s', 'print-my-blog'),
                        '<a href="https://wordpress.org/support/plugin/print-my-blog/" target="_blank" title="' . __('Plugin support forum (opens in new tab)', 'print-my-blog') . '">',
                        '</a>'
                    ); ?></span>
            </div>
        <?php }?>
        <div class="pmb-waiting-area">
            <h1 id='pmb-in-progress-h1' class="pmb-waiting-h1"><?php _e('Initializing', 'print-my-blog'); ?></h1>
        </div>
        <div class="pmb-print-ready pmb-print-instructions">
            <?php
            if ($pmb_format === 'ebook') {
                ?>
                <p>
                    <?php esc_html_e('You may now use dotEPUB to create the eBook.', 'print-my-blog'); ?>
                    <a href="https://wordpress.org/plugins/print-my-blog/#%0Ahow%20do%20i%20create%20an%20ebook%20using%20print%20my%20blog%3F%0A" target="_blank"><?php esc_html_e('How?', 'print-my-blog'); ?></a>
                </p>
                <?php
            } else if ($pmb_format === 'pdf') {
                if ($pmb_browser === 'firefox') {
                    ?>
                    <p> <?php esc_html_e('You may now create the PDF using a browser extension.', 'print-my-blog'); ?></p>
                    <?php
                } elseif(in_array($pmb_browser, ['chrome','desktop_safari'])) {
                    ?>
                    <input disabled="disabled" class="pmb-print-page-print-button" type="submit" onclick="window.print()"
                           value="<?php esc_attr_e('Print to PDF', 'print-my-blog'); ?>"/><?php
                } else {
                    ?>
                    <p><?php esc_html_e('Use your browser to print to PDF.', 'print-my-blog'); ?></p>
                    <?php
                }
                if(is_user_logged_in()) {
                    ?>
                    <a
                            target="_blank"
                            href="https://wordpress.org/plugins/print-my-blog/#how%20do%20i%20create%20a%20pdf%20using%20quick%20print%3F"
                            title="<?php esc_html_e('opens in new tab', 'print-my-blog'); ?>"
                    >
                        <?php esc_html_e('How?', 'print-my-blog'); ?>
                    </a>
                    <?php
                } else {
                    if ($pmb_browser === 'firefox') {
                        ?>
                        <details class="pmb-details">
                            <summary class="pmb-reveal-options pmb-inline"><?php esc_html_e('How?', 'print-my-blog'); ?>
                            </summary>
                            <div class="pmb-reveal-details">
                                <ol>
                                    <li><a href="https://addons.mozilla.org/en-US/firefox/addon/print-to-pdf-document/" target="_blank" title="<?php esc_html_e('opens in new tab', 'print-my-blog'); ?>"><?php esc_html_e('Download Print to PDF Browser Extension', 'print-my-blog'); ?></a></li>
                                    <li><?php esc_html_e('Click the extension’s button.', 'print-my-blog'); ?></li>
                                </ol>
                            </div>
                        </details>
                        <?php
                    } elseif($pmb_browser === 'chrome') {
                        ?>
                        <details class="pmb-details">
                            <summary class="pmb-reveal-options pmb-inline"><?php esc_html_e('How?', 'print-my-blog'); ?>
                            </summary>
                            <div class="pmb-reveal-details">
                                <p><?php esc_html_e('After clicking "Print to PDF", set the "Destination" to "Save as PDF."', 'print-my-blog'); ?></p>
                            </div>
                        </details>
                        <?php
                    } elseif($pmb_browser === 'desktop_safari') {
                        ?>
            <details class="pmb-details">
                <summary
                        class="pmb-reveal-options pmb-inline"><?php esc_html_e('How on Safari on Desktop', 'print-my-blog'); ?>
                </summary>
                <div class="pmb-reveal-details">
                    <ol>
                        <li><?php esc_html_e('Click "Print to PDF"', 'print-my-blog'); ?></li>
                        <li><?php esc_html_e('Choose to "Save as PDF."', 'print-my-blog'); ?></li>
                    </ol>
                </div>
            </details>
            <?php
                    } elseif( $pmb_browser === 'mobile_safari'){
                        ?>
                        <details class="pmb-details">
                            <summary class="pmb-reveal-options pmb-inline"><?php esc_html_e('How on Mobile Safari', 'print-my-blog'); ?>
                            </summary>
                            <div class="pmb-reveal-details">
                                <ol>
                                    <li>
                                        <?php esc_html_e('Click the share button', 'print-my-blog'); ?>
                                    </li>
                                    <li>
                                        <?php esc_html_e('Click "Options" and choose PDF', 'print-my-blog'); ?>
                                    </li>
                                    <li>
                                        <?php esc_html_e('Save to Files', 'print-my-blog'); ?>
                                    </li>
                                </ol>
                            </div>
                        </details>
                        <?php
                    }
                }
                ?><p class="pmb-help"><?php esc_html_e('Note: Google Chrome and Microsoft Edge are recommended for producing PDFs.', 'print-my-blog');?></_E></_E></_E></p><?php
            } elseif($pmb_format === 'html'){// HTML
                ?>
                <input type="submit" onclick="pmb_copy()" value="<?php esc_attr_e('Copy to Clipboard', 'print-my-blog'); ?>"/>
                <?php
            } else { // default: print
                ?>
                <input type="submit" disabled="disabled" class="pmb-print-page-print-button"  onclick="window.print()" value="<?php esc_attr_e('Print', 'print-my-blog'); ?>"/>
                <div class="pmb-small-instructions"><?php esc_html_e('Use your browser to print.', 'print-my-blog'); ?></div>
                <?php
            }
            ?>
        </div>
        <div class="pmb-posts-placeholder pmb-extra-content">
            <div class="pmb-spinner-container">
                <div class="pmb-spinner"></div>
            </div>
            <p class="pmb-status"><span class="pmb-posts-count"></span></p>
        </div>
    </div>
</div>

<div class="pmb-posts site dotEPUBcontent" id="content">
    <?php
    if ($pmb_format !== 'ebook') {
    // dotEPUB skips the title and description if they're not in the same div.
    // But it's nice for print and PDFs to have that area be in a different stylable div.
    ?>
    <?php
        if (in_array($pmb_format,['print','pdf'] )){
        ?>
    <div class="pmb-preview-note"><?php esc_html_e('Use your browser’s "print preview" for the best preview.', 'print-my-blog'); ?></div>
    <?php } ?>
    <div class="pmb-posts-header">
        <?php
        } else {
        ?>
        <div class="pmb-posts-body">
            <?php
            }
            ?>

            <?php if ($pmb_show_site_title) { ?>
                <h1 class="site-title" id="dotEPUBtitle"><?php echo $pmb_site_name; ?></h1>
            <?php } ?>
            <?php if ($pmb_show_site_tagline) { ?>
                <p class="site-description"><?php echo $pmb_site_description; ?></p>
            <?php } ?>
            <?php
            if ($pmb_show_filters) {
                // If they specified an after date, show it
                if ($pmb_after_date && $pmb_before_date) {
                    if($pmb_after_date === $pmb_before_date){
                        $date_range_string = sprintf('published on %1$s,', $pmb_after_date);
                    } else {
                        $date_range_string = sprintf('published from %1$s to %2$s,', $pmb_after_date, $pmb_before_date);
                    }
                } elseif ($pmb_after_date && !$pmb_before_date) {
                    $date_range_string = sprintf(
                    // translators: 1: date string
                        esc_html__('published on %1$s and later,', 'print-my-blog'),
                        $pmb_after_date
                    );
                } elseif (!$pmb_after_date && $pmb_before_date) {
                    $date_range_string = sprintf(
                    // translators: 1: date string
                        esc_html__('published on %1$s and earlier,', 'print-my-blog'),
                        $pmb_before_date
                    );
                } else {
                    $date_range_string = '';
                }

                // Figure out taxonomy filters used and how to display them.
                $taxonomy_filters_strings = array();
                foreach ($pmb_taxonomy_filters as $taxonomy_filter) {
                    $taxonomy = $taxonomy_filter['taxonomy'];
                    $terms = $taxonomy_filter['terms'];
                    $taxonomy_filters_strings[] = sprintf(
                    // translators: 1 taxonomy name, 2: list of terms that apply to this post
                        esc_html__('%1$s: %2$s', 'print-my-blog'),
                        count($terms) > 1 ? $taxonomy->labels->name : $taxonomy->labels->singular_name,
                        implode(', ', $terms)
                    );

                }
                $filters_string = count($taxonomy_filters_strings) ?
                    sprintf(
                    // @translators: $s, the categories and terms used to filter this print-out,
                    //  eg "Category: WordPress, Blogging; Term: Computers, Headphones".
                        esc_html__('with %s,', 'print-my-blog'),
                        implode('; ', $taxonomy_filters_strings)
                    ) :
                    '';
                if($pmb_author){
                    $filters_string .= ' ' . sprintf(esc_html__('by %s', 'print-my-blog'), $pmb_author->display_name);
                }
                $content_description = $pmb_post_type . ' ' . $date_range_string . ' ' . $filters_string;

                ?>
                <?php
            } else {
                $content_description = '';
            }
            ?>
            <p class="pmb-printout-meta">
                <?php
                if ($pmb_show_site_url && $content_description) {
                    printf(
                    // translators: 1 description of printout, 2: site URL
                        esc_html__('%1$s from %2$s.', 'print-my-blog'),
                        $content_description,
                        $pmb_site_url
                    );
                } elseif ($pmb_show_site_url && ! $content_description) {
                        echo $pmb_site_url;
                } elseif ($content_description) {
                    // if we're not showing the site's URL, but there's a content description to show, by all means...
                    // it should still be shown.
                    printf(
                        _x(
                            '%s.',
                            'Description of what\'s in the printout. Eg the post type, dante range, and filters.',
                            'print-my-blog'),
                        $content_description
                    );
                }
                ?><?php
                //give it some space
                echo ' ';
                if ($pmb_show_date_printed && $pmb_show_credit) {

                    printf(
                    // translators: 1: date, 2: opening link tag, 3: closing link tag
                        esc_html__('Printed on %1$s using %2$sPrint My Blog%3$s', 'print-my-blog'),
                        date_i18n(get_option('date_format')),
                        '<a href="https://wordpress.org/plugins/print-my-blog/">',
                        '</a>'
                    );
                } elseif ($pmb_show_date_printed) {
                    // translators: 1: date
                    printf(
                        esc_html__('Printed on %1$s', 'print-my-blog'),
                        date_i18n(get_option('date_format'))
                    );
                } elseif ($pmb_show_credit) {
                    printf(
                        esc_html__('Printed using %1$sPrint My Blog%2$s', 'print-my-blog'),
                        '<a href="https://wordpress.org/plugins/print-my-blog/">',
                        '</a>'
                    );
                }
                do_action('pmb_print_page_after_printed_on', $pmb_show_date_printed, $pmb_show_credit);
                ?></p>
            <?php
            if ($pmb_format !== 'ebook') {
            // If this is a print copy, we need to close this header div and open the pmb-posts-body div.
            ?>
        </div>
        <div class="pmb-posts-body">
            <?php
            }
            ?>


        </div>
    </div>
    <?php wp_footer(); ?>

</body>
</html>