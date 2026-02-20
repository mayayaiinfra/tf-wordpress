<?php
/**
 * TF_Blocks - Gutenberg block registration
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Blocks
 */
class TF_Blocks {

	/**
	 * Register blocks.
	 */
	public function register_blocks() {
		register_block_type(
			TF_PLUGIN_DIR . 'blocks/node-status',
			array(
				'render_callback' => array( $this, 'render_node_status' ),
			)
		);

		register_block_type(
			TF_PLUGIN_DIR . 'blocks/fleet-overview',
			array(
				'render_callback' => array( $this, 'render_fleet_overview' ),
			)
		);

		register_block_type(
			TF_PLUGIN_DIR . 'blocks/alert-feed',
			array(
				'render_callback' => array( $this, 'render_alert_feed' ),
			)
		);
	}

	/**
	 * Render node status block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_node_status( $attributes ) {
		$node_id          = isset( $attributes['nodeId'] ) ? sanitize_text_field( $attributes['nodeId'] ) : '';
		$show_theta       = isset( $attributes['showTheta'] ) ? (bool) $attributes['showTheta'] : true;
		$refresh_interval = isset( $attributes['refreshInterval'] ) ? absint( $attributes['refreshInterval'] ) : 30;

		if ( empty( $node_id ) ) {
			return '<div class="tf-block-error">' . esc_html__( 'Please specify a node ID.', 'thunderfire' ) . '</div>';
		}

		$client = TF_API_Client::get_instance();
		$health = $client->node_health( $node_id );

		ob_start();
		include TF_PLUGIN_DIR . 'public/partials/tf-node-status-display.php';
		return ob_get_clean();
	}

	/**
	 * Render fleet overview block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_fleet_overview( $attributes ) {
		$client = TF_API_Client::get_instance();
		$nodes  = $client->list_nodes();

		ob_start();
		include TF_PLUGIN_DIR . 'public/partials/tf-fleet-overview-display.php';
		return ob_get_clean();
	}

	/**
	 * Render alert feed block.
	 *
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_alert_feed( $attributes ) {
		$limit = isset( $attributes['limit'] ) ? absint( $attributes['limit'] ) : 5;
		$alerts = get_option( 'tf_pending_alerts', array() );
		$alerts = array_slice( $alerts, 0, $limit );

		ob_start();
		?>
		<div class="tf-alert-feed">
			<h4><?php esc_html_e( 'Recent Alerts', 'thunderfire' ); ?></h4>
			<?php if ( empty( $alerts ) ) : ?>
				<p class="tf-no-alerts"><?php esc_html_e( 'No recent alerts.', 'thunderfire' ); ?></p>
			<?php else : ?>
				<ul class="tf-alert-list">
					<?php foreach ( $alerts as $alert ) : ?>
						<li class="tf-alert tf-alert-<?php echo esc_attr( $alert['severity'] ?? 'info' ); ?>">
							<span class="tf-alert-time"><?php echo esc_html( human_time_diff( $alert['time'] ?? time() ) ); ?></span>
							<span class="tf-alert-message"><?php echo esc_html( $alert['message'] ?? '' ); ?></span>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
