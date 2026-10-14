<?php
/**
 * Sincroniza pagamentos Mercado Pago → status Woo / estoque.
 *
 * @package Mareli
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Access token de produção do gateway Custom (cartão) ou PIX.
 */
function mareli_mp_access_token(): string {
	foreach (['woocommerce_woo-mercado-pago-custom_settings', 'woocommerce_woo-mercado-pago-pix_settings'] as $opt) {
		$settings = get_option($opt, []);
		if (is_array($settings) && ! empty($settings['_mp_access_token_prod'])) {
			return (string) $settings['_mp_access_token_prod'];
		}
	}
	return '';
}

/**
 * Extrai ID numérico do pagamento a partir da meta do pedido.
 */
function mareli_mp_payment_id_from_order(WC_Order $order): string {
	$raw = (string) $order->get_meta('_Mercado_Pago_Payment_IDs');
	if ($raw === '') {
		$raw = (string) $order->get_meta('Mercado Pago - payment_id');
	}
	return preg_replace('/\\D+/', '', $raw) ?? '';
}

/**
 * Consulta status do pagamento na API MP.
 *
 * @return array{status?:string,status_detail?:string}|null
 */
function mareli_mp_fetch_payment(string $payment_id): ?array {
	$token = mareli_mp_access_token();
	if ($token === '' || $payment_id === '') {
		return null;
	}
	$response = wp_remote_get(
		'https://api.mercadopago.com/v1/payments/' . rawurlencode($payment_id),
		[
			'timeout' => 20,
			'headers' => [
				'Authorization' => 'Bearer ' . $token,
			],
		]
	);
	if (is_wp_error($response)) {
		return null;
	}
	$code = (int) wp_remote_retrieve_response_code($response);
	$body = json_decode((string) wp_remote_retrieve_body($response), true);
	if ($code !== 200 || ! is_array($body)) {
		return null;
	}
	return $body;
}

/**
 * Aplica status do MP no pedido Woo (e baixa estoque via payment_complete).
 */
function mareli_mp_sync_order(WC_Order $order, bool $force = false): bool {
	if (! in_array($order->get_status(), ['pending', 'on-hold', 'failed'], true)) {
		return false;
	}
	$payment_id = mareli_mp_payment_id_from_order($order);
	if ($payment_id === '') {
		return false;
	}

	// Evita martelar a API.
	$last = (int) $order->get_meta('_mareli_mp_sync_ts');
	if (! $force && $last && (time() - $last) < 45) {
		return false;
	}
	$order->update_meta_data('_mareli_mp_sync_ts', (string) time());
	$order->save();

	$payment = mareli_mp_fetch_payment($payment_id);
	if (! $payment) {
		return false;
	}

	$status = (string) ($payment['status'] ?? '');
	if ($status === 'approved' || $status === 'authorized') {
		$order->update_meta_data('_mareli_mp_paid', 'yes');
		$order->update_meta_data('_mareli_mp_status', $status);
		$order->save();
		if (! $order->is_paid() || $order->has_status(['pending', 'on-hold', 'failed'])) {
			$order->payment_complete($payment_id);
			$order->add_order_note('Mareli: pagamento Mercado Pago confirmado (' . $status . ').');
			wc_maybe_reduce_stock_levels($order->get_id());
			return true;
		}
		return true;
	}

	if (in_array($status, ['rejected', 'cancelled', 'refunded', 'charged_back'], true)) {
		if (! $order->has_status(['cancelled', 'refunded', 'failed'])) {
			$order->update_status('cancelled', 'Mareli: pagamento Mercado Pago ' . $status . '.');
			return true;
		}
	}

	return false;
}

/**
 * Verificação de e-mail do pedido: só dispensa se for o dono logado.
 * Com `key` válida na URL o Woo já libera (padrão); sem key e sem login, exige e-mail.
 */
add_filter('woocommerce_order_email_verification_required', static function ($required, $order) {
	if ($order instanceof WC_Order && is_user_logged_in() && (int) $order->get_user_id() === get_current_user_id()) {
		return false;
	}
	return (bool) $required;
}, 20, 2);

/**
 * Resolve pedido no retorno MP só com payment_id validado na API.
 * Nunca confia só em external_reference (evita IDOR / vazamento da key).
 */
