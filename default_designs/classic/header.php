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
    [wp_head]
</head>

<body class="<?php echo str_replace('has-sidebar', '', implode(' ',get_body_class('pmb-print-page'))); ?>">
<!-- Print My Blog Version <?php echo PMB_VERSION;?>-->
<div class="pmb-posts site">
    <div class="pmb-posts-header">
	    <?php if ($pmb_show_site_title) { ?>
            <h1 class="site-title" id="dotEPUBtitle"><?php echo $pmb_site_name; ?></h1>
	    <?php } ?>
	    <?php if ($pmb_show_site_tagline) { ?>
            <p class="site-description"><?php echo $pmb_site_description; ?></p>
	    <?php } ?>
        <p class="pmb-printout-meta">
            <?php
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
		    ?></p>
    </div>
<?php // sections will be added onto here, one at a time, then the footer.php file.