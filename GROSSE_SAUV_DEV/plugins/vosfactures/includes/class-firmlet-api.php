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

class VosfacturesApi {

	const API_URL         = 'vosfactures.fr';
	const NEW_ACCOUNT_URL = 'http://app.vosfactures.fr/account/new';
	const HELP_URL        = 'https://aide.vosfactures.fr/28664167-Plugin-WooCommerce';

	/**
	 * Returns api domain with in format "account_prefix.firmlet.com"
	 *
	 * @param  string $api_token API Token from Firmlet. Default: null
	 * @return string API domain
	 */
	public function __construct( $name = null, $version = null ) {
		$this->name    = $name;
		$this->version = $version;
	}

	public function get_api_domain( $api_token = null ) {
		if ( is_null( $api_token ) && is_array( get_option( 'woocommerce_firmlet_settings' ) ) ) {
			$api_token = get_option( 'woocommerce_firmlet_settings' )['api_token'];
		}

		$account_prefix = explode( '/', $api_token );
		$account_prefix = array_pop( $account_prefix );
		$api_url        = self::API_URL;

		if ( VOSFACTURES_DEBUG ) { // change domain to .test
			$api_url = explode( '.', $api_url );
			array_pop( $api_url );
			$api_url = implode( $api_url ) . '.test';
		}

		$domain = $account_prefix . '.' . $api_url;
		return $domain;
	}


	/**
	 * Returns api url
	 *
	 * @param  string $controller firmlet controller. Default: ''
	 * @param  string $api_token  Firmlet API Token. Default: null
	 * @return string API url
	 */
	public function get_api_url( $controller = '', $api_token = null ) {
		$http = VOSFACTURES_DEBUG ?
			'http://' : 'https://';
		return $http . $this->get_api_domain( $api_token ) . '/' . $controller;
	}


	/**
	 * Account urls methods
	 */
	public function get_account_url( $api_token = null ) {
		return $this->get_api_url( 'account', $api_token );
	}

	public function get_account_urlJson( $api_token = null ) {
		return $this->get_account_url( $api_token ) . '.json';
	}


	/**
	 * Invoices urls methods
	 */
	public function get_invoices_url( $api_token = null ) {
		return $this->get_api_url( 'invoices', $api_token );
	}

	public function get_invoices_urlJson( $api_token = null ) {
		return $this->get_invoices_url( $api_token ) . '.json';
	}

	public function get_invoice_url( $id, $api_token = null ) {
		return $this->get_invoices_url( $api_token ) . '/' . $id;
	}

	public function get_invoice_url_json( $id, $api_token = null ) {
		return $this->get_invoice_url( $id, $api_token ) . '.json';
	}

	/**
	 * Client urls methods
	 */
	public function get_clients_url( $api_token = null ) {
		return $this->get_api_url( 'clients', $api_token );
	}

	public function get_clients_url_json( $api_token = null ) {
		return $this->get_clients_url( $api_token ) . '.json';
	}

	public function get_client_url( $id, $api_token = null ) {
		return $this->get_clients_url( $api_token ) . '/' . $id;
	}

	public function get_client_url_json( $id, $api_token = null ) {
		return $this->get_client_url( $id, $api_token ) . '.json';
	}

	/**
	 * Department urls methods
	 */
	public function get_departments_url( $api_token = null ) {
		return $this->get_api_url( 'departments', $api_token );
	}

	public function get_departments_url_json( $api_token = null ) {
		return $this->get_departments_url( $api_token ) . '.json';
	}

	public function get_department_url( $id, $api_token = null ) {
		return $this->get_departments_url( $api_token ) . '/' . $id;
	}

	public function get_department_urlJson( $id, $api_token = null ) {
		return $this->get_department_url( $id, $api_token ) . '.json';
	}

	/**
	 * Category urls methods
	 */
	public function get_categories_url( $api_token = null ) {
		return $this->get_api_url( 'categories', $api_token );
	}

	public function get_categories_url_json( $api_token = null ) {
		return $this->get_categories_url( $api_token ) . '.json';
	}

	public function get_category_url( $id, $api_token = null ) {
		return $this->get_categories_url( $api_token ) . '/' . $id;
	}

	public function get_category_url_json( $id, $api_token = null ) {
		return $this->get_category_url( $id, $api_token ) . '.json';
	}

	private function curl( $url, $method, $data ) {
		$request = wp_remote_post(
			$url,
			array(
				'method'      => $method,
				'headers'     => array( 'Accept: application/json', 'Content-Type: application/json' ),
				'httpversion' => '1.0',
				'sslverify'   => false,
				'body'        => $data,
			)
		);

		$body = wp_remote_retrieve_body( $request );

		$result = json_decode( $body );

		return $result;
	}


