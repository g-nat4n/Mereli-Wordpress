<?php
/**
 * Template Name: Cadastro Mareli
 */
declare(strict_types=1);

if (is_user_logged_in()) {
	wp_safe_redirect(wc_get_page_permalink('myaccount'));
	exit;
}

get_header();
$login_url = home_url('/login/');
?>
<main class="site-main woo-main--auth">
	<div class="Mareli-auth">
		<div class="Mareli-auth__bg" aria-hidden="true"></div>
		<div class="Mareli-auth__intro">
			<p class="eyebrow">Mareli Arte</p>
			<h1 class="Mareli-auth__headline">Crie sua conta Mareli.</h1>
			<p class="Mareli-auth__lead">Guarde seu histórico de compras, acompanhe envios e finalize com mais facilidade.</p>
			<span class="gold-line" aria-hidden="true"></span>
		</div>
		<div class="Mareli-auth__card">
			<p class="eyebrow mobile-only">Mareli Arte</p>
			<h1 class="Mareli-auth__title">Criar conta</h1>
			<p class="Mareli-auth__sub">Preencha os dados para começar.</p>

			<?php wc_print_notices(); ?>

			<form method="post" class="Mareli-auth__form woocommerce-form woocommerce-form-register" action="">
				<label class="Mareli-field">
					<span>Nome</span>
					<input type="text" name="billing_first_name" id="reg_billing_first_name" autocomplete="name" required class="input-text">
				</label>
				<label class="Mareli-field">
					<span>E-mail</span>
					<input type="email" name="email" id="reg_email" autocomplete="email" required class="input-text">
				</label>
				<label class="Mareli-field">
					<span>Telefone</span>
					<input type="tel" name="billing_phone" id="reg_billing_phone" autocomplete="tel" class="input-text">
				</label>
				<label class="Mareli-field">
					<span>Senha</span>
					<input type="password" name="password" id="reg_password" autocomplete="new-password" required class="input-text">
				</label>
				<?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>
				<button type="submit" class="btn btn--wine Mareli-auth__submit" name="register" value="Cadastrar">Cadastrar</button>
			</form>

			<div class="Mareli-auth__links">
				<p>Já tem conta? <a href="<?php echo esc_url($login_url); ?>">Entrar</a></p>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
