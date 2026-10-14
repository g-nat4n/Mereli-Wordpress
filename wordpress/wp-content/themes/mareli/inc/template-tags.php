<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

function mareli_cart_count_badge(): void {
	$count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	echo '<span class="cart-count"' . ($count ? '' : ' hidden') . '>' . esc_html((string) $count) . '</span>';
}

/**
 * @return array<int, WC_Product>
 */
function mareli_get_featured_products(int $limit = 8): array {
	if (! function_exists('wc_get_products')) {
		return [];
	}
	return wc_get_products([
		'status'   => 'publish',
		'limit'    => $limit,
		'featured' => true,
		'orderby'  => 'date',
		'order'    => 'DESC',
	]);
}

/**
 * @return array<int, WC_Product>
 */
function mareli_get_bestseller_products(int $limit = 12): array {
	if (! function_exists('wc_get_products')) {
		return [];
	}
	$ids = get_option('mareli_bestsellers', []);
	if (! is_array($ids) || ! $ids) {
		return wc_get_products([
			'status'  => 'publish',
			'limit'   => $limit,
			'orderby' => 'popularity',
		]);
	}
	$ids = array_map('absint', $ids);
	return wc_get_products([
		'status'  => 'publish',
		'include' => array_slice($ids, 0, $limit),
		'orderby' => 'include',
		'limit'   => $limit,
	]);
}

function mareli_condition_label(string $condition): string {
	$map = [
		'NEW_WITH_TAG' => __('Nova com etiqueta', 'mareli'),
		'EXCELLENT'    => __('Excelente', 'mareli'),
		'VERY_GOOD'    => __('Muito boa', 'mareli'),
		'GOOD'         => __('Boa', 'mareli'),
		'VINTAGE'      => __('Retrô', 'mareli'),
	];
	return $map[$condition] ?? $condition;
}

/**
 * Dígitos do WhatsApp (option `mareli_whatsapp`, padrão Curitiba).
 */
function mareli_whatsapp_digits(): string {
	$raw = (string) get_option('mareli_whatsapp', '5541991710412');
	$digits = preg_replace('/\D+/', '', $raw) ?? '';
	return $digits !== '' ? $digits : '5541991710412';
}

/**
 * URL do WhatsApp da loja (só dígitos com DDI).
 */
function mareli_whatsapp_url(string $prefill = ''): string {
	$url = 'https://wa.me/' . mareli_whatsapp_digits();
	if ($prefill !== '') {
		$url = add_query_arg('text', rawurlencode($prefill), $url);
	}
	return $url;
}

/**
 * Mensagem WhatsApp para um produto.
 */
function mareli_product_whatsapp_message(WC_Product $product): string {
	$price = html_entity_decode(wp_strip_all_tags(wc_price((float) $product->get_price())), ENT_QUOTES, 'UTF-8');
	$link  = get_permalink($product->get_id()) ?: home_url('/');
	return sprintf(
		"Olá! Quero comprar pelo WhatsApp o móvel \"%s\" (%s).\n%s",
		$product->get_name(),
		$price,
		$link
	);
}

function mareli_product_whatsapp_url(WC_Product $product): string {
	return mareli_whatsapp_url(mareli_product_whatsapp_message($product));
}

/**
 * Mensagem WhatsApp com itens da sacola.
 */
function mareli_cart_whatsapp_url(): string {
	$cart = function_exists('WC') ? WC()->cart : null;
	if (! $cart || $cart->is_empty()) {
		return mareli_whatsapp_url();
	}

	$lines = [];
	$index = 1;
	foreach ($cart->get_cart() as $item) {
		$product = $item['data'] ?? null;
		if (! $product instanceof WC_Product) {
			continue;
		}
		$qty   = max(1, (int) $item['quantity']);
		$price = html_entity_decode(wp_strip_all_tags($cart->get_product_subtotal($product, $qty)), ENT_QUOTES, 'UTF-8');
		$link  = get_permalink((int) $item['product_id']) ?: '';
		$qty_s = $qty > 1 ? " x{$qty}" : '';
		$lines[] = "{$index}. {$product->get_name()}{$qty_s} — {$price}" . ($link ? "\n{$link}" : '');
		$index++;
	}

	$total = html_entity_decode(wp_strip_all_tags($cart->get_total()), ENT_QUOTES, 'UTF-8');
	$message = "Olá! Quero comprar pelo WhatsApp estes móveis:\n\n" . implode("\n\n", $lines) . "\n\nTotal: {$total}";
	return mareli_whatsapp_url($message);
}

