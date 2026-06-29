<?php
/**
 * Site-wide trust badge.
 *
 * @package ThreeWStorefrontPolish
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const THREEW_GOOGLE_REVIEWS_URL = 'https://maps.app.goo.gl/MMheoaH4VyDr6RUc9';

add_action(
	'wp_footer',
	static function () {
		?>
		<div id="threew-google-trust-badge" class="threew-google-trust-badge" hidden>
			<a class="threew-google-trust-badge__link" href="<?php echo esc_url( THREEW_GOOGLE_REVIEWS_URL ); ?>" aria-label="Read 3W Distributing reviews on Google">
				<span class="threew-google-trust-badge__stars" style="color:#fbbc04;-webkit-text-fill-color:#fbbc04;" aria-hidden="true">★★★★★</span>
				<span>Rated on Google · Read reviews</span>
			</a>
		</div>
		<script>
			(function () {
				var badge = document.getElementById('threew-google-trust-badge');
				var footer = document.querySelector('footer, #footer, .footer-wrapper, .site-footer, .footer-bottom');
				if (!badge || !footer) {
					return;
				}
				footer.appendChild(badge);
				badge.hidden = false;

				var hero = document.querySelector('.threew-hero-static .porto-ibanner-layer, .home-banner .porto-ibanner-layer');
				if (hero) {
					var heroBadge = badge.cloneNode(true);
					heroBadge.id = 'threew-google-trust-badge-hero';
					heroBadge.className += ' threew-google-trust-badge--hero';
					hero.appendChild(heroBadge);
				}
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
				color: #fbbc04 !important;
				letter-spacing: 1px;
			}
			.threew-google-trust-badge--hero {
				margin: 16px 0 0;
				text-align: left;
			}
			.threew-google-trust-badge--hero .threew-google-trust-badge__link {
				padding: 7px 11px;
				border-radius: 999px;
				background: rgba(0, 0, 0, .62);
				color: #fff;
				font-size: 12px;
				font-weight: 600;
				opacity: 1;
			}
			@media (max-width: 480px) {
				.threew-google-trust-badge {
					font-size: 12px;
				}
				.threew-google-trust-badge--hero {
					margin-top: 12px;
				}
				.threew-google-trust-badge--hero .threew-google-trust-badge__link {
					padding: 6px 9px;
					font-size: 10px;
				}
			}
		</style>
		<?php
	},
	30
);
