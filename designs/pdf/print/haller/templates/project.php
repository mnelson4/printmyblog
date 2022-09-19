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
<div id="haller-repeat-header-wrap">
    <div id="haller-repeat-header">
        <div>
            <span>
                <?php $pmb_project->echoSetting('date');?>
            </span>
        </div>
        <div id="haller-repeat-header-title" >
            <?php echo $pmb_design->getSetting('publication_title'); ?>
        </div>
        <div>
            <span>
                <?php $pmb_project->echoSetting('issue');?>, Page <span class="pmb-page-number"></span>
            </span>
        </div>
    </div>
    <div id="haller-repeat-header-subtitle">
        <?php echo $pmb_design->getSetting('publication_subtitle'); ?>
    </div>
</div>