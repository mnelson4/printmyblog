<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
*
 */
?>
<div class="pmb-posts-header">
	<?php
    if (in_array('title',$pmb_design->getPmbMeta('header_content'))) { ?>
        <h1 class="site-title" id="dotEPUBtitle"><?php echo $pmb_project->getPublishedTitle(); ?></h1>
	<?php } ?>
	<?php if ($pmb_show_site_tagline) { ?>
        <p class="site-description"><?php echo $pmb_site_description; ?></p>
	<?php } ?>
    <p class="pmb-printout-meta">
		<?php
		//give it some space
		echo ' ';
		if ($pmb_show_date_printed && $pmb_show_credit) {

			printf(
			// translators: 1: date, 2: opening link tag, 3: closing link tag
				esc_html__('Printed on %1$s using %2$sPrint My Blog%3$s', 'print-my-blog'),
				date_i18n(get_option('date_format')),
				'<a href="https://wordpress.org/plugins/print-my-blog/">',
				'</a>'
			);
		} elseif ($pmb_show_date_printed) {
			// translators: 1: date
			printf(
				esc_html__('Printed on %1$s', 'print-my-blog'),
				date_i18n(get_option('date_format'))
			);
		} elseif ($pmb_show_credit) {
			printf(
				esc_html__('Printed using %1$sPrint My Blog%2$s', 'print-my-blog'),
				'<a href="https://wordpress.org/plugins/print-my-blog/">',
				'</a>'
			);
		}
		?></p>