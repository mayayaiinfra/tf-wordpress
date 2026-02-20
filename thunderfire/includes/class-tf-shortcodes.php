<?php
/**
 * TF_Shortcodes - Classic Editor shortcodes
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Shortcodes
 */
class TF_Shortcodes {

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes() {
		add_shortcode( 'tf_node_status', array( $this, 'render_node_status' ) );
		add_shortcode( 'tf_fleet_overview', array( $this, 'render_fleet_overview' ) );
		add_shortcode( 'tf_alert_feed', array( $this, 'render_alert_feed' ) );
	}

	/**
	 * Render node status shortcode.
	 *
	 * [tf_node_status id="node-42" show_theta="true"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_node_status( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'         => '',
				'show_theta' => 'true',
			),
			$atts,
			'tf_node_status'
		);

		$node_id    = sanitize_text_field( $atts['id'] );
		$show_theta = 'true' === $atts['show_theta'];

		if ( empty( $node_id ) ) {
			return '<div class="tf-shortcode-error">' . esc_html__( 'Please specify a node ID.', 'thunderfire' ) . '</div>';
		}

		$client = TF_API_Client::get_instance();
		$health = $client->node_health( $node_id );

		ob_start();
		include TF_PLUGIN_DIR . 'public/partials/tf-node-status-display.php';
		return ob_get_clean();
	}

	/**
	 * Render fleet overview shortcode.
	 *
	 * [tf_fleet_overview]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_fleet_overview( $atts ) {
		$client = TF_API_Client::get_instance();
		$nodes  = $client->list_nodes();

		ob_start();
		include TF_PLUGIN_DIR . 'public/partials/tf-fleet-overview-display.php';
		return ob_get_clean();
	}

	/**
	 * Render alert feed shortcode.
	 *
	 * [tf_alert_feed limit="5"]
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_alert_feed( $atts ) {
		$atts = shortcode_atts(
			array(
				'limit' => 5,
			),
			$atts,
			'tf_alert_feed'
		);

		$limit  = absint( $atts['limit'] );
		$alerts = get_option( 'tf_pending_alerts', array() );
		$alerts = array_slice( $alerts, 0, $limit );

		ob_start();
		?>
		<div class="tf-alert-feed">
			<?php if ( empty( $alerts ) ) : ?>
				<p class="tf-no-alerts"><?php esc_html_e( 'No recent alerts.', 'thunderfire' ); ?></p>
			<?php else : ?>
				<ul class="tf-alert-list">
					<?php foreach ( $alerts as $alert ) : ?>
						<li class="tf-alert tf-alert-<?php echo esc_attr( $alert['severity'] ?? 'info' ); ?>">
							<?php echo esc_html( $alert['message'] ?? '' ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
