<?php
/**
 * Dane globalne sklepu (telefon, e-mail, adres, social media, pasek promocji).
 * Strona opcji to w ACF funkcja PRO, dlatego używamy Customizera (działa bez żadnej wtyczki): Wygląd → Dostosuj → Dane sklepu.
 *
 * @package BeanBox
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lista ustawień: klucz => [etykieta, typ kontrolki, funkcja sanitize].
 */
function beanbox_option_fields(): array {
	return array(
		'promo'     => array( __( 'Pasek promocji (nad nagłówkiem)', 'beanbox' ), 'text', 'sanitize_text_field' ),
		'telefon'   => array( __( 'Telefon', 'beanbox' ), 'text', 'sanitize_text_field' ),
		'email'     => array( __( 'E-mail', 'beanbox' ), 'email', 'sanitize_email' ),
		'adres'     => array( __( 'Adres', 'beanbox' ), 'textarea', 'sanitize_textarea_field' ),
		'facebook'  => array( __( 'Facebook (URL)', 'beanbox' ), 'url', 'esc_url_raw' ),
		'instagram' => array( __( 'Instagram (URL)', 'beanbox' ), 'url', 'esc_url_raw' ),
	);
}

/**
 * Akcja customize_register – sekcja „Dane sklepu”.
 *
 * @param WP_Customize_Manager $wp_customize Menedżer Customizera.
 */
function beanbox_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'beanbox_dane',
		array(
			'title'    => __( 'Dane sklepu', 'beanbox' ),
			'priority' => 30,
		)
	);

	foreach ( beanbox_option_fields() as $key => list( $label, $type, $sanitize ) ) {
		$wp_customize->add_setting(
			"beanbox_{$key}",
			array(
				'default'           => '',
				'sanitize_callback' => $sanitize, // czyścimy przy zapisie, escapujemy przy wyświetlaniu
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			"beanbox_{$key}",
			array(
				'label'   => $label,
				'section' => 'beanbox_dane',
				'type'    => $type,
			)
		);
	}
}
add_action( 'customize_register', 'beanbox_customize_register' );
