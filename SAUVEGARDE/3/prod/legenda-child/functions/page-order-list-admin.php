<?php

if (is_admin()){
/**
     * Add vosfactures link in the actions order list admin
     */
    add_action( 'admin_enqueue_scripts', function() { ?>
        <style>
            .widefat .column-wc_actions a.issue-invoice::after{content:"\f498";}
            .widefat .column-wc_actions a.download-invoice::after{content:"\f115";}
            .widefat .column-wc_actions .wc-action-button-invoice::after{font-size:1.2em;margin-top:0!important;}
        </style>
        <script>
            window.addEventListener("load", function(event) {
                jQuery(".issue-invoice").each(function (index) {
                    jQuery(this).one("click", function(e) {
                        e.preventDefault();
                        var data = {
                            action: "invoice_handler",
                            command: "issue",
                            order_id: jQuery(this).data('id'),
                            issue_kind: "issue"
                        };
                        let post = jQuery.post(ajaxurl, data);
                        post.always(function () {
                            location.reload();
                        });
                    });
                });
            });
        </script>
    <?php }, 20);
    add_filter( 'woocommerce_admin_order_actions', 'filter_woocommerce_admin_order_actions', 10, 2 );
    function filter_woocommerce_admin_order_actions( $actions, $object ) {
        if ( $object->has_status( array( 'completed' ) ) && class_exists('VosfacturesDatabase') ) {
            $db = new VosfacturesDatabase();
            $invoice = $db->get_last_invoice( $object->get_id() );
            if ( ! empty( $invoice ) && ! empty( $invoice->external_id ) && empty( $invoice->error ) ) {
                echo sprintf( '<a class="button wc-action-button wc-action-button-invoice download-invoice" target="_blank" href="%1$s" aria-label="%2$s" title="%2$s">%2$s</a>', esc_html( $invoice->view_url ), __( 'Voir facture' ) );
            } else {
                echo sprintf( '<a class="button wc-action-button wc-action-button-invoice issue-invoice" data-id="%1$s" href="" aria-label="%2$s" title="%2$s">%2$s</a>', $object->get_id(), __( 'Créer facture' ) );
            }
        }
        return $actions;
    }
    /*
    * Affiche @Marion sur la page de liste de commande
    */
	function check_for_special_order_condition($column) {
		global $post;
		if ($column === 'order_number') {
			$order = wc_get_order($post->ID);
			if ($order) {
				$customer_id = $order->get_customer_id();
				if ($customer_id) {
					$args = array(
						'customer_id' => $customer_id,
						'status' => 'completed',
						'date_before' => '2023-12-31',
						'limit' => -1, // Chercher toutes les commandes correspondantes
					);
					$orders = wc_get_orders($args);
					$special_condition_met = false;
					foreach ($orders as $order_item) {
						$date_completed = $order_item->get_date_completed();
						if ($date_completed && $date_completed->date('Y-m-d') <= '2023-12-31') {
							$special_condition_met = true;
							break;
						}
					}
					if ($special_condition_met) {
						echo '<div style="color: #e5128f; font-weight: bold;font-size:1.1em;padding:0 10px;">Suivi par @Marion</div>';
					}
				}
			}
		}
	}
	add_action('manage_shop_order_posts_custom_column', 'check_for_special_order_condition', 20);

    /*
    * Affiche les autres commandes / devis du client, sur la page de liste de commande
    */
	add_action('manage_shop_order_posts_custom_column', 'custom_order_number_column_content', 30, 1);
    function custom_order_number_column_content($column) {
        global $post;
        if ($column === 'order_number') {
            $order = wc_get_order($post->ID);
            if ($order) {
                $order_id = $order->get_order_number();
                $customer_id = $order->get_customer_id();
                if ( $customer_id ) {
                    $args = array(
                        'customer_id' => $customer_id,
                        'limit' => 4,
                    );
                    $cmd_info ='';
                    $orders = wc_get_orders($args);
                    $i=1;
                    foreach ($orders as $order_item) {
                        if ($i % 2 == 0){
                            $paire = "#f8f8f8";
                        }else {
                            $paire = "#fff";}
                        if($order_item->get_id() !== $order->get_id()){
                            $cmd_info  .= '<ul class="deux-colonnes" style="text-align:center;padding:5px;background-color:' . $paire . ';display: flex;flex-wrap: wrap;padding: 0;margin: 0;"><li>' . $order_item->get_order_number() . '</li>';
                            $orderItemId = $order_item->get_id();
                            $order_status = get_post_status_object( get_post_status( $orderItemId ) );
                            $cmd_info .= '<li>' . $order_status->label . '</li>';
                            $cmd_info .= '<li>' . $order_item->get_date_created()->format('d/m/Y') . '</li>';
                            $cmd_info .= '<li>' . $order_item->get_total() . '€</li></ul>';
                            $i++;
                        }
                    }
                    $total_orders = wc_get_customer_order_count($customer_id);
                    $total_orders = $total_orders - 1;
                    if ($total_orders > 0){
                        echo '<style>.deux-colonnes li{flex-basis: 50%;box-sizing: border-box;} .objets-lies{position:relative;color:red;}.objets-lies:hover span{left:0px;top:20px;background-color:#fff;border:1px solid #000;z-index:15000;width:max-content;}.objets-lies span{position:absolute;left:-5000px}</style><div class="objets-lies">'. $total_orders . ' objets liés<span>' . $cmd_info . '</span></div>';
                    }
                }
            }
        }
    }

    /**
     * Add paid order column in the admin list orders
     */
    add_filter( 'manage_edit-shop_order_columns', 'register_paid_order_column', 10, 1 );
    function register_paid_order_column( $columns ) {
        return array_slice( $columns, 0, 8, true )
            + array( 'bank_transfer' => 'Vir. / chq.' )
            + array_slice( $columns, 8, NULL, true );;
    }
    add_action( 'manage_shop_order_posts_custom_column', 'display_paid_order_column', 10, 1 );
    function display_paid_order_column( $column ) {
        global $post;
        if ( 'bank_transfer' === $column ) {
            $order = wc_get_order( $post->ID );
            $hide_payment = ['', 'mercanet_onetime', 'scalapay_gateway', 'yith-request-a-quote', 'other', 'heroPay3X'];
            if ( !in_array($order->get_payment_method(), $hide_payment) && $order->has_status( array( 'completed', 'processing', 'pending', 'on-hold' ) ) ) {
                $payment_amount = get_post_meta( $order->get_id(), '_custom_payment_amount', true );
                $payment_condition = get_post_meta( $order->get_id(), '_custom_payment_condition', true );
                echo empty($payment_amount) ? '<span style="color:#2271b1;">' . 0 . ' &euro;</span>' : '<span style="color:#2271b1;">' . number_format_i18n($payment_amount, 2) . ' &euro;</span>';
                echo empty($payment_condition) ? '' : '<br><small style="color:#2271b1;">(' . $payment_condition . ')</small>';
            }
        }
    }

    // Remplir la colonne personnalisée avec un message pour les commandes qui contiennent des produits provenant d'au moins deux fournisseurs différents
    add_action( 'manage_shop_order_posts_custom_column', 'display_multiple_supplier', 10, 2 );
    function display_multiple_supplier( $column, $post_id ) {
        if ( 'order_number' === $column ) {
            $order = wc_get_order( $post_id );
            $supplier_list = array();
            foreach( $order->get_items() as $item_id => $item ){
                $product_id = $item->get_product_id();
                $product = wc_get_product( $product_id );
                if (get_post_type($product_id) == 'product_variation') {
                    $parent_id = wp_get_post_parent_id($product_id);
                    $parent_product = wc_get_product($parent_id);
                    $supplier = $parent_product->get_meta('supplier');
                } else {
                    $product = wc_get_product($product_id);
                    if ($product){
                    $supplier = $product->get_meta('supplier');
                    }
                }
                if (!in_array($supplier, $supplier_list)) {
                    $supplier_list[] = $supplier;
                }
            }
            if ( count( $supplier_list ) >= 2 ) {
                echo '<span style="color: red;"><strong>' . esc_html__( 'Fournisseurs multiples', 'woocommerce' ) . '</strong></span>';
            }
            elseif ( count( $supplier_list ) == 1 && $supplier_list[0]  != 'Vinco' ) {
                echo '<span style="color: red;"><strong>' . $supplier_list[0] . '</strong></span>';
            }
        }
    }

    /***********************************
      * Manage notes on admin order list
      **********************************
    */


      add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 90 );
      function custom_shop_order_column( $columns )
      {
          $ordered_columns = array();
          foreach( $columns as $key => $column ){
              $ordered_columns[$key] = $column;
              if( 'order_date' == $key ){
                  $ordered_columns['order_notes'] = __( 'Notes', 'woocommerce');
              }
          }
          return $ordered_columns;
      }
      add_action( 'manage_shop_order_posts_custom_column' , 'custom_shop_order_list_column_content', 10, 1 );
      function custom_shop_order_list_column_content( $column )
      {
          global $post, $the_order;
          $customer_note = $post->post_excerpt;
          if ( $column == 'order_notes' ) {
              if ( $the_order->get_customer_note() ) {
                  echo '<span class="note-on customer tips" data-tip="' . wc_sanitize_tooltip( $the_order->get_customer_note() ) . '">' . '</span>';
              }
              if ( $post->comment_count ) {
                  $latest_notes = wc_get_order_notes( array(
                      'order_id' => $post->ID,
                      'limit'    => 'all',
                      'orderby'  => 'date_created_gmt',
                      'type' => 'internal', 
                  ) );
                  if ( $latest_notes ) {
                      foreach( $latest_notes as $key => $note ) {
                         if( ($note->added_by != 'system') ){
                          $notes = '';
                          $notes .= $note->content . '<br>';
                         }
                      }
                      if ( !empty( $notes ) ) {
                              echo '<span class="note-on tips" data-tip="' . wc_sanitize_tooltip( $notes) . '">' . '</span>';
                              $latest_note = current( $latest_notes );
                              echo '<div>'.$latest_note->content.'</div>' ;
                      }
                  }		
              }
          }
      }
      // Set Here the WooCommerce icon for your action button
      add_action( 'admin_head', 'add_custom_order_status_actions_button_css' );
      function add_custom_order_status_actions_button_css() {
          echo '<style>
          td.order_notes > .note-on { display: inline-block !important;}
          span.note-on.customer { margin-right: 4px !important;}
          span.note-on.customer::after { font-family: woocommerce !important; content: "\e026" !important;}
          table.wp-list-table .column-customer_message, table.wp-list-table .column-order_notes {width: 200px;}
          </style>';
      }
}