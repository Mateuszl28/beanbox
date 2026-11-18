<?php
/**
 * Karta opinii – używana w archiwum /opinie/ i w bloku „Opinie klientów”.
 * Pola ACF: ocena (1–5), miasto (tekst), produkt (ID produktu WooCommerce).
 *
 * @package BeanBox
 */

$beanbox_id      = get_the_ID();
$beanbox_ocena   = (int) beanbox_field( 'ocena', $beanbox_id );
$beanbox_miasto  = beanbox_field( 'miasto', $beanbox_id );
$beanbox_produkt = beanbox_field( 'produkt', $beanbox_id );
?>
<article class="opinia-card">
	<?php if ( $beanbox_ocena ) : ?>
		<?php echo beanbox_stars( $beanbox_ocena ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapowane w beanbox_stars(). ?>
	<?php endif; ?>
	<blockquote class="opinia-card__text"><?php echo wp_kses_post( wp_trim_words( get_the_content(), 40 ) ); ?></blockquote>
	<p class="opinia-card__author">
		<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
		<?php if ( $beanbox_miasto ) : ?>
			<span class="opinia-card__city"><?php echo esc_html( $beanbox_miasto ); ?></span>
		<?php endif; ?>
	</p>
	<?php if ( $beanbox_produkt ) : ?>
		<p class="opinia-card__product">
			<?php esc_html_e( 'O produkcie:', 'beanbox' ); ?>
			<a href="<?php echo esc_url( get_permalink( $beanbox_produkt ) ); ?>"><?php echo esc_html( get_the_title( $beanbox_produkt ) ); ?></a>
		</p>
	<?php endif; ?>
</article>
