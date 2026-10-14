<?php
/**
 * Dados da conta — área do cliente (padrão Mareli).
 *
 * @package Mareli
 * @var WP_User $user
 */

declare(strict_types=1);

defined('ABSPATH') || exit;
?>
<section class="Mareli-account Mareli-account--endpoint">
	<p class="Mareli-account__back">
		<a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">← <?php esc_html_e('Voltar à área do cliente', 'mareli'); ?></a>
	</p>
	<p class="eyebrow"><?php esc_html_e('Área do cliente', 'mareli'); ?></p>
	<h1 class="Mareli-account__hello"><?php esc_html_e('Dados da conta', 'mareli'); ?></h1>
	<p class="Mareli-account__hint"><?php esc_html_e('Atualize nome, e-mail e senha.', 'mareli'); ?></p>

	<form class="woocommerce-EditAccountForm edit-account Mareli-account__form" action="" method="post" <?php do_action('woocommerce_edit_account_form_tag'); ?>>
		<?php do_action('woocommerce_edit_account_form_start'); ?>

		<p class="woocommerce-form-row woocommerce-form-row--first form-row form-row-first">
			<label for="account_first_name"><?php esc_html_e('Nome', 'mareli'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr($user->first_name); ?>" required />
		</p>
		<p class="woocommerce-form-row woocommerce-form-row--last form-row form-row-last">
			<label for="account_last_name"><?php esc_html_e('Sobrenome', 'mareli'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr($user->last_name); ?>" required />
		</p>
		<div class="clear"></div>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="account_display_name"><?php esc_html_e('Nome de exibição', 'mareli'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" aria-describedby="account_display_name_description" value="<?php echo esc_attr($user->display_name); ?>" required />
			<span id="account_display_name_description"><em><?php esc_html_e('Assim seu nome aparece na conta e nos pedidos.', 'mareli'); ?></em></span>
		</p>

		<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
			<label for="account_email"><?php esc_html_e('E-mail', 'mareli'); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
			<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr($user->user_email); ?>" required />
		</p>

		<fieldset class="Mareli-account__fieldset">
			<legend><?php esc_html_e('Alterar senha', 'mareli'); ?></legend>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password_current"><?php esc_html_e('Senha atual (deixe em branco para não alterar)', 'mareli'); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password_1"><?php esc_html_e('Nova senha (deixe em branco para não alterar)', 'mareli'); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />
			</p>
			<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
				<label for="password_2"><?php esc_html_e('Confirmar nova senha', 'mareli'); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />
			</p>
		</fieldset>

		<?php do_action('woocommerce_edit_account_form'); ?>

		<p class="Mareli-account__form-actions">
			<?php wp_nonce_field('save_account_details', 'save-account-details-nonce'); ?>
			<button type="submit" class="woocommerce-Button button btn" name="save_account_details" value="<?php esc_attr_e('Salvar alterações', 'mareli'); ?>">
				<?php esc_html_e('Salvar alterações', 'mareli'); ?>
			</button>
			<input type="hidden" name="action" value="save_account_details" />
		</p>

		<?php do_action('woocommerce_edit_account_form_end'); ?>
	</form>
</section>
