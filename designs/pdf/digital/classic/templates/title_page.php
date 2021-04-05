<?php
/**
 * @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
*
 */
?>
<div class="pmb-posts-header">
	<?php
    if (in_array('title',$pmb_design->getSetting('header_content'))) {
        ?>
        <h1 class="site-title"><?php echo $pmb_project->getPublishedTitle(); ?></h1>
	    <?php
    }
    if (in_array('subtitle',$pmb_design->getSetting('header_content'))) {
        ?>
        <p class="site-description pmb-subtitle"><?php echo $pmb_project->getSetting('subtitle'); ?></p>
	    <?php
    }
    if (in_array('byline',$pmb_design->getSetting('header_content'))) {
        ?>
    <h2 class="project-byline"><?php echo $pmb_project->getPmbMeta('byline'); ?></h2>
    <?php
    }
    if(in_array('url', $pmb_design->getSetting('header_content'))){
        $url_text = $pmb_project->getSetting('url');
        $true_url = esc_url_raw($url_text) === $url_text;
        ?>
        <p class="site-url "><?php
            if($true_url){
                ?>
                <a href="<?php echo esc_attr($url_text);?>">
                <?php
            }
            echo $url_text;
            if($true_url){
                ?>
                </a>
                <?php
            }
            ?></p>
        <?php
	}
	?>
    <p class="pmb-printout-meta">
		<?php
		//give it some space
		echo ' ';
		if (in_array('date_printed',$pmb_design->getSetting('header_content')) &&
		    in_array('credit_pmb',$pmb_design->getSetting('header_content'))) {

			printf(
			// translators: 1: date, 2: opening link tag, 3: closing link tag
				esc_html__('Printed on %1$s using %2$sPrint My Blog%3$s', 'print-my-blog'),
				date_i18n(get_option('date_format')),
				'<a href="https://printmy.blog">',
				'</a>'
			);
		} elseif (in_array('date_printed',$pmb_design->getSetting('header_content'))) {
			// translators: 1: date
			printf(
				esc_html__('Printed on %1$s', 'print-my-blog'),
				date_i18n(get_option('date_format'))
			);
		} elseif (in_array('credit_pmb',$pmb_design->getSetting('header_content'))) {
			printf(
				esc_html__('Printed using %1$sPrint My Blog%2$s', 'print-my-blog'),
				'<a href="https://printmy.blog">',
				'</a>'
			);
		}
		?></p>