<?php
/**
 * Bloco mapa + contato (ContactBand do Next) — home e página Contato.
 *
 * @package Mareli
 */

if (! defined('ABSPATH')) {
	exit;
}

$wa = function_exists('mareli_whatsapp_url') ? mareli_whatsapp_url() : 'https://wa.me/5541991710412';
$map_query = rawurlencode('Rua Riachuelo, 384, Centro, Curitiba, Paraná');
$map_embed = 'https://maps.google.com/maps?q=' . $map_query . '&z=16&output=embed';
$heading_tag = (! empty($args['heading_tag']) && $args['heading_tag'] === 'h1') ? 'h1' : 'h2';
?>
<section class="section section--ivory contact-band-section" id="contato">
	<div class="container-main contact-band">
		<div class="contact-band__map">
			<iframe title="Mapa Mareli Arte" src="<?php echo esc_url($map_embed); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
		</div>
		<div class="contact-band__copy">
			<p class="eyebrow eyebrow--wine">Visite e fale conosco</p>
			<?php if ($heading_tag === 'h1') : ?>
				<h1 class="contact-band__title">Entre em contato</h1>
			<?php else : ?>
				<h2 class="contact-band__title">Entre em contato</h2>
			<?php endif; ?>
			<p class="contact-band__text">Tire dúvidas, peça orçamento ou agende uma conversa pelo WhatsApp.</p>

			<div class="contact-band__card">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
				<div>
					<p><strong>Rua Riachuelo, 384 — Centro</strong></p>
					<p>Curitiba (PR)</p>
					<p class="contact-band__wa-line">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
						<strong>WhatsApp (41) 99171-0412</strong>
					</p>
				</div>
			</div>

			<p class="contact-band__cta">
				<a class="btn contact-band__btn" href="<?php echo esc_url($wa); ?>" target="_blank" rel="noopener noreferrer">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
					Conversar no WhatsApp · (41) 99171-0412
				</a>
			</p>
		</div>
	</div>
</section>
