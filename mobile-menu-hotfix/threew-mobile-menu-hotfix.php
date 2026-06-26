<?php
/**
 * Plugin Name: 3W Mobile Menu Hotfix
 * Description: Restores the Porto mobile menu close hitbox and close behavior.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'wp_head',
	static function () {
		?>
		<style id="threew-mobile-menu-hotfix-css">
			html.panel-opened .page-wrapper.side-nav {
				z-index: auto !important;
			}

			.side-nav-panel-close {
				pointer-events: auto !important;
				z-index: 100000 !important;
			}
		</style>
		<?php
	},
	99
);

add_action(
	'wp_footer',
	static function () {
		?>
		<script id="threew-mobile-menu-hotfix-js">
			(function () {
				function closePanel(event) {
					if (event) {
						event.preventDefault();
						event.stopPropagation();
					}

					document.documentElement.classList.remove('panel-opened');
					document.body.classList.remove('panel-opened');

					document.querySelectorAll('.panel-overlay.active').forEach(function (overlay) {
						overlay.classList.remove('active');
					});

					document.querySelectorAll('#side-nav-panel').forEach(function (panel) {
						panel.classList.remove('active', 'open', 'opened');
					});
				}

				document.addEventListener(
					'click',
					function (event) {
						if (
							event.target.closest &&
							event.target.closest('.side-nav-panel-close, .panel-overlay.active')
						) {
							closePanel(event);
						}
					},
					true
				);

				document.addEventListener('keydown', function (event) {
					if (event.key === 'Escape' && document.documentElement.classList.contains('panel-opened')) {
						closePanel(event);
					}
				});
			})();
		</script>
		<?php
	},
	99
);
