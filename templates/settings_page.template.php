<?php
/**
 * @var $pmb_print_now_formats \PrintMyBlog\domain\PrintNowSettings
 */
?>
<div class="wrap nosubsub">
    <h1><?php esc_html_e('Print My Blog - Settings','print-my-blog' );?></h1>
    <form method="post">
        <h2><?php esc_html_e('Print Buttons', 'print-my-blog'); ?></h2>
        <p><?php esc_html_e('Choose what print buttons you want to show on posts.', 'print-my-blog'); ?></p>
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
                                <td> <details class="pmb-format-label">
                                        <summary class="pmb-reveal-options pmb-inline"><?php esc_html_e('Customize', 'event_espresso'); ?></summary>
                                    <label><?php esc_html_e('Label', 'print-my-blog'); ?> <input type="text" id="pmb-<?php echo $slug;?>-label" name="frontend_labels[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr( $pmb_print_now_formats->getFrontendLabel($slug)); ?>"></label>
                                    </details>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <button class="button-primary"><?php esc_html_e('Save Settings','print-my-blog' );?></button> <input name="pmb-reset" class="button button-secondary" type="submit" value="<?php esc_html_e('Reset to Defaults', 'event_espresso'); ?>">
    </form>
</div>