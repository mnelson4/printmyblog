<?php
// Template to show the HTML for layout options
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer Twine\services\display\FormInputs
 */

use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\services\display\FormInputs;

?>
<details class="pmb-details">
    <summary class="pmb-reveal-options"><?php esc_html_e('Troubleshooting Options','print-my-blog' );?></summary>
    <table class="form-table">
        <tbody>
        <?php echo $displayer->getHtmlForTabledOptions($print_options->troubleshootingOptions());?>
        </tbody>
    </table>
</details>
