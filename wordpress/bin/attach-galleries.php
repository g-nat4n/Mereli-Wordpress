<?php
/**
 * Anexa galerias extras aos produtos demo (roda uma vez).
 * Uso: wp eval-file /scripts/attach-galleries.php --allow-root
 */

if (! defined('ABSPATH')) {
	exit(1);
}

$map = [
	'sapato-roma' => [
		'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=1200&q=80',
		'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=1200&q=80',
	],
	'vestido-aurora' => [
		'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=1200&q=80',
		'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1200&q=80',
	],
	'blazer-siena' => [
		'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=1200&q=80',
	],
	'vestido-crepe-ember' => [
		'https://images.unsplash.com/photo-1566174053879-31528523f8ae?auto=format&fit=crop&w=1200&q=80',
		'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1200&q=80',
	],
];

if (! function_exists('media_sideload_image')) {
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
}

foreach ($map as $slug => $urls) {
	$post = get_page_by_path($slug, OBJECT, 'product');
	if (! $post) {
		WP_CLI::warning("Produto não encontrado: {$slug}");
		continue;
	}
	$product = wc_get_product($post->ID);
	if (! $product) {
		continue;
	}
	if (count($product->get_gallery_image_ids()) > 0) {
		WP_CLI::log("Galeria já existe: {$slug}");
		continue;
	}
	// Garante imagem destacada.
	if (! $product->get_image_id() && ! empty($urls[0])) {
		$feat = media_sideload_image($urls[0], $post->ID, null, 'id');
		if (! is_wp_error($feat)) {
			set_post_thumbnail($post->ID, (int) $feat);
		}
	}
	$gallery_ids = [];
	foreach ($urls as $url) {
		$att = media_sideload_image($url, $post->ID, null, 'id');
		if (! is_wp_error($att) && $att) {
			$gallery_ids[] = (int) $att;
		}
	}
	if ($gallery_ids) {
		$product->set_gallery_image_ids($gallery_ids);
		$product->save();
		WP_CLI::success("{$slug}: " . count($gallery_ids) . ' fotos na galeria');
	}
}
