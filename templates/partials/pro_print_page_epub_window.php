<?php
/**
* @var $project_generation \PrintMyBlog\entities\ProjectGeneration
 */
?>
<!-- Print My Blog Version <?php echo PMB_VERSION;?>-->
<div class="pmb-pro-print-window-wrapper">
    <div class="pmb-pro-print-window">
        <div class="pmb-pro-print-window-topbar">
            <div class="pmb-pro-window-topbar-left">
                <a  class="pmb-pro-window-button" href="javascript:history.back();">
                    <span class="pmb-spinner-container"><span class="dashicons dashicons-arrow-left-alt"></span></span>
                    <?php esc_html_e('Back', 'print-my-blog'); ?>
                </a>
            </div>
            <div class="pmb-pro-window-title"><h1><?php esc_html_e('Print My Blog — Pro Print', 'print-my-blog');?></h1></div>
            <div class="pmb-pro-window-topbar-right">
                <a class="pmb-pro-window-button" href="<?php echo esc_url(admin_url(PMB_ADMIN_HELP_PAGE_PATH));?>" title="Get Help">
                    <span class="pmb-spinner-container"><span class="dashicons
                dashicons-sos pmb-icon"></span></span><?php esc_html_e('Help!', 'print-my-blog');?></a>
            </div>
        </div>
        <div class="pmb-pro-print-window-content">
            <div class="pmb-pro-print-window-options">
                <div class="pmb-print-option pmb-constrained pmb-highlight">
                    <h2><?php esc_html_e('ePub Generator', 'print-my-blog');?></h2>
                    <p><?php esc_html_e('Unlimited ePubs with a current subscription', 'print-my-blog');?></p>
                    <div style="display:none" class="pmb-warning" id="pmb-print-page-warnings"></div>
                    <a class="pmb-pro-window-button pmb-disabled" id="download_link"  download="<?php echo esc_attr($project_generation->getFileNameWithExtension());?>"><?php esc_html_e('Download ePub', 'print-my-blog');?><div class="pmb-spinner-container"><div class="pmb-spinner"></div></div></a>
                    <p><?php esc_html_e('Please refer to our documentation if you have questions about styling ', 'print-my-blog');?></p>
                </div>
            </div>
        </div>
    </div>
</div>