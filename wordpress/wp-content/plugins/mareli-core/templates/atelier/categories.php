<?php
declare(strict_types=1);

if (! defined('ABSPATH')) {
	exit;
}

$default_cat = (int) get_option('default_product_cat');
$terms = get_terms([
	'taxonomy'   => 'product_cat',
	'hide_empty' => false,
	'orderby'    => 'name',
	'order'      => 'ASC',
]);
if (is_wp_error($terms)) {
	$terms = [];
}

$edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
$edit_term = $edit_id > 0 ? get_term($edit_id, 'product_cat') : null;
if (! $edit_term instanceof WP_Term) {
	$edit_term = null;
	$edit_id   = 0;
}

$notice = isset($_GET['notice']) ? sanitize_key((string) $_GET['notice']) : '';
?>
<p class="eyebrow">Organização</p>
<h1 class="display" style="margin-top:0.5rem;font-size:2.4rem;">Categorias</h1>
<p class="atelier-lead">Crie, edite ou exclua as categorias da loja. Elas aparecem no menu, no rodapé e nos filtros.</p>

<?php if ($notice === 'saved') : ?>
	<p class="atelier-notice atelier-notice--ok">Categoria salva.</p>
<?php elseif ($notice === 'deleted') : ?>
	<p class="atelier-notice atelier-notice--ok">Categoria excluída.</p>
<?php elseif ($notice === 'error') : ?>
	<p class="atelier-notice atelier-notice--err">Não foi possível concluir. Tente outro nome ou verifique se a categoria existe.</p>
<?php endif; ?>

<form class="atelier-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
	<?php mareli_atelier_nonce_field(); ?>
	<input type="hidden" name="action" value="mareli_atelier_save_category">
	<input type="hidden" name="term_id" value="<?php echo esc_attr((string) $edit_id); ?>">

	<p class="eyebrow" style="margin-bottom:0.75rem;">
		<?php echo $edit_term ? 'Editar categoria' : 'Nova categoria'; ?>
	</p>

	<div class="atelier-field">
		<label for="name">Nome</label>
		<input id="name" name="name" required value="<?php echo esc_attr($edit_term ? $edit_term->name : ''); ?>">
	</div>
	<div class="atelier-field">
		<label for="slug">Slug (URL)</label>
		<input id="slug" name="slug" placeholder="ex.: sofas" value="<?php echo esc_attr($edit_term ? $edit_term->slug : ''); ?>">
		<p class="atelier-hint">Deixe em branco para gerar automaticamente a partir do nome.</p>
	</div>
	<div class="atelier-field">
		<label for="description">Descrição</label>
		<textarea id="description" name="description"><?php echo esc_textarea($edit_term ? $edit_term->description : ''); ?></textarea>
	</div>

	<div class="atelier-card__actions" style="margin-top:0.5rem;">
		<button class="atelier-btn" type="submit">
			<?php echo $edit_term ? 'Salvar alterações' : 'Criar categoria'; ?>
		</button>
		<?php if ($edit_term) : ?>
			<a class="atelier-btn atelier-btn--ghost" href="<?php echo esc_url(Mareli_Core_Atelier::url('categorias')); ?>">Cancelar edição</a>
		<?php endif; ?>
	</div>
</form>

<div class="atelier-list" style="margin-top:2.5rem;">
	<?php foreach ($terms as $term) :
		if ((int) $term->term_id === $default_cat || $term->slug === 'uncategorized') {
			continue;
		}
		$is_editing = $edit_id === (int) $term->term_id;
		$shop_url   = add_query_arg('category', $term->slug, home_url('/produtos/'));
		?>
		<article class="atelier-list-item<?php echo $is_editing ? ' is-editing' : ''; ?>">
			<p class="eyebrow"><?php echo esc_html((string) $term->count); ?> <?php echo (int) $term->count === 1 ? 'peça' : 'peças'; ?></p>
			<h2><?php echo esc_html($term->name); ?></h2>
			<p class="atelier-muted" style="margin-top:0.35rem;">
				<code><?php echo esc_html($term->slug); ?></code>
				·
				<a href="<?php echo esc_url($shop_url); ?>" target="_blank" rel="noopener noreferrer">Ver na loja</a>
			</p>
			<?php if ($term->description) : ?>
				<p class="atelier-muted" style="margin-top:0.5rem;"><?php echo esc_html($term->description); ?></p>
			<?php endif; ?>

			<div class="atelier-card__actions" style="margin-top:0.9rem;">
				<a class="atelier-mini" href="<?php echo esc_url(add_query_arg('edit', (string) $term->term_id, Mareli_Core_Atelier::url('categorias'))); ?>">
					Editar
				</a>

				<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" data-confirm="<?php
					echo esc_attr(
						(int) $term->count > 0
							? 'Excluir esta categoria? Os ' . $term->count . ' produto(s) ficarão sem categoria (não categorizados).'
							: 'Excluir categoria?'
					);
				?>">
					<?php mareli_atelier_nonce_field(); ?>
					<input type="hidden" name="action" value="mareli_atelier_category_action">
					<input type="hidden" name="term_id" value="<?php echo esc_attr((string) $term->term_id); ?>">
					<input type="hidden" name="atelier_action" value="delete">
					<button class="atelier-mini atelier-mini--danger" type="submit">Excluir</button>
				</form>
			</div>
		</article>
	<?php endforeach; ?>

	<?php if (! $terms) : ?>
		<p class="atelier-muted">Nenhuma categoria ainda. Crie a primeira acima.</p>
	<?php endif; ?>
</div>
