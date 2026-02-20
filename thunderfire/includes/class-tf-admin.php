<?php
/**
 * TF_Admin - Admin settings and pages
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Admin
 */
class TF_Admin {

	/**
	 * Add admin menu page.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'THUNDERFIRE Settings', 'thunderfire' ),
			__( 'THUNDERFIRE', 'thunderfire' ),
			'manage_options',
			'thunderfire',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register settings.
	 */
	public function register_settings() {
		register_setting( 'tf_settings', 'tf_api_endpoint', array( 'sanitize_callback' => 'esc_url_raw' ) );
		register_setting( 'tf_settings', 'tf_api_key', array( 'sanitize_callback' => array( $this, 'sanitize_api_key' ) ) );
		register_setting( 'tf_settings', 'tf_bound_nodes', array( 'sanitize_callback' => 'sanitize_textarea_field' ) );
		register_setting( 'tf_settings', 'tf_refresh_interval', array( 'sanitize_callback' => 'absint' ) );
		register_setting( 'tf_settings', 'tf_enable_notifications', array( 'sanitize_callback' => 'rest_sanitize_boolean' ) );
		register_setting( 'tf_settings', 'tf_notification_severity', array( 'sanitize_callback' => 'sanitize_text_field' ) );
		register_setting( 'tf_settings', 'tf_woocommerce_integration', array( 'sanitize_callback' => 'rest_sanitize_boolean' ) );

		add_settings_section(
			'tf_api_section',
			__( 'API Configuration', 'thunderfire' ),
			array( $this, 'render_api_section' ),
			'thunderfire'
		);

		add_settings_field( 'tf_api_endpoint', __( 'API Endpoint', 'thunderfire' ), array( $this, 'render_endpoint_field' ), 'thunderfire', 'tf_api_section' );
		add_settings_field( 'tf_api_key', __( 'API Key', 'thunderfire' ), array( $this, 'render_api_key_field' ), 'thunderfire', 'tf_api_section' );
		add_settings_field( 'tf_bound_nodes', __( 'Bound Nodes', 'thunderfire' ), array( $this, 'render_bound_nodes_field' ), 'thunderfire', 'tf_api_section' );

		add_settings_section(
			'tf_display_section',
			__( 'Display Settings', 'thunderfire' ),
			null,
			'thunderfire'
		);

		add_settings_field( 'tf_refresh_interval', __( 'Refresh Interval', 'thunderfire' ), array( $this, 'render_interval_field' ), 'thunderfire', 'tf_display_section' );
		add_settings_field( 'tf_enable_notifications', __( 'Enable Notifications', 'thunderfire' ), array( $this, 'render_notifications_field' ), 'thunderfire', 'tf_display_section' );
		add_settings_field( 'tf_notification_severity', __( 'Notification Severity', 'thunderfire' ), array( $this, 'render_severity_field' ), 'thunderfire', 'tf_display_section' );

		if ( class_exists( 'WooCommerce' ) ) {
			add_settings_section( 'tf_woo_section', __( 'WooCommerce', 'thunderfire' ), null, 'thunderfire' );
			add_settings_field( 'tf_woocommerce_integration', __( 'Enable Integration', 'thunderfire' ), array( $this, 'render_woo_field' ), 'thunderfire', 'tf_woo_section' );
		}
	}

	/**
	 * Sanitize API key.
	 *
	 * @param string $value API key value.
	 * @return string Sanitized value.
	 */
	public function sanitize_api_key( $value ) {
		$value = sanitize_text_field( $value );
		if ( ! empty( $value ) && ! preg_match( '/^tf_(live|test)_/', $value ) ) {
			add_settings_error( 'tf_api_key', 'invalid_key', __( 'API key must start with tf_live_ or tf_test_', 'thunderfire' ) );
			return get_option( 'tf_api_key' );
		}
		return $value;
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		include TF_PLUGIN_DIR . 'admin/partials/tf-settings-page.php';
	}

	/**
	 * Render API section description.
	 */
	public function render_api_section() {
		echo '<p>' . esc_html__( 'Configure your THUNDERFIRE TOP API connection.', 'thunderfire' ) . '</p>';
	}

	/**
	 * Render endpoint field.
	 */
	public function render_endpoint_field() {
		$value = get_option( 'tf_api_endpoint', 'http://localhost:8080' );
		printf( '<input type="url" name="tf_api_endpoint" value="%s" class="regular-text" />', esc_attr( $value ) );
		echo '<p class="description">' . esc_html__( 'URL of your TOP API server.', 'thunderfire' ) . '</p>';
	}

