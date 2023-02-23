<?php
/**
 * The header for
 *
 */
pmb_include_design_template('partials/html_header');
global $pmb_design;
?>

<body class="<?php echo str_replace('has-sidebar', '', implode(' ',get_body_class('pmb-print-page pmb-pro-print-page pmb-pro-word pmb-posts'))); ?>">
<?php do_action('pmb_pro_print_window');?>
<!-- header/footer:
  This element will appears in your main document (unless you save in a separate HTML),
  therefore, we move it off the page (left 50 inches) and relegate its height
  to 1pt by using a table with 1 exact-height row
-->
<div class="pmb-posts pmb-project-content site">
    <div class=Section1><table style='margin-left:50in;'><tr style='height:1pt;mso-height-rule:exactly'>
            <td>
                <div style='mso-element:header' id=h1>
                    <?php echo do_shortcode($pmb_design->getSetting('header'));?>
                </div>
                &nbsp;
            </td>

            <td>
                <div style='mso-element:footer' id=f1>
                    <?php echo do_shortcode($pmb_design->getSetting('footer'));?>
                </div>
                &nbsp;
            </td></tr></table>