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
<div class="pmg-waiting-message-fullpage">
    <div class="pmg-waiting-message-outer-container">
        <h1 class="pmg-waiting-h1"><?php _e('We are preparing your blog&#8217;s content for printing. Please wait...','event_espresso' );?></h1>
        <div class="pmg-spinner-container">
            <div class="pmg-spinner"></div>
        </div>
        <p class="pmg-status"><?php esc_html_e('Loading posts','event_espresso' );?></p>
    </div>
</div>
<div class="pmg-posts"></div>

</body>