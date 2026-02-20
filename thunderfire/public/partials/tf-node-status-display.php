<?php
/**
 * Node status display template
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$refresh_interval = $refresh_interval ?? 30;
?>
<div class="tf-node-status" data-node-id="<?php echo esc_attr( $node_id ); ?>" data-refresh="<?php echo esc_attr( $refresh_interval ); ?>">
	<?php if ( is_wp_error( $health ) ) : ?>
		<p class="tf-error"><?php echo esc_html( $health->get_error_message() ); ?></p>
	<?php else : ?>
		<?php
		$h     = $health['health'] ?? 0;
		$class = $h >= 80 ? 'good' : ( $h >= 50 ? 'warning' : 'critical' );
		?>
		<div class="tf-status-card tf-health-<?php echo esc_attr( $class ); ?>">
			<h4 class="tf-node-id"><?php echo esc_html( $node_id ); ?></h4>
			<div class="tf-health-display">
				<span class="tf-health-value"><?php echo esc_html( $h ); ?>%</span>
				<span class="tf-health-label"><?php esc_html_e( 'Health', 'thunderfire' ); ?></span>
			</div>
			<div class="tf-health-bar">
				<span class="tf-health-fill" style="width: <?php echo esc_attr( $h ); ?>%;"></span>
			</div>

			<?php if ( ! empty( $show_theta ) ) : ?>
				<div class="tf-theta-status">
					<span class="tf-theta-label"><?php esc_html_e( 'THETA Stage:', 'thunderfire' ); ?></span>
					<span class="tf-theta-value"><?php echo esc_html( $health['stage'] ?? 'N/A' ); ?></span>
				</div>
			<?php endif; ?>

			<ul class="tf-chitral-fields">
				<?php
				$fields = array( 'capability', 'intent', 'timeline', 'resources', 'authority', 'lifecycle' );
				foreach ( $fields as $field ) :
					if ( isset( $health[ $field ] ) ) :
						?>
						<li>
							<span class="tf-field-name"><?php echo esc_html( ucfirst( $field ) ); ?></span>
							<span class="tf-field-value"><?php echo esc_html( $health[ $field ] ); ?></span>
						</li>
						<?php
					endif;
				endforeach;
				?>
			</ul>
		</div>
	<?php endif; ?>
</div>
