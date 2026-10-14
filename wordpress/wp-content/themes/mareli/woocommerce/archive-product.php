<?php
/**
 * Catálogo — toolbar + Escolha sob medida (paridade Next CatalogToolbar).
 *
 * @package Mareli
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$facets     = mareli_catalog_facets();
$current    = mareli_catalog_current_filters();
$conditions = function_exists('MARELI_CORE_conditions') ? MARELI_CORE_conditions() : [];
$has_space  = mareli_catalog_has_space_filter($current);
$fit_mode   = $current['fitMode'] === 'combo' ? 'combo' : 'single';
$combos     = ($fit_mode === 'combo' && $current['spaceWidth'] !== '')
	? mareli_catalog_space_combinations($current, 12)
	: [];
$total = (int) wc_get_loop_prop('total');
$shop  = home_url('/produtos/');
$filters_open = (
	$current['q'] !== ''
	|| $current['category'] !== ''
	|| $current['size'] !== ''
	|| $current['brand'] !== ''
	|| $current['condition'] !== ''
	|| ($current['availability'] !== '' && $current['availability'] !== 'available')
	|| ($current['sort'] !== '' && $current['sort'] !== 'recent')
);
?>
<section class="Mareli-catalog">
	<p class="eyebrow eyebrow--wine">Catálogo</p>
	<h1 class="Mareli-catalog__title display">Móveis</h1>
	<p class="Mareli-catalog__note">Informe suas medidas em Escolha sob medida: um móvel só, ou uma combinação que some no espaço.</p>

	<div class="measure-panel<?php echo $has_space ? ' is-open' : ''; ?>" data-measure-panel>
		<button type="button" class="measure-panel__toggle" data-measure-toggle aria-expanded="<?php echo $has_space ? 'true' : 'false'; ?>">
			<span class="measure-panel__lead">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M21.3 15.3a2.4 2.4 0 0 1 0 3.4l-2.6 2.6a2.4 2.4 0 0 1-3.4 0L2.7 8.7a2.4 2.4 0 0 1 0-3.4l2.6-2.6a2.4 2.4 0 0 1 3.4 0Z"/><path d="m14.5 12.5 2-2"/><path d="m11.5 9.5 2-2"/><path d="m8.5 6.5 2-2"/><path d="m17.5 15.5 2-2"/></svg>
				<span>
					<span class="measure-panel__label">Escolha sob medida</span>
					<span class="measure-panel__hint">Informe o espaço e escolha 1 móvel ou uma combinação que some nesse vão.</span>
				</span>
			</span>
			<span class="measure-panel__state" data-measure-state><?php echo $has_space ? 'Fechar' : 'Abrir'; ?></span>
		</button>

		<div class="measure-panel__body" data-measure-body<?php echo $has_space ? '' : ' hidden'; ?>>
			<form class="measure-panel__form" method="get" action="<?php echo esc_url($shop); ?>">
				<?php foreach (['q', 'category', 'size', 'brand', 'condition', 'availability', 'sort'] as $key) : ?>
					<?php if (! empty($current[ $key ]) && ! ($key === 'availability' && $current[ $key ] === 'available') && ! ($key === 'sort' && $current[ $key ] === 'recent')) : ?>
						<input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($current[ $key ]); ?>">
					<?php endif; ?>
				<?php endforeach; ?>

				<fieldset class="measure-panel__modes">
					<legend>Como encaixar no espaço</legend>
					<label class="measure-mode<?php echo $fit_mode === 'single' ? ' is-active' : ''; ?>">
						<input type="radio" name="fitMode" value="single" <?php checked($fit_mode, 'single'); ?>>
						<span>
							<span class="measure-mode__title">1 móvel</span>
							<span class="measure-mode__text">Um único móvel que cabe nas medidas informadas.</span>
						</span>
					</label>
					<label class="measure-mode<?php echo $fit_mode === 'combo' ? ' is-active' : ''; ?>">
						<input type="radio" name="fitMode" value="combo" <?php checked($fit_mode, 'combo'); ?>>
						<span>
							<span class="measure-mode__title">Combinação</span>
							<span class="measure-mode__text">Soma de 2 ou 3 móveis lado a lado que cabem na largura.</span>
						</span>
					</label>
				</fieldset>

				<div class="measure-panel__fields">
					<label>
						Largura disponível (cm)
						<input type="number" name="spaceWidth" min="1" step="1" placeholder="Ex.: 200" value="<?php echo esc_attr($current['spaceWidth']); ?>">
					</label>
					<label>
						Profundidade disponível (cm)
						<input type="number" name="spaceDepth" min="1" step="1" placeholder="Ex.: 90" value="<?php echo esc_attr($current['spaceDepth']); ?>">
					</label>
					<label>
						Altura disponível (cm)
						<input type="number" name="spaceHeight" min="1" step="1" placeholder="Ex.: 80" value="<?php echo esc_attr($current['spaceHeight']); ?>">
					</label>
					<div class="measure-panel__actions">
						<button type="submit" class="btn">Ver que cabem</button>
						<?php if ($has_space) : ?>
							<a class="btn btn--ghost" href="<?php echo esc_url(mareli_catalog_url(['spaceWidth' => '', 'spaceHeight' => '', 'spaceDepth' => '', 'fitMode' => 'single'])); ?>">Limpar</a>
						<?php endif; ?>
					</div>
				</div>
			</form>
			<p class="measure-panel__note">
				Em <strong>1 móvel</strong>, cada peça precisa caber sozinha. Em <strong>combinação</strong>, somamos as larguras de 2 ou 3 peças lado a lado (profundidade e altura ainda precisam caber em cada uma). Informe a largura para usar combinação.
			</p>
		</div>
	</div>

	<div class="filters-collapse<?php echo $filters_open ? ' is-open' : ''; ?>" data-filters-collapse>
		<button type="button" class="filters-collapse__toggle" data-filters-collapse-toggle aria-expanded="<?php echo $filters_open ? 'true' : 'false'; ?>">
			<span>
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M4 5h16"/><path d="M7 12h10"/><path d="M10 19h4"/></svg>
				Filtros
			</span>
			<svg class="filters-collapse__chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
		</button>
		<div class="filters-collapse__body" data-filters-collapse-body<?php echo $filters_open ? '' : ' hidden'; ?>>
			<form class="catalog-toolbar" method="get" action="<?php echo esc_url($shop); ?>">
				<?php foreach (['spaceWidth', 'spaceHeight', 'spaceDepth'] as $key) : ?>
					<?php if ($current[ $key ] !== '') : ?>
						<input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($current[ $key ]); ?>">
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if ($fit_mode === 'combo') : ?>
					<input type="hidden" name="fitMode" value="combo">
				<?php endif; ?>

				<label>
					Busca
					<input type="search" name="q" value="<?php echo esc_attr($current['q']); ?>" placeholder="Buscar móvel">
				</label>
				<label>
					Categoria
					<select name="category" onchange="this.form.submit()">
						<option value="">Todas</option>
						<?php foreach ($facets['categories'] as $cat) : ?>
							<option value="<?php echo esc_attr($cat['slug']); ?>" <?php selected($current['category'], $cat['slug']); ?>><?php echo esc_html($cat['name']); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					Porte
					<select name="size" onchange="this.form.submit()">
						<option value="">Todos</option>
						<?php foreach ($facets['sizes'] as $size) : ?>
							<option value="<?php echo esc_attr($size); ?>" <?php selected($current['size'], $size); ?>><?php echo esc_html($size); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					Marca
					<select name="brand" onchange="this.form.submit()">
						<option value="">Todas</option>
						<?php foreach ($facets['brands'] as $brand) : ?>
							<option value="<?php echo esc_attr($brand); ?>" <?php selected($current['brand'], $brand); ?>><?php echo esc_html($brand); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					Condição
					<select name="condition" onchange="this.form.submit()">
						<option value="">Todas</option>
						<?php foreach ($conditions as $value => $label) : ?>
							<option value="<?php echo esc_attr($value); ?>" <?php selected($current['condition'], $value); ?>><?php echo esc_html($label); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label>
					Disponibilidade
					<select name="availability" onchange="this.form.submit()">
						<option value="available" <?php selected($current['availability'], 'available'); ?>>Disponíveis</option>
						<option value="sold" <?php selected($current['availability'], 'sold'); ?>>Vendidos</option>
						<option value="all" <?php selected($current['availability'], 'all'); ?>>Todos</option>
					</select>
				</label>
				<label>
					Ordenar
					<select name="sort" onchange="this.form.submit()">
						<option value="recent" <?php selected($current['sort'], 'recent'); ?>>Mais recentes</option>
						<option value="price-asc" <?php selected($current['sort'], 'price-asc'); ?>>Menor preço</option>
						<option value="price-desc" <?php selected($current['sort'], 'price-desc'); ?>>Maior preço</option>
						<option value="name" <?php selected($current['sort'], 'name'); ?>>Nome</option>
					</select>
				</label>
				<button type="submit" class="sr-only">Filtrar</button>
			</form>
		</div>
	</div>

	<?php if ($fit_mode === 'combo' && $current['spaceWidth'] !== '') : ?>
		<?php if ($has_space) : ?>
			<p class="Mareli-catalog__space-msg">
				Combinando móveis para largura de <strong><?php echo esc_html($current['spaceWidth']); ?> cm</strong>
				<?php if ($current['spaceDepth'] !== '') : ?> · profundidade até <?php echo esc_html($current['spaceDepth']); ?> cm<?php endif; ?>
				<?php if ($current['spaceHeight'] !== '') : ?> · altura até <?php echo esc_html($current['spaceHeight']); ?> cm<?php endif; ?>.
			</p>
		<?php endif; ?>

		<?php if (! $combos) : ?>
			<p class="Mareli-catalog__empty">Nenhuma combinação cabe nessas medidas com os filtros atuais.</p>
		<?php else : ?>
			<div class="combo-grid">
				<?php foreach ($combos as $index => $combo) : ?>
					<article class="combo-card">
						<div class="combo-card__head">
							<div>
								<p class="combo-card__kicker">Combinação <?php echo esc_html((string) ($index + 1)); ?> · <?php echo esc_html((string) count($combo['items'])); ?> móveis</p>
								<p class="combo-card__width">
									<?php echo esc_html((string) (int) $combo['totalWidth']); ?> cm
									<span>de <?php echo esc_html($current['spaceWidth']); ?> cm · sobram <?php echo esc_html((string) $combo['leftover']); ?> cm</span>
								</p>
							</div>
							<p class="combo-card__price"><?php echo wp_kses_post(mareli_format_brl((float) $combo['totalPrice'])); ?></p>
						</div>
						<div class="combo-card__items">
							<?php foreach ($combo['items'] as $item_index => $item) : ?>
								<?php if (! $item instanceof WC_Product) { continue; } ?>
								<div class="combo-card__item">
									<?php if ($item_index > 0) : ?><span class="combo-card__plus" aria-hidden="true">+</span><?php endif; ?>
									<a href="<?php echo esc_url(get_permalink($item->get_id())); ?>">
										<div class="combo-card__media">
											<?php echo mareli_product_card_image($item, 'medium'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
										</div>
										<div class="combo-card__body">
											<p class="product-card__dims"><?php echo esc_html(mareli_product_dimensions($item)); ?></p>
											<h3 class="product-card__title"><?php echo esc_html($item->get_name()); ?></h3>
											<p class="product-card__price"><?php echo wp_kses_post(mareli_format_brl((float) $item->get_price())); ?></p>
										</div>
									</a>
								</div>
							<?php endforeach; ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<?php if ($has_space) : ?>
			<p class="Mareli-catalog__space-msg">
				Mostrando móveis que cabem em
				<?php
				$bits = [];
				if ($current['spaceWidth'] !== '') {
					$bits[] = 'largura ≤ ' . $current['spaceWidth'] . ' cm';
				}
				if ($current['spaceDepth'] !== '') {
					$bits[] = 'profundidade ≤ ' . $current['spaceDepth'] . ' cm';
				}
				if ($current['spaceHeight'] !== '') {
					$bits[] = 'altura ≤ ' . $current['spaceHeight'] . ' cm';
				}
				echo esc_html(implode(' · ', $bits));
				?>.
			</p>
		<?php endif; ?>

		<p class="Mareli-catalog__count">
			Você está vendo <strong><?php echo esc_html((string) $total); ?></strong>
			<?php echo esc_html($total === 1 ? 'resultado' : 'resultados'); ?>
		</p>

		<?php if (woocommerce_product_loop()) : ?>
			<?php woocommerce_product_loop_start(); ?>
			<?php while (have_posts()) : the_post(); ?>
				<?php wc_get_template_part('content', 'product'); ?>
			<?php endwhile; ?>
			<?php woocommerce_product_loop_end(); ?>
			<?php do_action('woocommerce_after_shop_loop'); ?>
		<?php else : ?>
			<p class="Mareli-catalog__empty">Nenhuma peça encontrada com esses filtros.</p>
		<?php endif; ?>
	<?php endif; ?>
</section>
