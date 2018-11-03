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
    <form action="<?php echo site_url();?>" method="get">
        <a href="#" onclick="jQuery('.pmb-page-setup-options-advanced').toggle();"><?php esc_html_e('Show Options', 'print_my_blog'); ?></a><br/><br/>
        <div class="pmb-page-setup-options-advanced" style="display:none">
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
                        <option value="1"><?php esc_html_e('1','print_my_blog' );?></option>
                        <option value="2" selected="selected"><?php esc_html_e('2', 'print_my_blog'); ?></option>
                        <option value="3"><?php esc_html_e('3', 'print_my_blog'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('The number of columns of text on each page. 2-3 makes it look a bit like a newspaper and the content tends to be more .','print_my_blog' );?></p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="font-size"><?php esc_html_e('Font Size','print_my_blog' );?></label>
                </th>
                <td>
                    <select name="font-size" id="font-size">
                        <option value="tiny"><?php esc_html_e('Tiny (saves ink and paper)','print_my_blog' );?></option>
                        <option value="small" selected="selected"><?php esc_html_e('Small (newspaper size)', 'print_my_blog'); ?></option>
                        <option value="normal"><?php esc_html_e('Normal (matches size on web)', 'print_my_blog'); ?></option>
                        <option value="big"><?php esc_html_e('Big (for those with difficulty reading)', 'print_my_blog'); ?></option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <h2><?php esc_html_e('Images','print_my_blog' );?></h2>
        <table class="form-table">
            <tbody>
            <tr>
                <th scope="row">
                    <label for="image-size"><?php esc_html_e('Image Size','print_my_blog' );?></label>
                </th>
                <td>
                    <select name="image-size" id="image-size">
                        <option value="full"><?php esc_html_e('Full','print_my_blog' );?></option>
                        <option value="large"><?php esc_html_e('Large','print_my_blog' );?></option>
                        <option value="medium" selected="selected"><?php esc_html_e('Medium', 'print_my_blog'); ?></option>
                        <option value="small"><?php esc_html_e('Small', 'print_my_blog'); ?></option>
                        <option value="none"><?php esc_html_e('None (hide images)', 'print_my_blog'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('The number of columns of text on each page. 2-3 makes it look a bit like a newspaper and the content tends to be more .','print_my_blog' );?></p>
                </td>

            </tr>
            </tbody>
        </table>
        <input type="hidden" name="<?php echo PMB_PRINTPAGE_SLUG;?>" value="1">
        </div>
        <button class="button-primary"><?php esc_html_e('Prepare Print Page','printmyblog' );?></button>
    </form>
</div>