<?php
/**
 * Template Name: Diário
 */
declare(strict_types=1);
get_header();

$posts = get_posts([
	'post_type'      => 'post',
	'posts_per_page' => 9,
	'post_status'    => 'publish',
]);

$fallback = [
	[
		'category' => 'Curadoria',
		'title'    => 'Menos peças, mais histórias',
		'text'     => 'Um olhar sobre escolhas que resistem ao tempo e criam espaço para combinações mais pessoais.',
		'image'    => 'https://images.pexels.com/photos/5709661/pexels-photo-5709661.jpeg?auto=compress&cs=tinysrgb&w=800',
	],
	[
		'category' => 'Tendências',
		'title'    => 'O tempo das peças com presença',
		'text'     => 'Texturas, cortes e detalhes que transformam uma tendência passageira em escolha afetiva.',
		'image'    => 'https://images.pexels.com/photos/3755706/pexels-photo-3755706.jpeg?auto=compress&cs=tinysrgb&w=800',
	],
	[
		'category' => 'Comportamento',
		'title'    => 'Vestir a própria linguagem',
		'text'     => 'Uma reflexão sobre moda como linguagem cotidiana, com liberdade para mudar de ideia e de forma.',
		'image'    => 'https://images.pexels.com/photos/7679720/pexels-photo-7679720.jpeg?auto=compress&cs=tinysrgb&w=800',
	],
];
?>
<main class="site-main">
	<div class="page-hero">
		<div class="container-main">
			<p class="eyebrow">Inspiração</p>
			<h1 class="display">Diário Mareli</h1>
		</div>
	</div>
	<div class="container-main section">
		<div class="journal-grid">
			<?php if ($posts) : ?>
				<?php foreach ($posts as $post) : setup_postdata($post); ?>
					<a class="journal-card" href="<?php echo esc_url(get_permalink($post)); ?>" data-reveal>
						<article>
							<?php if (has_post_thumbnail($post)) : ?>
								<?php echo get_the_post_thumbnail($post, 'large'); ?>
							<?php endif; ?>
							<p class="eyebrow"><?php echo esc_html(get_the_category_list(', ', '', $post->ID) ?: 'Diário'); ?></p>
							<h3><?php echo esc_html(get_the_title($post)); ?></h3>
							<p><?php echo esc_html(wp_trim_words(get_the_excerpt($post), 22)); ?></p>
						</article>
					</a>
				<?php endforeach; wp_reset_postdata(); ?>
			<?php else : ?>
				<?php foreach ($fallback as $item) : ?>
					<div class="journal-card" data-reveal>
						<article>
							<img src="<?php echo esc_url($item['image']); ?>" alt="">
							<p class="eyebrow"><?php echo esc_html($item['category']); ?></p>
							<h3><?php echo esc_html($item['title']); ?></h3>
							<p><?php echo esc_html($item['text']); ?></p>
						</article>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</main>
<?php
get_footer();
