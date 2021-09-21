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
                <a  class="pmb-pro-window-button" href="<?php echo esc_url($generate_url);?>">
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
                <div class="pmb-print-option">
                    <h2><?php esc_html_e('Free', 'print-my-blog');?></h2>
                    <a id="pmb-print-with-browser" class="pmb-pro-window-button" onclick="window.print()" tabindex="0">
                        <?php _e('Print with Browser', 'print-my-blog'); ?>
                    </a>
                    <p><?php esc_html_e('Limited to features supported by your browser.', 'print-my-blog');?></p>
                    <p><a target="_blank" href="https://printmy.blog/free-vs-pro/"><?php esc_html_e('Compare printing with your browser vs Pro Print Service', 'print-my-blog');?></a></p>
                </div>
                <div class="pmb-print-option pmb-big-option">
                    <h2><?php esc_html_e('Pro Print Service', 'print-my-blog');?></h2>
                    <?php if(! is_array($license_info) ){ ?>

                        <button class="pmb-pro-window-button pmb-download-test">
                            <?php _e('Download Test PDF', 'print-my-blog'); ?>
                        </button>
                        <a class="pmb-pro-window-button" href="<?php echo esc_url(pmb_fs()->get_upgrade_url());?>" >
                            <?php _e('Purchase Subscription', 'print-my-blog'); ?>
                        </a>
                        <p><a href="https://printmy.blog/free-vs-pro/" target="_blank"><?php esc_html_e('Feature Comparison List', 'print-my-blog');?></a></p>
                    <?php } else {?>
                    <button class="pmb-pro-window-button pmb-download-test">
                        <?php _e('Download Test PDF', 'print-my-blog'); ?>
                    </button>
                    <?php
                        if(! $license_info['remaining_credits']) {?>
                            <button class="pmb-pro-window-button" href="<?php echo esc_url(pmb_fs()->get_upgrade_url());?>" >
                                <?php _e('Upgrade Subscription', 'print-my-blog'); ?>
                            </button>
                            <p><?php esc_html_e('Sorry you have no more download credits for this month.', 'print-my-blog');?></p>
                            <p><?php esc_html_e('Please upgrade your plan for more credits per month. If you have a yearlong subscription and this is the first time, feel free to contact us and ask for a one-time increase to your credits.', 'print-my-blog');?></p>
                            </p>
                        <?php } else { ?>
                            <button class="pmb-pro-window-button pmb-download-live pmb-pro-disabled" title="<?php echo esc_attr(__('Please download the Test PDF before the Paid PDF', 'print-my-blog'));?>">
                                <?php _e('Download Paid PDF', 'print-my-blog'); ?>
                            </button>
                           <p class="description pmb-pro-description"><?php printf(
                                    esc_html__('Downloading the Paid PDF will use one of your %1$s remaining credits, and is non-refundable.',
                                        'print-my-blog'),
                                    $license_info['remaining_credits']
                                );?>
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
</div>