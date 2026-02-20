<?php
/**
 * TF_API_Client - TOP API REST Client
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_API_Client
 *
 * Singleton client for TOP Public API.
 */
class TF_API_Client {

	/**
	 * Singleton instance.
	 *
	 * @var TF_API_Client|null
	 */
	private static $instance = null;

	/**
	 * API endpoint URL.
	 *
	 * @var string
	 */
	private $endpoint;

	/**
	 * API key.
	 *
	 * @var string
	 */
	private $api_key;

	/**
	 * Cache TTL in seconds.
	 *
	 * @var int
	 */
	private $cache_ttl = 300; // 5 minutes.

	/**
	 * Get singleton instance.
	 *
	 * @return TF_API_Client
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->endpoint = rtrim( get_option( 'tf_api_endpoint', 'http://localhost:8080' ), '/' );
		$this->api_key  = get_option( 'tf_api_key', '' );
	}

	/**
	 * Make GET request to API.
	 *
	 * @param string $path   API path.
	 * @param array  $params Query parameters.
	 * @return array|WP_Error Response data or error.
	 */
	public function get( $path, $params = array() ) {
		// Check cache first.
		$cache_key = $this->get_cache_key( $path, $params );

		// Allow cache bypass in admin with nonce.
		$bypass_cache = isset( $_GET['tf_nocache'] ) && // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			current_user_can( 'manage_options' ) &&
			check_admin_referer( 'tf_nocache' );

		if ( ! $bypass_cache ) {
			$cached = get_transient( $cache_key );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		$url = $this->endpoint . '/' . ltrim( $path, '/' );
		if ( ! empty( $params ) ) {
			$url = add_query_arg( array_map( 'sanitize_text_field', $params ), $url );
		}

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'timeout' => 30,
			)
		);

		return $this->handle_response( $response, $cache_key );
	}

	/**
	 * Make POST request to API.
	 *
	 * @param string $path API path.
	 * @param array  $data Request body data.
	 * @return array|WP_Error Response data or error.
	 */
	public function post( $path, $data = array() ) {
		$url = $this->endpoint . '/' . ltrim( $path, '/' );

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $data ),
				'timeout' => 30,
			)
		);

		// POST requests don't use cache.
		return $this->handle_response( $response );
	}

	/**
	 * Handle API response.
	 *
	 * @param array|WP_Error $response  API response.
	 * @param string|null    $cache_key Cache key for GET requests.
	 * @return array|WP_Error Processed response or error.
	 */
	private function handle_response( $response, $cache_key = null ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( 401 === $code ) {
			return new WP_Error(
				'tf_auth_error',
				__( 'Invalid THUNDERFIRE API key.', 'thunderfire' )
			);
		}

		if ( 429 === $code ) {
			error_log( 'THUNDERFIRE: Rate limit exceeded' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return new WP_Error(
				'tf_rate_limit',
				__( 'Rate limit exceeded. Please try again later.', 'thunderfire' )
			);
		}

		if ( $code >= 500 ) {
			error_log( 'THUNDERFIRE: Server error ' . $code ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			// Try to use cached data.
			if ( $cache_key ) {
				$cached = get_transient( $cache_key . '_backup' );
				if ( false !== $cached ) {
					return $cached;
				}
			}
			return new WP_Error(
				'tf_server_error',
				__( 'THUNDERFIRE server error. Please try again later.', 'thunderfire' )
			);
		}

		$data = json_decode( $body, true );
		if ( null === $data ) {
			return new WP_Error(
				'tf_json_error',
				__( 'Invalid response from THUNDERFIRE API.', 'thunderfire' )
			);
		}

		// Check for RPC error.
		if ( isset( $data['error'] ) ) {
			return new WP_Error(
				'tf_api_error',
				isset( $data['error']['message'] ) ? sanitize_text_field( $data['error']['message'] ) : __( 'API error', 'thunderfire' )
			);
		}

		$result = isset( $data['result'] ) ? $data['result'] : $data;

		// Cache successful GET responses.
		if ( $cache_key ) {
			set_transient( $cache_key, $result, $this->cache_ttl );
			set_transient( $cache_key . '_backup', $result, DAY_IN_SECONDS );
		}

		return $result;
	}

	/**
	 * Generate cache key.
	 *
	 * @param string $path   API path.
	 * @param array  $params Parameters.
	 * @return string Cache key.
	 */
	private function get_cache_key( $path, $params ) {
		return 'tf_cache_' . md5( $path . wp_json_encode( $params ) );
	}

	/**
	 * Clear all THUNDERFIRE transients.
	 */
	public function clear_cache() {
		global $wpdb;
		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			"DELETE FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_tf_cache_%'
			OR option_name LIKE '_transient_timeout_tf_cache_%'"
		);
	}

	// =========================================================================
	// API Methods
	// =========================================================================

	/**
	 * List all nodes.
	 *
	 * @return array|WP_Error
	 */
	public function list_nodes() {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.node.list',
				'params' => new stdClass(),
			)
		);
	}

	/**
	 * Get node health.
	 *
	 * @param string $node_id Node ID.
	 * @return array|WP_Error
	 */
	public function node_health( $node_id ) {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.node.health',
				'params' => array( 'id' => sanitize_text_field( $node_id ) ),
			)
		);
	}

	/**
	 * Send command to node.
	 *
	 * @param string $node_id Node ID.
	 * @param array  $cmd     Command data.
	 * @return array|WP_Error
	 */
	public function send_command( $node_id, $cmd ) {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.node.command',
				'params' => array(
					'id'      => sanitize_text_field( $node_id ),
					'command' => $cmd,
				),
			)
		);
	}

	/**
	 * Set node goal.
	 *
	 * @param string $node_id Node ID.
	 * @param string $goal    Goal description.
	 * @return array|WP_Error
	 */
	public function set_goal( $node_id, $goal ) {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.theta.run',
				'params' => array(
					'id'     => sanitize_text_field( $node_id ),
					'params' => array( 'goal' => sanitize_text_field( $goal ) ),
				),
			)
		);
	}

	/**
	 * Search services.
	 *
	 * @param array $params Search parameters.
	 * @return array|WP_Error
	 */
	public function search_services( $params = array() ) {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.nop.services.search',
				'params' => array_map( 'sanitize_text_field', $params ),
			)
		);
	}

	/**
	 * List contracts.
	 *
	 * @return array|WP_Error
	 */
	public function list_contracts() {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.nop.contracts.list',
				'params' => new stdClass(),
			)
		);
	}

	/**
	 * Search TF Store.
	 *
	 * @param string $query Search query.
	 * @return array|WP_Error
	 */
	public function store_search( $query ) {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.marketplace.search',
				'params' => array( 'query' => sanitize_text_field( $query ) ),
			)
		);
	}

	/**
	 * Get messaging stats.
	 *
	 * @return array|WP_Error
	 */
	public function messaging_stats() {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.msg.stats',
				'params' => new stdClass(),
			)
		);
	}

	/**
	 * Get LLM status.
	 *
	 * @return array|WP_Error
	 */
	public function llm_status() {
		return $this->post(
			'api/v1/rpc',
			array(
				'method' => 'top.llm.status',
				'params' => new stdClass(),
			)
		);
	}
}
