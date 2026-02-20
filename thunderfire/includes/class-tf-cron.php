<?php
/**
 * TF_Cron - WP-Cron health checks
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Cron
 */
class TF_Cron {

	/**
	 * Run health check.
	 */
	public function run_health_check() {
		$client = TF_API_Client::get_instance();
		$nodes  = $client->list_nodes();

		if ( is_wp_error( $nodes ) ) {
			return;
		}

		$alerts   = get_option( 'tf_pending_alerts', array() );
		$severity = get_option( 'tf_notification_severity', 'warning' );

		foreach ( $nodes as $node ) {
			$health = $node['health'] ?? 100;

			if ( $health < 50 ) {
				$alert_severity = 'critical';
			} elseif ( $health < 80 ) {
				$alert_severity = 'warning';
			} else {
				continue;
			}

			// Check if we should show this severity.
			if ( 'critical' === $severity && 'warning' === $alert_severity ) {
				continue;
			}

			$alerts[] = array(
				'node_id'  => $node['id'] ?? 'unknown',
				'message'  => sprintf(
					/* translators: 1: Node ID, 2: Health percentage */
					__( 'Node %1$s health dropped to %2$d%%', 'thunderfire' ),
					$node['id'] ?? 'unknown',
					$health
				),
				'severity' => $alert_severity,
				'time'     => time(),
			);
		}

		// Keep only last 50 alerts.
		$alerts = array_slice( $alerts, -50 );
		update_option( 'tf_pending_alerts', $alerts );

		// WooCommerce integration: update product health meta.
		if ( class_exists( 'WooCommerce' ) && get_option( 'tf_woocommerce_integration', false ) ) {
			$this->update_woo_product_health( $nodes );
		}
	}

	/**
	 * Update WooCommerce product health meta.
	 *
	 * @param array $nodes Node list.
	 */
	private function update_woo_product_health( $nodes ) {
		$node_health = array();
		foreach ( $nodes as $node ) {
			$node_health[ $node['id'] ?? '' ] = $node['health'] ?? 100;
		}

		$products = wc_get_products(
			array(
				'limit'      => -1,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => '_tf_bound_node_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		foreach ( $products as $product ) {
			$bound_node = $product->get_meta( '_tf_bound_node_id' );
			if ( empty( $bound_node ) ) {
				continue;
			}

			$health  = $node_health[ $bound_node ] ?? null;
			$healthy = null === $health || $health >= 80;

			$product->update_meta_data( '_tf_node_healthy', $healthy );

			if ( ! $healthy ) {
				$product->update_meta_data(
					'_tf_health_warning',
					sprintf(
						/* translators: 1: Node ID, 2: Health percentage */
						__( 'Bound node %1$s health: %2$d%%', 'thunderfire' ),
						$bound_node,
						$health ?? 0
					)
				);
			} else {
				$product->delete_meta_data( '_tf_health_warning' );
			}

			$product->save();
		}
	}
}
