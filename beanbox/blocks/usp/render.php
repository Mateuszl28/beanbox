<?php
/**
 * Blok „Przewagi (USP)”. Repeater to w ACF funkcja PRO – stąd 4 grupy pól usp_1…usp_4
 * (każda: icon – wybór, title – tekst, text – obszar tekstowy).
 *
 * @package BeanBox
 */

$beanbox_heading = get_field( 'title' );
$beanbox_icons   = array(
	'bean'     => '☕',
	'truck'    => '🚚',
	'fire'     => '🔥',
	'leaf'     => '🌱',
	'gift'     => '🎁',
	'return'   => '↩',
);
$beanbox_items   = array();
for ( $beanbox_i = 1; $beanbox_i <= 4; $beanbox_i++ ) {
	$beanbox_item = get_field( "usp_{$beanbox_i}" );
	if ( ! empty( $beanbox_item['title'] ) ) {
		$beanbox_items[] = $beanbox_item;
	}
}
$beanbox_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'usp-' . $block['id'];
?>
<section id="<?php echo esc_attr( $beanbox_id ); ?>" class="usp section">
	<div class="container">
		<?php if ( $beanbox_heading ) : ?>
			<h2 class="section-title"><?php echo esc_html( $beanbox_heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $beanbox_items ) : ?>
			<ul class="usp__grid">
				<?php foreach ( $beanbox_items as $beanbox_item ) : ?>
					<li class="usp__item">
						<span class="usp__icon" aria-hidden="true"><?php echo esc_html( $beanbox_icons[ $beanbox_item['icon'] ] ?? '☕' ); ?></span>
						<h3 class="usp__title"><?php echo esc_html( $beanbox_item['title'] ); ?></h3>
						<?php if ( ! empty( $beanbox_item['text'] ) ) : ?>
							<p class="usp__text"><?php echo esc_html( $beanbox_item['text'] ); ?></p>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php elseif ( $is_preview ) : ?>
			<p><em><?php esc_html_e( 'Uzupełnij co najmniej jedną przewagę w panelu bloku →', 'beanbox' ); ?></em></p>
		<?php endif; ?>
	</div>
</section>
