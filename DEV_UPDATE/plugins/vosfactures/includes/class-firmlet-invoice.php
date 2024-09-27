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

class VosfacturesInvoice {
	/**
	 * @var WC_Order
	 */
	public $order;

	/**
	 * @var VosfacturesApi
	 */
	public $api;

	/**
	 * @var VosfacturesDatabase
	 */
	public $db;

	/**
	 * @var Vosfactures
	 */
	public $module;

	/**
	 * @var array
	 */
	public $invoice_data = array();

	/**
	 * @var array
	 */
	public $carriage = array();

	/**
	 * @var array
	 */
	public $positions = array();

	/**
	 * @var array
	 */
	public $fees = array();

	/**
	 * @var array
	 */
	public $address;

	/**
	 * @var array
	 */
	public $address_delivery;

	/**
	 * @var null|string
	 */
	public $country;

	/**
	 * @var null|string
	 */
	public $country_delivery;

	/**
	 * @var string
	 */
	public $lang;

	/**
	 * @var string
	 */
	public $currency;

	/**
	 * @var null|WC_Customer
	 */
	public $customer;

	/**
	 * @var null|string
	 */
	public $kind;

	/**
	 * @var null|string
	 */
	public $phone;

	/**
	 * @var null|string
	 */
	public $buyer_name;

	/**
	 * @var bool
	 */
	public $discount_applied = false;

	/**
	 * VosfacturesInvoice constructor.
	 *
	 * @param WC_Order $order
	 * @param string $kind
	 * @param null|string $name
	 * @param null|string $version
	 */
	public function __construct( $order, $kind, $name = null, $version = null ) {
		$logger = wc_get_logger();

		if ( $logger ) {
			$option = get_option( 'woocommerce_firmlet_settings' );

			if ( array_key_exists('identify_oss', $option) && $option['identify_oss'] == 'yes' ) {
				$logger->debug( 'order ' . $order->get_id() . ' - identify_oss enabled', [ 'source' => 'vosfactures' ] );
			}

			if ( array_key_exists('force_vat', $option) && $option['force_vat'] == 'yes' ) {
				$logger->debug( 'order ' . $order->get_id() . ' - force_vat enabled', [ 'source' => 'vosfactures' ] );
			}
		}

		$this->api    = new VosfacturesApi( $name, $version );
		$this->db     = new VosfacturesDatabase();
		$this->module = firmlet_vosfactures();
		$this->order  = $order;
		$this->init();

		// DO NOT CHANGE THIS ORDER
		$this->setup_positions();
		$this->setup_carriage();
		$this->add_carriage();
		$this->add_order_fee();
		$this->setup_kind( $kind );
		$this->setup_buyer_name();

		$this->prepare_final_invoice_data();
	}

	public function get_final_invoice_data() {
		return $this->invoice_data;
	}

	private function setup_carriage() {
		if ( ( get_option( 'woocommerce_firmlet_settings' )['incl_free_shipment'] == 'yes' ) ||
		     round( (float) $this->order->get_shipping_total(), 2 ) > 0 ) {
			$this->carriage = array();

			foreach ( $this->order->get_shipping_methods() as $shipping_item_obj ) {
				$option  = get_option( 'woocommerce_firmlet_settings' );
				$use_oss = array_key_exists( 'identify_oss', $option ) && $option['identify_oss'] == 'yes';

				if ( $use_oss ) {
					$this->order->calculate_taxes();
				}

				$taxes     = $shipping_item_obj->get_taxes();
				$tax_total = array_shift( $taxes );
				$taxes     = array_filter(
					$tax_total,
					static function ( $value ) {
						return ! is_null( $value ) && $value !== '';
					}
				);
				$rate_id   = key( $taxes );
				$tax       = $this->db->get_item_tax_rate( $rate_id );

				$item_total = (float) $shipping_item_obj->get_total();
				$total_tax  = (float) $shipping_item_obj->get_total_tax();

				$name = apply_filters( 'vf_invoice_carriage_name', $shipping_item_obj->get_name(), $shipping_item_obj );

				$this->carriage[] = array(
					'name'              => strip_tags( $name ),
					'quantity'          => 1,
					'total_price_gross' => $item_total + $total_tax,
					'tax'               => ( !$use_oss && $total_tax == 0 ? $this->disabled_tax_rate() : round( (float) $tax, 2 ) ),
					'service'           => true,
				);
			}
		}
	}

