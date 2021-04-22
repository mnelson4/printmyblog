<?php
/**
* @var $license_info array
 */
?>
<!-- Print My Blog Version <?php echo PMB_VERSION;?>-->
<div class="pmb-pro-print-window-wrapper">
    <div class="pmb-pro-print-window">
        <div class="pmb-pro-print-window-topbar">
            <div class="pmb-pro-window-topbar-left">
                <a  class="pmb-pro-window-button" href="javascript:history.back();">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php esc_html_e('Back', 'print-my-blog'); ?>
                </a>
            </div>
            <div class="pmb-pro-window-title"><h1><?php esc_html_e('Print My Blog Pro', 'print-my-blog');?></h1></div>
            <div class="pmb-pro-window-topbar-right">
                <a class="pmb-pro-window-button" href="<?php echo esc_url(admin_url(PMB_ADMIN_HELP_PAGE_PATH));?>" title="Get Help"><span class="dashicons
                dashicons-sos pmb-icon"></span><?php esc_html_e('Help!', 'print-my-blog');?></a>
            </div>
        </div>

        <div class="pmb-pro-print-window-content">
            <div class="pmb-pro-print-window-options">
                <div class="pmb-print-option">
                    <h2><?php esc_html_e('Free', 'print-my-blog');?></h2>
                    <a class="pmb-pro-window-button" onclick="window.print()">
                        <?php _e('Print with Browser', 'print-my-blog'); ?>
                    </a>
                    <p><?php esc_html_e('Totally free, but limited to features supported by your browser.', 'print-my-blog');?></p>
                </div>
                <div class="pmb-print-option pmb-big-option">
                    <?php if(! is_array($license_info) ){ ?>
                        <h2><?php esc_html_e('Pro Demo', 'print-my-blog');?></h2>
                        <button class="pmb-pro-window-button pmb-download-test">
                            <?php _e('Download Test PDF', 'print-my-blog'); ?>
                        </button>
                        <a class="pmb-pro-window-button" href="<?php echo esc_url(pmb_fs()->get_upgrade_url());?>" >
                            <?php _e('Purchase Subscription', 'print-my-blog'); ?>
                        </a>
                        <p><?php esc_html_e('Supports Pro features like:', 'print-my-blog');?></p>
                        <ul>
                            <li><?php esc_html_e('Table of contents with page numbers', 'print-my-blog');?></li>
                            <li><?php esc_html_e('Customizing page headers and footers', 'print-my-blog');?></li>
                            <li><?php esc_html_e('Convert hyperlinks to page references and footnotes', 'print-my-blog');?></li>
                            <li><?php esc_html_e('Intelligent realigning of images', 'print-my-blog');?></li>
                        </ul>
                    <?php } else {?>
                    <h2><?php esc_html_e('Pro', 'print-my-blog');?></h2>
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
                            ?></p>
                        <?php } else { ?>
                            <button class="pmb-pro-window-button pmb-download-live pmb-pro-disabled" title="<?php echo esc_attr(__('Download the Test PDF first', 'print-my-blog'));?>">
                                <?php _e('Download Paid PDF', 'print-my-blog'); ?>
                            </button>
                           <p class="description"><?php printf(
                                    esc_html__('Downloading the Paid PDF will use one of your %1$s remaining credits, and is non-refundable.',
                                        'print-my-blog'),
                                    $license_info['remaining_credits']
                                );?></p>
                        <?php }
                    } ?>

                </div>
            </div>
        </div>
        <div class="pmb-print-ready pmb-print-instructions">

        </div>
        <div class="pmb-posts-placeholder pmb-extra-content">
        </div>
    </div>
</div>