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

<body <?php body_class(); ?>>
<div class="pmb-waiting-message-fullpage">
    <div class="pmb-waiting-message-outer-container">
        <div class="pmb-waiting-area">
            <h1 class="pmb-waiting-h1"><?php _e('We are preparing your blog&#8217;s content for printing. Please wait...','printmyblog' );?></h1>
            <div class="pmb-spinner-container">
                <div class="pmb-spinner"></div>
            </div>
            <p class="pmb-status"><?php esc_html_e('Loading posts','printmyblog' );?></p>
        </div>
        <div class="pmb-print-ready">
            <h1 class="pmb-waiting-h1"><?php _e('Ready to Print!','printmyblog' );?></h1>
                <button onclick="window.print()"><?php esc_html_e('Print Now','printmyblog' );?></button>
            <button onclick="pmg_print_preview()"><?php esc_html_e('Print Preview','printmyblog' );?></button>
            <div class="print-preview-instructions">
                <p><?php esc_html_e('Your browser may support making a print preview by doing the following:','printmyblog' );?></p>
                <ol>
                    <li><?php esc_html_e('Press the "Alt" Key on your Keyboard','printmyblog' );?></li>
                    <li><?php esc_html_e('Click "File"','printmyblog' );?></li>
                    <li><?php esc_html_e('Click "Print Preview"','printmyblog' );?></li>
                </ol>
            </div>
        </div>
    </div>
</div>
<div class="pmb-posts">
    <h1 class="site-title"><?php  echo get_bloginfo('name');?></h1>
    <p class="site-description"><?php echo get_bloginfo('description');?></p>
    <p><?php printf(
            esc_html__('Printout of %1$s, generated on %2$s using "Print My Blog" plugin.','printmyblog' ),
            get_bloginfo('url'),
            date_i18n( get_option( 'date_format' ))
            );?>
    </p>
</div>

</body>