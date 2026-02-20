<?php
/**
 * TF_Dashboard_Widget - WordPress dashboard widget
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Dashboard_Widget
 */
class TF_Dashboard_Widget {

	/**
	 * Add dashboard widget.
	 */
	public function add_dashboard_widget() {
		wp_add_dashboard_widget(
			'tf_dashboard_widget',
			__( 'THUNDERFIRE Fleet Status', 'thunderfire' ),
			array( $this, 'render_widget' ),
			null,
			null,
			'normal',
			'high'
		);
	}

	/**
	 * Render the dashboard widget.
	 */
	public function render_widget() {
		$api_key = get_option( 'tf_api_key', '' );

		if ( empty( $api_key ) ) {
			printf(
				'<p>%s <a href="%s">%s</a></p>',
				esc_html__( 'Configure your API key to get started.', 'thunderfire' ),
				esc_url( admin_url( 'options-general.php?page=thunderfire' ) ),
				esc_html__( 'Settings', 'thunderfire' )
			);
			return;
		}

		$client = TF_API_Client::get_instance();
		$nodes  = $client->list_nodes();

		include TF_PLUGIN_DIR . 'admin/partials/tf-dashboard-widget-display.php';
	}

	/**
	 * AJAX handler for widget refresh.
	 */
	public function ajax_refresh() {
		check_ajax_referer( 'tf_refresh_dashboard' );

		if ( ! current_user_can( 'read' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'thunderfire' ) ) );
		}

		$client = TF_API_Client::get_instance();
		$client->clear_cache();
		$nodes = $client->list_nodes();

		ob_start();
		include TF_PLUGIN_DIR . 'admin/partials/tf-dashboard-widget-display.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
