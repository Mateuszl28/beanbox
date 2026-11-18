<?php
/**
 * Archiwum opinii: /opinie/
 *
 * @package BeanBox
 */

get_header();
?>
<div class="container section">
	<header class="page-header">
		<h1 class="page-title"><?php esc_html_e( 'Opinie klientów', 'beanbox' ); ?></h1>
		<p class="page-lead"><?php esc_html_e( 'Co piszą o nas osoby, które piją naszą kawę.', 'beanbox' ); ?></p>
	</header>

	<?php if ( have_posts() ) : ?>
		<div class="opinie-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				get_template_part( 'template-parts/opinia', 'card' );
			endwhile;
			?>
		</div>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nie ma jeszcze opinii.', 'beanbox' ); ?></p>
	<?php endif; ?>
</div>
<?php
get_footer();
