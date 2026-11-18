<?php
/**
 * Własne typy treści: opinie klientów i FAQ (FAQ jako osobne wpisy zamiast pola Repeater, które w ACF jest funkcją PRO).
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akcja init – rejestracja typów „opinia” i „faq”.
 * Po pierwszej rejestracji: Ustawienia → Bezpośrednie odnośniki → Zapisz (albo `wp rewrite flush`).
 */
function beanbox_register_post_types(): void {
	register_post_type(
		'opinia',
		array(
			'labels'       => array(
				'name'          => __( 'Opinie', 'beanbox' ),
				'singular_name' => __( 'Opinia', 'beanbox' ),
				'add_new_item'  => __( 'Dodaj opinię', 'beanbox' ),
				'edit_item'     => __( 'Edytuj opinię', 'beanbox' ),
				'all_items'     => __( 'Wszystkie opinie', 'beanbox' ),
			),
			'public'       => true,
			'has_archive'  => true,                     // archiwum pod /opinie/
			'rewrite'      => array( 'slug' => 'opinie' ),
			'menu_icon'    => 'dashicons-format-quote',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest' => true,                     // edytor blokowy + REST API
		)
	);

	register_post_type(
		'faq',
		array(
			'labels'       => array(
				'name'          => __( 'FAQ', 'beanbox' ),
				'singular_name' => __( 'Pytanie', 'beanbox' ),
				'add_new_item'  => __( 'Dodaj pytanie', 'beanbox' ),
			),
			'public'       => false,                    // wyświetlane tylko w bloku FAQ
			'show_ui'      => true,
			'menu_icon'    => 'dashicons-editor-help',
			'supports'     => array( 'title', 'editor', 'page-attributes' ), // tytuł = pytanie, treść = odpowiedź, kolejność
			'show_in_rest' => true,
		)
	);
}
add_action( 'init', 'beanbox_register_post_types' );
