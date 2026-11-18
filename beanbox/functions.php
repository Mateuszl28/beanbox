<?php
/**
 * Funkcje motywu BeanBox – plik tylko dołącza moduły z inc/.
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Blokada bezpośredniego wywołania pliku.
}

require get_theme_file_path( 'inc/setup.php' );
require get_theme_file_path( 'inc/assets.php' );
require get_theme_file_path( 'inc/template-tags.php' );
require get_theme_file_path( 'inc/customizer.php' );
require get_theme_file_path( 'inc/post-types.php' );
require get_theme_file_path( 'inc/blocks.php' );
require get_theme_file_path( 'inc/performance.php' );

if ( class_exists( 'WooCommerce' ) ) {
	require get_theme_file_path( 'inc/woocommerce.php' );
}
