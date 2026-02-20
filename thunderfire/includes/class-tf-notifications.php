<?php
/**
 * TF_Notifications - Admin bar and notices
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Notifications
 */
class TF_Notifications {

	/**
	 * Add admin bar alert.
	 *
	 * @param WP_Admin_Bar $admin_bar Admin bar instance.
	 */
	public function add_admin_bar_alert( $admin_bar ) {
		if ( ! get_option( 'tf_enable_notifications', true ) ) {
			return;
		}

		if ( ! current_user_can( 'read' ) ) {
			return;
		}

		$alerts = get_option( 'tf_pending_alerts', array() );
		$count  = count( $alerts );

		if ( 0 === $count ) {
			return;
		}

		$admin_bar->add_node(
			array(
				'id'    => 'tf-alerts',
				'title' => sprintf(
					'<span class="tf-alert-badge ab-icon dashicons-before dashicons-warning"></span><span class="tf-alert-count">%d</span>',
					$count
				),
				'href'  => admin_url( 'options-general.php?page=thunderfire' ),
				'meta'  => array(
					'class' => 'tf-admin-bar-alerts',
				),
			)
		);
	}

	/**
	 * Show admin notices.
	 */
	public function show_admin_notices() {
		$alerts = get_option( 'tf_pending_alerts', array() );

		$critical = array_filter( $alerts, fn( $a ) => 'critical' === ( $a['severity'] ?? '' ) );

		if ( empty( $critical ) ) {
			return;
		}

		$latest = end( $critical );
		?>
		<div class="notice notice-error is-dismissible tf-alert-notice" data-alert-time="<?php echo esc_attr( $latest['time'] ?? 0 ); ?>">
			<p>
				<strong><?php esc_html_e( 'THUNDERFIRE Alert:', 'thunderfire' ); ?></strong>
				<?php echo esc_html( $latest['message'] ?? '' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX handler for dismissing alerts.
	 */
	public function ajax_dismiss_alert() {
		check_ajax_referer( 'tf_dismiss_alert' );

		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error();
		}

		$time = isset( $_POST['alert_time'] ) ? absint( $_POST['alert_time'] ) : 0;

		$alerts = get_option( 'tf_pending_alerts', array() );
		$alerts = array_filter( $alerts, fn( $a ) => ( $a['time'] ?? 0 ) > $time );
		update_option( 'tf_pending_alerts', array_values( $alerts ) );

		wp_send_json_success();
	}
}
