<?php
/**
 * Treść startowa sklepu BeanBox: strony, synchronizacja ACF, dane globalne, opinie, FAQ, strona główna z bloków, menu.
 *   bash narzedzia/wp.sh eval-file narzedzia/konfiguracja-strony.php
 * Idempotentny (szuka istniejących wpisów po slugach).
 *
 * @package BeanBox
 */

/**
 * Wpis po slugu albo nowy.
 */
function bb_upsert( string $type, string $slug, array $data ): int {
	$found = get_page_by_path( $slug, OBJECT, $type );
	$data  = array_merge( array( 'post_type' => $type, 'post_name' => $slug, 'post_status' => 'publish' ), $data );
	if ( $found ) {
		$data['ID'] = $found->ID;
		return (int) wp_update_post( $data );
	}
	return (int) wp_insert_post( $data );
}

// --- 1. Polskie nazwy i adresy stron WooCommerce ---
foreach ( array( 'shop' => array( 'Sklep', 'sklep' ), 'cart' => array( 'Koszyk', 'koszyk' ), 'checkout' => array( 'Zamówienie', 'zamowienie' ), 'myaccount' => array( 'Moje konto', 'moje-konto' ) ) as $p => list( $title, $slug ) ) {
	$id = wc_get_page_id( $p );
	if ( $id > 0 ) {
		wp_update_post( array( 'ID' => $id, 'post_title' => $title, 'post_name' => $slug ) );
	}
}
wp_delete_post( (int) get_page_by_path( 'sample-page' )?->ID, true );
WP_CLI::log( 'Strony: Sklep, Koszyk, Zamówienie, Moje konto.' );

// --- 2. ACF Local JSON → baza (to samo co „Dostępna synchronizacja” → Synchronizuj) ---
foreach ( glob( get_theme_file_path( 'acf-json/*.json' ) ) as $file ) {
	$group = json_decode( file_get_contents( $file ), true ); // phpcs:ignore
	$old   = acf_get_field_group( $group['key'] );
	if ( $old && ! empty( $old['ID'] ) ) {
		$group['ID'] = $old['ID'];
	}
	acf_import_field_group( $group );
	WP_CLI::log( "ACF: {$group['title']}" );
}

// --- 3. Dane globalne (Customizer) – dane fikcyjne ---
foreach (
	array(
		'promo'     => 'Darmowa dostawa od 200 zł · kawa palona w tym tygodniu',
		'telefon'   => '+48 500 100 200',
		'email'     => 'sklep@beanbox.example.com',
		'adres'     => "BeanBox Sp. z o.o.\nul. Ziarnista 7\n00-950 Warszawa",
		'facebook'  => 'https://www.facebook.com/',
		'instagram' => 'https://www.instagram.com/',
	) as $k => $v
) {
	set_theme_mod( "beanbox_{$k}", $v );
}
WP_CLI::log( 'Customizer: telefon, e-mail, adres, social media, pasek promocji.' );

