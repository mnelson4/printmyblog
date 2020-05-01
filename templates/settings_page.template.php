<?php
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer PrintMyBlog\services\display\FormInputs
 * @var $settings PrintMyBlog\domain\FrontendPrintSettings;
 */

use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\services\display\FormInputs;
use PrintMyBlog\domain\FrontendPrintSettings;

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
                    <input type="checkbox" id="pmb-show-print-buttons" name="show_buttons" value="1" <?php echo $settings->showButtons() ? 'checked="checked"' : '' ?>>
                </td>
            </tr>
            </tbody>
        </table>
        <details class="pmb-details">
            <summary class="pmb-reveal-options pmb-inline"><?php esc_html_e('Customize Buttons', 'print-my-blog'); ?>
            </summary>
            <h2><?php esc_html_e('Print Formats', 'print-my-blog');?></h2>
            <?php foreach($settings->formats() as $slug => $format){ ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label>
                            <input type="checkbox" id="pmb-<?php echo esc_attr($slug);?>" name="format[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr($slug);?>" <?php echo $settings->isActive($slug)? 'checked="checked"' : '';?>>
                            <?php echo $format['admin_label'] ?>
                        </label>
                    </th>
                    <td>
                        <label><?php esc_html_e('Label', 'print-my-blog'); ?> <input type="text" id="pmb-<?php echo $slug;?>-label" name="frontend_labels[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr( $settings->getFrontendLabel($slug)); ?>"></label>

                        <details class="pmb-details">
                            <summary class="pmb-reveal-options" id="pmb-reveal-main-options"><?php esc_html_e('Show Options', 'print-my-blog'); ?></summary>
                            <?php
                            $displayer->setInputPrefixes(['print_options',$slug,]);
                            $displayer->setNewValues($settings->getPrintOptions($slug));
                            include('partials/display_options.template.php');?>
                        </details>
                    </td>
                </tr>
                </tbody>
            </table>
            <?php } ?>
        </details>
        <?php wp_nonce_field( 'pmb-settings' );?>
        <button class="button-primary"><?php esc_html_e('Save Settings','print-my-blog' );?></button> <input name="pmb-reset" class="button button-secondary" type="submit" value="<?php esc_html_e('Reset to Defaults', 'print-my-blog'); ?>">
    </form>
</div>