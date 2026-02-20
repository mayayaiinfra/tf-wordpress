<?php
/**
 * Uninstall THUNDERFIRE
 *
 * @package THUNDERFIRE
 */

// Exit if not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete options.
$options = array(
	'tf_api_endpoint',
	'tf_api_key',
	'tf_bound_nodes',
	'tf_refresh_interval',
	'tf_enable_notifications',
	'tf_notification_severity',
	'tf_woocommerce_integration',
	'tf_pending_alerts',
	'tf_last_sync',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Delete transients.
global $wpdb;
$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	"DELETE FROM {$wpdb->options}
	WHERE option_name LIKE '_transient_tf_cache_%'
	OR option_name LIKE '_transient_timeout_tf_cache_%'"
);

// Clear scheduled events.
wp_clear_scheduled_hook( 'tf_health_check_event' );

// Delete WooCommerce product meta if WooCommerce exists.
if ( class_exists( 'WooCommerce' ) ) {
	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"DELETE FROM {$wpdb->postmeta}
		WHERE meta_key IN ('_tf_bound_node_id', '_tf_node_healthy', '_tf_health_warning')"
	);
}
