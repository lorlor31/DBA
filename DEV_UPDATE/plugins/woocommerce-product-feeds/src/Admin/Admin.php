<?php

namespace Ademti\WoocommerceProductFeeds\Admin;

use ActionScheduler_Store;
use Ademti\WoocommerceProductFeeds\Cache\Cache;
use Ademti\WoocommerceProductFeeds\Configuration\Configuration;
use Ademti\WoocommerceProductFeeds\Configuration\FeedConfigRepository;
use Ademti\WoocommerceProductFeeds\Helpers\TemplateLoader;
use Exception;
use WP_Post;
use WP_Term;

/**
 * Admin class.
 *
 * Handles the display of the settings page, product meta boxes, and category meta.
 */
class Admin {

	// Dependencies.
	private Configuration $configuration;
	private TemplateLoader $template_loader;
	private ProductFeedImageManager $feed_image_manager;
	private FeedConfigRepository $feed_config_repository;
	private Cache $cache;

	/**
	 * @var array
	 */
	private array $settings = [];

	/**
	 * @var array
	 */
	private array $product_fields = [];

	/**
	 * Base directory of the plugin.
	 *
	 * @var string
	 */
	private string $base_dir;

	/**
	 * @var array
	 */
	private array $grouped_product_fields = [];

	/**
	 * Constructor.
	 *
	 * @param Configuration $configuration
	 * @param TemplateLoader $template_loader
	 * @param Cache $cache
	 * @param FeedConfigRepository $feed_config_repository
	 */
	public function __construct(
		Configuration $configuration,
		TemplateLoader $template_loader,
		Cache $cache,
		FeedConfigRepository $feed_config_repository,
		ProductFeedImageManager $feed_image_manager
	) {
		$this->configuration          = $configuration;
		$this->template_loader        = $template_loader;
		$this->cache                  = $cache;
		$this->feed_config_repository = $feed_config_repository;
		$this->feed_image_manager     = $feed_image_manager;
	}

	/**
	 * Set up the relevant hooks actions, and load the settings
	 *
	 * @access public
	 */
	public function initialise(): void {
		$this->base_dir = dirname( __DIR__, 2 );

		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_init', [ $this, 'admin_init' ], 11 );
		add_action( 'admin_print_styles', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_print_scripts', [ $this, 'enqueue_scripts' ] );

		// Extend category admin page.
		add_action( 'product_cat_add_form_fields', [ $this, 'category_add_meta_box' ], 99, 1 ); // After left-col
		add_action( 'product_cat_edit_form_fields', [ $this, 'category_edit_meta_box' ], 99, 2 ); // After left-col
		add_action( 'created_product_cat', [ $this, 'save_category' ], 15, 1 ); //After created
		add_action( 'edited_product_cat', [ $this, 'save_category' ], 15, 1 ); //After saved

		// Variation form input.
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'variation_input_fields' ], 90, 3 );
		add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation' ], 10, 2 );

