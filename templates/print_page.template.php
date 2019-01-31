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
        <div class="pmb-waiting-area">
            <h1 id='pmb-in-progress-h1' class="pmb-waiting-h1"><?php _e('Initializing','print-my-blog' );?></h1>
        </div>
        <div class="pmb-print-ready" style="visibility:hidden">
            <input type="submit" onclick="window.print()" value="<?php esc_attr_e('Print','print-my-blog' );?>"/>
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
        <p><?php printf(
                esc_html__('Printout of %1$s on %2$s using %3$sPrint My Blog%4$s','print-my-blog' ),
                $pmb_site_url,
                date_i18n( get_option( 'date_format' )),
                '<a href="https://wordpress.org/plugins/print-my-blog/">',
                '</a>'
            );?>
        </p>
    </div>
    <div class="pmb-posts-body">

    </div>
</div>

</body>