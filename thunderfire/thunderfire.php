<?php
/**
 * THUNDERFIRE - Autonomous Node Management for WordPress
 *
 * @package           THUNDERFIRE
 * @author            MAYAYAI
 * @copyright         2025 MAYAYAI
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       THUNDERFIRE
 * Plugin URI:        https://mayayai.com/thunderfire/wordpress
 * Description:       Manage THUNDERFIRE autonomous nodes from your WordPress dashboard. Display node health, fleet status, and integrate with WooCommerce for IoT-enabled e-commerce.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            MAYAYAI
 * Author URI:        https://mayayai.com
 * Text Domain:       thunderfire
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

// Abort if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Plugin constants.
define( 'TF_VERSION', '1.0.0' );
define( 'TF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'TF_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check requirements on activation.
 */
function tf_activation_check() {
	if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
		deactivate_plugins( TF_PLUGIN_BASENAME );
		wp_die(
			esc_html__( 'THUNDERFIRE requires PHP 8.0 or higher.', 'thunderfire' ),
			esc_html__( 'Plugin Activation Error', 'thunderfire' ),
			array( 'back_link' => true )
		);
	}

	global $wp_version;
	if ( version_compare( $wp_version, '6.0', '<' ) ) {
		deactivate_plugins( TF_PLUGIN_BASENAME );
		wp_die(
			esc_html__( 'THUNDERFIRE requires WordPress 6.0 or higher.', 'thunderfire' ),
			esc_html__( 'Plugin Activation Error', 'thunderfire' ),
			array( 'back_link' => true )
		);
	}

	// Set default options.
	add_option( 'tf_api_endpoint', 'http://localhost:8080' );
	add_option( 'tf_api_key', '' );
	add_option( 'tf_bound_nodes', '' );
	add_option( 'tf_refresh_interval', 5 );
	add_option( 'tf_enable_notifications', true );
	add_option( 'tf_notification_severity', 'warning' );
	add_option( 'tf_woocommerce_integration', false );
	add_option( 'tf_pending_alerts', array() );

	// Schedule cron.
	if ( ! wp_next_scheduled( 'tf_health_check_event' ) ) {
		wp_schedule_event( time(), 'tf_interval', 'tf_health_check_event' );
	}
}
register_activation_hook( __FILE__, 'tf_activation_check' );

/**
 * Cleanup on deactivation.
 */
function tf_deactivation() {
	wp_clear_scheduled_hook( 'tf_health_check_event' );
}
register_deactivation_hook( __FILE__, 'tf_deactivation' );

/**
 * Add custom cron interval.
 *
 * @param array $schedules Cron schedules.
 * @return array Modified schedules.
 */
function tf_add_cron_interval( $schedules ) {
	$interval              = absint( get_option( 'tf_refresh_interval', 5 ) );
	$schedules['tf_interval'] = array(
		'interval' => $interval * 60,
		/* translators: %d: interval in minutes */
		'display'  => sprintf( esc_html__( 'Every %d minutes', 'thunderfire' ), $interval ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'tf_add_cron_interval' );

/**
 * Load plugin textdomain.
 */
function tf_load_textdomain() {
	load_plugin_textdomain( 'thunderfire', false, dirname( TF_PLUGIN_BASENAME ) . '/languages' );
}
add_action( 'plugins_loaded', 'tf_load_textdomain' );

// Include class files.
require_once TF_PLUGIN_DIR . 'includes/class-tf-loader.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-api-client.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-admin.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-dashboard-widget.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-blocks.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-shortcodes.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-widget.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-rest-api.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-cron.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-notifications.php';
require_once TF_PLUGIN_DIR . 'includes/class-tf-roles.php';

// Load WooCommerce integration only if WooCommerce is active.
if ( class_exists( 'WooCommerce' ) && get_option( 'tf_woocommerce_integration', false ) ) {
	require_once TF_PLUGIN_DIR . 'includes/class-tf-woocommerce.php';
}

/**
 * Initialize the plugin.
 */
function tf_init() {
	$loader = new TF_Loader();
	$loader->run();
}
add_action( 'plugins_loaded', 'tf_init', 20 );
