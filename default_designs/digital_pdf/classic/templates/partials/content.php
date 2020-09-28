<div class="post-inner">
	<?php
	if ( has_post_thumbnail() ) {
		the_post_thumbnail('large');
	}
	?>
	<div class="entry-content post-content">
		<?php the_content();?>
	</div><!-- .entry-content -->

</div><!-- .post-inner -->