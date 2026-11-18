<?php
/**
 * Displayed when no products are found matching the current query
 *
 * NADPISANIE motywu BeanBox: własny komunikat z linkami do kategorii zamiast samego „No products were found”.
 * Oryginał: wp-content/plugins/woocommerce/templates/loop/no-products-found.php.
 * Dlaczego nadpisanie, a nie hook: ten szablon nie ma żadnej akcji (do_action) – całą treść trzeba zmienić w HTML.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/no-products-found.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;

$beanbox_cats = get_terms(
	array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
	)
);
?>
<div class="woocommerce-no-products-found no-products">
	<?php wc_print_notice( esc_html__( 'Nie mamy jeszcze takiej kawy… ale może coś z tych kategorii?', 'beanbox' ), 'notice' ); ?>
	<?php if ( ! is_wp_error( $beanbox_cats ) && $beanbox_cats ) : ?>
		<ul class="no-products__cats">
			<?php foreach ( $beanbox_cats as $beanbox_cat ) : ?>
				<li><a class="btn btn--ghost" href="<?php echo esc_url( get_term_link( $beanbox_cat ) ); ?>"><?php echo esc_html( $beanbox_cat->name ); ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
	<p><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Wróć do wszystkich produktów', 'beanbox' ); ?></a></p>
</div>