	public function make_request( $url, $method, $data ) {
		return $this->curl( $url, $method, $data );
	}

	private function delete_invoice( $url ) {
		return $this->make_request( $url, 'DELETE', null );
	}

	private function get_from_server( $api_token, $url ) {
		$url    = $url . '?api_token=' . $api_token;
		$result = $this->make_request( $url, 'GET', null );

		if ( isset( $result->code ) && $result->code === 'error' ) {
			return array();
		}

		return $result && count( (array) $result ) > 0 ? $result : array(); // prevention against null in foreach
	}

	public function get_categories( $api_token = null ) {
		if ( $api_token == null && is_array( get_option( 'woocommerce_firmlet_settings' ) ) ) {
			$api_token = get_option( 'woocommerce_firmlet_settings' )['api_token'];
		}
		return $this->get_from_server( $api_token, $this->get_categories_url_json( $api_token ) );
	}

	public function get_departments( $api_token = null ) {
		if ( $api_token == null && is_array( get_option( 'woocommerce_firmlet_settings' ) ) ) {
			$api_token = get_option( 'woocommerce_firmlet_settings' )['api_token'];
		}
		return $this->get_from_server( $api_token, $this->get_departments_url_json( $api_token ) );
	}

	public function get_account( $api_token = null ) {
		if ( $api_token == null && is_array( get_option( 'woocommerce_firmlet_settings' ) ) ) {
			$api_token = get_option( 'woocommerce_firmlet_settings' )['api_token'];
		}
		return $this->get_from_server( $api_token, $this->get_account_urlJson( $api_token ) );
	}

	public function remove_invoice( $id_order ) {
		$this->db = new VosfacturesDatabase();
		$order    = wc_get_order( $id_order );

		$error_message = "remove_invoice [{$order->get_id()}}]: ";
		$last_invoice  = $this->db->get_last_invoice( $id_order );
		if ( empty( $last_invoice ) ) {
			$logger = wc_get_logger();
			$logger->debug( $error_message . 'invoice_not_found', array( 'source' => 'vosfactures' ) );
			return false;
		}

		// firmlet side
		if ( ! $this->remove_invoice_firmlet( $last_invoice ) ) {
			return false;
		}

		// Everything is ok. Invoice was removed/not found on the server, so we remove it from Woocommerce's database
		$this->db->delete_invoice_via_firmlet_id( $last_invoice->id_firmlet_invoice );
		return true;
	}

	private function remove_invoice_firmlet( $invoice_row, $error_message = null ) {
		$url      = $this->get_invoice_url_json( $invoice_row->external_id ) . '?api_token=' . get_option( 'woocommerce_firmlet_settings' )['api_token'];
		$response = $this->delete_invoice( $url );
		$logger   = wc_get_logger();

		switch ( gettype( $response ) ) {
			case 'object': // object, not an array
				if ( ! empty( $response->status ) && $response->status == '404' ) {
					$logger->debug( $error_message . 'invoice_not_found_delete_from_db', array( 'source' => 'vosfactures' ) );
				} elseif ( ! empty( $response->code ) && $response->code === 'error' ) {
					$logger->debug( $error_message . 'api_token_incorrect', array( 'source' => 'vosfactures' ) );
					update_option( 'firmlet_error', 'invalid_api_token' );
					return false;
				}
				break;
			case 'string':
				if ( $response == 'ok' ) {
					// success, document removed from
					$logger->debug( $error_message . 'invoice_was_removed', array( 'source' => 'vosfactures' ) );
				} else {
					$logger->debug( $error_message . 'undefined_response', array( 'source' => 'vosfactures' ) );
					return false;
				}
				break;
			default:
		}

		return true;
	}

	public function get_origin() {
		return 'woocommerce_' . WC_VERSION . '|' . $this->name . '_' . $this->version;
	}

	public function get_additional_fields( $additional_fields = null ) {
		if ( $additional_fields === null && is_array( get_option( 'woocommerce_firmlet_settings' ) ) ) {
			$additional_fields = get_option( 'woocommerce_firmlet_settings' )['additional_fields'];
		}
		$additional_fields = trim( preg_replace( '/\s+/', '', $additional_fields ) );
		$additional_fields = str_replace( '\\', '', $additional_fields );
		$result            = json_decode( '{' . $additional_fields . '}' );
		if ( $result != null ) {
			return $result;
		}
		$logger = wc_get_logger();
		$logger->debug( 'get_additional_fields: error in parsing json', array( 'source' => 'vosfactures' ) );
		return null;
	}

	public function get_illegal_fields() {
		return array(
			'id',
			'account_id',
			'deleted',
			'token',
		);
	}
}
