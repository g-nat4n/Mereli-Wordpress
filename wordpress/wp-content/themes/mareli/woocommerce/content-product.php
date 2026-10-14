<?php
/**
 * Override do card de produto na loja — alinhado ao ShopProductCard do Next.
 *
 * @package Mareli
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

global $product;

if (! $product instanceof WC_Product || ! $product->is_visible()) {
	return;
}
?>
<li <?php wc_product_class('', $product); ?>>
	<?php mareli_product_card($product); ?>
</li>
