# Mareli Arte — WordPress / WooCommerce

Loja Mareli Arte em WordPress + WooCommerce (tema e plugin customizados).
A compra é feita pelo **WhatsApp** — sem Mercado Pago e sem Correios.

## Stack

WordPress · WooCommerce · PHP 8.2 · MySQL 8 · Docker · WhatsApp

## Subir local

1. Copie [`.env.example`](.env.example) para `.env` e defina `MYSQL_PASSWORD` e `MYSQL_ROOT_PASSWORD`.
2. Suba os containers:

```powershell
docker compose up -d mysql wordpress
.\wordpress\bin\setup.ps1
```

3. Abra http://localhost:8081  
   - Admin WP: http://localhost:8081/wp-admin  
   - **Atelier** (painel da loja): http://localhost:8081/admin/

Credenciais padrão do setup: `admin` / `MareliAdmin2026!` (`admin@mareliarte.com`).

WhatsApp padrão: `(41) 99171-0412` (`MARELI_WHATSAPP=5541991710412`).

## Estrutura

```
docker-compose.yml          # mysql + wordpress + wpcli
wordpress/
  bin/                      # setup, seed
  docker/                   # init SQL (banco mareli_wp)
  wp-content/themes/mareli/ # tema da loja
  wp-content/plugins/
    mareli-core/            # peça única, Atelier, looks, contato, seed
```

## Observação

Porta local **8081** (MySQL host **3308**) para não conflitar com a Vesta em 8080/3307.
A compra no site abre o WhatsApp com a mensagem do móvel ou da sacola.
