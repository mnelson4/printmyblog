<?php
// Template to show the HTML for layout options
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer Twine\services\display\FormInputs
 * @var $upsells boolean
 */

use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\services\display\FormInputs;

?>
<h2><?php esc_html_e('Page Layout','print-my-blog' );?></h2>
<table class="form-table">
    <tbody>
    <?php echo $displayer->getHtmlForTabledOptions($print_options->pageLayoutOptions($upsells));?>
    </tbody>
</table>