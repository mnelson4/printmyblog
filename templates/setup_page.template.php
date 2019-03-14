<?php
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 */

use PrintMyBlog\domain\PrintOptions;

?>
<div class="wrap nosubsub">
<h1><?php esc_html_e('Print My Blog','print-my-blog' );?></h1>
    <?php if(isset($_GET['welcome'])){
        ?>
        <div class="updated fade">
            <p>
                <?php esc_html_e('Welcome! This is where you begin preparing your blog for printing. You can get here from the left-hand menu, under "Tools", then "Print My Blog."','print-my-blog' );?>
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
                        <input name="site" id="pmb-site" placeholder="<?php echo site_url();?>">
                        <div class="pmb-spinner-container">
                            <div id="pmb-site-checking" class="pmb-spinner pmb-hidden-initially"></div>
                        </div>
                        <span id="pmb-site-ok" class="pmb-hidden-initially">✅</span><span id="pmb-site-bad" class="pmb-hidden-initially">❌</span><span id="pmb-site-status"></span>
                        <p class="description"><?php esc_html_e('URL of the WordPress site (self-hosted or on WordPress.com) you’d like to print. Leave blank to use this current site.', 'print-my-blog');?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php }?>

        <details class="pmb-details">
            <summary class="pmb-reveal-options" id="pmb-reveal-main-options"><?php esc_html_e('Show Options', 'print-my-blog'); ?></summary>
            <h1><?php esc_html_e('Options','print-my-blog' );?></h1>
            <h2><?php esc_html_e('Post Selection','print-my-blog' );?></h2>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e('Post Selection','event_espresso' );?></th>
                    <td>
                        <label><input class="pmb-post-type" type="radio" name="post-type" value="post" checked="checked"><?php esc_html_e('Posts', 'print-my-blog');?></label>
                        <br>
                        <label><input class="pmb-post-type" type="radio" name="post-type" value="page"><?php esc_html_e('Pages', 'print-my-blog');?></label>
                    </td>
                </tr>
                </tbody>
            </table>
            <h2><?php esc_html_e('Filters', 'print-my-blog');?> <div style="display:inline-block" id="pmb-categories-spinner"><div class="pmb-spinner"></div></div></h2>
            <table class="form-table">
                <tbody id="pmb-dynamic-categories">
                </tbody>
            </table>


                <h2><?php esc_html_e('Content','print-my-blog' );?></h2>
            <table class="form-table">
                <tbody>
                <?php

                ?>
                <tr>
                    <th scope="row"> <?php esc_html_e('Post Content to Print','print-my-blog' );?></th>
                    <td>
                    <?php
                    foreach($print_options->postContentOptions() as $option_name => $option_details){
                        ?>
                        <label for="<?php echo $option_name;?>">
                            <input type="checkbox" name="<?php echo $option_name;?>" id="<?php echo $option_name;?>"
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
                        <label for="printout-meta"><?php esc_html_e('Show Printout Meta Info','print-my-blog' );?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="printout-meta" id="printout-meta" checked="checked">
                        <p class="description"><?php esc_html_e('Whether to include your blog’s URL, date of printing, and that this printout was made using Print My Blog.','print-my-blog' );?></p>
                    </td>
                </tr>
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
                            <option value="full" selected="selected"><?php esc_html_e('Full (theme default)','print-my-blog' );?></option>
                            <option value="large"><?php esc_html_e('Large (3/4 size)','print-my-blog' );?></option>
                            <option value="medium" ><?php esc_html_e('Medium (1/2 size)', 'print-my-blog'); ?></option>
                            <option value="small"><?php esc_html_e('Small (1/4 size)', 'print-my-blog'); ?></option>
                            <option value="none"><?php esc_html_e('None (hide images)', 'print-my-blog'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('If you want to save paper, choose a smaller image size, or hide images altogether. On the other hand, deafult size images often look the best.','print-my-blog' );?></p>
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
        <button class="button-primary"><?php esc_html_e('Prepare Print-Page','print-my-blog' );?></button>
    </form>
</div>