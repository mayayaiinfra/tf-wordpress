# THUNDERFIRE WordPress Plugin

[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0)
[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B.svg)](https://wordpress.org)
[![PHPUnit Tests](https://img.shields.io/badge/tests-39%20passing-brightgreen.svg)](tests/)

WordPress + WooCommerce plugin for THUNDERFIRE autonomous node management.

## Features

- Dashboard widget showing fleet status
- 3 Gutenberg blocks (Node Status, Fleet Overview, Alert Feed)
- 3 shortcodes for Classic Editor
- Sidebar widget
- WP REST API extension
- WP-Cron health monitoring
- Admin bar notifications
- WooCommerce product-node binding

## Development Setup

```bash
# Install dependencies
composer install

# Run code standards check
composer lint

# Run tests
composer test
```

## Local Testing

1. Install WordPress test environment
2. Configure `wp-tests-config.php`
3. Run `phpunit`

## File Structure

```
thunderfire/
├── thunderfire.php          # Main plugin file
├── readme.txt               # WP directory readme
├── uninstall.php            # Cleanup on uninstall
├── includes/                # PHP classes
├── admin/                   # Admin assets
├── public/                  # Public assets
├── blocks/                  # Gutenberg blocks
└── languages/               # i18n
```

## WordPress.org Submission

1. Run WP Plugin Check tool
2. Fix any issues
3. Zip the `thunderfire/` directory
4. Submit at https://wordpress.org/plugins/developers/add/
5. After approval, upload via SVN

## License

GPL v2 or later (required by WordPress Plugin Directory)