	private function add_carriage() {
		// Decide whether to add carriage or not.
		if ( isset( $this->carriage ) && is_array( $this->carriage ) ) {
			foreach ( $this->carriage as $carr ) {
				$this->positions[] = $carr;
			}
		}
	}

	private function add_order_fee() {
		$fees = $this->order->get_fees();

		// Adding fee as last position
		if ( ! empty( $fees ) ) {
			$this->fees = array();

			foreach ( $fees as $fee_item_obj ) {
				$taxes     = $fee_item_obj->get_taxes();
				$tax_total = array_shift( $taxes );
				$taxes     = array_filter(
					$tax_total,
					static function ( $value ) {
						return ! is_null( $value ) && $value !== '';
					}
				);
				$rate_id   = key( $taxes );
				$tax       = $this->db->get_item_tax_rate( $rate_id );

				$item_total = (float) $fee_item_obj->get_total();
				$total_tax  = (float) $fee_item_obj->get_total_tax();

				$name = apply_filters( 'vf_invoice_fee_name', $fee_item_obj->get_name(), $fee_item_obj );

				$this->fees[] = array(
					'name'              => strip_tags( $name ),
					'quantity'          => 1,
					'total_price_gross' => $item_total + $total_tax,
					'tax'               => ( $total_tax == 0 ? $this->disabled_tax_rate() : round( (float) $tax, 2 ) ),
					'service'           => true,
				);
			}
		}
		if ( isset( $this->fees ) && is_array( $this->fees ) ) {
			foreach ( $this->fees as $fee ) {
				$this->positions[] = $fee;
			}
		}
	}

	private function setup_kind( $kind = '' ) {
		if ( $this->module->correct_firmlet( 'FT' ) ) {
			if ( $kind == '' ) {
				$issue_kind = get_option( 'woocommerce_firmlet_settings' )['issue_kind'];
				// Automatically issued invoice
				if ( $issue_kind === 'always_receipt' ) {
					$kind = 'receipt';
				} elseif ( $issue_kind === 'always_proforma' ) {
					$kind = 'proforma';
					// Kinds available only in FT
				} elseif ( $issue_kind === 'always_estimate' ) {
					$kind = 'estimate';
				} elseif ( $issue_kind === 'vat_or_receipt' ) {
					$kind = ( $this->is_valid_nip( get_post_meta( $this->order->get_id(), '_billing_tax', true ) ) && ! empty( $this->order->get_address()['company'] ) ) ? 'vat' : 'receipt';
				} elseif ( $issue_kind === 'always_bill' ) {
					$kind = 'bill';
				} else {
					$kind = 'vat';
				}
			} elseif ( ! in_array( $kind, array( 'vat', 'receipt', 'proforma', 'estimate', 'bill' ) ) ) {
				// manually created invoice
				$kind = ( $this->is_valid_nip( get_post_meta( $this->order->get_id(), '_billing_tax', true ) ) && ! empty( $this->order->get_address()['company'] ) ) ? 'vat' : 'receipt';
			}
		} else {
			// VosFactures
			$kind = 'vat';
		}

		// Tax is disabled on bills
		if ( $kind === 'bill' && $this->module->correct_firmlet( 'FT' ) ) {
			foreach ( $this->positions as $position ) {
				$position['tax'] = $this->disabled_tax_rate();
			}
		}

		$this->kind = $kind;
	}

