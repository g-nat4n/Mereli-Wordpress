<?php
/**
 * Plugin Name: Mareli Core
 * Description: Campos de peça única, looks, vitrine, best-sellers, Atelier, contato e seed da loja Mareli Arte.
 * Version: 1.1.9
 * Author: Mareli Arte
 * Requires at least: 6.4
 * Requires PHP: 8.1
 * Text Domain: mareli-core
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('MARELI_CORE_VERSION', '1.1.9');
define('MARELI_CORE_FILE', __FILE__);
define('MARELI_CORE_DIR', plugin_dir_path(__FILE__));
define('MARELI_CORE_URL', plugin_dir_url(__FILE__));

require_once MARELI_CORE_DIR . 'includes/class-product-meta.php';
require_once MARELI_CORE_DIR . 'includes/class-taxonomy-look.php';
require_once MARELI_CORE_DIR . 'includes/class-unique-piece.php';
require_once MARELI_CORE_DIR . 'includes/class-admin-showcase.php';
require_once MARELI_CORE_DIR . 'includes/class-contact.php';
require_once MARELI_CORE_DIR . 'includes/class-seed.php';
require_once MARELI_CORE_DIR . 'includes/class-permalinks.php';
require_once MARELI_CORE_DIR . 'includes/helpers.php';
require_once MARELI_CORE_DIR . 'includes/atelier-helpers.php';
require_once MARELI_CORE_DIR . 'includes/class-atelier.php';
require_once MARELI_CORE_DIR . 'includes/class-hardening.php';

add_action('plugins_loaded', static function (): void {
	Mareli_Core_Taxonomy_Look::init();
	Mareli_Core_Product_Meta::init();
	Mareli_Core_Unique_Piece::init();
	Mareli_Core_Admin_Showcase::init();
	Mareli_Core_Contact::init();
	Mareli_Core_Seed::init();
	Mareli_Core_Permalinks::init();
	Mareli_Core_Atelier::init();
	Mareli_Core_Hardening::init();
});

register_activation_hook(__FILE__, static function (): void {
	Mareli_Core_Permalinks::rewrites();
	Mareli_Core_Atelier::rewrites();
	flush_rewrite_rules();
});
