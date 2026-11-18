<?php
/**
 * Pojedyncza opinia: /opinie/{slug}/
 *
 * @package BeanBox
 */

get_header();

while ( have_posts() ) :
	the_post();
	$beanbox_ocena   = (int) beanbox_field( 'ocena' );
	$beanbox_miasto  = beanbox_field( 'miasto' );
	$beanbox_produkt = beanbox_field( 'produkt' );
	?>
	<article <?php post_class( 'container container--narrow section opinia-single' ); ?>>
		<p class="breadcrumbs"><a href="<?php echo esc_url( get_post_type_archive_link( 'opinia' ) ); ?>">← <?php esc_html_e( 'Wszystkie opinie', 'beanbox' ); ?></a></p>
		<h1 class="page-title"><?php the_title(); ?></h1>
		<?php if ( $beanbox_ocena ) : ?>
			<?php echo beanbox_stars( $beanbox_ocena ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapowane w beanbox_stars(). ?>
		<?php endif; ?>
		<?php if ( $beanbox_miasto ) : ?>
			<p class="opinia-card__city"><?php echo esc_html( $beanbox_miasto ); ?></p>
		<?php endif; ?>
		<div class="entry-content opinia-single__text"><?php the_content(); ?></div>
		<?php if ( $beanbox_produkt ) : ?>
			<p class="opinia-card__product">
				<?php esc_html_e( 'Opinia o produkcie:', 'beanbox' ); ?>
				<a href="<?php echo esc_url( get_permalink( $beanbox_produkt ) ); ?>"><?php echo esc_html( get_the_title( $beanbox_produkt ) ); ?></a>
			</p>
		<?php endif; ?>
	</article>
	<?php
endwhile;

get_footer();
