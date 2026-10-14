<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

final class Mareli_Core_Taxonomy_Look {
	public static function init(): void {
		add_action('init', [self::class, 'register']);
	}

	public static function register(): void {
		register_taxonomy(
			'look',
			['product'],
			[
				'labels' => [
					'name'          => __('Estilos', 'mareli-core'),
					'singular_name' => __('Estilo', 'mareli-core'),
					'search_items'  => __('Buscar looks', 'mareli-core'),
					'all_items'     => __('Todos os looks', 'mareli-core'),
					'edit_item'     => __('Editar look', 'mareli-core'),
					'update_item'   => __('Atualizar look', 'mareli-core'),
					'add_new_item'  => __('Adicionar look', 'mareli-core'),
					'new_item_name' => __('Novo look', 'mareli-core'),
					'menu_name'     => __('Estilos', 'mareli-core'),
				],
				'hierarchical'      => false,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'rewrite'           => ['slug' => 'look'],
				'show_in_rest'      => true,
			]
		);
	}
}
