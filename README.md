# Woo Category Markup Plugin

This repository contains a lightweight WooCommerce plugin that adjusts product prices on the fly according to configurable category-based markups. The plugin leaves catalog prices stored in the database untouched and applies the markup only when WooCommerce calculates runtime prices.

## Features
- Configure a default markup per category (15% for `brabus` out of the box).
- Allow individual category overrides via term meta (`_aw_markup_percent`).
- Supports variable products and all WooCommerce price contexts (regular, sale, variation).
- Cascades the base `brabus` markup to any nested slugs such as `brabus-mercedes`.
- Safe to remove at any time—no stored product prices are modified.

## Project Structure

```
category-markup/
  category-markup.php   # Main plugin file to drop into `wp-content/plugins`
upload_plugin_lftp.sh    # Helper script for pushing the plugin to a remote host via lftp
```

## Installation
1. Copy the `category-markup` directory into your WordPress installation under `wp-content/plugins/`.
2. Activate **Category Markup** from the WordPress admin Plugins screen.

## Configuration
- Edit the `AW_CATEGORY_MARKUPS` constant in `category-markup/category-markup.php` to add or change default markups.
- In the WooCommerce product category edit screen, set the **Markup Percent (%)** field to override the default for a specific category.
- Adjust strategy constants (`AW_MARKUP_STRATEGY`, `AW_APPLY_TO_SALE_PRICE`, etc.) if you need different behavior.

## Deployment Helper
Use `upload_plugin_lftp.sh` to sync the plugin to a remote host via FTPS.

### Setup
1. Create a `.env` file in the project root:
```bash
FTP_HOST=147.79.122.118
FTP_USER=u659513315.thrwdist
FTP_PASS=your_password
REMOTE_PLUGINS_PATH=wp-content/plugins
FORCE_TLS=true
```

2. The `.env` file is automatically gitignored for security.

### Deploy
```bash
bash upload_plugin_lftp.sh
```

The script automatically loads credentials from `.env` or falls back to environment variables.

## Development Notes
- The plugin relies on WooCommerce filter hooks to inject markups; ensure WooCommerce is active.
- Pricing caches are differentiated via `woocommerce_get_price_hash`, so marked-up values remain in sync with WooCommerce caching mechanisms.
