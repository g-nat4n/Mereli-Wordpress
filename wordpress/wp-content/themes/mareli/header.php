<?php
declare(strict_types=1);
if (! defined('ABSPATH')) {
	exit;
}

$account_url = is_user_logged_in()
	? (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('myaccount') : home_url('/minha-conta/'))
	: home_url('/login/');
$cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carrinho/');
$shop_url = home_url('/produtos/');

$nav_categories = [];
if (function_exists('mareli_catalog_facets')) {
	$nav_categories = mareli_catalog_facets()['categories'];
} else {
	$terms = get_terms([
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'exclude'    => [ (int) get_option('default_product_cat') ],
	]);
	if (! is_wp_error($terms)) {
		foreach ($terms as $term) {
			$nav_categories[] = ['name' => $term->name, 'slug' => $term->slug];
		}
	}
}
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header" id="topo" data-site-header>
	<div class="container-main site-header__inner">
		<a class="site-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Mareli Arte">
			<span class="site-brand__mark">MARELI</span>
			<span class="site-brand__sub">Arte</span>
		</a>

		<nav class="nav-primary" aria-label="<?php esc_attr_e('Navegação principal', 'mareli'); ?>">
			<a class="nav-link" href="<?php echo esc_url(home_url('/')); ?>">Início</a>
			<a class="nav-link" href="<?php echo esc_url($shop_url); ?>">Móveis</a>

			<div class="nav-dropdown" data-nav-dropdown>
				<button type="button" class="nav-link nav-dropdown__btn" data-nav-dropdown-btn aria-expanded="false" aria-haspopup="menu">
					Categorias
					<svg class="nav-dropdown__chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
				</button>
				<div class="nav-dropdown__menu" data-nav-dropdown-menu role="menu" hidden>
					<?php if (! $nav_categories) : ?>
						<p class="nav-dropdown__empty">Nenhuma categoria ainda</p>
					<?php else : ?>
						<?php foreach ($nav_categories as $cat) : ?>
							<a role="menuitem" href="<?php echo esc_url(add_query_arg('category', $cat['slug'], $shop_url)); ?>">
								<?php echo esc_html($cat['name']); ?>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
					<a class="nav-dropdown__all" href="<?php echo esc_url($shop_url); ?>">Ver todos os móveis</a>
				</div>
			</div>

			<a class="nav-link" href="<?php echo esc_url(home_url('/contato/')); ?>">Contato</a>
		</nav>

		<div class="header-actions">
			<button type="button" class="icon-btn" data-search-toggle aria-label="<?php esc_attr_e('Abrir busca', 'mareli'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
			</button>
			<a class="icon-btn" href="<?php echo esc_url($account_url); ?>" aria-label="<?php esc_attr_e('Conta', 'mareli'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
			</a>
			<a class="icon-btn cart-link" href="<?php echo esc_url($cart_url); ?>" data-open-cart aria-label="<?php esc_attr_e('Abrir sacola', 'mareli'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
				<?php mareli_cart_count_badge(); ?>
			</a>
			<button type="button" class="icon-btn menu-toggle" data-menu-toggle aria-expanded="false" aria-label="<?php esc_attr_e('Abrir menu', 'mareli'); ?>">
				<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
			</button>
		</div>
	</div>

	<div class="header-search" data-header-search hidden>
		<form class="container-main" action="<?php echo esc_url($shop_url); ?>" method="get" role="search">
			<label>
				<span class="sr-only"><?php esc_html_e('Buscar móveis', 'mareli'); ?></span>
				<input type="search" name="q" placeholder="<?php esc_attr_e('Buscar móvel', 'mareli'); ?>" value="<?php echo esc_attr(isset($_GET['q']) ? sanitize_text_field(wp_unslash((string) $_GET['q'])) : get_search_query()); ?>">
			</label>
		</form>
	</div>

	<nav class="container-main nav-mobile" data-nav-mobile aria-label="<?php esc_attr_e('Menu mobile', 'mareli'); ?>">
		<a href="<?php echo esc_url(home_url('/')); ?>">Início</a>
		<a href="<?php echo esc_url($shop_url); ?>">Móveis</a>
		<details class="nav-mobile__cats">
			<summary>
				<span>Categorias</span>
				<svg class="nav-mobile__chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
			</summary>
			<?php foreach ($nav_categories as $cat) : ?>
				<a href="<?php echo esc_url(add_query_arg('category', $cat['slug'], $shop_url)); ?>"><?php echo esc_html($cat['name']); ?></a>
			<?php endforeach; ?>
			<a href="<?php echo esc_url($shop_url); ?>">Ver todos os móveis</a>
		</details>
		<a href="<?php echo esc_url(home_url('/contato/')); ?>">Contato</a>
		<a href="<?php echo esc_url($account_url); ?>"><?php esc_html_e('Minha conta', 'mareli'); ?></a>
	</nav>
</header>