function mareli_mp_order_from_return_query(): ?WC_Order {
	if (! function_exists('wc_get_order')) {
		return null;
	}
	$payment_id = '';
	foreach (['payment_id', 'collection_id', 'paymentId'] as $key) {
		if (! empty($_GET[ $key ])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$payment_id = preg_replace('/\\D+/', '', (string) wp_unslash($_GET[ $key ])) ?? ''; // phpcs:ignore
			break;
		}
	}
	if ($payment_id === '') {
		return null;
	}

	$payment = mareli_mp_fetch_payment($payment_id);
	if (! is_array($payment)) {
		return null;
	}

	$api_ref = isset($payment['external_reference']) ? (string) $payment['external_reference'] : '';
	$order   = null;
	if ($api_ref !== '') {
		$maybe = wc_get_order(absint($api_ref));
		if ($maybe instanceof WC_Order) {
			$order = $maybe;
		}
	}
	if (! $order instanceof WC_Order) {
		$orders = wc_get_orders(
			[
				'limit'      => 1,
				'meta_key'   => '_Mercado_Pago_Payment_IDs',
				'meta_value' => $payment_id,
			]
		);
		if (! empty($orders[0]) && $orders[0] instanceof WC_Order) {
			$order = $orders[0];
		}
	}
	if (! $order instanceof WC_Order) {
		return null;
	}
	if ($api_ref !== '' && absint($api_ref) !== $order->get_id()) {
		return null;
	}

	return $order;
}

/**
 * Cartão no site (custom): evita fluxo “conta MP” / Checkout Pro.
 * Mantém PIX + cartão embutidos; o redirect da print 3 é do domínio do MP.
 */
add_action('init', static function (): void {
	if (get_option('_mareli_mp_custom_onsite') === '1') {
		return;
	}
	$key = 'woocommerce_woo-mercado-pago-custom_settings';
	$settings = get_option($key, []);
	if (! is_array($settings) || $settings === []) {
		return;
	}
	$settings['enabled'] = 'yes';
	// Desliga botão/carteira que abre login MP em popup.
	foreach (['wallet_button', 'wallet_button_enabled', '_mp_wallet_button', 'checkout_wallet_enabled'] as $flag) {
		if (array_key_exists($flag, $settings)) {
			$settings[ $flag ] = 'no';
		}
	}
	update_option($key, $settings);

	$basic = get_option('woocommerce_woo-mercado-pago-basic_settings', []);
	if (is_array($basic)) {
		$basic['enabled'] = 'no';
		update_option('woocommerce_woo-mercado-pago-basic_settings', $basic);
	}
	update_option('_mareli_mp_custom_onsite', '1');
}, 25);

/**
 * Com ?key= do pedido, permite pagar sem login
 * (evita tela de login em inglês no meio do fluxo).
 */
