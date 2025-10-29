# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a WordPress/WooCommerce plugin that applies category-based price markups dynamically at runtime without modifying stored database prices. The plugin is designed for a specific e-commerce use case where products in certain categories (particularly "brabus" and its variants) need percentage-based price adjustments.

## Architecture

### Core Pricing Logic

The plugin uses WooCommerce filter hooks to intercept price calculations:
- Hooks into 6 WooCommerce price filters (regular/sale/variation prices)
- `aw_cm_apply_markup()` (category-markup.php:27-158) is the single function handling all price modifications
- Returns modified prices as strings without touching database values

### Markup Resolution Strategy

The markup system follows this priority order:
1. Dynamic term meta (`_aw_markup_percent`) overrides static defaults
2. Static defaults defined in `AW_CATEGORY_MARKUPS` constant
3. "brabus" markup cascades to child slugs like "brabus-mercedes" automatically (lines 78-86)
4. Multiple category markups are resolved using `AW_MARKUP_STRATEGY`:
   - `max` (default): highest markup wins
   - `first`: first matching category's markup applies
   - `sum`: add all matching category markups

### Configuration Constants

All behavior is controlled via constants at the top of category-markup.php:
- `AW_CATEGORY_MARKUPS`: Static category slug → percentage mappings
- `AW_MARKUP_STRATEGY`: How to handle products in multiple categories ('max'|'first'|'sum')
- `AW_APPLY_TO_SALE_PRICE`: Whether sale prices get markup applied (true by default)
- `AW_ROUND_PRICE`: Round final prices to WooCommerce decimals (true by default)
- `AW_FRONTEND_ONLY`: Restrict markup to frontend only (false = applies everywhere)

### WooCommerce Integration

- Term meta UI added via `product_cat_add_form_fields` and `product_cat_edit_form_fields` hooks
- Price cache hash modified via `woocommerce_get_price_hash` to ensure proper cache differentiation
- Filters applied at priority 25 to allow other plugins to run first

## Development Commands

### Testing Locally
This plugin must run within a WordPress installation with WooCommerce active. There are no standalone tests.

1. Copy `category-markup/` to `wp-content/plugins/`
2. Activate in WP Admin → Plugins
3. Test pricing changes in WooCommerce products assigned to configured categories

### Deployment
Use the included FTPS deployment script:

```bash
export FTP_PASS='your_password'
bash upload_plugin_lftp.sh
```

Configuration via environment variables:
- `FTP_HOST`: Server hostname/IP (default: 147.79.122.118)
- `FTP_USER`: FTP username (default: u659513315.thrwdist)
- `REMOTE_PLUGINS_PATH`: Path to plugins directory (default: wp-content/plugins)
- `FORCE_TLS`: Require FTPS (default: true)
- `SKIP_CERT_CHECK`: TLS verification (default: auto - disables for IP addresses)

### Version Updates
When incrementing version:
1. Update version number in plugin header comment (category-markup.php:5)
2. Deploy via upload script
3. WordPress may prompt for plugin update after deployment

## Key Implementation Details

### Price Context Handling
The filter callback determines context (regular/sale/price) from the current filter name to apply appropriate logic. The 'price' context (default) intelligently chooses between regular and sale price as the base, respecting `AW_APPLY_TO_SALE_PRICE`.

### Variation Product Support
- Parent product ID is used to lookup categories for variation products
- All 6 WooCommerce variation price filters are hooked
- Markup applies consistently across simple and variable products

### Safety Features
- Returns original price if: no categories, no matching markups, invalid prices, or calculation errors
- Uses 'edit' context (`get_regular_price('edit')`) to fetch raw database values and avoid recursion
- Filter allows customization: `aw_new_price_after_markup` and `aw_category_markups`

## File Structure

```
category-markup/
  category-markup.php          # Single-file plugin (197 lines)
upload_plugin_lftp.sh          # FTPS deployment script
README.md                      # User documentation
```

All plugin logic is contained in one PHP file. No external dependencies beyond WordPress/WooCommerce.
