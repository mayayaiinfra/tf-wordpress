<?php
/**
 * TF_WooCommerce - WooCommerce integration
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_WooCommerce
 */
class TF_WooCommerce {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// Product node binding field.
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_node_binding_field' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_node_binding_field' ) );

		// Product page health widget.
		add_action( 'woocommerce_single_product_summary', array( $this, 'show_product_health' ), 25 );

		// Order fulfillment.
		add_action( 'woocommerce_order_status_processing', array( $this, 'notify_node_on_order' ) );

		// Admin product list warning column.
		add_filter( 'manage_product_posts_columns', array( $this, 'add_health_column' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'render_health_column' ), 10, 2 );
	}

	/**
	 * Add node binding field to product.
	 */
	public function add_node_binding_field() {
		woocommerce_wp_text_input(
			array(
				'id'          => '_tf_bound_node_id',
				'label'       => __( 'THUNDERFIRE Node ID', 'thunderfire' ),
				'desc_tip'    => true,
				'description' => __( 'Bind this product to a THUNDERFIRE autonomous node.', 'thunderfire' ),
			)
		);
	}

	/**
	 * Save node binding field.
	 *
	 * @param int $post_id Product ID.
	 */
	public function save_node_binding_field( $post_id ) {
		$node_id = isset( $_POST['_tf_bound_node_id'] ) ? sanitize_text_field( wp_unslash( $_POST['_tf_bound_node_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$product = wc_get_product( $post_id );
		$product->update_meta_data( '_tf_bound_node_id', $node_id );
		$product->save();
	}

	/**
	 * Show product health widget.
	 */
	public function show_product_health() {
		global $product;

		$node_id = $product->get_meta( '_tf_bound_node_id' );
		if ( empty( $node_id ) ) {
			return;
		}

		$client = TF_API_Client::get_instance();
		$health = $client->node_health( $node_id );

		include TF_PLUGIN_DIR . 'public/partials/tf-woo-node-health.php';
	}

	/**
	 * Notify node on order processing.
	 *
	 * @param int $order_id Order ID.
	 */
	public function notify_node_on_order( $order_id ) {
		$order = wc_get_order( $order_id );

		foreach ( $order->get_items() as $item ) {
			$product_id = $item->get_product_id();
			$product    = wc_get_product( $product_id );
			$node_id    = $product->get_meta( '_tf_bound_node_id' );

			if ( empty( $node_id ) ) {
				continue;
			}

			$goal = sprintf(
				'Prepare order #%d: %s x%d',
				$order_id,
				$item->get_name(),
				$item->get_quantity()
			);

			$client = TF_API_Client::get_instance();
			$client->set_goal( $node_id, $goal );
		}
	}

	/**
	 * Add health column to product list.
	 *
	 * @param array $columns Columns.
	 * @return array Modified columns.
	 */
	public function add_health_column( $columns ) {
		$columns['tf_health'] = __( 'Node', 'thunderfire' );
		return $columns;
	}

	/**
	 * Render health column.
	 *
	 * @param string $column  Column name.
	 * @param int    $post_id Post ID.
	 */
	public function render_health_column( $column, $post_id ) {
		if ( 'tf_health' !== $column ) {
			return;
		}

		$product = wc_get_product( $post_id );
		$node_id = $product->get_meta( '_tf_bound_node_id' );

		if ( empty( $node_id ) ) {
			echo '—';
			return;
		}

		$warning = $product->get_meta( '_tf_health_warning' );
		if ( $warning ) {
			echo '<span class="dashicons dashicons-warning" style="color: #d63638;" title="' . esc_attr( $warning ) . '"></span>';
		} else {
			echo '<span class="dashicons dashicons-yes-alt" style="color: #00a32a;" title="' . esc_attr( $node_id ) . '"></span>';
		}
	}
}

// Auto-initialize when loaded.
new TF_WooCommerce();
