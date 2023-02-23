<?php
/**
* @var $pmb_project \PrintMyBlog\orm\entities\Project
 * @var $pmb_design \PrintMyBlog\orm\entities\Design
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
	<link rel="profile" href="http://gmpg.org/xfn/11">
    <style type="text/css">
        @page Section1 {
            mso-header-margin:0.5in;
            mso-header: h1;
            mso-footer-margin:0.5in;
            mso-footer: f1;
        }

        div.Section1 {page:Section1;}

        p.headerFooter { margin:0in; text-align: center; }
    </style>


    <title><?php echo $pmb_project->getPublishedTitle();?></title>
	<?php
	// @todo: we should instead render this after we've fetched all the posts and found all the scripts they depend on
	wp_head();
	?>
</head>