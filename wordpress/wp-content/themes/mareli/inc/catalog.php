<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Curadoria — filtros por query string iguais ao Next (/produtos?category=&spaceWidth=...).
 */
add_action('template_redirect', static function (): void {
	if (is_tax('product_cat')) {
		$term = get_queried_object();
		if ($term instanceof WP_Term) {
			wp_safe_redirect(add_query_arg('category', $term->slug, home_url('/produtos/')), 302);
			exit;
		}
	}
});

add_action('pre_get_posts', static function (WP_Query $query): void {
	if (is_admin() || ! $query->is_main_query()) {
		return;
	}
	if (! function_exists('is_shop') || ! is_shop()) {
		return;
	}

	$filters = mareli_catalog_current_filters();

	// Modo combinação: a vitrine de combos é renderizada no template; loop vazio.
	if ($filters['fitMode'] === 'combo' && $filters['spaceWidth'] !== '') {
		$query->set('post__in', [0]);
		$query->set('posts_per_page', 1);
		return;
	}

	$meta_query = [];
	$tax_query  = [];

	if ($filters['category'] !== '') {
		$tax_query[] = [
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => [$filters['category']],
		];
	}

	if ($filters['size'] !== '') {
		$meta_query[] = [
			'key'   => '_mareli_size',
			'value' => $filters['size'],
		];
	}

	if ($filters['brand'] !== '') {
		$meta_query[] = [
			'key'   => '_mareli_brand',
			'value' => $filters['brand'],
		];
	}

	if ($filters['condition'] !== '') {
		$meta_query[] = [
			'key'   => '_mareli_condition',
			'value' => $filters['condition'],
		];
	}

	if ($filters['availability'] === 'available') {
		$meta_query[] = [
			'key'   => '_stock_status',
			'value' => 'instock',
		];
	} elseif ($filters['availability'] === 'sold') {
		$meta_query[] = [
			'key'   => '_stock_status',
			'value' => 'outofstock',
		];
	}

	if ($filters['q'] !== '') {
		$query->set('s', $filters['q']);
	}

	switch ($filters['sort']) {
		case 'price-asc':
			$query->set('orderby', 'meta_value_num');
			$query->set('meta_key', '_price');
			$query->set('order', 'ASC');
			break;
		case 'price-desc':
			$query->set('orderby', 'meta_value_num');
			$query->set('meta_key', '_price');
			$query->set('order', 'DESC');
			break;
		case 'name':
			$query->set('orderby', 'title');
			$query->set('order', 'ASC');
			break;
		default:
			$query->set('orderby', 'date');
			$query->set('order', 'DESC');
			break;
	}

	if ($meta_query) {
		$meta_query['relation'] = 'AND';
		$query->set('meta_query', $meta_query);
	}
	if ($tax_query) {
		$query->set('tax_query', $tax_query);
	}

	$has_space = $filters['spaceWidth'] !== '' || $filters['spaceHeight'] !== '' || $filters['spaceDepth'] !== '';
	if ($has_space && $filters['fitMode'] !== 'combo') {
		$ids = mareli_catalog_fitting_product_ids($filters);
		$query->set('post__in', $ids ?: [0]);
		if ($filters['sort'] === 'recent') {
			$query->set('orderby', 'post__in');
		}
	}

	$query->set('posts_per_page', 24);
});

/**
 * @return array{brands: string[], sizes: string[], categories: array<int, array{name:string,slug:string}>}
 */
function mareli_catalog_facets(): array {
	global $wpdb;

	$brands = $wpdb->get_col(
		"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_mareli_brand' AND pm.meta_value <> '' AND p.post_type = 'product' AND p.post_status = 'publish'
		ORDER BY meta_value ASC"
	);

	$sizes = $wpdb->get_col(
		"SELECT DISTINCT meta_value FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		WHERE pm.meta_key = '_mareli_size' AND pm.meta_value <> '' AND p.post_type = 'product' AND p.post_status = 'publish'
		ORDER BY meta_value ASC"
	);

	$terms = get_terms([
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => [get_option('default_product_cat')],
	]);

	$categories = [];
	if (! is_wp_error($terms)) {
		foreach ($terms as $term) {
			$categories[] = ['name' => $term->name, 'slug' => $term->slug];
		}
	}

	return [
		'brands'     => array_values(array_filter(array_map('strval', $brands ?: []))),
		'sizes'      => array_values(array_filter(array_map('strval', $sizes ?: []))),
		'categories' => $categories,
	];
}

