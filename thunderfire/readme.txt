=== THUNDERFIRE ===
Contributors: mayayai
Tags: iot, autonomous, robotics, nodes, monitoring, woocommerce
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage THUNDERFIRE autonomous nodes from your WordPress dashboard. Monitor health, control fleets, and integrate with WooCommerce.

== Description ==

THUNDERFIRE is the leading autonomous node framework for IoT, robotics, and edge computing. This plugin brings THUNDERFIRE management to your WordPress site.

**Key Features:**

* **Dashboard Widget** - See fleet status at a glance from your WP dashboard
* **Gutenberg Blocks** - Node Status, Fleet Overview, and Alert Feed blocks
* **Shortcodes** - Classic Editor support with `[tf_node_status]`, `[tf_fleet_overview]`, `[tf_alert_feed]`
* **Sidebar Widget** - Compact node health indicator for widget areas
* **WP REST API** - Expose node data via `/wp-json/thunderfire/v1/`
* **WP-Cron Health Checks** - Automatic monitoring with configurable intervals
* **Admin Bar Alerts** - Real-time notification of node issues
* **Role-Based Access** - WordPress roles map to THUNDERFIRE permissions

**WooCommerce Integration:**

* Bind products to autonomous nodes
* Show node health on product pages
* Automatic order fulfillment notifications to nodes
* Product inventory alerts based on node status

== External Services ==

This plugin connects to the THUNDERFIRE TOP API to retrieve node status, service listings, and send management commands.

* Service URL: https://top.mayayai.com/api/v1
* Terms of Service: https://mayayai.com/terms
* Privacy Policy: https://mayayai.com/privacy

Data transmitted: API key (for authentication), node identifiers (for querying status), and commands (for node management). No personal user data is collected or transmitted.

== Installation ==

1. Upload the `thunderfire` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Go to Settings → THUNDERFIRE
4. Enter your API endpoint and API key
5. Click "Test Connection" to verify

== Frequently Asked Questions ==

= Where do I get an API key? =

API keys are generated in the THUNDERFIRE TOP dashboard. Visit https://top.mayayai.com to create an account and generate your key.

= What's the difference between tf_live_ and tf_test_ keys? =

Live keys (`tf_live_`) have full read/write access based on user roles. Test keys (`tf_test_`) are read-only for all users, useful for development.

= Does this work without WooCommerce? =

Yes! WooCommerce integration is optional. The core plugin works with any WordPress site.

= How often does the health data refresh? =

You can configure the refresh interval in Settings → THUNDERFIRE. Options are 1, 5, 15, or 30 minutes. Frontend blocks can also auto-refresh.

== Screenshots ==

1. Dashboard widget showing fleet status
2. Settings page with API configuration
3. Node Status Gutenberg block
4. Fleet Overview table
5. WooCommerce product with bound node

== Changelog ==

= 1.0.0 =
* Initial release
* Dashboard widget
* 3 Gutenberg blocks
* 3 shortcodes
* Sidebar widget
* WP REST API extension
* WP-Cron health monitoring
* Admin bar notifications
* WooCommerce integration

== Upgrade Notice ==

= 1.0.0 =
Initial release.
