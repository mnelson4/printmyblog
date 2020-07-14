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
            <div class="pmb-help">
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
        </div>
        <div class="pmb-posts-placeholder pmb-extra-content">
            <div class="pmb-spinner-container">
                <div class="pmb-spinner"></div>
            </div>
            <p class="pmb-status"><span class="pmb-posts-count"></span></p>
        </div>
    </div>
</div>

<div class="pmb-posts site">
    <div class="pmb-posts-header">
        <div class="pmb-posts-body">
        </div>
    </div>
    <?php wp_footer(); ?>
</body>
</html>