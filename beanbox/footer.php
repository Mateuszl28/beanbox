<?php
/**
 * Stopka: dane globalne z Customizera (Wygląd → Dostosuj → Dane sklepu) i menu stopki.
 *
 * @package BeanBox
 */

$beanbox_telefon   = beanbox_option( 'telefon' );
$beanbox_email     = beanbox_option( 'email' );
$beanbox_adres     = beanbox_option( 'adres' );
$beanbox_facebook  = beanbox_option( 'facebook' );
$beanbox_instagram = beanbox_option( 'instagram' );
?>
</main>

<footer class="site-footer">
	<div class="container site-footer__grid">
		<div>
			<p class="site-footer__brand"><?php bloginfo( 'name' ); ?></p>
			<p><?php bloginfo( 'description' ); ?></p>
		</div>

		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Kontakt', 'beanbox' ); ?></h2>
			<ul class="site-footer__list">
				<?php if ( $beanbox_telefon ) : ?>
					<li><a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $beanbox_telefon ) ); ?>"><?php echo esc_html( $beanbox_telefon ); ?></a></li>
				<?php endif; ?>
				<?php if ( $beanbox_email ) : ?>
					<li><a href="<?php echo esc_url( 'mailto:' . $beanbox_email ); ?>"><?php echo esc_html( $beanbox_email ); ?></a></li>
				<?php endif; ?>
				<?php if ( $beanbox_adres ) : ?>
					<li><?php echo nl2br( esc_html( $beanbox_adres ) ); ?></li>
				<?php endif; ?>
			</ul>
		</div>

		<div>
			<h2 class="site-footer__title"><?php esc_html_e( 'Obserwuj nas', 'beanbox' ); ?></h2>
			<ul class="site-footer__list">
				<?php if ( $beanbox_facebook ) : ?>
					<li><a href="<?php echo esc_url( $beanbox_facebook ); ?>" rel="noopener" target="_blank">Facebook</a></li>
				<?php endif; ?>
				<?php if ( $beanbox_instagram ) : ?>
					<li><a href="<?php echo esc_url( $beanbox_instagram ); ?>" rel="noopener" target="_blank">Instagram</a></li>
				<?php endif; ?>
			</ul>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => 'site-footer__list',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</div>
	</div>
	<p class="container site-footer__copy">
		&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>
	</p>
</footer>

<?php wp_footer(); ?>
</body>
</html>
