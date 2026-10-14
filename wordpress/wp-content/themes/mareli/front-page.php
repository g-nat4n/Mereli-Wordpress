<?php
/**
 * Home — espelha a ordem do Next: Hero → Curadoria → Categorias → Manifesto → Editorial → Showcase → Contato.
 *
 * @package Mareli
 */

declare(strict_types=1);

get_header();

$hero_img = 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=1600';
$featured = function_exists('mareli_get_featured_products') ? mareli_get_featured_products(12) : [];
if (! $featured && function_exists('mareli_get_bestseller_products')) {
	$featured = mareli_get_bestseller_products(12);
}

$category_chips = [
	['Sofás', add_query_arg('category', 'sofas', home_url('/produtos/')), 'https://images.pexels.com/photos/1571463/pexels-photo-1571463.jpeg?auto=compress&cs=tinysrgb&w=800'],
	['Mesas', add_query_arg('category', 'mesas', home_url('/produtos/')), 'https://images.pexels.com/photos/1080721/pexels-photo-1080721.jpeg?auto=compress&cs=tinysrgb&w=800'],
	['Poltronas', add_query_arg('category', 'cadeiras-e-poltronas', home_url('/produtos/')), 'https://images.pexels.com/photos/276583/pexels-photo-276583.jpeg?auto=compress&cs=tinysrgb&w=800'],
	['Camas', add_query_arg('category', 'camas', home_url('/produtos/')), 'https://images.pexels.com/photos/813691/pexels-photo-813691.jpeg?auto=compress&cs=tinysrgb&w=800'],
];

$pillars = [
	['01', 'Espaço', 'Móveis pensados para circular, conviver e descansar — sem ocupar o ambiente de forma gratuita.'],
	['02', 'Ofício', 'Curadoria atenta a estrutura, acabamento, madeira e tudo o que faz uma peça durar além da tendência.'],
	['03', 'Moradia', 'Mobiliar também é cuidar do bem-estar diário — conforto, silêncio visual e sensação de chegar em casa.'],
];

$showcase = $featured ?: [];

