<?php
/**
 * WooCommerce product node health widget
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tf-woo-node-health">
	<?php if ( is_wp_error( $health ) ) : ?>
		<p class="tf-woo-error"><?php esc_html_e( 'Unable to fetch node status.', 'thunderfire' ); ?></p>
	<?php else : ?>
		<?php
		$h     = $health['health'] ?? 0;
		$class = $h >= 80 ? 'good' : ( $h >= 50 ? 'warning' : 'critical' );
		?>
		<div class="tf-woo-health-card tf-woo-health-<?php echo esc_attr( $class ); ?>">
			<h4><?php esc_html_e( 'Autonomous Node Status', 'thunderfire' ); ?></h4>
			<div class="tf-woo-status">
				<span class="tf-woo-node-id"><?php echo esc_html( $node_id ); ?></span>
				<span class="tf-woo-health-badge"><?php echo esc_html( $h ); ?>%</span>
			</div>
			<?php if ( isset( $health['stage'] ) ) : ?>
				<div class="tf-woo-theta">
					<?php
					printf(
						/* translators: %s: THETA stage name */
						esc_html__( 'THETA: %s', 'thunderfire' ),
						esc_html( $health['stage'] )
					);
					?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>