add_filter('user_has_cap', static function (array $allcaps, array $caps, array $args): array {
	if (($caps[0] ?? '') !== 'pay_for_order') {
		return $allcaps;
	}
	$order_id = isset($args[2]) ? absint($args[2]) : 0;
	if (! $order_id || empty($_GET['key'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return $allcaps;
	}
	$order = wc_get_order($order_id);
	if (! $order instanceof WC_Order) {
		return $allcaps;
	}
	$key = sanitize_text_field(wp_unslash((string) $_GET['key'])); // phpcs:ignore
	if ($key !== '' && hash_equals($order->get_order_key(), $key)) {
		$allcaps['pay_for_order'] = true;
	}
	return $allcaps;
}, 20, 3);

/** Botões de escape na página pagar pedido. */
add_action('woocommerce_before_pay', static function (): void {
	mareli_order_pay_escape_links();
}, 1);

add_action('woocommerce_before_main_content', static function (): void {
	if (! function_exists('is_checkout') || ! is_checkout() || empty(get_query_var('order-pay'))) {
		return;
	}
	mareli_order_pay_escape_links();
}, 3);

/**
 * Links para sair se o pagamento/modal do MP travar.
 */
function mareli_order_pay_escape_links(): void {
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	$store = home_url('/');
	$checkout = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : $store;
	echo '<p class="Mareli-pay__escape">';
	echo '<a class="Mareli-pay__escape-link" href="' . esc_url($checkout) . '">' . esc_html__('← Voltar ao checkout', 'mareli') . '</a>';
	echo '<span>·</span>';
	echo '<a class="Mareli-pay__escape-link" href="' . esc_url($store) . '">' . esc_html__('Voltar à loja', 'mareli') . '</a>';
	echo '</p>';
}

/** Após thankyou, tenta sincronizar (webhook às vezes atrasa/falha). */
add_action('woocommerce_thankyou', static function ($order_id): void {
	if (! $order_id) {
		return;
	}
	$order = wc_get_order((int) $order_id);
	if ($order instanceof WC_Order) {
		mareli_mp_sync_order($order);
	}
}, 20);

/**
 * Cliente sempre volta para a Compra confirmada — nunca para /admin (Atelier).
 * O Atelier é só da equipe; o pedido aparece lá automaticamente.
 */
add_filter('woocommerce_get_return_url', static function (string $return_url, $order): string {
	if ($order instanceof WC_Order) {
		return $order->get_checkout_order_received_url();
	}
	if (str_contains($return_url, '/admin')) {
		return function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/');
	}
	return $return_url;
}, 99, 2);

add_filter('woocommerce_payment_successful_result', static function ($result, $order_id) {
	$order = wc_get_order((int) $order_id);
	if (! $order instanceof WC_Order || ! is_array($result)) {
		return $result;
	}
	$result['redirect'] = $order->get_checkout_order_received_url();
	return $result;
}, 99, 2);

/** Se o MP devolver em /admin/… com payment_id válido, manda à confirmação. */
add_action('template_redirect', static function (): void {
	if (is_admin() || wp_doing_ajax()) {
		return;
	}
	$request_uri = isset($_SERVER['REQUEST_URI']) ? (string) wp_unslash($_SERVER['REQUEST_URI']) : '';
	if (! str_contains($request_uri, '/admin/')) {
		return;
	}
	$order = mareli_mp_order_from_return_query();
	if ($order instanceof WC_Order) {
		mareli_mp_sync_order($order);
		wp_safe_redirect($order->get_checkout_order_received_url());
		exit;
	}
}, 1);

/**
 * Retorno do Mercado Pago → tela.
 * Só com payment_id/collection_id validado na API (nunca só external_reference).
 */
add_action('template_redirect', static function (): void {
	if (is_admin() || wp_doing_ajax()) {
		return;
	}
	if (function_exists('is_order_received_page') && is_order_received_page()) {
		return;
	}
	$order = mareli_mp_order_from_return_query();
	if (! $order instanceof WC_Order) {
		return;
	}

	mareli_mp_sync_order($order);
	$url = $order->get_checkout_order_received_url();
	if ($url) {
		wp_safe_redirect($url);
		exit;
	}
}, 5);

/** No admin Woo / Atelier, sincroniza pedidos pendentes com ID MP. */
add_action('admin_init', static function (): void {
	if (! current_user_can('manage_woocommerce')) {
		return;
	}
	$orders = wc_get_orders(
		[
			'status' => ['pending', 'on-hold'],
			'limit'  => 8,
			'orderby'=> 'date',
			'order'  => 'DESC',
		]
	);
	foreach ($orders as $order) {
		if ($order instanceof WC_Order) {
			mareli_mp_sync_order($order);
		}
	}
}, 30);

/** Cron leve a cada 5 min. */
add_action('init', static function (): void {
	if (! wp_next_scheduled('mareli_mp_sync_pending_orders')) {
		wp_schedule_event(time() + 60, 'mareli_five_minutes', 'mareli_mp_sync_pending_orders');
	}
});

add_filter('cron_schedules', static function (array $schedules): array {
	$schedules['mareli_five_minutes'] = [
		'interval' => 300,
		'display'  => 'Mareli a cada 5 minutos',
	];
	return $schedules;
});

add_action('mareli_mp_sync_pending_orders', static function (): void {
	$orders = wc_get_orders(
		[
			'status' => ['pending', 'on-hold'],
			'limit'  => 20,
			'orderby'=> 'date',
			'order'  => 'DESC',
		]
	);
	foreach ($orders as $order) {
		if ($order instanceof WC_Order) {
			mareli_mp_sync_order($order);
		}
	}
});
