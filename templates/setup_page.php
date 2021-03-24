<?php
/**
 * @var $print_options PrintMyBlog\domain\PrintOptions
 * @var $displayer PrintMyBlog\services\display\FormInputs
 */

use PrintMyBlog\domain\PrintOptions;
use PrintMyBlog\services\display\FormInputs;
pmb_render_template('partials/free_header.php');
?>
<div class="wrap nosubsub">
<h1><?php esc_html_e('Print My Blog - Free Quick Print','print-my-blog' );?></h1>
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
					    ),
					    'html' => array(
						    'label' => esc_html__('HTML', 'print-my-blog'),
						    'help_text' => esc_html__('Easily copy-and-paste into another program like Microsoft Word or Google Docs. Note: this is not recommended for customizing printouts, as other programs usually format the content poorly. Instead, use Print My Blog’s CSS classes to remove or add content from printouts.', 'print-my-blog'),
						    'link' => 'https://wordpress.org/plugins/print-my-blog/#how%20do%20i%20remove%20post%20content%20from%20the%20printout%3F'

					    ),
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

        <details class="pmb-details">
            <summary class="pmb-reveal-options" id="pmb-reveal-main-options"><?php esc_html_e('Show More Print Options', 'print-my-blog'); ?></summary>
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

                    $statuses = [
                            'draft' => esc_html__('Draft' ),
                            'pending' => esc_html__( 'Pending Review' ),
                            'private' => esc_html__('Private'),
                            'password' => esc_html__('Password-Protected', 'print-my-blog'),
                            'publish' => esc_html__('Published'),
                            'future' => esc_html__('Scheduled'),
                            'trash' => esc_html__('Trash')
                    ];?>
                    <tr>
                        <th scope="row"><label><?php esc_html_e('Statuses','print-my-blog' );?></label></th>
                        <td>
                            <?php foreach($statuses as $value => $label ){
                               ?>
                                <input type="checkbox" name="statuses[]" value="<?php echo esc_attr($value);?>" id="<?php echo esc_attr($value);?>-id" <?php echo $value === 'publish' ? 'checked="checked"' : '';?>>
                            <label for="<?php echo esc_attr($value);?>-id"><?php echo $label;?></label><br>
                                <?php
                            }
                            ?>
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
                    <th scope="row"><?php esc_html_e('Posted After (and Including)', 'print-my-blog'); ?></th>
                    <td><input type="text" class="pmb-date" name="dates[after]"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Posted Before (and Including)', 'print-my-blog'); ?></th>
                    <td><input type="text" class="pmb-date" name="dates[before]"></td>
                </tr>
                </tbody>
            </table>

            <?php include('partials/display_options.php'); ?>

        </details>
        <input type="hidden" name="<?php echo PMB_PRINTPAGE_SLUG;?>" value="1">
        <button class="button-primary"><?php esc_html_e('Prepare Print-Page','print-my-blog' );?></button>
    </form>
</div>