/**
 * @return array<string, string>
 */
function mareli_catalog_current_filters(): array {
	$q = '';
	if (isset($_GET['q'])) {
		$q = sanitize_text_field(wp_unslash((string) $_GET['q']));
	} elseif (isset($_GET['s'])) {
		$q = sanitize_text_field(wp_unslash((string) $_GET['s']));
	}

	$space_w = isset($_GET['spaceWidth']) ? sanitize_text_field(wp_unslash((string) $_GET['spaceWidth'])) : '';
	$space_h = isset($_GET['spaceHeight']) ? sanitize_text_field(wp_unslash((string) $_GET['spaceHeight'])) : '';
	$space_d = isset($_GET['spaceDepth']) ? sanitize_text_field(wp_unslash((string) $_GET['spaceDepth'])) : '';
	$fit     = isset($_GET['fitMode']) ? sanitize_key(wp_unslash((string) $_GET['fitMode'])) : 'single';
	if ($fit !== 'combo') {
		$fit = 'single';
	}

	return [
		'q'            => $q,
		'category'     => isset($_GET['category']) ? sanitize_title(wp_unslash((string) $_GET['category'])) : '',
		'size'         => isset($_GET['size']) ? sanitize_text_field(wp_unslash((string) $_GET['size'])) : '',
		'brand'        => isset($_GET['brand']) ? sanitize_text_field(wp_unslash((string) $_GET['brand'])) : '',
		'condition'    => isset($_GET['condition']) ? sanitize_text_field(wp_unslash((string) $_GET['condition'])) : '',
		'availability' => isset($_GET['availability']) ? sanitize_key(wp_unslash((string) $_GET['availability'])) : 'available',
		'sort'         => isset($_GET['sort']) ? sanitize_key(wp_unslash((string) $_GET['sort'])) : 'recent',
		'spaceWidth'   => $space_w !== '' && (float) $space_w > 0 ? (string) (float) $space_w : '',
		'spaceHeight'  => $space_h !== '' && (float) $space_h > 0 ? (string) (float) $space_h : '',
		'spaceDepth'   => $space_d !== '' && (float) $space_d > 0 ? (string) (float) $space_d : '',
		'fitMode'      => $fit,
	];
}

function mareli_catalog_url(array $overrides = []): string {
	$current = array_merge(mareli_catalog_current_filters(), $overrides);
	$args    = [];
	foreach ($current as $key => $value) {
		if ($value === '' || ($key === 'availability' && $value === 'available') || ($key === 'sort' && $value === 'recent') || ($key === 'fitMode' && $value === 'single')) {
			continue;
		}
		$args[ $key ] = $value;
	}
	return add_query_arg($args, home_url('/produtos/'));
}

function mareli_catalog_has_space_filter(array $filters = []): bool {
	$filters = $filters ?: mareli_catalog_current_filters();
	return $filters['spaceWidth'] !== '' || $filters['spaceHeight'] !== '' || $filters['spaceDepth'] !== '';
}

/**
 * Produto cabe sozinho no vão informado.
 */
function mareli_product_fits_space(WC_Product $product, array $filters): bool {
	$w = mareli_product_measure_cm($product, 'width');
	$h = mareli_product_measure_cm($product, 'height');
	$d = mareli_product_measure_cm($product, 'depth');

	if ($filters['spaceWidth'] !== '' && ($w === null || $w > (float) $filters['spaceWidth'])) {
		return false;
	}
	if ($filters['spaceHeight'] !== '' && ($h === null || $h > (float) $filters['spaceHeight'])) {
		return false;
	}
	if ($filters['spaceDepth'] !== '' && ($d === null || $d > (float) $filters['spaceDepth'])) {
		return false;
	}
	return true;
}

/**
 * @return int[]
 */