/** Valida CPF brasileiro (dígitos verificadores). */
function mareli_is_valid_cpf(string $cpf): bool {
	$cpf = preg_replace('/\\D+/', '', $cpf) ?? '';
	if (strlen($cpf) !== 11 || preg_match('/^(\\d)\\1{10}$/', $cpf)) {
		return false;
	}
	for ($t = 9; $t < 11; $t++) {
		$sum = 0;
		for ($i = 0; $i < $t; $i++) {
			$sum += (int) $cpf[ $i ] * (($t + 1) - $i);
		}
		$digit = ((10 * $sum) % 11) % 10;
		if ((int) $cpf[ $t ] !== $digit) {
			return false;
		}
	}
	return true;
}

function mareli_format_brl(float $amount): string {
	return 'R$&nbsp;' . number_format($amount, 2, ',', '.');
}

/**
 * Preço sem centavos (formatBrandPrice do Next).
 */
function mareli_format_brand_price(float $amount): string {
	return 'R$&nbsp;' . number_format($amount, 0, ',', '.');
}

/**
 * Medida numérica em cm a partir do meta `_mareli_measurements`.
 */
function mareli_product_measure_cm(WC_Product $product, string $key): ?float {
	$m = get_post_meta($product->get_id(), '_mareli_measurements', true);
	if (! is_array($m) || empty($m[ $key ])) {
		return null;
	}
	$n = (float) preg_replace('/[^\d.]/', '', (string) $m[ $key ]);
	return $n > 0 ? $n : null;
}

/**
 * Dimensões no formato "L × P × A cm" (ProductCard do Next).
 */
function mareli_product_dimensions(WC_Product $product): string {
	$w = mareli_product_measure_cm($product, 'width');
	$d = mareli_product_measure_cm($product, 'depth');
	$h = mareli_product_measure_cm($product, 'height');
	if ($w === null || $d === null || $h === null) {
		return '';
	}
	return (int) $w . ' × ' . (int) $d . ' × ' . (int) $h . ' cm';
}

/**
 * HTML da imagem do card — thumbnail WP ou fallback da URL do seed.
 */
function mareli_product_card_image(WC_Product $product, string $size = 'medium_large'): string {
	if ($product->get_image_id()) {
		$html = $product->get_image($size, [
			'class'   => 'mareli-product-img',
			'loading' => 'lazy',
		]);
		return is_string($html) ? $html : '';
	}
	$url = (string) get_post_meta($product->get_id(), '_mareli_image_url', true);
	if ($url === '') {
		return $product->get_image($size);
	}
	return sprintf(
		'<img src="%s" alt="%s" class="mareli-remote-thumb" loading="lazy" decoding="async" width="600" height="800">',
		esc_url($url),
		esc_attr($product->get_name())
	);
}

/**
 * Carrossel horizontal de produtos.
 *
 * @param WC_Product[] $products
 */
function mareli_product_rail(string $eyebrow, string $title, array $products): void {
	$products = array_values(array_filter($products, static fn ($p) => $p instanceof WC_Product));
	if (! $products) {
		return;
	}
	?>
	<section class="Mareli-rail-section">
		<div class="Mareli-rail-section__head">
			<?php if ($eyebrow !== '') : ?>
				<p class="eyebrow"><?php echo esc_html($eyebrow); ?></p>
			<?php endif; ?>
			<?php if ($title !== '') : ?>
				<h2 class="Mareli-rail-section__title"><?php echo esc_html($title); ?></h2>
			<?php endif; ?>
		</div>
		<div class="product-rail" data-product-rail>
			<button type="button" class="product-rail__nav product-rail__nav--prev" data-rail-prev aria-label="<?php esc_attr_e('Anterior', 'mareli'); ?>">‹</button>
			<div class="product-rail__track" data-rail-track>
				<?php foreach ($products as $product) : ?>
					<div class="product-rail__item">
						<?php mareli_product_card($product); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="product-rail__nav product-rail__nav--next" data-rail-next aria-label="<?php esc_attr_e('Próximo', 'mareli'); ?>">›</button>
		</div>
	</section>
	<?php
}

/**
 * Card editorial da curadoria da home (FeaturedCuradoria do Next).
 */
