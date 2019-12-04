<?php
/**
 * @var $pmb_print_now_formats \PrintMyBlog\domain\PrintNowSettings
 */
?>
<div class="wrap nosubsub">
    <h1><?php esc_html_e('Print My Blog - Settings','print-my-blog' );?></h1>
    <form method="post">
        <h2><?php esc_html_e('Print Buttons', 'print-my-blog'); ?></h2>

        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="pmb-show-print-buttons"> <?php esc_html_e('Show visitors buttons to print your posts?', 'print-my-blog'); ?></label>
                </th>
                <td>
                    <input type="checkbox" id="pmb-show-print-buttons" name="show_buttons" value="1" <?php echo $pmb_print_now_formats->showButtons() ? 'checked="checked"' : '' ?>>
                </td>
            </tr>
            </tbody>
        </table>
        <details class="pmb-details">
            <summary class="pmb-reveal-options pmb-inline"><?php esc_html_e('Customize Buttons', 'print-my-blog'); ?>
            </summary>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Print Formats', 'print-my-blog');?>
                    </th>
                    <td>
                        <table>
                            <?php foreach($pmb_print_now_formats->formats() as $slug => $format){ ?>
                                <tr>
                                    <td>
                                        <label>
                                            <input type="checkbox" id="pmb-<?php echo esc_attr($slug);?>" name="format[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr($slug);?>" <?php echo $pmb_print_now_formats->isActive($slug)? 'checked="checked"' : '';?>>
                                            <?php echo $format['admin_label'] ?>
                                        </label>
                                    </td>
                                    <td>
                                        <label><?php esc_html_e('Label', 'print-my-blog'); ?> <input type="text" id="pmb-<?php echo $slug;?>-label" name="frontend_labels[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr( $pmb_print_now_formats->getFrontendLabel($slug)); ?>"></label>
                                    </td>
                                </tr>
                            <?php } ?>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </details>
        <?php wp_nonce_field( 'pmb-settings' );?>
        <button class="button-primary"><?php esc_html_e('Save Settings','print-my-blog' );?></button> <input name="pmb-reset" class="button button-secondary" type="submit" value="<?php esc_html_e('Reset to Defaults', 'print-my-blog'); ?>">
    </form>
</div>