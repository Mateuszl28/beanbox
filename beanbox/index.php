<?php
/**
 * Szablon awaryjny: lista wpisów (blog, archiwa, wyniki wyszukiwania).
 *
 * @package BeanBox
 */

get_header();
?>
<div class="container section">
	<header class="page-header">
		<h1 class="page-title">
			<?php
			if ( is_home() ) {
				esc_html_e( 'Blog', 'beanbox' );
			} elseif ( is_search() ) {
				/* translators: %s: wyszukiwana fraza */
				printf( esc_html__( 'Wyniki dla: %s', 'beanbox' ), esc_html( get_search_query() ) );
			} else {
				echo wp_kses_post( get_the_archive_title() );
			}
			?>
		</h1>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="card-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/content', 'card' );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nic tu jeszcze nie ma.', 'beanbox' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
