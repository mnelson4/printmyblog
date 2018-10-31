<div class="wrap nosubsub">
<h1><?php esc_html_e('Print My Blog','event_espresso' );?></h1>
    <?php if(isset($_GET['welcome'])){
        ?>
        <div class="updated fade">
            <p>
                <?php esc_html_e('Welcome! This is where you begin preparing your blog for printing. You can get here from the left-hand menu, under "Tools", then "Print My Blog."','event_espresso' );?>
            </p>
        </div>
    <?php
    }
    ?>
    <h2><?php esc_html_e('Setup','event_espresso' );?></h2>
    <p><?php esc_html_e('Someday, there will be setup options here. Right now, there are none!','event_espresso' );?></p>
    <form action="<?php echo site_url();?>" method="get">
        <input type="hidden" name="<?php echo PMB_PRINTPAGE_SLUG;?>" value="1">
        <button class="button-primary"><?php esc_html_e('Prepare Print Page','event_espresso' );?></button>
    </form>
</div>