	private function init() {
		$this->address = $this->order->get_address();

		// Used only if the deliver and invoice addresses differ
		$this->address_delivery = $this->order->get_address( 'shipping' );

		$this->country = $this->order->get_billing_country();

		$this->country_delivery = $this->order->get_shipping_country();

		$this->lang = get_locale();

		$this->currency = $this->order->get_currency();

		$this->customer = new WC_Customer( $this->order->get_customer_id() );

		return true;
	}

	/**
	 * Metoda zwraca produkt z przekazanej w parametrze pozycji
	 *
	 * @param $line_item
	 *
	 * @return mixed
	 */
	private function get_product_from_line_item( $line_item ) {
		$variation_id = $line_item->get_variation_id();

		if ( $variation_id == 0 ) {
			return $line_item->get_product();
		}

		return new WC_Product_Variation( $variation_id );
	}

	/**
	 * Metoda zwraca id zestawów z modułu WPC Product Bundles mają różne podatki
	 *
	 * @return array
	 */
	private function getBundleIdsWithDifferentTaxes( $line_items ) {
		$ids = [];

		foreach ( $line_items as $line_item ) {
			$wc_product = $this->get_product_from_line_item( $line_item );

			if ( is_a( $wc_product, WC_Product_Woosb::class ) &&
			     $this->bundle_have_different_taxes( $wc_product->get_id(), $line_items ) ) {
				$ids[] = $wc_product->get_id();
			}
		}

		return $ids;
	}

	/**
	 * Metoda sprawdza czy podprodukty zestawu z modułu WPC Product Bundles mają różne podatki
	 *
	 * @param int $bundle_id
	 * @param array $line_items
	 *
	 * @return boolean
	 */
	private function bundle_have_different_taxes( $bundle_id, $line_items ) {
		$first_tax = null;
		$other_tax = false;

		foreach ( $line_items as $line_item ) {
			$parent_id = $line_item->get_meta( '_woosb_parent_id' );

			if ( $parent_id == $bundle_id ) {
				$tax = $this->get_tax_from_line_item( $line_item );

				if ( $first_tax === null ) {
					$first_tax = $tax;
				} elseif ( $first_tax != $tax ) {
					$other_tax = true;
				}
			}
		}

		return $other_tax;
	}

	private function get_wp_tax( $bundle_id, $line_items ) {
		foreach ( $line_items as $line_item ) {
			$parent_id = $line_item->get_meta( '_woosb_parent_id' );

			if ( $parent_id == $bundle_id ) {
				return $this->get_tax_from_line_item( $line_item );
			}
		}

		return 0;
	}

	/**
	 * Metoda zwracająca podatek używany na pozycji zamówienia przekazanej w parametrze
	 *
	 * @param $line_item
	 * @param $check_amounts
	 *
	 * @return float|int|string
	 */
	private function get_tax_from_line_item( $line_item, $check_amounts = true ) {
		$taxes     = $line_item->get_taxes();
		$tax_total = array_shift( $taxes );
		$taxes     = array_filter(
			$tax_total,
			static function ( $value ) {
				return ! is_null( $value ) && $value !== '';
			}
		);
		$rate_id   = key( $taxes );

		if ( $check_amounts && $taxes[ $rate_id ] == 0 ) {
			return 0;
		}

		$tax       = $this->db->get_item_tax_rate( $rate_id );

		# W przypadku gdy usunięto tax rate z ustawień Woo rate_id zostaje przez co wcześniej pojawiał się błąd
		# wystawiania faktur na 0 ( bo nasz get_item_tax_rate zwracał 0 przy braku pozycji w bazie ).
		# Tutaj staramy się wyliczyć tax na podstawie dostępnych danych ( tylko w wyżej opisanym przypadku )
		if ( $rate_id && $tax == 0 && $line_item->get_total_tax() != 0 ) {
			$total_price = $line_item->get_subtotal() + $line_item->get_subtotal_tax();
			$net_price   = $line_item->get_subtotal();

			$tax = ( round( $total_price / $net_price, 2 ) - 1 ) * 100;
		}

		return $tax;
	}

