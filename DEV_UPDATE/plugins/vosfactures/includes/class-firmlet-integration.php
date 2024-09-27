<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    vosfactures.fr
 *  @copyright 2020 vosfactures.fr
 *  @license   LICENSE.txt
*/

/**
 *
 * @package  Vosfactures_Integration
 * @category Integration
 */
if ( ! class_exists( 'Vosfactures_Integration' ) ) :
	class Vosfactures_Integration extends WC_Integration {
		private $account;

		/**
		 * Init and hook in the integration.
		 */
		public function __construct() {
			global $woocommerce;
			$this->id           = 'firmlet';
			$this->module       = firmlet_vosfactures();
			$this->method_title = __( 'VosFactures', 'firmlet' );
			$this->plugin_admin = $this->module->plugin_admin;
			$this->api          = new VosfacturesApi( $this->module->get_name(), $this->module->get_version() );
			// Load the settings.
			$this->init_form_fields();
			$this->init_settings();
			// Actions.
			add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );
			add_filter( 'woocommerce_settings_api_sanitized_fields_' . $this->id, array( $this, 'sanitize_settings' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_js') );
		}

		public function load_admin_js() {
			wp_enqueue_script(
				'wc-google-analytics-admin-enhanced-settings',
				plugins_url( '/public/js/admin-settings.js',
				dirname( __FILE__ ) ),
				array(),
				'1.0.0',
				true
			);
		}

		/**
		 * Initialize integration settings form fields.
		 */
		public function init_form_fields() {
			$this->form_fields = $this->settings_init();
		}

		public function settings_init() {
			if ( isset( $_POST['woocommerce_firmlet_api_token'] ) ) {
				$api_token = $_POST['woocommerce_firmlet_api_token'];
			} elseif ( is_array( get_option( 'woocommerce_firmlet_settings' ) ) ) {
				$api_token = get_option( 'woocommerce_firmlet_settings' )['api_token'];
			} else {
				$api_token = null;
			}
			/*
			 * Departments
			 */
			if ( isset( $_GET['tab'] ) ) {
				if ( $_GET['tab'] == 'integration' ) {
					$depts = $this->api->get_departments( $api_token );
					$this->account = $this->api->get_account( $api_token );
				}
			} else {
				$depts = array();
			}

			$departments = array( null => '---' );
			if ( isset( $depts ) ) {
				foreach ( $depts as $dep ) {
					$departments[ $dep->id ] = $dep->shortcut;
				}
			}

			if ( empty( $depts ) && isset( $api_token,  $_GET['tab'] ) && ! isset( $_POST['woocommerce_firmlet_api_token'] ) && $_GET['tab'] == 'integration' ) {
				$settings['errors'] = sprintf( esc_html__( 'Department not found - please fill up the data at %s account', 'firmlet' ), $this->module->display_name );
				WC_Admin_Settings::add_error( $settings['errors'] );
			}

			if ( isset( $_GET['tab'] ) ) {
				if ( $_GET['tab'] == 'integration' ) {
					$cats = $this->api->get_categories( $api_token );
				}
			} else {
				$cats = array();
			}

			$categories = array( null => '---' );
			if ( isset( $depts ) ) {
				foreach ( $cats as $cat ) {
					$categories[ $cat->id ] = $cat->name;
				}
			}

			$settings_fields = array(
				array(
					'type' => 'description',
				),
				array(
					'type'  => 'title',
					'title' => __( 'Connection settings', 'firmlet' ),
				),
				'api_token'  => array(
					'type'        => 'text',
					'title'       => __( 'API Token', 'firmlet' ),
					'desc_tip'    => true,
					'description' => __( 'API Token description', 'firmlet' ),
					'default'     => '',
				),
				'auto_issue' => array(
					'type'    => 'select',
					'title'   => sprintf( __( 'Automatically issue a document on %s', 'firmlet' ), $this->module->display_name ),
					'desc_tip'    => true,
					'description' => __( 'Automatically issue description', 'firmlet' ),
					'options' => array(
						'order_paid'       => __( 'After an order is completed', 'firmlet' ),
						'order_creation'   => __( 'After an order is created', 'firmlet' ),
                        'order_processing' => __( 'After an order is processing', 'firmlet' ),
						'disabled'         => __( 'Never, only manually', 'firmlet' ),
					),
				),
			);

			if ( $this->module->correct_firmlet( 'FT' ) ) {
				$settings_fields_ft = array(
					'issue_kind' => array(
						'type'    => 'select',
						'title'   => __( 'Automatically issued document type', 'firmlet' ),
						'options' => array(
							'vat_or_receipt'  => __( 'VAT or receipt', 'firmlet' ),
							'always_vat'      => __( 'Always VAT', 'firmlet' ),
							'always_receipt'  => __( 'Always receipt', 'firmlet' ),
							'always_proforma' => __( 'Always proforma', 'firmlet' ),
							'always_estimate' => __( 'Always estimate', 'firmlet' ),
							'always_bill'     => __( 'Always bill', 'firmlet' ),
						),
					),
				);
				$settings_fields    = array_merge( $settings_fields, $settings_fields_ft );
			};

			if ( ! empty( $api_token ) ) {
				$settings_fields_auto_send = array(
					'auto_send' => array(
						'type'  => 'checkbox',
						'label' => sprintf( __( 'Automatically send an invoice via e-mail from %s', 'firmlet' ), $this->module->display_name ),
						'desc_tip'    => true,
						'description' => __( 'Automatically send description', 'firmlet' ),
					),
				);

				$settings_fields = array_merge( $settings_fields, $settings_fields_auto_send );

				$advanced_setting_fields = array(
					array(
						'type'  => 'title',
						'title' => __( 'Advanced settings', 'firmlet' ),
					),
					'department_id'         => array(
						'type'    => 'select',
						'title'   => __( 'Department:', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Department description', 'firmlet' ),
						'options' => $departments,
					),
					'category_id'           => array(
						'type'    => 'select',
						'title'   => __( 'Category:', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Category description', 'firmlet' ),
						'options' => $categories,
					),
					'incl_free_shipment'    => array(
						'type'  => 'checkbox',
						'label' => __( 'Show free delivery cost on invoice', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Show free delivery description', 'firmlet' ),
					),
					'delivery_address_always' => array(
						'type'  => 'checkbox',
						'label' => __( 'Always show delivery address on invoices', 'firmlet' ),
						'desc_tip' => true,
						'description' => __( 'If not checked: the delivery address is not displayed if it is the same as the billing address', 'firmlet' ),
					),
					'incl_prod_description' => array(
						'type'  => 'checkbox',
						'label' => __( 'Include product description on invoice', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Include product description description', 'firmlet' ),
					),
					'prod_desc_from_vf'     => array(
						'type'  => 'checkbox',
						'label' => __( 'Show on invoice product description from VF', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Include product description from VF', 'firmlet' ),
					),
					'incl_variations_info' => array(
						'type'  => 'checkbox',
						'label' => __( 'Show products variable on invoices', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Show products variable description', 'firmlet' ),
					),
					'incl_meta' => array(
						'type'  => 'checkbox',
						'label' => __( 'Include product metadata on invoices', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Include product metadata', 'firmlet' ),
					),
					'fill_default_desc'     => array(
						'type'  => 'checkbox',
						'label' => __( 'Show default “Note“ on invoices', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'default “Note“ description', 'firmlet' ),
					),
					'incl_order_description' => array(
						'type'  => 'checkbox',
						'label' => __( 'Show “Orders Notes“ on invoices', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Show “Orders Notes“ description', 'firmlet' ),
					),
					'optional_order_note' => array(
						'type'  => 'checkbox',
						'label' => __( 'Add optional order note on invoice', 'firmlet' ),
						'desc_tip' => true,
						'description' => __( 'Show “Optional order note“ on invoice', 'firmlet' ),
					),
					'identify_oss' => array(
						'type'  => 'checkbox',
						'label' => __( 'Identify OSS invoices', 'firmlet' ),
						'desc_tip' => true,
						'description' => __( 'Identify OSS invoices description', 'firmlet' ),
					),
					'show_tax_no_input' => array(
						'type'  => 'checkbox',
						'label' => __( 'Show additional tax number input', 'firmlet' ),
						'desc_tip' => true,
						'description' => __( 'Show additional tax number input description', 'firmlet' ),
						'default'     => 'yes',
					),
					'use_zero_tax_rate' => array(
						'type'  => 'checkbox',
						'label' => __( 'Show a zero tax rate on invoices', 'firmlet' ),
						'desc_tip' => true,
						'description' => __( 'If the tax rate for an item is zero, display a 0% rate on invoices', 'firmlet' ),
					),
					/*
					'force_vat' => array(
						'type'  => 'checkbox',
						'label' => __( 'Force invoicing with buyer VAT', 'firmlet' ),
						'desc_tip' => true,
						'description' => __( 'Force invoicing with buyer VAT description', 'firmlet' ),
					),
					*/
					'company_or_full_name'  => array(
						'type'    => 'select',
						'title'   => __( 'Buyer name on invoice', 'firmlet' ),
						'desc_tip'    => true,
						'description' => __( 'Buyer name description', 'firmlet' ),
						'section' => 'advanced',
						'options' => array(
							'company'               => __( 'Company', 'firmlet' ),
							'full_name'             => __( 'First name and last name', 'firmlet' ),
							'company_and_full_name' => __( 'Company with full name', 'firmlet' ),
						),
					),
					'additional_fields'     => array(
						'type'        => 'textarea',
						'title'       => __( 'Additional fields', 'firmlet' ),
						'description' => __( "Additional fields allows you to modify invoice before it is sent. You have to use JSON syntax to achieve it.\nExample JSON: \"kind\": \"vat\", \"exchange_currency\": \"GBP\"", 'firmlet' ),
					),
				);

				$settings_fields = array_merge( $settings_fields, $advanced_setting_fields );
			}

			return $settings_fields;
		}

		public function generate_description_html( $key, $data ) {
			$field    = $this->plugin_id . $this->id . '_' . $key;
			$defaults = array(
				'class'             => false,
				'css'               => false,
				'custom_attributes' => array(),
				'desc_tip'          => false,
				'description'       => false,
				'title'             => false,
			);

			$data = wp_parse_args( $data, $defaults );
			$database = new VosfacturesDatabase();

			ob_start();

			if ($this->account) { ?>
                <img alt="" src="<?= esc_html( plugin_dir_url( dirname( __FILE__ ) ) ); ?>admin/logo.png" height="32" width="32" style="float: left; margin-top: -6px;">
                <p>
                    <strong><?= sprintf( esc_html__( 'Integrates WooCommerce with your %s account', 'firmlet' ), esc_html( $this->module->display_name ) ); ?></strong>
                </p>
                <p>
					<?= sprintf( esc_html__( 'The module allows you to issue documents (VAT invoices and receipts) for orders in your %s account.', 'firmlet' ), esc_html( $this->module->display_name ) ); ?>
                </p>
                <p>
					<?= sprintf( esc_html__( 'Plugin version: %s', 'firmlet' ), esc_html( $this->module->get_version() ) ); ?>
                </p>
                <p>
					<?= sprintf( esc_html__( 'URL of linked account:', 'firmlet' ) ); ?>
                    <a target="_blank" href="https://<?= $this->account->prefix ?>.vosfactures.fr/">https://<?= $this->account->prefix ?>.vosfactures.fr/</a>
                </p>
                <p>
					<?= sprintf( esc_html__( 'Current plan: %s', 'firmlet' ), $this->account->plan ); ?>
                </p>
                <p>
                    <?php
                        if ($this->account->paid_to != '') {
                            echo sprintf( esc_html__( 'Paid to: %s', 'firmlet' ), $this->account->paid_to );
                        } else {
                            echo sprintf( esc_html__( 'Trial to: %s', 'firmlet' ), $this->account->trial_date );
                        }
                    ?>
                </p>
                <?php
                    if ($database->table_exists()) {
                        $value = esc_html__( 'valid', 'firmlet' );
                        $color = '#15a813';
                    } else {
                        $value = esc_html__( 'invalid', 'firmlet' );
                        $color = '#a30f0f';
                    }
                ?>
                <p>
                    <?= esc_html__( 'Database status:', 'firmlet' ) ?> <span style="color: <?= $color ?>"><?= $value ?></span>
                    <?php if (!$database->table_exists()) { ?>
                        <a style="margin-left: 10px;" class="button" href="#" onclick="recreateTableAction(); return false;" target="_blank"><?= esc_html__( 'Recreate table', 'firmlet' ) ?></a>

                        <script>
                            function recreateTableAction() {
                                let post = jQuery.post(ajaxurl, { action: "recreate_table" });
                                post.always(function () {
                                    location.reload();
                                });
                            }
                        </script>
                    <?php } ?>
                </p>
            <?php } else { ?>
                <img alt="" src="<?= esc_html( plugin_dir_url( dirname( __FILE__ ) ) ); ?>admin/logo.png" height="32" width="32" style="float: left; margin-top: -6px;">
                <p>
                    <strong><?= sprintf( esc_html__( 'Integrates WooCommerce with your %s account', 'firmlet' ), esc_html( $this->module->display_name ) ); ?></strong>
                </p>
                <p>
                    <?= sprintf( esc_html__( 'The module allows you to issue documents (VAT invoices and receipts) for orders in your %s account.', 'firmlet' ), esc_html( $this->module->display_name ) ); ?>
                </p>
                <p><?= sprintf( esc_html__( 'If you do not have an %s account', 'firmlet' ), esc_html( $this->module->display_name ) ); ?>:
                    <a class="button" href="http://app.vosfactures.fr/account/new" target="_blank"><?= sprintf( esc_html__( 'Create account', 'firmlet' ), 'firmlet' ); ?></a>
                </p>
                <p>
                    <a href="https://aide.vosfactures.fr/28664167-Plugin-WooCommerce" target="_blank"><?= sprintf( esc_html__( 'Learn more', 'firmlet' ), 'firmlet' ); ?></a> <?php echo sprintf( esc_html__( 'about setting up %s', 'firmlet' ), esc_html( $this->module->display_name ) ); ?>
                </p>
			<?php
			}
			return ob_get_clean();
		}

		public function sanitize_settings( $settings ) {
			if ( trim( $settings['api_token'] ) == '' ) {
				$settings['errors'] = esc_html__( 'The "API Token" field is required', 'firmlet' );
				WC_Admin_Settings::add_error( $settings['errors'] );
				return $settings;
			}
			// We're just going to make the api key all upper case characters since that's how our imaginary API works
			if ( $settings['api_token'] != get_option( 'woocommerce_firmlet_settings' )['api_token'] ) {
				// We override department_id, cause we need to set it up again.
				$settings['department_id'] = null;
			}

			if ( ! is_array( get_option( 'woocommerce_firmlet_settings' ) )
				|| ! empty( get_option( 'woocommerce_firmlet_settings' )['errors'] )
				|| $settings['api_token'] != get_option( 'woocommerce_firmlet_settings' )['api_token']
				|| $settings['department_id'] != get_option( 'woocommerce_firmlet_settings' )['department_id']
				|| $settings['category_id'] != get_option( 'woocommerce_firmlet_settings' )['category_id']
				|| $settings['additional_fields'] != get_option( 'woocommerce_firmlet_settings' )['additional_fields']
			) {
				if ( ! $this->test_integration1( $settings['api_token'] ) ) {
					$settings['errors'] = esc_html__( 'Connection test1 (checking the API token) failed - please correct the settings', 'firmlet' );
					WC_Admin_Settings::add_error( $settings['errors'] );
					$settings['api_token'] = null;
					return $settings;
				} elseif ( ! $this->test_integration2( $settings['api_token'] ) ) {
					$settings['errors'] = sprintf( esc_html__( 'Connection test2 (checking the company data) failed - please fill up the data at %s account', 'firmlet' ), $this->module->display_name );
					WC_Admin_Settings::add_error( $settings['errors'] );
					$settings['api_token'] = null;
					return $settings;
				} elseif ( ! $this->test_integration2b( $settings['api_token'] ) ) {
					$settings['errors'] = esc_html__( 'Connection test2b (checking the department ID) failed - please correct the settings', 'firmlet' );
					WC_Admin_Settings::add_error( $settings['errors'] );
					$settings['api_token'] = null;
					return $settings;
				} elseif ( ! $this->test_integration3( $settings['api_token'] ) ) {
					$settings['errors'] = esc_html__( 'Connection test3 (creating a test invoice) failed - please make sure your account is active and that you have permission to department', 'firmlet' );
					WC_Admin_Settings::add_error( $settings['errors'] );
					$settings['api_token'] = null;
					return $settings;
				}

				if ( isset( $settings['additional_fields'] ) ) {
					$additional_fields = $this->api->get_additional_fields( $settings['additional_fields'] );
					if ( $additional_fields == null ) {
						$settings['errors'] = esc_html__( 'Additional fields: error in json syntax', 'firmlet' );
						WC_Admin_Settings::add_error( $settings['errors'] );
						return $settings;
					} elseif ( ! $this->validate_fields( $settings['additional_fields'] ) ) {
						$settings['errors'] = esc_html__( 'Additional fields: illegal parameter was defined', 'firmlet' );
						WC_Admin_Settings::add_error( $settings['errors'] );
						return $settings;
					}
				}
			}
			$settings['errors'] = null;
			return $settings;
		}

		private function get_test_invoice() {
			$data = array(
				'invoice' => array(
					'issue_date'       => gmdate( 'Y-m-d' ),
					// 'seller_name' => 'seller_name_test',
					'number'           => 'woocommerce_integration_test',
					'kind'             => 'vat',
					'buyer_first_name' => 'buyer_first_name_test',
					'buyer_last_name'  => 'buyer_last_name_test',
					'buyer_name'       => 'woocommerce_integration_test',
					'buyer_city'       => 'buyer_city',
					'buyer_phone'      => '221234567',
					'buyer_country'    => 'PL',
					'buyer_post_code'  => '01-345',
					'buyer_street'     => 'buyer_street',
					'oid'              => 'test_oid',
					'buyer_email'      => 'buyer_email@test.pl',
					'buyer_tax_no'     => '2923019583',
					'payment_type'     => 'transfer',
					'lang'             => 'pl',
					'currency'         => 'PLN',
					'origin'           => $this->api->get_origin(),
					'positions'        => array(
						array(
							'name'              => 'woocommerce integration test',
							'kind'              => 'text_separator',
							'tax'               => 'disabled',
							'total_price_gross' => 0,
							'quantity'          => 0,
						),
					),
				),
			);

			if ( isset( $_POST['woocommerce_firmlet_department_id'] ) ) {
				$data['invoice']['department_id'] = trim( sanitize_text_field( wp_unslash( $_POST['woocommerce_firmlet_department_id'] ) ) );
			}

			return $data;
		}

		// if failure, probably no internet connection
		// or tried to access from wrong vendor
		// is api token is correct
		private function test_integration1( $api_token ) {
			$url       = $this->api->get_invoices_urlJson( $api_token ) . '?page=1&api_token=' . $api_token;

			$result = $this->api->make_request( $url, 'GET', null );
			return is_array( $result );
		}

		// is department set
		private function test_integration2( $api_token ) {
			// PHP 5.2.17 Compatibility - assign departments first
			$departments = $this->api->get_departments( $api_token );
			return ! empty( $departments[0]->id );
		}

		// is department_id is correct
		private function test_integration2b( $api_token ) {
			if ( empty( $_POST['woocommerce_firmlet_department_id'] ) ) {
				return true;
			} else {
				$department_id = trim( sanitize_text_field( wp_unslash( $_POST['woocommerce_firmlet_department_id'] ) ) );
			}

			foreach ( $this->api->get_departments( $api_token ) as $dep ) {
				if ( $dep->id == (int) $department_id ) {
					return true;
				}
			}

			return false;
		}

		// is invoice creation possible
		private function test_integration3( $api_token ) {
			$url                       = $this->api->get_invoices_urlJson( $api_token );
			$invoice_data              = $this->get_test_invoice();
			$invoice_data['api_token'] = $api_token;

			if ( isset( $_POST['woocommerce_firmlet_additional_fields'] ) ) {
				$additional_fields = $this->api->get_additional_fields( sanitize_text_field( wp_unslash( $_POST['woocommerce_firmlet_additional_fields'] ) ) );
				foreach ( $additional_fields as $key => $value ) {
					if ( in_array( $key, $this->api->get_illegal_fields() ) ) {
						WC_Admin_Settings::add_error( esc_html( __( 'Illegal field found in additional fields: "%s"', 'firmlet' ), $key ) );
						return false;
					} else {
						$invoice_data['invoice'][ $key ] = $value;
					}
				}
			}

			$result = $this->module->plugin_admin->issue_invoice( $invoice_data, $url );
			if ( isset( $result->code ) && $result->code === 'error' && isset( $result->message ) ) {
				$error_message = 'Test3: ';
				if ( empty( $result->message ) ) {
					WC_Admin_Settings::add_error( esc_html__( '%s Undefined response from the server.', 'firmlet' ), $error_message );
				} elseif ( isset( $result->message->seller_tax_no ) ) {
					WC_Admin_Settings::add_error( $error_message . $result->message->seller_tax_no[0] );
				} elseif ( isset( $result->message->seller_tax_no ) ) {
					WC_Admin_Settings::add_error( $error_message . $result->message->buyer_tax_no[0] );
				} elseif ( $result->message == 'account_not_pro' ) {
					WC_Admin_Settings::add_error( esc_html__( '%1$s Your account on %2$s Fakturownia is not PRO', 'firmlet' ), $error_message, $this->module->display_name, 'error' );
				} else {
					WC_Admin_Settings::add_error( $error_message . json_encode( $result->message ) );
				}
			}

			if ( empty( $result->id ) ) {
				return false;
			} else { // usuwanie dodanej faktury, klienta i sprzedawcy
				$url = $this->api->get_invoice_url_json( $result->id, $api_token ) . '?api_token=' . $api_token;
				$this->api->make_request( $url, 'DELETE', null );

				$url = $this->api->get_client_url_json( $result->client_id, $api_token ) . '?api_token=' . $api_token;
				$this->api->make_request( $url, 'DELETE', null );
				return true;
			}
		}

		private function validate_fields( $additional_fields ) {
			$additional_fields = $this->api->get_additional_fields( $additional_fields ); // json encode from string
			foreach ( $additional_fields as $key ) {
				if ( in_array( $key, $this->api->get_illegal_fields(), true ) ) {
					return false;
				}
			}

			return true;
		}
	}
endif;
