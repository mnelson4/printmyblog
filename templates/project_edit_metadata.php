<?php
/**
 * @var $project_form \Twine\forms\base\FormSectionProper
 * @var $form_url string
 */
?>
<form method="POST" action="<?php echo esc_attr($form_url);?>">
    <?php echo $form->get_html_and_js();?>
    <button class="button button-primary pmb-save"><?php esc_html_e('Save', 'print-my-blog');?></button>
</form>