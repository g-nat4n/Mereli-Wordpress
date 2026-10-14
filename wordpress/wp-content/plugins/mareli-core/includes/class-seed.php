<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Mareli_Core_Seed {
	public static function init(): void {
		if (defined('WP_CLI') && WP_CLI) {
			WP_CLI::add_command('mareli seed', [self::class, 'cli_seed']);
		}
	}

	/**
	 * @param array<int, string> $args
	 * @param array<string, string> $assoc
	 */
	public static function cli_seed(array $args = [], array $assoc = []): void {
		if (! class_exists('WooCommerce')) {
			if (defined('WP_CLI') && WP_CLI) {
				WP_CLI::error('WooCommerce precisa estar ativo.');
			}
			return;
		}

		$categories = [
			['Sofás', 'sofas', 'Assento profundo, tecidos honestos e presença para a sala.'],
			['Mesas', 'mesas', 'Jantar, centro e trabalho — madeira e proporção.'],
			['Cadeiras e poltronas', 'cadeiras-e-poltronas', 'Conforto para ler, conversar e pausar.'],
			['Camas', 'camas', 'Descanso com estrutura sólida e cabeceiras acolhedoras.'],
			['Estantes e racks', 'estantes-e-racks', 'Organização vertical e apoio para TV e objetos.'],
			['Aparadores', 'aparadores', 'Credenzas e buffets para jantar e corredor.'],
		];

		$cat_ids = [];
		foreach ($categories as $i => $cat) {
			$existing = get_term_by('slug', $cat[1], 'product_cat');
			if ($existing) {
				$cat_ids[$cat[1]] = (int) $existing->term_id;
				continue;
			}
			$result = wp_insert_term($cat[0], 'product_cat', [
				'slug'        => $cat[1],
				'description' => $cat[2],
			]);
			if (! is_wp_error($result)) {
				$cat_ids[$cat[1]] = (int) $result['term_id'];
				update_term_meta($cat_ids[$cat[1]], 'order', $i + 1);
			}
		}

		$look = get_term_by('slug', 'sala-serena', 'look');
		if (! $look) {
			$look_result = wp_insert_term('Sala serena', 'look', [
				'slug'        => 'sala-serena',
				'description' => 'Sofá de linho, rack baixo e poltrona de leitura em tom musgo.',
			]);
			$look_id = is_wp_error($look_result) ? 0 : (int) $look_result['term_id'];
		} else {
			$look_id = (int) $look->term_id;
		}

		$products = [
			[
				'name' => 'Sofá Linho Areia', 'slug' => 'sofa-linho-areia', 'cat' => 'sofas',
				'price' => '4890.00', 'compare' => '5490.00', 'brand' => 'Mareli Arte', 'size' => 'Grande', 'color' => 'Areia',
				'condition' => 'EXCELLENT', 'material' => 'Linho', 'featured' => true, 'bestseller' => true,
				'desc' => 'Sofá de três lugares em linho lavado, assento profundo e estrutura em madeira de eucalipto.',
				'story' => '',
				'measures' => ['width' => '220 cm', 'height' => '85 cm', 'depth' => '95 cm'],
				'image' => 'https://images.pexels.com/photos/1571463/pexels-photo-1571463.jpeg?auto=compress&cs=tinysrgb&w=1200',
				'look' => true,
			],
			[
				'name' => 'Mesa Jantar Carvalho', 'slug' => 'mesa-jantar-carvalho', 'cat' => 'mesas',
				'price' => '3290.00', 'compare' => '3790.00', 'brand' => 'Carvalho Norte', 'size' => 'Grande', 'color' => 'Natural',
				'condition' => 'NEW_WITH_TAG', 'material' => 'Carvalho', 'featured' => true, 'bestseller' => true,
				'desc' => 'Mesa retangular em carvalho maciço, tampo com veio aparente e base em treliça.',
				'story' => '',
				'measures' => ['width' => '180 cm', 'height' => '76 cm', 'depth' => '90 cm'],
				'image' => 'https://images.pexels.com/photos/1080721/pexels-photo-1080721.jpeg?auto=compress&cs=tinysrgb&w=1200',
				'look' => true,
			],
			[
				'name' => 'Poltrona Ninho', 'slug' => 'poltrona-ninho', 'cat' => 'cadeiras-e-poltronas',
				'price' => '1890.00', 'compare' => '2190.00', 'brand' => 'Ninho Studio', 'size' => 'Médio', 'color' => 'Musgo',
				'condition' => 'EXCELLENT', 'material' => 'Bouclé', 'featured' => true, 'bestseller' => true,
				'desc' => 'Poltrona envelopante em bouclé, base giratória discreta e apoio cervical generoso.',
				'story' => '',
				'measures' => ['width' => '78 cm', 'height' => '92 cm', 'depth' => '82 cm'],
				'image' => 'https://images.pexels.com/photos/276583/pexels-photo-276583.jpeg?auto=compress&cs=tinysrgb&w=1200',
				'look' => true,
			],
			[
				'name' => 'Estante Vertical Alto', 'slug' => 'estante-vertical-alto', 'cat' => 'estantes-e-racks',
				'price' => '2450.00', 'compare' => '2790.00', 'brand' => 'Alto Linha', 'size' => 'Grande', 'color' => 'Freijó',
				'condition' => 'VERY_GOOD', 'material' => 'Freijó', 'featured' => true, 'bestseller' => true,
				'desc' => 'Estante alta com cinco prateleiras, estrutura metálica preta e nichos assimétricos.',
				'story' => '',
				'measures' => ['width' => '120 cm', 'height' => '210 cm', 'depth' => '35 cm'],
				'image' => 'https://images.pexels.com/photos/1090638/pexels-photo-1090638.jpeg?auto=compress&cs=tinysrgb&w=1200',
			],
			[
				'name' => 'Sofá Modular Canto', 'slug' => 'sofa-modular-canto', 'cat' => 'sofas',
				'price' => '2150.00', 'compare' => '2490.00', 'brand' => 'Mareli Arte', 'size' => 'Grande', 'color' => 'Grafite',
				'condition' => 'VERY_GOOD', 'material' => 'Chenille', 'featured' => true, 'bestseller' => true,
				'desc' => 'Módulo de canto em chenille grafite, combina com linha Linho Areia.',
				'story' => 'Peça de mostruário com uso leve no assento — ficha honesta no anúncio.',
				'measures' => ['width' => '110 cm', 'height' => '85 cm', 'depth' => '110 cm'],
				'image' => 'https://images.pexels.com/photos/1866149/pexels-photo-1866149.jpeg?auto=compress&cs=tinysrgb&w=1200',
			],
			[
				'name' => 'Mesa Centro Redonda', 'slug' => 'mesa-centro-redonda', 'cat' => 'mesas',
				'price' => '890.00', 'compare' => '990.00', 'brand' => 'Carvalho Norte', 'size' => 'Compacto', 'color' => 'Freijó',
				'condition' => 'NEW_WITH_TAG', 'material' => 'Freijó', 'featured' => false, 'bestseller' => true,
				'desc' => 'Tampo redondo em freijó e base única esculpida. Porte compacto para salas menores.',
				'story' => '',
				'measures' => ['width' => '80 cm', 'height' => '42 cm', 'depth' => '80 cm'],
				'image' => 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=1200',
			],
			[
				'name' => 'Cadeira Jantar Olmo', 'slug' => 'cadeira-jantar-olmo', 'cat' => 'cadeiras-e-poltronas',
				'price' => '720.00', 'compare' => '820.00', 'brand' => 'Olmo Co.', 'size' => 'Compacto', 'color' => 'Cinza pedra',
				'condition' => 'VERY_GOOD', 'material' => 'Olmo e tecido', 'featured' => false, 'bestseller' => true,
				'desc' => 'Cadeira estofada com encosto curvo e pernas em olmo. Conjunto vendido por unidade.',
				'story' => '',
				'measures' => ['width' => '48 cm', 'height' => '88 cm', 'depth' => '54 cm'],
				'image' => 'https://images.pexels.com/photos/1080721/pexels-photo-1080721.jpeg?auto=compress&cs=tinysrgb&w=1200',
				'look' => true,
			],
			[
				'name' => 'Aparador Salgueiro', 'slug' => 'aparador-salgueiro', 'cat' => 'aparadores',
				'price' => '1980.00', 'compare' => '2280.00', 'brand' => 'Mareli Arte', 'size' => 'Médio', 'color' => 'Salgueiro',
				'condition' => 'EXCELLENT', 'material' => 'MDP revestido', 'featured' => false, 'bestseller' => true,
				'desc' => 'Aparador com três portas e pés altos, ideal para jantar ou hall.',
				'story' => 'Entrou na Mareli com ferragens originais e tom salgueiro envelhecido à mão.',
				'measures' => ['width' => '140 cm', 'height' => '85 cm', 'depth' => '45 cm'],
				'image' => 'https://images.pexels.com/photos/1350789/pexels-photo-1350789.jpeg?auto=compress&cs=tinysrgb&w=1200',
			],
			[
				'name' => 'Cama Casal Nord', 'slug' => 'cama-casal-nord', 'cat' => 'camas',
				'price' => '2790.00', 'compare' => '3190.00', 'brand' => 'Nord Sleep', 'size' => 'Grande', 'color' => 'Branco gelo',
				'condition' => 'EXCELLENT', 'material' => 'Madeira', 'featured' => true, 'bestseller' => true,
				'desc' => 'Cama casal com cabeceira baixa estofada, estrutura em madeira e sommier incluso.',
				'story' => '',
				'measures' => ['width' => '158 cm', 'height' => '105 cm', 'depth' => '208 cm'],
				'image' => 'https://images.pexels.com/photos/813691/pexels-photo-813691.jpeg?auto=compress&cs=tinysrgb&w=1200',
			],
			[
				'name' => 'Rack TV Baixo', 'slug' => 'rack-tv-baixo', 'cat' => 'estantes-e-racks',
				'price' => '1590.00', 'compare' => '1890.00', 'brand' => 'Mareli Arte', 'size' => 'Médio', 'color' => 'Nogueira',
				'condition' => 'GOOD', 'material' => 'Nogueira', 'featured' => true, 'bestseller' => true,
				'desc' => 'Rack baixo com duas gavetas e passagem para cabos, ideal para salas compactas.',
				'story' => '',
				'measures' => ['width' => '160 cm', 'height' => '45 cm', 'depth' => '42 cm'],
				'image' => 'https://images.pexels.com/photos/1571453/pexels-photo-1571453.jpeg?auto=compress&cs=tinysrgb&w=1200',
			],
		];

		$force_images = isset($assoc['images']) || isset($assoc['force-images']);
		$bestseller_ids = [];
		foreach ($products as $data) {
			$existing = get_page_by_path($data['slug'], OBJECT, 'product');
			if ($existing) {
				$product_id = (int) $existing->ID;
				$product    = wc_get_product($product_id);
				if ($product) {
					$product->set_regular_price($data['compare']);
					$product->set_sale_price($data['price']);
					$product->set_price($data['price']);
					$product->set_featured((bool) $data['featured']);
					if (! empty($cat_ids[$data['cat']])) {
						$product->set_category_ids([$cat_ids[$data['cat']]]);
					}
					$product->save();
				}
			} else {
				$product = new WC_Product_Simple();
				$product->set_name($data['name']);
				$product->set_slug($data['slug']);
				$product->set_status('publish');
				$product->set_catalog_visibility('visible');
				$product->set_description($data['desc']);
				$product->set_short_description($data['desc']);
				$product->set_regular_price($data['compare']);
				$product->set_sale_price($data['price']);
				$product->set_price($data['price']);
				$product->set_manage_stock(true);
				$product->set_stock_quantity(1);
				$product->set_stock_status('instock');
				$product->set_sold_individually(true);
				$product->set_featured((bool) $data['featured']);
				if (! empty($cat_ids[$data['cat']])) {
					$product->set_category_ids([$cat_ids[$data['cat']]]);
				}
				$product_id = $product->save();
			}

			update_post_meta($product_id, '_mareli_brand', $data['brand']);
			update_post_meta($product_id, '_mareli_size', $data['size']);
			update_post_meta($product_id, '_mareli_color', $data['color']);
			update_post_meta($product_id, '_mareli_material', $data['material']);
			update_post_meta($product_id, '_mareli_condition', $data['condition']);
			update_post_meta($product_id, '_mareli_unique_piece', 'yes');
			update_post_meta($product_id, '_mareli_story', $data['story']);
			update_post_meta($product_id, '_mareli_measurements', $data['measures']);
			update_post_meta($product_id, '_mareli_bestseller', ! empty($data['bestseller']) ? 'yes' : 'no');
			update_post_meta($product_id, '_mareli_status', 'AVAILABLE');
			update_post_meta($product_id, '_mareli_image_url', $data['image']);

			if (! empty($data['look']) && $look_id) {
				wp_set_object_terms($product_id, [$look_id], 'look');
			}
			if (! empty($data['bestseller'])) {
				$bestseller_ids[] = $product_id;
			}

			$needs_image = $force_images || ! has_post_thumbnail($product_id);
			if ($needs_image && ! empty($data['image'])) {
				self::sideload_image($data['image'], $product_id);
			}

			// Galeria extra (miniaturas na PDP).
			if (! empty($data['gallery']) && is_array($data['gallery'])) {
				$product_obj = wc_get_product($product_id);
				$existing_gal = $product_obj ? $product_obj->get_gallery_image_ids() : [];
				if ($product_obj && count($existing_gal) < 1) {
					$gallery_ids = [];
					foreach ($data['gallery'] as $gal_url) {
						$att = self::sideload_image_id((string) $gal_url, $product_id);
						if ($att) {
							$gallery_ids[] = $att;
						}
					}
					if ($gallery_ids) {
						$product_obj->set_gallery_image_ids($gallery_ids);
						$product_obj->save();
					}
				}
			}
		}

		update_option('mareli_bestsellers', $bestseller_ids);

		// Posts do Diário
		$articles = [
			['Móveis com presença', 'Como escolher peças que acompanham a rotina da casa.', 'https://images.pexels.com/photos/1571460/pexels-photo-1571460.jpeg?auto=compress&cs=tinysrgb&w=800'],
			['Madeira, luz e proporção', 'Detalhes que fazem o ambiente respirar.', 'https://images.pexels.com/photos/1080721/pexels-photo-1080721.jpeg?auto=compress&cs=tinysrgb&w=800'],
			['Mobiliar a própria rotina', 'Casa como extensão do dia a dia.', 'https://images.pexels.com/photos/813691/pexels-photo-813691.jpeg?auto=compress&cs=tinysrgb&w=800'],
		];
		foreach ($articles as $article) {
			$found = get_posts([
				'post_type'      => 'post',
				'title'          => $article[0],
				'posts_per_page' => 1,
				'post_status'    => 'any',
			]);
			if ($found) {
				continue;
			}
			$post_id = wp_insert_post([
				'post_title'   => $article[0],
				'post_content' => $article[1],
				'post_status'  => 'publish',
				'post_type'    => 'post',
			]);
			if ($post_id && ! is_wp_error($post_id)) {
				self::sideload_image($article[2], (int) $post_id);
			}
		}

		// Página templates
		foreach (['sobre' => 'page-sobre.php', 'contato' => 'page-contato.php', 'diario' => 'page-diario.php'] as $slug => $template) {
			$page = get_page_by_path($slug);
			if ($page) {
				update_post_meta($page->ID, '_wp_page_template', $template);
			}
		}

		if (defined('WP_CLI') && WP_CLI) {
			WP_CLI::success('Seed Mareli concluído (' . count($products) . ' produtos).');
		}
	}

	private static function sideload_image(string $url, int $post_id): void {
		$att_id = self::sideload_image_id($url, $post_id);
		if ($att_id) {
			set_post_thumbnail($post_id, $att_id);
		}
	}

	private static function sideload_image_id(string $url, int $post_id): int {
		if (! function_exists('media_sideload_image')) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		$att_id = media_sideload_image($url, $post_id, null, 'id');
		return (! is_wp_error($att_id) && $att_id) ? (int) $att_id : 0;
	}
}
