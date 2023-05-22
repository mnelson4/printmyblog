<?php
/**
 * @var $formats \PrintMyBlog\entities\FileFormat[]
 * @var $designs \PrintMyBlog\orm\entities\Design[]
 * @var $config \PrintMyBlog\services\config\Config
 */
?>
<div class="wrap nosubsub">
    <h1 class="wp-heading-inline"><?php _e('Pro Print ― Designs', 'print-my-blog');?></h1>
    <p><?php _e('Designs in Print My Blog are like Themes in WordPress: their settings affect how the projects are displayed. Edit them below or change which ones are used by default.', 'print-my-blog');?></p>
<?php
foreach($formats as $format){
    ?>
    <h2><?php echo $format->coloredTitleAndIcon();?></h2>
    <p><?php echo $format->desc();?></p>
<?php
    $designs_for_format = $designs[$format->slug()];
    pmb_render_template(
        'partials/select_designs.php',
        [
            'format' => $format,
            'designs'=> $designs_for_format,
            'chosen_design' => $config->getDefaultDesignFor($format),
            'active_text' => __('<span>Default:</span> %s', 'print-my-blog'),
            'select_button_text' => esc_html__('Make Default', 'print-my-blog'),
            'select_button_aria_label' => esc_html__('Make "%s" the Default Design for its Format', 'print-my-blog'),
            'customize_button_aria_label' => esc_html__(' Customize the Design "%s"', 'print-my-blog'),
        ]
    );
}
?>
</div>
