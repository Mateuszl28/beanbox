<?php
/**
 * Strona główna – treść to WYŁĄCZNIE bloki ACF ułożone w edytorze (hero, przewagi, opinie, FAQ).
 * Szablon nie dodaje własnego HTML poza obudową.
 *
 * @package BeanBox
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="front-page">
		<?php the_content(); ?>
	</div>
	<?php
endwhile;

get_footer();
