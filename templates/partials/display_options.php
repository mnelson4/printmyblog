<?php
// Template to show the HTML for display options
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer Twine\services\display\FormInputs
 * @var $upsells boolean
 */

pmb_render_template('partials/content_options.php',['print_options' => $print_options, 'displayer' => $displayer, 'upsells' => $upsells]);
pmb_render_template('partials/layout_options.php', ['print_options' => $print_options, 'displayer' => $displayer, 'upsells' => $upsells]);
include('debug_options.php');