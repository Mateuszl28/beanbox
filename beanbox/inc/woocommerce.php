<?php
/**
 * Integracja WooCommerce z motywem – wyłącznie przez hooki (jedyny nadpisany szablon:
 * woocommerce/loop/no-products-found.php).
 *
 * Decyzja zespołu: ZOSTAWIAMY domyślne style WooCommerce i nadpisujemy je w assets/css/main.css
 * (szybciej dostajemy działający koszyk i zamówienie; formularze i komunikaty Woo są już ostylowane).
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -------------------------------------------------------------------------
 * 1. Obudowa stron sklepu: własny kontener zamiast <div id="primary">, bez paska bocznego.
 * ---------------------------------------------------------------------- */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_action(
	'woocommerce_before_main_content',
	static function () {
		echo '<div class="container section shop">';
	},
	10
);
add_action(
	'woocommerce_after_main_content',
	static function () {
		echo '</div>';
	},
	10
);

/**
 * Filtr loop_shop_columns – 3 kolumny w siatce produktów (na telefonie CSS i tak daje 1–2).
 */
add_filter( 'loop_shop_columns', static fn() => 3 );

/**
 * Filtr loop_shop_per_page – 12 produktów na stronę.
 */
add_filter( 'loop_shop_per_page', static fn() => 12 );

/* -------------------------------------------------------------------------
 * 2. Karta produktu – przestawienie układu hookami.
 *    Domyślnie: tytuł 5, ocena 10, cena 10, krótki opis 20, przycisk 30, meta 40.
 *    U nas:     tytuł 5, ocena 10, krótki opis 20, CENA 25, „Darmowa dostawa” 28, przycisk 30, meta 40.
 * ---------------------------------------------------------------------- */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );

add_action(
	'woocommerce_single_product_summary',
	static function () {
		echo '<p class="shipping-note">' . esc_html__( 'Darmowa dostawa od 200 zł · wysyłka w 1–2 dni robocze', 'beanbox' ) . '</p>';
	},
	28
);

/**
 * Pod krótkim opisem: stopień palenia i nuty smakowe z ACF (jeśli wypełnione).
 */
add_action(
	'woocommerce_single_product_summary',
	static function () {
		global $product;
		$id      = $product->get_id();
		$palenie = beanbox_field( 'stopien_palenia', $id );
		$nuty    = beanbox_field( 'nuty_smakowe', $id );
		if ( ! $palenie && ! $nuty ) {
			return;
		}
		echo '<ul class="product-facts">';
		if ( $palenie ) {
			echo '<li><strong>' . esc_html__( 'Palenie:', 'beanbox' ) . '</strong> ' . esc_html( $palenie ) . '</li>';
		}
		if ( $nuty ) {
			echo '<li><strong>' . esc_html__( 'Nuty:', 'beanbox' ) . '</strong> ' . esc_html( $nuty ) . '</li>';
		}
		echo '</ul>';
	},
	22
);

/* -------------------------------------------------------------------------
 * 3. Zakładka „Skład i pochodzenie” z pól ACF grupy „Produkt – dodatkowe”.
 *    Zakładkę „Opinie” WooCommerce usuwamy – opinie mamy we własnym typie treści (blok „Opinie klientów”).
 * ---------------------------------------------------------------------- */
add_filter(
	'woocommerce_product_tabs',
	static function ( array $tabs ): array {
		global $product;
		$id = $product->get_id();
		if ( beanbox_field( 'sklad', $id ) || beanbox_field( 'pochodzenie', $id ) ) {
			$tabs['sklad'] = array(
				'title'    => __( 'Skład i pochodzenie', 'beanbox' ),
				'priority' => 5, // przed „Opisem” (10) – przy kawie skład i pochodzenie są najważniejsze, zakładka otwarta domyślnie
				'callback' => 'beanbox_tab_sklad',
			);
		}
		unset( $tabs['reviews'] );
		return $tabs;
	}
);

