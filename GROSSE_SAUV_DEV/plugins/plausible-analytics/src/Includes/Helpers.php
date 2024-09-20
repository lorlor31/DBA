<?php
/**
 * Plausible Analytics | Helpers
 *
 * @since      1.0.0
 *
 * @package    WordPress
 * @subpackage Plausible Analytics
 */

namespace Plausible\Analytics\WP\Includes;

use Exception;
use WpOrg\Requests\Exception\InvalidArgument;

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Helpers {

	/**
	 * Get Plain Domain (without protocol or www. subdomain)
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_domain() {
		$url = home_url();

		return preg_replace( '/^http(s?)\:\/\/(www\.)?/i', '', $url );
	}

	/**
	 * Get Analytics URL.
	 *
	 * @since  1.0.0
	 *
	 * @param bool $local Return the Local JS file IF proxy is enabled.
	 *
	 * @return string
	 */
	public static function get_js_url( $local = false ) {
		$settings       = self::get_settings();
		$file_name      = self::get_filename( $local );
		$default_domain = 'plausible.io';
		$domain         = $default_domain;

		/**
		 * If Avoid ad blockers is enabled, return URL to local file.
		 */
		if ( $local && self::proxy_enabled() ) {
			return esc_url(
				self::get_proxy_resource( 'cache_url' ) . $file_name . '.js'
			);
		}

		// Allows for hard-coding the self-hosted domain.
		if ( defined( 'PLAUSIBLE_SELF_HOSTED_DOMAIN' ) ) {
			// phpcs:ignore
			$domain = PLAUSIBLE_SELF_HOSTED_DOMAIN;
		}

		/**
		 * Set $domain to self_hosted_domain if it exists.
		 */
		if (
			! empty( $settings['self_hosted_domain'] ) && $domain === $default_domain
		) {
			$domain = $settings['self_hosted_domain'];
		}

		$url = "https://{$domain}/js/{$file_name}.js";

		return esc_url( $url );
	}

	/**
	 * Is the proxy enabled?
	 *
	 * @return bool
	 */
	public static function proxy_enabled() {
		$settings = self::get_settings();

		return ! empty( $settings['proxy_enabled'][0] ) || isset( $_GET['plausible_proxy'] );
	}

	/**
	 * A convenient way to retrieve the absolute path to the local JS file.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function get_js_path() {
		return self::get_proxy_resource( 'cache_dir' ) . self::get_filename( true ) . '.js';
	}

	/**
	 * Get filename (without file extension)
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public static function get_filename( $local = false ) {
		$settings  = self::get_settings();
		$file_name = 'plausible';

		if ( $local && self::proxy_enabled() ) {
			return self::get_proxy_resource( 'file_alias' );
		}

		foreach ( [ 'outbound-links', 'file-downloads', 'tagged-events', 'compat', 'hash' ] as $extension ) {
			if ( in_array( $extension, $settings['enhanced_measurements'], true ) ) {
				$file_name .= '.' . $extension;
			}
		}

		// Load exclusions.js if any excluded pages are set.
		if ( ! empty( $settings['excluded_pages'] ) ) {
			$file_name .= '.' . 'exclusions';
		}

		return $file_name;
	}

	/**
	 * Downloads the plausible.js file to this server.
	 *
	 * @since 1.3.0
	 *
	 * @param string $remote_file Full URL to file to download.
	 * @param string $local_file  Absolutate path to where to store the $remote_file.
	 *
	 * @return bool True when successfull. False if it fails.
	 *
	 * @throws InvalidArgument
	 * @throws Exception
	 */
	public static function download_file( $remote_file, $local_file ) {
		$file_contents = wp_remote_get( $remote_file );

		if ( is_wp_error( $file_contents ) ) {
			// TODO: add error handling?
			return false;
		}

		/**
		 * Some servers don't do a full overwrite if file already exists, so we delete it first.
		 */
		if ( file_exists( $local_file ) ) {
			unlink( $local_file );
		}

		$write = file_put_contents( $local_file, wp_remote_retrieve_body( $file_contents ) );

		return $write > 0;
	}

	/**
	 * Get Dashboard URL.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_analytics_dashboard_url() {
		$settings = self::get_settings();
		$domain   = $settings['domain_name'];

		return esc_url( "https://plausible.io/{$domain}" );
	}

	/**
	 * Get Settings.
	 *
	 * @since  1.0.0
	 * @access public
	 *
	 * @return array
	 */
	public static function get_settings() {
		$defaults = [
			'domain_name'             => '',
			'enhanced_measurements'   => [],
			'proxy_enabled'           => '',
			'shared_link'             => '',
			'excluded_pages'          => '',
			'tracked_user_roles'      => [],
			'expand_dashboard_access' => [],
			'disable_toolbar_menu'    => '',
			'self_hosted_domain'      => '',
		];

		$settings = get_option( 'plausible_analytics_settings', [] );

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Get a proxy resource by name.
	 *
	 * @param string $resource_name
	 *
	 * @return string Value of resource from DB or empty string if Bypass ad blockers option is disabled.
	 *
	 * @throws Exception
	 */
	public static function get_proxy_resource( $resource_name = '' ) {
		$resources = self::get_proxy_resources();

		/**
		 * Create the cache directory if it doesn't exist.
		 */
		if ( $resource_name === 'cache_dir' && ! is_dir( $resources[ $resource_name ] ) ) {
			wp_mkdir_p( $resources[ $resource_name ] );
		}

		return isset( $resources[ $resource_name ] ) ? $resources[ $resource_name ] : '';
	}

	/**
	 * Get (and generate/store if non-existent) proxy resources.
	 *
	 * @return array
	 */
	public static function get_proxy_resources() {
		static $resources;

		if ( $resources === null ) {
			$resources = get_option( 'plausible_analytics_proxy_resources', [] );
		}

		/**
		 * Force a refresh of our resources if the user recently switched to SSL and we still have non-SSL resources stored.
		 */
		if ( ! empty( $resources ) && is_ssl() && isset( $resources['cache_url'] ) && ( strpos( $resources['cache_url'], 'http:' ) !== false ) ) {
			$resources = [];
		}

		if ( empty( $resources ) ) {
			$cache_dir  = bin2hex( random_bytes( 5 ) );
			$upload_dir = wp_get_upload_dir();
			$resources  = [
				'namespace'  => bin2hex( random_bytes( 3 ) ),
				'base'       => bin2hex( random_bytes( 2 ) ),
				'endpoint'   => bin2hex( random_bytes( 4 ) ),
				'cache_dir'  => trailingslashit( $upload_dir['basedir'] ) . trailingslashit( $cache_dir ),
				'cache_url'  => trailingslashit( $upload_dir['baseurl'] ) . trailingslashit( $cache_dir ),
				'file_alias' => bin2hex( random_bytes( 4 ) ),
			];

			update_option( 'plausible_analytics_proxy_resources', $resources );
		}

		return $resources;
	}

	/**
	 * Get Data API URL.
	 *
	 * @since  1.2.2
	 * @access public
	 *
	 * @return string
	 */
	public static function get_data_api_url() {
		$settings = self::get_settings();
		$url      = 'https://plausible.io/api/event';

		if ( self::proxy_enabled() ) {
			// This'll make sure the API endpoint is properly registered when we're testing.
			$append = isset( $_GET['plausible_proxy'] ) ? '?plausible_proxy=1' : '';

			return self::get_rest_endpoint() . $append;
		}

		// Triggered when self-hosted analytics is enabled.
		if (
			! empty( $settings['self_hosted_domain'] )
		) {
			$default_domain = $settings['self_hosted_domain'];
			$url            = "https://{$default_domain}/api/event";
		}

		return esc_url( $url );
	}

	/**
	 * Returns the Proxy's REST endpoint.
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function get_rest_endpoint( $abs_url = true ) {
		$namespace = self::get_proxy_resource( 'namespace' );
		$base      = self::get_proxy_resource( 'base' );
		$endpoint  = self::get_proxy_resource( 'endpoint' );

		$uri = "$namespace/v1/$base/$endpoint";

		if ( $abs_url ) {
			return get_rest_url( null, $uri );
		}

		return '/' . rest_get_url_prefix() . '/' . $uri;
	}

	/**
	 * Get Quick Actions.
	 *
	 * @since  1.3.0
	 * @access public
	 *
	 * @return array
	 */
	public static function get_quick_actions() {
		return [
			'view-docs'        => [
				'label' => esc_html__( 'Documentation', 'plausible-analytics' ),
				'url'   => esc_url( 'https://docs.plausible.io/' ),
			],
			'report-issue'     => [
				'label' => esc_html__( 'Report an issue', 'plausible-analytics' ),
				'url'   => esc_url( 'https://github.com/plausible/wordpress/issues/new' ),
			],
			'translate-plugin' => [
				'label' => esc_html__( 'Translate Plugin', 'plausible-analytics' ),
				'url'   => esc_url( 'https://translate.wordpress.org/projects/wp-plugins/plausible-analytics/' ),
			],
		];
	}

	/**
	 * Render Quick Actions
	 *
	 * @since  1.3.0
	 * @access public
	 *
	 * @return string
	 */
	public static function render_quick_actions() {
		ob_start();
		$quick_actions = self::get_quick_actions();
		?>
		<div class="plausible-analytics-quick-actions">
		<?php
		if ( ! empty( $quick_actions ) && count( $quick_actions ) > 0 ) {
			?>
			<div class="plausible-analytics-quick-actions-title">
				<?php esc_html_e( 'Quick Links', 'plausible-analytics' ); ?>
			</div>
			<ul>
			<?php
			foreach ( $quick_actions as $quick_action ) {
				?>
				<li>
					<a target="_blank" href="<?php echo $quick_action['url']; ?>" title="<?php echo $quick_action['label']; ?>">
						<?php echo $quick_action['label']; ?>
					</a>
				</li>
				<?php
			}
			?>
			</ul>
			<?php
		}
		?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Clean variables using `sanitize_text_field`.
	 * Arrays are cleaned recursively. Non-scalar values are ignored.
	 *
	 * @param string|array $var Sanitize the variable.
	 *
	 * @since  1.3.0
	 * @access public
	 *
	 * @return string|array
	 */
	public static function clean( $var ) {
		// If the variable is an array, recursively apply the function to each element of the array.
		if ( is_array( $var ) ) {
			return array_map( [ __CLASS__, __METHOD__ ], $var );
		}

		// If the variable is a scalar value (string, integer, float, boolean).
		if ( is_scalar( $var ) ) {
			// Parse the variable using the wp_parse_url function.
			$parsed = wp_parse_url( $var );
			// If the variable has a scheme (e.g. http:// or https://), sanitize the variable using the esc_url_raw function.
			if ( isset( $parsed['scheme'] ) ) {
				return esc_url_raw( wp_unslash( $var ), [ $parsed['scheme'] ] );
			}
			// If the variable does not have a scheme, sanitize the variable using the sanitize_text_field function.
			return sanitize_text_field( wp_unslash( $var ) );
		}

		// If the variable is not an array or a scalar value, return the variable unchanged.
		return $var;
	}

	/**
	 * Get user role for the logged-in user.
	 *
	 * @since  1.3.0
	 * @access public
	 *
	 * @return string
	 */
	public static function get_user_role() {
		global $current_user;

		$user_roles = $current_user->roles;

		return array_shift( $user_roles );
	}
}
