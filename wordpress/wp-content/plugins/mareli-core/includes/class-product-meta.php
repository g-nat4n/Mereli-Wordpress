<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Mareli_Core_Product_Meta {
	public static function init(): void {
		add_action('woocommerce_product_options_general_product_data', [self::class, 'render_fields']);
		add_action('woocommerce_process_product_meta', [self::class, 'save_fields']);
	}

	public static function render_fields(): void {
		echo '<div class="options_group">';
		woocommerce_wp_text_input([
			'id'    => '_mareli_brand',
			'label' => __('Marca', 'mareli-core'),
		]);
		woocommerce_wp_text_input([
			'id'    => '_mareli_size',
			'label' => __('Tamanho', 'mareli-core'),
		]);
		woocommerce_wp_text_input([
			'id'    => '_mareli_color',
			'label' => __('Cor', 'mareli-core'),
		]);
		woocommerce_wp_text_input([
			'id'    => '_mareli_material',
			'label' => __('Material', 'mareli-core'),
		]);
		woocommerce_wp_select([
			'id'      => '_mareli_condition',
			'label'   => __('Condição', 'mareli-core'),
			'options' => array_merge(['' => __('Selecionar', 'mareli-core')], MARELI_CORE_conditions()),
		]);
		woocommerce_wp_checkbox([
			'id'          => '_mareli_unique_piece',
			'label'       => __('Peça única', 'mareli-core'),
			'description' => __('Estoque padrão 1; ao vender, fica esgotada.', 'mareli-core'),
			'value'       => get_post_meta(get_the_ID(), '_mareli_unique_piece', true) ?: 'yes',
			'cbvalue'     => 'yes',
		]);
		woocommerce_wp_checkbox([
			'id'    => '_mareli_bestseller',
			'label' => __('Mais vendido', 'mareli-core'),
		]);
		woocommerce_wp_textarea_input([
			'id'    => '_mareli_story',
			'label' => __('História da peça', 'mareli-core'),
		]);

		$measures = get_post_meta(get_the_ID(), '_mareli_measurements', true);
		if (! is_array($measures)) {
			$measures = [];
		}
		foreach (['bust' => 'Busto', 'waist' => 'Cintura', 'hip' => 'Quadril', 'length' => 'Comprimento', 'shoulder' => 'Ombro'] as $key => $label) {
			woocommerce_wp_text_input([
				'id'                => '_mareli_measure_' . $key,
				'label'             => $label,
				'value'             => $measures[$key] ?? '',
				'custom_attributes' => ['data-measure' => $key],
			]);
		}
		echo '</div>';
	}

	public static function save_fields(int $product_id): void {
		$fields = ['_mareli_brand', '_mareli_size', '_mareli_color', '_mareli_material', '_mareli_condition', '_mareli_story'];
		foreach ($fields as $field) {
			if (isset($_POST[$field])) {
				update_post_meta($product_id, $field, sanitize_text_field(wp_unslash((string) $_POST[$field])));
			}
		}

		$unique = isset($_POST['_mareli_unique_piece']) ? 'yes' : 'no';
		update_post_meta($product_id, '_mareli_unique_piece', $unique);
		update_post_meta($product_id, '_mareli_bestseller', isset($_POST['_mareli_bestseller']) ? 'yes' : 'no');

		$measures = [];
		foreach (['bust', 'waist', 'hip', 'length', 'shoulder'] as $key) {
			$raw = isset($_POST['_mareli_measure_' . $key]) ? sanitize_text_field(wp_unslash((string) $_POST['_mareli_measure_' . $key])) : '';
			if ($raw !== '') {
				$measures[$key] = $raw;
			}
		}
		update_post_meta($product_id, '_mareli_measurements', $measures);

		if ($unique === 'yes') {
			update_post_meta($product_id, '_manage_stock', 'yes');
			$stock = get_post_meta($product_id, '_stock', true);
			if ($stock === '' || $stock === false) {
				update_post_meta($product_id, '_stock', '1');
			}
		}

		self::sync_bestseller_option($product_id);
	}

	private static function sync_bestseller_option(int $product_id): void {
		$ids = get_option('mareli_bestsellers', []);
		if (! is_array($ids)) {
			$ids = [];
		}
		$ids = array_map('absint', $ids);
		$flag = get_post_meta($product_id, '_mareli_bestseller', true) === 'yes';
		if ($flag && ! in_array($product_id, $ids, true)) {
			$ids[] = $product_id;
		}
		if (! $flag) {
			$ids = array_values(array_diff($ids, [$product_id]));
		}
		update_option('mareli_bestsellers', $ids);
	}
}
