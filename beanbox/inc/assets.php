<?php
/**
 * Style i skrypty motywu.
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akcja wp_enqueue_scripts – CSS i JS motywu (wersja = czas modyfikacji pliku, żeby omijać cache).
 */
function beanbox_assets(): void {
	$css = get_theme_file_path( 'assets/css/main.css' );
	$js  = get_theme_file_path( 'assets/js/main.js' );

	wp_enqueue_style( 'beanbox-main', get_theme_file_uri( 'assets/css/main.css' ), array(), (string) filemtime( $css ) );
	wp_enqueue_script(
		'beanbox-main',
		get_theme_file_uri( 'assets/js/main.js' ),
		array(),
		(string) filemtime( $js ),
		array( 'strategy' => 'defer', 'in_footer' => true )
	);
}
add_action( 'wp_enqueue_scripts', 'beanbox_assets' );