	private function setup_positions() {
		$line_items                   = $this->order->get_items();
		$positions                    = array();
		$bundles_with_different_taxes = $this->getBundleIdsWithDifferentTaxes( $line_items );

		foreach ( $line_items as $line_item ) {
			$variation_id = $line_item->get_variation_id();

			$variantDescription = '';

			$wc_product = $this->get_product_from_line_item( $line_item );

			if ( $variation_id != 0 && get_option( 'woocommerce_firmlet_settings' )['incl_variations_info'] == 'yes' ) {
				$variantDescription .= wc_get_formatted_variation( $wc_product->get_variation_attributes(), true ) . PHP_EOL;

				if ( $variantDescription != '' && get_option( 'woocommerce_firmlet_settings' )['incl_prod_description'] == 'yes' ) {
					$variantDescription = str_replace( ', ', ',' . PHP_EOL, $variantDescription );

					$variantDescription .= PHP_EOL;
				}
			}

			if ( $line_item->meta_exists( '_woosb_parent_id' ) &&
			     ! in_array( $line_item->get_meta( '_woosb_parent_id' ), $bundles_with_different_taxes ) ) {
				continue;
			}

			if ( get_option( 'woocommerce_firmlet_settings' )['incl_meta'] == 'yes' ) {
				if ( $variantDescription != '' ) {
					$variantDescription .= PHP_EOL;
				}

				$metadata = $line_item->get_formatted_meta_data();

				foreach ( $metadata as $item ) {
					if ( $item instanceof stdClass ) {
						$variantDescription .= $item->display_key . ': ' . strip_tags( $item->display_value );
					} elseif ( is_array( $item ) ) {
						$variantDescription .= $item['display_key'] . ': ' . strip_tags( $item['display_value'] );
					}
				}
			}

			$tax                    = $this->get_tax_from_line_item( $line_item );
			$discount               = $this->get_discount( $line_item, $tax );
			$usedTax                = $line_item->get_total_tax();
			$woosb_prod             = is_a( $wc_product, WC_Product_Woosb::class );
			$show_as_text_separator = $woosb_prod && in_array( $wc_product->get_id(), $bundles_with_different_taxes );
			$delta_total            = 0;

			if ( $woosb_prod ) {
				if ($line_item->get_total() == 0) {
					$tax = $usedTax = $this->get_wp_tax($wc_product->get_id(), $line_items);
				} else {
					$usedTax = $tax;
				}
			} else {
				$order_total_price = $line_item->get_total() + $line_item->get_total_tax();
				$calculated_total_price = $this->get_total_price_gross( $line_item, $tax );
				$delta_total = $calculated_total_price - $order_total_price;

				# Różnica nigdy nie powinna być duża - na wszelki wypadek if
				if ( $delta_total > 0.3 ) {
					$delta_total = 0;
				}
			}

			$refunded_quantity = $this->order->get_qty_refunded_for_item($line_item->get_id(), $line_item->get_type());
			$refunded_total = $this->order->get_total_refunded_for_item($line_item->get_id(), $line_item->get_type());
			$refunded_total *= (1 + ($tax / 100));

			$quantity = $line_item->get_quantity() + $refunded_quantity;

			if ($quantity == 0) {
				continue;
			}

			$total_price_gross = $this->get_total_price_gross( $line_item, $tax ) + $discount - $delta_total - $refunded_total;
			if ($total_price_gross < 0) {
				$total_price_gross = 0;
			}

			if ( $total_price_gross == 0 && $usedTax == 0 ) {
				$tax = $usedTax = $this->get_tax_from_line_item( $line_item, false );
			}

			$tax = ( $usedTax == 0 ? $this->disabled_tax_rate() : round( (float) $tax, 2 ) );

			$name = apply_filters( 'vf_invoice_position_name', $line_item->get_name(), $line_item );

			$position_attributes = array(
				'kind'              => $show_as_text_separator ? 'text_separator' : '',
				'name'              => $this->remove_emoji( strip_tags( $name ) ),
				'quantity'          => $quantity,
				'total_price_gross' => $total_price_gross,
				'tax'               => $tax,
				'discount'          => $discount,
			);

			if ( get_option( 'woocommerce_firmlet_settings' )['incl_variations_info'] == 'yes' ) {
				$descr = '';

				$meta_data_list = $line_item->get_formatted_meta_data();

				foreach ($meta_data_list as $data) {
					$txt = $data->key . ': ' . $data->value;

					if (!str_contains($variantDescription, $txt)) {
						$descr .= $txt . PHP_EOL;
					}
				}

				$variantDescription .= $descr;
			}

			$item_batches = $line_item->get_meta( '_wcpbn_data' );

			if ( ! empty( $item_batches ) && is_array( $item_batches ) ) {
				foreach ( $item_batches as $batch ) {
					$batchId = $batch['batch_number'];

					if ( $batchId && strpos( $variantDescription, $batchId ) === false ) {
						$variantDescription .= "batch_number: $batchId" . PHP_EOL;
					}
				}
			}

			// case when product was deleted
			if ( $wc_product ) {
				$position_attributes['description'] = ( get_option( 'woocommerce_firmlet_settings' )['incl_prod_description'] == 'yes' ) ? $variantDescription . $wc_product->get_description() : $variantDescription;
				$position_attributes['code']        = $wc_product->get_sku();
			}

			$positions[] = $position_attributes;

			$total_price_gross = $line_item->get_total() * ( $usedTax == 0 ? 1 : ( 1 + ( round( (float) $tax, 2 ) / 100 ) ) );
			if ($total_price_gross < 0) {
				$total_price_gross = 0;
			}

			if ( $show_as_text_separator && $line_item->get_total() != 0 ) {
				$positions[] = array(
					'name'              => apply_filters( 'vf_invoice_reduction_name', 'Reduction', $line_item ),
					'quantity'          => 1,
					'total_price_gross' => $total_price_gross,
					'tax'               => ( $usedTax == 0 ? $this->disabled_tax_rate() : round( (float) $tax, 2 ) )
				);
			}
		}

		$this->positions = $positions;
		$this->phone     = $this->order->get_address()['phone'];
	}

