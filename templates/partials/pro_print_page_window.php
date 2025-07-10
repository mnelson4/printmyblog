<?php
/**
* @var $license_info array
 * @var $project PrintMyBlog\orm\entities\Project
 */
$generate_url = add_query_arg(
    [
        'ID' => $project->getWpPost()->ID,
        'action' => \PrintMyBlog\controllers\Admin::SLUG_ACTION_EDIT_PROJECT,
        'subaction' => \PrintMyBlog\entities\ProjectProgress::GENERATE_STEP
    ],
    admin_url(PMB_ADMIN_PROJECTS_PAGE_PATH)
);
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
                    <span class="dashicons
                dashicons-sos pmb-icon"></span><?php esc_html_e('Help!', 'print-my-blog');?></a>
            </div>
        </div>

        <div class="pmb-pro-print-window-content">
            <div class="pmb-pro-print-window-options">

                <div id="pmb-print-with-browser-option" class="pmb-print-option">
                    <h2><?php esc_html_e('Free', 'print-my-blog');?></h2>
                    <a id="pmb-print-with-browser" class="pmb-pro-window-button" tabindex="0">
                        <?php _e('Print with Browser', 'print-my-blog'); ?>
                    </a>
                    <p><?php esc_html_e('Limited to features supported by your browser.', 'print-my-blog');?></p>
                    <p><a target="_blank" href="https://printmy.blog/free-vs-pro/"><?php esc_html_e('Compare printing with your browser vs Pro PDF Service', 'print-my-blog');?></a></p>
                </div>
                <div id="pmb-print-refresh-window-option" class="pmb-print-option" style="display: none">
                    <h2><?php esc_html_e('Note', 'print-my-blog');?></h2>
                    <button id="pmb-refresh-print-window-button" class="pmb-pro-window-button"><?php esc_html_e('Refresh', 'print-my-blog')?></button>
                    <p id="pmb-refresh-for-browser" style="display: none"><?php esc_html_e('This page has been optimized for printing using our Pro PDF Print Service. Please push the refresh button if you want to print the page through your browser.', 'print-my-blog');?></p>
                    <p id="pmb-refresh-for-pro-pdf" style="display: none"><?php esc_html_e('This page has been optimized for printing from your browser. Please push the refresh button if you want to print the page using our Pro PDF Service.', 'print-my-blog');?></p>
                </div>
                <div id="pmb-print-pro-pdf-option" class="pmb-print-option pmb-big-option pmb-highlight">
                    <h2><?php esc_html_e('Pro PDF Service', 'print-my-blog');?></h2>
                    <div style="display:none" class="pmb-warning" id="pmb-print-page-warnings"></div>
                    <button class="pmb-pro-window-button pmb-download-test pmb-disabled">
                        <?php _e('Download Test PDF', 'print-my-blog'); ?> <div class="pmb-spinner-container"><div class="pmb-spinner"></div></div>
                    </button>
                    <?php if(! is_array($license_info) ){ ?>


                        <a class="pmb-pro-window-button" href="<?php echo esc_url(pmb_fs()->get_upgrade_url());?>" >
                            <?php _e('Purchase Subscription', 'print-my-blog'); ?>
                        </a>
                        <p><a href="https://printmy.blog/free-vs-pro/" target="_blank"><?php esc_html_e('Feature Comparison List', 'print-my-blog');?></a></p>
                    <?php } else { ?>
                    <?php
                        if(! $license_info['remaining_credits']) {?>
                            <button class="pmb-pro-window-button" href="<?php echo esc_url(pmb_fs()->get_upgrade_url());?>" >
                                <?php _e('Upgrade Subscription', 'print-my-blog'); ?>
                            </button>
                            <p><?php esc_html_e('Sorry you have no more download credits for this month.', 'print-my-blog');?></p>
                            <p><?php esc_html_e('Please upgrade your plan for more credits per month. If you have a yearlong subscription and this is the first time, feel free to contact us and ask for a one-time increase to your credits.', 'print-my-blog');?></p>
                            </p>
                        <?php } else { ?>
                            <button class="pmb-pro-window-button pmb-download-live pmb-disabled" title="<?php echo esc_attr(__('Please download the Test PDF before the Paid PDF', 'print-my-blog'));?>">
                                <?php _e('Download Paid PDF', 'print-my-blog'); ?>
                            </button>
                            <p class="pmb-pro-description"><?php esc_html_e('Always download a free Test PDF before a Paid PDF.', 'print-my-blog');?>
                           </p>
                            <div class="pmb-pro-after-pro">
                                <p><?php esc_html_e('One credit was used to generate the file.', 'print-my-blog');?></p>
                                <a class="pmb-pro-window-button" href="javascript:history.back();"><?php esc_html_e('Return to Generate Page', 'print-my-blog');?></a>
                            </div>
                        <?php }
                    } ?>
                    <p class="pmb-help"><a href="https://printmy.blog/user-guide/getting-started/privacy-and-pmb-pro/" target="_blank"><?php
                            _e('Read how your data is handled', 'print-my-blog');?></a></p>
                </div>
            </div>
        </div>
    </div>
    <?php do_action('pmb_pro_print_page_window_end', 'pdf');?>
</div>