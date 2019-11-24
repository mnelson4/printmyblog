<div class="wrap nosubsub">
    <h1><?php esc_html_e('Print My Blog','print-my-blog' );?></h1>
    <p><?php esc_html_e('Configure how site visitors print your blog.', 'print-my-blog'); ?></p>
    <form method="post">
        <h2><?php esc_html_e('Print Buttons', 'print-my-blog'); ?></h2>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Print Formats', 'print-my-blog');?>
                </th>
                <td>
                    <table>
                        <tr>
                            <td>
                                <label>
                                    <input type="checkbox" id="pmb-paper" name="format" value="paper" checked="checked">
                                    <?php esc_html_e('Paper', 'print-my-blog'); ?>
                                </label>
                            </td>
                            <td>
                                <?php esc_html_e('Label', 'print-my-blog'); ?> <input type="text" id="pmb-paper-label" name="format-paper-label" value="<?php esc_html_e('Print', 'print-my-blog'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <label>
                                    <input type="checkbox" id="pmb-pdf" name="format" value="pdf" checked="checked">
                                    <?php esc_html_e('PDF', 'print-my-blog'); ?>
                                </label>
                            </td>
                            <td>
                                <?php esc_html_e('Label', 'print-my-blog'); ?> <input type="text" id="pmb-pdf-label" name="format-pdf-label" value="<?php esc_html_e('PDF', 'print-my-blog'); ?>">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <button class="button-primary"><?php esc_html_e('Save Settings','print-my-blog' );?></button>
    </form>
</div>