$categories_for_filter = [];
$styles_for_filter = [];
foreach ($featured as $p) {
	if (! $p instanceof WC_Product) {
		continue;
	}
	$terms = get_the_terms($p->get_id(), 'product_cat');
	if (is_array($terms) && ! is_wp_error($terms)) {
		foreach ($terms as $term) {
			$categories_for_filter[ $term->name ] = $term->name;
		}
	}
	$mat = (string) get_post_meta($p->get_id(), '_mareli_material', true);
	if ($mat !== '') {
		$styles_for_filter[ $mat ] = $mat;
	}
}
?>
<main id="top">
	<section class="hero" id="inicio">
		<div class="hero__frame">
			<div class="hero__media" aria-hidden="true">
				<img src="<?php echo esc_url($hero_img); ?>" alt="">
			</div>
			<div class="hero__content" data-reveal>
				<p class="hero__kicker">Design para morar</p>
				<h1 class="hero__line">MÓVEIS COM</h1>
				<p class="hero__accent">presença</p>
				<p class="hero__desc">Seleção de sofás, mesas, camas e peças de madeira com acabamento honesto — para ambientes que respiram calma e personalidade.</p>
				<div class="hero__actions">
					<a class="btn btn--outline-light" href="<?php echo esc_url(home_url('/produtos/')); ?>">Explorar móveis</a>
					<a class="btn btn--ghost-light" href="#categorias">Ver categorias</a>
				</div>
			</div>
		</div>
	</section>

	<?php if ($featured) : ?>
	<section class="curadoria" id="curadoria" data-curadoria>
		<div class="container-main">
			<div class="curadoria__head" data-reveal>
				<div>
					<p class="eyebrow eyebrow--wine">Seleção da semana</p>
					<h2 class="curadoria__title">Móveis em destaque</h2>
					<p class="curadoria__note">Peças e valores apresentados apenas para demonstração.</p>
				</div>
				<p class="curadoria__favs" data-fav-count aria-live="polite">0 favoritos</p>
			</div>

			<div class="filters-collapse" data-filters-collapse data-reveal>
				<button type="button" class="filters-collapse__toggle" data-filters-collapse-toggle aria-expanded="false">
					<span>
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M4 5h16"/><path d="M7 12h10"/><path d="M10 19h4"/></svg>
						Filtros
					</span>
					<svg class="filters-collapse__chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg>
				</button>
				<div class="filters-collapse__body" data-filters-collapse-body hidden>
					<div class="curadoria__toolbar">
						<label class="curadoria__search">
							<span class="sr-only">Buscar nos móveis</span>
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
							<input type="search" data-curadoria-q placeholder="Buscar móvel ou ambiente">
						</label>
						<select data-curadoria-category aria-label="Filtrar por categoria">
							<option value="">Todas as categorias</option>
							<?php foreach ($categories_for_filter as $name) : ?>
								<option value="<?php echo esc_attr($name); ?>"><?php echo esc_html($name); ?></option>
							<?php endforeach; ?>
						</select>
						<select data-curadoria-style aria-label="Filtrar por estilo">
							<option value="">Todos os estilos</option>
							<?php foreach ($styles_for_filter as $name) : ?>
								<option value="<?php echo esc_attr($name); ?>"><?php echo esc_html($name); ?></option>
							<?php endforeach; ?>
						</select>
						<select data-curadoria-sort aria-label="Ordenar móveis">
							<option value="featured">Ordenar: destaque</option>
							<option value="low">Menor valor</option>
							<option value="high">Maior valor</option>
						</select>
					</div>

					<div class="curadoria__prices">
						<span>Faixa de preço:</span>
						<div class="curadoria__price-chips">
							<button type="button" class="is-active" data-curadoria-price="all">Todos</button>
							<button type="button" data-curadoria-price="low">Até R$ 2.000</button>
							<button type="button" data-curadoria-price="mid">R$ 2.001–4.000</button>
							<button type="button" data-curadoria-price="high">Acima de R$ 4.000</button>
						</div>
					</div>
				</div>
			</div>

			<div class="curadoria__empty" data-curadoria-empty hidden>
				<h3>Ainda não encontramos esse móvel</h3>
				<p>Tente outro termo ou remova alguns filtros para reencontrar a peça certa para o seu espaço.</p>
				<button type="button" class="btn" data-curadoria-clear>Limpar filtros</button>
			</div>

			<div class="curadoria__grid" data-curadoria-grid>
				<?php foreach ($featured as $product) : ?>
					<?php if ($product instanceof WC_Product) : ?>
						<?php mareli_curadoria_card($product); ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<section class="section section--white" id="categorias">
		<div class="container-main">
			<h2 class="section-title">CATEGORIAS</h2>
			<div class="category-chips">
				<?php foreach ($category_chips as [$label, $url, $img]) : ?>
					<a class="category-chip" href="<?php echo esc_url($url); ?>">
						<img src="<?php echo esc_url($img); ?>" alt="">
						<span><?php echo esc_html($label); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section section--ivory" id="morada">
		<div class="container-main">
			<div class="Mareli-manifesto-head" data-reveal>
				<p class="eyebrow" style="color:rgba(89,66,56,.45)">Manifesto</p>
				<h2 class="display" style="margin-top:1rem;font-size:clamp(2rem,4vw,3rem)">A Mareli é o lugar que você escolhe</h2>
				<p style="margin:1.5rem auto 0;max-width:36rem;color:var(--taupe);line-height:1.8">Não existe um único estilo de casa certo. Existe o desejo de viver em um ambiente que acolhe quem você é — com madeira, textura, proporção e luz.</p>
			</div>
			<div class="Mareli-manifesto-pillars">
				<?php foreach ($pillars as [$n, $title, $text]) : ?>
					<article class="manifesto-pillar" data-reveal>
						<span><?php echo esc_html($n); ?></span>
						<h3><?php echo esc_html($title); ?></h3>
						<p><?php echo esc_html($text); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="editorial-band">
		<div class="container-main editorial-band__grid editorial-band__grid--simple">
			<div data-reveal>
				<p class="editorial-band__kicker">Viver como gesto</p>
				<h2 class="editorial-band__title">Escolha habitar.</h2>
			</div>
			<div class="editorial-band__text" data-reveal>
				<p>O móvel pode ser abrigo, memória, encontro e pausa. Escolha aquilo que faz sua casa parecer sua — sem catálogo genérico, com espaço para a sua própria narrativa.</p>
			</div>
		</div>
	</section>

	<?php if ($showcase) : ?>
	<section class="showcase" id="ambientes">
		<div class="container-main">
			<div class="showcase__head" data-reveal>
				<p class="eyebrow" style="color:rgba(89,66,56,.45)">Ambientes</p>
				<h2 class="display" style="margin-top:.75rem;font-size:clamp(2rem,4vw,3rem)">Móveis em movimento</h2>
				<p>Uma seleção visual da casa — deslize com o olhar e escolha o que cabe no seu espaço.</p>
			</div>
		</div>
		<div class="showcase__marquee" data-showcase-marquee>
			<div class="showcase__track">
				<?php
				$loop = array_merge($showcase, $showcase);
				foreach ($loop as $product) :
					if (! $product instanceof WC_Product) {
						continue;
					}
					?>
					<a class="showcase__card" href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
						<?php echo $product->get_image('large'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<div class="showcase__caption">
							<p class="showcase__label">Mareli</p>
							<p class="showcase__name"><?php echo esc_html($product->get_name()); ?></p>
						</div>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
		<p class="showcase__cta">
			<a class="btn" href="<?php echo esc_url(home_url('/produtos/')); ?>">Ver todos os móveis</a>
		</p>
	</section>
	<?php endif; ?>

	<?php get_template_part('template-parts/contact-band', null, ['heading_tag' => 'h2']); ?>
</main>
<?php
get_footer();
