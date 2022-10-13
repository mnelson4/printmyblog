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
<div class="haller-repeat-header-wrap" id="haller-repeat-header-wrap-right">
    <div id="haller-repeat-header-right" class="haller-repeat-header">
        <div id="haller-repeat-heder-date">
            <span>
                <?php $pmb_project->echoSetting('date');?>
            </span>
        </div>
        <div class="haller-repeat-header-title" >
            <?php echo $pmb_design->getSetting('publication_title'); ?>
        </div>
        <div id="haller-repeat-header-issue-page">
            <span>
                <?php printf(__('Issue %s, Page %s', 'print-my-blog'), $pmb_project->getSetting('issue'), '<span class="pmb-page-number"></span>');?>
            </span>
        </div>
    </div>
    <div class="haller-repeat-header-subtitle">
        <?php echo $pmb_design->getSetting('publication_subtitle'); ?>
    </div>
</div>
<div class="haller-repeat-header-wrap" id="haller-repeat-header-wrap-left">
    <div id="haller-repeat-header-left" class="haller-repeat-header">
        <div id="haller-repeat-header-issue-page">
            <span>
                <?php printf(__('Issue %s, Page %s', 'print-my-blog'), $pmb_project->getSetting('issue'), '<span class="pmb-page-number"></span>');?>
            </span>
        </div>
        <div class="haller-repeat-header-title" >
            <?php echo $pmb_design->getSetting('publication_title'); ?>
        </div>
        <div id="haller-repeat-heder-date">
            <span>
                <?php $pmb_project->echoSetting('date');?>
            </span>
        </div>
    </div>
    <div class="haller-repeat-header-subtitle">
        <?php echo $pmb_design->getSetting('publication_subtitle'); ?>
    </div>
</div>