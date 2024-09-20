<?php
/*
Plugin Name: Woocommerce Partial Shipment Pro
Plugin URI: http://wooexperts.com
Description: Add ability to partially ship an order.
Author: Vikram S
Version: 1.6 
Author URI: http://wooexperts.com 
Text Domain: wxp-partial-shipment
License: GPLv3
Requires at least: 5.4
Tested up to: 5.9
Requires PHP: 7.2
WC requires at least: 5.4
WC tested up to: 6.0
*/

if(!defined('ABSPATH')){
	exit;
}

class WXP_Partial_Shipment_Pro{

	protected static $_instance = null;
	protected $wc_partial_shipment_settings = array();
	protected $wc_partial_labels = array();
	protected $wxp_api = 'no';
	protected $wxp_api_key = '';
	protected $email_ids = array('customer_partial_shipment' => array('WC_Email_Customer_Partial_shipment',900));
	public static function instance(){

		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	function __construct(){
		if(!defined('WXP_PARTIAL_SHIP_PRO_VER')){
			define('WXP_PARTIAL_SHIP_PRO_VER',1.1);
		}
		if(!defined('WXP_PARTIAL_SHIP_DIR')){
			define('WXP_PARTIAL_SHIP_DIR',__DIR__);
		}
		add_action('init',array($this,'init_autoload'));
		add_action('init',array($this,'autoload_classes'));
		add_action('plugins_loaded',array($this,'load_textdomain'));
        add_action('plugins_loaded',array($this,'init_wxp_updater'));
		add_action('init',array($this,'load_settings'));
		add_action('init',array($this,'wxp_partial_complete_register_status'),999);
		add_filter('plugin_action_links_'.plugin_basename(__FILE__),array($this,'wxp_partial_action_links'),10,1);

		add_action('woocommerce_admin_order_item_headers',array($this,'wxp_order_item_headers'),10,1);
		add_action('woocommerce_admin_order_item_values',array($this,'wxp_order_item_values'),10,3);
		add_action('admin_enqueue_scripts',array($this,'wxp_admin_head'),999);
		add_action('wp_enqueue_scripts',array($this,'wxp_front'));
		add_action('woocommerce_order_item_add_action_buttons',array($this,'wxp_order_shipment_button'),10,1);
		add_action('woocommerce_checkout_update_order_meta',array($this,'wxp_set_order_data'),999,2);

		add_action('wp_ajax_wxp_order_shipment',array($this,'wxp_order_shipment'));
		add_action('wp_ajax_wxp_order_item_shipment',array($this,'wxp_order_item_shipment'));
		add_action('wp_ajax_wxp_order_set_shipped',array($this,'wxp_order_set_shipped'));

		add_action('woocommerce_order_item_meta_end',array($this,'wxp_order_item_icons'),999,4);
		add_filter('wc_order_statuses',array($this,'add_partial_complete_status'));

		add_filter('woocommerce_admin_order_preview_line_item_columns',array($this,'wxp_order_status_in_popup'),10,2);
		add_filter('woocommerce_admin_order_preview_line_item_column_wxp_status',array($this,'wxp_order_status_in_popup_value'),10,4);
		add_action('woocommerce_order_actions',array($this,'wxp_shipment_mail'),10,1);

		add_filter('woocommerce_email_classes',array($this,'wxp_shipment_email_class'),10,1);
		add_action('woocommerce_email_partially_shipped_order_details',array($this,'order_details'),10,4);
		add_action('wxp_order_status',array($this,'wxp_order_status_update'),10,1);
		add_action('woocommerce_order_status_completed',array($this,'wxp_order_status_switch'),10,1);
		add_action('woocommerce_saved_order_items',array($this,'wxp_save_order_items'),10,2);
		add_action('rest_api_init',array($this,'wxp_data_route'));
        add_action('woocommerce_order_action_wxp_partial_shipment',array($this,'trigger_wxp_shipment_mail'),10,1);

        add_action('wp',array($this,'check_wxp_shipment_mail'),10);
        add_action('add_email_in_wxp_queue',array($this,'wxp_update_queue'),10,1);
        add_action('wxp_shipment_mail_event',array($this,'wxp_shipment_defer_mail'),10,1);
        add_filter('cron_schedules',array($this,'wxp_shipment_intervals'),999,1);
        add_action('admin_head',array($this,'wxp_shipment_check_before'));

        add_filter('wc_partial_labels',array($this,'wxp_labels'),10,1);

    }

    function init_wxp_updater(){
        $plugin_file = basename(__DIR__).'/'.basename(__FILE__);
        $plugin_slug = strtolower(basename(__DIR__));
        include(dirname(__FILE__).'/updater/wxp-updater.php');
        new WXP_Updater($plugin_slug,$plugin_file);
    }

    function wxp_partial_action_links($links){
		$wxp_link = array(
			'<a href="'.admin_url('admin.php?page=wc-settings&tab=wxp_partial_shipping_settings').'">'.__('Settings','wc-cancel-order').'</a>',
			'<a target="_blank" href="http://wooexperts.com/pro-support/">'.__('Pro Support','wc-cancel-order').'</a>',
		);
		return array_merge($links,$wxp_link);
	}

	function plugin_path(){
		return untrailingslashit(plugin_dir_path(__FILE__));
	}

	function wxp_shipment_check_before(){
	    global $pagenow;
	    global $post_type;
	    if($post_type=='shop_order' && $pagenow=='post.php' && isset($_GET['action']) && $_GET['action']=='edit'){
            global $post;
            $id = $post->ID;
            $shipped = get_post_meta($id,'_wxp_shipment',true);
            $shipped = $this->repair_wxp_data($id,$shipped);
        }
    }

	function wxp_data_route(){
		register_rest_route('wxp-shipment-data','wxp-data', array(
				'methods' => 'POST',
				'callback' => array($this,'wxp_shipment_data'),
                'permission_callback' => '__return_true',
				'args' => array()
			)
		);
	}

	function wxp_shipment_data($args){

		if($this->wxp_api=='no'){
			return new WP_REST_Response(__('API is Disabled.','wxp-partial-shipment'),200);
		}
		$params = $args->get_params();
		if($params['key']!=$this->wxp_api_key){
			return new WP_REST_Response(__('Invalid API Key.','wxp-partial-shipment'),200);
		}

		if(isset($params['order-id']) && $params['order-id']){
			$order = wc_get_order($params['order-id']);
			if(is_a($order,'WC_Order')){
				$order_id = $order->get_id();
				$wxp_shipment = $this->get_wxp_shipment_data($order_id);
				if(isset($params['data']) && $params['data']=='set'){
					if(isset($params['items']) && is_array($params['items']) && !empty($params['items'])){
						foreach($params['items'] as $item){
							$wxp_shipment = $this->set_shipment($item,$wxp_shipment);
						}
					}
					update_post_meta($order_id,'_wxp_shipment',$wxp_shipment);
					update_post_meta($order_id,'_init_wxp_shipment',1);
					do_action('wxp_order_status',$order_id);
					$response = rest_ensure_response($wxp_shipment);
					$response->header('Content-Type',"application/json");
					return $response;
				}
				elseif(isset($params['data']) && $params['data']=='get'){
					$response = rest_ensure_response($wxp_shipment);
					$response->header('Content-Type',"application/json");
					return $response;
				}
			}
		}
		exit;
	}

	function init_autoload(){
		spl_autoload_register(function($class){
			$class = strtolower($class);
			$class = str_replace('_','-',$class);
			if(is_file(dirname(__FILE__).'/classes/'.$class.'.php')) {
				include_once('classes/'.$class.'.php');
			}
		});
	}

	function autoload_classes(){
		$wcp = new Wxp_Partial_Shipment_Settings();
		$wcp->init();
	}

	function load_settings(){

		$this->wc_partial_labels['shipped'] = get_option('partially_shipped_custom')!='' ? get_option('partially_shipped_custom') : __('Shipped','wxp-partial-shipment');
		$this->wc_partial_labels['not-shipped'] = get_option('partially_not_shipped_custom')!='' ? get_option('partially_not_shipped_custom') : __('Not Shipped','wxp-partial-shipment');
		$this->wc_partial_labels['partially-shipped'] = get_option('partially_shipped_label_custom')!='' ? get_option('partially_shipped_label_custom') : __('Partially Shipped','wxp-partial-shipment');

        $this->wc_partial_labels = apply_filters('wc_partial_labels',$this->wc_partial_labels);

		$this->wxp_api = get_option('enable_wxp_api')!='' ? get_option('enable_wxp_api') : 'no';
		$this->wxp_api_key = get_option('wxp_api_key');

		$this->wc_partial_shipment_settings = array(
			'partially_shipped_status' => get_option('partially_shipped_status')!='' ? get_option('partially_shipped_status') : 'yes',
			'partially_auto_complete' => get_option('partially_auto_complete')!='' ? get_option('partially_auto_complete') : 'yes',
			'partially_hide_status' => get_option('partially_hide_status')!='' ? get_option('partially_hide_status') : 'yes',
			'partially_enable_status_popup' => get_option('partially_enable_status_popup')!='' ? get_option('partially_enable_status_popup') : 'yes',
		);
	}

	function wxp_set_order_data($order_id,$data){
		$order = wc_get_order($order_id);
		$shipment_data = array();
		if(is_a($order,'WC_Order')){
			$items = $order->get_items();
			if(is_array($items) && !empty($items)){
				foreach($items as $item_key=>$item){
					if(is_a($item,'WC_Order_Item_Product')){
						$item_data = $item->get_data();
                        $product = $item->get_product();
						if(isset($item_data['quantity'])){
							$item_qty = $item_data['quantity'];
						}
						$shipment_data[] = array(
							'order_id'=> isset($item_data['order_id']) ? $item_data['order_id'] : $order->get_id(),
							'item_id' => isset($item_data['id']) ? $item_data['id'] : $item->get_id(),
							'name'    => isset($item_data['name']) ? $item_data['name'] : $item->get_name(),
                            'sku'     => $product->get_sku(),
							'qty'     => isset($item_qty) ? $item_qty : $item->get_quantity(),
							'shipped' => 0,
							'status'  => 'not-shipped',
						);
					}
				}
			}
		}
		update_post_meta($order_id,'_wxp_shipment',$shipment_data);
		update_post_meta($order_id,'_init_wxp_shipment',0);
	}

	function wxp_front(){
		wp_enqueue_style('wxp_front_style',plugins_url('',__FILE__).'/assets/css/front.css');
	}

	function wxp_admin_head(){
		$screen = get_current_screen();
		if(isset($screen->id) && in_array($screen->id,array('edit-shop_order','shop_order'))){
			wp_enqueue_style('fancybox',plugins_url('',__FILE__).'/assets/css/jquery.fancybox.min.css');
			wp_enqueue_style('wxp_style',plugins_url('',__FILE__).'/assets/css/admin-style.css');
			wp_enqueue_script('fancybox',plugins_url('',__FILE__).'/assets/js/jquery.fancybox.min.js',array('jquery'),WXP_PARTIAL_SHIP_PRO_VER,false);
			wp_register_script('wxp_partial_ship_script',plugins_url('',__FILE__).'/assets/js/admin-script.js',array('fancybox'),WXP_PARTIAL_SHIP_PRO_VER,true);

			$js_array = array(
				'wxp_loader' => untrailingslashit(plugins_url('/', __FILE__ )).'/images/ajax-loader.gif',
				'wxp_ajax' => admin_url('admin-ajax.php'),
				'wxp_nonce' => wp_nonce_field('wxp_partial_shipment','wxp_partial_ship',false,false),
				'wxp_title' => __('Title','wxp-partial-shipment'),
				'wxp_qty' => __('Quantity','wxp-partial-shipment'),
				'wxp_ship' => __('Shipped','wxp-partial-shipment'),
				'wxp_bulk_action' => __('Bulk Actions','wxp-partial-shipment'),
				'wxp_bulk_mark_shipped' => __('Mark as Shipped','wxp-partial-shipment'),
				'wxp_bulk_mark_not_shipped' => __('Unset Shipped','wxp-partial-shipment'),
				'wxp_update' => __('Update','wxp-partial-shipment'),
				'wxp_order_nonce' => wp_create_nonce('order-item'),
			);
			wp_localize_script('wxp_partial_ship_script','wxp_partial_ship',$js_array);
			wp_enqueue_script('wxp_partial_ship_script');
		}
	}

	function wxp_order_item_headers($order){
		echo '<th class="wxp-partital-item-head">'.__('Shipment','wxp-partial-shipment').'</th>';
		$order_id = $order->get_id();
		if($order_id){
			echo '<th class="wxp-partital-item-head">&nbsp;</th>';
		}
	}

	function wxp_order_item_values($product,$item,$item_id){

		if($product){
			$order_id = $item->get_order_id();
			$wxp_shipments = $this->get_wxp_shipment_data($order_id);
			$row = false;
			if(is_array($wxp_shipments) && !empty($wxp_shipments) && !$product->is_virtual()){
				foreach($wxp_shipments as $wxp_shipment){
					if(isset($wxp_shipment['item_id']) && $wxp_shipment['item_id']==$item_id){
						$row = true;
						$icon = '';
						if(isset($wxp_shipment['status']) && $wxp_shipment['status']=='shipped'){
							$icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').': '.$wxp_shipment['shipped'].'/'.$wxp_shipment['qty'].'"><span class="wxp-shipped wxp-ship-status" title="'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').' - '.$wxp_shipment['shipped'].'</span></a>';
						}
						elseif(isset($wxp_shipment['status']) && $wxp_shipment['status']=='not-shipped'){
							$icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').'"><span class="wxp-not-shipped wxp-ship-status" title="'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').' - '.$wxp_shipment['qty'].'</span></a>';
						}
						elseif(isset($wxp_shipment['status']) && $wxp_shipment['status']=='partially-shipped'){
							$icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').': '.$wxp_shipment['shipped'].'/'.$wxp_shipment['qty'].'"><span class="wxp-partial-shipped wxp-ship-status" title="'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').' - '.$wxp_shipment['shipped'].'</span></a>';
						}
						echo '<td class="wxp-partital-line-item"><a href="javascript:void(0);" data-item-id="'.$item_id.'" data-order-id="'.$order_id.'" title="'.__('Manage Shipment','wxp-partial-shipment').'" class="wxp-icons icon-wxp-set-shipping"></a></td>';
						echo '<td class="wxp-partital-item-icon" width="1%">'.$icon.'</td>';
					}
				}
			}
			if(!$row){
				echo '<td></td>';
				echo '<td></td>';
			}
		}
		else
		{
			echo '<td></td>';
			echo '<td></td>';
		}
	}

	function wxp_order_shipment_button($order){
		echo '<button type="button" data-order-id="'.$order->get_id().'" class="button wxp-order-shipment">'.__('Shipment','wxp-partial-shipment').'</button>';
	}

	function wxp_order_shipment(){
		$valid = false;
		if(isset($_POST['order_id']) && $_POST['order_id']){
			$order_id  = $_POST['order_id'];
            $init = (int)get_post_meta($order_id,'_init_wxp_shipment',true);
			$order = wc_get_order($order_id);
			// BYME
		    if (!$order instanceof WC_Order) {
				echo json_encode(array('error' => 'Invalid order ID'));
            exit();
			}
			
			$wxp_shipment = $this->get_wxp_shipment_data($order_id);
			if(is_array($wxp_shipment) && !empty($wxp_shipment)){
				$valid = true;
				foreach($wxp_shipment as $item){
                    $pitem    = $order->get_item($item['item_id']);
			        // BYME
					if (!$pitem instanceof WC_Order_Item) {
						continue; // Skip invalid items
					}		
					
                    $product = $pitem->get_product();
					// BYME
				    if (!$product instanceof WC_Product) {
						continue; // Skip invalid products
					}
					
					$products[] = array(
						'id' => $item['item_id'],
						'name' => $item['name'],
                        'sku' => $product->get_sku(),
                        'virtual' => $product->is_virtual(),
						'qty' => $item['qty'],
						'shipped' => $item['shipped'],
						'order_id' => $item['order_id']
					);
				}
			}
		}
		echo json_encode(array('order_id'=>$_POST['order_id'],'valid'=>$valid,'products'=>$products,'init'=>$init));
		exit();
	}

	function wxp_order_item_shipment(){

		$products = array();
		$valid = false;
		$item_id = isset($_POST['item_id']) ? $_POST['item_id'] : 0;
		$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
		if($order_id && $item_id){
			$wxp_shipment = $this->get_wxp_shipment_data($order_id);
			if(is_array($wxp_shipment) && !empty($wxp_shipment)){
				$valid = true;
				foreach($wxp_shipment as $item){
					if($item['item_id']==$item_id){
						$valid = true;
						$products[] = array(
							'id' => $item['item_id'],
							'name' => $item['name'],
							'qty' => $item['qty'],
							'shipped' => $item['shipped'],
							'order_id' => $item['order_id']
						);
						break;
					}
				}
			}
		}
		echo json_encode(array('order_id'=>$order_id,'item_id'=>$item_id,'valid'=>$valid,'products'=>$products));
		exit();
	}

	function wxp_order_set_shipped(){
		$order_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
		$wxp_shipment = $this->get_wxp_shipment_data($order_id);
		if(isset($_POST['order_id']) && $_POST['order_id']){
			if(isset($_POST['shipped']) && is_array($_POST['shipped']) && !empty($_POST['shipped'])){
				foreach($_POST['shipped'] as $shipped_item_key=>$shipped_item){
					$wxp_shipment = $this->set_shipment($shipped_item,$wxp_shipment);
				}
			}
			update_post_meta($_POST['order_id'],'_wxp_shipment',$wxp_shipment);
			update_post_meta($_POST['order_id'],'_init_wxp_shipment',1);
		}
		do_action('wxp_order_status',$order_id);
		$status = get_post_meta($order_id,'_status_key',true);
		echo json_encode(array('order_id'=>$order_id,'status'=>$status));
		exit();
	}

	function set_shipment($element,$shipment_data=array()){
		if(is_array($shipment_data) && !empty($shipment_data)){
			foreach($shipment_data as $key=>$val){
				if(isset($val['item_id']) && isset($element['item_id']) && $val['item_id']==$element['item_id']){
					$shipped = isset($element['shipped']) && $element['type']=='shipped' ? $element['shipped'] : 0;
					$shipment_data[$key]['shipped'] = $shipped;
					if($shipped>0){
						$status =  $shipped < $val['qty'] ? 'partially-shipped' : 'shipped';
					}
					else
					{
						$status =  'not-shipped';
					}
					$shipment_data[$key]['status'] = $status;
				}
				elseif(isset($val['sku']) && isset($element['sku']) && $val['sku']==$element['sku']){
                    $shipped = isset($element['shipped']) && $element['type']=='shipped' ? $element['shipped'] : 0;
                    $shipment_data[$key]['shipped'] = $shipped;
                    if($shipped>0){
                        $status =  $shipped < $val['qty'] ? 'partially-shipped' : 'shipped';
                    }
                    else
                    {
                        $status =  'not-shipped';
                    }
                    $shipment_data[$key]['status'] = $status;
                }
			}
		}
		return $shipment_data;
	}

	function repair_wxp_data($order_id,$rows){
		$update = false;
		$order = wc_get_order($order_id);
		$item_ids = array();
		if(is_array($rows) && !empty($rows)){
			foreach($rows as $row_key=>$row){
				if(isset($rows[$row_key]['type']) && isset($rows[$row_key]['item_id'])){
					$item  = WC_Order_Factory::get_order_item($rows[$row_key]['item_id']);
					if(is_a($item,'WC_Order_Item_Product')){
						$update = true;
						$item_data = $item->get_data();
                        $product = $item->get_product();
                        $sku = is_a($product,'WC_Product') ? $product->get_sku() : '';
						if($rows[$row_key]['shipped']>0){
							$status =  $rows[$row_key]['shipped'] < $item_data['quantity'] ? 'partially-shipped' : 'shipped';
						}
						else
						{
							$status =  'not-shipped';
						}
						$item_ids[] = $item->get_id();
						$rows[$row_key] = array(
							'order_id' => $rows[$row_key]['order_id'],
							'item_id' => $rows[$row_key]['item_id'],
							'name' => $item_data['name'],
							'sku' => $sku,
							'qty' => $item_data['quantity'],
							'shipped' => $rows[$row_key]['shipped'],
							'status' => $status,
						);
					}
				}
				elseif(isset($rows[$row_key]['item_id'])){
					$item_ids[] = $rows[$row_key]['item_id'];
				}
			}
		}
		$items = $order->get_items();
		if(is_array($items) && !empty($items)){
			$count = is_array($rows) ? count(array_filter($rows)) : 0;
			$rows = $count>0 ? array_values($rows) : array();
			foreach($items as $itm_key=>$itm){
				if(is_a($itm,'WC_Order_Item_Product')){
					if(in_array($itm_key,$item_ids)){
						continue;
					}
					$item_data = $itm->get_data();
                    $product = $itm->get_product();
                    $sku = is_a($product,'WC_Product') ? $product->get_sku() : '';
					$update = true;
					$rows[$count] = array(
						'order_id' => $item_data['order_id'],
						'item_id' => $item_data['id'],
						'name' => $item_data['name'],
                        'sku' => $sku,
						'qty' => $item_data['quantity'],
						'shipped' => 0,
						'status' => 'not-shipped',
					);


					$count++;
				}
			}
		}

		if($update){
			update_post_meta($order_id,'_wxp_shipment',$rows);
		}
		return $rows;
	} 

	function get_wxp_shipment_data($order_id){
		$shipped = get_post_meta($order_id,'_wxp_shipment',true);
		$shipped = $this->repair_wxp_data($order_id,$shipped);
		return $shipped;
	}

	function wxp_order_item_icons($item_id, $item, $order, $bol = false){

        $product = $item->get_product();
		$order_id = $item->get_order_id();
		$init = get_post_meta($order_id,'_init_wxp_shipment',true);
		$show = true;
		if($this->wc_partial_shipment_settings['partially_hide_status']=='yes' && $init!='1'){
			$show = false;
		}
		if($show && !$product->is_virtual()){
			$shipped = $this->get_wxp_shipment_data($order_id);
			if(is_array($shipped) && !empty($shipped)){
				foreach($shipped as $shipped_item){
					if(isset($shipped_item['item_id']) && $shipped_item['item_id']==$item_id){
						if(isset($shipped_item['status']) && $shipped_item['status']=='shipped'){
							$icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').': '.$shipped_item['shipped'].'/'.$shipped_item['qty'].'"><span class="wxp-ship-status wxp-shipped" title="'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').' '.$shipped_item['shipped'].'</span></a>';
						}
						elseif(isset($shipped_item['status']) && $shipped_item['status']=='not-shipped'){
							$icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').'"><span class="wxp-ship-status wxp-not-shipped" title="'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').' '.$shipped_item['qty'].'</span></a>';
						}
						elseif(isset($shipped_item['status']) && $shipped_item['status']=='partially-shipped'){
							$icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').': '.$shipped_item['shipped'].'/'.$shipped_item['qty'].'"><span class="wxp-ship-status wxp-partial-shipped" title="'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').' '.$shipped_item['shipped'].'</span></a>';
						}
					}
				}
			}
			if(is_page()){
				echo $icon;
			}
		}
	}

	function wxp_labels($labels){
	    if(is_array($labels) && !empty($labels)){
	        foreach($labels as $lk=>$lv){
	            if(trim($lv)!=''){
                    $labels[$lk]=$lv.' x ';
                }
            }
        }
        return $labels;
    }

	function add_partial_complete_status($statuses){
	    if(isset($this->wc_partial_shipment_settings['partially_shipped_status'])){
            if($this->wc_partial_shipment_settings['partially_shipped_status']=='yes'){
                $statuses['wc-partial-shipped'] = __('Partially Shipped','wxp-partial-shipment');
            }
        }
		return $statuses;
	}

	function wxp_order_status_in_popup($columns,$order){
	    if(isset($this->wc_partial_shipment_settings['partially_enable_status_popup'])){
            if($this->wc_partial_shipment_settings['partially_enable_status_popup']=='yes'){
                $columns['wxp_status'] = __('Status','wxp-partial-shipment');
            }
        }
		return $columns;
	}

	function wxp_partial_complete_register_status(){
	    if(isset($this->wc_partial_shipment_settings['partially_shipped_status'])){
            if($this->wc_partial_shipment_settings['partially_shipped_status']=='yes'){
                register_post_status('wc-partial-shipped', array(
                    'label' => __('Partially Shipped','wxp-partial-shipment'),
                    'public' => true,
                    'exclude_from_search' => false,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop('Partially Shipped <span class="count">(%s)</span>', 'Partially Shipped <span class="count">(%s)</span>')
                ));
            }
        }
	}

	function wxp_order_status_in_popup_value($val,$item,$item_id,$order){
	    if(isset($this->wc_partial_shipment_settings['partially_enable_status_popup'])){
            if($this->wc_partial_shipment_settings['partially_enable_status_popup']=='yes'){
                $order_id = $item->get_order_id();
                $product = $item->get_product();
                $wxp_shipments = $this->get_wxp_shipment_data($order_id);
                $icon = '';
                if(is_array($wxp_shipments) && !empty($wxp_shipments) && !$product->is_virtual()){
                    foreach($wxp_shipments as $wxp_shipment){
                        if(isset($wxp_shipment['item_id']) && $wxp_shipment['item_id']==$item_id){
                            if(isset($wxp_shipment['status']) && $wxp_shipment['status']=='shipped'){
                                $icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').': '.$wxp_shipment['shipped'].'/'.$wxp_shipment['qty'].'"><span class="wxp-ship-status wxp-shipped" title="'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['shipped'],'wxp-partial-shipment').wxp_shipment['shipped'].'</span></a>';
                            }
                            elseif(isset($wxp_shipment['status']) && $wxp_shipment['status']=='partially-shipped'){
                                $icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').': '.$wxp_shipment['shipped'].'/'.$wxp_shipment['qty'].'"><span class="wxp-ship-status wxp-partial-shipped" title="'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').$wxp_shipment['shipped'].'</span></a>';
                            }
                            elseif(isset($wxp_shipment['status']) && $wxp_shipment['status']=='not-shipped'){
                                $icon = '<a href="javascript:void(0);" class="wxp-top" title="'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').'"><span class="wxp-ship-status wxp-not-shipped" title="'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').'">'.__($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').$wxp_shipment['qty'].'</span></a>';
                            }
                        }
                    }
                }
                $val = $icon;
            }
        }
		return $val;
	}

	function wxp_shipment_mail($actions){
		$actions['wxp_partial_shipment'] = __('Partial shipment notification','wxp-partial-shipment');
		return $actions;
	}

	function set_email_sent_message($location){
		return add_query_arg('message',11,$location);
	}

	function wxp_shipment_email_class($emails){
		$emails['WC_Email_Customer_Partial_shipment'] = include dirname(__FILE__).'/inc/class-wc-email-partial-shipment.php';
		return $emails;
	}

	function order_details($order, $sent_to_admin = false, $plain_text = false, $email = ''){

		if($plain_text){
			wc_get_template(
				'emails/plain/email-partial-order-details.php', array(
					'order'         => $order,
					'sent_to_admin' => $sent_to_admin,
					'plain_text'    => $plain_text,
					'email'         => $email,
				),
				'',
				WXP_PARTIAL_SHIP_DIR.'/'
			);
		} else {
			wc_get_template(
				'emails/email-partial-order-details.php', array(
					'order'         => $order,
					'sent_to_admin' => $sent_to_admin,
					'plain_text'    => $plain_text,
					'email'         => $email,
				),
				'',
				WXP_PARTIAL_SHIP_DIR.'/'
			);
		}
	}

	function wc_get_email_partial_order_items( $order, $args = array() ) {
		ob_start();

		$defaults = array(
			'show_sku'      => false,
			'show_image'    => false,
			'image_size'    => array( 32, 32 ),
			'plain_text'    => false,
			'sent_to_admin' => false,
		);

		$args     = wp_parse_args( $args, $defaults );
		$template = $args['plain_text'] ? 'emails/plain/email-partial-order-items.php' : 'emails/email-partial-order-items.php';

		wc_get_template( $template, apply_filters( 'woocommerce_email_order_items_args', array(
			'order'               => $order,
			'items'               => $order->get_items(),
			'show_download_links' => $order->is_download_permitted() && ! $args['sent_to_admin'],
			'show_sku'            => $args['show_sku'],
			'show_purchase_note'  => $order->is_paid() && ! $args['sent_to_admin'],
			'show_image'          => $args['show_image'],
			'image_size'          => $args['image_size'],
			'plain_text'          => $args['plain_text'],
			'sent_to_admin'       => $args['sent_to_admin'],
		) ),
			'',
			WXP_PARTIAL_SHIP_DIR.'/');

		return apply_filters( 'woocommerce_email_order_items_table', ob_get_clean(), $order );
	}

	function get_item_status($item_id, $item, $order){
		$icon = '';
		$order_id = $item->get_order_id();
		$rows = $this->get_wxp_shipment_data($order_id);
        $product = $item->get_product();
		if(is_array($rows) && !empty($rows) && !$product->is_virtual()){
			foreach($rows as $row){
				if(isset($row['item_id']) && $row['item_id']==$item_id){
					if(isset($row['status']) && $row['status']=='shipped'){
						$icon = __($this->wc_partial_labels['shipped'],'wxp-partial-shipment').$row['shipped'];
					}
					elseif(isset($row['status']) && $row['status']=='partially-shipped'){
						$icon = __($this->wc_partial_labels['partially-shipped'],'wxp-partial-shipment').$row['shipped'];
					}
					elseif(isset($row['status']) && $row['status']=='not-shipped'){
						$icon = __($this->wc_partial_labels['not-shipped'],'wxp-partial-shipment').$row['qty'];;
					}
					break;
				}
			}
		}
		return $icon;
	}

	function wxp_order_status_update($order_id){

		$wxp_shipment = $this->get_wxp_shipment_data($order_id);
		if(is_array($wxp_shipment) && !empty($wxp_shipment)){
			$total_count = count($wxp_shipment);
			$shipped = $not_shipped = $partially_shipped = 0;
			foreach($wxp_shipment as $item){
				if($item['status']=='shipped'){
					$shipped++;
				}
				elseif($item['status']=='partially-shipped'){
					$partially_shipped++;
				}
				elseif($item['status']=='not-shipped'){
					$not_shipped++;
				}
			}

			$statuses = wc_get_order_statuses();
			$statuses_key = is_array($statuses) ? array_keys($statuses) : array();
			delete_post_meta($order_id,'_status_key');
			if($total_count>0 && $shipped==$total_count && is_array($statuses_key) && in_array('wc-completed',$statuses_key)){
				$order = wc_get_order($order_id);
				if(is_a($order,'WC_Order') && $this->wc_partial_shipment_settings['partially_auto_complete']=='yes'){
					$order->update_status('completed',__('Order Completed by Woocommerce Partial Shipment.','wxp-partial-shipment'));
					update_post_meta($order_id,'_status_key','wc-completed');
				}
			}
			elseif($total_count>0 && $not_shipped==$total_count && is_array($statuses_key) && in_array('wc-processing',$statuses_key)){
				$order = wc_get_order($order_id);
				if(is_a($order,'WC_Order')){
					$order->update_status('processing',__('Order Processed by Woocommerce Partial Shipment.','wxp-partial-shipment'));
					update_post_meta($order_id,'_status_key','wc-processing');
				}
			}
			elseif($total_count>0 && is_array($statuses_key) && in_array('wc-partial-shipped',$statuses_key)){
				$order = wc_get_order($order_id);
				if(is_a($order,'WC_Order') && $this->wc_partial_shipment_settings['partially_shipped_status']=='yes'){
					$order->update_status('partial-shipped',__('Order Partially Shipped by Woocommerce Partial Shipment.','wxp-partial-shipment'));
					update_post_meta($order_id,'_status_key','wc-partial-shipped');
					do_action('add_email_in_wxp_queue',$order_id);
				}
			}
		}
	}

	function wxp_order_status_switch($order_id){
		$rows = $this->get_wxp_shipment_data($order_id);
		$update = false;
		if(is_array($rows) && !empty($rows)){
			foreach($rows as $row_key=>$row){
				if(isset($rows[$row_key]['status'])){
					$update = true;
					$rows[$row_key]['status'] = 'shipped';
					$rows[$row_key]['shipped'] = $rows[$row_key]['qty'];
				}
			}
		}
		if($update){
			update_post_meta($order_id,'_wxp_shipment',$rows);
		} 
	}

	function load_textdomain(){
		load_plugin_textdomain('wxp-partial-shipment',false,dirname(plugin_basename(__FILE__)).'/lang/');
	}

	function update_status($shipped){
		if(is_array($shipped) && !empty($shipped)){
			foreach($shipped as $ship_item_key => $ship_item){
				if(isset($ship_item['shipped']) && $ship_item['shipped']>$ship_item['qty']){
					$shipped[$ship_item_key]['shipped']=$ship_item['qty'];
				}

				if(isset($ship_item['qty']) && $ship_item['qty']>0 && $ship_item['shipped']==0){
					$shipped[$ship_item_key]['status']='not-shipped';
				}
				elseif(isset($ship_item['qty']) && $ship_item['qty']>0 && $ship_item['shipped']>0 && $ship_item['shipped']<$ship_item['qty']){
					$shipped[$ship_item_key]['status']='partially-shipped';
				}
				elseif(isset($ship_item['qty']) && $ship_item['qty']>0 && $ship_item['shipped']==$ship_item['qty']){
					$shipped[$ship_item_key]['status']='shipped';
				}
			}
		}
		return $shipped;
	}

	function wxp_save_order_items($order_id,$items){
		$shipped = get_post_meta($order_id,'_wxp_shipment',true);
		if(is_array($shipped) && !empty($shipped)){
			foreach($shipped as $ship_item_key=>$ship_item){
				if(is_array($items) && !empty($items)){
					if(isset($items['order_item_id']) && is_array($items['order_item_id'])){
						foreach($items['order_item_id'] as $item_id){
							if(isset($ship_item['item_id']) && $ship_item['item_id']==$item_id){
								if(isset($items['order_item_qty'][$item_id])){
									$shipped[$ship_item_key]['qty'] = $items['order_item_qty'][$item_id];
								}
							}
						}
					}
				}
			}
		}
		$shipped = $this->repair_wxp_data($order_id,$shipped);
		$shipped = $this->update_status($shipped);
		update_post_meta($order_id,'_wxp_shipment',$shipped);
	}

    function trigger_wxp_shipment_mail($order){
        $order_id = $order->get_id();
        WC()->payment_gateways();
        WC()->shipping();
        $emails = WC()->mailer()->get_emails();
        $emails['WC_Email_Customer_Partial_shipment']->trigger($order_id);
        $order->add_order_note(__( 'Partial order details manually sent to customer.','wxp-partial-shipment'),false,true);
        add_filter('redirect_post_location',array($this,'set_email_sent_message'));
    }

    function wxp_shipment_intervals($schedules){
        $schedules['wxp_shipment_event'] = array(
            'interval' => apply_filters('wc_partial_shipment_email_interval',900),
            'display' => __('Every 15 Minute')
        );
        return $schedules;
    }

    function wxp_update_queue($order_id){
        $email_queue = (array)get_option('_wxp_email_queue');
        $email_queue[$order_id] = array('id'=>$order_id,'time'=>current_time('timestamp',0));
        update_option('_wxp_email_queue',array_filter($email_queue),'no');
    }

    function check_wxp_shipment_mail(){
	    $args = array('queue'=>array());
	    //$cron_context = array( 'WP Cron' );
        if(!wp_next_scheduled('wxp_shipment_mail_event',$args)){
            wp_schedule_event(time(),'wxp_shipment_event','wxp_shipment_mail_event',$args);
        }
    }

    function wxp_shipment_defer_mail($queue=array()){
		$check_time  = apply_filters('wc_partial_shipment_check_interval',300);
        $queue = get_option('_wxp_email_queue');
        if(is_array($queue) && !empty($queue)){
            foreach($queue as $order_id=>$data){
                if(isset($data['time']) && $data['time'] && ($data['time']+$check_time)<current_time('timestamp',0)){
                    $order = wc_get_order($order_id);
                    if(is_a($order,'WC_Order')){
                        $order_id = $order->get_id();
                        WC()->payment_gateways();
                        WC()->shipping();
                        $emails = WC()->mailer()->get_emails();
                        $emails['WC_Email_Customer_Partial_shipment']->trigger($order_id);
                        $order->add_order_note(__( 'Partial order details sent to customer.','wxp-partial-shipment'),false,true);
                        unset($queue[$order_id]);
                        update_option('_wxp_email_queue',array_filter($queue),'no');
                    }
                }
            }
        }
    }

}

function WXP_Partial_Shipment_Init_Pro(){
	return WXP_Partial_Shipment_Pro::instance();
}

if(function_exists('is_multisite') && is_multisite()){

    if(!function_exists( 'is_plugin_active_for_network')){
        require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
    }
    if(is_plugin_active_for_network('woocommerce/woocommerce.php')){
        WXP_Partial_Shipment_Init_Pro();
    }
}
elseif(in_array('woocommerce/woocommerce.php',apply_filters('active_plugins',get_option('active_plugins')))){
    WXP_Partial_Shipment_Init_Pro();
}

?>