function mareli_catalog_candidate_products(array $filters, bool $ignore_space_width = false): array {
	$meta_query = ['relation' => 'AND'];
	$tax_query  = [];

	if ($filters['category'] !== '') {
		$tax_query[] = [
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => [$filters['category']],
		];
	}
	if ($filters['size'] !== '') {
		$meta_query[] = ['key' => '_mareli_size', 'value' => $filters['size']];
	}
	if ($filters['brand'] !== '') {
		$meta_query[] = ['key' => '_mareli_brand', 'value' => $filters['brand']];
	}
	if ($filters['condition'] !== '') {
		$meta_query[] = ['key' => '_mareli_condition', 'value' => $filters['condition']];
	}
	if ($filters['availability'] === 'available') {
		$meta_query[] = ['key' => '_stock_status', 'value' => 'instock'];
	} elseif ($filters['availability'] === 'sold') {
		$meta_query[] = ['key' => '_stock_status', 'value' => 'outofstock'];
	}

	$args = [
		'status'  => 'publish',
		'limit'   => 80,
		'orderby' => 'date',
		'order'   => 'DESC',
		'return'  => 'objects',
	];
	if (count($meta_query) > 1) {
		$args['meta_query'] = $meta_query;
	}
	if ($tax_query) {
		$args['tax_query'] = $tax_query;
	}
	if ($filters['q'] !== '') {
		$args['s'] = $filters['q'];
	}

	$products = function_exists('wc_get_products') ? wc_get_products($args) : [];
	$fit_filters = $filters;
	if ($ignore_space_width) {
		$fit_filters['spaceWidth'] = '';
	}

	$out = [];
	foreach ($products as $product) {
		if (! $product instanceof WC_Product) {
			continue;
		}
		if (! mareli_product_fits_space($product, $fit_filters)) {
			continue;
		}
		$out[] = $product;
	}
	return $out;
}

/**
 * @return int[]
 */
function mareli_catalog_fitting_product_ids(array $filters): array {
	$ids = [];
	foreach (mareli_catalog_candidate_products($filters) as $product) {
		$ids[] = $product->get_id();
	}
	return $ids;
}

/**
 * Combinações de 2–3 móveis que somam na largura (igual Next).
 *
 * @return array<int, array{items: WC_Product[], totalWidth: float, totalPrice: float, leftover: float}>
 */
function mareli_catalog_space_combinations(array $filters, int $limit = 12): array {
	$space_width = (float) $filters['spaceWidth'];
	if ($space_width <= 0) {
		return [];
	}

	$pool = [];
	foreach (mareli_catalog_candidate_products($filters, true) as $product) {
		$w = mareli_product_measure_cm($product, 'width');
		if ($w === null || $w > $space_width) {
			continue;
		}
		$pool[] = $product;
	}

	usort(
		$pool,
		static fn (WC_Product $a, WC_Product $b) => (mareli_product_measure_cm($a, 'width') ?? 0) <=> (mareli_product_measure_cm($b, 'width') ?? 0)
	);

	$combos = [];
	$n      = count($pool);
	for ($i = 0; $i < $n; $i++) {
		for ($j = $i + 1; $j < $n; $j++) {
			$a = $pool[ $i ];
			$b = $pool[ $j ];
			$wa = mareli_product_measure_cm($a, 'width') ?? 0;
			$wb = mareli_product_measure_cm($b, 'width') ?? 0;
			$total = $wa + $wb;
			if ($total > $space_width) {
				continue;
			}
			$combos[] = [
				'items'      => [$a, $b],
				'totalWidth' => $total,
				'totalPrice' => (float) $a->get_price() + (float) $b->get_price(),
				'leftover'   => round($space_width - $total, 1),
			];
		}
	}
	for ($i = 0; $i < $n; $i++) {
		for ($j = $i + 1; $j < $n; $j++) {
			for ($k = $j + 1; $k < $n; $k++) {
				$a = $pool[ $i ];
				$b = $pool[ $j ];
				$c = $pool[ $k ];
				$wa = mareli_product_measure_cm($a, 'width') ?? 0;
				$wb = mareli_product_measure_cm($b, 'width') ?? 0;
				$wc = mareli_product_measure_cm($c, 'width') ?? 0;
				$total = $wa + $wb + $wc;
				if ($total > $space_width) {
					continue;
				}
				$combos[] = [
					'items'      => [$a, $b, $c],
					'totalWidth' => $total,
					'totalPrice' => (float) $a->get_price() + (float) $b->get_price() + (float) $c->get_price(),
					'leftover'   => round($space_width - $total, 1),
				];
			}
		}
	}

	usort(
		$combos,
		static function (array $left, array $right): int {
			if ($left['leftover'] !== $right['leftover']) {
				return $left['leftover'] <=> $right['leftover'];
			}
			return $left['totalPrice'] <=> $right['totalPrice'];
		}
	);

	return array_slice($combos, 0, $limit);
}
