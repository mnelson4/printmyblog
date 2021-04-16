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
                <a class="pmb-pro-window-button" href="http://cmljnelson.test/wp-admin/admin.php?page=print-my-blog-help" title="Get Help"><span class="dashicons
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
                <div class="pmb-print-option">
                    <h2><?php esc_html_e('Demo Pro', 'print-my-blog');?></h2>
                    <a class="pmb-pro-window-button" onclick="window.print()">
                        <?php _e('Download Test PDF', 'print-my-blog'); ?>
                    </a>
                    <p><?php esc_html_e('Supports all PDF features of Print My Blog Pro, but has added watermark.', 'print-my-blog');?></p>
                </div>
                <div class="pmb-print-option">
                    <h2><?php esc_html_e('Buy Pro', 'print-my-blog');?></h2>
                    <a class="pmb-pro-window-button" onclick="window.print()">
                        <?php _e('Purchase Subscription', 'print-my-blog'); ?>
                    </a>
                    <p><?php esc_html_e('To generate Print My Blog Pro PDFs with no watermarks', 'print-my-blog');?></p>
                </div>
            </div>
        </div>
        <div class="pmb-print-ready pmb-print-instructions">

        </div>
        <div class="pmb-posts-placeholder pmb-extra-content">
        </div>
    </div>
</div>