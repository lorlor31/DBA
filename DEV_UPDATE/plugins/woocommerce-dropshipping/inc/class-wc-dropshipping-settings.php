<?php

if (!defined('ABSPATH')) {

	exit; // Exit if accessed directly } if ( ! class_exists( 'WC_DS_Settings' ) ) :
}

if (!class_exists('WC_DS_Settings')) :

	function wc_ds_add_settings($settings)
	{

		/**
		 * Settings class
		 */

		class WC_DS_Settings extends WC_Settings_Page
		{

			/**
			 * The request response
			 *
			 * @var array
			 */

			private $response = null;

			/**
			 * The error message
			 *
			 * @var string
			 */

			private $error_message = '';

			/**
			 * Setup settings class
			 */

			const SETTINGS_NAMESPACE = 'dropshipping';

			public function __construct()
			{

				$this->id = 'wc_dropship_settings';

				$this->label = __('Dropshipping', 'wc_dropship_settings');

				add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_page'), 20);

				add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
			}

			/**
			 * Get settings array
			 *
			 * @param string $current_section Optional. Defaults to empty string.
			 * @return array Array of settings
			 */
			public function get_dropshipping_settings($current_section = '')
			{

				$base_name = explode('/', plugin_basename(__FILE__));

				wp_enqueue_style('wc_dropshipping_checkout_style', plugins_url() . '/' . $base_name[0] . '/assets/css/custom.css');

				// Tab to update options //global $current_section;

				if (isset($_POST) && !empty($_POST)) {

					$options = get_option('wc_dropship_manager');

					foreach ($_POST as $key => $opt) {

						if ($key != 'submit') {
							$options[$key] = $_POST[$key];
						}
					}

					if (isset($_POST['supp_notification'])) {

						$options['supp_notification'] = '1';
					} else {

						$options['supp_notification'] = '0';
					}

					if (isset($_POST['csv_inmail'])) {

						$options['csv_inmail'] = '1';
					} else {

						$options['csv_inmail'] = '0';
					}

					if (isset($_POST['billing_phone'])) {

						$options['billing_phone'] = '1';
					} else {

						$options['billing_phone'] = '0';
					}

					if (isset($_POST['email_supplier'])) {

						$options['email_supplier'] = '1';
					} else {

						$options['email_supplier'] = '0';
					}

					if (isset($_POST['hide_suppliername'])) {

						$options['hide_suppliername'] = '1';
					} else {

						$options['hide_suppliername'] = '0';
					}

					if (isset($_POST['hide_suppliername_on_product_page'])) {

						$options['hide_suppliername_on_product_page'] = '1';
					} else {

						$options['hide_suppliername_on_product_page'] = '0';
					}

					if (isset($_POST['hideorderdetail_suppliername'])) {

						$options['hideorderdetail_suppliername'] = '1';
					} else {

						$options['hideorderdetail_suppliername'] = '0';
					}

					if (isset($_POST['full_information'])) {

						$options['full_information'] = '1';
					} else {

						$options['full_information'] = '0';
					}

					if (isset($_POST['show_logo'])) {

						$options['show_logo'] = '1';
					} else {

						$options['show_logo'] = '0';
					}

					if (isset($_POST['order_date'])) {

						$options['order_date'] = '1';
					} else {

						$options['order_date'] = '0';
					}

					if (isset($_POST['smtp_check'])) {

						$options['smtp_check'] = '1';
					} else {

						$options['smtp_check'] = '0';
					}

					if (isset($_POST['std_mail'])) {

						$options['std_mail'] = '1';
					} else {

						$options['std_mail'] = '0';
					}

					if (isset($_POST['checkout_order_number'])) {

						$options['checkout_order_number'] = '1';
					} else {

						$options['checkout_order_number'] = '0';
					}

					if (isset($_POST['show_pay_type'])) {

						$options['show_pay_type'] = '1';
					} else {

						$options['show_pay_type'] = '0';
					}

					if (isset($_POST['cnf_mail'])) {

						$options['cnf_mail'] = '1';
					} else {

						$options['cnf_mail'] = '0';
					}

					if (isset($_POST['cc_mail'])) {

						$options['cc_mail'] = '1';
					} else {

						$options['cc_mail'] = '0';
					}

					/** Staert Hide client info 5.6
					 * created at : 13/10/2022
					 * Updated at :
					 */
					if (isset($_POST['hide_client_info_Suppliers'])) {

						$options['hide_client_info_Suppliers'] = '1';
					} else {

						$options['hide_client_info_Suppliers'] = '0';
					}
					/** End Hide client info */

					/** Supplier Email Notifications */
					if (isset($_POST['view_order'])) {

						$options['view_order'] = '1';
					} else {

						$options['view_order'] = '0';
					}

					if (isset($_POST['renewal_email'])) {

						$options['renewal_email'] = '1';
					} else {

						$options['renewal_email'] = '0';
					}
					/** End Supplier Email Notifications */

					// Start hide contact_info_Suppliers
					if (isset($_POST['hide_contact_info_Suppliers'])) {

						$options['hide_contact_info_Suppliers'] = '1';
					} else {

						$options['hide_contact_info_Suppliers'] = '0';
					}

					// Customer Phone Number to Supplier
					if (isset($_POST['billing_phone'])) {

						$options['billing_phone'] = '1';
					} else {

						$options['billing_phone'] = '0';
					}
					// End Customer Phone Number to Supplier

					// End hide contact_info_Suppliers

					// store add_shipping_add
					if (isset($_POST['store_add_shipping_add'])) {

						$options['store_add_shipping_add'] = '1';
					} else {

						$options['store_add_shipping_add'] = '0';
					}
					// store add_shipping_add

					if (isset($_POST['from_name'])) {

						$options['from_name'] = $_POST['from_name'];
					} else {

						$options['from_name'] = '';
					}

					if (isset($_POST['from_email'])) {

						$options['from_email'] = $_POST['from_email'];
					} else {

						$options['from_email'] = '';
					}

					if (isset($_POST['hide_shipping_price'])) {

						$options['hide_shipping_price'] = '1';
					} else {

						$options['hide_shipping_price'] = '0';
					}

					if (isset($_POST['hide_tax'])) {

						$options['hide_tax'] = '1';
					} else {

						$options['hide_tax'] = '0';
					}

					if (isset($_POST['total_price'])) {

						$options['total_price'] = '1';
					} else {

						$options['total_price'] = '0';
					}

					if (isset($_POST['product_price'])) {

						$options['product_price'] = '1';
					} else {

						$options['product_price'] = '0';
					}

					if (isset($_POST['shipping'])) {

						$options['shipping'] = '1';
					} else {

						$options['shipping'] = '0';
					}

					if (isset($_POST['payment_method'])) {

						$options['payment_method'] = '1';
					} else {

						$options['payment_method'] = '0';
					}

					if (isset($_POST['cost_of_goods'])) {

						$options['cost_of_goods'] = '1';
					} else {

						$options['cost_of_goods'] = '0';
					}

					if (isset($_POST['show_gst_supplier_email'])) {

						$options['show_gst_supplier_email'] = '1';
					} else {

						$options['show_gst_supplier_email'] = '0';
					}

					if (isset($_POST['billing_address'])) {

						$options['billing_address'] = '1';
					} else {

						$options['billing_address'] = '0';
					}

					if (isset($_POST['shipping_address'])) {

						$options['shipping_address'] = '1';
					} else {

						$options['shipping_address'] = '0';
					}

					if (isset($_POST['product_image'])) {

						$options['product_image'] = '1';
					} else {

						$options['product_image'] = '0';
					}

					if (isset($_POST['store_name'])) {

						$options['store_name'] = '1';
					} else {

						$options['store_name'] = '0';
					}

					if (isset($_POST['store_address'])) {

						$options['store_address'] = '1';
					} else {

						$options['store_address'] = '0';
					}

					if (isset($_POST['complete_email'])) {

						$options['complete_email'] = '1';
					} else {

						$options['complete_email'] = '0';
					}

					if (isset($_POST['order_complete_link'])) {

						$options['order_complete_link'] = '1';
					} else {

						$options['order_complete_link'] = '0';
					}

					if (isset($_POST['type_of_package'])) {

						$options['type_of_package'] = '1';
					} else {

						$options['type_of_package'] = '0';
					}

					if (isset($_POST['customer_note'])) {

						$options['customer_note'] = '1';
					} else {

						$options['customer_note'] = '0';
					}

					if (isset($_POST['customer_email'])) {
						$options['customer_email'] = '1';
					} else {
						$options['customer_email'] = '0';
					}

					// Aliexpress Settings get POST

					if (isset($_POST['ali_cbe_enable_name'])) {

						$options['ali_cbe_enable_name'] = '1';
					} else {

						$options['ali_cbe_enable_name'] = '0';
					}

					if (isset($_POST['ali_cbe_price_rate_name'])) {

						$options['ali_cbe_price_rate_name'] = $_POST['ali_cbe_price_rate_name'];
					} else {

						$options['ali_cbe_price_rate'] = '';
					}

					if (isset($_POST['ali_cbe_price_rate_value_name'])) {

						if ($options['ali_cbe_price_rate_value_name'] < 1 || !is_numeric($options['ali_cbe_price_rate_value_name'])) {

							$options['ali_cbe_price_rate_value_name'] = 0;
						} else {

							$options['ali_cbe_price_rate_value_name'] = $_POST['ali_cbe_price_rate_value_name'];
						}
					} else {

						$options['ali_cbe_price_rate_value_name'] = 0;
					}

					/* Related to Price Calculator */

					if (isset($_POST['dynamic_profit_margin'])) {

						$options['dynamic_profit_margin'] = '1';
					} else {

						$options['dynamic_profit_margin'] = '0';
					}

					/* Related to Price Calculator End */

					update_option('wc_dropship_manager', $options);
				}

				/* Part 2 */

				$options = get_option('wc_dropship_manager');

				if (isset($options['supp_notification'])) {

					$supp_notification = $options['supp_notification'];
				} else {

					$supp_notification = '';
				}

				if (isset($options['csv_inmail'])) {

					$csvcheck = $options['csv_inmail'];
				} else {

					$csvcheck = '';
				}

				if (isset($_POST['packing_slip_header'])) {

					if ('' != $_POST['packing_slip_header']) {

						$options['packing_slip_header'] = $_POST['packing_slip_header'];
					} else {

						$options['packing_slip_header'] = '';
					}
				}

				if (isset($options['full_information'])) {

					$full_information = $options['full_information'];
				} else {

					$full_information = '';
				}

				if (isset($options['show_logo'])) {

					$show_logo = $options['show_logo'];
				} else {

					$show_logo = '';
				}

				if (isset($options['order_date'])) {

					$order_date = $options['order_date'];
				} else {

					$order_date = '';
				}

				if (isset($options['smtp_check'])) {

					$smtp_check = $options['smtp_check'];
				} else {

					$smtp_check = '';
				}

				if (isset($options['std_mail'])) {

					$std_mail = $options['std_mail'];
				} else {

					$std_mail = '';
				}

				if (isset($options['checkout_order_number'])) {

					$checkout_order_number = $options['checkout_order_number'];
				} else {

					$checkout_order_number = 0;
				}

				if (isset($options['show_pay_type'])) {

					$show_pay_type = $options['show_pay_type'];
				} else {

					$show_pay_type = '';
				}

				if (isset($options['cnf_mail'])) {

					$cnf_mail = $options['cnf_mail'];
				} else {

					$cnf_mail = '';
				}

				if (isset($options['cc_mail'])) {

					$cc_mail = $options['cc_mail'];
				} else {

					$cc_mail = '';
				}

				/** hide client_info_Suppliers */
				if (isset($options['hide_client_info_Suppliers'])) {

					$hide_client_info_Suppliers = $options['hide_client_info_Suppliers'];
				} else {

					$hide_client_info_Suppliers = '';
				}
				// hide client_info_Suppliers

				// Supplier Email Notifications
				if (isset($options['view_order'])) {

					$view_order = $options['view_order'];
				} else {

					$view_order = '';
				}

				if (isset($options['renewal_email'])) {

					$renewal_email = $options['renewal_email'];
				} else {

					$renewal_email = '';
				}
				// End Supplier Email Notifications

				// hide contact_info_Suppliers
				if (isset($options['hide_contact_info_Suppliers'])) {

					$hide_contact_info_Suppliers = $options['hide_contact_info_Suppliers'];
				} else {

					$hide_contact_info_Suppliers = '';
				}
				// hide contact_info_Suppliers

				// Customer Phone Number to Supplier
				if (isset($options['billing_phone'])) {

					$billing_phone = $options['billing_phone'];
				} else {

					$billing_phone = '';
				}
				// End Customer Phone Number to Supplier

				// store add_shipping_add
				if (isset($options['store_add_shipping_add'])) {

					$store_add_shipping_add = $options['store_add_shipping_add'];
				} else {

					$store_add_shipping_add = '';
				}
				// store add_shipping_add

				if (isset($options['from_name'])) {

					$from_name = $options['from_name'];
				} else {

					$from_name = '';
				}

				if (isset($options['from_email'])) {

					$from_email = $options['from_email'];
				} else {

					$from_email = '';
				}

				if (isset($options['hide_shipping_price'])) {

					$hide_shipping_price = $options['hide_shipping_price'];
				} else {

					$hide_shipping_price = '';
				}

				if (isset($options['hide_tax'])) {

					$hide_tax = $options['hide_tax'];
				} else {

					$hide_tax = '';
				}

				if (isset($options['total_price'])) {

					$total_price = $options['total_price'];
				} else {

					$total_price = '';
				}

				if (isset($options['product_price'])) {

					$product_price = $options['product_price'];
				} else {

					$product_price = '';
				}

				if (isset($options['shipping'])) {

					$shipping = $options['shipping'];
				} else {

					$shipping = '';
				}

				if (isset($options['cost_of_goods'])) {

					$cost_of_goods = $options['cost_of_goods'];
				} else {

					$cost_of_goods = '';
				}

				if (isset($options['show_gst_supplier_email'])) {

					$show_gst_supplier_email = $options['show_gst_supplier_email'];
				} else {

					$show_gst_supplier_email = '';
				}

				if (isset($options['billing_address'])) {

					$billing_address = $options['billing_address'];
				} else {

					$billing_address = '';
				}

				if (isset($options['billing_phone'])) {

					$billing_phone = $options['billing_phone'];
				} else {

					$billing_phone = '';
				}

				if (isset($options['email_supplier'])) {

					$email_supplier = $options['email_supplier'];
				} else {

					$email_supplier = '';
				}

				if (isset($options['hide_suppliername'])) {

					$hide_suppliername = $options['hide_suppliername'];
				} else {

					$hide_suppliername = '';
				}

				if (isset($options['hide_suppliername_on_product_page'])) {

					$hide_suppliername_on_product_page = $options['hide_suppliername_on_product_page'];
				} else {

					$hide_suppliername_on_product_page = '';
				}

				if (isset($options['hideorderdetail_suppliername'])) {

					$hideorderdetail_suppliername = $options['hideorderdetail_suppliername'];
				} else {

					$hideorderdetail_suppliername = '';
				}

				if (isset($options['shipping_address'])) {

					$shipping_address = $options['shipping_address'];
				} else {

					$shipping_address = '';
				}

				if (isset($options['product_image'])) {

					$product_image = $options['product_image'];
				} else {

					$product_image = '';
				}

				if (isset($options['store_name'])) {

					$store_name = $options['store_name'];
				} else {

					$store_name = '';
				}

				if (isset($options['store_address'])) {

					$store_address = $options['store_address'];
				} else {

					$store_address = '';
				}

				if (isset($options['complete_email'])) {

					$complete_email = $options['complete_email'];
				} else {

					$complete_email = '';
				}

				if (isset($options['order_complete_link'])) {

					$order_complete_link = $options['order_complete_link'];
				} else {

					$order_complete_link = '';
				}

				if (isset($options['type_of_package'])) {

					$type_of_package = $options['type_of_package'];
				} else {

					$type_of_package = '';
				}

				if (isset($options['customer_note'])) {

					$customer_note = $options['customer_note'];
				} else {

					$customer_note = '';
				}

				if (isset($options['customer_email'])) {
					$customer_email = $options['customer_email'];
				} else {
					$customer_email = '';
				}

				if ($customer_email == '1') {
					$customer_email = ' checked="checked" ';
				} else {
					$customer_email = ' ';
				}

				// Aliexpress Settings for setting variable creation

				if (isset($options['ali_cbe_enable_name'])) {

					$ali_cbe_enable_setting = $options['ali_cbe_enable_name'];
				} else {

					$ali_cbe_enable_setting = '';
				}

				if (isset($options['ali_cbe_price_rate_name'])) {
					$ali_cbe_price_rate_selected_1 = '';
					$ali_cbe_price_rate_selected_2 = '';

					if ($options['ali_cbe_price_rate_name'] == 'ali_cbe_price_rate_percent_offset') {

						$ali_cbe_price_rate_selected_1 = 'selected';

						$ali_cbe_price_rate_selected_2 = '';
					} else {

						$ali_cbe_price_rate_selected_1 = '';

						$ali_cbe_price_rate_selected_2 = 'selected';
					}
				}

				/* Related to Price Calculator */

				if (isset($_POST['fee_percent_value'])) {

					if ('' != $_POST['fee_percent_value']) {

						$options['fee_percent_value'] = $_POST['fee_percent_value'];
					} else {

						$options['fee_percent_value'] = '';
					}
				}

				if (isset($_POST['fee_doller_value'])) {

					if ('' != $_POST['fee_doller_value']) {

						$options['fee_doller_value'] = $_POST['fee_doller_value'];
					} else {

						$options['fee_doller_value'] = '';
					}
				}

				if (isset($_POST['profit_doller_value'])) {

					if ('' != $_POST['profit_doller_value']) {

						$options['profit_doller_value'] = $_POST['profit_doller_value'];
					} else {

						$options['profit_doller_value'] = '';
					}
				}

				if (isset($_POST['profit_percent_value'])) {

					if ('' != $_POST['profit_percent_value']) {

						$options['profit_percent_value'] = $_POST['profit_percent_value'];
					} else {

						$options['profit_percent_value'] = '';
					}
				}

				if (isset($options['dynamic_profit_margin'])) {

					$dynamic_profit_margin_setting = $options['dynamic_profit_margin'];
				} else {

					$options['dynamic_profit_margin'] = '';
				}

				/*
				 Pricing and Profit Calculation staeted here */
				// $Fee = [ {100+ (8.5% *100) + 0.5 + 0.30} x 100 / (100 - 2.9 ) ] - {100+ (8.5% * 100) + 0.5 + 0.30};
				// $prft_prcnt_val = $options['profit_percent_value'];
				// $prft_dolr_val = $options['profit_doller_value'];
				// $fee_prcnt_val = $options['fee_percent_value'];
				// $fee_dolr_val = $options['fee_doller_value'];

				if (isset($options['profit_percent_value'])) {
					$profit_percent_value = $options['profit_percent_value'];
				} else {
					$profit_percent_value = '';
				}

				if (!empty($profit_percent_value) || '' != $profit_percent_value) {

					$prft_prcnt_val = $profit_percent_value;
				} else {

					$prft_prcnt_val = 0;
				}

				if (isset($options['profit_doller_value'])) {
					$profit_doller_value = $options['profit_doller_value'];
				} else {
					$profit_doller_value = '';
				}

				if (!empty($profit_doller_value) || '' != $profit_doller_value) {

					$prft_dolr_val = $profit_doller_value;
				} else {

					$prft_dolr_val = 0;
				}

				if (isset($options['fee_percent_value'])) {
					$fee_percent_value = $options['fee_percent_value'];
				} else {
					$fee_percent_value = '';
				}

				if (!empty($fee_percent_value) || '' != $fee_percent_value) {

					$fee_prcnt_val = $fee_percent_value;
				} else {

					$fee_prcnt_val = 0;
				}

				if (isset($options['fee_doller_value'])) {
					$fee_doller_value = $options['fee_doller_value'];
				} else {
					$fee_doller_value = '';
				}

				if (!empty($fee_doller_value) || '' != $fee_doller_value) {

					$fee_dolr_val = $fee_doller_value;
				} else {

					$fee_dolr_val = 0;
				}

				$pcnt_profit = $prft_prcnt_val / 100 * 100;

				$all_prft_some = 100 + $pcnt_profit + $prft_dolr_val + $fee_dolr_val;
				$final_some = $all_prft_some / 100 * 100;

				$devide_left = 100 - $fee_prcnt_val;

				$right_calculesn = number_format($final_some * 100 / $devide_left, 2);
				$final_prcnt_fee = number_format($right_calculesn - $final_some, 2);

				// if (isset($options['ali_cbe_price_rate_value_name'])) { // $ali_cbe_price_rate_value_setting = $options['ali_cbe_price_rate_value_name']; // } else { // $ali_cbe_price_rate_value_setting = ''; // } // For Checked Checkbox

				/* Part 3 */

				if ($options['dynamic_profit_margin'] == '1') {

					$dynamic_profit_margin_setting = ' checked="checked" ';
				} else {

					$dynamic_profit_margin_setting = ' ';
				}
				/* Related to Price Calculator End */

				if ($csvcheck == '1') {

					$csvInMail = ' checked="checked" ';
				} else {

					$csvInMail = ' ';
				}

				if ($supp_notification == '1') {

					$supp_notification_attr = ' checked="checked" ';
				} else {

					$supp_notification_attr = ' ';
				}

				if ($full_information == '1') {

					$checkfull = ' checked="checked" ';

					$disabledPdfOptions = '';
				} else {

					$checkfull = ' ';

					$disabledPdfOptions = 'disabled';
				}

				if ($show_logo == '1') {

					$logoshow = ' checked="checked" ';

					$show_logo_option = 'style="display:block"';
				} else {

					$logoshow = ' ';

					$show_logo_option = 'style="display:none"';
				}

				if ($order_date == '1') {

					$date_order = ' checked="checked" ';
				} else {

					$date_order = ' ';
				}

				if ($smtp_check == '1') {

					$check_smtp = ' checked="checked" ';
				} else {

					$check_smtp = ' ';
				}

				if ($std_mail == '1' || $std_mail == '') {

					$std_mail = ' checked="checked" ';
				} else {

					$std_mail = ' ';
				}

				if ($checkout_order_number == '1') {

					$checkout_order_number = ' checked="checked" ';
				} else {

					$checkout_order_number = ' ';
				}

				if ($show_pay_type == '1' || $show_pay_type == '') {

					$show_pay_type = ' checked="checked" ';
				} else {

					$show_pay_type = ' ';
				}

				if ($cnf_mail == '1') {

					$cnf_mail = ' checked="checked" ';
				} else {

					$cnf_mail = ' ';
				}

				if ($cc_mail == '1' || $cc_mail == '') {

					$cc_mail = ' checked="checked" ';
				} else {

					$cc_mail = ' ';
				}

				// hide client_info_Suppliers
				if ($hide_client_info_Suppliers == '1' || $hide_client_info_Suppliers == '') {

					$hide_client_info_Suppliers = ' checked="checked" ';
				} else {

					$hide_client_info_Suppliers = ' ';
				}
				// hide client_info_Suppliers

				// Supplier Email Notifications
				if ($view_order == '1' || $view_order == '') {

					$view_order = ' checked="checked" ';
				} else {

					$view_order = ' ';
				}

				if ($renewal_email == '1' || $renewal_email == '') {

					$renewal_email = ' checked="checked" ';
				} else {

					$renewal_email = ' ';
				}
				// End Supplier Email Notifications

				// hide contact_info_Suppliers
				if ($hide_contact_info_Suppliers == '1' || $hide_contact_info_Suppliers == '') {

					$hide_contact_info_Suppliers = ' checked="checked" ';
				} else {

					$hide_contact_info_Suppliers = ' ';
				}
				// hide contact_info_Suppliers

				// Customer Phone Number to Supplier
				if ($billing_phone == '1' || $billing_phone == '') {

					$billing_phone = ' checked="checked" ';
				} else {

					$billing_phone = ' ';
				}
				// End Customer Phone Number to Supplier

				// store add_shipping_add
				if ($store_add_shipping_add == '1' || $store_add_shipping_add == '') {

					$store_add_shipping_add = ' checked="checked" ';
				} else {

					$store_add_shipping_add = ' ';
				}
				// store add_shipping_add

				if ($hide_shipping_price == '1' || $hide_shipping_price == '') {

					$hide_shipping_price = ' checked="checked" ';
				} else {

					$hide_shipping_price = ' ';
				}

				if ($hide_tax == '1') {

					$hide_tax = ' checked="checked" ';
				} else {

					$hide_tax = ' ';
				}

				if ($total_price == '1') {

					$total_price = ' checked="checked" ';
				} else {

					$total_price = ' ';
				}

				if ($product_price == '1') {

					$price_product = ' checked="checked" ';

					$product_price_option = 'style="display:block"';
				} else {

					$price_product = ' ';

					$product_price_option = 'style="display:hide"';
				}

				if ($shipping == '1') {

					$product_shipping = ' checked="checked" ';

					$show_shipping_information_option = 'style="display:block"';
				} else {

					$product_shipping = ' ';

					$show_shipping_information_option = 'style="display:hide"';
				}

				if ($cost_of_goods == '1' || $cost_of_goods == '') {

					$cost_of_goods = ' checked="checked" ';
				} else {

					$cost_of_goods = ' ';
				}

				if ($show_gst_supplier_email == '1' || $show_gst_supplier_email == '') {

					$show_gst_supplier_email = ' checked="checked" ';
				} else {

					$show_gst_supplier_email = ' ';
				}

				if ($billing_address == '1') {

					$address_billing = ' checked="checked" ';

					$billing_address_option = 'style="display:block"';
				} else {

					$address_billing = ' ';

					$billing_address_option = 'style="display:hide"';
				}

				if ($email_supplier == '1') {

					$supplier_email = ' checked="checked" ';
				} else {

					$supplier_email = ' ';
				}

				if ($hide_suppliername == '1') {

					$suppliername_hide = ' checked="checked" ';
				} else {

					$suppliername_hide = ' ';
				}

				if ($hide_suppliername_on_product_page == '1') {

					$hide_suppliername_on_product_page = ' checked="checked" ';
				} else {

					$hide_suppliername_on_product_page = ' ';
				}

				if ($hideorderdetail_suppliername == '1') {

					$suppliername_hideorderdetail = ' checked="checked" ';
				} else {

					$suppliername_hideorderdetail = ' ';
				}

				if ($shipping_address == '1') {

					$address_shipping = ' checked="checked" ';

					$shipping_address_option = 'style="display:block"';
				} else {

					$address_shipping = ' ';

					$shipping_address_option = 'style="display:hide"';
				}

				if ($product_image == '1') {

					$image_product = ' checked="checked" ';

					$product_image_option = 'style="display:block"';
				} else {

					$image_product = ' ';

					$product_image_option = 'style="display:hide"';
				}

				if ($store_name == '1') {

					$name_store = ' checked="checked" ';
				} else {

					$name_store = ' ';
				}

				if ($store_address == '1') {

					$address_store = ' checked="checked" ';
				} else {

					$address_store = ' ';
				}

				if ($complete_email == '1') {

					$email_complete = ' checked="checked" ';
				} else {

					$email_complete = ' ';
				}

				if ($order_complete_link == '1') {

					$link_complete_order = ' checked="checked" ';
				} else {

					$link_complete_order = ' ';
				}

				if ($type_of_package == '1') {

					$type_of_package = ' checked="checked" ';

					$type_of_package_option = 'style="display:block"';
				} else {

					$type_of_package = ' ';

					$type_of_package_option = 'style="display:hide"';
				}

				if ($customer_note == '1') {

					$customer_note = ' checked="checked" ';
				} else {

					$customer_note = ' ';
				}

				// Aliexpress Settings for checkbox value

				if ($ali_cbe_enable_setting == '1') {

					$ali_cbe_enable_checkbox = ' checked="checked" ';
				} else {

					$ali_cbe_enable_checkbox = ' ';
				}

				if (isset($options['ali_cbe_price_rate_value_name'])) {

					if ($options['ali_cbe_price_rate_value_name'] < 1 || !is_numeric($options['ali_cbe_price_rate_value_name'])) {

						$options['ali_cbe_price_rate_value_name'] = 0;
					}
				}

				$woocommerce_url = plugins_url() . '/woocommerce/';

				// echo '<form method="post" id="mainform" action="" enctype="multipart/form-data">';


					echo '<ul class="wc-dropship-setting-tabs">

                    <li data-id="general_settings" class="active">' . __('AliExpress Settings', 'woocommerce-dropshipping') . '</li>

                    <li data-id="supplier_email_notifications">' . __('Supplier Email Notifications', 'woocommerce-dropshipping') . '</li>

                    <li data-id="packing_slips">' . __('Packing Slips', 'woocommerce-dropshipping') . '</li>

                    <li data-id="customised_supplier_emails">' . __('Customised Supplier Emails', 'woocommerce-dropshipping') . '</li>

                    <li data-id="smtp_options">' . __('SMTP Options', 'woocommerce-dropshipping') . '</li>

                    <li data-id="price_calculator_options" id="prices_cal">' . __('Price Calculator', 'woocommerce-dropshipping') . '</li>

				</ul>';


				echo '<div class="drop-setting-section active" id="general_settings">';

				echo '<h3>' . __('AliExpress Chrome Browser Extension (CBE) Settings', 'woocommerce-dropshipping') . '</h3>';

				echo '<table>

					<tr>

					<td><h4><label for="ali_cbe_enable_name">' . __('Enable Support for the AliExpress CBE:', 'woocommerce-dropshipping') . '</label></h4></td>

					<td>
					<span>

						<td><input name="ali_cbe_enable_name" id="ali_cbe_enable_name" type="checkbox" ' . $ali_cbe_enable_checkbox . ' /></td>

					</span>

					<td>
					</tr>

				</table>';


				if (isset($ali_cbe_enable_setting)) {

					if ($ali_cbe_enable_setting == '1') {

						echo '<table>
								<tr>
									<td><h4>' . __( 'Generate AliExpress API Key:', 'woocommerce-dropshipping' ) . '</h4></td>
									<td>
										<span>
											<button type="button" id="generate_ali_key" class="button-primary">' . __( 'Generate AliExpress API Key', 'woocommerce-dropshipping' ) . '</button>
										</span>
									<td>
								</tr>
							</table>';


						echo '<table>

                            <tr id="hide_key">
                                <td id="ali_api_key"></td>
                            </tr>

    					</table>';

						echo '<table>

                            <tr>

                                <td><h4>' . __( 'Price Markup Method:', 'woocommerce-dropshipping' ) . '</h4></td>

                                <td><img class="help_tip" data-tip="' . esc_attr__( 'This setting controls whether the prices listed for products on your WooCommerce store are marked up by a given percentage or by a fixed amount when compared to the AliExpress supplier&apos;s prices', 'woocommerce-dropshipping' ) . '" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td>

                                    <span>
                                        <td>
                                            <select name="ali_cbe_price_rate_name">';

						if (isset($options['ali_cbe_price_rate_name'])) {
							$ali_cbe_price_rate_selected_1 = '';
							$ali_cbe_price_rate_selected_2 = '';

							if ('ali_cbe_price_rate_percent_offset' == $options['ali_cbe_price_rate_name']) {

								$ali_cbe_price_rate_selected_1 = 'selected';

								$ali_cbe_price_rate_selected_2 = '';
							} else {

								$ali_cbe_price_rate_selected_1 = '';

								$ali_cbe_price_rate_selected_2 = 'selected';
							}
						}
						echo '<option value="ali_cbe_price_rate_percent_offset" ' . $ali_cbe_price_rate_selected_1 . '>' . __( 'Percentage Offset', 'woocommerce-dropshipping' ) . '</option>

						<option value="ali_cbe_fixed_price_offset"' . $ali_cbe_price_rate_selected_2 . '>Fixed Amount Offset</option>';


						echo '   </select>
                                        </td>
                                    </span>
                                <td>
                            </tr>
                        </table>';

						echo '<table>
								<tr>
									<td><h4>' . __( 'Markup Offset Value:', 'woocommerce-dropshipping' ) . '</h4></td>
									<td><img class="help_tip" data-tip="' . esc_attr__( 'This setting will either contain a percentage or fixed amount based on the chosen price markup method above', 'woocommerce-dropshipping' ) . '" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>
									<td>
										<span>
											<td><input name="ali_cbe_price_rate_value_name" value="' . esc_attr( @$options['ali_cbe_price_rate_value_name'] ) . '" size="5" /></td>
										</span>
									<td>
								</tr>
							</table>';

					}
				}

				echo '</div>';

				echo '<div class="drop-setting-section" id="supplier_email_notifications">';

				echo '<h3>' . __( 'Supplier Email Notifications', 'woocommerce-dropshipping' ) . '</h3>

						<p>' . __( 'Supplier Email Notifications', 'woocommerce-dropshipping' ) . '</p>

						<p>' . __( 'When an order&apos;s status switches to processing, emails are sent to each supplier to notify them to ship their products.', 'woocommerce-dropshipping' ) . '<br>' . __( ' You can set a custom message for the suppliers in the box below to be included in these emails.', 'woocommerce-dropshipping' ) . '</p>

						<table>
							<tr>
								<td><label for="email_order_note">' . __( 'Email order note:', 'woocommerce-dropshipping' ) . '</label></td>
								<td><img class="help_tip" data-tip="This note will appear on emails that suppliers will receive with your order notifications and HTML is allowed." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

								<td><textarea name="email_order_note" id="email_order_note" cols="90" rows="5" >' . __( @$options['email_order_note'] , 'woocommerce-dropshipping' ). '</textarea></td>
							</tr>
						</table>';


				echo '<p></p>

                            <table>
                                <tr>
                                    <td><label for="view_order">' . __( 'Include \'View order\' link in suppliers email:', 'woocommerce-dropshipping' ) . '</label></td>

                                    <td><img class="help_tip" data-tip="If checked this option will not send subscription renewal email\'s to suppliers." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

									<td><input name="view_order" id="view_order" class="view_order" type="checkbox" ' . $view_order . '  /></td>
                                </tr>

                            </table>

                            <table>
                                <tr>
                                    <td><label for="renewal_email">' . __( 'Do not send renewal email to suppliers:', 'woocommerce-dropshipping' ) . '</label></td>

                                    <td><img class="help_tip" data-tip="If checked this option will not send subscription renewal email\'s to suppliers." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

									<td><input name="renewal_email" id="renewal_email" class="view_order" type="checkbox" ' . $renewal_email . '  /></td>

                                </tr>

                            </table>';

				echo '<h3>' . __( '.CSV File Inventory Update Settings', 'woocommerce-dropshipping' ) . '</h3>

                            <p>' . __( '.These options relate to how your store processes data imported from CSV spreadsheet files, if you receive them from your supplier', 'woocommerce-dropshipping' ) . '</p>

                            <table>

                                <tr>

                                    <td><label for="inventory_pad" style="margin-left: -2px;">' . __( 'Inventory Buffer:', 'woocommerce-dropshipping' ) . '</label></td>

                                    <td><img class="help_tip" data-tip="Set this to zero if you want to directly use the inventory numbers your supplier gives you, or higher if you want to ensure that they don&apos;t sell out of their products before you make a sale." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>
                                    <td><input name="inventory_pad" value="' . @$options['inventory_pad'] . '" size="1" /></td>
                                </tr>
							</table>
							' . __( 'If the supplier&apos;s stock falls below this number on an imported spreadsheet, the item will be considered out of stock in your store.', 'woocommerce-dropshipping' ) . '';

				echo '</div>';

				echo '<div class="drop-setting-section" id="packing_slips">';

				echo '<h3>' . __( 'Packing Slips', 'woocommerce-dropshipping' ) . '</h3>

					   <table>
                            <tr>
                                <td><input name="full_information" id="full_information" class="fullinfo miscellaneous_packing_slip_options_master_checkbox" type="checkbox" ' . $checkfull . ' /></td>

                                <td><label for="full_information"><b>' . __( 'Attach PDF to supplier Email', 'woocommerce-dropshipping' ) . '</b></label></td>
                                </tr>
                            </table>
    					<br/><br/>

    					<div class="packing-slip-sections">

    					<h4>' . __( 'Header', 'woocommerce-dropshipping' ) . '</h4>

    					<table>
                            <tr>
                                <td><label for="packing_slip_header">' . __( 'Option to view packing slip.', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="This will be the custom title of the packing slip" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="packing_slip_header" value="' .__( @$options['packing_slip_header'], 'woocommerce-dropshipping' ). '" size="100" /></td>
                            </tr>
                        </table>
                        <p></p>

                        <table>

                            <tr>

                                <td><input name="show_logo" id="show_logo" class="miscellaneous_packing_slip_options_checkbox_false" data-id="show_logo" type="checkbox" ' . $logoshow . '  /></td>

                                <td><label for="show_logo">' . __( 'Show logo in the header.', 'woocommerce-dropshipping' ) . '</label></td>
                            </tr>
                        </table>

                        <p></p>

                        <div class="show_logo" ' . $show_logo_option . '>

                            <p style="margin-left:50px;"><b>' . __( 'NOTE:', 'woocommerce-dropshipping' ) . '</b> ' . __( 'For best results, please keep logo dimensions within 200x60 px', 'woocommerce-dropshipping' ) . '</p>

                            <table style="margin-left:50px;">

                                <tr>

                                    <td style="width:150px"><label for="packing_slip_url_to_logo" >' . __( 'Url to Logo:', 'woocommerce-dropshipping' ) . '</label></td>

                                    <td><img class="help_tip" data-tip="Please specify the URL where your company&apos;s logo can be found" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                    <td><input name="packing_slip_url_to_logo" value="' . @$options['packing_slip_url_to_logo'] . '" size="75" /></td>
                                </tr>
                            </table>
                            <p></p>

                            <table style="margin-left:50px;">
                                <tr>
                                    <td style="width:150px"><label for="packing_slip_url_to_logo_width" >' . __( 'Logo Width:', 'woocommerce-dropshipping' ) . '</label></td>

                                    <td><img class="help_tip" data-tip="Please specify the width of your company logo in pixels" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                    <td><input name="packing_slip_url_to_logo_width" value="' . @$options['packing_slip_url_to_logo_width'] . '" size="5" />
                                    </td>
                                </tr>
                            </table>
                        </div>';

				echo '<p></p>

                            <table>
                                <tr>
                                    <td><input name="order_date" id="show_order_date" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $date_order . '  /></td>

                                    <td><label for="show_order_date">' . __( 'Show order date beside order number.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>
                            </table>';

				echo '<p></p>

                            <table>
                                <tr>
                                    <td><input name="shipping" id="show_shipping_information" data-id="show_shipping_information" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $product_shipping . '  /></td>

                                    <td><label for="show_shipping_information">' . __( 'Show shipping information.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>
                            </table>';

				echo '<p></p>

                            <div class="inner-toggle show_shipping_information" ' . $show_shipping_information_option . '>

                                <p style="margin-left:50px;"><b>' . __( 'NOTE:', 'woocommerce-dropshipping' ) . '</b> ' . __( 'For best results, please make sure that any custom terms or phrases listed below are kept to a reasonable length. If your terms are too long, it may cause text wrapping and alignment issues with your packing slips.', 'woocommerce-dropshipping' ) . '</p>

                                <table style="margin-left:50px;">
                                    <tr>
                                        <td style="width:250px"><label for="dropship_chosen_shipping_method" >' . __( 'Chosen Shipping Method Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please specify chosen Shipping Method Label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><input name="dropship_chosen_shipping_method" value="' . __( @$options['dropship_chosen_shipping_method'], 'woocommerce-dropshipping' ) . '" size="30" maxlength="50" /></td>
                                    </tr>
                                </table>
                                <p></p>

                                <table style="margin-left:50px;">
                                    <tr>
                                        <td style="width:250px"><label for="dropship_payment_type" >' . __( 'Payment Type Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please specify chosen Payment Type Label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><input name="dropship_payment_type" value="' . __( @$options['dropship_payment_type'], 'woocommerce-dropshipping' ) . '" size="30" maxlength="50"/></td>
                                    </tr>
                                </table>
                            </div>';

				echo '<p></p>

                            <table>
                                <tr>
                                    <td><input name="customer_note" id="show_customer_note" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $customer_note . '  /></td>

                                    <td><label for="show_customer_note">' . __( 'Display the "Customer Note" into the Dropshipper packing slip.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>
                            </table>
                        </div>';

				echo '<br/><br/>

                            <div class="packing-slip-sections">
                                <h4>' . __( 'Products', 'woocommerce-dropshipping' ) . '</h4>

                                <table>
                                    <tr>
                                        <td><input name="product_image" id="product_image" data-id="product_image" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $image_product . '  /></td>

                                        <td><label for="product_image">' . __( 'Show product thumbnail image.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>
                                <p></p>

                                <div class="inner-toggle product_image" ' . $product_image_option . '>

                                    <table>
                                        <tr>
                                            <td style="width:150px"><label for="dropship_image" >' . __( 'Image Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                            <td><img class="help_tip" data-tip="Please specify Image Label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                            <td><input name="dropship_image" value="' . __( @$options['dropship_image'] , 'woocommerce-dropshipping' ). '" size="30" maxlength="50" /></td>
                                        </tr>
                                    </table>
                                </div>';

				echo '<p></p>

                                <table>
                                    <tr>
                                    <td style="width:150px"><label for="dropship_sku" >' . __( 'SKU Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                    <td><img class="help_tip" data-tip="Please specify SKU label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                    <td><input name="dropship_sku" value="' .  __( @$options['dropship_sku'], 'woocommerce-dropshipping' ) . '" size="30" maxlength="50" /></td>
                                    </tr>
                                </table>

                                <table>
                                    <tr>
                                        <td style="width:150px"><label for="dropship_product" >' . __( 'Product Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please specify Product label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><input name="dropship_product" value="' . __( @$options['dropship_product'] , 'woocommerce-dropshipping' ). '" size="30" maxlength="50" /></td>
                                    </tr>
                                </table>

                                <table>
                                    <tr>
                                        <td style="width:150px"><label for="dropship_quantity">' . __( 'Quantity Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please specify Quantity label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><input name="dropship_quantity" value="' .  __( @$options['dropship_quantity'], 'woocommerce-dropshipping' ) . '" size="30" maxlength="50"/></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="type_of_package" id="type_of_package" data-id="type_of_package" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $type_of_package . '  /></td>

                                        <td><label for="type_of_package">' . __( 'Additional field in the "Add/Edit Product" to specify the "Type of Package"', 'woocommerce-dropshipping' ) . '

                                        <img class="help_tip" data-tip="This will also be added as an additional column in the packing slip" style="margin: 0 0 0 0px;" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <div class="inner-toggle type_of_package" ' . $type_of_package_option . '>
                                    <table style="margin-left:50px;">
                                        <tr>
                                            <td style="width:250px"><label for="type_of_package_conversion">' . __( 'Type Of Package Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                            <td><img class="help_tip" data-tip="Please specify Type Of Package label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                            <td><input name="type_of_package_conversion" value="' . @$options['type_of_package_conversion'] . '" size="30" maxlength="50"/></td>
                                        </tr>
                                    </table>
                                </div>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="product_price" id="product_price" data-id="product_price" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $price_product . '  /></td>

                                        <td><label for="product_price">' . __( 'Show product prices.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <div class="inner-toggle product_price" ' . $product_price_option . '>

                                    <table style="margin-left:50px;">
                                        <tr>
                                            <td style="width:250px"><label for="dropship_price">' . __( 'Price Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                            <td><img class="help_tip" data-tip="Please specify Price label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                            <td><input name="dropship_price" value="' .  __( @$options['dropship_price'], 'woocommerce-dropshipping' )  . '" size="30" maxlength="50" /></td>
                                        </tr>
                                    </table>
                                </div>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="cost_of_goods" id="cost_of_goods" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $cost_of_goods . '  /></td>

                                        <td><label for="cost_of_goods">' . __( 'Show Cost instead of Sell Price.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="show_gst_supplier_email" id="show_gst_supplier_email" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $show_gst_supplier_email . '  /></td>

                                        <td><label for="show_gst_supplier_email">' . __( 'Show Tax Split.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table> ';

				echo '<p></p>


                            </div>';

				echo '<br/><br/>

                                <div class="packing-slip-sections">
                                    <h4>' . __( 'Company Details', 'woocommerce-dropshipping' ) . '</h4>

                                    <table>
                                        <tr>
                                        <td style="width:250px"><label for="packing_slip_company_name" >' . __( 'Company Name:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please enter the name of your company" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><input name="packing_slip_company_name" value="' . __( @$options['packing_slip_company_name'], 'woocommerce-dropshipping' ) . '" style="width: 30ch;" /></td>
                                        </tr>
                                    </table>';

				echo '<p></p>

                                    <div class="inner-toggle">

                                        <table style="margin-left:50px;">
                                            <tr>
                                            <td ><label for="dropship_company_address">' . __( 'Company Address Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                            <td><img class="help_tip" data-tip="Please specify the Company Address Label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                            <td><input name="dropship_company_address" value="' . @$options['dropship_company_address'] . '" style="width: 30ch;" /></td>
                                            </tr>
                                            </tr>
                                        </table>
                                    </div>';

				echo '<p></p>

                                    <table>

                                        <tr>
                                            <td style="width:250px"><label for="packing_slip_address" >' . __( 'Address:', 'woocommerce-dropshipping' ) . '</label></td>

                                            <td><img class="help_tip" data-tip="Please enter your company&apos;s mailing address. This address will appear on your packing slips" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                            <td><textarea name="packing_slip_address" maxlength="200" rows="5" style="width: 30ch; border: 1px solid #8c8f9454;">' . __( @$options['packing_slip_address'], 'woocommerce-dropshipping' ) . '</textarea></td>
                                        </tr>
                                    </table>
                                    <p></p>
                                    <table>
                                        <tr>
                                        <td style="width:250px"><label for="packing_slip_customer_service_email" >' . __( 'Customer Service Email:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please enter the email address at which customers can reach your company if they have service issues" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><input name="packing_slip_customer_service_email" value="' . __( @$options['packing_slip_customer_service_email'], 'woocommerce-dropshipping' ) . '" style="width: 30ch;" /></td>
                                        </tr>
                                    </table>

                                    <p></p>

                                    <table>
                                        <tr>
                                        <td style="width:250px"><label for="packing_slip_customer_service_phone">' . __( 'Customer Service Phone Number:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please enter the phone number at which customers can reach your company if they have service issues" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><input name="packing_slip_customer_service_phone" value="' . @$options['packing_slip_customer_service_phone'] . '" style="width: 30ch;" /></td>
                                        </tr>
                                    </table>';

				echo '<p></p>

                                    <table>
                                        <tr>
                                            <td><input name="shipping_address" id="shipping_address" data-id="shipping_address" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $address_shipping . '  /></td>

                                            <td><label for="shipping_address">' . __( 'Show shipping address at the bottom.', 'woocommerce-dropshipping' ) . '</label></td>
                                        </tr>
                                    </table>';

				echo '<p></p>

                                    <div class="inner-toggle shipping_address" ' . $shipping_address_option . '>

                                        <table style="margin-left:50px;">
                                            <tr>
                                                <td style="width:150px"><label for="dropship_shipping_address_email">' . __( 'Shipping Address Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                                <td><img class="help_tip" data-tip="Please specify Shipping Address Label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                                <td><input name="dropship_shipping_address_email" value="' . __( @$options['dropship_shipping_address_email'], 'woocommerce-dropshipping' ) . '" size="30" maxlength="50" /></td>
                                            </tr>
                                        </table>
                                    </div>';

				echo '<p></p>

                                    <table>
                                        <tr>
                                            <td><input name="billing_address" id="billing_address" data-id="billing_address" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $address_billing . '  /></td>

                                            <td><label for="billing_address">' . __( 'Show billing address at the bottom.', 'woocommerce-dropshipping' ) . '</label></td>
                                        </tr>
                                    </table>';

				echo '<p></p>

                                    <div class="inner-toggle billing_address" ' . $billing_address_option . '>

                                        <table style="margin-left:50px;">

                                            <tr>

                                            <td style="width:150px"><label for="dropship_billing_address_email">' . __( 'Billing Address Label:', 'woocommerce-dropshipping' ) . '</label></td>

                                            <td><img class="help_tip" data-tip="Please specify Billing Address Label" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                            <td><input name="dropship_billing_address_email" value="' .  __( @$options['dropship_billing_address_email'], 'woocommerce-dropshipping' ) . '" size="30" maxlength="50"/></td>

                                            </tr>
                                        </table>
                                    </div>
                                </div>';

				if (empty(@$options['dropship_additional_comment'])) {

					$additionalCommentDefault = '';
				} else {
					// $additionalCommentDefault = '';
					$additionalCommentDefault = __( @$options['dropship_additional_comment'], 'woocommerce-dropshipping' );
				}

				echo '<br/><br/>
                                <div class="packing-slip-sections">

                                    <h4>' . __( 'Footer', 'woocommerce-dropshipping' ) . '</h4>
                                    <p><b>' . __( 'Any additional comment to be displayed', 'woocommerce-dropshipping' ) . '</b></p>
                                    <p>' . __( 'Max length: 200 characters', 'woocommerce-dropshipping' ) . '</p>
                                    <p>' . __( 'NOTE: Please make sure this content as small as possible so that it fits properly at the bottom of PDF.', 'woocommerce-dropshipping' ) . '</p>

                                    <table>
                                        <tr>
                                        <td style="width:150px"><label for="dropship_additional_comment" >' . __( 'Comments:', 'woocommerce-dropshipping' ) . '</label></td>

                                        <td><img class="help_tip" data-tip="Please specify Additional Comment" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><textarea name="dropship_additional_comment" maxlength="200" rows="5" cols="30">' . $additionalCommentDefault . '</textarea></td>
                                        </tr>
                                    </table>';

				echo '<p></p>

                                        <table>
                                            <tr>
                                                <td style="width:150px"><label for="packing_slip_thankyou">' . __( 'Thank You Message:', 'woocommerce-dropshipping' ) . '</label></td>

                                                <td><img class="help_tip" data-tip="This message will appear at the bottom of the packing slip" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                                <td><textarea name="packing_slip_thankyou" maxlength="200" rows="5" cols="30">' . __( @$options['packing_slip_thankyou'], 'woocommerce-dropshipping' ). '</textarea></td>
                                            </tr>

                                        </table>
                                </div>';

				echo '<br/><br/>';

				echo '<div class="packing-slip-sections">

                                <h4>' . __( 'Send Order Details to Suppliers', 'woocommerce-dropshipping' ) . '</h4>
                                <p></p>
                                <table>
                                    <tr>
                                        <td><input name="supp_notification" id="supp_notification" type="checkbox" ' . $supp_notification_attr . ' /></td>

                                        <td><label for="supp_notification">' . __( 'Do not send email notifications to supplier', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>

                                <p>' . __( 'This option controls whether or not you want to send a .CSV spreadsheet file as an attachment with the regular order notification emails that are sent to your suppliers', 'woocommerce-dropshipping' ) . '</p>

                                <table>
                                    <tr>
                                        <td><input name="csv_inmail" id="csv_inmail" class="" type="checkbox" ' . $csvInMail . ' /></td>

										<td><img class="help_tip" data-tip="It will send CSV file on supplier email" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                        <td><label for="csv_inmail">' . __( 'Send CSV with Supplier Notifications', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>
                            </div>';

				echo '<p></p>

                                <table>
                                    <tr>
                                    <td><input name="total_price" id="total_price" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $total_price . '  /></td>

                                    <td><label for="total_price">' . __( 'Show the total price in the packing slip.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="hide_shipping_price" id="hide_shipping_price" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $hide_shipping_price . '  /></td>

                                        <td><label for="hide_shipping_price">' . __( 'Hide the shipping cost in the packing slip.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="hide_tax" id="hide_tax" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $hide_tax . '  /></td>

                                        <td><label for="hide_tax">' . __( 'Hide Tax in supplier email.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>

                                </table>';

				// echo '<p></p>

				// <table>
				// <tr>
				// <td><input name="customer_phone_billing" id="customer_phone_billing" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $customer_phone_billing . '  /></td>

				// <td><label for="customer_phone_billing">Include the customer&apos;s phone number in the packing slip.</label></td>
				// </tr>
				// </table>';

				echo '<p></p>
                            <table>
                                <tr>
                                    <td><input type="checkbox" name="billing_phone" id="billing_phone" class="miscellaneous_packing_slip_options_checkbox_false"  value="1" tabIndex="1" onClick="ckChange(this)" ' . $billing_phone . '></td>

                                    <td><label for="billing_phone">' . __( 'Include the customer&apos;s phone number in the packing slip.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>
                            </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="hide_suppliername" id="hidesuppliername" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $suppliername_hide . '  /></td>

                                        <td><label for="hidesuppliername">' . __( 'Hide the supplier names on order confirmation emails.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="hide_suppliername_on_product_page" id="hide_suppliername_on_product_page" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $hide_suppliername_on_product_page . '  /></td>

                                        <td><label for="hide_suppliername_on_product_page">' . __( 'Show supplier names on product pages.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="hideorderdetail_suppliername" id="hideorderdetail_suppliername" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $suppliername_hideorderdetail . '  /></td>

                                        <td><label for="hideorderdetail_suppliername">' . __( 'Hide supplier names on the Order Details page.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="show_pay_type" id="show_pay_type" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $show_pay_type . '  /></td>

                                        <td><label for="show_pay_type">' . __( 'Show "Payment Type" in the notification email.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';
				echo '<p></p>
                                <table>
                                    <tr>
                                        <td><input name="customer_email" id="customer_email" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $customer_email . '  /></td>
                                        <td><label for="customer_email">' . __( 'Include "Customer email" into the Dropshipper packing slip.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="store_name" id="store_name" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $name_store . '  /></td>

                                        <td><label for="store_name">' . __( 'Include store name in the order notification CSV filename.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="store_address" id="store_address" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $address_store . '  /></td>

                                        <td><label for="store_address">' . __( 'Include the store&apos;s URL in the order notification CSV filename.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="complete_email" id="complete_email" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $email_complete . '  /></td>

                                        <td><label for="complete_email">' . __( 'Send an additional email to the supplier when the order is completed.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="order_complete_link" id="order_complete_link" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $link_complete_order . '  /></td>

                                        <td><label for="order_complete_link">' . __( 'Allow suppliers to mark their orders as shipped by clicking a link on the email, without logging in to your store.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="email_supplier" id="email_supplier" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $supplier_email . '  /></td>

                                        <td><label for="email_supplier">' . __( 'When an admin creates a new supplier, send registration details to the supplier&apos;s email.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>

                                    <tr>

                                        <td><input name="cnf_mail" id="cnf_mail" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $cnf_mail . '  /></td>

                                        <td><label for="cnf_mail">
										<img class="help_tip" data-tip="A notification will be sent to your store when a supplier opens order notification emails that you send out." style="margin: 0 0 0 0px;" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">

										' . __( 'Notify via email when suppliers open order notification emails.', 'woocommerce-dropshipping' ) . '

                                        </label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="std_mail" id="std_mail" class="miscellaneous_packing_slip_options_checkbox" type="checkbox" ' . $std_mail . '  /></td>

                                        <td><label for="std_mail">' . __( 'Use the standard WooCommerce mail format for email notification', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>
                                </table>';

				echo '<p></p>

                                <table>
                                    <tr>
                                        <td><input name="checkout_order_number" id="checkout_order_number" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $checkout_order_number . '  /></td>

                                        <td><label for="checkout_order_number">' . __( 'Include order number field on checkout.', 'woocommerce-dropshipping' ) . '</label></td>
                                    </tr>

                                </table>';

				echo '<p></p>
								<table>
                                <tr>
                                    <td><input name="cc_mail" id="cc_mail" class="miscellaneous_packing_slip_options_checkbox_false" type="checkbox" ' . $cc_mail . '  /></td> ';

				echo '<td><label for="cc_mail">' . __( 'Don&apos;t cc: the store admin when sending order notification emails to suppliers.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>

                            </table>';

				echo '<p></p>
                            <table>
                                <tr>
                                    <td><input type="checkbox" name="hide_client_info_Suppliers"          id="hide_client_info_Suppliers" class="miscellaneous_packing_slip_options_checkbox_false hide_client_info_Suppliers"  tabIndex="1" onClick="ckChange(this)" ' . $hide_client_info_Suppliers . '></td> ';

				echo '

                                    <td><label for="hide_client_info_Suppliers">
									' . __( 'Hide Customer info in the Packing Slip, Order emails and Suppliers dashboard.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>
                            </table>';

				echo '<p></p>
                            <table>
                                <tr>
                                    <td><input type="checkbox" name="hide_contact_info_Suppliers" id="hide_contact_info_Suppliers" class="miscellaneous_packing_slip_options_checkbox_false"  value="1" tabIndex="1" onClick="ckChange(this)" ' . $hide_contact_info_Suppliers . '></td>

                                    <td><label for="hide_contact_info_Suppliers">
									' . __( 'Hide Customer Contact info in the Packing Slip, Order emails and Suppliers dashboard.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>
                            </table>';

				if ($hide_client_info_Suppliers == ' checked="checked" ') {
					echo '<p></p>
								<table>
									<tr>
										<td><input name="store_add_shipping_add" id="store_add_shipping_add" class="miscellaneous_packing_slip_options_checkbox_false store_add_shipping_add" type="checkbox" value="1" tabIndex="1" onClick="ckChange(this)" ' . $store_add_shipping_add . ' disabled= "true"/></td>

										<td><label for="store_add_shipping_add" disabled= "true">' . __( 'Enable store address as shipping address in Suppliers Order list dashboard.', 'woocommerce-dropshipping' ) . '</label></td>
									</tr>

								</table>';
				} else {

					echo '<p></p>
                            <table>
                            <tr>
                                <td><input name="store_add_shipping_add" id="store_add_shipping_add" class="miscellaneous_packing_slip_options_checkbox_false store_add_shipping_add" type="checkbox" value="1" tabIndex="1" onClick="ckChange(this)" ' . $store_add_shipping_add . ' /></td>

                                <td><label for="store_add_shipping_add">' . __( 'Enable store address as shipping address in Suppliers Order list dashboard.', 'woocommerce-dropshipping' ) . '</label></td>
                            </tr>

                        </table>';
				}
				echo '</div>

						<div class="drop-setting-section" id="customised_supplier_emails">

						<h3>' . __( 'Customised Supplier Emails', 'woocommerce-dropshipping' ) . '</h3>

						<p>' . __( 'Dropshipping Pro plugin adds additional features. ', 'woocommerce-dropshipping' ) . '<a href="https://woocommerce.com/products/pro-add-on-for-woocommerce-dropshipping/">' . __( 'Click here to find out more.', 'woocommerce-dropshipping' ) . '</a></p>

                       <p></p>
                        <div style="';
				if (class_exists('WC_DS_Settings_Pro')) {
					echo 'width:50%;';
				} else {
					echo 'width:100%;';
				}
				echo 'display:inline-block; ">
                        <table>

                            <p></p>

                            <tr>
                             <td>
                             <div class="packing-slip-sections">

        					<h4>' . __( 'Packing Slip ', 'woocommerce-dropshipping' ) . '</h4>
    							<table>
    							<tr>
                                <td class="woocommerce-segmented-selection"><label for="supplier_email_packing_slip_title_color" >' . __( 'Title Color:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: #000" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_packing_slip_title_color" class="drop_color" type="text" value="' . @$options['supplier_email_packing_slip_title_color'] . '" size="30" /></td>
                                </td>
                                </tr>



                            <tr>
                                <td><label for="supplier_email_packing_slip_title_font_size" >' . __( 'Title Font Size:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: 24px" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_packing_slip_title_font_size" type="text" value="' . @$options['supplier_email_packing_slip_title_font_size'] . '" size="30" /></td>
                            </tr>

 </table>
                            </div>
                            </td>
                             </tr>
                              <tr><td colspan="3" height="40"></td></tr>
                        <tr>
                             <td>
                             <div class="packing-slip-sections woocommerce-customer-effort-score__selection">

        					<h4>' . __( 'Email ', 'woocommerce-dropshipping' ) . '</h4>
    							<table>
					        <tr>

                                <td class="woocommerce-segmented-selection"><label for="supplier_email_background_color" >' . __( 'Background Color:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: #000" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_background_color" class="drop_color" type="text" value="' . @$options['supplier_email_background_color'] . '" size="30" /></td>
                            </tr>

                            <tr>
                                <td><label for="supplier_email_order_note_font_size"> ' . __( 'Order Note Font Size :', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: 14px" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_order_note_font_size" type="text" value="' . @$options['supplier_email_order_note_font_size'] . '" size="30" /></td>
                            </tr>
                            <tr>
                                <td><label for="supplier_email_order_note_font_color">' . __( 'Order Note Font Color :', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: #000" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_order_note_font_color" class="drop_color" type="text" value="' . @$options['supplier_email_order_note_font_color'] . '" size="30" /></td>
                            </tr>

                            <tr>

                                <td><label for="supplier_email_footer_message_font_size">' . __( 'Footer Message Font Size :', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: 14px" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_footer_message_font_size" type="text" value="' . @$options['supplier_email_footer_message_font_size'] . '" size="30" /></td>
                            </tr>

                            <tr>
                                <td><label for="supplier_email_footer_message_font_color">' . __( 'Footer Message Font Color :', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: #000" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_footer_message_font_color" class="drop_color" type="text" value="' . @$options['supplier_email_footer_message_font_color'] . '" size="30" /></td>
                            </tr>
                        </table>
                        </div>
                        </td>
                        </tr>

                       <tr>
                             <td>
                             <div class="packing-slip-sections woocommerce-customer-effort-score__selection">

        					<h4>' . __( 'Email Body ', 'woocommerce-dropshipping' ) . '</h4>
    							<table>
                            <tr>
                                <td class="woocommerce-segmented-selection"><label for="supplier_email_body_font_size" >' . __( 'Font Size:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: 18px" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_body_font_size" type="text" value="' . @$options['supplier_email_body_font_size'] . '" size="30" /></td>
                            </tr>

                            <tr>
                                <td><label for="supplier_email_body_font_color" >' . __( 'Font Color:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: #000" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_body_font_color" class="drop_color" type="text" value="' . @$options['supplier_email_body_font_color'] . '" size="30" /></td>
                            </tr>
                         </table>
                        </div>
                        </td>
                        </tr>
                        <tr>
                             <td>
                             <div class="packing-slip-sections woocommerce-customer-effort-score__selection">

        					<h4>' . __( 'Email Bottom ', 'woocommerce-dropshipping' ) . '</h4>
    							<table>
                            <tr>
                                <td class="woocommerce-segmented-selection"><label for="supplier_email_bottom_sub_heading_font_size" >' . __( 'Sub Heading Font Size:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: 14px" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_bottom_sub_heading_font_size" type="text" value="' . @$options['supplier_email_bottom_sub_heading_font_size'] . '" size="30" /></td>
                            </tr>

                            <tr>
                                <td><label for="supplier_email_bottom_sub_heading_font_color" >' . __( 'Sub Heading Font Color:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: #000" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_bottom_sub_heading_font_color" class="drop_color" type="text" value="' . @$options['supplier_email_bottom_sub_heading_font_color'] . '" size="30" /></td>
                            </tr>

                            <tr>
                                <td><label for="supplier_email_bottom_sub_heading_content_font_size" >' . __( 'Sub Heading Content Font Size:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: 14px" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_bottom_sub_heading_content_font_size" type="text" value="' . @$options['supplier_email_bottom_sub_heading_content_font_size'] . '" size="30" /></td>
                            </tr>

                            <tr>
                                <td><label for="supplier_email_bottom_sub_heading_content_color" >' . __( 'Sub Heading Content Color:', 'woocommerce-dropshipping' ) . '</label></td>

                                <td><img class="help_tip" data-tip="EX: #000" src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16"></td>

                                <td><input name="supplier_email_bottom_sub_heading_content_color" class="drop_color" type="text" value="' . @$options['supplier_email_bottom_sub_heading_content_color'] . '" size="30" /></td>
                            </tr>

                            </table>
                        </div>
                        </td>
                        </tr>
                        </table>
                        </div>';
				if (class_exists('WC_DS_Settings_Pro')) {
					$data = '';
					apply_filters('add_extra_supplier_email_dropshipping_pro_settings', $data);
				}
				echo '</div>';

				echo '<div class="drop-setting-section" id="smtp_options">';

				echo '<h3>' . __( 'SMTP Options - SMTP is used for sending emails.', 'woocommerce-dropshipping' ) . '</h3>

                            <p></p>
                            <table>
                                <tr>
                                    <td><input name="smtp_check" id="smtp_check" type="checkbox" ' . $check_smtp . ' /></td>

                                    <td><label for="smtp_check">' . __( 'Check this option if you are using SMTP to send emails from your WooCommerce store.', 'woocommerce-dropshipping' ) . '</label></td>
                                </tr>
                            </table>';

				$from_name_sanitized = esc_html($from_name);
				$from_email_sanitized = esc_html($from_email);
				$woocommerce_url_sanitized = esc_url($woocommerce_url);

				echo '<h2>' . __( 'Email Sender Information', 'woocommerce-dropshipping' ) . '</h2>
							<p style="margin-top: -10px;">' . __( 'If left empty, emails sent from the store will use default WooCommerce settings.', 'woocommerce-dropshipping' ) . '</p>

                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row" class="titledesc">

                                            <label for="from_name">' . __( 'Emails sent from the store should show this sender name: ', 'woocommerce-dropshipping' ) . '<img class="help_tip" data-tip="This option will override default functionality of woocommerce" src="' . $woocommerce_url_sanitized . 'assets/images/help.png" height="16" width="16"></label>
                                        </th>

                                        <td class="forminp forminp-text">

                                            <input name="from_name" id="from_name" type="text" size="30" value="' . __( $from_name_sanitized, 'woocommerce-dropshipping' )  . '" class="" placeholder="">
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row" class="titledesc">
                                            <label for="from_email">' . __( 'Emails sent from the store should show this sender email address:', 'woocommerce-dropshipping' ) . '<img class="help_tip" data-tip="This option will override default WooCommerce functionality" src="' . $woocommerce_url_sanitized . 'assets/images/help.png" height="16" width="16"></label>
                                        </th>

                                        <td class="forminp forminp-email">
                                            <input name="from_email" id="from_email" type="email" size="30" value="' . __( $from_email_sanitized, 'woocommerce-dropshipping' ) . '" class="" placeholder="" multiple="multiple">

                                            <input type="hidden" name="show_admin_notice_option" value="0" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>';

				/* Price Calculator Start */

				echo '<div class="drop-setting-section" id="price_calculator_options" style="margin-left:20px;margin-right:20px;">';

				echo '<h3>' . __( 'Price Calculator', 'woocommerce-dropshipping' ) . '</h3>';

				/* Progress Bar */

				$green = 100 - $prft_prcnt_val;
				$blue = $prft_prcnt_val;
				$nevy_blue = $prft_dolr_val;

				echo '<table class="w-ful responsive-table" style="width:80%; margin-top:35px;">
				            <p></p>
				            <tr>
				                <td>
				                    <div class="packing-slip-sections">
				        				<h4>' . __( 'Pricing and Profit Calculator', 'woocommerce-dropshipping' ) . '</h4>

				    					<table id="packing_t" style="background: #f8f8f8;width:100%;">
				    						<tbody>
				    							<tr hidden="hidden">
				    								<th id="row_cog_price"></th>
				    								<th  id="row_profit_price"></th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>
				    								<th >' . __( 'Cost Of Product', 'woocommerce-dropshipping' ) . '</th>

				    							</tr>
				    							<tr>
					    							<td colspan="3">
					    							</td>

					    							<td colspan="4" id="brek_evn_val" style="border-left: 1px solid #c1c1c1; border-right: 1px solid #c1c1c1;padding-top: 20px; border-bottom: 1px solid #c1c1c1; text-align: center;">
					    							<p>' . __( 'Break Even Value', 'woocommerce-dropshipping' ) . '</p>
					    							</td>

					    							<td colspan="3">
					    							</td>
				    							<tr>

				    							<tr>
					    							<td id="cog_val" style="text-align: left; position: relative; top: 15px; padding: 0px 0px 0px 10px;" rowspan="2">
					    							</td>

					    							<td colspan="2" id="profit_val" style="text-align: inherit; position: relative; top: 15px;" rowspan="2">
					    								<p></p>
					    							</td>
					    							<td style="border-left: 1px solid #c1c1c1;"> </td>
					    							<td style="padding-top: 15px; text-align: center;">
					    								<p id="fee_prcnt_val" style="margin-bottom: 0.2rem;">' . $fee_prcnt_val . '% fee</p>
					    							</td>
					    							<td></td>
					    							<td  style="padding-top: 15px;border-right: 1px solid #c1c1c1; text-align: center;">
					    								<p id="fee_dolr_val" style="margin-bottom: 0.2rem;">$' . $fee_dolr_val . ' fee</p>
					    							</td>
					    							<td></td>
					    							<td style="padding-top: 15px; text-align: center;">
					    								<p style="margin-bottom: 0.2rem;">' . __( 'Total Price', 'woocommerce-dropshipping' ) . '</p>
					    							</td>
				    							<tr>

				    							<tr>
				    								<td colspan="2" id="progress_bar_td" style="padding: 0 0 15px 10px;">
				    									<div class="progress" style="max-width: 100%">


										            <div class="progress-bar bg-success progress-bar-animated" id="green_progress" role="progressbar"
										                style="width:' . $green . '%"><span id="cost_of_product">' . __( 'Cost Of Product ', 'woocommerce-dropshipping' ) . '</span>
										                $100

										            </div>
										            <div class="progress-bar
										                progress-bar-stripped progress-bar-animated" id="blue_progress"  role="progressbar" style="width:' . $blue . '%">
										                ';
				if (isset($options['profit_percent_value'])) {
					if ($options['profit_percent_value'] > 0 || $options['profit_doller_value'] > 0) {
						echo '<span id="profir_margin">' . __( 'Profit Margin', 'woocommerce-dropshipping' ) . ' </span>';
					}
				}
				echo $blue . '%
										            </div>
										            <div id="percent_fee_bar" class="progress-bar
										                progress-bar-stripped progress-bar-animated" role="progressbar" style="width: ' . $nevy_blue . '%; background: #007bff80;">
										                $' . $nevy_blue . '
										            </div>
									       		</div>
					    							</td>

					    							<td rowspan="2" style="text-align: center;">
					    								<p style="font-size: 40px;">}</p>
					    							</td>

					    							<td style="border-left: 1px solid #c1c1c1; text-align: right;">
					    								<p style="margin-bottom: 1.5rem;">+</p>
					    							</td>

					    							<td style="text-align: center;">
					    								<p id="final_prcnt_fee" style="margin-bottom: 1.5rem;">$' . $final_prcnt_fee . '</p>
					    							</td>

					    							<td style="text-align: center;">
					    								<p style="margin-bottom: 1.5rem;">+</p>
					    							</td>

					    							<td style="border-right: 1px solid #c1c1c1; text-align: center;">
					    								<p id="fee_dolr_val_fix" style="margin-bottom: 1.5rem;">$' . $fee_dolr_val . '</p>
					    							</td>

					    							<td style="text-align: center;">
					    								<p style="margin-bottom: 1.5rem;">=</p>
					    							</td>

					    							<td style="text-align: center;">
					    								<p id="right_calculesn" style="margin-bottom: 1.5rem;">$' . $right_calculesn . '</p>
					    							</td>

				    							</tr>


				    						</tbody>
				    					</table>
				                    </div>
				                </td>
				            </tr>
				       	</table>';

				echo '<table class="w-ful" style="width:80%; margin-top: 34px;">
				            <p></p>
				            <tr>
				                <td>
				                    <div class="packing-slip-sections">
				        				<h4>' . __( 'Break Even Values', 'woocommerce-dropshipping' ) . '</h4>

				    					<table>
				    						<tr>
				                            	<td>
				                            		<label for="title_fee_percent" >' . __( '% Fee', 'woocommerce-dropshipping' ) . '</label>
				                            	</td>

				                           		<td>
				                            		<img class="help_tip" data-tip="Enter the transaction percentage fee value here." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">
				                            	</td>

				                            	<td>
				                            		<input name="fee_percent_value" class="bar_cal" id="fee_percent_value" type="number" value="' . @$options['fee_percent_value'] . '" min="0" step="0.01" style="width:150px;" />
				                            	</td>
				                            </tr>

				                            <tr>
				                            	<td>
				                            		<label for="title_fee_doller" >' . __( '$ Fee', 'woocommerce-dropshipping' ) . '</label>
				                            	</td>

				                           		<td>
				                            		<img class="help_tip" data-tip="Enter the fixed transaction fee value here." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">
				                            	</td>

				                            	<td>
				                            		<input name="fee_doller_value" class="bar_cal" id="fee_doller_value" type="number" value="' . @$options['fee_doller_value'] . '" min="0" step="0.01" style="width:150px;" />
				                            	</td>
				                            </tr>
				 						</table>
				                    </div>
				                </td>
				            </tr>
				       	</table>';

				echo '<table class="w-ful" style="width:80%; margin-top: 34px;">
				            <p></p>
				            <tr>
				                <td>
				                    <div class="packing-slip-sections">
				        				<h4>' . __( 'Profit Margin', 'woocommerce-dropshipping' ) . '</h4>';

				if ($dynamic_profit_margin_setting == ' checked="checked" ') {
					echo '<table>
						<tr>
                        	<td>
                        		<label for="title_profit_percent" >' . __( '% Profit', 'woocommerce-dropshipping' ) . '</label>
                        	</td>

                       		<td>
                        		<img class="help_tip" data-tip="Enter the Profit Percent value here." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">
                        	</td>

                        	<td>
                        		<input name="profit_percent_value" id="profit_percent_value" class="bar_cal" type="number" value="' . @$options['profit_percent_value'] . '" min="0" step="0.01" style="width:150px;" disabled/>
                        	</td>
                        </tr>

                        <tr>
                        	<td>
                        		<label for="title_profit_doller" >' . __( '$ Profit', 'woocommerce-dropshipping' ) . '</label>
                        	</td>

                       		<td>
                        		<img class="help_tip" data-tip="Enter the Fixed Profit value here." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">
                        	</td>

                        	<td>
                        		<input name="profit_doller_value" class="bar_cal" type="number" id="profit_doller_value" value="' . @$options['profit_doller_value'] . '" min="0" step="0.01" style="width:150px;" disabled />
                        	</td>
                        </tr>

						</table>';
				} else {

					echo '<table>
					<tr>
                    	<td>
                    		<label for="title_profit_percent" >' . __( '% Profit', 'woocommerce-dropshipping' ) . '</label>
                    	</td>

                   		<td>
                    		<img class="help_tip" data-tip="Enter the Profit Percent value here." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">
                    	</td>

                    	<td>
                    		<input name="profit_percent_value" class="bar_cal" type="number" id="profit_percent_value" value="' . @$options['profit_percent_value'] . '" min="0" step="0.01" style="width:140px;" />
                    	</td>
                    </tr>

                    <tr>
                    	<td>
                    		<label for="title_profit_doller" >' . __( '$ Profit', 'woocommerce-dropshipping' ) . '</label>
                    	</td>

                   		<td>
                    		<img class="help_tip" data-tip="Enter the Fixed Profit value here." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">
                    	</td>

                    	<td>
                    		<input name="profit_doller_value" class="bar_cal" type="number" id="profit_doller_value" value="' . @$options['profit_doller_value'] . '" min="0" step="0.01" style="width:140px;" />
                    	</td>
                    </tr>

					</table>';
				}

				if ($dynamic_profit_margin_setting == ' checked="checked" ') {
					$dynamic_profit = 'yes';
				} else {
					$dynamic_profit = 'no';
				}

				echo '<table style="margin-top: 10px;">

					<tr valign="top" style="position:relative;">
						<th scope="row" class="titledesc">
						<label for="dynamic_profit_margin">
						' . __( 'Dynamic Profit Margin', 'woocommerce-dropshipping' ) . '


						</label></th>
						<th>
							<img class="help_tip" data-tip="You enable should save the settings. After it is saved then you will be able to add multiple rules." src="' . $woocommerce_url . 'assets/images/help.png" height="16" width="16">
	                	</th>

						<td  class="forminp forminp-checkbox">
							<fieldset>
								<legend class="screen-reader-text"><span>' . __( 'Dynamic Profit Margin ', 'woocommerce-dropshipping' ) . '</span></legend>
								<label for="dynamic_profit_margin" class="opmc-toggle-control">

								<input name="dynamic_profit_margin" id="dynamic_profit_margin" type="checkbox" value="1"'; ?> <?php checked($dynamic_profit, 'yes'); ?> <?php
																																										echo '>
									<span class="opmc-control"></span>
								</label>
							</fieldset>
						</td>
					</tr>
				</table>';

																																										if ($dynamic_profit_margin_setting == ' checked="checked" ') {

																																											$textAreaValue = trim(@$options['profit_margin_hidden_textarea']);

																																											if ($textAreaValue == '') { // i.e. no dynamic profit margin set yet

																																												echo '<div class="dynamic_profit_margin_section">
									<textarea id="profit_margin_hidden" name="profit_margin_hidden_textarea" hidden="hidden"></textarea>

									<table class="form-table" id="tr_clone" style="width:100%;">

										<tbody>
										<p class="p_cost_range">Product Cost Range</p>
											<tr valign="top" class="mappingBlocks field-close" data-index="1" data-max_rows="5" id="trs_clone">
											<div class="rows">
												<td>
													<label id="title_dynamic" for="title_from" >' . __( 'From:', 'woocommerce-dropshipping' ) . '</label>

													<fieldset>
														<input name="dynamic_from_value[1]" class="dynamic_from_value clone_tds from_val requiredClass" id="dynamic_from_value_1" type="number" data="vfrom" min="0" step="0.01" style="width:100px;" required />
													</fieldset>
												</td>

												<td>
													<label id="title_dynamic" for="title_to" >To:</label>

													<fieldset>

														<input name="dynamic_to_value[1]" class="dynamic_to_value clone_tds to_val requiredClass" id="dynamic_to_value_1" type="number" data="vto" min="0" step="0.01" style="width:100px;" required />
													</fieldset>
												</td>
												<td>
													<label id="title_dynamic" for="title_profit_percent" >' . __( '% Profit', 'woocommerce-dropshipping' ) . '</label>

													<fieldset>

														<input name="dynamic_profit_percent_value[1]" class="dynamic_profit_percent_value clone_tds requiredClass" id="dynamic_profit_percent_value_1" data="vpercent" type="number" min="0" step="0.01" style="width:100px;" required />
													</fieldset>
												</td>

												<td>
													<label id="title_dynamic" for="title_profit_doller" >' . __( '$ Profit', 'woocommerce-dropshipping' ) . '</label>

													<fieldset>

														<input name="dynamic_profit_doller_value[1]" class="dynamic_profit_doller_value clone_tds requiredClass" id="dynamic_profit_doller_value_1" data="vfixed" type="number" min="0" step="0.01"  style="width:100px;" required />
													</fieldset>
												</td>
												</div>
											</tr>
											<tr class="add-rem-bttn" style="line-height: 4;">
												<td class="forminp" >
													<input type="button" class="btn btn-primary" value="(-) Remove Rule" id="removeRows" style="width: 120px;padding: 4px 0px;  font-size: 13px; border: 0;"/>
												</td>
												<td colspan="3">
													<input type="button" class="btn btn-primary" value="(+) Add Rule" id="addMoreRows" style="width: 120px;padding: 4px 0px;  font-size: 13px; border: 0;"/>
												</td>
											</tr>

										</tbody>
									</table>
									<p id="amount_message" style="display:none">' . __( 'Change the range to lower value to add more rules.', 'woocommerce-dropshipping' ) . '</p>
								</div>';
																																											} else { // if($textAreaValue == "") // i.e. dynamic profit margin has already been set

																																												$allElements = explode('~', $textAreaValue);
																																												$nRows = count($allElements);

																																												$elementsHtml = '
								<div class="dynamic_profit_margin_section">
									<textarea id="profit_margin_hidden" name="profit_margin_hidden_textarea" hidden="hidden">' . $textAreaValue . '</textarea>
									<table class="form-table" id="tr_clone" style="width:100%;">
										<tbody>
										<p class="p_cost_range">' . __( 'Product Cost Range', 'woocommerce-dropshipping' ) . '</p>';
																																												$rowCount = 0;
																																												foreach ($allElements as $row) {
																																													$rowCount++;

																																													$elementsHtml .= '
									<tr valign="top" class="mappingBlocks field-close" data-index="1" data-max_rows="5" id="trs_clone">
										<div class="rows">
									';

																																													$allTds = explode('_', $row);
																																													$tdCount = 0;
																																													foreach ($allTds as $td) {
																																														$tdCount++;
																																														switch ($tdCount) {
																																															case 1:
																																																$elementsHtml .= '
													<td><label id="title_dynamic" for="title_from" >' . __( 'From:', 'woocommerce-dropshipping' ) . '</label><fieldset><input name="dynamic_from_value[' . $rowCount . ']" class="dynamic_from_value clone_tds from_val requiredClass" id="dynamic_from_value_' . $rowCount . '" type="number" data="vfrom" min="0" step="0.01" style="width:100px;" value="' . $td . '" required /></fieldset></td>
												';
																																																break;
																																															case 2:
																																																$elementsHtml .= '
													<td><label id="title_dynamic" for="title_to" >' . __( 'To:', 'woocommerce-dropshipping' ) . '</label>
													<fieldset><input name="dynamic_to_value[' . $rowCount . ']" class="dynamic_to_value clone_tds to_val requiredClass" id="dynamic_to_value_' . $rowCount . '" type="number" data="vto" min="0" step="0.01" style="width:100px;" value="' . $td . '" required /></fieldset></td>
												';
																																																break;
																																															case 3:
																																																$elementsHtml .= '
													<td><label id="title_dynamic" for="title_profit_percent" >' . __( '% Profit', 'woocommerce-dropshipping' ) . '</label>
													<fieldset><input name="dynamic_profit_percent_value[' . $rowCount . ']" class="dynamic_profit_percent_value clone_tds requiredClass" id="dynamic_profit_percent_value_' . $rowCount . '" type="number" data="vpercent" min="0" step="0.01" style="width:100px;" value="' . $td . '" required /></fieldset></td>
												';
																																																break;
																																															case 4:
																																																$elementsHtml .= '
													<td><label id="title_dynamic" for="title_profit_doller" >' . __( '$ Profit', 'woocommerce-dropshipping' ) . '</label>
													<fieldset><input name="dynamic_profit_doller_value[' . $rowCount . ']" class="dynamic_profit_doller_value clone_tds requiredClass" id="dynamic_profit_doller_value_' . $rowCount . '" type="number" min="0" step="0.01" data="vfixed" style="width:100px;" value="' . $td . '" required /></fieldset></td>
												';
																																																break;
																																														} // switch($tdCount)

																																													} // foreach($allTds as $td)

																																													$elementsHtml .= '
										</div>
									</tr>
									';
																																												} // foreach($allElements as $row)

																																												$elementsHtml .= '
											<tr class="add-rem-bttn" style="line-height: 4;">
												<td class="forminp" >
													<input type="button" class="btn btn-primary" value="(-) Remove Rule" id="removeRows" style="width: 120px;padding: 4px 0px; font-size: 13px; border: 0;" />
												</td>
												<td colspan="3">
													<input type="button" class="btn btn-primary" value="(+) Add Rule" id="addMoreRows" style="width: 120px;padding: 4px 0px; font-size: 13px; border: 0;" />
												</td>
											</tr>
										</tbody>
									</table>
									<p id="amount_message" style="display:none">' . __( 'Change the range to lower value to add more rules.', 'woocommerce-dropshipping' ) . '</p>
								</div>';

																																												echo $elementsHtml;
																																											} // else of if($textAreaValue == "")

																																										} // if ( $dynamic_profit_margin_setting == ' checked="checked" ' )

																																										echo '</td>
								</tr>
				            </tr>
				       	</table>
				    </div>';
																																										/* Price Calculator End */

																																										echo '<div class="slidesection_bkp">
					 <p></p>';

																																										return apply_filters('woocommerce_get_settings_' . $this->id, array(), $current_section);
																																									}

																																									/**
																																									 *
																																									 * Output the settings
																																									 */
																																									public function output()
																																									{

																																										global $current_section;

																																										$settings = $this->get_dropshipping_settings($current_section);

																																										WC_Admin_Settings::output_fields($settings);
																																									}
																																								}

																																								$settings[] = new WC_DS_Settings();

																																								return $settings;
																																							}

																																							add_filter('woocommerce_get_settings_pages', 'wc_ds_add_settings', 15);

																																						endif;
