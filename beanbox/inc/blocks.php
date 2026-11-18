<?php
/**
 * Bloki ACF motywu i ograniczenie listy bloków w edytorze.
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akcja init – rejestruje automatycznie każdy katalog z block.json w blocks/.
 */
function beanbox_register_blocks(): void {
	foreach ( glob( get_theme_file_path( 'blocks/*/block.json' ) ) as $file ) {
		register_block_type( dirname( $file ) );
	}
}
add_action( 'init', 'beanbox_register_blocks' );

/**
 * Filtr block_categories_all – własna kategoria „BeanBox” na początku listy.
 *
 * @param array $categories Kategorie bloków.
 */
function beanbox_block_categories( array $categories ): array {
	array_unshift(
		$categories,
		array(
			'slug'  => 'beanbox',
			'title' => __( 'BeanBox', 'beanbox' ),
			'icon'  => null,
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'beanbox_block_categories' );

/**
 * Filtr allowed_block_types_all – klient widzi tylko nasze bloki i kilka podstawowych.
 * Strony WooCommerce (koszyk, zamówienie, konto) zachowują swoje bloki/shortcody.
 *
 * @param bool|string[]           $allowed Dozwolone bloki.
 * @param WP_Block_Editor_Context $context Kontekst edytora.
 * @return bool|string[]
 */
function beanbox_allowed_blocks( $allowed, $context ) {
	if ( ! empty( $context->post ) && function_exists( 'wc_get_page_id' ) ) {
		$woo_pages = array( wc_get_page_id( 'cart' ), wc_get_page_id( 'checkout' ), wc_get_page_id( 'myaccount' ) );
		if ( in_array( (int) $context->post->ID, $woo_pages, true ) ) {
			return $allowed;
		}
	}

	return array(
		'acf/hero',
		'acf/usp',
		'acf/opinie',
		'acf/faq',
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/image',
		'core/shortcode', // [woocommerce_cart] itp. na zwykłych stronach
	);
}
add_filter( 'allowed_block_types_all', 'beanbox_allowed_blocks', 10, 2 );
