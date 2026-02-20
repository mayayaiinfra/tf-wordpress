<?php
/**
 * TF_Widget - Sidebar widget
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TF_Widget
 */
class TF_Widget extends WP_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			'tf_widget',
			__( 'THUNDERFIRE Node Health', 'thunderfire' ),
			array(
				'description' => __( 'Display node health in sidebar.', 'thunderfire' ),
			)
		);
	}

	/**
	 * Register the widget.
	 */
	public function register_widget() {
		register_widget( 'TF_Widget' );
	}

	/**
	 * Widget output.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Widget instance.
	 */
	public function widget( $args, $instance ) {
		$title   = apply_filters( 'widget_title', $instance['title'] ?? '' );
		$node_id = sanitize_text_field( $instance['node_id'] ?? 'all' );

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( ! empty( $title ) ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		$client = TF_API_Client::get_instance();

		if ( 'all' === $node_id ) {
			$nodes = $client->list_nodes();
			if ( is_wp_error( $nodes ) ) {
				echo '<p class="tf-widget-error">' . esc_html__( 'Unable to fetch node data.', 'thunderfire' ) . '</p>';
			} elseif ( is_array( $nodes ) ) {
				$online = count( array_filter( $nodes, fn( $n ) => ( $n['health'] ?? 0 ) > 0 ) );
				$total  = count( $nodes );
				$class  = $online === $total ? 'tf-health-good' : ( $online > 0 ? 'tf-health-warning' : 'tf-health-critical' );
				printf(
					'<div class="tf-widget-status %s"><span class="tf-status-dot"></span> %s: %d/%d %s</div>',
					esc_attr( $class ),
					esc_html__( 'Fleet', 'thunderfire' ),
					$online,
					$total,
					esc_html__( 'nodes online', 'thunderfire' )
				);
			}
		} else {
			$health = $client->node_health( $node_id );
			if ( is_wp_error( $health ) ) {
				echo '<p class="tf-widget-error">' . esc_html__( 'Unable to fetch node data.', 'thunderfire' ) . '</p>';
			} else {
				$h     = $health['health'] ?? 0;
				$class = $h >= 80 ? 'tf-health-good' : ( $h >= 50 ? 'tf-health-warning' : 'tf-health-critical' );
				$label = $h >= 80 ? __( 'Healthy', 'thunderfire' ) : ( $h >= 50 ? __( 'Warning', 'thunderfire' ) : __( 'Critical', 'thunderfire' ) );
				printf(
					'<div class="tf-widget-status %s"><span class="tf-status-dot"></span> %s: %s</div>',
					esc_attr( $class ),
					esc_html( $node_id ),
					esc_html( $label )
				);
			}
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Widget form in admin.
	 *
	 * @param array $instance Widget instance.
	 */
	public function form( $instance ) {
		$title   = $instance['title'] ?? __( 'Node Status', 'thunderfire' );
		$node_id = $instance['node_id'] ?? 'all';
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'thunderfire' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'node_id' ) ); ?>"><?php esc_html_e( 'Node ID (or "all"):', 'thunderfire' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'node_id' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'node_id' ) ); ?>" type="text" value="<?php echo esc_attr( $node_id ); ?>">
		</p>
		<?php
	}

	/**
	 * Update widget settings.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array Updated settings.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance            = array();
		$instance['title']   = sanitize_text_field( $new_instance['title'] ?? '' );
		$instance['node_id'] = sanitize_text_field( $new_instance['node_id'] ?? 'all' );
		return $instance;
	}
}
