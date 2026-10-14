<?php
/**
 * Editar endereço — área do cliente (padrão Mareli).
 *
 * @package Mareli
 * @var string $load_address billing|shipping
 * @var string $page_title
 * @var array  $address
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

$page_title = ('billing' === $load_address)
	? __('Endereço de cobrança', 'mareli')
	: __('Endereço de entrega', 'mareli');
?>
<section class="Mareli-account Mareli-account--endpoint">
	<p class="Mareli-account__back">
		<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">← <?php esc_html_e('Voltar à área do cliente', 'mareli'); ?></a>
	</p>
	<p class="eyebrow"><?php esc_html_e('Área do cliente', 'mareli'); ?></p>
	<h1 class="Mareli-account__hello"><?php echo esc_html($page_title); ?></h1>
	<p class="Mareli-account__hint"><?php esc_html_e('Confira os dados antes de salvar.', 'mareli'); ?></p>

	<form method="post" class="Mareli-account__form">
		<div class="woocommerce-address-fields">
			<?php do_action("woocommerce_before_edit_address_form_{$load_address}"); ?>

			<div class="woocommerce-address-fields__field-wrapper">
				<?php
				foreach ($address as $key => $field) {
					woocommerce_form_field($key, $field, wc_get_post_data_by_key($key, $field['value'] ?? ''));
				}
				?>
			</div>

			<?php do_action("woocommerce_after_edit_address_form_{$load_address}"); ?>

			<p class="Mareli-account__form-actions">
				<button type="submit" class="button btn" name="save_address" value="<?php esc_attr_e('Salvar endereço', 'mareli'); ?>">
					<?php esc_html_e('Salvar endereço', 'mareli'); ?>
				</button>
				<?php wp_nonce_field('woocommerce-edit_address', 'woocommerce-edit-address-nonce'); ?>
				<input type="hidden" name="action" value="edit_address" />
			</p>
		</div>
	</form>
</section>