/**
 * Treść zakładki „Skład i pochodzenie”.
 */
function beanbox_tab_sklad(): void {
	global $product;
	$id      = $product->get_id();
	$sklad   = beanbox_field( 'sklad', $id );
	$origin  = beanbox_field( 'pochodzenie', $id );
	$palenie = beanbox_field( 'stopien_palenia', $id );
	$dni     = beanbox_field( 'czas_realizacji', $id );

	echo '<h2>' . esc_html__( 'Skład i pochodzenie', 'beanbox' ) . '</h2>';
	if ( $sklad ) {
		echo wp_kses_post( $sklad );
	}
	echo '<dl class="product-spec">';
	if ( $origin ) {
		echo '<dt>' . esc_html__( 'Pochodzenie', 'beanbox' ) . '</dt><dd>' . esc_html( $origin ) . '</dd>';
	}
	if ( $palenie ) {
		echo '<dt>' . esc_html__( 'Stopień palenia', 'beanbox' ) . '</dt><dd>' . esc_html( $palenie ) . '</dd>';
	}
	if ( '' !== (string) $dni && null !== $dni ) {
		/* translators: %d: liczba dni roboczych */
		echo '<dt>' . esc_html__( 'Czas realizacji', 'beanbox' ) . '</dt><dd>' . esc_html( sprintf( _n( '%d dzień roboczy', '%d dni robocze', (int) $dni, 'beanbox' ), (int) $dni ) ) . '</dd>';
	}
	echo '</dl>';
}

/* -------------------------------------------------------------------------
 * 4. Baner kategorii z pól ACF (reguła: Taksonomia = Kategoria produktu).
 * ---------------------------------------------------------------------- */
add_action(
	'woocommerce_archive_description',
	static function () {
		if ( ! is_product_category() ) {
			return;
		}
		$term  = get_queried_object();
		$image = beanbox_field( 'baner', $term );
		$text  = beanbox_field( 'baner_tekst', $term );
		if ( ! $image && ! $text ) {
			return;
		}
		echo '<div class="category-banner">';
		if ( $image ) {
			echo wp_get_attachment_image( (int) $image, 'full', false, array( 'class' => 'category-banner__img' ) );
		}
		if ( $text ) {
			echo '<p class="category-banner__text">' . esc_html( $text ) . '</p>';
		}
		echo '</div>';
	},
	5 // przed opisem kategorii (priorytet 10)
);

/* -------------------------------------------------------------------------
 * 5. Znaczek „Nowość” dla produktów nowszych niż 30 dni (lista produktów).
 * ---------------------------------------------------------------------- */
add_action(
	'woocommerce_before_shop_loop_item_title',
	static function () {
		global $product;
		$created = $product->get_date_created();
		if ( $created && $created->getTimestamp() > strtotime( '-30 days' ) ) {
			echo '<span class="badge badge--new">' . esc_html__( 'Nowość', 'beanbox' ) . '</span>';
		}
	},
	9 // 9 = przed znaczkiem promocji (woocommerce_show_product_loop_sale_flash ma 10)
);

/* -------------------------------------------------------------------------
 * 6. Mini-koszyk w nagłówku – licznik odświeżany bez przeładowania (fragmenty AJAX).
 *    Po „Dodaj do koszyka” Woo wysyła ?wc-ajax=add_to_cart, w odpowiedzi dostaje fragments
 *    i podmienia element pasujący do selektora span.cart-count.
 * ---------------------------------------------------------------------- */
add_filter(
	'woocommerce_add_to_cart_fragments',
	static function ( array $fragments ): array {
		$fragments['span.cart-count'] = '<span class="cart-count">' . esc_html( WC()->cart->get_cart_contents_count() ) . '</span>';
		return $fragments;
	}
);

// Od WooCommerce 7.8 skrypt fragmentów nie ładuje się sam na każdej stronie.
add_action(
	'wp_enqueue_scripts',
	static function () {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
);
