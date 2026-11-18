<?php
/**
 * Karta wpisu w listach.
 *
 * @package BeanBox
 */

?>
<article <?php post_class( 'card' ); ?>>
	<?php if ( has_post_thumbnail() ) : ?>
		<a class="card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true">
			<?php the_post_thumbnail( 'medium_large' ); ?>
		</a>
	<?php endif; ?>
	<div class="card__body">
		<h2 class="card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p class="card__meta"><?php echo esc_html( get_the_date() ); ?></p>
		<div class="card__text"><?php the_excerpt(); ?></div>
	</div>
</article>
