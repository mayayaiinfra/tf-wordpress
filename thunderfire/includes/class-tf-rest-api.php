<?php
/**
 * TF_REST_API - WordPress REST API extension
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_REST_API
 */
class TF_REST_API {

	/**
	 * Namespace.
	 *
	 * @var string
	 */
	private $namespace = 'thunderfire/v1';

	/**
	 * Register REST routes.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/nodes',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_nodes' ),
				'permission_callback' => array( $this, 'read_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/nodes/(?P<id>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_node_health' ),
				'permission_callback' => array( $this, 'read_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/nodes/(?P<id>[a-zA-Z0-9_-]+)/command',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'send_command' ),
				'permission_callback' => array( $this, 'write_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/nodes/(?P<id>[a-zA-Z0-9_-]+)/goal',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'set_goal' ),
				'permission_callback' => array( $this, 'write_permission' ),
				'args'                => array(
					'id' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/services',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_services' ),
				'permission_callback' => array( $this, 'read_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/contracts',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_contracts' ),
				'permission_callback' => array( $this, 'read_permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/cache/clear',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'clear_cache' ),
				'permission_callback' => array( $this, 'admin_permission' ),
			)
		);
	}

	/**
	 * Read permission check.
	 *
	 * @return bool
	 */
	public function read_permission() {
		return is_user_logged_in();
	}

	/**
	 * Write permission check.
	 *
	 * @return bool
	 */
	public function write_permission() {
		return current_user_can( 'edit_posts' );
	}

	/**
	 * Admin permission check.
	 *
	 * @return bool
	 */
	public function admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get all nodes.
	 *
	 * @return WP_REST_Response
	 */
	public function get_nodes() {
		$client = TF_API_Client::get_instance();
		$result = $client->list_nodes();

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Get node health.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_node_health( $request ) {
		$node_id = $request->get_param( 'id' );
		$client  = TF_API_Client::get_instance();
		$result  = $client->node_health( $node_id );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Send command to node.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function send_command( $request ) {
		$node_id = $request->get_param( 'id' );
		$command = $request->get_json_params();
		$client  = TF_API_Client::get_instance();
		$result  = $client->send_command( $node_id, $command );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Set node goal.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function set_goal( $request ) {
		$node_id = $request->get_param( 'id' );
		$params  = $request->get_json_params();
		$goal    = sanitize_text_field( $params['goal'] ?? '' );

		if ( empty( $goal ) ) {
			return new WP_REST_Response( array( 'error' => 'Goal is required' ), 400 );
		}

		$client = TF_API_Client::get_instance();
		$result = $client->set_goal( $node_id, $goal );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Get services.
	 *
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function get_services( $request ) {
		$params = array();
		if ( $request->get_param( 'category' ) ) {
			$params['capability'] = sanitize_text_field( $request->get_param( 'category' ) );
		}
		if ( $request->get_param( 'min_tier' ) ) {
			$params['tier'] = absint( $request->get_param( 'min_tier' ) );
		}

		$client = TF_API_Client::get_instance();
		$result = $client->search_services( $params );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Get contracts.
	 *
	 * @return WP_REST_Response
	 */
	public function get_contracts() {
		$client = TF_API_Client::get_instance();
		$result = $client->list_contracts();

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response( array( 'error' => $result->get_error_message() ), 500 );
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Clear cache.
	 *
	 * @return WP_REST_Response
	 */
	public function clear_cache() {
		$client = TF_API_Client::get_instance();
		$client->clear_cache();

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}
}
