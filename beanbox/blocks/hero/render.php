<?php
/**
 * Blok „Hero”. ACF przekazuje $block (ustawienia bloku) i $is_preview (podgląd w edytorze).
 * Pola: title (tekst), text (obszar tekstowy), image (obrazek, format ID), button (link, tablica).
 *
 * @package BeanBox
 */

$beanbox_title  = get_field( 'title' );
$beanbox_text   = get_field( 'text' );
$beanbox_image  = get_field( 'image' );  // ID załącznika
$beanbox_button = get_field( 'button' ); // tablica: url, title, target
$beanbox_id     = ! empty( $block['anchor'] ) ? $block['anchor'] : 'hero-' . $block['id'];
?>
<section id="<?php echo esc_attr( $beanbox_id ); ?>" class="hero<?php echo $beanbox_image ? ' hero--image' : ''; ?>">
	<?php if ( $beanbox_image ) : ?>
		<?php echo wp_get_attachment_image( $beanbox_image, 'full', false, array( 'class' => 'hero__bg', 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?>
	<?php endif; ?>
	<div class="container hero__content">
		<?php if ( $beanbox_title ) : ?>
			<h1 class="hero__title"><?php echo esc_html( $beanbox_title ); ?></h1>
		<?php elseif ( $is_preview ) : ?>
			<p><em><?php esc_html_e( 'Uzupełnij tytuł w panelu bloku →', 'beanbox' ); ?></em></p>
		<?php endif; ?>

		<?php if ( $beanbox_text ) : ?>
			<p class="hero__text"><?php echo esc_html( $beanbox_text ); ?></p>
		<?php endif; ?>

		<?php if ( $beanbox_button && ! empty( $beanbox_button['url'] ) ) : ?>
			<a class="btn btn--light" href="<?php echo esc_url( $beanbox_button['url'] ); ?>"
				target="<?php echo esc_attr( $beanbox_button['target'] ?: '_self' ); ?>">
				<?php echo esc_html( $beanbox_button['title'] ?: __( 'Zobacz', 'beanbox' ) ); ?>
			</a>
		<?php endif; ?>
	</div>
</section>