	function remove_emoji( $string ) {
		$regex_alphanumeric = '/[\x{1F100}-\x{1F1FF}]/u';
		$clear_string       = preg_replace( $regex_alphanumeric, '', $string );

		$regex_symbols = '/[\x{1F300}-\x{1F5FF}]/u';
		$clear_string  = preg_replace( $regex_symbols, '', $clear_string );

		$regex_emoticons = '/[\x{1F600}-\x{1F64F}]/u';
		$clear_string    = preg_replace( $regex_emoticons, '', $clear_string );

		$regex_transport = '/[\x{1F680}-\x{1F6FF}]/u';
		$clear_string    = preg_replace( $regex_transport, '', $clear_string );

		$regex_supplemental = '/[\x{1F900}-\x{1F9FF}]/u';
		$clear_string       = preg_replace( $regex_supplemental, '', $clear_string );

		$regex_misc   = '/[\x{2600}-\x{26FF}]/u';
		$clear_string = preg_replace( $regex_misc, '', $clear_string );

		$regex_dingbats = '/[\x{2700}-\x{27BF}]/u';
		$clear_string   = preg_replace( $regex_dingbats, '', $clear_string );

		return $clear_string;
	}

	private function disabled_tax_rate() {
		$options = get_option( 'woocommerce_firmlet_settings' );

		if (isset($options['use_zero_tax_rate']) && $options['use_zero_tax_rate'] == 'yes') {
			return 0;
		}

		return 'disabled';
	}

