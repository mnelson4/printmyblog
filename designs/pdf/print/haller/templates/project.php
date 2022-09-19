<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
 *
 */

pmb_include_design_template('partials/html_header');
?>

<body class="<?php
//phpcs:ignore -- get_body_class does the escaping
echo str_replace('has-sidebar', '', implode(' ', get_body_class('pmb-print-page pmb-pro-print-page pmb-pro-print-pdf')));
?>">
<?php do_action('pmb_pro_print_window'); ?>
<div class="pmb-posts pmb-project-content site">
<div id="haller-repeat-header">
    <div class="haller-centered">
        <div class="haller-centered-inner">
            <?php $pmb_project->echoSetting('issue');?>
            <?php $pmb_project->echoSetting('date');?>
        </div>
    </div>
    <div id="haller-repeat-header-title">
        <?php echo $pmb_design->getSetting('publication_title'); ?>
    </div>
    <div>Page <span class="pmb-page-number"></span></div>
</div>