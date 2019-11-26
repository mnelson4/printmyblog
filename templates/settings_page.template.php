<?php
/**
 * @var $pmb_print_now_formats \PrintMyBlog\domain\PrintNowSettings
 */
?>
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
                        <?php foreach($pmb_print_now_formats->formats() as $slug => $format){ ?>
                            <tr>
                                <td>
                                    <label>
                                        <input type="checkbox" id="pmb-<?php echo $slug;?>" name="format" value="<?php echo $slug;?>" <?php echo $pmb_print_now_formats->isActive($slug)? 'checked="checked"' : '';?>>
                                        <?php echo $format['admin_label'] ?>
                                    </label>
                                </td>
                                <td>
                                    <?php esc_html_e('Label', 'print-my-blog'); ?> <input type="text" id="pmb-<?php echo $slug;?>-label" name="frontend_labels[<?php echo $slug;?>]" value="<?php echo $pmb_print_now_formats->getFrontendLabel($slug); ?>">
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </td>
            </tr>
            </tbody>
        </table>
        <button class="button-primary"><?php esc_html_e('Save Settings','print-my-blog' );?></button>
    </form>
</div>