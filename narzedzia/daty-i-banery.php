<?php
/**
 * Daty dodania produktów (znaczek „Nowość” tylko dla produktów < 30 dni) i banery kategorii (pola ACF termu).
 *   bash narzedzia/wp.sh eval-file narzedzia/daty-i-banery.php --user=admin
 *
 * @package BeanBox
 */

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$nowe = array( 'P006', 'P013', 'P020', 'P025' ); // „nowości” w ofercie
foreach ( wc_get_products( array( 'limit' => -1, 'type' => array( 'simple', 'variable' ) ) ) as $p ) {
	$dni  = in_array( $p->get_sku(), $nowe, true ) ? wp_rand( 2, 20 ) : wp_rand( 45, 120 );
	$data = gmdate( 'Y-m-d H:i:s', strtotime( "-{$dni} days" ) );
	wp_update_post( array( 'ID' => $p->get_id(), 'post_date' => get_date_from_gmt( $data ), 'post_date_gmt' => $data ) );
}
WP_CLI::log( 'Daty produktów ustawione; nowości: ' . implode( ', ', $nowe ) );

$hasla = array(
	'kawa'      => 'Świeżo palona speciality – z datą palenia na każdej torebce',
	'akcesoria' => 'Wszystko do V60, Chemexa i Aeropressu',
	'sprzet'    => 'Młynki i ekspresy dla domowego baristy',
);
$dir = wp_get_upload_dir();
foreach ( $hasla as $slug => $haslo ) {
	$term = get_term_by( 'slug', $slug, 'product_cat' );
	if ( ! $term ) {
		continue;
	}
	$plik = $dir['basedir'] . "/beanbox-import/baner-{$slug}.jpg";
	$tmp  = wp_tempnam( "baner-{$slug}.jpg" );
	copy( $plik, $tmp );
	$att = media_handle_sideload( array( 'name' => "baner-{$slug}.jpg", 'tmp_name' => $tmp ), 0, "Baner kategorii {$term->name}" );
	if ( is_wp_error( $att ) ) {
		WP_CLI::warning( $att->get_error_message() );
		continue;
	}
	update_post_meta( $att, '_wp_attachment_image_alt', "Baner kategorii {$term->name}" );
	update_field( 'baner', $att, $term );
	update_field( 'baner_tekst', $haslo, $term );
	wp_update_term( $term->term_id, 'product_cat', array( 'description' => '' ) );
	WP_CLI::log( "Baner: {$term->name} (#{$att})" );
}
