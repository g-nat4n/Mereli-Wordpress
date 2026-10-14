<?php
/**
 * PDP — paridade com ProductDetails / ProductGallery do Next.
 *
 * @package Mareli
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;
if (! $product instanceof WC_Product) {
	return;
}

$id          = $product->get_id();
$brand       = (string) get_post_meta($id, '_mareli_brand', true);
$size        = (string) get_post_meta($id, '_mareli_size', true);
$color       = (string) get_post_meta($id, '_mareli_color', true);
$condition   = (string) get_post_meta($id, '_mareli_condition', true);
$story       = (string) get_post_meta($id, '_mareli_story', true);
$unique      = get_post_meta($id, '_mareli_unique_piece', true) !== 'no';
$measures    = get_post_meta($id, '_mareli_measurements', true);
if (! is_array($measures)) {
	$measures = [];
}
$conditions  = function_exists('MARELI_CORE_conditions') ? MARELI_CORE_conditions() : [];
$terms       = get_the_terms($id, 'product_cat');
$cat_name    = ($terms && ! is_wp_error($terms)) ? $terms[0]->name : '';
$sold        = ! $product->is_in_stock();
$stock_qty   = max(0, (int) $product->get_stock_quantity());
if ($unique && ! $sold) {
	$stock_qty = max(1, $stock_qty);
}
$price       = (float) $product->get_price();
$regular     = (float) $product->get_regular_price();
$on_sale     = $product->is_on_sale() && $regular > $price;

$gallery_ids = $product->get_gallery_image_ids();
$image_ids   = array_values(array_unique(array_filter(array_merge(
	[(int) $product->get_image_id()],
	array_map('intval', $gallery_ids)
))));

$shop_url = home_url('/produtos/');

// Carrosséis de baixo (estilo Mercado Livre / Next).
$exclude_ids = [$id];
$look_products = [];
$look_terms = get_the_terms($id, 'look');
if ($look_terms && ! is_wp_error($look_terms)) {
	$look_ids = get_posts([
		'post_type'      => 'product',
		'posts_per_page' => 12,
		'post_status'    => 'publish',
		'post__not_in'   => $exclude_ids,
		'fields'         => 'ids',
		'tax_query'      => [[
			'taxonomy' => 'look',
			'field'    => 'term_id',
			'terms'    => [(int) $look_terms[0]->term_id],
		]],
	]);
	foreach ($look_ids as $look_pid) {
		$lp = wc_get_product((int) $look_pid);
		if ($lp instanceof WC_Product) {
			$look_products[] = $lp;
			$exclude_ids[] = (int) $look_pid;
		}
	}
}

$suggestion_products = mareli_query_products([
	'limit'   => 12,
	'exclude' => $exclude_ids,
	'orderby' => 'rand',
]);
?>
<div <?php wc_product_class('Mareli-pdp', $product); ?> data-product-id="<?php echo esc_attr((string) $id); ?>">
	<?php do_action('woocommerce_before_single_product'); ?>

	<section class="Mareli-pdp__main">
		<div class="Mareli-pdp__back-mobile">
			<a href="<?php echo esc_url($shop_url); ?>">← Voltar aos móveis</a>
		</div>

		<div class="Mareli-pdp__grid">
			<div class="Mareli-pdp__gallery" data-pdp-gallery>
				<?php if ($image_ids) : ?>
					<div class="Mareli-pdp__stage <?php echo $sold ? 'is-sold' : ''; ?>">
						<?php
						$main = wp_get_attachment_image_src((int) $image_ids[0], 'large');
						$alt  = get_post_meta((int) $image_ids[0], '_wp_attachment_image_alt', true) ?: $product->get_name();
						?>
						<img
							src="<?php echo esc_url($main[0] ?? ''); ?>"
							alt="<?php echo esc_attr((string) $alt); ?>"
							data-pdp-main
							width="<?php echo esc_attr((string) ($main[1] ?? 800)); ?>"
							height="<?php echo esc_attr((string) ($main[2] ?? 1000)); ?>"
						>
						<?php if ($sold) : ?>
							<span class="Mareli-pdp__sold-tag">Vendida</span>
						<?php endif; ?>
					</div>
					<?php if (count($image_ids) > 1) : ?>
						<div class="Mareli-pdp__thumbs" role="list">
							<?php foreach ($image_ids as $index => $img_id) :
								$thumb = wp_get_attachment_image_src((int) $img_id, 'thumbnail');
								$full  = wp_get_attachment_image_src((int) $img_id, 'large');
								if (! $thumb || ! $full) {
									continue;
								}
								?>
								<button
									type="button"
									class="Mareli-pdp__thumb<?php echo $index === 0 ? ' is-active' : ''; ?>"
									data-pdp-thumb
									data-full="<?php echo esc_url($full[0]); ?>"
									aria-label="<?php echo esc_attr(sprintf('Ver imagem %d', $index + 1)); ?>"
									aria-current="<?php echo $index === 0 ? 'true' : 'false'; ?>"
								>
									<img src="<?php echo esc_url($thumb[0]); ?>" alt="" width="64" height="80">
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<div class="Mareli-pdp__stage Mareli-pdp__stage--empty" aria-hidden="true"></div>
				<?php endif; ?>
			</div>

			<div class="Mareli-pdp__summary">
				<a class="Mareli-pdp__back-desktop" href="<?php echo esc_url($shop_url); ?>">← Voltar à curadoria</a>

				<p class="Mareli-pdp__meta-top">
					<?php echo esc_html(trim($brand . ($brand && $cat_name ? ' · ' : '') . $cat_name)); ?>
				</p>

				<h1 class="Mareli-pdp__title"><?php the_title(); ?></h1>

				<div class="Mareli-pdp__price-row">
					<p class="Mareli-pdp__price"><?php echo wp_kses_post(wc_price($price)); ?></p>
					<?php if ($on_sale) : ?>
						<p class="Mareli-pdp__compare"><?php echo wp_kses_post(wc_price($regular)); ?></p>
					<?php endif; ?>
				</div>
				<?php if ($price > 0) : ?>
					<p class="Mareli-pdp__installments">8x <?php echo wp_kses_post(wc_price($price / 8)); ?></p>
				<?php endif; ?>

				<div class="Mareli-pdp__badges">
					<?php if ($sold) : ?>
						<span class="Mareli-badge Mareli-badge--ink">Vendida</span>
					<?php else : ?>
						<?php if ($unique) : ?>
							<span class="Mareli-badge">Peça única</span>
						<?php endif; ?>
						<?php if ($stock_qty === 1) : ?>
							<span class="Mareli-badge Mareli-badge--wine">Última unidade</span>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<dl class="Mareli-pdp__facts">
					<div>
						<dt>Tamanho</dt>
						<dd><?php echo esc_html($size ?: '—'); ?></dd>
					</div>
					<div>
						<dt>Cor</dt>
						<dd><?php echo esc_html($color ?: '—'); ?></dd>
					</div>
					<div>
						<dt>Condição</dt>
						<dd><?php echo esc_html(($condition && isset($conditions[$condition])) ? $conditions[$condition] : ($condition ?: '—')); ?></dd>
					</div>
					<div>
						<dt>Estoque</dt>
						<dd><?php echo $sold ? esc_html__('Indisponível', 'mareli') : esc_html($stock_qty . ' un.'); ?></dd>
					</div>
				</dl>

				<?php if (! $sold) : ?>
					<div class="Mareli-pdp__actions">
						<button type="button" class="btn btn--ghost" data-add-to-cart="<?php echo esc_attr((string) $id); ?>">
							Adicionar à sacola
						</button>
						<a class="btn btn--wine" href="<?php echo esc_url(mareli_product_whatsapp_url($product)); ?>" target="_blank" rel="noopener noreferrer">
							Comprar pelo WhatsApp
						</a>
					</div>
				<?php endif; ?>

				<?php if ($product->get_description()) : ?>
					<p class="Mareli-pdp__desc"><?php echo esc_html(wp_strip_all_tags($product->get_description())); ?></p>
				<?php endif; ?>

				<?php if ($story) : ?>
					<p class="Mareli-pdp__story"><?php echo esc_html(wp_strip_all_tags($story)); ?></p>
				<?php endif; ?>

				<?php
				$has_measures = false;
				foreach ($measures as $v) {
					if ($v !== '' && $v !== null) {
						$has_measures = true;
						break;
					}
				}
				if ($has_measures) :
					$map = [
						'width' => 'Largura', 'height' => 'Altura', 'depth' => 'Profundidade',
						'length' => 'Comprimento',
					];
					?>
					<div class="Mareli-pdp__measures">
						<p class="Mareli-pdp__label">Medidas aproximadas</p>
						<ul>
							<?php foreach ($map as $key => $label) :
								if (empty($measures[$key])) {
									continue;
								}
								?>
								<li>
									<span><?php echo esc_html($label); ?></span>
									<span><?php echo esc_html((string) $measures[$key]); ?></span>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>
				<?php endif; ?>

				<p class="Mareli-pdp__shipping-note">
					Entrega e retirada combinadas pelo WhatsApp · (41) 99171-0412
				</p>
			</div>
		</div>
	</section>

	<?php
	if ($look_products) {
		$look_name = ($look_terms && ! is_wp_error($look_terms)) ? $look_terms[0]->name : 'O look';
		mareli_product_rail('Complete o look', $look_name, $look_products);
	}
	if ($suggestion_products) {
		mareli_product_rail('Para você', 'Quem viu este item também se interessou', $suggestion_products);
	}
	?>

	<?php if (! $sold) : ?>
		<div class="Mareli-pdp__sticky" data-pdp-sticky>
			<div class="Mareli-pdp__sticky-inner">
				<div class="Mareli-pdp__sticky-info">
					<p class="Mareli-pdp__sticky-name"><?php echo esc_html($product->get_name()); ?></p>
					<div class="Mareli-pdp__sticky-price">
						<span><?php echo wp_kses_post(wc_price($price)); ?></span>
						<?php if ($on_sale) : ?>
							<s><?php echo wp_kses_post(wc_price($regular)); ?></s>
						<?php endif; ?>
					</div>
				</div>
				<a class="btn btn--wine" href="<?php echo esc_url(mareli_product_whatsapp_url($product)); ?>" target="_blank" rel="noopener noreferrer">
					Comprar pelo WhatsApp
				</a>
			</div>
		</div>
	<?php else : ?>
		<div class="Mareli-pdp__sticky Mareli-pdp__sticky--sold" data-pdp-sticky>
			<p>Peça vendida</p>
		</div>
	<?php endif; ?>

	<?php do_action('woocommerce_after_single_product'); ?>
</div>
