<?php
/**
 * Pojedynczy wpis bloga.
 *
 * @package BeanBox
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'container container--narrow section' ); ?>>
		<h1 class="page-title"><?php the_title(); ?></h1>
		<p class="card__meta"><?php echo esc_html( get_the_date() ); ?></p>
		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="single__media"><?php the_post_thumbnail( 'large' ); ?></figure>
		<?php endif; ?>
		<div class="entry-content"><?php the_content(); ?></div>
	</article>
	<?php
endwhile;

get_footer();
