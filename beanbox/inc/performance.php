<?php
/**
 * Poprawki po audycie Lighthouse karty produktu (lab 3): meta description i wyłączenie skryptu emoji.
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akcja wp_head – <meta name="description"> (Lighthouse SEO: „Document does not have a meta description”).
 * Produkt → krótki opis, kategoria → hasło z banera / opis, pozostałe → opis witryny.
 */
function beanbox_meta_description(): void {
	$opis = '';
	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		$opis    = $product ? $product->get_short_description() : '';
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		$opis = beanbox_field( 'baner_tekst', $term ) ?: term_description( $term );
	} elseif ( is_singular() && has_excerpt() ) {
		$opis = get_the_excerpt();
	}
	if ( ! $opis ) {
		$opis = get_bloginfo( 'description' );
	}
	$opis = wp_trim_words( wp_strip_all_tags( (string) $opis ), 30, '…' );
	if ( $opis ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $opis ) );
	}
}
add_action( 'wp_head', 'beanbox_meta_description', 1 );

/**
 * Wyłączenie skryptu emoji WordPressa: podmieniał 🛒 i 🔍 na <img> z s.w.org bez width/height
 * (Lighthouse: „Image elements do not have explicit width and height”) i dokładał zapytania do zewnętrznego serwera.
 * Przeglądarki wyświetlają emoji same.
 */
function beanbox_disable_emoji(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'beanbox_disable_emoji' );
