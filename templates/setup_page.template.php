<?php
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 */

use PrintMyBlog\domain\PrintOptions;

?>
<div class="wrap nosubsub">
<h1><?php esc_html_e('Print My Blog - Print Now','print-my-blog' );?></h1>
    <?php
    if(isset($legacy_page)){
        ?>
        <div class="notice notice-warning">
            <p>
                <?php esc_html_e('Print My Blog’s Page is moving! It’s new location is on the left, under "Print My Blog", then "Print Now".', 'print-my-blog'); ?>
            </p>
        </div>
        <?php
    }
    ?>
    <p><?php esc_html_e('Configure how you’d like the blog to be printed, or just use our recommended defaults.', 'print-my-blog'); ?></p>
    <form action="<?php echo site_url();?>" method="get">
        <?php if(PMB_REST_PROXY_EXISTS){?>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Site URL (including "https://" or "http://")', 'print-my-blog');?>
                    </th>
                    <td>
                        <input name="site" id="pmb-site" class="pmb-wide-input" placeholder="<?php echo site_url();?>">
                        <div class="pmb-spinner-container">
                            <div id="pmb-site-checking" class="pmb-spinner pmb-hidden-initially"></div>
                        </div>
                        <span id="pmb-site-ok" class="pmb-hidden-initially">✅</span><span id="pmb-site-bad" class="pmb-hidden-initially">❌</span><span id="pmb-site-status"></span>
                        <p class="description"><?php esc_html_e('URL of the WordPress site (self-hosted or on WordPress.com) you’d like to print. Leave blank to use this current site.', 'print-my-blog');?></p>
                    </td>
                </tr>
        <?php }?>
            </tbody>
        </table>

        <details class="pmb-details">
            <summary class="pmb-reveal-options" id="pmb-reveal-main-options"><?php esc_html_e('Show Options', 'print-my-blog'); ?></summary>
            <h1><?php esc_html_e('Options','print-my-blog' );?></h1>
            <h2><?php esc_html_e('Post Selection','print-my-blog' );?></h2>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Post Selection','print-my-blog' );?></th>
                    <td>
                        <label><input class="pmb-post-type" type="radio" name="post-type" value="post" checked="checked"><?php esc_html_e('Posts', 'print-my-blog');?></label>
                        <br>
                        <label><input class="pmb-post-type" type="radio" name="post-type" value="page"><?php esc_html_e('Pages', 'print-my-blog');?></label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Order', 'print-my-blog'); ?></th>
                    <td>
                        <div id="pmb-order-by-date">
                            <p><?php esc_html_e('Posts will be ordered:', 'print-my-blog'); ?></p>
                            <label><input class="pmb-order" type="radio" name="order-date" value="asc" checked="checked"><?php esc_html_e('Oldest First', 'print-my-blog'); ?></label><br/>
                            <label><input class="pmb-order" type="radio" name="order-date" value="desc"><?php esc_html_e('Newest First', 'print-my-blog'); ?></label>
                        </div>
                        <div id="pmb-order-by-menu">
                            <p><?php esc_html_e('Pages will be ordered using the "Order" attribute:', 'print-my-blog'); ?></p>
                            <label><input class="pmb-order" type="radio" name="order-menu" value="asc" checked="checked"><?php esc_html_e('From Lowest to Highest', 'print-my-blog'); ?></label><br>
                            <label><input class="pmb-order" type="radio" name="order-menu" value="desc" ><?php esc_html_e('From Highest to Lowest', 'print-my-blog'); ?></label>
                        </div>
                    </td>
                </tr>
                <?php
                if( is_user_logged_in()){
                ?>
                    <tr>
                        <th scope="row"><label for="pmb-include-private-posts"><?php esc_html_e('Include Password-Protected and Private Posts','print-my-blog' );?></label></th>
                        <td>
                            <input type="checkbox" id="pmb-include-private-posts" name="include-private-posts" value="1" checked="checked">
                            <p class="description"><?php esc_html_e('If unchecked, only public posts will be included in the printout.', 'print-my-blog'); ?></p>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>
            <h2><?php esc_html_e('Filters', 'print-my-blog');?> <div style="display:inline-block" id="pmb-categories-spinner"><div class="pmb-spinner"></div></div></h2>
            <table class="form-table">
                <tbody id="pmb-dynamic-categories">
                </tbody>
            </table>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Authored By', 'print-my-blog'); ?></th>
                    <td><select id="pmb-author-select" class="pmb-author-select" name="pmb-author"></select></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Posted On or After...', 'print-my-blog'); ?></th>
                    <td><input type="text" class="pmb-date" name="dates[after]"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Posted On or Before...', 'print-my-blog'); ?></th>
                    <td><input type="text" class="pmb-date" name="dates[before]"></td>
                </tr>
                </tbody>
            </table>


                <h2><?php esc_html_e('Content','print-my-blog' );?></h2>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label ><?php esc_html_e('Header Content to Print','print-my-blog' );?></label>
                    </th>
                    <td>
                        <?php foreach($print_options->headerContentOptions() as $option_name => $option_details){
                            ?>
                        <label for="show_<?php echo esc_attr($option_name);?>">
                            <input type="checkbox" name="show_<?php echo esc_attr($option_name);?>" id="show_<?php echo $option_name;?>"
                                <?php
                                if ($option_details['default']){
                                    ?> checked="checked"
                                    <?php
                                }
                                ?> value="1">
                            <?php echo $option_details['label'];?></label><br>
                        <?php
                            if (isset($option_details['help'])){
                            ?> <p class="description"><?php echo $option_details['help'];?></p>
                            <?php
                            }
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"> <?php esc_html_e('Post Content to Print','print-my-blog' );?></th>
                    <td>
                    <?php
                    foreach($print_options->postContentOptions() as $option_name => $option_details){
                        ?>
                        <label for="show_<?php echo esc_attr($option_name);?>">
                            <input type="checkbox" name="show_<?php echo esc_attr($option_name);?>" id="show_<?php echo esc_attr($option_name);?>"
                                <?php
                                if ($option_details['default']){
                                ?> checked="checked"
                            <?php
                            }
                            ?> value="1">
                            <?php echo $option_details['label'];?></label><br>
                    <?php
                    }
                    ?>
                    </td>
                </tr>
                </tbody>
            </table>

            <h2><?php esc_html_e('Page Layout','print-my-blog' );?></h2>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="post-page-break"><?php esc_html_e('Each Post Begins on a New Page','print-my-blog' );?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="post-page-break" id="post-page-break" checked="checked">
                        <p class="description"><?php esc_html_e('Whether to force posts to always start on a new page. Doing so makes the page more legible, but uses more paper.','print-my-blog' );?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="columns"><?php esc_html_e('Columns','print-my-blog' );?></label>
                    </th>
                    <td>
                        <select name="columns" id="columns">
                            <option value="1" selected="selected"><?php esc_html_e('1','print-my-blog' );?></option>
                            <option value="2"><?php esc_html_e('2', 'print-my-blog'); ?></option>
                            <option value="3"><?php esc_html_e('3', 'print-my-blog'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('The number of columns of text on each page. Not supported by some web browsers.','print-my-blog' );?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="font-size"><?php esc_html_e('Font Size','print-my-blog' );?></label>
                    </th>
                    <td>
                        <select name="font-size" id="font-size">
                            <option value="tiny"><?php esc_html_e('Tiny (1/2 size)','print-my-blog' );?></option>
                            <option value="small"><?php esc_html_e('Small (3/4 size)', 'print-my-blog'); ?></option>
                            <option value="normal" selected="selected"><?php esc_html_e('Normal (theme default)', 'print-my-blog'); ?></option>
                            <option value="large"><?php esc_html_e('Large (slightly larger than normal)', 'print-my-blog'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="image-size"><?php esc_html_e('Image Size','print-my-blog' );?></label>
                    </th>
                    <td>
                        <select name="image-size" id="image-size">
                            <option value="full"><?php esc_html_e('Full (theme default)','print-my-blog' );?></option>
                            <option value="large"><?php esc_html_e('Large (3/4 size)','print-my-blog' );?></option>
                            <option value="medium" selected="selected"><?php esc_html_e('Medium (1/2 size)', 'print-my-blog'); ?></option>
                            <option value="small"><?php esc_html_e('Small (1/4 size)', 'print-my-blog'); ?></option>
                            <option value="none"><?php esc_html_e('None (hide images)', 'print-my-blog'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('If you want to save paper, choose a smaller image size, or hide images altogether.','print-my-blog' );?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="links"><?php esc_html_e('Include Hyperlinks','print-my-blog' );?></label>
                    </th>
                    <td>
                        <select name="links" id="image-size">
                            <option value="include" selected="selected"><?php esc_html_e('Include','print-my-blog' );?></option>
                            <option value="remove"><?php esc_html_e('Remove','print-my-blog' );?></option>

                        </select>
                        <p class="description"><?php esc_html_e('Whether to remove hyperlinks or not.','print-my-blog' );?></p>
                    </td>
                </tr>
                </tbody>
            </table>


            <details class="pmb-details">
                <summary class="pmb-reveal-options"><?php esc_html_e('Troubleshooting Options','print-my-blog' );?></summary>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="rendering-wait"><?php esc_html_e('Post Rendering Wait-Time','print-my-blog' );?></label>
                        </th>
                        <td>
                            <input name="rendering-wait" value="200"><?php esc_html_e('ms','print-my-blog' );?>
                            <p class="description"><?php esc_html_e('Milliseconds to wait between rendering posts. If posts are rendered too quickly on the page, sometimes images won’t load properly. ','print-my-blog' );?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="include-inline-js"><?php esc_html_e('Include Inline Javascript','print-my-blog' );?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="include-inline-js" value="1">
                            <p class="description"><?php esc_html_e('Sometimes posts contain inline javascript which can cause errors and stop the page from rendering.','print-my-blog' );?></p>
                        </td>

                    </tr>
                    </tbody>
                </table>
            </details>
            <input type="hidden" name="<?php echo PMB_PRINTPAGE_SLUG;?>" value="1">
        </details>

        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <?php esc_html_e('Format', 'print-my-blog');?>
                </th>
                <td>
                    <?php
                    $formats = array(
                        'print' => array(
                            'label' => esc_html__('Paper', 'print-my-blog'),
                            'help_text' => esc_html__('Print a physical copy using your web browser’s print functionality.', 'print-my-blog'),
                            'checked' => true
                        ),
                        'pdf' => array(
                            'label' => esc_html__('Digital PDF', 'print-my-blog'),
                            'help_text' => esc_html__('Make a PDF file, intended for reading from a computer or other device, using your browser or a browser extension.', 'print-my-blog'),
                            'link' => 'https://wordpress.org/plugins/print-my-blog/#how%20do%20i%20create%20a%20pdf%20using%20print%20my%20blog%3F'
                        ),
                        'ebook' => array(
                            'label' => esc_html__('eBook (ePub or MOBI)', 'print-my-blog'),
                            'help_text' => esc_html__('Make a free eBook using dotEPUB.', 'print-my-blog'),
                            'link' => 'https://wordpress.org/plugins/print-my-blog/#how%20do%20i%20create%20an%20ebook%20using%20print%20my%20blog%3F'
                        )
                    );
                    foreach($formats as $key => $details){
                        ?>
                        <div class="pmb-format-option">
                            <input type="radio" name="format" id="format-<?php echo $key;?>" value="<?php echo $key;?>" <?php echo isset($details['checked']) ? 'checked="checked"' : '';?>>
                            <label for="format-<?php echo $key;?>">
                                <?php echo $details['label']; ?>
                            </label>
                            <p class="description">
                                <?php echo $details['help_text']; ?>
                                <?php echo isset($details['link']) ? '<a href="' . $details['link'] . '" target="_blank">' . esc_html__('Learn More','print-my-blog' ) . '</a>' : '';?>
                            </p>
                        </div>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            </tbody>
        </table>
        <button class="button-primary"><?php esc_html_e('Prepare Print-Page','print-my-blog' );?></button>
    </form>
</div>