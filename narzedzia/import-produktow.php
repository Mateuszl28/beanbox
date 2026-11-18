<?php
/**
 * Import produktów z dane/produkty_woocommerce.csv – ten sam importer co „Produkty → Importuj” w panelu.
 *   bash narzedzia/wp.sh eval-file narzedzia/import-produktow.php "<ścieżka do CSV>" --user=admin
 * (--user jest potrzebne: importer tworzy nowe kategorie tylko z uprawnieniem manage_product_terms)
 * Bez drugiego argumentu tworzy nowe produkty (istniejące SKU pomija); z argumentem „1” aktualizuje istniejące.
 *
 * @package BeanBox
 */

$csv = $args[0] ?? '';
if ( ! is_readable( $csv ) ) {
	WP_CLI::error( "Brak pliku CSV: {$csv}" );
}
require_once WC_ABSPATH . 'includes/import/class-wc-product-csv-importer.php';

// Mapowanie nagłówek CSV → pole importera (w panelu to krok „Mapowanie kolumn”).
$naglowki = str_getcsv( strtok( file_get_contents( $csv ), "\n" ) ); // phpcs:ignore
$mapa     = array(
	'ID' => 'id', 'Type' => 'type', 'SKU' => 'sku', 'Name' => 'name', 'Published' => 'published',
	'Is featured?' => 'featured', 'Visibility in catalog' => 'catalog_visibility',
	'Short description' => 'short_description', 'Description' => 'description', 'Tax status' => 'tax_status',
	'In stock?' => 'stock_status', 'Stock' => 'stock_quantity', 'Regular price' => 'regular_price',
	'Categories' => 'category_ids', 'Tags' => 'tag_ids', 'Images' => 'images', 'Parent' => 'parent_id',
	'Attribute 1 name' => 'attributes:name1', 'Attribute 1 value(s)' => 'attributes:value1',
	'Attribute 1 visible' => 'attributes:visible1', 'Attribute 1 global' => 'attributes:taxonomy1',
	'Attribute 1 default' => 'attributes:default1',
);
$mapping = array();
foreach ( $naglowki as $h ) {
	$mapping[ $h ] = $mapa[ $h ] ?? ( str_starts_with( $h, 'Meta: ' ) ? 'meta:' . substr( $h, 6 ) : '' );
}

$importer = new WC_Product_CSV_Importer(
	$csv,
	array(
		'parse'            => true,
		'update_existing'  => (bool) ( $args[1] ?? false ), // drugi argument „1” = aktualizuj istniejące
		'prevent_timeouts' => false,
		'lines'            => -1,
		'mapping'          => $mapping,
		'delimiter'        => ',',
	)
);
$wynik = $importer->import();
foreach ( array( 'imported', 'updated', 'failed', 'skipped' ) as $k ) {
	WP_CLI::log( sprintf( '%-9s %d', $k, count( $wynik[ $k ] ?? array() ) ) );
}
foreach ( array_merge( $wynik['failed'] ?? array(), array_slice( $wynik['skipped'] ?? array(), 0, 3 ) ) as $e ) {
	WP_CLI::warning( is_wp_error( $e ) ? $e->get_error_message() : print_r( $e, true ) ); // phpcs:ignore
}
