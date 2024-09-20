<?php

class WC_TrackingTableNote {

    /**
     * Single instance of WC_TrackingTableNote
     * @var 	object
     * @access  private
     */
    private static $_instance = null;

    /**
     * Note value
     * @var 	int
     * @access  private
     */
    private $note;

    /**
     * Order id
     * @var 	string
     * @access  private
     */
    private $order_id;

	public function __construct() {
	    $this->note = urldecode($_REQUEST['note']) ?? null;
	    $this->order_id = $_REQUEST['order_id'] ?? null;
	}

    public function init() {
        $object = new stdClass();
        $object->id = $this->order_id;
        if ( is_null($this->order_id) || is_null($this->note) ) {
            $object->message = 'Les paramètres envoyés via AJAX sont incorrects';
            wp_send_json_error($object);
            die();
        }
        if ( !empty($this->note) ) {
            update_post_meta( $this->order_id, WC_TrackingTable::$meta_note, $this->note );
        } else {
            delete_post_meta( $this->order_id, WC_TrackingTable::$meta_note );
        }
        $object->message = 'Note de la commande mise à jour';
        wp_send_json_success($object);
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
