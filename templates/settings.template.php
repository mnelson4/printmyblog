<div>
<h1><?php esc_html_e('Print My Blog','event_espresso' );?></h1>
    <p><?php esc_html_e('We will prepare a page with all your posts, which you can then print.','event_espresso' );?></p>
    <form action="<?php echo site_url();?>" method="get">
        <input type="hidden" name="<?php echo PMG_PRINTPAGE_SLUG;?>" value="1">
        <button><?php esc_html_e('Begin Preparing Print Page','event_espresso' );?></button>
    </form>
</div>