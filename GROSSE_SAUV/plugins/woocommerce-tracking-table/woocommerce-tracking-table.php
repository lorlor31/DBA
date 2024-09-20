<?php
/**
 * Plugin Name: WooCommerce Tracking Table
 * Plugin URI:
 * Description: Tableau de suivi des commandes avec import CSV fournisseur pour comparer les dates d'expédition. Liens pour signaler un retard ou envoyer une demande d'avis.
 * Version: 1.9.0
 * Author: DBA
 * Author URI: https://www.dba-france.com
 * Developer: Hani
 * Developer URI: https://jagullo.fr
 * Requires at least: 4.7
 * Tested up to: 5.9.0
 * Copyright: © 2022 DBA
 * License: Private License
 *
 * Text Domain: woocommerce-tracking-table
 * Domain Path:
 *
 * @package WC_TrackingTable
 * @category Extension
 * @author DBA
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define('WCTT_VERSION', '1.9.0');
define('DSP', DIRECTORY_SEPARATOR);
define('WCTT_SLUG', 'woocommerce-tracking-table');
define('WCTT_NAME', 'Woocommerce Tracking Table');
define('WCTT_MAIN_FILE', __FILE__);
define('WCTT_BASENAME', plugin_basename(WCTT_MAIN_FILE));
define('WCTT_PATH', dirname(WCTT_MAIN_FILE) . DSP);
define('WCTT_URL', plugins_url( '/', __FILE__));
define('WCTT_ASSETS', WCTT_URL . 'assets');
define('WCTT_TEMPLATES', WCTT_URL . 'templates');
define('WCTT_UPLOADS', wp_upload_dir()['basedir'] . DSP . 'tracking-table');
define('WCTT_DEBUG', false);

/**
 * Returns the main instance of WC_TrackingTable
 *
 * @since  1.0.0
 * @return object WC_TrackingTable
 */
function WC_TrackingTable() {
	return WC_TrackingTable::instance();
}

if ( is_admin() ) {
    WC_TrackingTable();
}

final class WC_TrackingTable {

	/**
	 * Single instance of WC_TrackingTable
	 * @var 	object
	 * @access  private
	 */
    private static $_instance = null;

    /**
     * Check woocommerce is actived
     * @var 	boolean
     * @access  private
     */
    private $dependencies = true;

    /**
     * Check if parser view
     * @var 	boolean
     * @access  protected
     */
    protected $is_parser = false;

    /**
     * Post meta to set vinco reference order
     * @var 	string
     * @access  public
     */
    public static $meta_vinco = '_tracking_vinco';

    /**
     * Post meta to set shipping date vendor
     * @var 	string
     * @access  public
     */
    public static $meta_date = '_tracking_date';

    /**
     * Post meta to set shipping process vendor
     * @var 	string
     * @access  public
     */
    public static $meta_process = '_tracking_process';

    /**
     * Post meta to set shipping blocked vendor
     * @var 	string
     * @access  public
     */
    public static $meta_blocked = '_tracking_blocked';

    /**
     * Post meta to count delay alert
     * @var 	string
     * @access  public
     */
    public static $meta_delay = '_tracking_delay';

    /**
     * Post meta to count review alert
     * @var 	string
     * @access  public
     */
    public static $meta_review = '_tracking_review';

    /**
     * Post meta to save note order
     * @var 	string
     * @access  public
     */
    public static $meta_note = '_tracking_note';

    /**
     * Post meta to set delivery order
     * @var 	string
     * @access  public
     */
    public static $meta_delivery = '_tracking_delivery';

    /**
     * Post meta to set highlight order
     * @var 	string
     * @access  public
     */
    public static $meta_follow = '_tracking_follow';

