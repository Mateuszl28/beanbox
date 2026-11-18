<?php
/**
 * Konfiguracja WooCommerce dla BeanBox (lab 3). Uruchomienie:
 *   bash narzedzia/wp.sh eval-file narzedzia/konfiguracja-woo.php
 * Skrypt jest idempotentny – można go uruchomić ponownie.
 *
 * @package BeanBox
 */

// --- Sklep: Polska, PLN, format cen „1 234,56 zł”, bez podatków (ceny brutto) ---
$opcje = array(
	'woocommerce_default_country'          => 'PL',
	'woocommerce_store_address'            => 'ul. Ziarnista 7',
	'woocommerce_store_city'               => 'Warszawa',
	'woocommerce_store_postcode'           => '00-950',
	'woocommerce_currency'                 => 'PLN',
	'woocommerce_currency_pos'             => 'right_space',
	'woocommerce_price_thousand_sep'       => ' ',
	'woocommerce_price_decimal_sep'        => ',',
	'woocommerce_price_num_decimals'       => 2,
	'woocommerce_calc_taxes'               => 'no',
	'woocommerce_weight_unit'              => 'kg',
	'woocommerce_dimension_unit'           => 'cm',
	'woocommerce_allowed_countries'        => 'specific',
	'woocommerce_specific_allowed_countries' => array( 'PL' ),
	'woocommerce_ship_to_countries'        => '',
	'woocommerce_enable_ajax_add_to_cart'  => 'yes', // AJAX w archiwach
	'woocommerce_cart_redirect_after_add'  => 'no',
	'woocommerce_enable_reviews'           => 'no',  // opinie mamy we własnym CPT
	'woocommerce_coming_soon'              => 'no',
	'woocommerce_store_pages_only'         => 'no',
	'woocommerce_onboarding_profile'       => array( 'skipped' => true ),
	'woocommerce_task_list_hidden'         => 'yes',
	'woocommerce_show_marketplace_suggestions' => 'no',
	'woocommerce_allow_tracking'           => 'no',
);
foreach ( $opcje as $k => $v ) {
	update_option( $k, $v );
}
WP_CLI::log( 'Opcje sklepu: PL, PLN, bez podatków, AJAX w archiwach.' );

// --- Strefa wysyłki „Polska”: stała stawka 15 zł + darmowa od 200 zł ---
$zone_id = 0;
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( 'Polska' === $z['zone_name'] ) {
		$zone_id = (int) $z['id'];
	}
}
$zone = new WC_Shipping_Zone( $zone_id ?: null );
$zone->set_zone_name( 'Polska' );
$zone->set_zone_order( 0 );
$zone->set_locations( array( array( 'code' => 'PL', 'type' => 'country' ) ) );
$zone->save();

$typy = wp_list_pluck( $zone->get_shipping_methods(), 'id' );
if ( ! in_array( 'flat_rate', $typy, true ) ) {
	$zone->add_shipping_method( 'flat_rate' );
}
if ( ! in_array( 'free_shipping', $typy, true ) ) {
	$zone->add_shipping_method( 'free_shipping' );
}
foreach ( $zone->get_shipping_methods() as $m ) {
	$key = "woocommerce_{$m->id}_{$m->instance_id}_settings";
	if ( 'flat_rate' === $m->id ) {
		update_option( $key, array( 'title' => 'Kurier', 'tax_status' => 'none', 'cost' => '15' ) );
	}
	if ( 'free_shipping' === $m->id ) {
		update_option( $key, array( 'title' => 'Darmowa dostawa', 'requires' => 'min_amount', 'min_amount' => '200', 'ignore_discounts' => 'no' ) );
	}
}
WC_Cache_Helper::get_transient_version( 'shipping', true );
WP_CLI::log( "Strefa wysyłki „Polska” (id {$zone->get_id()}): Kurier 15 zł, darmowa od 200 zł." );

// --- Płatności: przelew bankowy (bacs) i za pobraniem (cod); reszta wyłączona ---
update_option(
	'woocommerce_bacs_settings',
	array(
		'enabled'      => 'yes',
		'title'        => 'Przelew bankowy',
		'description'  => 'Wpłać kwotę zamówienia na nasze konto; w tytule podaj numer zamówienia.',
		'instructions' => 'Zamówienie wyślemy po zaksięgowaniu wpłaty.',
	)
);
update_option(
	'woocommerce_bacs_accounts',
	array(
		array(
			'account_name'   => 'BeanBox Sp. z o.o. (dane fikcyjne)',
			'account_number' => '00 0000 0000 0000 0000 0000 0000',
			'bank_name'      => 'Bank Testowy',
			'sort_code'      => '',
			'iban'           => '',
			'bic'            => '',
		),
	)
);
update_option(
	'woocommerce_cod_settings',
	array(
		'enabled'            => 'yes',
		'title'              => 'Płatność przy odbiorze',
		'description'        => 'Płacisz kurierowi gotówką lub kartą.',
		'instructions'       => 'Przygotuj kwotę zamówienia przy odbiorze.',
		'enable_for_methods' => array(),
		'enable_for_virtual' => 'no',
	)
);
foreach ( array( 'cheque', 'paypal' ) as $g ) {
	$s            = (array) get_option( "woocommerce_{$g}_settings", array() );
	$s['enabled'] = 'no';
	update_option( "woocommerce_{$g}_settings", $s );
}
WP_CLI::log( 'Płatności: przelew bankowy + płatność przy odbiorze.' );

// --- Koszyk i zamówienie: klasyczne shortcody (działają hooki PHP i nadpisywanie szablonów) ---
foreach ( array( 'cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]' ) as $page => $sc ) {
	$id = wc_get_page_id( $page );
	if ( $id > 0 ) {
		wp_update_post(
			array(
				'ID'           => $id,
				'post_content' => "<!-- wp:shortcode -->\n{$sc}\n<!-- /wp:shortcode -->",
			)
		);
		WP_CLI::log( "Strona „{$page}” (#{$id}) → {$sc}" );
	}
}
WP_CLI::success( 'WooCommerce skonfigurowany.' );
