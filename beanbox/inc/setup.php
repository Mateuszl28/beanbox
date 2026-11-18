<?php
/**
 * Konfiguracja motywu: wsparcie funkcji WordPressa i WooCommerce, menu.
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akcja after_setup_theme – deklaracje wsparcia.
 */
function beanbox_setup(): void {
	load_theme_textdomain( 'beanbox', get_theme_file_path( 'languages' ) );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array( 'height' => 64, 'width' => 220, 'flex-width' => true, 'flex-height' => true ) );

	// WooCommerce – bez tej deklaracji Woo uzna motyw za niekompatybilny i włączy tryb awaryjny.
	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600, // miniatury w liście produktów
			'single_image_width'    => 900, // główne zdjęcie w karcie produktu
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu główne', 'beanbox' ),
			'footer'  => __( 'Menu w stopce', 'beanbox' ),
		)
	);
}
add_action( 'after_setup_theme', 'beanbox_setup' );
