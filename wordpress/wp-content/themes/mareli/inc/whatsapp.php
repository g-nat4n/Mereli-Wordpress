<?php
/**
 * Compra via WhatsApp — redireciona checkout Woo para a sacola.
 *
 * @package Mareli
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/** Checkout WooCommerce não é o fluxo de compra — manda para a sacola. */
add_action('template_redirect', static function (): void {
	if (! function_exists('is_checkout') || ! is_checkout() || is_wc_endpoint_url('order-received')) {
		return;
	}
	wp_safe_redirect(home_url('/carrinho/'));
	exit;
}, 5);
