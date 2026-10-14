<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

/**
 * @return array<string, string>
 */
function MARELI_CORE_conditions(): array {
	return [
		'NEW_WITH_TAG' => __('Nova com etiqueta', 'mareli-core'),
		'EXCELLENT'    => __('Excelente', 'mareli-core'),
		'VERY_GOOD'    => __('Muito boa', 'mareli-core'),
		'GOOD'         => __('Boa', 'mareli-core'),
		'VINTAGE'      => __('Retrô', 'mareli-core'),
	];
}

/**
 * @return array<string, string>
 */
function MARELI_CORE_image_kinds(): array {
	return [
		'MAIN'   => __('Principal', 'mareli-core'),
		'FRONT'  => __('Frente', 'mareli-core'),
		'BACK'   => __('Costas', 'mareli-core'),
		'LABEL'  => __('Etiqueta', 'mareli-core'),
		'DETAIL' => __('Detalhe', 'mareli-core'),
		'FLAW'   => __('Defeito', 'mareli-core'),
	];
}

function MARELI_CORE_render_product_meta_block(): void {
	global $product;
	if (! $product instanceof WC_Product) {
		return;
	}
	$id        = $product->get_id();
	$condition = (string) get_post_meta($id, '_mareli_condition', true);
	$brand     = (string) get_post_meta($id, '_mareli_brand', true);
	$size      = (string) get_post_meta($id, '_mareli_size', true);
	$color     = (string) get_post_meta($id, '_mareli_color', true);
	$material  = (string) get_post_meta($id, '_mareli_material', true);
	$unique    = get_post_meta($id, '_mareli_unique_piece', true);
	$measures  = get_post_meta($id, '_mareli_measurements', true);
	if (! is_array($measures)) {
		$measures = [];
	}

	$conditions = MARELI_CORE_conditions();
	?>
	<div class="Mareli-meta-block">
		<?php if ($brand) : ?>
			<dt><?php esc_html_e('Marca', 'mareli-core'); ?></dt>
			<dd><?php echo esc_html($brand); ?></dd>
		<?php endif; ?>
		<?php if ($condition && isset($conditions[$condition])) : ?>
			<dt><?php esc_html_e('Condição', 'mareli-core'); ?></dt>
			<dd><?php echo esc_html($conditions[$condition]); ?></dd>
		<?php endif; ?>
		<?php if ($size) : ?>
			<dt><?php esc_html_e('Tamanho', 'mareli-core'); ?></dt>
			<dd><?php echo esc_html($size); ?></dd>
		<?php endif; ?>
		<?php if ($color) : ?>
			<dt><?php esc_html_e('Cor', 'mareli-core'); ?></dt>
			<dd><?php echo esc_html($color); ?></dd>
		<?php endif; ?>
		<?php if ($material) : ?>
			<dt><?php esc_html_e('Material', 'mareli-core'); ?></dt>
			<dd><?php echo esc_html($material); ?></dd>
		<?php endif; ?>
		<?php if ($unique === 'yes' || $unique === '1' || $unique === '') : ?>
			<dt><?php esc_html_e('Disponibilidade', 'mareli-core'); ?></dt>
			<dd><?php esc_html_e('Peça única', 'mareli-core'); ?></dd>
		<?php endif; ?>
		<?php if ($measures) : ?>
			<dt><?php esc_html_e('Medidas', 'mareli-core'); ?></dt>
			<dd>
				<?php
				$parts = [];
				$map   = [
					'bust'     => 'Busto',
					'waist'    => 'Cintura',
					'hip'      => 'Quadril',
					'length'   => 'Comprimento',
					'shoulder' => 'Ombro',
				];
				foreach ($map as $key => $label) {
					if (! empty($measures[$key])) {
						$parts[] = $label . ': ' . $measures[$key];
					}
				}
				echo esc_html(implode(' · ', $parts));
				?>
			</dd>
		<?php endif; ?>
	</div>
	<?php
}