	/**
	 * Render API key field.
	 */
	public function render_api_key_field() {
		$value = get_option( 'tf_api_key', '' );
		printf( '<input type="password" name="tf_api_key" value="%s" class="regular-text" />', esc_attr( $value ) );
		echo '<p class="description">' . esc_html__( 'Your THUNDERFIRE API key (starts with tf_live_ or tf_test_).', 'thunderfire' ) . '</p>';
	}

	/**
	 * Render bound nodes field.
	 */
	public function render_bound_nodes_field() {
		$value = get_option( 'tf_bound_nodes', '' );
		printf( '<textarea name="tf_bound_nodes" rows="3" class="large-text">%s</textarea>', esc_textarea( $value ) );
		echo '<p class="description">' . esc_html__( 'Comma-separated list of node IDs to monitor.', 'thunderfire' ) . '</p>';
	}

	/**
	 * Render interval field.
	 */
	public function render_interval_field() {
		$value   = get_option( 'tf_refresh_interval', 5 );
		$options = array( 1, 5, 15, 30 );
		echo '<select name="tf_refresh_interval">';
		foreach ( $options as $opt ) {
			printf(
				'<option value="%d" %s>%d %s</option>',
				$opt,
				selected( $value, $opt, false ),
				$opt,
				esc_html__( 'minutes', 'thunderfire' )
			);
		}
		echo '</select>';
	}

	/**
	 * Render notifications field.
	 */
	public function render_notifications_field() {
		$value = get_option( 'tf_enable_notifications', true );
		printf( '<input type="checkbox" name="tf_enable_notifications" value="1" %s />', checked( $value, true, false ) );
		echo '<label>' . esc_html__( 'Show alerts in admin bar', 'thunderfire' ) . '</label>';
	}

	/**
	 * Render severity field.
	 */
	public function render_severity_field() {
		$value   = get_option( 'tf_notification_severity', 'warning' );
		$options = array(
			'all'      => __( 'All', 'thunderfire' ),
			'warning'  => __( 'Warning and above', 'thunderfire' ),
			'critical' => __( 'Critical only', 'thunderfire' ),
		);
		echo '<select name="tf_notification_severity">';
		foreach ( $options as $key => $label ) {
			printf( '<option value="%s" %s>%s</option>', esc_attr( $key ), selected( $value, $key, false ), esc_html( $label ) );
		}
		echo '</select>';
	}

	/**
	 * Render WooCommerce field.
	 */
	public function render_woo_field() {
		$value = get_option( 'tf_woocommerce_integration', false );
		printf( '<input type="checkbox" name="tf_woocommerce_integration" value="1" %s />', checked( $value, true, false ) );
		echo '<label>' . esc_html__( 'Enable WooCommerce node binding', 'thunderfire' ) . '</label>';
	}

	/**
	 * Enqueue admin styles.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_styles( $hook ) {
		if ( 'settings_page_thunderfire' !== $hook && 'index.php' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'tf-admin', TF_PLUGIN_URL . 'admin/css/tf-admin.css', array(), TF_VERSION );
	}

	/**
	 * Enqueue admin scripts.
	 *
	 * @param string $hook Current admin page.
	 */
	public function enqueue_scripts( $hook ) {
		if ( 'settings_page_thunderfire' !== $hook && 'index.php' !== $hook ) {
			return;
		}
		wp_enqueue_script( 'tf-admin', TF_PLUGIN_URL . 'admin/js/tf-admin.js', array( 'jquery' ), TF_VERSION, true );
		wp_localize_script(
			'tf-admin',
			'tfAdmin',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'testNonce'       => wp_create_nonce( 'tf_test_connection' ),
				'clearCacheNonce' => wp_create_nonce( 'tf_clear_cache' ),
				'refreshNonce'    => wp_create_nonce( 'tf_refresh_dashboard' ),
			)
		);
	}

	/**
	 * AJAX handler for test connection.
	 */
	public function ajax_test_connection() {
		check_ajax_referer( 'tf_test_connection' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'thunderfire' ) ) );
		}

		$client = TF_API_Client::get_instance();
		$result = $client->list_nodes();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Connection successful!', 'thunderfire' ),
				'node_count' => is_array( $result ) ? count( $result ) : 0,
			)
		);
	}

	/**
	 * AJAX handler for cache clear.
	 */
	public function ajax_clear_cache() {
		check_ajax_referer( 'tf_clear_cache' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'thunderfire' ) ) );
		}

		$client = TF_API_Client::get_instance();
		$client->clear_cache();

		wp_send_json_success( array( 'message' => __( 'Cache cleared.', 'thunderfire' ) ) );
	}
}
