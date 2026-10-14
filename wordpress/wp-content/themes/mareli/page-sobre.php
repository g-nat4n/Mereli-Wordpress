<?php
/**
 * Template Name: Sobre
 */
declare(strict_types=1);
get_header();
?>
<main class="site-main">
	<section class="page-hero">
		<div class="container-main" style="max-width:42rem">
			<p class="eyebrow">Manifesto</p>
			<h1 class="display" style="font-size:clamp(2rem,5vw,3rem);margin:.75rem 0 0">A Mareli é o lugar que você escolhe</h1>
			<p style="margin-top:1.25rem;color:var(--taupe);line-height:1.7">
				Não existe um único estilo de casa certo. Existe o desejo de viver em um ambiente que acolhe quem você é — com madeira, textura, proporção e luz.
			</p>
		</div>
	</section>

	<section class="container-main section" style="display:grid;gap:2rem;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))">
		<article>
			<p class="eyebrow">01</p>
			<h3 style="font-family:var(--font-serif);margin:.5rem 0">Espaço</h3>
			<p style="color:var(--taupe);font-size:.95rem;line-height:1.65">Móveis pensados para circular, conviver e descansar — sem ocupar o ambiente de forma gratuita.</p>
		</article>
		<article>
			<p class="eyebrow">02</p>
			<h3 style="font-family:var(--font-serif);margin:.5rem 0">Ofício</h3>
			<p style="color:var(--taupe);font-size:.95rem;line-height:1.65">Acabamento honesto, madeira e proporção — peças com presença para o espaço que você vive.</p>
		</article>
		<article>
			<p class="eyebrow">03</p>
			<h3 style="font-family:var(--font-serif);margin:.5rem 0">Curadoria</h3>
			<p style="color:var(--taupe);font-size:.95rem;line-height:1.65">Seleção de sofás, mesas, camas e peças de madeira — para ambientes que respiram calma.</p>
		</article>
	</section>

	<section class="container-main section" style="text-align:center;padding-bottom:4rem">
		<p style="color:var(--taupe)">Rua Riachuelo, 384 — Centro, Curitiba (PR)</p>
		<p style="margin-top:1rem">
			<a class="btn btn--wine" href="<?php echo esc_url(home_url('/produtos/')); ?>">Explorar móveis</a>
		</p>
	</section>
</main>
<?php
get_footer();
