#!/usr/bin/env bash
# Setup Mareli Arte WordPress — compra via WhatsApp (sem Mercado Pago / Correios)
set -euo pipefail

SITE_URL="${SITE_URL:-http://localhost:8081}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-MareliAdmin2026!}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@mareliarte.com}"
MARELI_WHATSAPP="${MARELI_WHATSAPP:-5541991710412}"

wp() {
  docker compose run --rm --entrypoint wp wpcli "$@" --allow-root
}

echo "==> Aguardando WordPress..."
for i in $(seq 1 60); do
  if wp core version >/dev/null 2>&1; then break; fi
  sleep 3
done

if ! wp core is-installed >/dev/null 2>&1; then
  echo "==> Instalando WordPress..."
  wp core install --url="$SITE_URL" --title="Mareli Arte" \
    --admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" --skip-email
fi

wp language core install pt_BR --activate || true
wp site switch-language pt_BR || true
wp rewrite structure "/%postname%/" --hard
wp rewrite flush --hard

echo "==> WooCommerce..."
wp plugin install woocommerce --version=9.8.5 --activate --force
wp language plugin install woocommerce pt_BR || true

echo "==> Ativando Mareli..."
wp plugin activate mareli-core
wp theme activate mareli

wp option update woocommerce_store_address "Rua Riachuelo, 384"
wp option update woocommerce_store_address_2 "Centro"
wp option update woocommerce_store_city "Curitiba"
wp option update woocommerce_default_country "BR:PR"
wp option update woocommerce_currency "BRL"
wp option update mareli_whatsapp "$MARELI_WHATSAPP"
wp wc tool run install_pages --user=1 || true

SHOP_ID=$(wp option get woocommerce_shop_page_id 2>/dev/null || true)
CART_ID=$(wp option get woocommerce_cart_page_id 2>/dev/null || true)
[ -n "$SHOP_ID" ] && wp post update "$SHOP_ID" --post_name=produtos --post_title="Moveis"
[ -n "$CART_ID" ] && wp post update "$CART_ID" --post_name=carrinho --post_title="Sacola" --post_content="[woocommerce_cart]"

wp post list --post_type=page --name=sobre --field=ID | grep -q . || \
  wp post create --post_type=page --post_title="Sobre" --post_name=sobre --post_status=publish
wp post list --post_type=page --name=contato --field=ID | grep -q . || \
  wp post create --post_type=page --post_title="Contato" --post_name=contato --post_status=publish
wp post list --post_type=page --name=diario --field=ID | grep -q . || \
  wp post create --post_type=page --post_title="Diario" --post_name=diario --post_status=publish
wp post list --post_type=page --name=login --field=ID | grep -q . || \
  wp post create --post_type=page --post_title="Entrar" --post_name=login --post_status=publish
wp post list --post_type=page --name=cadastro --field=ID | grep -q . || \
  wp post create --post_type=page --post_title="Cadastro" --post_name=cadastro --post_status=publish
wp post list --post_type=page --name=recuperar-senha --field=ID | grep -q . || \
  wp post create --post_type=page --post_title="Recuperar senha" --post_name=recuperar-senha --post_status=publish

FRONT=$(wp post list --post_type=page --name=inicio --field=ID 2>/dev/null || true)
if [ -z "$FRONT" ]; then
  FRONT=$(wp post create --post_type=page --post_title="Inicio" --post_name=inicio --post_status=publish --porcelain)
fi
wp option update show_on_front page
wp option update page_on_front "$FRONT"

echo "==> Seed..."
wp mareli seed || echo "Aviso: seed falhou."
wp rewrite flush --hard

echo "Mareli Arte pronto em $SITE_URL (WhatsApp $MARELI_WHATSAPP)"