function mareli_curadoria_card(WC_Product $product): void {
	$link     = get_permalink($product->get_id());
	$price    = (float) $product->get_price();
	$sold     = ! $product->is_in_stock();
	$brand    = (string) get_post_meta($product->get_id(), '_mareli_brand', true);
	$material = (string) get_post_meta($product->get_id(), '_mareli_material', true);
	$cats     = get_the_terms($product->get_id(), 'product_cat');
	$cat_name = (is_array($cats) && $cats && ! is_wp_error($cats)) ? $cats[0]->name : '';
	$cat_slug = (is_array($cats) && $cats && ! is_wp_error($cats)) ? $cats[0]->slug : '';
	$meta_bits = array_filter([$cat_name, $material]);
	$meta      = implode(' · ', $meta_bits);
	$style     = $material !== '' ? $material : '';
	?>
	<article
		class="curadoria-card"
		data-curadoria-card
		data-name="<?php echo esc_attr(mb_strtolower($product->get_name() . ' ' . $meta . ' ' . $cat_name)); ?>"
		data-category="<?php echo esc_attr($cat_name); ?>"
		data-category-slug="<?php echo esc_attr($cat_slug); ?>"
		data-style="<?php echo esc_attr($style); ?>"
		data-price="<?php echo esc_attr((string) (int) round($price * 100)); ?>"
	>
		<div class="curadoria-card__media">
			<a href="<?php echo esc_url($link); ?>">
				<?php
				$img = mareli_product_card_image($product, 'large');
				if ($sold) {
					$img = str_replace('<img ', '<img class="is-sold" ', $img);
				}
				echo $img; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</a>
			<?php if ($sold) : ?>
				<span class="curadoria-card__badge"><?php esc_html_e('Vendido', 'mareli'); ?></span>
			<?php endif; ?>
			<button type="button" class="curadoria-card__fav" data-fav aria-label="<?php echo esc_attr(sprintf(__('Favoritar %s', 'mareli'), $product->get_name())); ?>">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
			</button>
		</div>
		<div class="curadoria-card__body">
			<div class="curadoria-card__row">
				<div>
					<h3 class="curadoria-card__title">
						<a href="<?php echo esc_url($link); ?>"><?php echo esc_html($product->get_name()); ?></a>
					</h3>
					<?php if ($meta) : ?>
						<p class="curadoria-card__meta"><?php echo esc_html($meta); ?></p>
					<?php endif; ?>
				</div>
				<p class="curadoria-card__price"><?php echo wp_kses_post(mareli_format_brand_price($price)); ?></p>
			</div>
			<?php if (! $sold) : ?>
				<a class="curadoria-card__cta" href="<?php echo esc_url(mareli_product_whatsapp_url($product)); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e('Comprar pelo WhatsApp', 'mareli'); ?>
				</a>
			<?php else : ?>
				<span class="curadoria-card__cta curadoria-card__cta--disabled"><?php esc_html_e('Indisponível', 'mareli'); ?></span>
			<?php endif; ?>
		</div>
	</article>
	<?php
}

/**
 * @return WC_Product[]
 */
function mareli_query_products(array $args): array {
	$defaults = [
		'status'  => 'publish',
		'limit'   => 12,
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'objects',
	];
	$query = array_merge($defaults, $args);
	$items = wc_get_products($query);
	return is_array($items) ? $items : [];
}

/**
 * ProductCard do Next: imagem 3/4, medidas, nome, marca, preço, WhatsApp.
 */
function mareli_product_card(WC_Product $product): void {
	$link   = get_permalink($product->get_id());
	$price  = (float) $product->get_price();
	$sold   = ! $product->is_in_stock();
	$unique = get_post_meta($product->get_id(), '_mareli_unique_piece', true) !== 'no';
	$stock  = $product->managing_stock() ? (int) $product->get_stock_quantity() : ($sold ? 0 : 2);
	$brand  = (string) get_post_meta($product->get_id(), '_mareli_brand', true);
	$dims   = mareli_product_dimensions($product);
	$cats   = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'slugs']);
	$cat_slug = is_array($cats) && $cats ? (string) $cats[0] : '';
	?>
	<article class="product-card" data-category="<?php echo esc_attr($cat_slug); ?>">
		<a class="product-card__link" href="<?php echo esc_url($link); ?>">
			<div class="product-card__media<?php echo $sold ? ' is-sold' : ''; ?>">
				<?php echo mareli_product_card_image($product); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<div class="product-card__badges">
					<?php if ($sold) : ?>
						<span class="product-card__badge product-card__badge--ink"><?php esc_html_e('Vendido', 'mareli'); ?></span>
					<?php else : ?>
						<?php if ($unique) : ?>
							<span class="product-card__badge product-card__badge--forest"><?php esc_html_e('Exclusivo', 'mareli'); ?></span>
						<?php endif; ?>
						<?php if ($stock === 1) : ?>
							<span class="product-card__badge product-card__badge--wine"><?php esc_html_e('Última unidade', 'mareli'); ?></span>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="product-card__body">
				<?php if ($dims !== '') : ?>
					<p class="product-card__dims"><?php echo esc_html($dims); ?></p>
				<?php endif; ?>
				<h3 class="product-card__title"><?php echo esc_html($product->get_name()); ?></h3>
				<?php if ($brand !== '') : ?>
					<p class="product-card__brand"><?php echo esc_html($brand); ?></p>
				<?php endif; ?>
				<p class="product-card__price"><?php echo wp_kses_post(mareli_format_brl($price)); ?></p>
			</div>
		</a>
		<?php if (! $sold) : ?>
			<a class="product-card__wa" href="<?php echo esc_url(mareli_product_whatsapp_url($product)); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e('Comprar pelo WhatsApp', 'mareli'); ?>
			</a>
		<?php endif; ?>
	</article>
	<?php
}
