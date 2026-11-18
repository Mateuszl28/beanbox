<?php
/**
 * Strona 404.
 *
 * @package BeanBox
 */

get_header();
?>
<div class="container container--narrow section text-center">
	<h1 class="page-title"><?php esc_html_e( 'Tej strony nie ma w naszym menu', 'beanbox' ); ?></h1>
	<p><?php esc_html_e( 'Być może kawa wystygła, a link się zmienił.', 'beanbox' ); ?></p>
	<p><a class="btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Wróć na stronę główną', 'beanbox' ); ?></a></p>
</div>
<?php
get_footer();
