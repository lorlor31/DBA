<?php

class WC_TrackingTableDelivery {

    /**
     * Single instance of WC_TrackingTableDelivery
     * @var 	object
     * @access  private
     */
    private static $_instance = null;

    /**
     * Delivery value
     * @var 	int
     * @access  private
     */
    private $delivery;

    /**
     * Order id
     * @var 	string
     * @access  private
     */
    private $order_id;

	public function __construct() {
	    $this->delivery = $_REQUEST['delivery'] ?? null;
	    $this->order_id = $_REQUEST['order_id'] ?? null;
	}

    public function init() {
        $object = new stdClass();
        $object->id = $this->order_id;
        if ( is_null($this->order_id) || is_null($this->delivery) ) {
            $object->message = 'Les paramètres envoyés via AJAX sont incorrects';
            wp_send_json_error($object);
            die();
        }
        if ( $this->delivery == 1 ) {
            update_post_meta( $this->order_id, WC_TrackingTable::$meta_delivery, 1 );
        } else {
            delete_post_meta( $this->order_id, WC_TrackingTable::$meta_delivery );
        }
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
