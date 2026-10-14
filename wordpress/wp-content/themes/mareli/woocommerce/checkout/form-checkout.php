<?php
/**
 * Checkout — layout CheckoutForm do Next:
 * esquerda: dados + frete + cupom + CTA
 * direita: resumo sticky
 *
 * @package Mareli
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_checkout_form', WC()->checkout());

if (! WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required() && ! is_user_logged_in()) {
	echo esc_html__('Você precisa estar logado para finalizar.', 'mareli');
	return;
}

$gateways = WC()->payment_gateways()->get_available_payment_gateways();
$default_gateway = function_exists('mareli_checkout_default_gateway')
	? mareli_checkout_default_gateway()
	: '';
if ($default_gateway === '' && $gateways) {
	foreach ($gateways as $id => $gateway) {
		if (stripos((string) $id, 'mercado') !== false) {
			$default_gateway = (string) $id;
			break;
		}
	}
	if ($default_gateway === '') {
		$default_gateway = (string) array_key_first($gateways);
	}
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout Mareli-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" data-payment-gateway="<?php echo esc_attr($default_gateway); ?>">

	<div class="Mareli-checkout__grid">
		<div class="Mareli-checkout__main" id="customer_details">
			<h2 class="Mareli-checkout__section-title">Dados e entrega</h2>

			<?php do_action('woocommerce_checkout_before_customer_details'); ?>
			<div class="Mareli-checkout__fields">
				<?php do_action('woocommerce_checkout_billing'); ?>
			</div>
			<?php do_action('woocommerce_checkout_after_customer_details'); ?>

			<div class="Mareli-checkout__shipping" id="Mareli-shipping">
				<p class="Mareli-checkout__label">Envio</p>
				<div id="Mareli-shipping-methods">
					<?php if (WC()->cart && WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
						<table class="Mareli-shipping-table"><tbody>
							<?php wc_cart_totals_shipping_html(); ?>
						</tbody></table>
					<?php else : ?>
						<p class="Mareli-checkout__hint">Informe o CEP para ver as opções reais de envio.</p>
					<?php endif; ?>
				</div>
			</div>

			<div class="Mareli-checkout__coupon">
				<label for="mareli_checkout_coupon">Cupom</label>
				<div class="Mareli-checkout__coupon-row">
					<input type="text" id="mareli_checkout_coupon" placeholder="Mareli10" autocomplete="off">
					<button type="button" id="mareli_apply_coupon">Aplicar</button>
				</div>
				<p class="Mareli-checkout__coupon-msg" id="mareli_coupon_msg" hidden></p>
			</div>

			<div class="Mareli-checkout__cta" id="order_review">
				<?php do_action('woocommerce_checkout_before_order_review'); ?>

				<?php if ($gateways) : ?>
					<p class="Mareli-checkout__label"><?php esc_html_e('Pagamento', 'mareli'); ?></p>
					<ul class="wc_payment_methods payment_methods methods Mareli-pay-methods">
						<?php
						$chosen = $default_gateway !== '' && isset($gateways[$default_gateway])
							? $default_gateway
							: (string) array_key_first($gateways);
						foreach ($gateways as $gateway) {
							if ($chosen !== '' && $gateway->id === $chosen) {
								$gateway->chosen = true;
							}
							wc_get_template('checkout/payment-method.php', ['gateway' => $gateway]);
						}
						?>
					</ul>
				<?php else : ?>
					<div class="woocommerce-error Mareli-checkout__pay-error" role="alert">
						Pagamento indisponível: ative o Mercado Pago em WooCommerce → Pagamentos e configure as credenciais.
					</div>
				<?php endif; ?>

				<?php
				// Terms ocultos visualmente (Woo ainda pode exigir via filtro).
				echo '<div class="Mareli-checkout__terms-hidden">';
				wc_get_template('checkout/terms.php');
				echo '</div>';

				$btn_disabled = $default_gateway === '' ? ' disabled aria-disabled="true"' : '';
				echo apply_filters(
					'woocommerce_order_button_html',
					'<button type="submit" class="button alt btn btn--wine Mareli-place-order" name="woocommerce_checkout_place_order" id="place_order" value="Finalizar pedido"' . $btn_disabled . '>Finalizar pedido</button>'
				);
				wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce');
				?>

				<p class="Mareli-checkout__secure">PIX e cartão no site, sem precisar ter conta no Mercado Pago.</p>
			</div>
		</div>

		<aside class="Mareli-checkout__aside">
			<div class="Mareli-checkout__summary">
				<h2 class="Mareli-checkout__section-title">Seu pedido</h2>
				<p class="Mareli-checkout__aside-sub">Confira o que você está levando</p>
				<div class="Mareli-checkout__summary-body">
					<?php wc_get_template('checkout/review-order.php'); ?>
				</div>
			</div>
		</aside>
	</div>
</form>

<?php
do_action('woocommerce_after_checkout_form', WC()->checkout());
