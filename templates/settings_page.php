<?php
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer PrintMyBlog\services\display\FormInputs
 * @var $settings PrintMyBlog\domain\FrontendPrintSettings
 * @var $settings_form Twine\forms\base\FormSection
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
                    <?php esc_html_e('Show print buttons on:', 'print-my-blog'); ?>
                </th>
                <td>
                    <input type="checkbox" id="pmb-show-print-buttons" name="pmb_show_buttons" value="1" <?php echo $settings->showButtons() ? 'checked="checked"' : '' ?>>
                    <label for="pmb-show-print-buttons"><?php esc_html_e(
                        'Posts',
                        'print-my-blog'
                        ); ?></label><br/>
                    <input type="checkbox" id="pmb-show-print-buttons-pages" name="pmb_show_buttons_pages" value="1" <?php echo $settings->showButtonsPages() ? 'checked="checked"' : '' ?>>
                    <label for="pmb-show-print-buttons-pages"><?php esc_html_e(
                        'Pages',
                        'print-my-blog'
                    ); ?>
                    </label>
                </td>
            </tr>
            </tbody>
        </table>
        <details class="pmb-details">
            <summary class="pmb-reveal-options pmb-inline"><?php esc_html_e('Customize Buttons', 'print-my-blog'); ?>
            </summary>
            <div class="pmb-reveal-details">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="pmb-place-above"><?php esc_html_e('Place Buttons', 'print-my-blog');?></label>
                        </th>
                        <td>
                            <select name="pmb_place_above" id="pmb-place-above">
                                <option value="1" <?php echo $settings->showButtonsAbove() ? 'selected="selected"' : '';?>><?php esc_html_e('Above Content', 'print-my-blog');?></option>
                                <option value="0" <?php echo ! $settings->showButtonsAbove() ? 'selected="selected"' : '';?>><?php esc_html_e('Below Content', 'print-my-blog');?></option>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <h2><?php esc_html_e('Print Formats', 'print-my-blog');?></h2>
                <?php foreach($settings->formats() as $slug => $format){ ?>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label>
                                <input type="checkbox" id="pmb-<?php echo esc_attr($slug);?>" name="pmb_format[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr($slug);?>" <?php echo $settings->isActive($slug)? 'checked="checked"' : '';?>>
                                <?php echo $format['admin_label'] ?>
                            </label>
                        </th>
                        <td>
                            <label><?php esc_html_e('Label', 'print-my-blog'); ?> <input type="text" id="pmb-<?php echo $slug;?>-label" name="pmb_frontend_labels[<?php echo esc_attr($slug);?>]" value="<?php echo esc_attr( $settings->getFrontendLabel($slug)); ?>"></label>

                            <details class="pmb-details">
                                <summary class="pmb-reveal-options" id="pmb-reveal-main-options"><?php esc_html_e('Show Options', 'print-my-blog'); ?></summary>
                                <div class="pmb-reveal-details">
                                    <?php
                                    $displayer->setInputPrefixes(['pmb_print_options',$slug,]);
                                    $displayer->setNewValues($settings->getPrintOptionsAndValues($slug));
                                    pmb_render_template('partials/display_options.php',['print_options' => $print_options, 'displayer' => $displayer, 'upsells' => false]);?>
                                </div>
                            </details>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <?php } ?>
            </div>
        </details>

        <h2><?php _e('Admin Quick Project Buttons', 'print-my-blog');?></h2>
        <?php echo $settings_form->getHtmlAndJs();?>
        <?php wp_nonce_field( 'pmb-settings' );?>
        <button class="button-primary"><?php esc_html_e('Save Settings','print-my-blog' );?></button> <input name="pmb-reset" class="button button-secondary" onclick="return confirm('<?php echo esc_attr(__('Are you sure you want to reset the settings back to defaults?','print-my-blog'));?>');" type="submit" value="<?php esc_html_e('Reset to Defaults', 'print-my-blog'); ?>">
    </form>
</div>