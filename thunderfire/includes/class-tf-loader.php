<?php
/**
 * TF_Loader - Central hook registration
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Loader
 *
 * Registers all WordPress hooks for the plugin.
 */
class TF_Loader {

	/**
	 * Actions to register.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Filters to register.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->load_dependencies();
	}

	/**
	 * Load and instantiate all dependencies.
	 */
	private function load_dependencies() {
		// Admin.
		$admin = new TF_Admin();
		$this->add_action( 'admin_menu', $admin, 'add_admin_menu' );
		$this->add_action( 'admin_init', $admin, 'register_settings' );
		$this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->add_action( 'wp_ajax_tf_test_connection', $admin, 'ajax_test_connection' );
		$this->add_action( 'wp_ajax_tf_clear_cache', $admin, 'ajax_clear_cache' );

		// Dashboard widget.
		$dashboard = new TF_Dashboard_Widget();
		$this->add_action( 'wp_dashboard_setup', $dashboard, 'add_dashboard_widget' );
		$this->add_action( 'wp_ajax_tf_refresh_dashboard', $dashboard, 'ajax_refresh' );

		// Gutenberg blocks.
		$blocks = new TF_Blocks();
		$this->add_action( 'init', $blocks, 'register_blocks' );

		// Shortcodes.
		$shortcodes = new TF_Shortcodes();
		$this->add_action( 'init', $shortcodes, 'register_shortcodes' );

		// Sidebar widget.
		$widget = new TF_Widget();
		$this->add_action( 'widgets_init', $widget, 'register_widget' );

		// REST API.
		$rest = new TF_REST_API();
		$this->add_action( 'rest_api_init', $rest, 'register_routes' );

		// Cron.
		$cron = new TF_Cron();
		$this->add_action( 'tf_health_check_event', $cron, 'run_health_check' );

		// Notifications.
		$notifications = new TF_Notifications();
		$this->add_action( 'admin_bar_menu', $notifications, 'add_admin_bar_alert', 100 );
		$this->add_action( 'admin_notices', $notifications, 'show_admin_notices' );
		$this->add_action( 'wp_ajax_tf_dismiss_alert', $notifications, 'ajax_dismiss_alert' );

		// Public assets.
		$this->add_action( 'wp_enqueue_scripts', $this, 'enqueue_public_assets' );
	}

	/**
	 * Add an action hook.
	 *
	 * @param string $hook     Hook name.
	 * @param object $component Component instance.
	 * @param string $callback Callback method name.
	 * @param int    $priority Priority.
	 * @param int    $args     Number of arguments.
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $args = 1 ) {
		$this->actions[] = array(
			'hook'      => $hook,
			'component' => $component,
			'callback'  => $callback,
			'priority'  => $priority,
			'args'      => $args,
		);
	}

	/**
	 * Add a filter hook.
	 *
	 * @param string $hook     Hook name.
	 * @param object $component Component instance.
	 * @param string $callback Callback method name.
	 * @param int    $priority Priority.
	 * @param int    $args     Number of arguments.
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $args = 1 ) {
		$this->filters[] = array(
			'hook'      => $hook,
			'component' => $component,
			'callback'  => $callback,
			'priority'  => $priority,
			'args'      => $args,
		);
	}

	/**
	 * Register all hooks with WordPress.
	 */
	public function run() {
		foreach ( $this->actions as $hook ) {
			add_action(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['args']
			);
		}

		foreach ( $this->filters as $hook ) {
			add_filter(
				$hook['hook'],
				array( $hook['component'], $hook['callback'] ),
				$hook['priority'],
				$hook['args']
			);
		}
	}

	/**
	 * Enqueue public-facing assets.
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style(
			'tf-public',
			TF_PLUGIN_URL . 'public/css/tf-public.css',
			array(),
			TF_VERSION
		);

		wp_enqueue_script(
			'tf-public',
			TF_PLUGIN_URL . 'public/js/tf-public.js',
			array( 'jquery' ),
			TF_VERSION,
			true
		);

		wp_localize_script(
			'tf-public',
			'tfPublic',
			array(
				'restUrl' => esc_url_raw( rest_url( 'thunderfire/v1/' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
