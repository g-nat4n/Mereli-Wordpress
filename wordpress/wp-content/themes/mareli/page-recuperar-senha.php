<?php
/**
 * Template Name: Recuperar senha
 */
declare(strict_types=1);

get_header();
$login_url = home_url('/login/');
$sent      = isset($_GET['reset-link-sent']);
?>
<main class="site-main">
	<div class="Mareli-lost">
		<?php if ($sent) : ?>
			<p class="eyebrow">Recuperar senha</p>
			<h1 class="display Mareli-lost__title">E-mail enviado</h1>
			<p class="Mareli-lost__text">Enviamos o link se este e-mail estiver cadastrado. Ele vale por 30 minutos.</p>
			<a class="Mareli-lost__back" href="<?php echo esc_url($login_url); ?>">Voltar ao login</a>
		<?php else : ?>
			<p class="eyebrow">Recuperar senha</p>
			<h1 class="display Mareli-lost__title">Esqueceu a senha?</h1>
			<p class="Mareli-lost__text">Informe o e-mail da sua conta. Se ele existir, enviamos o link de redefinição.</p>

			<?php wc_print_notices(); ?>

			<form method="post" class="Mareli-lost__form woocommerce-ResetPassword lost_reset_password">
				<label class="Mareli-field">
					<span>E-mail</span>
					<input class="input-text" type="text" name="user_login" id="user_login" autocomplete="username" required>
				</label>
				<?php do_action('woocommerce_lostpassword_form'); ?>
				<?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
				<input type="hidden" name="wc_reset_password" value="true">
				<button type="submit" class="btn btn--wine" value="<?php esc_attr_e('Enviar link', 'mareli'); ?>">Enviar link</button>
			</form>
			<a class="Mareli-lost__back" href="<?php echo esc_url($login_url); ?>">Voltar ao login</a>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
