<?php
/**
 * Login / cadastro — visual LoginForm do Next.
 *
 * @package Mareli
 */

declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

do_action('woocommerce_before_customer_login_form');
?>
<div class="Mareli-auth" id="customer_login">
	<div class="Mareli-auth__bg" aria-hidden="true"></div>

	<div class="Mareli-auth__intro">
		<p class="eyebrow">Mareli Arte</p>
		<h1 class="Mareli-auth__headline">Bem-vinda de volta.</h1>
		<p class="Mareli-auth__lead">Entre para acompanhar pedidos, favoritos e finalizar compras com mais agilidade.</p>
		<span class="gold-line" aria-hidden="true"></span>
	</div>

	<div class="Mareli-auth__card">
		<p class="eyebrow mobile-only">Mareli Arte</p>
		<h2 class="Mareli-auth__title">Entrar</h2>
		<p class="Mareli-auth__sub">Use o e-mail e a senha da sua conta.</p>

		<form class="woocommerce-form woocommerce-form-login login Mareli-auth__form" method="post" action="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">
			<?php do_action('woocommerce_login_form_start'); ?>

			<label class="Mareli-field">
				<span>E-mail</span>
				<input type="email" class="woocommerce-Input input-text" name="username" id="username" autocomplete="username" required>
			</label>

			<label class="Mareli-field">
				<span>Senha</span>
				<input type="password" class="woocommerce-Input input-text" name="password" id="password" autocomplete="current-password" required>
			</label>

			<?php do_action('woocommerce_login_form'); ?>

			<label class="Mareli-auth__remember">
				<input type="checkbox" name="rememberme" value="forever">
				<span><?php esc_html_e('Lembrar-me', 'mareli'); ?></span>
			</label>

			<?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
			<button type="submit" class="btn btn--wine Mareli-auth__submit" name="login" value="<?php esc_attr_e('Entrar', 'mareli'); ?>">
				<?php esc_html_e('Entrar', 'mareli'); ?>
			</button>

			<?php do_action('woocommerce_login_form_end'); ?>
		</form>

		<div class="Mareli-auth__links">
			<p>
				<a href="<?php echo esc_url(wp_lostpassword_url()); ?>"><?php esc_html_e('Esqueci a senha', 'mareli'); ?></a>
			</p>
			<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
				<p>
					Ainda não tem conta?
					<a href="#Mareli-register"><?php esc_html_e('Criar cadastro', 'mareli'); ?></a>
				</p>
			<?php endif; ?>
			<p class="Mareli-auth__footnote">Equipe Mareli? Após entrar, você vai direto ao atelier.</p>
		</div>

		<?php if ('yes' === get_option('woocommerce_enable_myaccount_registration')) : ?>
			<div class="Mareli-auth__register" id="Mareli-register">
				<h3 class="Mareli-auth__title" style="font-size:1.75rem">Criar cadastro</h3>
				<form method="post" class="woocommerce-form woocommerce-form-register register Mareli-auth__form" <?php do_action('woocommerce_register_form_tag'); ?>>
					<?php do_action('woocommerce_register_form_start'); ?>
					<label class="Mareli-field">
						<span>E-mail</span>
						<input type="email" class="input-text" name="email" id="reg_email" autocomplete="email" required>
					</label>
					<?php if ('no' === get_option('woocommerce_registration_generate_password')) : ?>
						<label class="Mareli-field">
							<span>Senha</span>
							<input type="password" class="input-text" name="password" id="reg_password" autocomplete="new-password" required>
						</label>
					<?php endif; ?>
					<?php do_action('woocommerce_register_form'); ?>
					<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
					<button type="submit" class="btn Mareli-auth__submit" name="register" value="<?php esc_attr_e('Cadastrar', 'mareli'); ?>">
						<?php esc_html_e('Cadastrar', 'mareli'); ?>
					</button>
					<?php do_action('woocommerce_register_form_end'); ?>
				</form>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
do_action('woocommerce_after_customer_login_form');
