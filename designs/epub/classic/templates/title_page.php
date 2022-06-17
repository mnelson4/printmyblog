<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
 *
 */
?>
<div class="pmb-posts-header">
    <?php
    if (in_array('title', $pmb_design->getSetting('header_content'), true)) {
        ?>
        <h1 class="site-title"><?php $pmb_project->echoPublishedTitle(); ?></h1>
        <?php
    }
    if (in_array('subtitle', $pmb_design->getSetting('header_content'), true)) {
        ?>
        <h2 class="site-description pmb-subtitle"><?php $pmb_project->echoSetting('subtitle'); ?></h2>
        <?php
    }
    if (in_array('byline', $pmb_design->getSetting('header_content'), true)) {
        ?>
        <h2 class="project-byline"><?php $pmb_project->echoSetting('byline'); ?></h2>
        <?php
    }
    ?>
    <div class="pmb-title-page-meta"><?php
        if (in_array('url', $pmb_design->getSetting('header_content'), true)) {
            $url_text = $pmb_project->getSetting('url');
            $true_url = esc_url_raw($url_text) === $url_text;
            ?>
            <p class="site-url "><?php
                echo esc_html($url_text);
                ?></p>
            <?php
        }
        ?>
        <p class="pmb-printout-meta">
            <?php
            // give it some space
            echo ' ';
            if (in_array('date_printed', $pmb_design->getSetting('header_content'), true)) {
                printf(
                    // translators: 1: date
                    esc_html__('Printed on %1$s', 'print-my-blog'),
                    esc_html(date_i18n(get_option('date_format')))
                );
            }
            ?></p>
    </div>
