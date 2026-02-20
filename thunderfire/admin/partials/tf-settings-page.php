<?php
/**
 * Settings page template
 *
 * @package THUNDERFIRE
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap tf-settings-page">
	<h1><?php esc_html_e( 'THUNDERFIRE Settings', 'thunderfire' ); ?></h1>

	<div class="tf-test-connection-wrapper">
		<button type="button" class="button button-secondary" id="tf-test-connection">
			<?php esc_html_e( 'Test Connection', 'thunderfire' ); ?>
		</button>
		<button type="button" class="button button-secondary" id="tf-clear-cache">
			<?php esc_html_e( 'Clear Cache', 'thunderfire' ); ?>
		</button>
		<span id="tf-connection-status"></span>
	</div>

	<form method="post" action="options.php">
		<?php
		settings_fields( 'tf_settings' );
		do_settings_sections( 'thunderfire' );
		submit_button();
		?>
	</form>
</div>
