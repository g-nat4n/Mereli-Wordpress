<?php
declare(strict_types=1);
if (! defined('ABSPATH')) {
	exit;
}

$page_links = [
	['Início', home_url('/')],
	['Móveis', home_url('/produtos/')],
	['Contato', home_url('/contato/')],
	['Sacola', function_exists('wc_get_cart_url') ? wc_get_cart_url() : home_url('/carrinho/')],
];

$categories = [];
if (function_exists('mareli_catalog_facets')) {
	$categories = mareli_catalog_facets()['categories'];
}

$wa_url = function_exists('mareli_whatsapp_url') ? mareli_whatsapp_url() : 'https://wa.me/5541991710412';
$instagram = 'https://www.instagram.com/mareli_arte/';
?>
<footer class="site-footer">
	<div class="container-main footer-grid">
		<div class="footer-brand">
			<a class="site-brand site-brand--light" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Mareli Arte">
				<span class="site-brand__mark">MARELI</span>
				<span class="site-brand__sub">Arte</span>
			</a>
			<p class="phrase">O espaço que você habita, com intenção.</p>
			<span class="footer-rule" aria-hidden="true"></span>
		</div>

		<div>
			<p class="footer-heading">Páginas</p>
			<nav class="footer-nav" aria-label="<?php esc_attr_e('Páginas do rodapé', 'mareli'); ?>">
				<?php foreach ($page_links as [$label, $url]) : ?>
					<a href="<?php echo esc_url($url); ?>"><?php echo esc_html($label); ?></a>
				<?php endforeach; ?>
			</nav>
		</div>

		<div>
			<p class="footer-heading">Categorias</p>
			<nav class="footer-nav" aria-label="<?php esc_attr_e('Categorias do rodapé', 'mareli'); ?>">
				<?php if (! $categories) : ?>
					<span class="footer-muted">Em breve</span>
				<?php else : ?>
					<?php foreach ($categories as $cat) : ?>
						<a href="<?php echo esc_url(add_query_arg('category', $cat['slug'], home_url('/produtos/'))); ?>">
							<?php echo esc_html($cat['name']); ?>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</nav>
		</div>

		<div>
			<p class="footer-heading">Contato e redes</p>
			<p class="footer-service">Rua Riachuelo, 384 — Centro, Curitiba (PR). WhatsApp (41) 99171-0412.</p>
			<div class="footer-contact-list">
				<p>
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
					<span>Rua Riachuelo, 384 — Centro<br>Curitiba (PR)</span>
				</p>
				<a href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
					WhatsApp (41) 99171-0412
				</a>
				<a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
					@mareli_arte
				</a>
			</div>
			<div class="footer-social">
				<a href="<?php echo esc_url($instagram); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram Mareli Arte">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
				</a>
				<a href="<?php echo esc_url($wa_url); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp Mareli Arte">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
				</a>
			</div>
		</div>
	</div>

	<div class="container-main footer-bottom">
		<p>© <?php echo esc_html(gmdate('Y')); ?> MARELI ARTE</p>
		<p class="center footer-credit">
			<span>Site construído por</span>
			<a href="https://grupocostabr.com.br/" target="_blank" rel="noopener noreferrer">Grupo Costa Brasil</a>
			<a class="footer-credit__ig" href="https://www.instagram.com/grupocostabrasil/" target="_blank" rel="noopener noreferrer" aria-label="Instagram Grupo Costa Brasil">
				<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
			</a>
		</p>
		<p class="right"><a href="#topo">Voltar ao topo ↑</a></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
