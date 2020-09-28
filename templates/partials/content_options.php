<?php
// Template to show the HTML for display options
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer Twine\services\display\FormInputs
 */

use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\services\display\FormInputs;

?>

<h2><?php esc_html_e('Content','print-my-blog' );?></h2>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row">
            <label ><?php esc_html_e('Header Content to Print','print-my-blog' );?></label>
            <p class="description"><?php esc_html_e('Appears at the top of the first page.', 'event_espresso'); ?></p>
        </th>
        <td>
            <?php
            echo $displayer->getHtmlForShortOptions($print_options->headerContentOptions());
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"> <?php esc_html_e('Post Content to Print','print-my-blog' );?></th>
        <td>
            <?php
            echo $displayer->getHtmlForShortOptions($print_options->postContentOptions());
            ?>
        </td>
    </tr>
    </tbody>
</table>