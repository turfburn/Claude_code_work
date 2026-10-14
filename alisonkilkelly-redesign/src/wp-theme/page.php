<?php
/**
 * Pages. Pages set up by Appearance > AK Counseling Setup carry their own full-width
 * sections and are printed as-is. Any other page gets a standard title band + text column.
 */
get_header();
while ( have_posts() ) : the_post();
	if ( ak_has_designed_layout( get_post() ) ) :
		the_content();
	else : ?>
		<section class="page-hero">
		  <div class="wrap"><h1><?php the_title(); ?></h1></div>
		</section>
		<section class="section section--white">
		  <div class="wrap wrap--narrow prose"><?php the_content(); ?></div>
		</section>
	<?php endif;
endwhile;
get_footer();
