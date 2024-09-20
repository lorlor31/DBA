<?php

class WC_TrackingTableFollow {

    /**
     * Single instance of WC_TrackingTableFollow
     * @var 	object
     * @access  private
     */
    private static $_instance = null;

    /**
     * Follow value
     * @var 	int
     * @access  private
     */
    private $follow;

    /**
     * Order id
     * @var 	string
     * @access  private
     */
    private $order_id;

	public function __construct() {
	    $this->follow = $_REQUEST['follow'] ?? null;
	    $this->order_id = $_REQUEST['order_id'] ?? null;
	}

    public function init() {
        $object = new stdClass();
        $object->id = $this->order_id;
        if ( is_null($this->order_id) || is_null($this->follow) ) {
            $object->message = 'Les paramètres envoyés via AJAX sont incorrects';
            wp_send_json_error($object);
            die();
        }
        if ( $this->follow == 2 ) {
            update_post_meta( $this->order_id, WC_TrackingTable::$meta_follow, 2 );
        } elseif ( $this->follow == 1 ) {
            update_post_meta( $this->order_id, WC_TrackingTable::$meta_follow, 1 );
        } else {
            delete_post_meta( $this->order_id, WC_TrackingTable::$meta_follow );
        }
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
