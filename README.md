# THUNDERFIRE WordPress Plugin

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
