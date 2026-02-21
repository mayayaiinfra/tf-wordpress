# Changelog

All notable changes to the THUNDERFIRE WordPress Plugin will be documented in this file.

## [1.0.0] - 2026-02-21

### Added
- **Dashboard Widget** - Fleet status in WP admin dashboard
- **Gutenberg Blocks** - Node Status, Fleet Overview, Alert Feed
- **Shortcodes** - `[thunderfire]`, `[thunderfire_fleet]`, `[thunderfire_node]`
- **Sidebar Widget** - Node health for theme sidebars
- **REST API Extension** - `/wp-json/thunderfire/v1/` endpoints
- **WP-Cron Integration** - Background health monitoring
- **Admin Bar Notifications** - Real-time alert badge
- **WooCommerce Integration** - Product-node binding
- **Admin Settings** - API URL, key, refresh interval
- **PHPUnit Tests** - 39 tests covering all classes

### Security
- `sanitize_text_field()` for all inputs
- `esc_html()`, `esc_url()` for outputs
- Capability checks (`manage_options`)
- Nonce verification for forms

### WordPress Compatibility
- WordPress 6.0+
- PHP 8.0+
- WooCommerce 8.0+ (optional)

[1.0.0]: https://github.com/mayayaiinfra/tf-wordpress/releases/tag/v1.0.0
