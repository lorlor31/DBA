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

class VosfacturesDatabase {

	private static $prefix = 'firmlet_invoice';

	private static $invoice_keys = array(
		'id_order',
		'view_url',
		'external_id',
		'error',
	);

	public function install_database() {
		global $wpdb;
		include_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$logger = wc_get_logger();
		$logger->debug( 'install_database started', array( 'source' => 'vosfactures' ) );

		if ( $this->table_exists() ) {
			$logger->debug( 'table already already exists', array( 'source' => 'vosfactures' ) );

			return true;
		}

		$table_name = $wpdb->prefix . 'firmlet_invoice';

		$wpdb->query(
			'
                    CREATE TABLE ' . $table_name . ' (
                    id_firmlet_invoice int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    id_order int(11) UNSIGNED NOT NULL,
                    view_url varchar(255),
                    external_id int(11),
                    error varchar(255),
                    PRIMARY KEY (id_firmlet_invoice),
                    KEY id_order (id_order)
                    ) ' . $wpdb->get_charset_collate() . ';
                '
		);

		if ( $wpdb->last_error == '' ) {
			$logger->debug( 'Table created successfully', array( 'source' => 'vosfactures' ) );
		} else {
			$logger->debug( 'Error occurred: ' . $wpdb->last_error, array( 'source' => 'vosfactures' ) );
		}
	}

	public function table_exists() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'firmlet_invoice';

		return $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name;
	}

	public function get_all_invoices( $id_order ) {
		return $this->get_last_invoice( $id_order, true );
	}

	/**
	 * returns last created invoice for $id_order
	 *
	 * @param  int  $id_order
	 * @param  bool $all      Should return all invoices for $id_order?
	 *                        Default: false
	 * @return array - if any rows are present
	 *         null - if no records are present
	 */
	public function get_last_invoice( $id_order, $all = false ) {
		global $wpdb;
		if ( $all ) {
			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'firmlet_invoice WHERE id_order = %d ORDER BY id_firmlet_invoice', $id_order ) );
		} else {
			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'firmlet_invoice WHERE id_order = %d ORDER BY id_firmlet_invoice DESC LIMIT 1', $id_order ) );
		}
		$sql = $wpdb->prepare(
			'
                    SELECT * FROM ' . $wpdb->prefix . 'firmlet_invoice
                    WHERE id_order = %d
                    ORDER BY id_firmlet_invoice
                ',
			$id_order
		);

		$row = $wpdb->get_row( $sql . ( $all ? '' : 'DESC LIMIT 1' ) );

		if ( $all ) {
			return $row;
		}

		return ! empty( $row ) > 0 ? $row : null;
	}

	public function invoices_with_errors() {
		global $wpdb;
		$result = $wpdb->get_results(
			'
                    SELECT id_order
                    FROM  ' . $wpdb->prefix . 'firmlet_invoice
                    WHERE error IS NOT NULL
                '
		);

		return $result;
	}

	public function insert_invoice( $id_order, $view_url, $external_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'firmlet_invoice';
		$logger     = wc_get_logger();
		$logger->debug( 'Insert invoice [id_order => ' . $id_order . ', view_url => ' . $view_url . ', external_id => ' . $external_id . ']', array( 'source' => 'vosfactures' ) );

		$wpdb->insert(
			$table_name,
			array(
				'id_order'    => $id_order,
				'view_url'    => $view_url,
				'external_id' => $external_id,
			),
			array(
				'%d',
				'%s',
				'%d',
			)
		);
	}

	public function insert_invoice_with_error( $id_order, $error ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'firmlet_invoice';
		$logger     = wc_get_logger();
		$logger->debug( 'Insert invoice [id_order => ' . $id_order . ', error => ' . $error . ']', array( 'source' => 'vosfactures' ) );

		$success = $wpdb->insert(
			$table_name,
			array(
				'id_order' => $id_order,
				'error'    => $error,
			),
			array(
				'%d',
				'%s',
			)
		);
		return $success;
	}

	public function delete_invoice( $id_order ) {
		global $wpdb;
		$logger = wc_get_logger();
		$logger->debug( 'Delete on id = ' . $id_order, array( 'source' => 'vosfactures' ) );

		$table_name = $wpdb->prefix . 'firmlet_invoice';

		$wpdb->delete( $table_name, array( 'id_order' => $id_order ), array( '%d' ) );
	}

	public function delete_invoice_via_firmlet_id( $id_firmlet_invoice ) {
		global $wpdb;
		$logger = wc_get_logger();
		$logger->debug( 'Delete on firmlet_id = ' . $id_firmlet_invoice, array( 'source' => 'vosfactures' ) );

		$table_name = $wpdb->prefix . 'firmlet_invoice';

		$wpdb->delete( $table_name, array( 'id_firmlet_invoice' => $id_firmlet_invoice ), array( '%d' ) );
	}

	public function get_item_tax_rate( $key ) {
		global $wpdb;
		$rate = $wpdb->get_var( $wpdb->prepare( "SELECT tax_rate FROM {$wpdb->prefix}woocommerce_tax_rates WHERE tax_rate_id = %s", $key ) );

		return is_numeric($rate) ? $rate : 0;
	}

	public function count_issued() {
		global $wpdb;
		$result = $wpdb->get_results(
			'SELECT COUNT(*) as "count" FROM  ' . $wpdb->prefix . 'firmlet_invoice WHERE error IS NULL'
		);

		if ( isset($result[0]) ) {
			if ( $result[0] instanceof stdClass) {
				return $result[0]->count;
			} else {
				return $result[0]['count'];
			}
		}

		return 0;
	}
}
