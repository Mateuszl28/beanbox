<?php
/**
 * Blok „FAQ” – pytania z CPT „faq” (Repeater to w ACF funkcja PRO – nie uzależniamy od niej motywu) w akordeonie <details>,
 * plus dane strukturalne FAQPage (JSON-LD), dzięki którym Google może pokazać pytania w wynikach.
 * Pola: title (nagłówek), count (liczba pytań 1–20).
 *
 * @package BeanBox
 */

$beanbox_heading = get_field( 'title' );
$beanbox_count   = (int) ( get_field( 'count' ) ?: 6 );
$beanbox_faqs    = get_posts(
	array(
		'post_type'      => 'faq',
		'posts_per_page' => max( 1, min( 20, $beanbox_count ) ),
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
	)
);
$beanbox_id      = ! empty( $block['anchor'] ) ? $block['anchor'] : 'faq-' . $block['id'];
?>
<section id="<?php echo esc_attr( $beanbox_id ); ?>" class="faq section">
	<div class="container container--narrow">
		<?php if ( $beanbox_heading ) : ?>
			<h2 class="section-title"><?php echo esc_html( $beanbox_heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $beanbox_faqs ) : ?>
			<div class="faq__list">
				<?php foreach ( $beanbox_faqs as $beanbox_faq ) : ?>
					<details class="faq__item">
						<summary class="faq__question"><?php echo esc_html( get_the_title( $beanbox_faq ) ); ?></summary>
						<div class="faq__answer"><?php echo wp_kses_post( wpautop( $beanbox_faq->post_content ) ); ?></div>
					</details>
				<?php endforeach; ?>
			</div>

			<?php
			if ( ! $is_preview ) :
				$beanbox_schema = array(
					'@context'   => 'https://schema.org',
					'@type'      => 'FAQPage',
					'mainEntity' => array_map(
						static fn( $f ) => array(
							'@type'          => 'Question',
							'name'           => wp_strip_all_tags( get_the_title( $f ) ),
							'acceptedAnswer' => array(
								'@type' => 'Answer',
								'text'  => wp_strip_all_tags( $f->post_content ),
							),
						),
						$beanbox_faqs
					),
				);
				// wp_json_encode escapuje znaki specjalne (w tym </script>) – bezpieczne w <script>.
				printf( '<script type="application/ld+json">%s</script>', wp_json_encode( $beanbox_schema, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) );
			endif;
			?>
		<?php elseif ( $is_preview ) : ?>
			<p><em><?php esc_html_e( 'Dodaj pytania w menu „FAQ”.', 'beanbox' ); ?></em></p>
		<?php endif; ?>
	</div>
</section>
