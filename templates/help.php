<?php
/**
* @var $form \Twine\forms\base\FormSection
 * @var $form_url string
 */
?>
<div class="pmb-after-trouble">
    <h2><?php esc_html_e('Sorry Something’s Not Right!', 'print-my-blog');?></h2>
    <form method="POST" action="<?php echo esc_attr($form_url);?>">
    <?php echo $form->getHtmlAndJs();?>
    <div>
        <button class="button button-primary"><?php esc_html_e('Email Print My Blog Support', 'print-my-blog');?></button>
    </div>
    </form>
</div>