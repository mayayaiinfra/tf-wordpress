/**
 * THUNDERFIRE Public Scripts
 */

(function() {
	'use strict';

	// Auto-refresh node status blocks
	function initAutoRefresh() {
		document.querySelectorAll('.tf-node-status[data-refresh]').forEach(function(el) {
			var nodeId = el.dataset.nodeId;
			var interval = parseInt(el.dataset.refresh, 10) * 1000;

			if (interval > 0 && nodeId) {
				setInterval(function() {
					refreshNodeStatus(el, nodeId);
				}, interval);
			}
		});
	}

	function refreshNodeStatus(el, nodeId) {
		fetch(tfPublic.restUrl + 'nodes/' + encodeURIComponent(nodeId), {
			headers: {
				'X-WP-Nonce': tfPublic.nonce
			}
		})
		.then(function(response) {
			return response.json();
		})
		.then(function(data) {
			if (data && typeof data.health !== 'undefined') {
				updateHealthDisplay(el, data);
			}
		})
		.catch(function(err) {
			console.error('THUNDERFIRE: Failed to refresh node status', err);
		});
	}

	function updateHealthDisplay(el, data) {
		var health = data.health || 0;
		var healthClass = health >= 80 ? 'good' : (health >= 50 ? 'warning' : 'critical');

		var card = el.querySelector('.tf-status-card');
		if (card) {
			card.className = 'tf-status-card tf-health-' + healthClass;
		}

		var valueEl = el.querySelector('.tf-health-value');
		if (valueEl) {
			valueEl.textContent = health + '%';
		}

		var fillEl = el.querySelector('.tf-health-fill');
		if (fillEl) {
			fillEl.style.width = health + '%';
		}

		if (data.stage) {
			var thetaEl = el.querySelector('.tf-theta-value');
			if (thetaEl) {
				thetaEl.textContent = data.stage;
			}
		}
	}

	// Initialize on DOM ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initAutoRefresh);
	} else {
		initAutoRefresh();
	}
})();
