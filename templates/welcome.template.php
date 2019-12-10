<div class="wrap nosubsub pmb-welcome">
    <h1>🎉<?php esc_html_e('Let’s make it easy to print your blog!','print-my-blog' );?>🎉</h1>
    <p class="pmb-middle-important-text"><?php esc_html_e('What would you like to do?', 'print-my-blog'); ?></p>
    <div class="pmb-welcome-options">
        <div class="pmb-welcome-option-wrap">
            <div class="pmb-welcome-option-inner"><h2><?php esc_html_e('Print My Blog Now', 'print-my-blog'); ?></h2>
            <p><?php printf(
                esc_html__('Then %1$svisit the Print Now page.%2$s', 'print-my-blog'),
                    '<a href="' . admin_url(PMB_ADMIN_PAGE_PATH) . '">',
                    '</a>'
                    ); ?></p>
            </div>
        </div>
        <div class="pmb-welcome-option-wrap">
            <div class="pmb-welcome-option-inner">
            <h2><?php esc_html_e('Let Visitors Print My Blog', 'print-my-blog'); ?></h2>
            <p><?php printf(
                esc_html__('Then %1$sconfigure the print buttons on the Settings page%2$s', 'print-my-blog'),
                '<a href="' . admin_url(PMB_ADMIN_SETTINGS_PAGE_PATH) . '">',
                '</a>'
                ); ?></p>
            </div>
        </div>
    </div>
</div>