	private function setup_buyer_name() {
		$details   = $this->order->get_address();
		$company   = $details['company'];
		$full_name = trim( $details['first_name'] . ' ' . $details['last_name'] );

		switch ( get_option( 'woocommerce_firmlet_settings' )['company_or_full_name'] ) {
			case 'company':
				$this->buyer_name = empty( $company ) ? $full_name : $company;
				break;
			case 'full_name':
				$this->buyer_name = empty( $full_name ) ? $company : $full_name;
				break;
			case 'company_and_full_name':
				if ( empty( $company ) ) {
					$this->buyer_name = $full_name;
				} elseif ( empty( $full_name ) ) {
					$this->buyer_name = $company;
				} else {
					$this->buyer_name = $company . ', ' . $full_name;
				}
				break;
			default:
				$logger = wc_get_logger();

				if ( $logger ) {
					$logger->debug( 'Company or full name is incorrect', array( 'source' => 'vosfactures' ) );
				}
				break;
		}
	}

	private function prepare_invoice_data() {
		$paid_date = get_post_meta( $this->order->get_id(), '_paid_date', true );

		if ( $paid_date != '' ) {
			$paid_date = date( 'Y-m-d', strtotime( $paid_date ) );
		}

		$oid                       = get_post_meta( $this->order->get_id(), '_order_number', true );
		$yithSequentialOrderNumber = get_post_meta( $this->order->get_id(), '_ywson_custom_number_order_complete', true );

		if ( $yithSequentialOrderNumber != null ) {
			$oid = $yithSequentialOrderNumber;
		}

		if ( ! $oid ) {
			$oid = $this->order->get_id();
		}

		$tax_no = get_post_meta( $this->order->get_id(), '_billing_tax', true );

		if ( $tax_no == '' ) {
			$tax_no = get_post_meta( $this->order->get_id(), '_billing_vat_number', true );
		}

		if ( $tax_no == '' ) {
			$tax_no = get_post_meta( $this->order->get_id(), '_billing_ice', true );
		}

		if ( $tax_no == '' ) {
			$tax_no = get_post_meta( $this->order->get_id(), 'vat_number', true );
		}

		$eu_vat_number = get_post_meta( $this->order->get_id(), '_billing_eu_vat_number', true );

		if ( $eu_vat_number != '' ) {
			$tax_no = $eu_vat_number;
		}

		$invoice_description = '';

		if ( get_option( 'woocommerce_firmlet_settings' )['incl_order_description'] == 'yes' ) {
			$invoice_description = $this->get_description();
		}

		if ( get_option( 'woocommerce_firmlet_settings' )['optional_order_note'] == 'yes' ) {
			if ($invoice_description != '') {
				$invoice_description .= ' ';
			}

			$invoice_description .= $this->order->get_customer_note();
		}

		$this->invoice_data = array(
			'kind'                            => $this->kind,
			'buyer_first_name'                => $this->address['first_name'],
			'buyer_last_name'                 => $this->address['last_name'],
			'buyer_name'                      => $this->buyer_name,
			'buyer_city'                      => $this->address['city'],
			'buyer_phone'                     => $this->phone,
			'buyer_country'                   => $this->country,
			'buyer_post_code'                 => $this->address['postcode'],
			'buyer_street'                    => (
			empty( $this->address['address_2'] )
				? $this->address['address_1']
				: $this->address['address_1'] . ', ' . $this->address['address_2']
			),
			'buyer_email'                     => $this->address['email'],
			'buyer_tax_no'                    => $tax_no,
			'positions'                       => $this->positions,
			'lang'                            => substr( $this->lang, 0, 2 ),
			'currency'                        => $this->currency,
			'buyer_company'                   => ! empty( $this->address['company'] ),
			'origin'                          => $this->api->get_origin(),
			'skip_buyer_last_name_validation' => true,
			'oid'                             => $oid,
			'paid_date'                       => $paid_date,
			'description'                     => $invoice_description,
		);
	}