// --- 4. Opinie (CPT „opinia”) z polami ACF ---
$opinie = array(
	array( 'Anna z Krakowa', 'Etiopia Yirgacheffe pachnie jak jaśmin – pierwszy raz piję kawę bez cukru. Paczka przyszła w 2 dni.', 5, 'Kraków', 'P001' ),
	array( 'Tomasz, kawiarnia domowa', 'Młynek BX-200 mieli równo nawet pod espresso. Obsługa doradziła ustawienia przez czat.', 5, 'Poznań', 'P009' ),
	array( 'Marta', 'Subskrypcja to świetny prezent – co miesiąc inna kawa i karteczka z opisem farmy.', 5, 'Gdańsk', 'P025' ),
	array( 'Piotr', 'Kenia AA bardzo kwasowa, ale w V60 wychodzi rewelacyjnie. Następnym razem wezmę 1 kg.', 4, 'Wrocław', 'P006' ),
	array( 'Ola', 'Czajnik super, choć czekałam na niego dłużej, bo był chwilowo niedostępny.', 4, 'Łódź', 'P014' ),
	array( 'Krzysztof', 'Kawa dobra, ale torebka bez strunowego zamknięcia.', 3, 'Lublin', 'P003' ),
);
foreach ( $opinie as $i => list( $title, $text, $ocena, $miasto, $sku ) ) {
	$id = bb_upsert( 'opinia', sanitize_title( $title ), array( 'post_title' => $title, 'post_content' => $text, 'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( '-' . ( $i * 3 + 1 ) . ' days' ) ) ) );
	update_field( 'ocena', (string) $ocena, $id );
	update_field( 'miasto', $miasto, $id );
	$pid = wc_get_product_id_by_sku( $sku );
	if ( $pid ) {
		update_field( 'produkt', $pid, $id );
	}
}
WP_CLI::log( 'Opinie: ' . count( $opinie ) );

// --- 5. FAQ (CPT „faq”) ---
$faq = array(
	array( 'Jak szybko wysyłacie zamówienia?', 'Kawę palimy we wtorki i czwartki, a zamówienia wysyłamy w ciągu 1–2 dni roboczych od zaksięgowania płatności.' ),
	array( 'Ile kosztuje dostawa?', 'Dostawa kurierem kosztuje 15 zł, a od 200 zł jest darmowa.' ),
	array( 'Czy mogę zwrócić produkt?', 'Tak, masz 14 dni od doręczenia na odstąpienie od umowy bez podania przyczyny. Wypełnij formularz zwrotu w zakładce Moje konto.' ),
	array( 'Kawa ziarnista czy mielona?', 'Ziarnista dłużej zachowuje aromat. Jeśli nie masz młynka, zmielimy kawę pod Twoją metodę parzenia – napisz to w uwagach do zamówienia.' ),
	array( 'Jak przechowywać kawę?', 'W szczelnym, nieprzezroczystym pojemniku, w temperaturze pokojowej. Nie trzymaj kawy w lodówce.' ),
	array( 'Czy wystawiacie faktury VAT?', 'Tak – podaj NIP w formularzu zamówienia, a faktura przyjdzie e-mailem razem z potwierdzeniem wysyłki.' ),
);
foreach ( $faq as $i => list( $q, $a ) ) {
	bb_upsert( 'faq', sanitize_title( $q ), array( 'post_title' => $q, 'post_content' => $a, 'menu_order' => $i ) );
}
WP_CLI::log( 'FAQ: ' . count( $faq ) );

// --- 6. Strona główna złożona WYŁĄCZNIE z bloków ACF ---
/**
 * Komentarz bloku ACF z danymi pól (pole => wartość + _pole => klucz pola).
 */
function bb_block( string $name, array $data ): string {
	$out = array();
	foreach ( $data as $k => $v ) {
		$out[ $k ]       = $v;
		$out[ "_{$k}" ] = "field_bb_{$name}_" . preg_replace( '/^usp_\d+_/', '', $k );
	}
	return '<!-- wp:acf/' . $name . ' ' . wp_json_encode( array( 'name' => "acf/{$name}", 'data' => $out, 'mode' => 'preview' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . " /-->\n\n";
}
$usp = array( 'title' => 'Dlaczego BeanBox?' );
$usp_items = array(
	array( 'fire', 'Świeżo palona', 'Palimy dwa razy w tygodniu. Na torebce zawsze data palenia.' ),
	array( 'truck', 'Dostawa w 48 h', 'Wysyłka w 1–2 dni robocze, darmowa od 200 zł.' ),
	array( 'bean', 'Tylko speciality', 'Kawy z oceną SCA 84+ od sprawdzonych farm.' ),
	array( 'return', '14 dni na zwrot', 'Nie pasuje? Odeślij bez podawania przyczyny.' ),
);
$hero_content = bb_block( 'hero', array( 'title' => 'Kawa speciality prosto z palarni', 'text' => 'Świeżo palone ziarna z Etiopii, Kenii i Kolumbii oraz sprzęt do parzenia w domu. Dostawa w 48 godzin.', 'image' => '', 'button' => array( 'title' => 'Zobacz kawy', 'url' => wp_make_link_relative( get_term_link( 'kawa', 'product_cat' ) ), 'target' => '' ) ) );
// grupa USP: ACF zapisuje pola podgrupy jako usp_1_icon itd.; klucze podpól mają prefiks usp1/usp2…
$usp_data = array( 'title' => $usp['title'], '_title' => 'field_bb_usp_title' );
foreach ( $usp_items as $i => list( $icon, $t, $d ) ) {
	$n = $i + 1;
	$usp_data[ "usp_{$n}" ]          = '';
	$usp_data[ "_usp_{$n}" ]         = "field_bb_usp_usp_{$n}";
	$usp_data[ "usp_{$n}_icon" ]     = $icon;
	$usp_data[ "_usp_{$n}_icon" ]    = "field_bb_usp{$n}_icon";
	$usp_data[ "usp_{$n}_title" ]    = $t;
	$usp_data[ "_usp_{$n}_title" ]   = "field_bb_usp{$n}_title";
	$usp_data[ "usp_{$n}_text" ]     = $d;
	$usp_data[ "_usp_{$n}_text" ]    = "field_bb_usp{$n}_text";
}
$usp_content = '<!-- wp:acf/usp ' . wp_json_encode( array( 'name' => 'acf/usp', 'data' => $usp_data, 'mode' => 'preview' ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . " /-->\n\n";
$content = $hero_content . $usp_content
	. bb_block( 'opinie', array( 'title' => 'Co mówią klienci', 'count' => 3, 'min_rating' => '4' ) )
	. bb_block( 'faq', array( 'title' => 'Najczęstsze pytania', 'count' => 6 ) );
$home = bb_upsert( 'page', 'strona-glowna', array( 'post_title' => 'Strona główna', 'post_content' => $content ) );
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $home );
WP_CLI::log( "Strona główna #{$home}: hero, usp, opinie, faq." );

// --- 7. Menu główne i menu stopki ---
$menu_id = wp_get_nav_menu_object( 'Menu główne' )->term_id ?? wp_create_nav_menu( 'Menu główne' );
foreach ( wp_get_nav_menu_items( $menu_id ) ?: array() as $item ) {
	wp_delete_post( $item->ID, true );
}
$items = array( array( 'Sklep', get_permalink( wc_get_page_id( 'shop' ) ) ) );
foreach ( array( 'kawa', 'akcesoria', 'sprzet' ) as $slug ) {
	$t = get_term_by( 'slug', $slug, 'product_cat' );
	if ( $t ) {
		$items[] = array( $t->name, get_term_link( $t ) );
	}
}
$items[] = array( 'Opinie', get_post_type_archive_link( 'opinia' ) );
foreach ( $items as list( $title, $url ) ) {
	wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-title' => $title, 'menu-item-url' => $url, 'menu-item-status' => 'publish' ) );
}
$footer_id = wp_get_nav_menu_object( 'Stopka' )->term_id ?? wp_create_nav_menu( 'Stopka' );
foreach ( wp_get_nav_menu_items( $footer_id ) ?: array() as $item ) {
	wp_delete_post( $item->ID, true );
}
foreach ( array( 'myaccount' => 'Moje konto', 'cart' => 'Koszyk' ) as $p => $title ) {
	wp_update_nav_menu_item( $footer_id, 0, array( 'menu-item-title' => $title, 'menu-item-url' => get_permalink( wc_get_page_id( $p ) ), 'menu-item-status' => 'publish' ) );
}
set_theme_mod( 'nav_menu_locations', array( 'primary' => $menu_id, 'footer' => $footer_id ) );
WP_CLI::success( 'Treść startowa gotowa.' );
