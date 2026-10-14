# Setup Mareli Arte WordPress via Docker + WP-CLI (Windows)
# Compra via WhatsApp — sem Mercado Pago / Correios
$ErrorActionPreference = "Continue"

$SiteUrl = if ($env:SITE_URL) { $env:SITE_URL } else { "http://localhost:8081" }
$AdminUser = if ($env:WP_ADMIN_USER) { $env:WP_ADMIN_USER } else { "admin" }
$AdminPass = if ($env:WP_ADMIN_PASSWORD) { $env:WP_ADMIN_PASSWORD } else { "MareliAdmin2026!" }
$AdminEmail = if ($env:WP_ADMIN_EMAIL) { $env:WP_ADMIN_EMAIL } else { "admin@mareliarte.com" }
$WhatsApp = if ($env:MARELI_WHATSAPP) { $env:MARELI_WHATSAPP } else { "5541991710412" }

function Invoke-Wp {
  param([Parameter(ValueFromRemainingArguments = $true)][string[]]$CliArgs)
  $prev = $ErrorActionPreference
  $ErrorActionPreference = "SilentlyContinue"
  & docker compose run --rm --entrypoint wp wpcli @CliArgs --allow-root
  $code = $LASTEXITCODE
  $ErrorActionPreference = $prev
  if ($code -ne 0) {
    throw "wp falhou: $($CliArgs -join ' ')"
  }
}

function Try-Wp {
  param([Parameter(ValueFromRemainingArguments = $true)][string[]]$CliArgs)
  $prev = $ErrorActionPreference
  $ErrorActionPreference = "SilentlyContinue"
  & docker compose run --rm --entrypoint wp wpcli @CliArgs --allow-root | Out-Null
  $ok = ($LASTEXITCODE -eq 0)
  $ErrorActionPreference = $prev
  return $ok
}

Write-Host "==> Aguardando WordPress..."
$ready = $false
for ($i = 0; $i -lt 60; $i++) {
  if (Try-Wp core version) {
    $ready = $true
    break
  }
  Start-Sleep -Seconds 3
}
if (-not $ready) { throw "WordPress nao respondeu a tempo." }

$installed = Try-Wp core is-installed
if (-not $installed) {
  Write-Host "==> Instalando WordPress..."
  Invoke-Wp core install --url="$SiteUrl" --title="Mareli Arte" --admin_user="$AdminUser" --admin_password="$AdminPass" --admin_email="$AdminEmail" --skip-email
}

Write-Host "==> Idioma pt_BR..."
Try-Wp language core install pt_BR --activate | Out-Null
Try-Wp site switch-language pt_BR | Out-Null

Write-Host "==> Permalinks..."
Invoke-Wp rewrite structure "/%postname%/" --hard
Invoke-Wp rewrite flush --hard

Write-Host "==> WooCommerce (catalogo; compra via WhatsApp)..."
Invoke-Wp plugin install woocommerce --version=9.8.5 --activate --force
Try-Wp language plugin install woocommerce pt_BR | Out-Null

Write-Host "==> Ativando Mareli..."
Invoke-Wp plugin activate mareli-core
Invoke-Wp theme activate mareli

Write-Host "==> Ajustes WooCommerce BR + WhatsApp..."
Invoke-Wp option update woocommerce_store_address "Rua Riachuelo, 384"
Invoke-Wp option update woocommerce_store_address_2 "Centro"
Invoke-Wp option update woocommerce_store_city "Curitiba"
Invoke-Wp option update woocommerce_default_country "BR:PR"
Invoke-Wp option update woocommerce_store_postcode "80020-000"
Invoke-Wp option update woocommerce_currency "BRL"
Invoke-Wp option update woocommerce_weight_unit "kg"
Invoke-Wp option update woocommerce_dimension_unit "cm"
Invoke-Wp option update mareli_whatsapp $WhatsApp
Try-Wp wc tool run install_pages --user=1 | Out-Null

function Set-PageSlug([string]$optionKey, [string]$slug, [string]$title) {
  $id = (& docker compose run --rm --entrypoint wp wpcli option get $optionKey --allow-root 2>$null)
  if ($LASTEXITCODE -eq 0 -and $id) {
    $id = $id.ToString().Trim()
    if ($id -ne "" -and $id -ne "0") {
      Invoke-Wp post update $id --post_name=$slug --post_title="$title"
    }
  }
}

Set-PageSlug "woocommerce_shop_page_id" "produtos" "Moveis"
Set-PageSlug "woocommerce_cart_page_id" "carrinho" "Sacola"
Set-PageSlug "woocommerce_checkout_page_id" "checkout" "Checkout"
Set-PageSlug "woocommerce_myaccount_page_id" "minha-conta" "Minha conta"

$cartId = (& docker compose run --rm --entrypoint wp wpcli option get woocommerce_cart_page_id --allow-root 2>$null)
if ($cartId) { Invoke-Wp post update $cartId.ToString().Trim() --post_content="[woocommerce_cart]" }
Invoke-Wp option update woocommerce_cart_redirect_after_add no
Invoke-Wp option update woocommerce_enable_ajax_add_to_cart yes
Invoke-Wp option update woocommerce_coming_soon no
Invoke-Wp option update woocommerce_store_pages_only no

function Ensure-Page([string]$slug, [string]$title, [string]$content = "") {
  $existing = (& docker compose run --rm --entrypoint wp wpcli post list --post_type=page --name=$slug --field=ID --allow-root 2>$null)
  if ($LASTEXITCODE -eq 0 -and $existing -and $existing.ToString().Trim() -ne "") {
    return
  }
  if ($content) {
    Invoke-Wp post create --post_type=page --post_title="$title" --post_name=$slug --post_status=publish --post_content="$content"
  } else {
    Invoke-Wp post create --post_type=page --post_title="$title" --post_name=$slug --post_status=publish
  }
}

Ensure-Page "sobre" "Sobre"
Ensure-Page "contato" "Contato"
Ensure-Page "diario" "Diario"
Ensure-Page "login" "Entrar"
Ensure-Page "cadastro" "Cadastro"
Ensure-Page "recuperar-senha" "Recuperar senha"

$front = (& docker compose run --rm --entrypoint wp wpcli post list --post_type=page --name=inicio --field=ID --allow-root 2>$null)
if (-not $front -or $front.ToString().Trim() -eq "") {
  $front = (& docker compose run --rm --entrypoint wp wpcli post create --post_type=page --post_title="Inicio" --post_name=inicio --post_status=publish --porcelain --allow-root)
}
$front = $front.ToString().Trim()
Invoke-Wp option update show_on_front page
Invoke-Wp option update page_on_front $front

Write-Host "==> Seed demo..."
if (-not (Try-Wp mareli seed)) {
  Write-Host "Aviso: seed falhou. Rode depois: docker compose run --rm --entrypoint wp wpcli mareli seed --allow-root"
}

Invoke-Wp rewrite flush --hard

Write-Host ""
Write-Host "============================================"
Write-Host " Mareli Arte WordPress pronto em $SiteUrl"
Write-Host " Admin: $SiteUrl/wp-admin"
Write-Host " Usuario: $AdminUser"
Write-Host " Senha:   $AdminPass"
Write-Host " WhatsApp: $WhatsApp"
Write-Host "============================================"
