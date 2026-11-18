<?php
/**
 * Nagłówek: pasek promocji, logo, menu, mini-koszyk.
 *
 * @package BeanBox
 */

$beanbox_promo = beanbox_option( 'promo' );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#tresc"><?php esc_html_e( 'Przejdź do treści', 'beanbox' ); ?></a>

<?php if ( $beanbox_promo ) : ?>
	<div class="promo-bar"><?php echo esc_html( $beanbox_promo ); ?></div>
<?php endif; ?>

<header class="site-header">
	<div class="container site-header__inner">
		<div class="site-brand">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a class="site-brand__name" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
					<span class="site-brand__mark" aria-hidden="true">●</span><?php bloginfo( 'name' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<button class="nav-toggle" type="button" aria-expanded="false" aria-controls="menu-glowne">
			<span class="screen-reader-text"><?php esc_html_e( 'Menu', 'beanbox' ); ?></span>
			<span class="nav-toggle__bar" aria-hidden="true"></span>
		</button>

		<nav class="site-nav" id="menu-glowne" aria-label="<?php esc_attr_e( 'Menu główne', 'beanbox' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'fallback_cb'    => false,
					'depth'          => 2,
				)
			);
			?>
		</nav>

	</div>
</header>

<main id="tresc" class="site-main">
