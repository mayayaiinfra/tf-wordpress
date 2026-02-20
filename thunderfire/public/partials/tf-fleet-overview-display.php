<?php
/**
 * Fleet overview display template
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="tf-fleet-overview">
	<?php if ( is_wp_error( $nodes ) ) : ?>
		<p class="tf-error"><?php echo esc_html( $nodes->get_error_message() ); ?></p>
	<?php elseif ( empty( $nodes ) || ! is_array( $nodes ) ) : ?>
		<p><?php esc_html_e( 'No nodes connected.', 'thunderfire' ); ?></p>
	<?php else : ?>
		<table class="tf-fleet-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'ID', 'thunderfire' ); ?></th>
					<th><?php esc_html_e( 'Name', 'thunderfire' ); ?></th>
					<th><?php esc_html_e( 'Tier', 'thunderfire' ); ?></th>
					<th><?php esc_html_e( 'Health', 'thunderfire' ); ?></th>
					<th><?php esc_html_e( 'Status', 'thunderfire' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $nodes as $node ) : ?>
					<?php
					$health = $node['health'] ?? 0;
					$class  = $health >= 80 ? 'good' : ( $health >= 50 ? 'warning' : 'critical' );
					?>
					<tr class="tf-health-<?php echo esc_attr( $class ); ?>">
						<td><?php echo esc_html( $node['id'] ?? '?' ); ?></td>
						<td><?php echo esc_html( $node['name'] ?? '—' ); ?></td>
						<td>T<?php echo esc_html( $node['tier'] ?? '?' ); ?></td>
						<td>
							<div class="tf-health-bar-small">
								<span class="tf-health-fill" style="width: <?php echo esc_attr( $health ); ?>%;"></span>
							</div>
							<?php echo esc_html( $health ); ?>%
						</td>
						<td><?php echo esc_html( $node['status'] ?? 'unknown' ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>
