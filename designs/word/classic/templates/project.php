<?php
/**
 * The header for
 *
 */
pmb_include_design_template('partials/html_header');
?>

<body class="<?php echo str_replace('has-sidebar', '', implode(' ',get_body_class('pmb-print-page pmb-pro-print-page pmb-pro-word'))); ?>">
<?php do_action('pmb_pro_print_window');?>
<div class="pmb-posts pmb-project-content site">
