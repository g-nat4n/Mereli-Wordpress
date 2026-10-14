<?php
declare(strict_types=1);
if (! defined('ABSPATH') || ! function_exists('WC')) {
	return;
}
?>
<div class="Mareli-drawer" data-cart-drawer hidden>
	<button type="button" class="Mareli-drawer__overlay" data-cart-close aria-label="<?php esc_attr_e('Fechar sacola', 'mareli'); ?>"></button>
	<aside class="Mareli-drawer__panel" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Sacola', 'mareli'); ?>">
		<div class="Mareli-drawer__header">
			<div>
				<p class="Mareli-drawer__kicker"><?php esc_html_e('Sua seleção', 'mareli'); ?></p>
				<h2 class="Mareli-drawer__title"><?php esc_html_e('Sacola', 'mareli'); ?></h2>
			</div>
			<button type="button" class="Mareli-drawer__close" data-cart-close aria-label="<?php esc_attr_e('Fechar', 'mareli'); ?>">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
			</button>
		</div>
		<div class="Mareli-drawer__body">
			<?php mareli_render_cart_drawer_body(); ?>
		</div>
		<div class="Mareli-drawer__sand" aria-hidden="true"></div>
		<div class="Mareli-drawer__footer">
			<?php mareli_render_cart_drawer_footer(); ?>
		</div>
	</aside>
</div>
