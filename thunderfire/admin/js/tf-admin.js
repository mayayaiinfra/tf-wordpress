/**
 * THUNDERFIRE Admin Scripts
 */

(function($) {
	'use strict';

	// Test Connection
	$('#tf-test-connection').on('click', function() {
		var $btn = $(this);
		var $status = $('#tf-connection-status');

		$btn.prop('disabled', true);
		$status.text('Testing...').removeClass('success error');

		$.post(tfAdmin.ajaxUrl, {
			action: 'tf_test_connection',
			_ajax_nonce: tfAdmin.testNonce
		}, function(response) {
			$btn.prop('disabled', false);
			if (response.success) {
				$status.addClass('success').text(
					response.data.message + ' (' + response.data.node_count + ' nodes)'
				);
			} else {
				$status.addClass('error').text(response.data.message);
			}
		}).fail(function() {
			$btn.prop('disabled', false);
			$status.addClass('error').text('Network error');
		});
	});

	// Clear Cache
	$('#tf-clear-cache').on('click', function() {
		var $btn = $(this);
		var $status = $('#tf-connection-status');

		$btn.prop('disabled', true);

		$.post(tfAdmin.ajaxUrl, {
			action: 'tf_clear_cache',
			_ajax_nonce: tfAdmin.clearCacheNonce
		}, function(response) {
			$btn.prop('disabled', false);
			if (response.success) {
				$status.addClass('success').text(response.data.message);
			}
		}).fail(function() {
			$btn.prop('disabled', false);
		});
	});

	// Dashboard Widget Refresh
	$(document).on('click', '#tf-refresh-widget', function() {
		var $btn = $(this);
		var $widget = $btn.closest('.tf-dashboard-widget');

		$btn.prop('disabled', true).text('Refreshing...');

		$.post(tfAdmin.ajaxUrl, {
			action: 'tf_refresh_dashboard',
			_ajax_nonce: tfAdmin.refreshNonce
		}, function(response) {
			if (response.success) {
				$widget.replaceWith(response.data.html);
			}
		}).always(function() {
			$btn.prop('disabled', false).text('Refresh');
		});
	});

})(jQuery);
