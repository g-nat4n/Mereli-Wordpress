<?php
/**
 * Acompanhar pedido — PT + foto + voltar.
 *
 * @package Mareli
 * @var WC_Order $order
 * @var int      $order_id
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

if (! isset($order) || ! $order instanceof WC_Order) {
	$order = wc_get_order((int) ($order_id ?? 0));
}
if (! $order instanceof WC_Order) {
	echo '<p class="Mareli-account__empty">' . esc_html__('Pedido não encontrado.', 'mareli') . '</p>';
	return;
}

$account_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/minha-conta/');
$notes       = $order->get_customer_order_notes();
$status_name = wc_get_order_status_name($order->get_status());
?>

<section class="Mareli-account Mareli-account--endpoint Mareli-view-order">
	<p class="Mareli-account__back">
		<a href="<?php echo esc_url($account_url); ?>">← <?php esc_html_e('Voltar à área do cliente', 'mareli'); ?></a>
	</p>

	<p class="eyebrow"><?php esc_html_e('Pedido', 'mareli'); ?></p>
	<h1 class="Mareli-account__hello">#<?php echo esc_html($order->get_order_number()); ?></h1>
	<p class="Mareli-view-order__status">
		<?php
		echo esc_html(
			sprintf(
				/* translators: 1: order date 2: order status */
				__('Feito em %1$s · Situação: %2$s', 'mareli'),
				wc_format_datetime($order->get_date_created()),
				$status_name
			)
		);
		?>
	</p>

	<?php if ($notes) : ?>
		<section class="Mareli-view-order__updates">
			<h2><?php esc_html_e('Atualizações do pedido', 'mareli'); ?></h2>
			<ol>
				<?php foreach ($notes as $note) : ?>
					<li>
						<strong><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($note->comment_date))); ?></strong>
						<div><?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?></div>
					</li>
				<?php endforeach; ?>
			</ol>
		</section>
	<?php endif; ?>

	<?php
	// Render direto (não depender só do hook — evita tela em branco).
	wc_get_template(
		'order/order-details.php',
		[
			'order'          => $order,
			'order_id'       => $order->get_id(),
			'show_downloads' => false,
		]
	);
	?>
</section>
