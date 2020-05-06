<?php
/**
 * @var WP_User $current_user
 */
?>
<div class="notice notice-info pmb-pro-notice" id="pmb-pro-notice">
    <script>
        function pmb_hide_pro_notice(){
            document.getElementById("pmb-pro-notice").style.display = "none";
        }
    </script>
    <div class="image"><img src="<?php echo PMB_IMAGES_URL . 'pmb-book.gif';?>"></div>
    <div class="content">
        <h2>Write Professional Books and Documents Right in WordPress with Print My Blog Pro</h2>
        <p>Help make it the perfect tool for YOU by having your say.</p>
        <p>Signup to get involved and <a href="https://printmy.blog/2020/04/27/get-a-major-discount-for-print-my-blog-pro-by-becoming-a-founding-member/">get a major discount (or even a free lifetime license if you're quick!)</a></p>
        <form method="POST" action="<?php echo esc_url( "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);?>" target="_blank">
        <input type="hidden" name="pmb_pro_notice_signup" value="signup">
        <input type="text" name="name" value="<?php echo esc_attr($current_user->first_name);?>" placeholder="Your Name" required="required">
        <input type="email" name="email" value="<?php echo esc_attr($current_user->user_email);?>" required="required">
        <button class="button button-primary" onclick="pmb_hide_pro_notice()">Sign Me Up!</button>
            <a href="<?php
echo add_query_arg(
    [
        'pmb_pro_notice_dismiss' => 'now'
    ],
    "//" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
);
?>" class="button button-secondary">No Thanks</a>
        </form>

    </div>
</div>