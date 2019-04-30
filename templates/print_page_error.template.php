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
<div class="pmb-waiting-area">
    <h1 class="pmb-waiting-h1"><?php esc_html_e('Sorry, we could not print the WordPress Blog.','print-my-blog' );?></h1>
    <p><?php printf(esc_html__('Problem: %s','event_espresso' ), $pmb_wp_error->get_error_message());?></p>
    <p><?php printf(esc_html__('Code: %s','print-my-blog' ),$pmb_wp_error->get_error_code());?></p>
    <p><?php
        if(isset($_GET['site']) && $_GET['site'] !== site_url()){
            printf(esc_html__('The site URL you provided was "%1$s". Are you sure that URL is correct and that it\'s a WordPress site?','event_espresso' ), esc_url($_GET['site']));
        }
        ?></p>
    <p><?php esc_html_e('Are you sure the blog hasn\'t deactivated the REST API?', 'print-my-blog');?></p>
    <p><?php
        printf(
            esc_html__('If you are still having problems, please report it to the %1$sPrint My Blog%2$s support forum.','print-my-blog'),
            '<a href="https://wordpress.org/support/plugin/print-my-blog">',
            '</a>'
        );
        ?></p>
</div>
</body>