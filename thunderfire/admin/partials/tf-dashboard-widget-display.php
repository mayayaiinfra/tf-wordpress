<?php
/**
 * Dashboard widget display template
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tf-dashboard-widget">
	<?php if ( is_wp_error( $nodes ) ) : ?>
		<p class="tf-error"><?php echo esc_html( $nodes->get_error_message() ); ?></p>
	<?php elseif ( empty( $nodes ) || ! is_array( $nodes ) ) : ?>
		<p><?php esc_html_e( 'No nodes connected.', 'thunderfire' ); ?></p>
	<?php else : ?>
		<?php
		$online = count( array_filter( $nodes, fn( $n ) => ( $n['health'] ?? 0 ) > 0 ) );
		$total  = count( $nodes );
		?>
		<div class="tf-fleet-summary">
			<span class="tf-node-count">
				<?php
				printf(
					/* translators: 1: online count, 2: total count */
					esc_html__( '%1$d / %2$d nodes online', 'thunderfire' ),
					$online,
					$total
				);
				?>
			</span>
		</div>

		<ul class="tf-node-list">
			<?php foreach ( array_slice( $nodes, 0, 5 ) as $node ) : ?>
				<?php
				$health = $node['health'] ?? 0;
				$class  = $health >= 80 ? 'good' : ( $health >= 50 ? 'warning' : 'critical' );
				?>
				<li class="tf-node-item tf-health-<?php echo esc_attr( $class ); ?>">
					<span class="tf-node-name"><?php echo esc_html( $node['name'] ?? $node['id'] ?? '?' ); ?></span>
					<span class="tf-health-bar">
						<span class="tf-health-fill" style="width: <?php echo esc_attr( $health ); ?>%;"></span>
					</span>
					<span class="tf-health-percent"><?php echo esc_html( $health ); ?>%</span>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php
		$alerts = get_option( 'tf_pending_alerts', array() );
		$recent = array_slice( $alerts, -3 );
		if ( ! empty( $recent ) ) :
			?>
			<div class="tf-recent-alerts">
				<h4><?php esc_html_e( 'Recent Alerts', 'thunderfire' ); ?></h4>
				<ul>
					<?php foreach ( array_reverse( $recent ) as $alert ) : ?>
						<li class="tf-alert-<?php echo esc_attr( $alert['severity'] ?? 'info' ); ?>">
							<?php echo esc_html( $alert['message'] ?? '' ); ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="tf-widget-footer">
			<span class="tf-last-update">
				<?php
				printf(
					/* translators: %s: time ago */
					esc_html__( 'Last updated: %s ago', 'thunderfire' ),
					esc_html( human_time_diff( get_option( 'tf_last_sync', time() ) ) )
				);
				?>
			</span>
			<button type="button" class="button button-small" id="tf-refresh-widget">
				<?php esc_html_e( 'Refresh', 'thunderfire' ); ?>
			</button>
		</div>
	<?php endif; ?>
</div>
