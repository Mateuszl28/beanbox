<?php
/**
 * Funkcje pomocnicze używane w szablonach.
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wartość z Customizera (sekcja „Dane sklepu”). Zwraca surowy tekst – escapujemy przy echo.
 *
 * @param string $key Klucz: promo, telefon, email, adres, facebook, instagram.
 */
function beanbox_option( string $key ): string {
	return (string) get_theme_mod( "beanbox_{$key}", '' );
}

/**
 * Gwiazdki oceny 1–5 (tekst + aria-label).
 *
 * @param int $rating Ocena.
 */
function beanbox_stars( int $rating ): string {
	$rating = max( 0, min( 5, $rating ) );
	return sprintf(
		'<span class="stars" role="img" aria-label="%1$s">%2$s</span>',
		/* translators: %d: ocena 1–5 */
		esc_attr( sprintf( __( 'Ocena %d na 5', 'beanbox' ), $rating ) ),
		esc_html( str_repeat( '★', $rating ) . str_repeat( '☆', 5 - $rating ) )
	);
}

/**
 * Pole ACF bez fatal error, gdy wtyczka ACF jest wyłączona.
 *
 * @param string    $name    Nazwa pola.
 * @param int|mixed $post_id ID wpisu / obiekt / 'option'.
 * @return mixed
 */
function beanbox_field( string $name, $post_id = false ) {
	return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : null;
}