	public function get_description() {
		$description = '';

		try {
			$notes = $this->order->get_customer_order_notes();

			foreach ( $notes as $note ) {
				if ( $description != '' ) {
					$description .= PHP_EOL;
				}
				$description .= $note->comment_content;
			}
		} catch ( Exception $e ) {
			$logger = wc_get_logger();

			if ( $logger ) {
				$logger->debug( 'Error while getting order description: ' . $e->getMessage() . ', Order' .
				                $this->order->get_id(), array( 'source' => 'vosfactures' ) );
			}
		}

		return $description;
	}

	public function setup_payment_type() {
		$payment_method = $this->order->get_payment_method();

		switch ( $payment_method ) {
			case 'cod':
				$payment_method_title = __( 'Cash on delivery', 'firmlet' );
				break;
			case 'bacs':
				$payment_method_title = 'transfer';
				break;
			case 'check':
				$payment_method_title = 'cheque';
				break;
			default:
				$payment_method_title = $this->order->get_payment_method_title();
		}

		$this->invoice_data['payment_type'] = $payment_method_title;
	}

	public function setup_delivery_address() {
		$address_to = $this->address;
		$delivery_address = $this->address_delivery;

		unset( $address_to['email'], $address_to['phone'], $delivery_address['email'], $delivery_address['phone'] );

		if ( get_option( 'woocommerce_firmlet_settings' )['delivery_address_always'] != 'yes' &&
			( $address_to == $delivery_address || ! array_filter( $delivery_address ) ) ) {
			return;
		}

		$this->invoice_data['use_delivery_address'] = true;
		$this->invoice_data['delivery_address']     =
			( ! empty( $this->address_delivery['company'] ) ? $this->address_delivery['company'] : $this->address_delivery['first_name'] . ' ' . $this->address_delivery['last_name'] )
			. PHP_EOL . $this->address_delivery['address_1'] . ( empty( $this->address_delivery['address_2'] ) ? '' : PHP_EOL . $this->address_delivery['address_2'] )
			. PHP_EOL . $this->address_delivery['postcode'] . ' ' . $this->address_delivery['city']
			. ', ' . wc()->countries->countries[ $this->country_delivery ]; // Country name in customer's language
	}

	/**
	 * Setting additional fields
	 *
	 * Note: if option is illegal, it is not added to the invoice data and a new line is added to log
	 */
	public function replace_invoice_data_fields() {
		$additional_fields = $this->api->get_additional_fields();

		if ( $additional_fields != null ) {
			foreach ( $additional_fields as $key => $value ) {
				if ( in_array( $key, $this->api->get_illegal_fields() ) ) {
					$error_message = "create invoice data: '" . $key . "' CANNOT be defined by additional_fields";
					$logger        = wc_get_logger();

					if ( $logger ) {
						$logger->debug( $error_message . 'Order' . (int) $this->order->get_id(), array( 'source' => 'vosfactures' ) );
					}
				} else {
					$this->invoice_data[ $key ] = $value;
				}
			}
		}
	}

	/**
	 * @return array
	 */
	private function prepare_final_invoice_data() {
		$this->prepare_invoice_data();
		$this->setup_payment_type();

		if ( $this->discount_applied ) {
			$this->invoice_data['discount_kind'] = 'amount';
			$this->invoice_data['show_discount'] = 'true';
		}

		$cards = $this->order->get_items( 'pw_gift_card' );

		if ( isset( $cards ) && $cards != '' && is_array( $cards ) ) {
			$card_numbers = [];

			foreach ( $cards as $card ) {
				$card_numbers[] = $card->get_card_number();
			}

			if ( count( $card_numbers ) > 0 ) {
				if ( $this->order->get_total() == 0 ) {
					$this->invoice_data['payment_type'] = 'Carte-cadeau numéro ' . implode( ', ', $card_numbers );
					$this->invoice_data['status']       = 'paid';
				} else {
					$this->invoice_data['payment_type'] = 'Carte-cadeau numéro ' .
					                                      implode( ', ', $card_numbers ) . ', ' .
					                                      $this->invoice_data['payment_type'];
				}
			}
		}

		$this->setup_delivery_address();

		if ( ! empty( get_option( 'woocommerce_firmlet_settings' )['department_id'] ) ) {
			$this->invoice_data['department_id'] = get_option( 'woocommerce_firmlet_settings' )['department_id'];
		}

		if ( ! empty( get_option( 'woocommerce_firmlet_settings' )['category_id'] ) ) {
			$this->invoice_data['category_id'] = get_option( 'woocommerce_firmlet_settings' )['category_id'];
		}

		$this->replace_invoice_data_fields();

		do_action( 'vosfactures_prepare_final_invoice_data', $this );

		return $this->invoice_data;
	}


