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

        </div>
        <?php if(is_user_logged_in()){?>

        <?php }?>
        <div class="pmb-waiting-area">
            <h1 id='pmb-in-progress-h1' class="pmb-waiting-h1"><?php _e('Generating...', 'print-my-blog'); ?></h1>
            <div class="pmb-spinner-container">
                <div class="pmb-spinner"></div>
            </div>
            <p class="pmb-status"><span class="pmb-posts-count"></span></p>
        </div>
    </div>
</div>
</body>
</html>