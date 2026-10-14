<?php
/**
 * Tema Mareli — bootstrap.
 *
 * @package Mareli
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

define('MARELI_THEME_VERSION', '1.8.4');
define('MARELI_THEME_DIR', get_template_directory());
define('MARELI_THEME_URI', get_template_directory_uri());

require_once MARELI_THEME_DIR . '/inc/setup.php';
require_once MARELI_THEME_DIR . '/inc/assets.php';
require_once MARELI_THEME_DIR . '/inc/branding.php';
require_once MARELI_THEME_DIR . '/inc/woocommerce.php';
require_once MARELI_THEME_DIR . '/inc/template-tags.php';
require_once MARELI_THEME_DIR . '/inc/cart.php';
require_once MARELI_THEME_DIR . '/inc/whatsapp.php';
require_once MARELI_THEME_DIR . '/inc/mail.php';
require_once MARELI_THEME_DIR . '/inc/catalog.php';
require_once MARELI_THEME_DIR . '/inc/auth-pages.php';
