<?php
/**
 * Pojedyncza strona (także Koszyk, Zamówienie, Moje konto WooCommerce).
 *
 * @package BeanBox
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'container section page-content' ); ?>>
		<?php if ( ! is_front_page() ) : ?>
			<h1 class="page-title"><?php the_title(); ?></h1>
		<?php endif; ?>
		<div class="entry-content"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
