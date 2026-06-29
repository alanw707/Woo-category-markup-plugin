<?php
/**
 * Site-wide trust badge.
 *
 * @package ThreeWStorefrontPolish
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const THREEW_GOOGLE_REVIEWS_URL = 'https://share.google/LZQ9Kw1ok4qVabLpg';

add_action(
	'wp_footer',
	static function () {
		?>
		<div id="threew-google-trust-badge" class="threew-google-trust-badge" hidden>
			<a class="threew-google-trust-badge__link" href="<?php echo esc_url( THREEW_GOOGLE_REVIEWS_URL ); ?>" aria-label="Read 3W Distributing reviews on Google">
				<span class="threew-google-trust-badge__stars" aria-hidden="true">★★★★★</span>
				<span>Rated on Google · Read reviews</span>
			</a>
		</div>
		<script>
			(function () {
				var badge = document.getElementById('threew-google-trust-badge');
				var footer = document.querySelector('footer, #footer, .footer-wrapper, .site-footer');
				if (!badge || !footer) {
					return;
				}
				footer.appendChild(badge);
				badge.hidden = false;
			}());
		</script>
		<?php
	},
	30
);

add_action(
	'wp_head',
	static function () {
		?>
		<style>
			.threew-google-trust-badge {
				margin: 14px auto 0;
				text-align: center;
				font-size: 13px;
				line-height: 1.4;
			}
			.threew-google-trust-badge__link {
				display: inline-flex;
				align-items: center;
				gap: 7px;
				color: inherit;
				text-decoration: none;
				opacity: .86;
			}
			.threew-google-trust-badge__link:hover,
			.threew-google-trust-badge__link:focus {
				opacity: 1;
				text-decoration: underline;
			}
			.threew-google-trust-badge__stars {
				color: #fbbc04;
				letter-spacing: 1px;
			}
			@media (max-width: 480px) {
				.threew-google-trust-badge {
					font-size: 12px;
				}
			}
		</style>
		<?php
	},
	30
);
