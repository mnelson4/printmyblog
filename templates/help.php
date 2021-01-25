<?php
/**
* @var $form \Twine\forms\base\FormSection
 * @var $form_url string
 */
?>
<div class="pmb-after-trouble">
    <h2><?php esc_html_e('We’re Here to Help!', 'print-my-blog');?></h2>
    <p><?php printf(
            esc_html__('Please %1$stake a look at our user guide%2$s for quick answers. If that doesn’t contain the info you need, please get in touch.', 'print-my-blog'),
        '<a href="https://printmy.blog/user-guide/" target="_blank">',
        '</a>'
        );?></p>
    <form method="POST" action="<?php echo esc_attr($form_url);?>">
    <?php echo $form->getHtmlAndJs();?>
    <div>
        <button class="button button-primary"><?php esc_html_e('Email Print My Blog Support', 'print-my-blog');?></button>
    </div>
    </form>
</div>