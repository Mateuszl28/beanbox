<?php
/**
 * Blok „Opinie klientów” – dane z CPT „opinia”.
 * Pola: title (nagłówek), count (liczba opinii 1–12), min_rating (minimalna ocena).
 *
 * @package BeanBox
 */

$beanbox_heading = get_field( 'title' );
$beanbox_count   = (int) ( get_field( 'count' ) ?: 3 );
$beanbox_min     = (int) ( get_field( 'min_rating' ) ?: 1 );

$beanbox_query = new WP_Query(
	array(
		'post_type'           => 'opinia',
		'posts_per_page'      => max( 1, min( 12, $beanbox_count ) ),
		'no_found_rows'       => true,
		'ignore_sticky_posts' => true,
		'meta_query'          => array( // phpcs:ignore WordPress.DB.SlowDBQuery -- mała liczba wpisów.
			array(
				'key'     => 'ocena',
				'value'   => $beanbox_min,
				'compare' => '>=',
				'type'    => 'NUMERIC',
			),
		),
	)
);
$beanbox_id = ! empty( $block['anchor'] ) ? $block['anchor'] : 'opinie-' . $block['id'];
?>
<section id="<?php echo esc_attr( $beanbox_id ); ?>" class="opinie section section--alt">
	<div class="container">
		<?php if ( $beanbox_heading ) : ?>
			<h2 class="section-title"><?php echo esc_html( $beanbox_heading ); ?></h2>
		<?php endif; ?>

		<?php if ( $beanbox_query->have_posts() ) : ?>
			<div class="opinie-grid">
				<?php
				while ( $beanbox_query->have_posts() ) :
					$beanbox_query->the_post();
					get_template_part( 'template-parts/opinia', 'card' );
				endwhile;
				wp_reset_postdata();
				?>
			</div>
			<p class="text-center">
				<a class="btn btn--ghost" href="<?php echo esc_url( get_post_type_archive_link( 'opinia' ) ); ?>"><?php esc_html_e( 'Wszystkie opinie', 'beanbox' ); ?></a>
			</p>
		<?php elseif ( $is_preview ) : ?>
			<p><em><?php esc_html_e( 'Brak opinii spełniających warunki – dodaj je w menu „Opinie”.', 'beanbox' ); ?></em></p>
		<?php endif; ?>
	</div>
</section>