	/**
	 * Used in FT only
	 *
	 * @param string $nip
	 *
	 * @return bool
	 */
	private function is_valid_nip( $nip ) {
		$nip = preg_replace( '/\D/', '', $nip );

		if ( strlen( $nip ) != 10 ) {
			return false;
		}

		$weights = array( 6, 5, 7, 2, 3, 4, 5, 6, 7 );
		$control = 0;

		for ( $i = 0; $i < 9; $i ++ ) {
			$control += $weights[ $i ] * $nip[ $i ];
		}

		$control %= 11;

		return $control == $nip[9];
	}

	/**
	 * @param float $price
	 * @param int $tax_rate
	 *
	 * @return float
	 */
	private function get_tax_amount( $price, $tax_rate ) {
		return round( ( $tax_rate / 100 ) * $price, 2 );
	}

	/**
	 * @param WC_Order_Item $line_item
	 * @param int $tax_rate
	 *
	 * @return float|int
	 */
	private function get_total_price_gross( $line_item, $tax_rate ) {
		$price = round( (float) $line_item->get_total(), 2 );

		$wc_product = $line_item->get_product();

		if ( $price === null ) {
			$price = $wc_product->get_regular_price();
		} elseif ( is_a( $wc_product, 'WC_Product_Woosb' ) ) {
			$line_items = $this->order->get_items();

			$price = round( (float) $line_item->get_total(), 2 );

			foreach ( $line_items as $line_item ) {
				if ( $line_item->meta_exists( '_woosb_parent_id' ) && $line_item->get_meta( '_woosb_parent_id' ) == $wc_product->get_id() ) {
					$price += round( (float) $line_item->get_total(), 2 );
				}
			}
		}

		$price += $this->get_tax_amount( $price, $tax_rate );

		return $price;
	}

	/**
	 * @param WC_Order_Item $line_item
	 * @param int $tax_rate
	 *
	 * @return float
	 */
	private function get_discount( $line_item, $tax_rate ) {
		$discount = $line_item->get_subtotal() - $line_item->get_total();
		$discount_from_prod = false;

		$wc_product = $line_item->get_product();

		if ( $discount == 0 && $wc_product && $wc_product->is_on_sale() && $line_item->get_total() == $wc_product->get_sale_price() ) {
			$discount = $wc_product->get_regular_price() - $wc_product->get_sale_price();
			$discount_from_prod = true;
		}

		if ( $wc_product && is_a( $wc_product, 'WC_Product_Woosb' ) ) {
			$line_items = $this->order->get_items();

			foreach ( $line_items as $line_item ) {
				if ( $line_item->meta_exists( '_woosb_parent_id' ) && $line_item->get_meta( '_woosb_parent_id' ) == $wc_product->get_id() ) {
					$discount += $line_item->get_subtotal() - $line_item->get_total();
				}
			}
		}

		if ( $discount > 0 ) {
			$this->discount_applied = true;

			if ( ! $discount_from_prod || ! wc_prices_include_tax() ) {
				$discount += $this->get_tax_amount( $discount, $tax_rate );
			}
		}

		return round( $discount, 2 );
	}
}
