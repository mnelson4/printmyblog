<div class="wrap nosubsub">
<h1><?php esc_html_e('Print My Blog','printmyblog' );?></h1>
    <?php if(isset($_GET['welcome'])){
        ?>
        <div class="updated fade">
            <p>
                <?php esc_html_e('Welcome! This is where you begin preparing your blog for printing. You can get here from the left-hand menu, under "Tools", then "Print My Blog."','printmyblog' );?>
            </p>
        </div>
    <?php
    }
    ?>
    <p><?php esc_html_e('Configure how you\'d like the blog to be printed, or just use our recommended defaults.', 'print_my_blog'); ?></p>
    <a href="" onclick="jQuery('.pmb-page-setup-options-advanced').toggle();return false;"><?php esc_html_e('Show Options', 'print_my_blog'); ?></a><br/><br/>
    <form action="<?php echo site_url();?>" method="get">
        <div class="pmb-page-setup-options-advanced" style="display:none">
            <h1><?php esc_html_e('Options','print_my_blog' );?></h1>
            <h2><?php esc_html_e('Content','print_my_blog' );?></h2>
            <table class="form-table">
                <tbody>
                <?php if(PMB_REST_PROXY_EXISTS){?>
                    <tr>
                        <th scope="row"><?php esc_html_e('Site URL (including "https://" or "http://")', 'print_my_blog');?></th>
                        <td>
                            <input name="site" placeholder="<?php echo site_url();?>">
                            <p class="description"><?php esc_html_e('URL of the WordPress site (self-hosted or on WordPress.com) you\'d like to print. Leave blank to use this current site.', 'print_my_blog');?></p>
                        </td>
                    </tr>
                <?php }?>
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Content to Print','print_my_blog' );?>
                    </th>
                    <td>
                        <label><input type="radio" name="post-type" value="post" checked="checked"><?php esc_html_e('Posts', 'print_my_blog');?></label>
                        <br>
                        <label><input type="radio" name="post-type" value="page"><?php esc_html_e('Pages', 'print_my_blog');?></label>
                    </td>

                </tr>
                <tr>
                    <th scope="row">
                        <label for="include-excerpts"><?php esc_html_e('Include Excerpts','print_my_blog' );?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="include-excerpts" value="1">
                        <p class="description"><?php esc_html_e('Whether to include the posts\'s excerpt before the rest of the content. Useful in case you put different content in there.','print_my_blog' );?></p>
                    </td>

                </tr>
                </tbody>
            </table>

            <h2><?php esc_html_e('Page Layout','print_my_blog' );?></h2>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="post-page-break"><?php esc_html_e('Each Post Begins on a New Page','print_my_blog' );?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="post-page-break" id="post-page-break" checked="checked">
                        <p class="description"><?php esc_html_e('Whether to force posts to always start on a new page. Doing so makes the page more legible, but uses more paper.','print_my_blog' );?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="columns"><?php esc_html_e('Columns','print_my_blog' );?></label>
                    </th>
                    <td>
                        <select name="columns" id="columns">
                            <option value="1" selected="selected"><?php esc_html_e('1','print_my_blog' );?></option>
                            <option value="2"><?php esc_html_e('2', 'print_my_blog'); ?></option>
                            <option value="3"><?php esc_html_e('3', 'print_my_blog'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('The number of columns of text on each page. Not supported by some web browsers.','print_my_blog' );?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="font-size"><?php esc_html_e('Font Size','print_my_blog' );?></label>
                    </th>
                    <td>
                        <select name="font-size" id="font-size">
                            <option value="tiny"><?php esc_html_e('Tiny (1/2 size)','print_my_blog' );?></option>
                            <option value="small"><?php esc_html_e('Small (3/4 size)', 'print_my_blog'); ?></option>
                            <option value="normal" selected="selected"><?php esc_html_e('Normal (theme default)', 'print_my_blog'); ?></option>
                            <option value="large"><?php esc_html_e('Large (slightly larger than normal)', 'print_my_blog'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="image-size"><?php esc_html_e('Image Size','print_my_blog' );?></label>
                    </th>
                    <td>
                        <select name="image-size" id="image-size">
                            <option value="full" selected="selected"><?php esc_html_e('Full (theme default)','print_my_blog' );?></option>
                            <option value="large"><?php esc_html_e('Large (3/4 size)','print_my_blog' );?></option>
                            <option value="medium" ><?php esc_html_e('Medium (1/2 size)', 'print_my_blog'); ?></option>
                            <option value="small"><?php esc_html_e('Small (1/4 size)', 'print_my_blog'); ?></option>
                            <option value="none"><?php esc_html_e('None (hide images)', 'print_my_blog'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('If you want to save paper, choose a smaller image size, or hide images altogether. On the other hand, deafult size images often look the best.','print_my_blog' );?></p>
                    </td>
                </tr>
                </tbody>
            </table>

            <a href="" onclick="jQuery('#pmb-troubleshooting-options').toggle();return false;"><?php esc_html_e('Show Troubleshooting Options', 'print_my_blog'); ?></a><br/><br/>

            <div id="pmb-troubleshooting-options" style="display:none">
                <h2><?php esc_html_e('Troubleshooting Options','print_my_blog' );?></h2>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <label for="rendering-wait"><?php esc_html_e('Post Rendering Wait-Time','print_my_blog' );?></label>
                        </th>
                        <td>
                            <input name="rendering-wait" value="500"><?php esc_html_e('ms','print_my_blog' );?>
                            <p class="description"><?php esc_html_e('Milliseconds to wait between rendering posts. If posts are rendered too quickly on the page, sometimes images won\'t load properly. ','print_my_blog' );?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="include-inline-js"><?php esc_html_e('Include Inline Javascript','print_my_blog' );?></label>
                        </th>
                        <td>
                            <input type="checkbox" name="include-inline-js" value="1">
                            <p class="description"><?php esc_html_e('Sometimes posts contain inline javascript which can cause errors and stop the page from rendering.','print_my_blog' );?></p>
                        </td>

                    </tr>
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="<?php echo PMB_PRINTPAGE_SLUG;?>" value="1">
        </div>
        <button class="button-primary"><?php esc_html_e('Prepare Print Page','printmyblog' );?></button>
    </form>
</div>