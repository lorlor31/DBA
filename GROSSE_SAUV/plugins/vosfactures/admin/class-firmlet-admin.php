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
	 * The admin-specific functionality of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @package    firmlet
	 * @subpackage firmlet/admin
	 */

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * Defines the plugin name, version, and two examples hooks for how to
	 * enqueue the admin-specific stylesheet and JavaScript.
	 *
	 * @package    firmlet
	 * @subpackage firmlet/admin
	 * @author     VosFactures
	 */
class VosfacturesAdmin {


	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $name The ID of this plugin.
	 */
	private $name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $name    The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $name, $version, $firmlet ) {
		$this->name       = $name;
		$this->version    = $version;
		$this->firmlet    = $firmlet;
		$this->module     = firmlet_vosfactures();
		$this->module_key = '67cf9d1ee45d26f253a847dafcfc2ba9';
		$this->api        = new VosfacturesApi( $name, $version );
		$this->db         = new VosfacturesDatabase();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in VosfacturesLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The VosfacturesLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'css/firmlet-admin.css', false, '1.3.1' );
		wp_enqueue_style( 'custom_wp_admin_css' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in VosfacturesLoader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The VosfacturesLoader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->name, plugin_dir_url( __FILE__ ) . 'js/firmlet-admin.js', array( 'jquery' ), $this->version, false );
	}


	// SETTINGS

	/**
	 * Add a new integration to WooCommerce.
	 */
	public function add_integration( $integrations ) {
		if ( class_exists( 'WC_Integration' ) ) {
			add_option( 'firmlet_error' );
			include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-firmlet-integration.php';
			$integrations[] = 'Vosfactures_Integration';
			return $integrations;
		}
	}

	public function page_options() {
		include plugin_dir_path( __FILE__ ) . 'partials/firmlet-admin-options-page.php';
	} // page_options()

	public function link_settings( $links ) {
		array_unshift( $links, sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=wc-settings&tab=integration&section=firmlet' ) ), esc_html__( 'Settings', 'firmlet' ) ) );
		return $links;
	} // link_settings()

	// SETTINGS END

	public function recreate_table() {
		$logger = wc_get_logger();

		$logger->debug( 'recreate_table action triggered', array( 'source' => 'vosfactures' ) );

		$database = new VosfacturesDatabase();
		$database->install_database();
	}

	public function invoice_handler() {
		if ( isset( $_POST['command'] ) ) {
			$command = sanitize_text_field( wp_unslash( $_POST['command'] ) );
		}

		$logger = wc_get_logger();

		switch ( $command ) {
			case 'delete':
				$logger->debug( 'Manual invoice deletion (command: : ' . $command . ')', array( 'source' => 'vosfactures' ) );
				$this->remove_invoice();
				break;
			case 'issue':
				$logger->debug( 'Manual issue invoice (command: : ' . $command . ')', array( 'source' => 'vosfactures' ) );
				$this->issue_invoice();
				break;
			default:
				$logger->debug( 'UNDEFINED COMMAND: ' . $command . '', array( 'source' => 'vosfactures' ) );
		}
	}


	/**
	 * $order_id
	 *
	 * @since 1.0.3
	 */
	public function issue_invoice( $invoice_data = null, $url = null, $order_id = null ) {
		if ( ! isset( $invoice_data ) ) {
			$api_token = get_option( 'woocommerce_firmlet_settings' )['api_token'];
			$url       = $this->api->get_invoices_urlJson( $api_token );
			if ( ! isset( $order_id ) ) {
				if ( isset( $_POST['order_id'] ) ) {
					$order_id = sanitize_text_field( wp_unslash( $_POST['order_id'] ) );
				} elseif ( isset( $_POST['post_ID'] ) ) {
					$order_id = sanitize_text_field( wp_unslash( $_POST['post_ID'] ) );
				}
			}
			$order = wc_get_order( $order_id );

			if ( $this->module->correct_firmlet( 'FT' ) ) {
				$invoice_data = $this->invoice_from_order( $order_id, sanitize_text_field( wp_unslash( $_POST['issue_kind'] ) ) );
			} elseif ( $this->module->correct_firmlet( 'VF' ) ) {
				$invoice_data = $this->invoice_from_order( $order_id );
			}

			if ( $order->get_status() == 'completed' ) {
				$invoice_data['invoice']['status']         = 'paid';
				$invoice_data['invoice']['payment_to_kind'] = '0';
			}

			if ( get_option( 'woocommerce_firmlet_settings' )['fill_default_desc'] == 'yes' ) {
				$invoice_data['fill_default_descriptions_from_department'] = true;
			}

			if ( get_option( 'woocommerce_firmlet_settings' )['prod_desc_from_vf'] == 'yes' ) {
				$invoice_data['fill_products_descriptions'] = true;
			}

			$response = $this->api->make_request( $url, 'POST', $invoice_data );

			if ( ! isset( $response ) ) {
				update_option( 'firmlet_error', 'firmlet_connection_failed' );
				return false;
			} else {
				$logger = wc_get_logger();
				$error_message = 'send_invoice failed [Order ' . $order_id . ']: ';
				switch ( gettype( $response ) ) {
					case 'object': // object, not an array
						if ( ! empty( $response->code ) && $response->code === 'error' ) {
							if ( ! empty( $response->message->buyer_tax_no ) ) {
								update_option( 'firmlet_error', 'invalid_buyer_tax_no' );
							} elseif ( $response->message == 'account_not_pro' ) {
								update_option( 'firmlet_error', 'account_not_pro' );
							} elseif ( ! empty( $response->message->buyer_email ) ) {
								update_option( 'firmlet_error', 'buyer_email_invalid' );
							} else {
								$error = json_encode( $response->message );

								update_option( 'firmlet_error', 'check_log_info' );
								$logger->debug( $error_message . $error, array( 'source' => 'vosfactures' ) );
							}
							return false;
						} else {
							$this->after_send_actions( $response, $order );
						}
						break;
					case 'string':
						if ( $response == 'ok' ) {
							// success, issued on firmlet
							$logger->debug( $invoice_data['invoice']['number'] . 'invoice_was_issued', array( 'source' => 'vosfactures' ) );
						} else {
							$logger->debug( $error_message, array( 'source' => 'vosfactures' ) );
							return false;
						}
						break;
					default:
				}
			}
		} else {
			if ( is_array( get_option( 'woocommerce_firmlet_settings' ) ) && get_option( 'woocommerce_firmlet_settings' )['fill_default_desc'] == 'yes' ) {
				$invoice_data['fill_default_descriptions_from_department'] = true;
			}

			$response = $this->api->make_request( $url, 'POST', $invoice_data );
		}

		return $response;
	}

	private function remove_invoice() {
		if ( isset( $_POST['order_id'] ) ) {
			$this->api->remove_invoice( sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) );
		}
	}

	private function invoice_from_order( $order_id, $kind = '' ) {
		$order    = wc_get_order( $order_id );
		$invoice  = new VosfacturesInvoice( $order, $kind, $this->name, $this->version );
		$api_data = array(
			'api_token' => get_option( 'woocommerce_firmlet_settings' )['api_token'],
			'invoice'   => $invoice->get_final_invoice_data(),
		);

		if ( get_option( 'woocommerce_firmlet_settings' )['identify_oss'] == 'yes' ) {
			$api_data['identify_oss'] = true;

			if ( get_option( 'woocommerce_firmlet_settings' )['force_vat'] == 'yes' ) {
				$api_data['oss_force_tax'] = true;
			}
		}

		return $api_data;
	}

    public function firmlet_after_payment_complete ( $order_id ) {
        $last_invoice = $this->db->get_last_invoice($order_id);

        if ( $last_invoice && $last_invoice->external_id != 0 ) {
            $this->make_invoice_status_paid( $last_invoice->external_id );
        }
    }

	public function firmlet_completed( $order_id ) {
        $last_invoice = $this->db->get_last_invoice($order_id);
		$logger       = wc_get_logger();
		$logger->debug( 'completed for order [' . $order_id . '] was triggered', array( 'source' => 'vosfactures' ) );
		if ( ! empty( $last_invoice ) ) {
			if ( $last_invoice->external_id != 0 ) {
				$this->make_invoice_status_paid( $last_invoice->external_id );
			}
		} elseif ( get_option( 'woocommerce_firmlet_settings' )['auto_issue'] == 'order_paid' ) {
			$this->issue_invoice( null, null, $order_id );
		}
	}

	private function make_invoice_status_paid( $id_firmlet_invoice ) {
		$url  = $this->api->get_invoice_url( (int) $id_firmlet_invoice ) . '/change_status.json';
		$data = array(
			'api_token' => get_option( 'woocommerce_firmlet_settings' )['api_token'],
			'status'    => 'paid',
		);
		$this->api->make_request( $url, 'POST', $data );
	}

	public function firmlet_after_new_admin_order( $order_id ) {
		$logger = wc_get_logger();
        if ( $_POST["original_post_status"] == "auto-draft" ) {
			$logger->debug( 'creating invoice after woocommerce_process_shop_order_meta [' . $order_id . '] was triggered', array( 'source' => 'vosfactures' ) );
			$this->firmlet_after_new_order( $order_id );
		}
	}

	public function firmlet_after_scheduled_subscription_payment( $subscription_id )
	{
		$auto_issue = get_option( 'woocommerce_firmlet_settings' )['auto_issue'];

		if ( $auto_issue == 'order_creation' ) {
			$sub = new WC_Subscription( $subscription_id );

			$last_order_id = $sub->get_last_order( 'all', 'any' )->id;

			if ( $last_order_id ) {
				$this->firmlet_after_new_order( $last_order_id );
			}
		}
	}

	public function firmlet_after_processing_checkout( $order_id ) {
		$logger = wc_get_logger();
		$logger->debug( 'woocommerce_checkout_order_processed [' . $order_id . '] was triggered', array( 'source' => 'vosfactures' ) );
		$this->firmlet_after_new_order( $order_id );
	}

	public function firmlet_processing( $order_id ) {
		$last_invoice = $this->db->get_last_invoice($order_id);
		$logger       = wc_get_logger();
		$logger->debug( 'processing for order [' . $order_id . '] was triggered', array( 'source' => 'vosfactures' ) );

		if ( ! empty( $last_invoice ) ) {
			if ( $last_invoice->external_id != 0 ) {
				$this->make_invoice_status_paid( $last_invoice->external_id );
			}
		} elseif ( get_option( 'woocommerce_firmlet_settings' )['auto_issue'] == 'order_processing' ) {
			$this->issue_invoice( null, null, $order_id );
		}
	}

	public function firmlet_after_new_order( $order_id ) {
		$module       = firmlet_vosfactures();
		$order        = wc_get_order( $order_id );
		$logger       = wc_get_logger();

		$last_invoice = $this->db->get_last_invoice($order_id);
		$auto_issue   = get_option( 'woocommerce_firmlet_settings' )['auto_issue'];

		$logger->debug('Starting for order ' . $order_id . ': ' .
						'is_configured: ' . $module->is_configured() . ', ' .
						'auto_issue: ' . $auto_issue . ', ' .
						'order_status: ' . $order->get_status() . ', ' .
						'last_invoice: ' . json_encode($last_invoice),
						array( 'source' => 'vosfactures' ));

		if ( $module->is_configured() && in_array( $auto_issue, array( 'order_creation', 'order_paid', 'order_processing' ) ) ) {
			if ( $auto_issue == 'order_creation' ) {
				if ( empty( $last_invoice ) ) { // issue invoice after order creation
                    $logger->debug( 'issuing for order_creation', array( 'source' => 'vosfactures' ) );
					$url          = $this->api->get_invoices_urlJson();
					$invoice_data = $this->invoice_from_order( $order_id );
					if ( $order->get_status() == 'completed' ) {
						$invoice_data['invoice']['status']          = 'paid';
						$invoice_data['invoice']['payment_to_kind'] = '0';
					}
					$result = $this->issue_invoice( $invoice_data, $url );
					$this->after_send_actions( $result, $order );
				}
			} elseif ( $auto_issue == 'order_paid' && $order->get_status() == 'completed' ) {
				if ( $order->get_status() == 'completed' && empty( $last_invoice ) ) { // issue invoice
					$logger->debug( 'issuing for order_paid', array( 'source' => 'vosfactures' ) );
					$url                                        = $this->api->get_invoices_urlJson();
					$invoice_data                               = $this->invoice_from_order( $order_id );
					$invoice_data['invoice']['status']          = 'paid';
					$invoice_data['invoice']['payment_to_kind'] = '0';
					$result                                     = $this->issue_invoice( $invoice_data, $url );
					$this->after_send_actions( $result, $order );
				} elseif ( ! empty( $last_invoice ) ) { // set status as paid
                    if( $last_invoice instanceof stdClass ) {
                        $logger->debug( 'mark_paid for order_paid ( stdClass )', array( 'source' => 'vosfactures' ) );
                        $this->make_invoice_status_paid( $last_invoice->external_id );
                    } else {
                        $logger->debug( 'mark_paid for order_paid', array( 'source' => 'vosfactures' ) );
                        $this->make_invoice_status_paid( $last_invoice['external_id'] );
                    }
				}
			} elseif ( $auto_issue == 'order_processing' && $order->get_status() == 'processing' ) {
			    if( empty( $last_invoice ) ) {
                    $logger->debug( 'issuing for order_processing', array( 'source' => 'vosfactures' ) );
                    $url = $this->api->get_invoices_urlJson();
                    $invoice_data = $this->invoice_from_order($order_id);
                    $invoice_data['invoice']['status'] = 'paid';
                    $invoice_data['invoice']['payment_to_kind'] = '0';
                    $result = $this->issue_invoice($invoice_data, $url);
                    $this->after_send_actions($result, $order);
                } elseif( $last_invoice instanceof stdClass ) {
                    $logger->debug( 'mark_paid for order_processing ( stdClass )', array( 'source' => 'vosfactures' ) );
                    $this->make_invoice_status_paid( $last_invoice->external_id );
                } else {
                    $logger->debug( 'mark_paid for order_processing', array( 'source' => 'vosfactures' ) );
                    $this->make_invoice_status_paid( $last_invoice['external_id'] );
                }
            }
		}
	}

	public function vf_view_order( $order_id ) {
		$module  = firmlet_vosfactures();
		$invoice = $this->db->get_last_invoice( $order_id );
		?>
			<?php if ( $module->is_configured() && ! empty( $invoice ) && ! empty( $invoice->external_id ) && empty( $invoice->error ) ) : ?>
					<h2><?php _e( 'Invoice', 'firmlet' ); ?></h2>
					<a target="_blank" class="button" href="<?php echo $invoice->view_url; ?>.pdf"><?php esc_html_e( 'Download document as pdf', 'firmlet' ); ?></a>
			<?php endif; ?>
			<?php
	}

	public function vf_after_order_delete( $order_id ) {
		if ( get_post_type( $order_id ) !== 'shop_order' ) {
			return;
		}

		$notices   = get_option( 'firmlet_deferred_admin_notices', array() );
		$notices[] = esc_html__( 'The invoice has been already generated on your VosFactures account, you can cancel it or make a credit note.', 'firmlet' );
		update_option( 'firmlet_deferred_admin_notices', $notices );
	}

	private function after_send_actions( $result, $order ) {
		$order_invoices = $this->db->get_all_invoices( $order->id );
		if ( $order_invoices && $order_invoices != '' ) {
			$this->db->delete_invoice( $order->id );
		}
		$logger = wc_get_logger();
		if ( empty( $result ) ) {
			// Response is null. That probably means that the server is down.
			$error = esc_html__( '%s failed to return a response to the request. We believe that it\'s our issue and we\'ll do whatever we can to speed up the process. Please, try again later.', 'firmlet' );
			$this->db->insert_invoice_with_error( $order->get_id(), $error );
			$logger->debug( $error . 'Order' . (int) $order->get_id(), array( 'source' => 'vosfactures' ) );
			return false;
		}

		if ( empty( $result->view_url ) || empty( $result->id ) ) {
			if ( $result->code == 'error' && ! empty( $result->message ) ) {
				$error = $this->prepare_firmlet_error( $result->message );
			} else {
				$error = $this->api->get_api_domain();
			}
			$this->db->insert_invoice_with_error( $order->get_id(), $error );
			$logger->debug( $error . 'Order' . (int) $order->get_id(), array( 'source' => 'vosfactures' ) );
			return false;
		}

		$this->db->insert_invoice( $order->get_id(), $result->view_url, $result->id );

		if ( get_option( 'woocommerce_firmlet_settings' )['auto_send'] == 'yes' ) {
			$url = $this->api->get_invoice_url( $result->id ) . '/send_by_email.json?api_token=' . get_option( 'woocommerce_firmlet_settings' )['api_token'];
			$this->api->make_request( $url, 'POST', null );
		}

		if ( $order->get_status() === 'completed' ) {
			$this->make_invoice_status_paid( $result->id );
		}

		return true;
	}

	private function prepare_firmlet_error( $message ) {
		switch ( gettype( $message ) ) {
			case 'string':
				return $message;
			case 'object':
				$error = '';
				foreach ( $message as $key => $value ) {
					foreach ( $value as $array_value ) {
						$error .= $key . ' ' . $array_value . PHP_EOL;
					}
				}
				return trim( $error );
			default:
				return json_encode( $message );
		}
	}

	// VAT NO
	// VAT Number in WooCommerce Checkout
	public function tax_no_override_checkout_fields( $fields ) {
		$fields['billing']['billing_tax'] = array(
			'label'    => __( 'VAT Number', 'firmlet' ),
			'required' => false,
			'class'    => array( 'form-row-wide' ),
			'clear'    => true,
		);

		return $fields;
	}

	public function tax_no_checkout_field_update_order_meta( $order_id ) {
		if ( ! empty( $_POST['billing_tax'] ) ) {
			update_post_meta( $order_id, 'tax_no', sanitize_text_field( $_POST['billing_tax'] ) );
		}
	}

	// Display field value on the order edit page
	public function tax_no_checkout_field_display_admin_order_meta( $fields ) {
		if ( isset( $_GET['post'] ) ) {
			$fields['tax'] = array(
				'label' => __( 'VAT Number', 'firmlet' ),
				'show'  => true,
				'value' => get_post_meta( sanitize_text_field( wp_unslash( $_GET['post'] ) ), 'tax_no', true ),
			);
		} else {
			$fields['tax'] = array(
				'label' => __( 'VAT Number', 'firmlet' ),
				'show'  => true,
			);
		}
		return $fields;
	}

	public function save_tax_no_after_order_details( $post_id ) {
		if ( ! empty( $_POST['_billing_tax'] ) ) {
			update_post_meta( $post_id, 'tax_no', $_POST['_billing_tax'] );
		}
	}

	// hide tax_no from custom fields
	public function tax_no_exclude_custom_fields( $protected, $meta_key ) {
		if ( 'shop_order' == get_post_type() ) {
			if ( in_array( $meta_key, array( 'tax_no' ) ) ) {
				return true;
			}
		}
		return $protected;
	}

	// VAT NO END

	public function invoices_with_errors_check( $has_orders ) {
		if ( get_current_screen()->id == 'edit-shop_order' ) {
			$invoices_with_errors = $this->db->invoices_with_errors();
			$orders               = array();
			foreach ( $invoices_with_errors as $invoice ) {
				$order = wc_get_order( $invoice->id_order );
				$last_invoice = $this->db->get_last_invoice( $invoice->id_order );

				if ( $order == false || empty( $last_invoice->error ) ) {
					continue;
				}

				array_push( $orders, $order );
			}

			if ( ! empty( $orders ) ) {
				?>
					<div class="notice notice-error is-dismissible">
						<p><?php _e( 'There were problems when issuing invoices for the following orders:', 'firmlet' ); ?></p>
					<?php
					foreach ( $orders as $order ) {
						echo '<li><a href="' . esc_url( $order->get_edit_order_url() ) . '">' . wp_kses_post( $order->get_order_number() ) . '</a></li>';
					}
					?>
					</div>
					<?php
			}
		}
	}

	public function firmlet_error_notice() {
		if ( get_option( 'firmlet_error' ) != null ) {
			?>
					<div class="error notice"><p>
					<?php
					if ( get_option( 'firmlet_error' ) == 'invalid_api_token' ) {
						_e( 'The "API Token" field is incorrect. Please, correct it.', 'firmlet' );
					} elseif ( get_option( 'firmlet_error' ) == 'firmlet_connection_failed' ) {
						printf( esc_html__( '%s failed to return a response to the request. We believe that it\'s our issue and we\'ll do whatever we can to speed up the process. Please, try again later.', 'firmlet' ), $this->module->display_name );
					} elseif ( get_option( 'firmlet_error' ) == 'invalid_buyer_tax_no' ) {
						_e( 'Invalid buyer tax number', 'firmlet' );
					} elseif ( get_option( 'firmlet_error' ) == 'buyer_email_invalid' ) {
						_e( 'Invalid buyer email', 'firmlet' );
					} elseif ( get_option( 'firmlet_error' ) == 'account_not_pro' ) {
						printf( esc_html__( 'Your account on %s is not PRO', 'firmlet' ), $this->module->display_name );
					} elseif ( get_option( 'firmlet_error' ) == 'check_log_info' ) {
						printf( esc_html__( 'Document creation failed on %s. Please check logs for more info.', 'firmlet' ), $this->module->display_name );
					}
					?>
					</p></div>
				<?php
				delete_option( 'firmlet_error' );
		}

		if ( $notices = get_option( 'firmlet_deferred_admin_notices' ) ) {
			foreach ( $notices as $notice ) {
				echo "<div class='updated'><p>$notice</p></div>";
			}
			delete_option( 'firmlet_deferred_admin_notices' );
		}
	}

	public function vf_shop_order_column( $columns ) {
		$reordered_columns = array();

		foreach( $columns as $key => $column){
			$reordered_columns[$key] = $column;
			if( $key ==  'order_status' ){
				$reordered_columns['vf_column'] = __( 'Invoice issued on VosFactures','firmlet');
			}
		}

		return $reordered_columns;
	}

	public function vf_orders_list_column_content( $column, $post_id ) {
		if ($column == 'vf_column') {
			$invoice = $this->db->get_last_invoice( $post_id );

			if ( empty($invoice) ) {
				echo '<mark class="order-status status-pending"><span>' . __( 'No','firmlet') . '</span></mark>';
			} elseif ( empty($invoice->error) ) {
				echo '<mark class="order-status status-processing"><span>' . __( 'Yes','firmlet') . '</span></mark>';
			} else {
				echo '<mark data-tip="' . $invoice->error . '" class="order-status status-failed tips"><span>' . __( 'Failed','firmlet') . '</span></mark>';
			}
		}
	}

	public function vf_wp_dashboard_setup() {
		wp_add_dashboard_widget(
			'vf_invoices_widget',
			'VosFactures',
			array($this, 'render_vf_invoices_widget')
		);
	}

	public function render_vf_invoices_widget() {
		echo '<p><span class="dashicons dashicons-dashboard"></span> ' . __('Number of issued invoices', 'firmlet') . ': <strong>' . $this->db->count_issued() . '</strong></p>';

		$errorsR = $this->db->invoices_with_errors();
		$errors = [];

		foreach ( $errorsR as $error ) {
			$order = wc_get_order( $error->id_order );

			if ($order) {
				$errors[] = $order;
			}
		}

		if ( empty($errors) ) {
			echo '<div style="margin: 5px -12px 15px -12px; padding: 1px 12px; border-left: 2px solid #72aee6; background: #f6f7f7;">';
			echo '<p>' . __( 'Everything\'s ok, we did not notice any problems', 'firmlet' ) . '</p>';
		} else {
			echo '<div style="margin: 5px -12px 15px -12px; padding: 1px 12px; border-left: 2px solid #d63638; background: #f6f7f7;">';
			echo '<p>' . __( 'There were problems when issuing invoices for the following orders:', 'firmlet' ) . '</p>';
			echo '<ul>';
			foreach ( $errors as $order ) {
				echo '<li><a href="' . esc_url( $order->get_edit_order_url() ) . '">#' . wp_kses_post( $order->get_order_number() ) . '</a></li>';
			}
			echo '</ul>';
		}
		echo '</div>';

		echo '<a href="' . admin_url('edit.php?post_type=shop_order') . '" class="button button-primary">' . __('Go to orders', 'firmlet') . '</a>';
	}

	public function firmlet_bulk_actions_edit_shop_order ( $bulk_actions ) {
		$bulk_actions['firmlet_issue'] = __('Issue invoice on VosFactures', 'firmlet');
		return $bulk_actions;
	}

	function firmlet_issue_bulk_action_edit_shop_order( $redirect_to, $action, $post_ids ) {
		if ( $action == 'firmlet_issue' ) {
			$logger = wc_get_logger();
			$logger->debug( 'Bulk issuing invoices started, post_ids: ' . json_encode($post_ids), array( 'source' => 'vosfactures' ) );

			foreach ( $post_ids as $post_id ) {
				$last_invoice = $this->db->get_last_invoice( $post_id );

				if ( empty( $last_invoice ) ) {
					$this->issue_invoice( null, null, $post_id );
				}
			}

			$logger->debug( 'Bulk issuing invoices finished', array( 'source' => 'vosfactures' ) );
		}

		return $redirect_to;
	}
}
