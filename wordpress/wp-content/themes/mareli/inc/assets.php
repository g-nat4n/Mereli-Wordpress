<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

add_action('wp_enqueue_scripts', static function (): void {
	wp_enqueue_style(
		'Mareli-fonts',
		'https://fonts.googleapis.com/css2?family=Arimo:ital,wght@0,400;0,600;0,700;1,400&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,500&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'Mareli-main',
		MARELI_THEME_URI . '/assets/css/main.css',
		['Mareli-fonts'],
		MARELI_THEME_VERSION
	);

	wp_enqueue_script(
		'Mareli-main',
		MARELI_THEME_URI . '/assets/js/main.js',
		[],
		MARELI_THEME_VERSION,
		true
	);

	wp_localize_script('Mareli-main', 'MareliTheme', [
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'homeUrl' => home_url('/'),
	]);
});