		add_filter( 'woocommerce_settings_tabs_array', [ $this, 'add_woocommerce_settings_tab' ], 99 );
		add_action( 'woocommerce_settings_gpf', [ $this, 'config_page' ] );
		add_action( 'woocommerce_update_options_gpf', [ $this, 'save_settings' ] );
		add_action( 'woocommerce_settings_save_general', [ $this, 'save_general_settings' ] );
	}

	/**
	 * Handle ajax callbacks for Google and bing category lookups
	 * Set up localisation
	 *
	 * @access public
	 */
	public function init(): void {
		// Load the settings from the configuration object.
		$this->settings = $this->configuration->get_settings();
		// Add a Settings link to the plugin page.
		$plugin_file = basename( dirname( __DIR__, 2 ) ) . '/woocommerce-gpf.php';
		add_filter( 'plugin_action_links_' . $plugin_file, [ $this, 'add_settings_link' ], 11 );

		// Handle ajax requests for the google taxonomy search
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['woocommerce_gpf_search'] ) ) {
			$this->ajax_handler( sanitize_text_field( $_GET['query'] ) );
			exit();
		}

		// Handle ajax requests for the bing taxonomy search
		if ( isset( $_GET['woocommerce_gpf_bing_search'] ) ) {
			$this->bing_ajax_handler( sanitize_text_field( $_GET['query'] ) );
			exit();
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Read in the field data.
		$this->product_fields = apply_filters( 'woocommerce_gpf_product_fields', $this->configuration->product_fields );

		// Sort the product fields by ui_group, then name.
		$this->grouped_product_fields = $this->generate_grouped_product_fields();

		// Set up i18n for the admin screens.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'woocommerce_gpf' );
		load_textdomain( 'woocommerce_gpf', WP_LANG_DIR . '/woocommerce-google-product-feed/woocommerce_gpf-' . $locale . '.mo' );
		load_plugin_textdomain( 'woocommerce_gpf', false, $this->base_dir . '/languages/' );
	}

	/**
	 * Generate an array grouped by ui_group, and ordered by field "desc".
	 *
	 * @return array
	 */
	private function generate_grouped_product_fields(): array {
		$results = [];
		// Create an array grouped by ui_group.
		foreach ( $this->product_fields as $field_name => $field ) {
			$field_ui_group = $field['ui_group'] ?? 'advanced';
			if ( ! isset( $results[ $field_ui_group ] ) ) {
				$results[ $field_ui_group ] = [];
			}
			$results[ $field_ui_group ][ $field_name ] = $field['desc'];
		}
		// Order the sub-arrays by the field name.
		foreach ( $results as $group_key => $fields ) {
			asort( $fields );
			$results[ $group_key ] = $fields;
		}

		return $results;
	}

	/**
	 * Add a "Settings" link next to the plugin on the Plugins page.
	 *
	 * @param array $links The existing plugin links.
	 *
	 * @return  array          The revised list of plugin links.
	 * @throws Exception
	 */
	public function add_settings_link( array $links ): array {
		$settings_url = add_query_arg(
			[
				'page' => 'wc-settings',
				'tab'  => 'gpf',
			],
			admin_url( 'admin.php' )
		);
		array_unshift(
			$links,
			sprintf(
				'<a href="https://woocommerce.com/products/google-product-feed/#comments">%s</a>',
				__( 'Review', 'woocommerce_gpf' )
			)
		);
		array_unshift(
			$links,
			sprintf(
				'<a href="https://woocommerce.com/feature-requests/google-product-feed/">%s</a>',
				__( 'Feature requests', 'woocommerce_gpf' )
			)
		);
		array_unshift(
			$links,
			sprintf(
				'<a href="https://woocommerce.com/document/google-product-feed-troubleshooting/">%s</a>',
				__( 'Troubleshooting', 'woocommerce_gpf' )
			)
		);
		array_unshift(
			$links,
			sprintf(
				'<a href="https://woocommerce.com/document/google-product-feed-setting-up-your-feed-google-merchant-centre/">%s</a>',
				__( 'Docs', 'woocommerce_gpf' )
			)
		);
		array_unshift(
			$links,
			sprintf(
				'<a href="%s">%s</a>',
				esc_attr( $settings_url ),
				__( 'Settings', 'woocommerce_gpf' )
			)
		);

		return $links;
	}

	/**
	 * Extend Product Edit Page
	 *
	 * @access public
	 */
	public function admin_init(): void {
		add_action( 'save_post_product', [ $this, 'save_product' ] );
		if ( isset( $this->settings['product_fields'] ) && count( $this->settings['product_fields'] ) ) {
			add_meta_box(
				'woocommerce-gpf-product-fields',
				__( 'Product Feed Information', 'woocommerce_gpf' ),
				[ $this, 'product_meta_box' ],
				'product',
				'advanced',
				'high'
			);
		}
		if ( isset( $_GET['gpf_action'] ) && 'rebuild_cache' === $_GET['gpf_action'] ) {
			if ( wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'gpf_rebuild_cache' ) ) {
				$this->cache->flush_all();
			}
			wp_safe_redirect(
				add_query_arg(
					[
						'page' => 'wc-settings',
						'tab'  => 'gpf',
					],
					admin_url( 'admin.php' )
				)
			);
			exit;
		}
		if ( isset( $_GET['gpf_action'] ) && 'refresh_custom_fields' === $_GET['gpf_action'] ) {
			if ( wp_verify_nonce( sanitize_text_field( $_GET['_wpnonce'] ), 'gpf_refresh_custom_fields' ) ) {
				delete_transient( 'woocommerce_gpf_meta_prepopulate_options' );
				wp_safe_redirect(
					add_query_arg(
						[
							'page' => 'wc-settings',
							'tab'  => 'gpf',
						],
						admin_url( 'admin.php' )
					)
				);
				exit;
			}
			wp_die( 'Invalid request' );
		}
	}

	/**
	 * Handle ajax requests for the google taxonomy search. Returns a JSON encoded list of matches
	 * and terminates execution.
	 *
	 * @access public
	 *
	 * @param string $query The user input to search for
	 *
	 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
	 */
	private function ajax_handler( $query ): void {

		global $wpdb, $table_prefix;

		$this->maybe_refresh_google_taxonomies();
		$locales = $this->configuration->get_google_taxonomy_locales();
		$sql     = "SELECT taxonomy_term
                          FROM {$table_prefix}woocommerce_gpf_google_taxonomy
                         WHERE search_term LIKE %s";

		$search_term = '%' . strtolower( $query ) . '%';
		if ( count( $locales ) > 1 ) {
			$sql .= ' AND locale in (' . implode( ',', array_fill( 0, count( $locales ), '%s' ) ) . ')';

			$query_arguments = $locales;
			array_unshift( $query_arguments, $search_term );

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared_query = $wpdb->prepare( $sql, $query_arguments );
		} else {
			$sql .= ' AND locale = %s';

			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$prepared_query = $wpdb->prepare( $sql, [ $search_term, $locales[0] ?? '' ] );
		}
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$results     = $wpdb->get_results( $prepared_query );
		$suggestions = [];
		foreach ( $results as $match ) {
			$suggestions[] = $match->taxonomy_term;
		}
		$results = [
			'query'       => $query,
			'suggestions' => $suggestions,
			'data'        => $suggestions,
		];
		echo wp_json_encode( $results );
		exit();
	}

	/**
	 * Handle ajax requests for the Bing taxonomy search. Outputs a JSON encoded list of matches
	 * and terminates execution.
	 *
	 * @access public
	 *
	 * @param string $query The user input to search for
	 */
	private function bing_ajax_handler( string $query ): void {
		$taxonomy = [
			'Arts & Crafts',
			'Baby & Nursery',
			'Beauty & Fragrance',
			'Books & Magazines',
			'Cameras & Optics',
			'Car & Garage',
			'Clothing & Shoes',
			'Collectibles & Memorabilia',
			'Computing',
			'Electronics',
			'Flowers',
			'Gourmet Food & Chocolate',
			'Health & Wellness',
			'Home Furnishings',
			'Jewelry & Watches',
			'Kitchen & Housewares',
			'Lawn & Garden',
			'Miscellaneous',
			'Movies',
			'Music',
			'Musical Instruments',
			'Office Products',
			'Pet Supplies',
			'Software',
			'Sports & Outdoors',
			'Tools & Hardware',
			'Toys',
			'Travel',
			'Vehicles',
			'Video Games',
		];

		$suggestions = [];
		foreach ( $taxonomy as $b_cat ) {
			if ( stripos( $b_cat, $query ) !== false ) {
				$suggestions[] = $b_cat;
			}
		}
		$results = [
			'query'       => $query,
			'suggestions' => $suggestions,
			'data'        => $suggestions,
		];
		echo wp_json_encode( $results );
		exit();
	}

	/*
	 * Enqueue CSS needed for product pages
	 *
	 * @access public
	 */
	public function enqueue_styles(): void {
		wp_enqueue_style(
			'woocommerce_gpf_admin',
			plugins_url( basename( $this->base_dir ) . '/css/woocommerce-gpf.css' ),
			[],
			WOOCOMMERCE_GPF_VERSION
		);
		wp_enqueue_style(
			'wooautocomplete',
			plugins_url( basename( $this->base_dir ) . '/js/jquery.autocomplete.css' ),
			[],
			WOOCOMMERCE_GPF_VERSION
		);
	}

	/**
	 * Enqueue javascript for product_type / google_product_category selector
	 *
	 * @access public
	 */
	public function enqueue_scripts(): void {
		wp_enqueue_script(
			'wooautocomplete',
			plugins_url( basename( $this->base_dir ) ) . '/js/jquery.autocomplete.js',
			[ 'jquery', 'jquery-ui-core' ],
			WOOCOMMERCE_GPF_VERSION,
			true
		);
		wp_enqueue_script(
			'woocommerce_gpf',
			plugins_url( basename( $this->base_dir ) ) . '/js/woocommerce-gpf.js',
			[ 'jquery', 'jquery-ui-datepicker' ],
			WOOCOMMERCE_GPF_VERSION,
			true
		);
	}

	public function category_add_meta_box( string $taxonomy ): void {
		$this->category_meta_box( $taxonomy, null );
	}

	public function category_edit_meta_box( WP_Term $term, string $taxonomy ): void {
		$this->category_meta_box( $taxonomy, $term );
	}

	/**
	 * Render the form to allow users to set defaults per-category
	 *
	 * @access public
	 *
	 * @param string $taxonomy
	 * @param ?WP_Term $term
	 *
	 * @throws Exception
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function category_meta_box( string $taxonomy, ?WP_Term $term ): void {
		if ( $term ) {
			$current_data = get_term_meta( $term->term_id, '_woocommerce_gpf_data', true );
			$this->template_loader->output_template_with_variables(
				'woo-gpf',
				'category-edit-intro',
				[ 'loop_idx' => '' ]
			);
		} else {
			$current_data = [];
			$this->template_loader->output_template_with_variables(
				'woo-gpf',
				'categories-edit-intro',
				[ 'loop_idx' => '' ]
			);
		}

		foreach ( $this->grouped_product_fields as $group => $field_keys ) {

			if ( empty( $field_keys ) ) {
				continue;
			}
			if ( ! $term ) {
				$group_header = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'category-row-group',
					[ 'group_name' => $this->configuration->get_ui_group_name( $group ) ]
				);
			} else {
				$group_header = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'categories-row-group',
					[ 'group_name' => $this->configuration->get_ui_group_name( $group ) ]
				);
			}

			$group_content = '';
			foreach ( array_keys( $field_keys ) as $key ) {
				$fieldinfo = $this->product_fields[ $key ];

				// Skip if not enabled & not mandatory.
				if ( ! isset( $this->settings['product_fields'][ $key ] ) &&
					( ! isset( $this->product_fields[ $key ]['mandatory'] ) || ! $this->product_fields[ $key ]['mandatory'] )
				) {
					continue;
				}
				// Skip if not to be shown on product pages.
				if ( isset( $this->product_fields[ $key ]['skip_on_category_pages'] ) &&
					$this->product_fields[ $key ]['skip_on_category_pages']
				) {
					continue;
				}

				$header_vars = [];
				$def_vars    = [];
				$row_vars    = [];
				$variables   = [];

				$header_vars['row_title'] = esc_html( $fieldinfo['desc'] );
				$header_vars['key']       = esc_html( $key );

				$header_vars['default_text'] = '';
				$placeholder                 = '';
				if ( isset( $fieldinfo['can_default'] ) && ! empty( $this->settings['product_defaults'][ $key ] ) ) {
					$default_value = $this->settings['product_defaults'][ $key ];
					if ( is_array( $default_value ) ) {
						$default_value = implode( ',', $default_value );
					}
					$header_vars['default_text'] .= '<span class="woocommerce_gpf_default_label">(' .
													__( 'Default: ', 'woocommerce_gpf' ) .
													esc_html( $default_value ) .
													')</span>';
					$placeholder                  = __( 'Use default', 'woo_gpf' );
				}
				$row_vars['header_content'] = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'meta-field-row-header',
					$header_vars
				);

				$current_value            = isset( $current_data[ $key ] ) ? $current_data[ $key ] : '';
				$def_vars['defaultinput'] = $this->render_field_default_input( $key, 'category', $current_value, $placeholder, null );
				$def_vars['key']          = $key;
				$variables['defaults']    = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'meta-field-row-defaults',
					$def_vars
				);
				$row_vars['data_content'] = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'meta-field-row-data',
					$variables
				);
				if ( ! $term ) {
					$group_content .= $this->template_loader->get_template_with_variables(
						'woo-gpf',
						'category-field-row',
						$row_vars
					);
				} else {
					$group_content .= $this->template_loader->get_template_with_variables(
						'woo-gpf',
						'categories-field-row',
						$row_vars
					);
				}
			}
			if ( ! empty( $group_content ) ) {
				echo $group_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $group_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Store the per-category defaults
	 *
	 * @access public
	 *
	 * @param $term_id
	 */
	public function save_category( int $term_id ): void {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST['_woocommerce_gpf_data'] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $_POST['_woocommerce_gpf_data'] as $key => $value ) {
				$_POST['_woocommerce_gpf_data'][ $key ] = stripslashes_deep( $value );
			}
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			update_term_meta( $term_id, '_woocommerce_gpf_data', $_POST['_woocommerce_gpf_data'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * @param $loop_idx
	 * @param $variation_data
	 * @param $variation
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	public function variation_input_fields( $loop_idx, $variation_data, $variation ): void {

		echo '<div class="wc_gpf_metabox closed">';
		echo '<h2><strong>';
		esc_html_e( 'Product Feed Information', 'woocommerce_gpf' );
		echo '</strong><div class="handlediv" aria-label="Click to toggle"></div>';
		echo '</h2>';
		echo '<div class="wc_gpf_metabox_content" style="display:none;">';
		echo '<p>';
		esc_html_e( 'Set values here if you want to override the information for this specific variation. If information should apply to all variations, then set it against the main product.', 'woocommerce_gpf' );
		echo '</p>';
		$current_data     = get_post_meta( $variation->ID, '_woocommerce_gpf_data', true );
		$product_defaults = $this->configuration->get_defaults_for_product( $variation->ID, 'all' );

		$this->render_exclude_product(
			'exclude_product',
			'variation',
			! empty( $current_data['exclude_product'] ) ? true : false,
			null,
			$loop_idx
		);
		$style = ! empty( $current_data['exclude_product'] ) ?
			'style="display: none;"' :
			'';

		$this->template_loader->output_template_with_variables(
			'woo-gpf',
			'product-meta-edit-intro',
			[
				'loop_idx' => $loop_idx,
				'style'    => $style,
			]
		);

		foreach ( $this->grouped_product_fields as $group => $field_keys ) {

			if ( empty( $field_keys ) ) {
				continue;
			}

			$group_header = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'product-meta-field-group',
				[ 'group_name' => $this->configuration->get_ui_group_name( $group ) ]
			);

			$group_content = '';
			foreach ( array_keys( $field_keys ) as $key ) {

				if ( ! isset( $this->settings['product_fields'][ $key ] ) || 'description' === $key ) {
					continue;
				}
				$fieldinfo                      = $this->product_fields[ $key ];
				$variables                      = $this->default_field_variables( $key, $loop_idx );
				$variables['field_description'] = esc_html( $fieldinfo['desc'] );
				$variables['field_defaults']    = '';
				$placeholder                    = '';
				if ( isset( $fieldinfo['can_prepopulate'] ) && ! empty( $this->settings['product_prepopulate'][ $key ] ) ) {
					$prepopulate_vars             = [];
					$prepopulate_vars['label']    = $this->get_prepopulate_label( $this->settings['product_prepopulate'][ $key ] );
					$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
						'woo-gpf',
						'product-meta-prepopulate-text',
						$prepopulate_vars
					);
				}
				if ( isset( $fieldinfo['can_default'] ) && ! empty( $product_defaults[ $key ] ) ) {
					$default_value = $product_defaults[ $key ];
					if ( is_array( $default_value ) ) {
						$default_value = implode( ',', $default_value );
					}
					$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
						'woo-gpf',
						'variation-meta-default-text',
						[
							'default' => sprintf(
								'Defaults to value from main product, or &quot;%s&quot;.',
								esc_html( $default_value )
							),
						]
					);
					$placeholder                  = __( 'Use default', 'woo_gpf' );
				}
				if ( ! isset( $fieldinfo['callback'] ) || ! is_callable( [ &$this, $fieldinfo['callback'] ] ) ) {
					$current_value            = ! empty( $current_data[ $key ] ) ? $current_data[ $key ] : '';
					$variables['field_input'] = $this->render_field_default_input(
						$key,
						'variation',
						$current_value,
						$placeholder,
						$loop_idx
					);
				} elseif ( isset( $current_data[ $key ] ) ) {
					$variables['field_input'] = call_user_func(
						[ $this, $fieldinfo['callback'] ],
						$key,
						'variation',
						$current_data[ $key ],
						$placeholder,
						$loop_idx
					);
				} else {
					$variables['field_input'] = call_user_func(
						[ $this, $fieldinfo['callback'] ],
						$key,
						'variation',
						null,
						$placeholder,
						$loop_idx
					);
				}
				$group_content .= $this->template_loader->get_template_with_variables( 'woo-gpf', 'product-meta-field-row', $variables );
			}
			if ( ! empty( $group_content ) ) {
				echo $group_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $group_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'product-meta-edit-footer' );
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Meta box on product pages for setting per-product information
	 *
	 * @access public
	 */
	public function product_meta_box( WP_Post $post ): void {
		$current_data     = get_post_meta( $post->ID, '_woocommerce_gpf_data', true );
		$product_defaults = $this->configuration->get_defaults_for_product( $post->ID, 'all' );

		$this->render_exclude_product(
			'exclude_product',
			'product',
			! empty( $current_data['exclude_product'] )
		);

		$this->feed_image_manager->render_summary( $post );

		$this->template_loader->output_template_with_variables(
			'woo-gpf',
			'product-meta-edit-intro',
			[
				'loop_idx' => '',
				'style'    => '',
			]
		);

		foreach ( $this->grouped_product_fields as $group => $field_keys ) {

			if ( empty( $field_keys ) ) {
				continue;
			}

			$group_header = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'product-meta-field-group',
				[ 'group_name' => $this->configuration->get_ui_group_name( $group ) ]
			);

			$group_content = '';
			foreach ( array_keys( $field_keys ) as $key ) {
				$fieldinfo = $this->product_fields[ $key ];

				// Skip if not enabled & not mandatory.
				if ( ! isset( $this->settings['product_fields'][ $key ] ) &&
					( ! isset( $this->product_fields[ $key ]['mandatory'] ) || ! $this->product_fields[ $key ]['mandatory'] )
				) {
					continue;
				}
				// Skip if not to be shown on product pages.
				if ( isset( $this->product_fields[ $key ]['skip_on_product_pages'] ) &&
					$this->product_fields[ $key ]['skip_on_product_pages']
				) {
					continue;
				}

				$variables                      = $this->default_field_variables( $key );
				$variables['field_description'] = esc_html( $fieldinfo['desc'] );
				$variables['field_defaults']    = '';
				$placeholder                    = '';
				if ( isset( $fieldinfo['can_prepopulate'] ) && ! empty( $this->settings['product_prepopulate'][ $key ] ) ) {
					$prepopulate_vars          = [];
					$prepopulate_vars['label'] = $this->get_prepopulate_label( $this->settings['product_prepopulate'][ $key ] );
					if ( ! empty( $prepopulate_vars['label'] ) ) {
						$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
							'woo-gpf',
							'product-meta-prepopulate-text',
							$prepopulate_vars
						);
					}
				}
				if ( isset( $fieldinfo['can_default'] ) && ! empty( $product_defaults[ $key ] ) ) {
					$default_value = $product_defaults[ $key ];
					if ( is_array( $default_value ) ) {
						$default_value = implode( ',', $default_value );
					}
					$variables['field_defaults'] .= $this->template_loader->get_template_with_variables(
						'woo-gpf',
						'product-meta-default-text',
						[
							'default' => '(' . __( 'Default: ', 'woocommerce_gpf' ) . esc_html( $default_value ) . ')',
						]
					);
					$placeholder                  = __( 'Use default', 'woo_gpf' );
				}
				if ( ! isset( $fieldinfo['callback'] ) || ! is_callable( [ &$this, $fieldinfo['callback'] ] ) ) {
					$current_value            = $current_data[ $key ] ?? '';
					$variables['field_input'] = $this->render_field_default_input(
						$key,
						'product',
						$current_value,
						$placeholder,
						null
					);
				} else {
					$current_value            = $current_data[ $key ] ?? null;
					$variables['field_input'] = call_user_func(
						[ $this, $fieldinfo['callback'] ],
						$key,
						'product',
						$current_value,
						$placeholder,
						null
					);
				}
				$group_content .= $this->template_loader->get_template_with_variables( 'woo-gpf', 'product-meta-field-row', $variables );
			}
			if ( ! empty( $group_content ) ) {
				echo $group_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $group_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'product-meta-edit-footer' );
	}

	/**
	 * Store the per-product meta information.
	 *
	 * @access public
	 *
	 * @param mixed $product_id
	 *
	 * @return void
	 */
	public function save_product( $product_id ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( empty( $_POST['_woocommerce_gpf_data'] ) ) {
			return;
		}
		$current_data = get_post_meta( $product_id, '_woocommerce_gpf_data', true );
		if ( ! $current_data ) {
			$current_data = [];
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$post_data = $_POST['_woocommerce_gpf_data'];
		// Remove entries that are blanked out
		foreach ( $post_data as $key => $value ) {
			if ( is_numeric( $key ) ) {
				// This is the variation data, ignore it.
				unset( $post_data[ $key ] );
				continue;
			}
			if ( empty( $value ) && '0' !== $value ) {
				unset( $post_data[ $key ] );
				if ( isset( $current_data[ $key ] ) ) {
					unset( $current_data[ $key ] );
				}
			} elseif ( is_array( $value ) ) {
				$post_data[ $key ] = stripslashes_deep( $value );
			} else {
				$post_data[ $key ] = stripslashes( $value );
			}
		}
		// Including missing checkboxes
		if ( ! isset( $post_data['exclude_product'] ) ) {
			unset( $current_data['exclude_product'] );
		}
		if ( ! isset( $post_data['is_bundle'] ) ) {
			unset( $current_data['is_bundle'] );
		}
		$current_data = array_merge( $current_data, $post_data );
		// Fix legacy data which may contain variation data.
		foreach ( array_keys( $current_data ) as $key ) {
			if ( is_numeric( $key ) ) {
				unset( $current_data[ $key ] );
			}
		}
		update_post_meta( $product_id, '_woocommerce_gpf_data', $current_data );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Store GPF data set specifically against the variation.
	 *
	 * @return void
	 */
	public function save_variation( $product_id, $idx ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( empty( $_POST['_woocommerce_gpf_data'][ $idx ] ) ) {
			return;
		}
		$current_data = get_post_meta( $product_id, '_woocommerce_gpf_data', true );
		if ( ! $current_data ) {
			$current_data = [];
		}
		// Remove entries that are blanked out
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		foreach ( $_POST['_woocommerce_gpf_data'][ $idx ] as $key => $value ) {
			if ( empty( $value ) ) {
				unset( $_POST['_woocommerce_gpf_data'][ $idx ][ $key ] );
				if ( isset( $current_data[ $key ] ) ) {
					unset( $current_data[ $key ] );
				}
			} else {
				$_POST['_woocommerce_gpf_data'][ $idx ][ $key ] = stripslashes_deep( $value );
			}
		}
		// Including missing checkboxes
		if ( ! isset( $_POST['_woocommerce_gpf_data'][ $idx ]['exclude_product'] ) ) {
			unset( $current_data['exclude_product'] );
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$current_data = array_merge( $current_data, $_POST['_woocommerce_gpf_data'][ $idx ] );
		update_post_meta( $product_id, '_woocommerce_gpf_data', $current_data );
		// phpcs:enable WordPress.Security.NonceVerification.Missing
	}

	/**
	 * Produce a default variables array for passing to a field's default template.
	 *
	 * @param string $key The key being processed.
	 * @param int|null $loop_idx
	 *
	 * @return array        The default variables array.
	 *
	 * @throws Exception
	 */
	private function default_field_variables( $key, ?int $loop_idx = null ) {
		$variables            = [];
		$variables['raw_key'] = esc_attr( $key );
		if ( is_null( $loop_idx ) ) {
			$variables['key'] = esc_attr( $key );
		} else {
			$variables['key'] = $loop_idx . '][' . esc_attr( $key );
		}
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['post'] ) ||
			isset( $_REQUEST['taxonomy'] ) ||
			isset( $_REQUEST['post_type'] ) ||
			! is_null( $loop_idx ) ) {
			$variables['emptytext'] = __( 'Use default', 'woocommerce_gpf' );
		} else {
			$variables['emptytext'] = __( 'No default', 'woocommerce_gpf' );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return $variables;
	}

	/**
	 * Loop through the available choices and set a variable for each choice indicating if it is
	 * selected or not.
	 *
	 * @param array $choices Array of choice values.
	 * @param string $current_data The current selected value.
	 * @param array $variables The template variables array to add to.
	 *
	 * @return array                The modified template variables array.
	 */
	private function default_selected_choices( $choices, $current_data, $variables ) {
		foreach ( $choices as $choice ) {
			if ( $choice === $current_data ) {
				$variables[ $choice . '-selected' ] = ' selected';
			} else {
				$variables[ $choice . '-selected' ] = '';
			}
		}

		return $variables;
	}

	/**
	 * Render the options for the exclude product checkbox.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed.
	 * @param bool|null $current_data The current value of this key.
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 *
	 * @throws Exception
	 *
	 * @psalm-param 'product'|'variation' $context
	 */
	private function render_exclude_product( $key, string $context, $current_data = null, $placeholder = null, $loop_idx = null ): void {
		$variables            = $this->default_field_variables( $key );
		$variables['checked'] = '';
		if ( $current_data ) {
			$variables['checked'] = ' checked="checked"';
		}
		$variables['hide_product_text'] = __( 'Hide this product from the feed', 'woocommerce_gpf' );
		if ( ! is_null( $loop_idx ) ) {
			$variables['loop_idx'] = '[' . $loop_idx . ']';
			$variables['loop_num'] = $loop_idx;
		} else {
			$variables['loop_idx'] = '';
			$variables['loop_num'] = '';
		}
		$this->template_loader->output_template_with_variables(
			'woo-gpf',
			'meta-exclude-product',
			$variables
		);
	}

	/**
	 * Render the options for the identifier_exists attribute
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_i_exists( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		$variables = $this->default_selected_choices(
			[ 'included', 'not-included' ],
			$current_data,
			$variables
		);

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-iexists',
			$variables
		);
	}

	/**
	 * Render large text box for title field.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_title( $key, $context, $current_data = null, $placeholder = '', $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}
		$variables['current_data'] = esc_attr( $current_data );

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-title',
			$variables
		);
	}

	/**
	 *  NULL render since we can't (yet) override description.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	private function render_description( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		return '';
	}
	// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed

	/**
	 * Let people choose whether a product is_bundle.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_is_bundle( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables          = $this->default_field_variables( $key, $loop_idx );
		$variables['value'] = checked( 'on', $current_data, false );

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-is-bundle',
			$variables
		);
	}


	/**
	 * Let people choose an availability date for a product.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_availability_date( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables          = $this->default_field_variables( $key, $loop_idx );
		$variables['value'] = esc_attr( $current_data );
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-availability-date',
			$variables
		);
	}

	/**
	 * Used to render the drop-down of valid size types
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_generic_select( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables                = $this->default_field_variables( $key, $loop_idx );
		$variables['options']     = $this->build_select_options(
			$this->product_fields[ $key ]['options_callback'](),
			$current_data
		);
		$variables['emptyoption'] = '';
		$optional                 = ! isset( $this->product_fields[ $key ]['mandatory'] ) ||
									! $this->product_fields[ $key ]['mandatory'];
		// Mandatory fields are only mandatory on the main config screen. If this is some other context (category,
		// product, variation), or the field is optional, we need the "use default"/"no default" option.
		if ( 'config' !== $context || $optional ) {
			$variables['emptyoption'] = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-generic-select-empty-option',
				$variables
			);
		}

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-generic-select',
			$variables
		);
	}

	/**
	 * Used to render the drop-down of valid size types
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_generic_multi_select( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		if ( ! is_array( $current_data ) ) {
			$current_data = [ $current_data ];
		}
		$variables                = $this->default_field_variables( $key, $loop_idx );
		$variables['options']     = $this->build_multi_select_options(
			$this->product_fields[ $key ]['options_callback'](),
			$current_data
		);
		$variables['emptyoption'] = '';
		$optional                 = ! isset( $this->product_fields[ $key ]['mandatory'] ) ||
									! $this->product_fields[ $key ]['mandatory'];
		// Mandatory fields are only mandatory on the main config screen. If this is some other context (category,
		// product, variation), or the field is optional, we need the "use default"/"no default" option.
		if ( 'config' !== $context || $optional ) {
			$variables['emptyoption'] = $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-generic-select-empty-option',
				$variables
			);
		}

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-generic-multi-select',
			$variables
		);
	}

	/**
	 * Used to render the fields for 'certification'.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_certification( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables                                    = $this->default_field_variables( $key, $loop_idx );
		$variables['certification_code']              = $current_data[0]['certification_code'] ?? '';
		$variables['certification_authority_options'] = [ 'EC' => __( 'European commission (EC)', 'woocommerce_gpf' ) ];
		$variables['certification_name_options']      = [ 'EPREL' => __( 'European Registry for Energy Labeling (EPREL)', 'woocommerce_gpf' ) ];

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-certification',
			$variables
		);
	}

	/**
	 * Used to render the fields for 'installment'.
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 * @throws Exception
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_installment( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables                       = $this->default_field_variables( $key, $loop_idx );
		$variables['defaultvaluemonths'] = ! empty( $current_data[0]['months'] ) ? $current_data[0]['months'] : '';
		$variables['defaultvalueamount'] = ! empty( $current_data[0]['amount'] ) ? $current_data[0]['amount'] : '';

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-installment',
			$variables
		);
	}

	/**
	 * Used to render the fields for consumer_notice.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_consumer_notice( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );

		$output       = '';
		$subidx_limit = apply_filters( 'woocommerce_gpf_number_of_consumer_notice_fields', 2 );
		for ( $subidx = 0; $subidx < $subidx_limit; $subidx++ ) {
			$variables['subidx']                    = $subidx;
			$variables['notice_message']            = '';
			$variables['US_CA_PROP_65_selected']    = '';
			$variables['safety_warning_selected']   = '';
			$variables['legal_disclaimer_selected'] = '';
			if ( isset( $current_data[ $subidx ] ) ) {
				$variables['notice_message']                                        = $current_data[ $subidx ]['notice_message'];
				$variables[ $current_data[ $subidx ]['notice_type'] . '_selected' ] = 'selected';
			}
			$output .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-consumer-notice',
				$variables
			);
		}

		return $output;
	}

	/**
	 * Used to render the fields for product highlight.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_product_highlight( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );

		$output       = '';
		$subidx_limit = apply_filters( 'woocommerce_gpf_number_of_product_highlight_fields', 5 );
		for ( $subidx = 0; $subidx < $subidx_limit; $subidx++ ) {
			$variables['subidx']    = $subidx;
			$variables['highlight'] = '';
			if ( isset( $current_data[ $subidx ] ) ) {
				$variables['highlight'] = is_array( $current_data[ $subidx ] ) ?
					$current_data[ $subidx ]['highlight'] :
					$current_data[ $subidx ];
			}
			$output .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-product-highlight',
				$variables
			);
		}

		return $output;
	}

	/**
	 * Used to render the fields for product detail.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_product_detail( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );

		$output       = '';
		$subidx_limit = apply_filters( 'woocommerce_gpf_number_of_product_detail_fields', 2 );
		for ( $subidx = 0; $subidx < $subidx_limit; $subidx++ ) {
			$variables['subidx']          = $subidx;
			$variables['section_name']    = '';
			$variables['attribute_name']  = '';
			$variables['attribute_value'] = '';
			if ( isset( $current_data[ $subidx ] ) ) {
				$variables['section_name']    = $current_data[ $subidx ]['section_name'];
				$variables['attribute_name']  = $current_data[ $subidx ]['attribute_name'];
				$variables['attribute_value'] = $current_data[ $subidx ]['attribute_value'];
			}
			$output .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-product-detail',
				$variables
			);
		}

		return $output;
	}

	/**
	 * Used to render the fields for consumer datasheet.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_consumer_datasheet( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );

		$output       = '';
		$subidx_limit = apply_filters( 'woocommerce_gpf_number_of_consumer_datasheet_fields', 2 );
		for ( $subidx = 0; $subidx < $subidx_limit; $subidx++ ) {
			$variables['subidx']          = $subidx;
			$variables['attribute_name']  = '';
			$variables['attribute_value'] = '';
			if ( isset( $current_data[ $subidx ] ) ) {
				$variables['attribute_name']  = $current_data[ $subidx ]['attribute_name'];
				$variables['attribute_value'] = $current_data[ $subidx ]['attribute_value'];
			}
			$output .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-consumer-datasheet',
				$variables
			);
		}

		return $output;
	}

	/**
	 * Used to render the fields for product fee.
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param array $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_product_fee( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );

		$output       = '';
		$subidx_limit = apply_filters( 'woocommerce_gpf_number_of_product_fee_fields', 1 );
		for ( $subidx = 0; $subidx < $subidx_limit; $subidx++ ) {
			$variables['subidx'] = $subidx;
			$variables['type']   = '';
			$variables['amount'] = '';
			if ( isset( $current_data[ $subidx ] ) ) {
				$variables['type']   = $current_data[ $subidx ]['type'];
				$variables['amount'] = $current_data[ $subidx ]['amount'];
			}
			$output .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-product-fee',
				$variables
			);
		}

		return $output;
	}

	/**
	 * Let people choose from the Google taxonomy for the product_type tag
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @throws Exception
	 */
	private function render_product_type( $key, $context, $current_data = null, $placeholder = '', $loop_idx = null ) {
		$variables = $this->default_field_variables( $key, $loop_idx );
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}
		$variables['current_data'] = esc_attr( $current_data );
		$variables['locale_list']  = implode(
			', ',
			array_map(
				[
					$this,
					'map_google_taxonomy_locale_names',
				],
				$this->configuration->get_google_taxonomy_locales()
			)
		);

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-product-type',
			$variables
		);
	}

	/**
	 * Promotion ID entry. Standard text field, but with additional explanatory text.
	 *
	 * @param string $key The key being processed
	 * @param string $context
	 * @param string|null $current_data The current value of this key
	 * @param string|null $placeholder
	 * @param int|null $loop_idx
	 *
	 * @return string
	 *
	 * @throws Exception
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_promotion_id(
		string $key,
		string $context,
		?string $current_data = null,
		?string $placeholder = '',
		?int $loop_idx = null
	): string {
		$variables                 = $this->default_field_variables( $key, $loop_idx );
		$variables['current_data'] = esc_attr( $current_data );
		$variables['extra_text']   = '';
		if ( in_array( $context, [ 'category', 'product', 'variation' ], true ) ) {
			if ( $this->feed_config_repository->has_active_feed_of_type( 'googlepromotions' ) ) {
				$variables['extra_text'] = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'admin-promotion-id-extra-text'
				);
			}
		}

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-promotion-id',
			$variables
		);
	}

	/**
	 * @param $locale
	 *
	 * @return mixed
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function map_google_taxonomy_locale_names( $locale ) {
		$map = [
			'ar-SA' => __( 'Arabic', 'woocommerce_gpf' ),
			'cs-CZ' => __( 'Czech', 'woocommerce_gpf' ),
			'da-DK' => __( 'Danish', 'woocommerce_gpf' ),
			'de-DE' => __( 'German', 'woocommerce_gpf' ),
			'en-AU' => __( 'English (Australian)', 'woocommerce_gpf' ),
			'en-GB' => __( 'English (British)', 'woocommerce_gpf' ),
			'en-US' => __( 'English (US)', 'woocommerce_gpf' ),
			'es-ES' => __( 'Spanish', 'woocommerce_gpf' ),
			'fi-FI' => __( 'Finnish', 'woocommerce_gpf' ),
			'fr-FR' => __( 'French', 'woocommerce_gpf' ),
			'it-IT' => __( 'Italian', 'woocommerce_gpf' ),
			'ja-JP' => __( 'Japanese', 'woocommerce_gpf' ),
			'nl-NL' => __( 'Dutch', 'woocommerce_gpf' ),
			'no-NO' => __( 'Norwegian', 'woocommerce_gpf' ),
			'pl-PL' => __( 'Polish', 'woocommerce_gpf' ),
			'pt-BR' => __( 'Portuguese', 'woocommerce_gpf' ),
			'ru-RU' => __( 'Russian', 'woocommerce_gpf' ),
			'sv-SE' => __( 'Swedish', 'woocommerce_gpf' ),
			'tr-TR' => __( 'Turkish', 'woocommerce_gpf' ),
			'vi-VN' => __( 'Vietnamese', 'woocommerce_gpf' ),
			'zh-CN' => __( 'Chinese', 'woocommerce_gpf' ),
		];

		return $map[ $locale ] ?? $locale;
	}

	/**
	 * Let people choose from the Bing taxonomy for the bing_category tag
	 *
	 * @access private
	 *
	 * @param string $key The key being processed
	 * @param string $current_data The current value of this key
	 *
	 * @return string
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function render_b_category( $key, $context, $current_data = null, $placeholder = null, $loop_idx = null ) {
		$variables                 = $this->default_field_variables( $key, $loop_idx );
		$variables['current_data'] = esc_attr( $current_data );
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}

		return $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'field-row-default-bing-category',
			$variables
		);
	}


	/**
	 * Add a tab to the WooCommerce settings pages
	 *
	 * @access public
	 *
	 * @param array $tabs The current list of settings tabs
	 *
	 * @return array       The tabs array with the new item added
	 *
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	public function add_woocommerce_settings_tab( $tabs ) {
		$tabs['gpf'] = __( 'Product Feeds', 'woocommerce_gpf' );

		return $tabs;
	}

	/**
	 * Generate the feed icons for the field on the main settings page.
	 *
	 * @param key-of<TArray> $key
	 */
	private function feed_images_for_field( $key ): string {
		$results        = '';
		$all_feed_types = $this->configuration->get_feed_types();
		foreach ( $this->product_fields[ $key ]['feed_types'] as $feed_type ) {
			$image_url = isset( $all_feed_types[ $feed_type ]['icon'] ) ?
				$all_feed_types[ $feed_type ]['icon'] :
				null;
			if ( is_null( $image_url ) ) {
				continue;
			}
			$results .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'admin-feed-image',
				[
					'image_url' => $image_url,
					'alt_text'  => esc_attr( $all_feed_types[ $feed_type ]['name'] ),
				]
			);
		}

		return $results;
	}

	/**
	 * Show the preopulate selector for a field.
	 *
	 * @param  string  $prepopulate_key  The key of the current field.
	 *
	 * @return string      The markup for the selector.
	 * @throws Exception
	 */
	private function prepopulate_selector_for_field( $prepopulate_key ) {
		if ( empty( $this->product_fields[ $prepopulate_key ]['can_prepopulate'] ) ) {
			return '';
		}
		$options        = $this->configuration->get_prepopulate_options( $prepopulate_key );
		$selected_value = ! empty( $this->settings['product_prepopulate'][ $prepopulate_key ] ) ?
			$this->settings['product_prepopulate'][ $prepopulate_key ] :
			'';

		$variables               = [];
		$variables['key']        = esc_attr( $prepopulate_key );
		$variables['intro_text'] = __( 'Use value from existing product field: ', 'woo_gpf' );

		// Create the list of options.
		$variables['options'] = '';
		if ( 'description' !== $prepopulate_key ) {
			$variables['options'] = '<option value="">' . __( 'No', 'woo_gpf' ) . '</option>';
		}
		foreach ( $options as $key => $value ) {
			if ( 0 === stripos( $key, 'disabled' ) ) {
				$disabled = ' disabled';
			} else {
				$disabled = '';
			}
			$variables['options'] .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-prepopulate-option',
				[
					'key'      => esc_attr( $key ),
					'value'    => esc_html( $value ),
					'disabled' => $disabled,
					'selected' => selected( $key, $selected_value, false ),
				]
			);
		}

		return $this->template_loader->get_template_with_variables( 'woo-gpf', 'field-row-prepopulate', $variables );
	}

	/**
	 * Get the label descriptor for a given field.
	 *
	 * @param string $field The field to find the label for.
	 *
	 * @return string          The field label.
	 */
	private function get_label_descriptor_for_field( $field ) {

		$prepopulate_options = $this->configuration->get_prepopulate_options();
		if ( ! empty( $prepopulate_options[ 'field:' . $field ] ) ) {
			return $prepopulate_options[ 'field:' . $field ];
		} else {
			return $field;
		}
	}

	/**
	 * Get the label descriptor for a given meta pre-population rule.
	 *
	 * @param string $meta The meta to find the label for.
	 *
	 * @return string          The field label.
	 */
	private function get_label_descriptor_for_meta( $meta ) {

		$prepopulate_options = $this->configuration->get_prepopulate_options();
		if ( ! empty( $prepopulate_options[ 'meta:' . $meta ] ) ) {
			return $prepopulate_options[ 'meta:' . $meta ];
		} else {
			return $meta;
		}
	}

	/**
	 * Get the label describing how a field is prepopulated.
	 *
	 * @param string $key The prepopulate value for the field
	 *
	 * @return string       The label text.
	 */
	private function get_prepopulate_label( $key ) {
		$descriptor           = '';
		list( $type, $value ) = explode( ':', $key );
		switch ( $type ) {
			case 'tax':
				$taxonomy = get_taxonomy( $value );
				if ( $taxonomy ) {
					// Translators: %s is the name of the taxonomy
					$descriptor = sprintf( __( '<em>%s</em> taxonomy', 'woo_gpf' ), $taxonomy->labels->singular_name );
				}
				break;
			case 'taxhierarchy':
				$taxonomy = get_taxonomy( $value );
				if ( $taxonomy ) {
					// Translators: %s is the name of the taxonomy
					$descriptor = sprintf( __( '<em>%s</em> taxonomy (full hierarchy)', 'woo_gpf' ), $taxonomy->labels->singular_name );
				}
				break;
			case 'field':
				$label = $this->get_label_descriptor_for_field( $value );
				// Translators: %s is the name of the field
				$descriptor = sprintf( __( '<em>%s</em> field', 'woo_gpf' ), $label );
				break;
			case 'meta':
				$label = $this->get_label_descriptor_for_meta( $value );
				// Translators: %1$s is the name of the meta field, %2$s is the raw meta key
				$descriptor = sprintf( __( '<em>%1$s</em> meta (%2$s)', 'woo_gpf' ), $label, $value );
				break;
			default:
				$descriptor = apply_filters( 'woocommerce_gpf_prepopulate_label', $descriptor, $key );
				break;
		}
		if ( empty( $descriptor ) ) {
			return '';
		}

		return sprintf(
		// Translators: %s is the description of where the data will be pulled from.
			__( 'Uses value from %s if set.', 'woo_gpf' ),
			$descriptor
		);
	}

	/**
	 * Show config page, and process form submission
	 *
	 * @access public
	 */
	public function config_page(): void {

		// Output the header.
		$variables = [];

		$feed_types                      = $this->configuration->get_feed_types();
		$settings_url                    = add_query_arg(
			[
				'page' => 'wc-settings',
				'tab'  => 'gpf',
			],
			admin_url( 'admin.php' )
		);
		$variables['cache_status']       = apply_filters( 'woocommerce_gpf_cache_status', '', $settings_url );
		$variables['refresh_fields_url'] = wp_nonce_url(
			add_query_arg(
				[
					'gpf_action' => 'refresh_custom_fields',
				],
				$settings_url
			),
			'gpf_refresh_custom_fields'
		);

		$feed_configs = $this->feed_config_repository->all();

		$variables['active_feeds'] = $this->template_loader->get_template_with_variables( 'woo-gpf', 'admin-feeds-intro' );
		foreach ( $feed_configs as $config ) {
			$feed_type      = $feed_types[ $config->type ];
			$feed_url       = get_home_url( null, '/woocommerce_gpf/' . $config->id );
			$feed_variables = [
				'html_name' => esc_html( $config->name ),
				'icon'      => $feed_type['icon'],
				'attr_url'  => esc_attr( $feed_url ),
				'html_url'  => esc_html( $feed_url ),
			];

			$variables['active_feeds'] .= $this->template_loader->get_template_with_variables( 'woo-gpf', 'admin-feeds-feed', $feed_variables );
		}
		$variables['active_feeds'] .= $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'admin-feeds-footer',
			[
				'manage_url' => esc_attr( admin_url( 'admin.php?page=woocommerce-gpf-manage-feeds' ) ),
			]
		);

		$this->template_loader->output_template_with_variables( 'woo-gpf', 'admin-intro', $variables );
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'admin-feed-fields-intro' );

		// Output the fields.
		foreach ( $this->grouped_product_fields as $group => $field_keys ) {

			$this->template_loader->output_template_with_variables(
				'woo-gpf',
				'admin-field-group-header',
				[ 'group_name' => $this->configuration->get_ui_group_name( $group ) ]
			);

			foreach ( array_keys( $field_keys ) as $key ) {

				$variables = [];
				$row_vars  = [];
				$def_vars  = [];
				$info      = $this->product_fields[ $key ] ?? [];

				// If the field is deprecated, and not currently enabled, skip it.
				if ( ! empty( $info['deprecated'] ) && ! isset( $this->settings['product_fields'][ $key ] ) ) {
					continue;
				}

				$variables['row_title']     = esc_html( $info['desc'] );
				$variables['feed_images']   = $this->feed_images_for_field( $key );
				$row_vars['header_content'] = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'field-row-header',
					$variables
				);

				$variables            = [];
				$variables['key']     = esc_attr( $key );
				$variables['checked'] = '';
				if ( isset( $this->settings['product_fields'][ $key ] ) ) {
					$variables['checked'] = 'checked="checked"';
				}

				$variables['full_desc'] = esc_html( $info['full_desc'] );

				if ( isset( $this->product_fields[ $key ]['can_default'] ) ) {
					$def_vars['defaultinput'] = __( 'Store default: <br>', 'woocommerce_gpf' ) .
												$this->render_field_default_input( $key, 'config' );
				} else {
					$def_vars['defaultinput'] = '';
				}
				$def_vars['prepopulates'] = $this->prepopulate_selector_for_field( $key );
				$def_vars['key']          = $key;
				$def_vars['displaynone']  = '';
				if ( ! isset( $this->settings['product_fields'][ $key ] ) ) {
					$def_vars['displaynone'] = ' style="display:none;"';
				}
				$variables['class_mandatory'] = '';
				if ( isset( $this->product_fields[ $key ]['mandatory'] ) && $this->product_fields[ $key ]['mandatory'] ) {
					$variables['checked']         = 'checked="checked"';
					$variables['class_mandatory'] = 'woocommerce_gpf_field_selector_mandatory"';
					$def_vars['displaynone']      = '';
				}

				$variables['defaults']    = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'field-row-defaults',
					$def_vars
				);
				$row_vars['data_content'] = $this->template_loader->get_template_with_variables(
					'woo-gpf',
					'field-row-data',
					$variables
				);
				$this->template_loader->output_template_with_variables(
					'woo-gpf',
					'config-field-row',
					$row_vars
				);
			}
		}
		$variables                                = [];
		$variables['include_variations_selected'] = checked(
			'on',
			isset( $this->settings['include_variations'] ) ? $this->settings['include_variations'] : '',
			false
		);
		$variables['include_variations']          = $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'admin-include-variations',
			$variables
		);
		$variables['send_item_group_id_selected'] = checked(
			'on',
			isset( $this->settings['send_item_group_id'] ) ? $this->settings['send_item_group_id'] : '',
			false
		);
		$variables['send_item_group_id']          = $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'admin-send-item-group-id',
			$variables
		);
		$variables['expanded_schema_selected']    = checked(
			'on',
			isset( $this->settings['expanded_schema'] ) ? $this->settings['expanded_schema'] : '',
			false
		);
		$variables['expanded_schema']             = $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'admin-expanded-schema',
			$variables
		);
		$variables['shop_code']                   = $this->template_loader->get_template_with_variables(
			'woo-gpf',
			'admin-shop-code',
			[
				'shop_code' => ! empty( $this->settings['shop_code'] ) ?
					esc_attr( $this->settings['shop_code'] ) :
					'',
			]
		);
		$this->template_loader->output_template_with_variables( 'woo-gpf', 'admin-footer', $variables );
	}

	/**
	 * Renders the output for the "default" box for a field.
	 *
	 * @param string $key The field being rendered.
	 * @param string $context The page being rendered: config, category, variation, or product
	 * @param string|false $current_data The current value. If not provided, the default will be used
	 *                              from the store wide settings.
	 * @param string $placeholder Placeholder text to use, leave blank for no placeholder.
	 * @param int $loop_idx The loop idx of the variation being output.
	 *
	 * @return string
	 */
	private function render_field_default_input( $key, $context, $current_data = false, $placeholder = '', $loop_idx = null ) {
		$variables = [];
		if ( null === $loop_idx ) {
			$variables['key'] = $key;
		} else {
			$variables['key'] = $loop_idx . '][' . $key;
		}
		if ( ! empty( $placeholder ) ) {
			$variables['placeholder'] = ' placeholder="' . esc_attr( $placeholder ) . '"';
		} else {
			$variables['placeholder'] = '';
		}
		if ( false === $current_data ) {
			$current_data = isset( $this->settings['product_defaults'][ $key ] ) ? $this->settings['product_defaults'][ $key ] : '';
		}
		if ( ! isset( $this->{'product_fields'}[ $key ]['callback'] ) ||
			! is_callable( [ $this, $this->{'product_fields'}[ $key ]['callback'] ] ) ) {
			$variables['defaultvalue'] = esc_attr( $current_data );

			return $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-generic',
				$variables
			);
		}

		return call_user_func(
			[
				$this,
				$this->{'product_fields'}[ $key ]['callback'],
			],
			$key,
			$context,
			$current_data,
			$placeholder,
			$loop_idx
		);
	}

	/**
	 * @return void
	 */
	public function save_general_settings() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$new_country     = sanitize_text_field( $_POST['woocommerce_default_country'] ?? '' );
		$current_country = WC()->countries->get_base_country();
		if ( ! empty( $new_country ) && $current_country === $new_country ) {
			// Country unchanged. Do nothing.
			return;
		}
		$pending = as_get_scheduled_actions(
			[
				'hook'     => 'woocommerce_product_feeds_maybe_refresh_google_taxonomies',
				'args'     => [],
				'status'   => [
					ActionScheduler_Store::STATUS_PENDING,
					ActionScheduler_Store::STATUS_RUNNING,
				],
				'per_page' => 1,
				'orderby'  => 'none',
			],
			'ids'
		);
		// Do not trigger if we already have a queued action.
		if ( ! empty( $pending ) ) {
			return;
		}
		as_schedule_single_action(
			time(),
			'woocommerce_product_feeds_maybe_refresh_google_taxonomies',
			[],
			'woocommerce-product-feeds'
		);
	}

	/**
	 * Save the settings from the config page
	 *
	 * @access public
	 *
	 * @return void
	 */
	public function save_settings() {

		// Check nonce
		if ( ! wp_verify_nonce( sanitize_text_field( $_REQUEST['_wpnonce'] ), 'woocommerce-settings' ) ) {
			die( esc_html( __( 'Action failed. Please refresh the page and retry.', 'woothemes' ) ) );
		}

		if ( ! $this->settings ) {
			$this->settings = [];
			add_option( 'woocommerce_gpf_config', $this->settings, '', true );
		}

		if ( ! empty( $_POST['_woocommerce_gpf_data'] ) ) {
			// We do these so we can re-use the same form field rendering code for the fields
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $_POST['_woocommerce_gpf_data'] as $key => $value ) {
				$_POST['_woocommerce_gpf_data'][ $key ] = stripslashes_deep( $value );
			}
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$_POST['woocommerce_gpf_config']['product_defaults'] = $_POST['_woocommerce_gpf_data'];
			unset( $_POST['_woocommerce_gpf_data'] );
		}

		if ( ! empty( $_POST['_woocommerce_gpf_prepopulate'] ) ) {
			// We do these so we can re-use the same form field rendering code for the fields
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			foreach ( $_POST['_woocommerce_gpf_prepopulate'] as $key => $value ) {
				$_POST['_woocommerce_gpf_prepopulate'][ $key ] = stripslashes( $value );
			}
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$_POST['woocommerce_gpf_config']['product_prepopulate'] = $_POST['_woocommerce_gpf_prepopulate'];
			unset( $_POST['_woocommerce_gpf_prepopulate'] );
		}
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$this->settings = $_POST['woocommerce_gpf_config'];
		update_option( 'woocommerce_gpf_config', $this->settings );
	}

	/**
	 * @param array $options
	 * @param string $current_data
	 *
	 * @return string
	 */
	private function build_select_options( $options, $current_data ) {
		$result = '';
		foreach ( $options as $value => $description ) {
			$result .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-generic-select-option',
				[
					'value'       => $value,
					'description' => $description,
					'selected'    => ( $current_data === $value ) ? 'selected' : '',
				]
			);
		}

		return $result;
	}

	/**
	 * @param array $options
	 * @param array $current_data
	 *
	 * @return string
	 */
	private function build_multi_select_options( $options, $current_data ) {
		$result = '';
		foreach ( $options as $value => $description ) {
			$result .= $this->template_loader->get_template_with_variables(
				'woo-gpf',
				'field-row-default-generic-select-option',
				[
					'value'       => $value,
					'description' => $description,
					'selected'    => in_array( $value, $current_data, true ) ? 'selected' : '',
				]
			);
		}

		return $result;
	}

	/**
	 * @return void
	 */
	private function maybe_refresh_google_taxonomies() {
		// AJAX auto-complete triggers checking at most once in a 24-hr period.
		$refresh_last_triggered = get_option( 'woocommerce_gpf_autocomplete_last_triggered_refresh', 0 );
		if ( $refresh_last_triggered > time() - 86400 ) {
			return;
		}

		// Do not need to trigger if there is already a queued action.
		$pending = as_get_scheduled_actions(
			[
				'hook'     => 'woocommerce_product_feeds_maybe_refresh_google_taxonomies',
				'args'     => [],
				'status'   => [ ActionScheduler_Store::STATUS_PENDING, ActionScheduler_Store::STATUS_RUNNING ],
				'per_page' => 1,
				'orderby'  => 'none',
			],
			'ids'
		);
		// Do not trigger if we already have a queued action.
		if ( ! empty( $pending ) ) {
			return;
		}

		update_option( 'woocommerce_gpf_autocomplete_last_triggered_refresh', time() );
		as_schedule_single_action(
			time(),
			'woocommerce_product_feeds_maybe_refresh_google_taxonomies',
			[],
			'woocommerce-product-feeds'
		);
	}
}