    public function __construct() {
        if ( is_admin() && wp_doing_ajax() ) {
            // ajax alert
            add_action('wp_ajax_nopriv_tracking-alert', array( $this, 'ajax_alert' ));
            add_action('wp_ajax_tracking-alert', array( $this, 'ajax_alert' ));
            // ajax follow
            add_action('wp_ajax_nopriv_tracking-follow', array( $this, 'ajax_follow' ));
            add_action('wp_ajax_tracking-follow', array( $this, 'ajax_follow' ));
            // ajax delivery
            add_action('wp_ajax_nopriv_tracking-delivery', array( $this, 'ajax_delivery' ));
            add_action('wp_ajax_tracking-delivery', array( $this, 'ajax_delivery' ));
            // ajax note
            add_action('wp_ajax_nopriv_tracking-note', array( $this, 'ajax_note' ));
            add_action('wp_ajax_tracking-note', array( $this, 'ajax_note' ));
        } elseif ( is_admin() ){
            add_action( 'plugins_loaded', array( $this, 'load' ) );
            add_action( 'admin_menu', array( $this, 'init' ), 99 );
        }
    }

    public function load() {
        $plugins = (array) get_option( 'active_plugins', array() );
        if ( !in_array( 'woocommerce/woocommerce.php', $plugins ) ) {
            $this->dependencies = false;
            add_action( 'admin_notices', function() {
                self::notify('Woocommerce Tracking Table requiert le plugin Woocommerce activé.', 'error');
            });
        }
    }

    public function init() {
        if ( !$this->dependencies ) {
            return;
        }
        add_submenu_page( 'woocommerce', 'Woocommerce Tracking Table', 'Tableau de suivi', 'manage_woocommerce', WCTT_SLUG, array( $this, 'run' ), 2 );
        global $pagenow;
        if ( $pagenow == 'admin.php' && $_GET['page'] == 'woocommerce-tracking-table' ) {
            ini_set('display_errors', WCTT_DEBUG);
            add_action( 'admin_enqueue_scripts', array( $this, 'load_assets' ) );
        }
    }

    public function load_assets() {
        wp_enqueue_style( 'wctt-style', esc_url( WCTT_ASSETS . '/style.css' ), false );
        wp_enqueue_script( 'wctt-script', esc_url( WCTT_ASSETS . '/script.js' ), false );
    }

    public function run() {
        echo '<div class="wrap">';
        echo '<div class="wrap-loading"><div class="custom-spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>';
        if ( isset($_FILES['i']) ) {
            require_once WCTT_PATH . 'includes/class-wc-tracking-table-parser.php';
            $import = WC_TrackingTableParser::instance();
            $this->is_parser = $import->parser();
        }
        if ( isset($_REQUEST['plugin-action']) && $_REQUEST['plugin-action'] == 'reset-track-action' ) {
            delete_post_meta_by_key( self::$meta_date );
            delete_post_meta_by_key( self::$meta_vinco );
            delete_post_meta_by_key( self::$meta_process );
            delete_post_meta_by_key( self::$meta_blocked );
            self::notify('Les fdr du fournisseur sont bien supprimées.', 'info');
        } elseif ( isset($_REQUEST['plugin-action']) && $_REQUEST['plugin-action'] == 'reset-follow-action' ) {
            delete_post_meta_by_key( self::$meta_follow );
            self::notify('Les suivis de commande sont bien supprimés.', 'info');
        }
        require_once WCTT_PATH . 'includes/class-wc-tracking-table-order.php';
        $table = WC_TrackingTableOrder::instance();
        if ( !$this->is_parser ) {
            $table->prepare_items();
        } else {
            $table->prepare_items_csv();
        }
        $table->display();
        echo '</div>';
    }

    public function ajax_alert() {
        require_once WCTT_PATH . 'includes/class-wc-tracking-table-alert.php';
        WC_TrackingTableAlert::instance()->send();
    }

    public function ajax_follow() {
        require_once WCTT_PATH . 'includes/class-wc-tracking-table-follow.php';
        WC_TrackingTableFollow::instance()->init();
    }

    public function ajax_delivery() {
        require_once WCTT_PATH . 'includes/class-wc-tracking-table-delivery.php';
        WC_TrackingTableDelivery::instance()->init();
    }

    public function ajax_note() {
        require_once WCTT_PATH . 'includes/class-wc-tracking-table-note.php';
        WC_TrackingTableNote::instance()->init();
    }

    public static function notify($message, $type = null) {
        $type = $type ?: 'info';
        echo '<div class="notice notice-' . $type . '"><p>' . __( $message ) . '</p></div>';
    }

    public static function instance () {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
