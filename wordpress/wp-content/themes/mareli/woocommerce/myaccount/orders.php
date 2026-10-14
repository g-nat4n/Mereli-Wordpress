<?php
/**
 * Pedidos — área do cliente (padrão Mareli).
 *
 * @package Mareli
 * @var bool  $has_orders
 * @var mixed $customer_orders
 * @var int   $current_page
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$orders = [];
if (is_array($customer_orders)) {
	foreach ($customer_orders as $row) {
		$order = $row instanceof WC_Order ? $row : wc_get_order($row);
		if ($order instanceof WC_Order) {
			$orders[] = $order;
		}
	}
} elseif (is_object($customer_orders) && isset($customer_orders->orders) && is_array($customer_orders->orders)) {
	foreach ($customer_orders->orders as $row) {
		$order = $row instanceof WC_Order ? $row : wc_get_order($row);
		if ($order instanceof WC_Order) {
			$orders[] = $order;
		}
	}
}

$max_pages = 1;
if (is_object($customer_orders) && isset($customer_orders->max_num_pages)) {
	$max_pages = (int) $customer_orders->max_num_pages;
} elseif (isset($max_num_pages)) {
	$max_pages = (int) $max_num_pages;
}
?>
<section class="Mareli-account Mareli-account--endpoint">
	<p class="Mareli-account__back">
		<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">← <?php esc_html_e('Voltar à área do cliente', 'mareli'); ?></a>
	</p>
	<p class="eyebrow"><?php esc_html_e('Área do cliente', 'mareli'); ?></p>
	<h1 class="Mareli-account__hello"><?php esc_html_e('Pedidos', 'mareli'); ?></h1>
	<p class="Mareli-account__hint"><?php esc_html_e('Acompanhe o andamento das suas compras.', 'mareli'); ?></p>

	<?php if (! empty($has_orders) && $orders) : ?>
		<ul class="Mareli-account__orders">
			<?php foreach ($orders as $order) :
				$status     = $order->get_status();
				$status_lbl = wc_get_order_status_name($status);
				$bucket     = in_array($status, ['cancelled', 'refunded', 'failed'], true)
					? 'cancelled'
					: (in_array($status, ['completed'], true) ? 'done' : 'active');
				$names      = [];
				$cover_html = '';
				foreach ($order->get_items() as $item) {
					$names[] = $item->get_name();
					if ($cover_html === '' && $item instanceof WC_Order_Item_Product) {
						$p = $item->get_product();
						if ($p instanceof WC_Product) {
							$cover_html = $p->get_image('woocommerce_thumbnail', ['class' => 'Mareli-account__order-cover-img']);
						}
					}
				}
				$date = $order->get_date_created();
				$date_label = $date ? $date->date_i18n('d M Y') : '';
				?>
				<li>
					<a class="Mareli-account__order" href="<?php echo esc_url($order->get_view_order_url()); ?>">
						<div class="Mareli-account__order-cover">
							<?php echo $cover_html ?: '<span class="Mareli-account__order-cover-ph" aria-hidden="true"></span>'; // phpcs:ignore ?>
						</div>
						<div class="Mareli-account__order-body">
							<div class="Mareli-account__order-top">
								<strong>#<?php echo esc_html($order->get_order_number()); ?></strong>
								<span class="Mareli-account__badge Mareli-account__badge--<?php echo esc_attr($bucket); ?>">
									<?php echo esc_html($status_lbl); ?>
								</span>
							</div>
							<p class="Mareli-account__order-items"><?php echo esc_html(implode(' · ', $names)); ?></p>
							<?php if ($date_label) : ?>
								<p class="Mareli-account__order-meta"><?php echo esc_html($date_label); ?></p>
							<?php endif; ?>
						</div>
						<div class="Mareli-account__order-side">
							<span class="Mareli-account__order-total"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></span>
							<span class="Mareli-account__order-cta"><?php esc_html_e('Ver pedido', 'mareli'); ?></span>
						</div>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ($max_pages > 1) : ?>
			<nav class="Mareli-account__pagination woocommerce-pagination">
				<?php
				echo wp_kses_post(
					paginate_links(
						[
							'base'      => esc_url_raw(str_replace(999999999, '%#%', remove_query_arg('add-to-cart', get_pagenum_link(999999999, false)))),
							'format'    => '',
							'current'   => max(1, (int) $current_page),
							'total'     => $max_pages,
							'prev_text' => '&larr;',
							'next_text' => '&rarr;',
							'type'      => 'list',
						]
					)
				);
				?>
			</nav>
		<?php endif; ?>
	<?php else : ?>
		<p class="Mareli-account__empty">
			<?php esc_html_e('Você ainda não fez um pedido.', 'mareli'); ?>
			<a href="<?php echo esc_url(home_url('/produtos/')); ?>"><?php esc_html_e('Ver curadoria', 'mareli'); ?></a>
		</p>
	<?php endif; ?>
</section>
