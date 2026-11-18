<?php
/**
 * Testowe zamówienie (dane fikcyjne) – przez API WooCommerce, tak jak robi to kasa sklepu.
 *   bash narzedzia/wp.sh eval-file narzedzia/zamowienie-testowe.php --user=admin
 *
 * @package BeanBox
 */

$adres = array(
	'first_name' => 'Jan',
	'last_name'  => 'Testowy',
	'email'      => 'jan.testowy@example.com',
	'phone'      => '500 000 000',
	'address_1'  => 'ul. Przykładowa 1',
	'city'       => 'Warszawa',
	'postcode'   => '00-001',
	'country'    => 'PL',
);
$order = wc_create_order( array( 'customer_note' => 'Zamówienie testowe – laboratorium 3.' ) );
$etiopia_1kg = wc_get_product( wc_get_product_id_by_sku( 'P001-1KG' ) );
$order->add_product( $etiopia_1kg, 1 );                                   // wariant 1 kg – 169 zł
$order->add_product( wc_get_product( wc_get_product_id_by_sku( 'P011' ) ), 2 ); // filtry V60 – 2 × 25 zł
$order->set_address( $adres, 'billing' );
$order->set_address( $adres, 'shipping' );

// wysyłka wg reguł strefy „Polska”: ≥ 200 zł → darmowa
$suma  = (float) $order->get_subtotal();
$metoda = $suma >= 200 ? array( 'free_shipping', 'Darmowa dostawa', 0 ) : array( 'flat_rate', 'Kurier', 15 );
$item  = new WC_Order_Item_Shipping();
$item->set_props( array( 'method_id' => $metoda[0], 'method_title' => $metoda[1], 'total' => $metoda[2] ) );
$order->add_item( $item );

$order->set_payment_method( 'bacs' );
$order->set_payment_method_title( 'Przelew bankowy' );
$order->calculate_totals();
$order->update_status( 'on-hold', 'Oczekuje na przelew (zamówienie testowe).' ); // jak po złożeniu zamówienia z przelewem
WP_CLI::success( sprintf( 'Zamówienie #%d: %s, wysyłka: %s, razem %s zł, status: %s', $order->get_id(), implode( ', ', array_map( fn( $i ) => $i->get_name() . ' ×' . $i->get_quantity(), $order->get_items() ) ), $metoda[1], $order->get_total(), $order->get_status() ) );
