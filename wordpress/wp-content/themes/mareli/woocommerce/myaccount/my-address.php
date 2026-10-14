<?php
/**
 * Endereços — área do cliente (padrão Mareli).
 *
 * @package Mareli
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$types = [
	'billing'  => __('Endereço de cobrança', 'mareli'),
	'shipping' => __('Endereço de entrega', 'mareli'),
];
?>
<section class="Mareli-account Mareli-account--endpoint">
	<p class="Mareli-account__back">
		<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">← <?php esc_html_e('Voltar à área do cliente', 'mareli'); ?></a>
	</p>
	<p class="eyebrow"><?php esc_html_e('Área do cliente', 'mareli'); ?></p>
	<h1 class="Mareli-account__hello"><?php esc_html_e('Endereços', 'mareli'); ?></h1>
	<p class="Mareli-account__hint"><?php esc_html_e('Endereços usados na entrega e na cobrança.', 'mareli'); ?></p>

	<div class="Mareli-account__address-grid">
		<?php foreach ($types as $name => $title) :
			$address = wc_get_account_formatted_address($name);
			$edit    = wc_get_endpoint_url('edit-address', $name, wc_get_page_permalink('myaccount'));
			?>
			<article class="Mareli-account__address-card">
				<div class="Mareli-account__address-head">
					<h2><?php echo esc_html($title); ?></h2>
					<a class="Mareli-account__address-edit" href="<?php echo esc_url($edit); ?>">
						<?php echo $address ? esc_html__('Editar', 'mareli') : esc_html__('Adicionar', 'mareli'); ?>
					</a>
				</div>
				<?php if ($address) : ?>
					<address class="Mareli-account__address-body"><?php echo wp_kses_post($address); ?></address>
				<?php else : ?>
					<p class="Mareli-account__hint"><?php esc_html_e('Nenhum endereço cadastrado ainda.', 'mareli'); ?></p>
				<?php endif; ?>
			</article>
		<?php endforeach; ?>
	</div>
</section>
