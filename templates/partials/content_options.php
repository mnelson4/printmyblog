<?php
// Template to show the HTML for display options
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer Twine\services\display\FormInputs
 * @var $upsells boolean
 */

use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\services\display\FormInputs;
?>

<h2><?php esc_html_e('Content','print-my-blog' );?></h2>
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row">
            <label ><?php esc_html_e('Header Content to Print','print-my-blog' ); $upsells ? pmb_pro_better_e(__('Pro Print supports full-page title pages, custom title pages, or no title page', 'print-my-blog')) : null?></label>
            <p class="description"><?php esc_html_e('Appears at the top of the first page.', 'print-my-blog'); ?></p>
        </th>
        <td>
            <?php
            echo $displayer->getHtmlForShortOptions($print_options->headerContentOptions($upsells));
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