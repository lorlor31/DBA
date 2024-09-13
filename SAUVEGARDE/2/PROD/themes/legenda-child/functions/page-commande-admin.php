<?php 
/*
 * FORCE EXPIRATION DEVIS YITH REQUEST A QUOTE
 */
function force_enable_expiry_date_meta( $order_id, $raq ) {
    $order = wc_get_order( $order_id );

    // Forcer l'activation de l'expiration
    $order->update_meta_data( '_ywraq_enable_expiry_date', 'yes' );

    // Mettre à jour la date d'expiration si nécessaire
    if ( 'yes' === get_option( 'ywraq_enable_expired_time', 'no' ) ) {
        $expire_option = get_option( 'ywraq_expired_time', array( 'days' => 20 ) );
        $expire_time = time() + intval( $expire_option['days'] ) * DAY_IN_SECONDS;
        $expire_date = apply_filters( 'ywraq_expire_date_format', date( 'Y-m-d', $expire_time ), $expire_time );
        
        // Si la date d'expiration n'est pas définie pour la commande, utiliser la date calculée
        if ( empty( $order->get_meta( '_ywcm_request_expire' ) ) ) {
            $order->update_meta_data( '_ywcm_request_expire', $expire_date );
        }
    }

    $order->save();
}
add_action( 'ywraq_add_order_meta', 'force_enable_expiry_date_meta', 10, 2 );


/*
 * PERMET D'AJOUTER DES PRODUITS AU PANIER PAR URL
 */
// Ajoute cette action pour s'assurer que la fonction est exécutée au bon moment.
add_action('wp', 'custom_add_to_cart_via_url', 1); // Exécuter tôt pour surpasser la fonction de WooCommerce
function custom_add_to_cart_via_url() {
    $products_to_add = [];

    // Gérer les produits avec addons
    if (isset($_GET['add-to-cart-with-addons']) && !empty($_GET['add-to-cart-with-addons'])) {
        if (!did_action('wp_loaded')) {
            wc_get_template('cart/cart-empty.php');
            exit;
        }
        $decoded_addons = urldecode($_GET['add-to-cart-with-addons']);
        $decoded_addons = stripslashes($decoded_addons);
        $addons_by_order_item_id = json_decode($decoded_addons, true);
        if (is_array($addons_by_order_item_id)) {
            foreach ($addons_by_order_item_id as $order_item_id => $data) {
                $product_id = $data['product_id'];
                $quantity = $data['quantity'];
                $addon_data_for_cart = !empty($data['addons']) ? prepare_addon_data_for_cart($data['addons']) : [];
                $products_to_add[] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'addons' => $addon_data_for_cart
                ];
            }
        }
    }

    // Gérer les produits sans addons
    if (!empty($_GET['add-to-cart']) && !empty($_GET['quantities'])) {
        $product_ids = explode(',', $_GET['add-to-cart']);
        $quantities = explode(',', $_GET['quantities']);

        foreach ($product_ids as $index => $product_id) {
            $quantity = $quantities[$index] ?? 1;
            $products_to_add[] = [
                'product_id' => $product_id,
                'quantity' => $quantity,
                'addons' => []
            ];
        }
    }

    // Ajouter tous les produits au panier
    if (!empty($products_to_add)) {
        WC()->cart->empty_cart();
        foreach ($products_to_add as $product) {
            WC()->cart->add_to_cart($product['product_id'], $product['quantity'], 0, [], $product['addons']);
        }
    }
}

function prepare_addon_data_for_cart($addons) {
    $addon_data_for_cart = ['yith_wapo_options' => []];
    foreach ($addons as $addon) {
		
        foreach ($addon as $addon_id => $addon_value) {
            // Vérifier et manipuler correctement les données d'addons
            if (is_array($addon_value) && isset($addon_value['value']) && isset($addon_value['quantity'])) {
                for ($i = 0; $i < $addon_value['quantity']; $i++) {
                    $addon_data_for_cart['yith_wapo_options'][] = [
                        $addon_id => $addon_value['value']
                    ];
                }
            } else {
                error_log("Erreur de format d'addon: " . print_r($addon_value, true));
            }
        }
    }
    return $addon_data_for_cart;
}





if (!function_exists('get_addon_sets')) {
    function get_addon_sets($addon_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yith_wapo_addons';
        $query = $wpdb->prepare("SELECT settings, options FROM {$table_name} WHERE ID = %d", $addon_id);
        $result = $wpdb->get_row($query, ARRAY_A);
        return [
            'settings' => maybe_unserialize($result['settings']),
            'options' => maybe_unserialize($result['options']),
        ];
    }
}
if (!function_exists('extract_quantity_from_string')) {
    function extract_quantity_from_string($value) {
        if (preg_match('/^(\d+)\s*x\s*/', $value, $matches)) {
            return (int)$matches[1];
        }
        return 1; // Quantité par défaut si non spécifiée
    }
}
if (!function_exists('get_addon_option')) {
    function get_addon_option($addon_id, $index) {
        $data = get_addon_sets($addon_id);
        $settings = $data['settings'];
        $options = $data['options'];

        if ($settings && $options) {
            if (isset($settings['type']) && $settings['type'] === 'product') {
                $title_addon = $settings['title'];
                $product_addon_id = isset($options['product'][$index]) ? $options['product'][$index] : '';
                $label_product_addon = isset($options['label'][$index]) ? $options['label'][$index] : '';
                preg_match('/\(([^)]+)\)/', $label_product_addon, $sku_matches);
                $sku_product_addon = isset($sku_matches[1]) ? $sku_matches[1] : '';
                $label_product_addon_formatted = preg_replace('/\s*\(.*?\)/', ' ', $label_product_addon);
                $product_addon_id = get_product_id_by_sku($sku_product_addon);

                return [
                    'type' => $settings['type'],
                    'title_addon' => $title_addon,
                    'product_addon_id' => $product_addon_id,
                    'sku_product_addon' => $sku_product_addon,
                    'label_product_addon_formatted' => $label_product_addon_formatted,
					'addon_choix_id' => "{$addon_id}-{$index}"
                ];
            } elseif (isset($settings['type']) && $settings['type'] === 'label') {
                $label_product_addon = isset($options['label'][$index]) ? $options['label'][$index] : '';

                return [
                    'type' => $settings['type'],
                    'label_product_addon' => $label_product_addon,
					'addon_choix_id' => "{$addon_id}-{$index}"
                ];
            }
        }
        return null;
    }
}


if (!function_exists('get_product_id_by_sku')) {
    function get_product_id_by_sku($sku) {
        global $wpdb;
          $product_id = $wpdb->get_var($wpdb->prepare("
            SELECT p.ID
            FROM {$wpdb->prefix}posts AS p
            INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
            WHERE pm.meta_key = '_sku' AND pm.meta_value = %s
            AND p.post_type IN ('product', 'product_variation')
        ", $sku));
		return $product_id ? (int) $product_id : null;
    }
}


function generate_add_to_cart_link($order) {
    $item_list_id = '';
    $item_list_qte = '';
    $addons_by_product_encoded = [];

    foreach ($order->get_items() as $item_id => $item) {	
        $product_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
        $item_quantity = $item->get_quantity();
        $item_list_id .= $product_id . ',';
        $item_list_qte .= $item_quantity . ',';
        $order_item_id = $item->get_id();

        $meta_ywapo = $item->get_meta('_ywraq_wc_ywapo', true);
        $meta_data = $item->get_meta('_ywapo_meta_data', true);

        $meta_ywapo = is_array($meta_ywapo) ? $meta_ywapo : [];
        $meta_data = is_array($meta_data) ? $meta_data : [];

        $addons = array_merge($meta_ywapo, $meta_data);
		
        $addon_data_for_cart = [];
		$processed_meta_ids = [];
		
        foreach ($addons as $addon) {
            foreach ($addon as $addon_key => $addon_value) {
                list($addon_id, $index) = explode('-', $addon_key);
                $addon_option = get_addon_option($addon_id, $index);

                if ($addon_option) {
                    $addon_quantity = 1; // Default quantity
                    foreach ($item->get_meta_data() as $meta) {
                        if (in_array($meta->id, $processed_meta_ids)) {
                            continue;
                        }
                        if ($meta->key === $addon_option['title_addon']) {
                            $addon_quantity = extract_quantity_from_string($meta->value);
							 $processed_meta_ids[] = $meta->id;
							 break;
                        }
                    }

                    $addon_data_for_cart[] = [
                        $addon_key => [
                            'value' => $addon_value,
                            'quantity' => $addon_quantity,
                            'product_addon_id' => $addon_option['product_addon_id'],
                            'label_product_addon' => $addon_option['label_product_addon'],
                            'sku_product_addon' => $addon_option['sku_product_addon'],
                            'label_product_addon_formatted' => $addon_option['label_product_addon_formatted'],
							'meta_id' => $addon_option['meta_id']
                        ]
                    ];
                }
            }
        }

        if (!empty($addon_data_for_cart)) {
            $addons_by_product_encoded[$order_item_id] = [
                'product_id' => $product_id,
                'quantity' => $item_quantity,
                'addons' => $addon_data_for_cart,
            ];
        } else {
            $addons_by_product_encoded[$order_item_id] = [
                'product_id' => $product_id,
                'quantity' => $item_quantity,
            ];
        }
    }

    $item_list_id = rtrim($item_list_id, ',');
    $item_list_qte = rtrim($item_list_qte, ',');

    if (!empty($addons_by_product_encoded)) {
        $encoded_addons_by_product = urlencode(json_encode($addons_by_product_encoded));
        $add_to_cart_link = "/panier/?add-to-cart-with-addons={$encoded_addons_by_product}";
    } else {
        $add_to_cart_link = "/panier/?add-to-cart=" . $item_list_id . "&quantities=" . $item_list_qte;
    }

    return $add_to_cart_link;
}
// add_filter('woocommerce_get_item_data', 'custom_display_cart_item_addons', 10, 2);
// function custom_display_cart_item_addons($item_data, $cart_item) {
    // if (isset($cart_item['yith_wapo_options']) && !empty($cart_item['yith_wapo_options'])) {
        // $addons = [];
        // foreach ($cart_item['yith_wapo_options'] as $addon) {
			// error_log('addon111' . print_r($addon,true));
            // foreach ($addon as $addon_id => $addon_value) {

                // if (preg_match('/product-(\d+)-/', $addon_value, $matches)) {
                    // $product_id = $matches[1];

                    // $product = wc_get_product($product_id);
                    // if ($product) {
                        // if (isset($addons[$product_id])) {
                            // $addons[$product_id]['quantity']++;
                        // } else {
                            // $addons[$product_id] = [
                                // 'name' => $product->get_name(),
                                // 'quantity' => 1,
                                // 'price' => $product->get_price(),
                                // 'sku' => $product->get_sku(),
                            // ];
                        // }
                    // }
                // } else {

                    // if (!isset($addons[$addon_id])) {
                        // $addon_settings = get_addon_settings($addon_id);
						// $addon_title = $addon_settings['title'] ?? '';
                        // $addons[$addon_id] = [
                            // 'name' => $addon_title,
                            // 'value' => $addon_value,
                        // ];
                    // } else {
                        // $addons[$addon_id]['value'] .= ', ' . $addon_value;
                    // }
                // }
            // }
        // }

        // foreach ($addons as $addon) {
			// error_log('addon' . print_r($addon,true));
            // if (isset($addon['price'])) {
                // $item_data[] = [
                    // 'name' => $addon['quantity'] . ' x ' . $addon['name'] . ($addon['sku'] ? ' ( ' . $addon['sku'] . ')' : ''),
                    // 'value' => wc_price($addon['price'] * $addon['quantity']),
                // ];
            // } else {
                // $item_data[] = [
                    // 'name' => $addon['name'],
                    // 'value' => $addon['value'],
                // ];
            // }
        // }
    // }
    // return $item_data;
// }



if (is_admin()){
    /**
     * Ajouter Date de Paiement "PAYÉ" pr commande mandat administratif
     */
    add_action( 'woocommerce_order_status_processing',  'wc_auto_complete_paid_order' );
    function wc_auto_complete_paid_order( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( is_null($order->get_date_paid()) && $order->get_payment_method('cod') ){
            $order->set_date_paid($order->get_date_modified());
            $order->save();
        }
    }

    /*
    * Clone Order
    */
    // Ne pas cloner la date de création du document
    add_filter( 'vibe_clone_orders_clone_date_created', '__return_false' );
    // Créer un nouveau doc avec le statut Nouvelle demande de devis
    function clone_order_status( $status ) {
        return 'ywraq-new';
    }
    add_filter( 'vibe_clone_orders_clone_order_status', 'clone_order_status' );

    /**
    * Fix le problème de génération d'un numéro de commande en mettant à jour un devis en attente (facturation, livraison)*
    */
    if ( function_exists('YWSON_Manager') ) {
        remove_action( 'woocommerce_process_shop_order_meta', array( YWSON_Manager(), 'save_sequential_order_number' ), 50, 2 );
        add_action( 'woocommerce_process_shop_order_meta', 'custom_save_sequential_order_number', 50, 2 );
        function custom_save_sequential_order_number( $post_id, $post = array() ) {
            $order = wc_get_order( $post_id );
            $excluded_status = array('ywraq-new', 'ywraq-pending', 'ywraq-rejected');
            if ( ( 'admin' === get_post_meta( $order->get_id(), '_created_via', true ) && 'ywraq-new' === $order->get_status() ) || !in_array( $order->get_status(), $excluded_status ) ) {
                YWSON_Manager()->generate_sequential_order_number( $order );
            }
        }
    }

    /**
     * Supprimer la meta ywraq_raq_status et ywraq_pending_status_date pour repasser une commande en devis
     */
    add_action( 'woocommerce_order_status_changed', 'woocommerce_remove_quote_meta', 10, 3 );
    function woocommerce_remove_quote_meta( $order_id, $old_status, $new_status ) {
        $quote_status = ['ywraq-new'];
        if ( !in_array($old_status, $quote_status) && in_array($new_status, $quote_status) ) {
            delete_post_meta($order_id, 'ywraq_raq_status');
            delete_post_meta($order_id, 'ywraq_pending_status_date');
        }
    }

     /**
     * Display Code postal livr sur devis new et en attente
     */
	add_action('woocommerce_admin_order_data_after_shipping_address', 'display_postcode', 10, 1);
	function display_postcode($order){
		$dt_order = $order->data["shipping"];
		$postcode = $dt_order["postcode"];
		$exclusion_status = check_postcode_for_exclusion($postcode, $order);
		$postcode_color = $exclusion_status ? "red" : "blue";
		if ($order->data["status"] == 'ywraq-new' || $order->data["status"] == 'ywraq-pending'){
			echo '<p><strong>Code postal de livraison:</strong><br><span style="color:' . $postcode_color . ';"><b>' . $postcode . '</b></span>';
			if ($exclusion_status) {
				echo '<br><b style="color:red;">ATTENTION Code postal exclu dans le groupe [' . $exclusion_status . ']</b><br>Veuillez demander un devis aux fournisseurs</p>';
				echo "<script type='text/javascript'>
							alert('ATTENTION Code postal exclu dans le groupe [" . $exclusion_status . "]\\nVeuillez demander un devis aux fournisseurs');
					</script>";
			}
			echo '</p>';
		}
	}


/*
 *  Ajouter un BOX dans la page commande Note au Frounisseur
 */
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'ma_custom_note_fournisseur' );

function ma_custom_note_fournisseur( $order ) {
    $note_fournisseur = get_post_meta( $order->get_id(), 'note_fournisseur', true );
    ?>
    <div class="form-field form-field-wide">
        <label for="note_fournisseur">Note au fournisseur:</label>
        <textarea name="note_fournisseur" rows="2" cols="50"><?php echo esc_textarea( $note_fournisseur ); ?></textarea>
    </div>
    <?php
}
add_action( 'save_post', 'sauvegarder_note_fournisseur' );
function sauvegarder_note_fournisseur( $post_id ) {
    if ( isset( $_POST['note_fournisseur'] ) ) {
        update_post_meta( $post_id, 'note_fournisseur', sanitize_text_field( $_POST['note_fournisseur'] ) );
    }
}


	
	/**
     * Ajouter un BOX dans la page commande Pour gérer les relance de devis
     */	

		add_action( 'add_meta_boxes', 'add_custom_order_email_meta_box' );
		function add_custom_order_email_meta_box() {
			add_meta_box( 'custom_order_email', 'Relance Commerciale', 'custom_order_email_meta_box_callback', 'shop_order', 'side', 'high' );
		}
		function custom_order_email_meta_box_callback( $post ) {
			// Vérifiez que l'objet $post est une instance de WP_Post pour éviter les erreurs
			if ( ! $post instanceof WP_Post ) {
				return;
			}
			$relance1_sent = get_post_meta($post->ID, '_mail_relance1_sent', true);
			$relance2_sent = get_post_meta($post->ID, '_mail_relance2_sent', true);
			$relance3_sent = get_post_meta($post->ID, '_mail_relance3_sent', true);
			echo '<select id="email_template" name="email_template">';
			echo '<option value="defaut" selected>Envoyer une Relance par mail</option>';
			// Désactiver les options si les mails de relance ont été envoyés
			echo '<option value="relance1"' . (($relance1_sent === 'yes' || $relance2_sent === 'yes' || $relance3_sent === 'yes') ? ' disabled' : '') . '>Relance Mail 1</option>';
			echo '<option value="relance2"' . (($relance2_sent === 'yes' || $relance3_sent === 'yes') ? ' disabled' : '') . '>Relance Mail 2</option>';
			echo '<option value="relance3"' . ($relance3_sent === 'yes' ? ' disabled' : '') . '>Relance Mail 3</option>';
			echo '<option value="relance4">Mail Libre</option>';
			echo '</select>';
			// Ajouter un champ pour l'objet du mail
			$subject = get_post_meta($post->ID, '_custom_email_subject', true);
			echo '<p id="custom_email_subject_container" style="display:none;"><label for="custom_email_subject">Objet du Mail :</label><br />';
			echo '<input type="text" id="custom_email_subject" name="custom_email_subject" value="' . esc_attr($subject) . '" style="width:100%;max-width:500px" /></p>';
			// Éditeur WYSIWYG caché par défaut
			echo '<div id="email_editor_container" style="display:none;">';
			wp_editor( '', 'custom_email_content', array( 'textarea_rows' => 10 ) );
			echo '</div>';
			// Bouton pour envoyer l'email, caché par défaut
			echo '<button id="send_custom_email" data-order-id="' . esc_attr( $post->ID ) . '" style="display:none;">Envoyer Email</button>';
			?>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
				function fetchEmailData(orderId, template) {
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							'action': 'get_email_data',
							'order_id': orderId,
							'template': template
						},
						success: function(response) {
							var data = JSON.parse(response);
							
							var subject = '';
							switch (template) {
								case 'relance1':
								case 'relance2':
								case 'relance3':
									subject = 'Devis n°' + data.orderNumber + ' | Armoire PLUS';
									break;
								case 'relance4':
									subject = 'Dossier n°' + data.orderNumber + ' | Armoire PLUS';
									break;
								default:
									subject = 'Sujet par défaut';
							}

							$('#custom_email_subject').val(subject);
							updateEditorContent(data.emailContent);
						}
					});
				}

					$('#email_template').change(function() {
						var selectedTemplate = $(this).val();
						var orderId = $('#send_custom_email').data('order-id');

						if (selectedTemplate === 'relance1' || selectedTemplate === 'relance2' || selectedTemplate === 'relance3' || selectedTemplate === 'relance4') {
							$('#email_editor_container').show();
							$('#send_custom_email').show();
							fetchEmailData(orderId, selectedTemplate);
							$('#custom_email_subject_container').show(); // Afficher le champ d'objet
						} else {
							$('#email_editor_container').hide();
							$('#send_custom_email').hide();
							$('#custom_email_subject_container').hide(); // Cacher le champ d'objet
							$('#custom_email_subject').val(''); // Réinitialiser la valeur
						}
					});

					// Fonction pour mettre à jour le contenu de l'éditeur
					function updateEditorContent(content) {
						if (typeof tinyMCE !== 'undefined' && tinyMCE.get('custom_email_content') !== null) {
							tinyMCE.get('custom_email_content').setContent(content);
						}
					}

					$('#send_custom_email').click(function() {
						var orderId = $(this).data('order-id');
						var emailContent = tinyMCE.get('custom_email_content').getContent();
						var emailSubject = $('#custom_email_subject').val();
						var selectedTemplate = $('#email_template').val();
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							data: {
								'action': 'send_custom_email',
								'order_id': orderId,
								'email_content': emailContent,
								'email_subject': emailSubject,
								'template': selectedTemplate
							},
							success: function(response) {
								alert('E-mail envoyé : ' + response);
								// Désactiver l'option "Mail de relance 1" si nécessaire
								// if(selectedTemplate === 'relance1') {
									// $('#email_template option[value="relance1"]').prop('disabled', true);
								// }
							}
						});
					});

					$('#email_template').trigger('change');
				});
				var emailCache = {};
				function fetchEmailData(orderId, template) {
					if(emailCache[template]) {
						// Utiliser les données en cache
						$('#custom_email_subject').val(emailCache[template].subject);
						updateEditorContent(emailCache[template].content);
					} else {
						// Faire l'appel AJAX et mettre en cache les résultats
						// ...
						emailCache[template] = {
							subject: subject,
							content: data.emailContent
						};
					}
				}
			</script>
					<?php
					}

			add_action('save_post', 'save_custom_email_meta_box_data');
			function save_custom_email_meta_box_data($post_id) {
				// Vérifier si notre champ nonce est défini.
				if (!isset($_POST['custom_email_meta_box_nonce'])) {
					return;
				}
				// Vérifier que le nonce est valide.
				if (!wp_verify_nonce($_POST['custom_email_meta_box_nonce'], 'save_custom_email_meta_box_data')) {
					return;
				}
				// Si c'est une sauvegarde automatique, notre formulaire n'a pas été soumis, donc nous ne voulons rien faire.
				if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
					return;
				}
				// Vérifier les permissions de l'utilisateur.
				if (!current_user_can('edit_post', $post_id)) {
					return;
				}

				// OK, il est sûr pour nous d'enregistrer les données maintenant.

				// Vérifier si un objet du mail a été envoyé.
				if (isset($_POST['custom_email_subject'])) {
					$subject = sanitize_text_field($_POST['custom_email_subject']);
					update_post_meta($post_id, '_custom_email_subject', $subject);
				}
			}
			add_action('wp_ajax_get_email_data', 'get_email_data_ajax');
			function get_email_data_ajax() {
				$order_id = intval($_POST['order_id']);
				$template = sanitize_text_field($_POST['template']);
				$order = wc_get_order($order_id);

				$data = array();

				if ($order) {
					$data['orderNumber'] = $order->get_order_number();

					switch ($template) {
						case 'relance1':
							$data['emailContent'] = get_relance_mail_1_template($order_id);
							break;
						case 'relance2':
							$data['emailContent'] = get_relance_mail_2_template($order_id);
							break;
						case 'relance3':
							$data['emailContent'] = get_relance_mail_3_template($order_id);
							break;
						case 'relance4':
							$data['emailContent'] = get_relance_mail_4_template();
							break;
					}
				} else {
					$data['orderNumber'] = 'Non trouvé';
					$data['emailContent'] = '';
				}

				echo json_encode($data);
				wp_die();
			}
			function send_custom_email_to_order_billing_address( $order_id, $template, $custom_content ) {
				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					return 'Commande non trouvée';
				}

				$billing_email = $order->get_billing_email();
				if ( ! $billing_email ) {
					return 'Email de facturation non trouvé';
				}

				// Choix du modèle
				$email_content = '';
				if ( 'relance1' === $template ) {
					$email_content = get_relance_mail_1_template($order_id);
				} elseif ( 'relance2' === $template ) {
					$email_content = get_relance_mail_2_template($order_id);
				}elseif ( 'relance3' === $template ) {
					$email_content = get_relance_mail_3_template();
				}elseif ( 'relance4' === $template ) {
					$email_content = get_relance_mail_4_template();
				}

				// Ajouter le contenu personnalisé
				if ( ! empty( $custom_content ) ) {
					$email_content .= '<br />' . $custom_content;
				}
			$full_email_content = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Email</title></head><body>' . $email_content . '</body></html>';
			echo '<textarea style="width: 100%; height: 200px;">' . esc_textarea( $full_email_content ) . '</textarea>';


				$subject = 'Email de Relance';
				$headers = array('Content-Type: text/html; charset=UTF-8');
			 // return wp_mail( $billing_email, $subject, $full_email_content, $headers );
			}

			add_action('wp_ajax_get_email_content', 'get_email_content_ajax');
			function get_email_content_ajax() {
				$order_id = intval( $_POST['order_id'] );
				$template = sanitize_text_field( $_POST['template'] );

				if ($template === 'relance1') {
					echo get_relance_mail_1_template($order_id);
				} elseif ($template === 'relance2') {
					echo get_relance_mail_2_template($order_id);
				}elseif ($template === 'relance3') {
					echo get_relance_mail_3_template();
				}elseif ($template === 'relance4') {
					echo get_relance_mail_4_template();
				}
				wp_die(); // termine la requête AJAX
			}
					// Gérer l'action AJAX
			add_action( 'wp_ajax_send_custom_email', 'handle_send_custom_email_ajax' );
			function handle_send_custom_email_ajax() {
				$order_id = intval( $_POST['order_id'] );
				$template = sanitize_text_field($_POST['template']);
				$email_content =  wp_kses_post($_POST['email_content']) ;
				// Récupérer les détails de la commande ici, si nécessaire
				$order = wc_get_order( $order_id );
				$order_number = $order->get_order_number();
				$billing_email = $order->get_billing_email();
				$subject = !empty($_POST['email_subject']) ? sanitize_text_field($_POST['email_subject']) :
				(($template === 'relance1' || $template === 'relance2' || $template === 'relance3') ? 'Devis n°' . $order_number . ' | Armoire PLUS' :
				($template === 'relance4' ? 'Dossier n°' . $order_number . ' | Armoire PLUS' : 'Dossier n°' . $order_number . ' | Armoire PLUS'));
				// Ajout du PDF en pièce jointe
				$attachments = array();
				$pdf_path = get_pdf_attachment_path($order_id);
				if(file_exists($pdf_path)) {
					$attachments[] = $pdf_path;
				}
				$headers = array(
					'Content-Type: text/html; charset=UTF-8',
					'Bcc: service-client@armoireplus.fr' // Ajouter une adresse e-mail en BCC
				);
				// Envoi de l'email
				$email_sent = wp_mail( $billing_email, $subject, wp_unslash($email_content), $headers, $attachments );
				if ($email_sent) {
					echo 'Email envoyé avec succès à ' . esc_html($billing_email);
					 $order = wc_get_order($order_id);
					if ($order && ($template === 'relance1' || $template === 'relance2' || $template === 'relance3')) {
						$relanceNum = ($template === 'relance1' ? '1' : ($template === 'relance2' ? '2' : '3'));
						$note = 'Mail de relance ' . $relanceNum . ' envoyé';
						$order->add_order_note($note,0,1); // true pour une note privée
						update_post_meta($order_id, '_mail_relance' . $relanceNum . '_sent', 'yes');
					}
				} else {
					echo 'Échec de l\'envoi de l\'email';
				}
				wp_die(); // Termine correctement la requête AJAX
			}
// Fonction pour récupérer le chemin d'accès au PDF
function get_pdf_attachment_path($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        return '';
    }

    // Construire le nom de fichier basé sur le numéro de commande et la date de commande
    $order_date = $order->get_date_created();
    $year = $order_date->format('Y');
    $month = $order_date->format('m');
    $order_number = $order->get_order_number();
    $pdf_file_name = "dossier_" . $order_number . ".pdf";
    $upload_dir = wp_upload_dir();
    $pdf_file_path = $upload_dir['basedir'] . '/yith_ywraq/' . $year . '/' . $month . '/' . $pdf_file_name;
    if (file_exists($pdf_file_path)) {
        return $pdf_file_path;
    }
    return '';
}

		function get_relance_mail_1_template($order_id) {
			    $current_user = wp_get_current_user();
				$username = $current_user->display_name ;
				$billing_phone = get_user_meta($current_user->ID, 'billing_phone', true);
				$clean_phone = preg_replace('/[^0-9]/', '', $billing_phone);
				$formatted_phone = 'tel:+33' . substr($clean_phone, 1);
				$billing_email = $current_user->user_email;
				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					return 'Commande non trouvée';
				}
				// Récupérer la date de création de la commande et la formater
				$order_date = $order->get_date_created()->format('d/m/Y');
				// Récupérer le numéro de la commande
				$order_number = $order->get_order_number();
			$email_content =  'Bonjour,<br /><br />
					Suite à votre demande, je vous ai envoyé votre devis le&nbsp;' . $order_date . ' numéro&nbsp;n° ' . $order_number . '.<br /><br />
					Je voulais m\'assurer que vous l\'avez bien reçu, car il peut parfois arriver qu\'il se retrouve malencontreusement dans vos spams ou que l\'adresse e-mail comporte une erreur.<br /><br />
					Si c\'est le cas, avez-vous eu l\'occasion de le consulter ? Si vous avez des questions, notamment concernant nos produits ou nos modes de livraison, n\'hésitez pas à me les poser. Je suis disponible pour vous accompagner.<br /><br />
					Pour faciliter nos échanges, je vous communique ci-dessous mes coordonnées directes.<br /><br />
					Je vous souhaite une excellente journée.<br /><br />
					Cordialement, ' . $username . ' Armoire PLUS<br />
					<img src="https://www.armoireplus.fr/wp-content/uploads/2020/02/Logo-ArmoirePlus_small.png" style="height:40px; width:144px" /><br />
					Ma ligne directe : <a href="' . $formatted_phone . '">' . $billing_phone . '</a><br />
					<a href="mailto:'.$billing_email.'">'.$billing_email.'</a> - <a href="https://www.armoireplus.fr/">https://www.armoireplus.fr</a> &nbsp;
					';
			 return $email_content;
		}
		function get_relance_mail_2_template($order_id) {
			    $current_user = wp_get_current_user();
				$username = $current_user->display_name ;
				$billing_phone = get_user_meta($current_user->ID, 'billing_phone', true);
				$clean_phone = preg_replace('/[^0-9]/', '', $billing_phone);
				$formatted_phone = 'tel:+33' . substr($clean_phone, 1);
				$billing_email = $current_user->user_email;
				$order = wc_get_order( $order_id );
				if ( ! $order ) {
					return 'Commande non trouvée';
				}
				// Récupérer la date de création de la commande et la formater
				$order_date = $order->get_date_created()->format('d/m/Y');
				// Récupérer le numéro de la commande
				$order_number = $order->get_order_number();
			$email_content =  'Bonjour,<br />
					<br />
					Suite &agrave; votre demande, j\'ai eu le plaisir de vous adresser le&nbsp;' . $order_date . '&nbsp;&nbsp;notre devis n&deg; ' . $order_number . '&nbsp; que vous retrouverez en pi&egrave;ce jointe et pour lequel je n&rsquo;ai pas encore &eacute;t&eacute; inform&eacute; de la suite &agrave; donner.<br />
					<br />
					Pourriez-vous me tenir informée de l\'avancement de votre projet ? Je souhaite simplement m\'assurer que celui-ci est toujours en cours et déterminer s\'il doit être maintenu en attente ou s\'il peut être archivé.<br />
					<br />
					Par ailleurs, par mesure de qualité et dans un souci d\'amélioration continue de nos services, si ma proposition n\'est pas retenue, pourriez-vous me faire part des raisons afin que je puisse améliorer nos offres commerciales à l\'avenir ?<br />
					<br />
					Je vous souhaite une excellente journée.<br /><br />
					Cordialement, ' . $username . ' Armoire PLUS<br />
					<img src="https://www.armoireplus.fr/wp-content/uploads/2020/02/Logo-ArmoirePlus_small.png" style="height:40px; width:144px" /><br />
					Ma ligne directe : <a href="' . $formatted_phone . '">' . $billing_phone . '</a><br />
					<a href="mailto:'.$billing_email.'">'.$billing_email.'</a> - <a href="https://www.armoireplus.fr/">https://www.armoireplus.fr</a> &nbsp;<br />
					<br />
					&nbsp;
					';
			 return $email_content;
		}		
		function get_relance_mail_3_template() {
			    $current_user = wp_get_current_user();
				$username = $current_user->display_name ;
				$billing_phone = get_user_meta($current_user->ID, 'billing_phone', true);
				$clean_phone = preg_replace('/[^0-9]/', '', $billing_phone);
				$formatted_phone = 'tel:+33' . substr($clean_phone, 1);
				$billing_email = $current_user->user_email;
			$email_content =  'Bonjour,<br />
					<br />
					Je me permets de revenir vers vous au sujet de notre précédent échange concernant votre devis.<br />
					<br />
					À ce jour, je n\'ai pas reçu de retour de votre part quant à la suite à donner à ce dossier. Si vous préférez archiver ce devis ou le maintenir en attente pour le moment, merci de nous en informer.<br /><br />
					De plus, dans un souci constant d\'amélioration de nos services, nous serions reconnaissants de recevoir vos éventuels commentaires si notre offre commerciale n\'a pas été retenue.<br /><br />
					Vos retours seront précieux pour nous aider à mieux répondre à vos besoins à l\'avenir.<br /><br />
					N\'hésitez pas à me contacter si vous avez la moindre question ou demande. Je reste disponible pour toute clarification nécessaire.<br /><br />
					Je vous remercie pour votre collaboration et je vous souhaite une excellente journée.<br /><br />
					Cordialement, ' . $username . ' Armoire PLUS<br />
					<img src="https://www.armoireplus.fr/wp-content/uploads/2020/02/Logo-ArmoirePlus_small.png" style="height:40px; width:144px" /><br />
					Ma ligne directe : <a href="' . $formatted_phone . '">' . $billing_phone . '</a><br />
					<a href="mailto:'.$billing_email.'">'.$billing_email.'</a> - <a href="https://www.armoireplus.fr/">https://www.armoireplus.fr</a> &nbsp;<br />';
			 return $email_content;
		}
		function get_relance_mail_4_template() {
			    $current_user = wp_get_current_user();
				$username = $current_user->display_name ;
				$billing_phone = get_user_meta($current_user->ID, 'billing_phone', true);
				$clean_phone = preg_replace('/[^0-9]/', '', $billing_phone);
				$formatted_phone = 'tel:+33' . substr($clean_phone, 1);
				$billing_email = $current_user->user_email;
			$email_content =  'Bonjour,<br />
					<br />
					Rédiger votre message<br />
					<br />
					Cordialement, ' . $username . ' Armoire PLUS<br />
					<img src="https://www.armoireplus.fr/wp-content/uploads/2020/02/Logo-ArmoirePlus_small.png" style="height:40px; width:144px" /><br />
					Ma ligne directe : <a href="' . $formatted_phone . '">' . $billing_phone . '</a><br />
					<a href="mailto:'.$billing_email.'">'.$billing_email.'</a> - <a href="https://www.armoireplus.fr/">https://www.armoireplus.fr</a> &nbsp;<br />';
			 return $email_content;
		}		
	
    /**
     * Ajouter un BOX dans la page commande (admin) pour gerer les documents
     */
function ajouter_scripts_admin() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
			$('#post').attr('enctype', 'multipart/form-data');
            $('.supprimer-document').on('click', function(e) {
                e.preventDefault();
                var attachmentId = $(this).data('attachment-id');
                var data = {
                    action: 'supprimer_document',
                    post_id: <?php echo get_the_ID(); ?>,
                    attachment_id: attachmentId,
                    nonce: '<?php echo wp_create_nonce('supprimer-document'); ?>'
                };

                $.post(ajaxurl, data, function(response) {
                    location.reload(); // Recharger la page pour mettre à jour la liste des fichiers
                });
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'ajouter_scripts_admin');
add_action('wp_ajax_supprimer_document', 'supprimer_document_ajax');
function supprimer_document_ajax() {
    check_ajax_referer('supprimer-document', 'nonce');

    $post_id = intval($_POST['post_id']);
    $attachment_id = intval($_POST['attachment_id']);

    if (current_user_can('edit_post', $post_id) && $attachment_id) {
        wp_delete_attachment($attachment_id, true);

        // Mettre à jour les métadonnées de la commande
        $file_ids = get_post_meta($post_id, '_mes_documents_ids', true);
        if (($key = array_search($attachment_id, $file_ids)) !== false) {
            unset($file_ids[$key]);
            update_post_meta($post_id, '_mes_documents_ids', $file_ids);
        }
    }

    wp_die();
}

add_action('add_meta_boxes', 'gestion_documents');
function gestion_documents() {
	if (current_user_can('administrator')) {
    add_meta_box(
        'gestion_documents',              // ID de la meta box
        'Documents de la Commande',    // Titre de la meta box
        'afficher_gestion_documents',        // Fonction de callback pour afficher le contenu
        'shop_order',                  // Post type (ici, les commandes WooCommerce)
        'side',                        // Contexte (où la box doit apparaître)
        'default'                      // Priorité
    );
	}
}
function afficher_gestion_documents($post) {
    wp_nonce_field(plugin_basename(__FILE__), 'gestion_documents_nonce');
    echo '<input type="file" name="mes_documents[]" multiple>';
    $mes_documents_ids = get_post_meta($post->ID, '_mes_documents_ids', true);
    if (!empty($mes_documents_ids)) {
        echo '<h4>Fichiers Téléchargés</h4>';
        echo '<ul>';
        foreach ($mes_documents_ids as $attachment_id) {
            $file_url = wp_get_attachment_url($attachment_id);
            $file_name = basename(get_attached_file($attachment_id));
            echo '<li>';
            echo '<a href="' . esc_url($file_url) . '">' . esc_html($file_name) . '</a>';
            echo ' <a href="#" style="margin-left:30px;" class="supprimer-document" data-attachment-id="' . esc_attr($attachment_id) . '">Supprimer</a>';
            echo '</li>';
        }
        echo '</ul>';
    }
}
function enregistrer_mes_documents($post_id) {
    if ('POST' !== $_SERVER['REQUEST_METHOD']) {
        return $post_id;
    }
    // Vérifier si l'utilisateur actuel a les droits d'administrateur
    if (!current_user_can('administrator')) {
        return $post_id;
    }

    // Vérifier la nonce pour la sécurité
    if (!isset($_POST['gestion_documents_nonce']) || !wp_verify_nonce($_POST['gestion_documents_nonce'], plugin_basename(__FILE__))) {
        return $post_id;
    }

    // Vérifier si l'utilisateur peut éditer le post et si le type de post est correct
    if ('shop_order' != $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }
    if (isset($_POST['supprimer_document'])) {
        $attachment_id_to_delete = intval($_POST['supprimer_document']);
        if ($attachment_id_to_delete) {
            wp_delete_attachment($attachment_id_to_delete, true);

            // Mettre à jour les métadonnées de la commande en supprimant l'ID du fichier
            $file_ids = get_post_meta($post_id, '_mes_documents_ids', true);
            if (($key = array_search($attachment_id_to_delete, $file_ids)) !== false) {
                unset($file_ids[$key]);
                update_post_meta($post_id, '_mes_documents_ids', $file_ids);
            }
        }
    }
    // Traiter les fichiers téléchargés
    if (!empty($_FILES['mes_documents']['name'][0])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $file_ids = array();
       $num_files = count($_FILES['mes_documents']['name']);

        for ($i = 0; $i < $num_files; $i++) {
            if ($_FILES['mes_documents']['name'][$i]) {
                $file = array(
                    'name'     => $_FILES['mes_documents']['name'][$i],
                    'type'     => $_FILES['mes_documents']['type'][$i],
                    'tmp_name' => $_FILES['mes_documents']['tmp_name'][$i],
                    'error'    => $_FILES['mes_documents']['error'][$i],
                    'size'     => $_FILES['mes_documents']['size'][$i]
                );

                // Assigner le fichier à un nouvel élément du tableau $_FILES
                $_FILES['upload_file'] = $file;

                $attachment_id = media_handle_upload('upload_file', 0);

                if (is_wp_error($attachment_id)) {
                    // Gérer l'erreur
                    error_log('Erreur de téléchargement de fichier : ' . $attachment_id->get_error_message());
                } else {
                    array_push($file_ids, $attachment_id);
                }
            }
        }
        foreach ($_FILES['mes_documents']['name'] as $key => $value) {
            if ($_FILES['mes_documents']['name'][$key]) {
                $file = array(
                    'name'     => $_FILES['mes_documents']['name'][$key],
                    'type'     => $_FILES['mes_documents']['type'][$key],
                    'tmp_name' => $_FILES['mes_documents']['tmp_name'][$key],
                    'error'    => $_FILES['mes_documents']['error'][$key],
                    'size'     => $_FILES['mes_documents']['size'][$key]
                );

                $_FILES = array("upload_file" => $file);
                $attachment_id = media_handle_upload("upload_file", 0);

                if (is_wp_error($attachment_id)) {
                    // Log de l'erreur
                    error_log('Erreur de téléchargement de fichier : ' . $attachment_id->get_error_message());
                } else {
                    array_push($file_ids, $attachment_id);
                }
            }
        }

        if (!empty($file_ids)) {
            // Récupérer les identifiants de fichiers existants et les fusionner avec les nouveaux
            $existing_file_ids = get_post_meta($post_id, '_mes_documents_ids', true);
            if (!is_array($existing_file_ids)) {
                $existing_file_ids = array();
            }
            $updated_file_ids = array_merge($existing_file_ids, $file_ids);

            // Mettre à jour les métadonnées de la commande
            update_post_meta($post_id, '_mes_documents_ids', $updated_file_ids);
        }
    }
}
add_action('save_post', 'enregistrer_mes_documents');

    /**
     * Ajouter un postbox dans la page commande (admin) pour afficher une aide sur les expéditions
     */
    add_action( 'add_meta_boxes', 'woocommerce_order_shipping_method' );
    function woocommerce_order_shipping_method() {
        add_meta_box(
            'box_order_shipping_helper',
            'Aide expédition',
            'shipping_method_box_html',
            'shop_order',
            'side'
        );
    }
    function shipping_method_box_html( $post ) {
        $order = new WC_Order( $post->ID );
        $items = '';
        foreach ( $order->get_items() as $item_id => $item ) {
            $items .= $item->get_name() . ' x ' . $item->get_quantity() . ', ';
        }
        $items = substr($items, 0, -2);
        ?>
        <div class="shipping_helper">
            <p><b>Cliquer sur une méthode :</b></p>
            <span onclick="selectText(this)">Formule CLÉ EN MAIN HARTMANN</span><br>
            <span onclick="selectText(this)">Formule Messagerie HARTMANN</span><br>
            <span onclick="selectText(this)">Formule CLÉ EN MAIN</span><br>
            <span onclick="selectText(this)">Formule ECO [OFFERTE]</span><br>
            <span onclick="selectText(this)">Formule ECO Plus</span><br>
            <span onclick="selectText(this)">Formule ECO</span><br>
            <span onclick="selectText(this)">Messagerie</span><br>
            <p><b>Ajouter la méta <b class="label" onclick="selectText(this)">supplier</b> à chaque article pour les lier aux fournisseurs :</b></p>
            <p><b>Ajouter la méta <b class="label" onclick="selectText(this)">Articles</b> à chaque expédition pour les lier aux articles :</b></p>
            <?php
                $products_by_supplier = array();
            foreach($order->get_items() as $item_id => $item ) {
                $product = $item->get_product();
                $product_id = $item->get_product_id();
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
                if(!isset($products_by_supplier[$supplier])) {
                    $products_by_supplier[$supplier] = array();
                }
                $products_by_supplier[$supplier][] = $item;
                $pdt_id = ($item->get_variation_id() != null) ? $item->get_variation_id() : $item->get_product_id() ;
            }
            echo '<table style="font-size:13px;">';
            foreach($products_by_supplier as $supplier => $items) {
                echo '<tr><td style="padding-bottom:10px;"><b>Fournisseur : <b class="supplier-echo" style="text-decoration:underline;" onclick="selectText(this)">' . $supplier . '</b></b></td></tr>';
                echo '<tr><td style="padding:0 15px 10px 15px;">';
                $item_list = '';
                foreach($items as $item) {
                    $item_list .= $item->get_name() . ' x ' . $item->get_quantity() . ', ';
                }
                echo '<span onclick="selectText(this)">' . substr($item_list, 0, -2) . '</span>';
                echo '</td></tr>';
            }
            echo '</table>';
			$add_to_cart_link = generate_add_to_cart_link($order);
            echo '<a target="_blank" href="' . esc_url($add_to_cart_link) . '">Ajouter tous les articles au panier</a>';
            ?>
        </div>
        <style>
            .shipping_helper span{cursor:pointer;}
            .shipping_helper span:hover{text-decoration:underline;}
            .shipping_helper .copy, .shipping_helper .label{color:#f7f7f7;padding:2px 4px;border-radius:3px;background-color:#4f4f4f;}
            .shipping_helper .copy{margin-left:5px;font-size:0.75em;}
            .shipping_helper .label{font-size:0.9em;}
            .shipping_helper pre{width:100%;overflow-x:auto;}
            .shipping_helper .supplier-echo:hover{font-size:14px;}
        </style>
        <script>
            function selectText(node) {
                let range;
                if (document.body.createTextRange) {
                    range = document.body.createTextRange();
                    range.moveToElementText(node);
                    range.select();
                } else if (window.getSelection) {
                    range = document.createRange();
                    const selection = window.getSelection();
                    range.selectNodeContents(node);
                    selection.removeAllRanges();
                    selection.addRange(range);
                }
                navigator.clipboard.writeText(node.textContent).then(function() {
                    const copy = document.createElement('b');
                    copy.classList.add('copy');
                    copy.textContent = 'copié';
                    node.insertAdjacentElement('afterend', copy)
                    setTimeout(function() {
                        copy.remove();
                    }, 2000);
                });
            }
        </script>
        <?php
    }


    /**
     * Ajouter une metabox dans la page commande (admin) pour enregistrer le paiement client
     */
    add_action( 'add_meta_boxes', 'woocommerce_payment_box' );
    function woocommerce_payment_box() {
        global $post, $current_screen;
        if ( $current_screen->id == 'shop_order' ) {
            $order = wc_get_order( $post->ID );
            $hide_box = ['mercanet_onetime', 'scalapay_gateway', 'yith-request-a-quote', 'heroPay3X'];
            if ( !in_array($order->get_payment_method(), $hide_box) ) {
                add_meta_box(
                    'box_order_confirm_payment',
                    'Paiement',
                    'add_payment_box_html',
                    'shop_order',
                    'side'
                );
            }
        }
    }
    function add_payment_box_html( $post ) {
        $order = wc_get_order( $post->ID );
        $payment_date = get_post_meta( $order->get_id(), '_custom_payment_date', true );
        $payment_amount = get_post_meta( $order->get_id(), '_custom_payment_amount', true );
        $payment_condition = get_post_meta( $order->get_id(), '_custom_payment_condition', true );
        ?>
        <p>
            <b><?= $order->get_payment_method_title() ?></b>
        </p>
        <p style="display:flex;flex-flow:row wrap;justify-content:space-between;align-items:center;margin:0 0 10px;">
            <label for="custom_payment_date">Date</label>
            <input type="date" id="custom_payment_date" name="payment_date" value="<?= $payment_date ?>" style="width:150px;"/>
        </p>
        <p style="display:flex;flex-flow:row wrap;justify-content:space-between;align-items:center;margin:0 0 10px;">
            <label for="custom_payment_amount">Montant <abbr style="color:#a71818;">*</abbr></label>
            <input type="number" step=".01" id="custom_payment_amount" name="payment_amount" value="<?= $payment_amount ?>" style="width:150px;"/>
        </p>
        <p style="display:flex;flex-flow:row wrap;justify-content:space-between;align-items:center;margin:0 0 10px;">
            <label for="custom_payment_condition">Conditions <abbr style="color:#a71818;">*</abbr></label>
            <select id="custom_payment_condition" name="payment_condition" style="width:150px;">
                <option value="undefined">Non défini</option>
                <option value="a-la-commande" <?= $payment_condition == 'a-la-commande' ? 'selected' : '' ?>>À la commande</option>
                <option value="Accompte-30%-solde-reception" <?= $payment_condition == 'Accompte-30%-solde-reception' ? 'selected' : '' ?>>Accompte 30%, solde à reception</option>
                <option value="Accompte-50%-solde-reception" <?= $payment_condition == 'Accompte-50%-solde-reception' ? 'selected' : '' ?>>Accompte 50%, solde à reception</option>
                <option value="a-reception" <?= $payment_condition == 'a-reception' ? 'selected' : '' ?>>À réception</option>
                <option value="30-jours" <?= $payment_condition == '30-jours' ? 'selected' : '' ?>>Sous 30 jours</option>
                <option value="45-jours" <?= $payment_condition == '45-jours' ? 'selected' : '' ?>>Sous 45 jours</option>
                <option value="60-jours" <?= $payment_condition == '60-jours' ? 'selected' : '' ?>>Sous 60 jours</option>
            </select>
        </p>
        <?php
        if ( !empty($payment_amount) ):
            if ( $payment_amount <> $order->get_total() ):
                $diff = $payment_amount - $order->get_total(); ?>
                <p style="border:1px solid #a71818;color:#a71818;font-weight:bold;padding:5px 10px;">
                    <?= $diff >= 0 ? 'Trop-perçu' : 'Reste à payer'; ?> :
                    <?= number_format(abs($diff), 2, '.', ' ') ?> &euro;
                </p>
            <?php else: ?>
                <p id="metabox-payment-ok" style="border:1px solid #29830b;color:#29830b;font-weight:bold;padding:5px 10px;">
                    Commande réglée
                </p>
            <?php
            endif;
        endif;
        ?>
        <p>
            <input type="hidden" name="payment_box" value="1"/>
            <button type="submit" name="submit_payment_box" class="button">Mettre à jour</button>
            <button type="submit" name="reset_payment_box" style="border:0;background-color:transparent;color:#a71818;cursor:pointer;margin-left:5px;">Supprimer</button>
        </p>
        <script>
            const diff = <?php echo (int) $payment_amount - $order->get_total(); ?>;
            jQuery(document).ready(function($) {
                $('#order_status ~ .select2-container').on('click', function (e) {
                    if ( $('#order_status').val() == 'wc-processing' && diff < 0 ) {
                        $('.modal-alert').find('.title').html('Après avoir passé la commande en terminée');
                        $('.modal-alert').find('.content').html('<b>Modifier la facture associée</b> sur "VosFactures" car elle n\'est pas totalement réglée.');
                        $('.modal-alert').css('display', 'flex');
                    }
                    if ( ( $('#order_status').val() == 'wc-on-hold' || $('#order_status').val() == 'wc-pending' ) && $('#custom_payment_amount').val() == '' ) {
                        $('.modal-alert').find('.title').html('Avant de passer la commande en cours');
                        $('.modal-alert').find('.content').html('<b>Indiquer le montant, la date et les conditions du règlement</b> dans la box "Paiement".');
                        $('.modal-alert').css('display', 'flex');
                    }
                });
            });
        </script>
        <?php
    }
    add_action( 'save_post', 'save_payment_box' );
    function save_payment_box( $post_ID ) {
        if ( isset($_POST['payment_box']) ) {
            if ( isset($_POST['submit_payment_box']) ) {
                $valid_box = [
                    'payment_amount' => false,
                    'payment_condition' => false,
                ];
                if ( isset($_POST['payment_amount']) ) {
                    $valid_box['payment_amount'] = true;
                }
                if ( isset($_POST['payment_condition']) && $_POST['payment_condition'] != 'undefined' ) {
                    $valid_box['payment_condition'] = true;
                }
                if ( ! in_array(false, $valid_box, true) ) {
                    update_post_meta($post_ID, '_custom_payment_amount', esc_html($_POST['payment_amount']));
                    update_post_meta($post_ID, '_custom_payment_condition', esc_html($_POST['payment_condition']));
                    if ( isset($_POST['payment_date']) && !empty($_POST['payment_date']) ) {
                        update_post_meta($post_ID, '_custom_payment_date', esc_html($_POST['payment_date']));
                    }
                    add_filter( 'redirect_post_location', 'add_payment_box_query_success', 99 );
                } else {
                    add_filter( 'redirect_post_location', 'add_payment_box_query_error', 99 );
                }
            }
            if ( isset($_POST['reset_payment_box']) ) {
                delete_post_meta($post_ID, '_custom_payment_date');
                delete_post_meta($post_ID, '_custom_payment_amount');
                delete_post_meta($post_ID, '_custom_payment_condition');
                add_filter( 'redirect_post_location', 'add_payment_box_query_reset', 99 );
            }
        }
    }
    add_action( 'admin_notices', 'payment_box_notices', 20 );
    function payment_box_notices() {
        if ( ! isset( $_GET['payment-notice'] ) ) {
            return;
        }
        $message = '';
        switch ( $_GET['payment-notice'] ) {
            case 'success':
                $message = 'Les informations du réglement sont bien enregistrées';
                break;
            case 'error':
                $message = 'Renseigner tous les champs obligatoires du réglement';
                break;
            case 'info':
                $message = 'Les informations du réglement sont bien supprimées';
                break;
        }
        printf('<div class="notice notice-%s"><p>%s</p></div>', $_GET['payment-notice'], $message);
    }
    function add_payment_box_query_success( $location ) {
        remove_filter( 'redirect_post_location', 'add_payment_box_query_success', 99 );
        return add_query_arg( 'payment-notice', 'success', $location );
    }
    function add_payment_box_query_error( $location ) {
        remove_filter( 'redirect_post_location', 'add_payment_box_query_error', 99 );
        return add_query_arg( 'payment-notice', 'error', $location );
    }
    function add_payment_box_query_reset( $location ) {
        remove_filter( 'redirect_post_location', 'add_payment_box_query_error', 99 );
        return add_query_arg( 'payment-notice', 'info', $location );
    }

    /**
     * Action pour créer un numéro de commande dans les actions de commande (admin)
     */
    add_action( 'woocommerce_order_actions', 'custom_add_order_meta_box_action' );
    function custom_add_order_meta_box_action( $actions ) {
        global $post;
        if (empty(get_post_meta( $post->ID, '_ywson_custom_number_order_complete', true ))) {
            global $wpdb;
            $query = $wpdb->prepare( "SELECT option_value AS next_number FROM {$wpdb->options} WHERE option_name = %s ", 'ywson_base_module_settings' );
            $value = maybe_unserialize( $wpdb->get_var( $query ) );
            $next_number = YWSON_Manager()->get_prefix() . $value['order_number'];
            $actions['wc_generate_order_number_action'] = 'Générer le numéro de commande ' . $next_number;
        }
        return $actions;
    }
    add_action( 'woocommerce_order_action_wc_generate_order_number_action', 'woocommerce_generate_new_sequential_order_action' );
    function woocommerce_generate_new_sequential_order_action( $order ) {
        YWSON_Manager()->generate_sequential_order_number( $order );
        $message = 'Génération du numéro ' . get_post_meta( $order->get_id(), '_ywson_custom_number_order_complete', true ) . ' par ' . wp_get_current_user()->display_name;
        $order->add_order_note( $message );
    }


    /**
     * Display Conditions commerciales:
     */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'nolo_custom_field_display_cust_order_meta', 10, 1 );
    function nolo_custom_field_display_cust_order_meta($order){
        $user = $order->get_user();
        if (!empty(get_user_meta($user->ID, 'description', true))){
            echo '<p><strong> Conditions commerciales:</strong><br><span style="color:red;">' . get_user_meta($user->ID, 'description', true) . '</span></p>';
        }
    }
	  /*
    * Affiche commercial @Marion
    */
		function display_marion_if_completed_order_before($order) {
			$customer_id = $order->get_customer_id();

			if ($customer_id) {
				$args = array(
					'customer_id' => $customer_id,
					'limit' => -1, // Récupérer toutes les commandes pour ce client
					'status' => 'completed', // Seulement les commandes terminées
				);
				$orders = wc_get_orders($args);
				foreach ($orders as $order_item) {
					if ($order_item->get_date_completed() && $order_item->get_date_completed()->date('Y-m-d') <= '2023-12-31') {
						echo '<h3 style="color:#e5128f;font-weight:bold;padding:10px;margin:90px 0 0;border:1px solid #e5128f; font-size:1.2em;clear:both;">Suivi par @Marion</h3>';
						break; // Arrête la boucle dès qu'une commande correspondante est trouvée
					}
				}
			}
		}
	add_action('woocommerce_admin_order_data_after_order_details', 'display_marion_if_completed_order_before', 12);


    /*
    * Affiche le nombre de commande passé par le client sur la page de commandes en admin
    */
    add_action('woocommerce_admin_order_data_after_order_details', 'display_customer_orders', 12);
    function display_customer_orders($order) {
        $customer_id = $order->get_customer_id();

        if ( $customer_id ) {
            $args = array(
                'customer_id' => $customer_id,
                'limit' => 6,
            );
            $orders = wc_get_orders($args);

            if (!empty($orders)) {
                $total_orders = wc_get_customer_order_count($customer_id);
                if ($total_orders > 1) {
                    echo '<table id="old_order_clt" style="clear: both;"><thead><tr style="border-spacing:0"><th>Numéro de commande</th><th>Statut de la commande</th><th>Date de commande</th></tr></thead><tbody>';
                    $i = 1;
                    foreach ($orders as $order_item) {
                        if ($i % 2 == 0){
                            $paire = "#f8f8f8";
                        }else $paire = "#fff";
                        if($order_item->get_id() !== $order->get_id()){
                            echo  '<tr style="border-spacing:0;background-color:'.$paire.';"><td>' . $order_item->get_order_number() . '</td>';
                            $orderItemId = $order_item->get_id();
                            $order_status = get_post_status_object( get_post_status( $orderItemId ) );
                            echo '<td>' . $order_status->label . '</td>';
                            echo '<td>' . $order_item->get_date_created()->format('d/m/Y') . '</td></tr>';
                        }
                        $i++;
                    }
                    echo '</tbody><tfoot>';
                    $total_orders = wc_get_customer_order_count($customer_id);
                    if ($total_orders > 5) {
                        echo '<tr><td><strong>Total de commandes : ' . $total_orders . '</strong></td></tr>';
                    }
                    echo '</tfoot></table>';
                } else {
                    echo '<h3 style="margin-top:55px;color:#D10000"><strong>C&#8217;est la première commande de ce client !<strong></h3>';
                }
            }
        }
    }

    function modifier_titre_commande_admin($order_id) {
        $order = wc_get_order($order_id);
        $order_number = $order->get_order_number();
        $order_type_object = get_post_type_object( $order->get_type() );
        $status = $order->get_status();
        $order_name = $order_type_object->labels->singular_name;
        $new_heading = '';
        
        // Modifier le titre en fonction du statut de la commande
        switch ($status) {
            case 'ywraq-new':
                $new_heading = 'Détails Devis n°'.$order_number;
                break;
            case 'ywraq-pending':
                $new_heading = 'Détails Devis n°'.$order_number;
                break;
            case 'ywraq-rejected':
                $new_heading = 'Détails Devis n°'.$order_number;
                break;
            case 'ywraq-expired':
                $new_heading = 'Détails Devis n°'.$order_number;
                break;
            case 'ywraq-accepted':
                $new_heading = 'Détails Devis n°'.$order_number;
                break;
            // Ajoutez d'autres cas en fonction de vos besoins
            default:
                $new_heading = 'Détails Commande n°'.$order_number;
                break;
        }
        
        // Afficher le nouveau titre
        echo '<h2 class="woocommerce-order-data__heading titre-page-order-detail-admin">' . $new_heading . '</h2>';
    }
    add_action('woocommerce_admin_order_data_after_order_details', 'modifier_titre_commande_admin');

    function edit_css_order_detail_admin(){
        ?><style>.woocommerce-order-data__heading{visibility: hidden;}
        .titre-page-order-detail-admin{visibility: visible!important;position: absolute;top: 15px;}
        </style>';
        <?php
    }
    add_action( 'admin_head', 'edit_css_order_detail_admin' );

    /*
    * Vérifie le statut de la commande et si c'est un devis alors on appelle une fonction pour cacher l'expedition partielle
    */
    function hide_partial_shipment_notice( $order_id ) {
        // Récupérer l'objet de la commande
        $order = wc_get_order( $order_id );
        if ($order){
            // Récupérer le statut de la commande
            $order_status = $order->get_status();
        
            // Liste des statuts de commande qui ne permettent pas d'expédition partielle
            $disallowed_statuses = array( 'ywraq-new', 'ywraq-pending', 'ywraq-rejected', 'ywraq-expired', 'ywraq-accepted' );
            
            // Vérifier si le statut de la commande est dans la liste des statuts non autorisés
            if ( in_array( $order_status, $disallowed_statuses ) ) {
                // Cacher l'expédition partielle
                $hide_exped = true;
                
            }
        }

        // Passer la valeur de $hide_exped à la fonction hide_exped()
        if (isset($hide_exped) && $hide_exped) {
        hide_expedition(true);
        }
    }
    add_action( 'woocommerce_admin_order_data_after_order_details', 'hide_partial_shipment_notice' );

    /*
    * Cache l'expedition partielle
    */
    function hide_expedition($val=false) {
        if ($val){
        ?>
        <style>
        .wxp-partital-line-item, .wxp-partital-item-icon { visibility: hidden !important; }
        .wxp-partital-item-head{color:transparent !important;}
        </style><?php
        }
    }
    add_action( 'admin_head', 'hide_expedition' );

    /**
     * Vérifier si erreur email client page commande (admin)
     */
    add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocommerce_admin_check_email_billing' );
    function woocommerce_admin_check_email_billing( $order ) {
        $email = $order->get_billing_email();
        if ( !empty($email) ) echo woocommerce_admin_check_email_error( $email, $order->get_date_created() );
    }
    add_action( 'woocommerce_admin_order_data_after_shipping_address', 'woocommerce_admin_check_email_shipping' );
    function woocommerce_admin_check_email_shipping( $order ) {
        $email = get_post_meta($order->get_id(), '_shipping_email', true);
        if ( !empty($email) && $email != $order->get_billing_email() ) echo woocommerce_admin_check_email_error( $email, $order->get_date_created() );
    }
    function woocommerce_admin_check_email_error( $email, $date_created ) {
        global $wpdb;
        $format = 'Y-m-d H:i:s';
        $date_start = new DateTime($date_created->date($format));
        $date_end = new DateTime($date_created->date($format));
        $date_start->sub(new DateInterval('P2M'));
        $date_end->add(new DateInterval('P2M'));
        $request = $wpdb->get_results( $wpdb->prepare( "SELECT * from {$wpdb->prefix}wpmailsmtp_emails_log
        WHERE `people` LIKE '%s' 
        AND (`error_text` != '' OR `error_text` != NULL)
        AND `date_sent` BETWEEN '%s' and '%s'
        ORDER BY `date_sent` DESC LIMIT 1", '%'.$email.'%', $date_start->format($format), $date_end->format($format) ) );
        if (sizeof($request) > 0) {
            return '<span style="display:inline-block;font-size:11px;color:#b20718;border:1px solid #b20718;padding:7px 12px;"><b>Email error ' . $request[0]->date_sent .'</b><br>'. $request[0]->error_text . '</span>';
        }
        return null;
    }
    function wpms_failed_email_notification( $wp_error ) {
        $admin_email = get_option( 'admin_email' ); // Adresse e-mail de l'administrateur du site

        // Vérifier si le problème est lié à l'envoi de l'e-mail
        if ( isset( $wp_error->errors['wp_mail_failed'] ) ) {
            // Envoyer un e-mail de notification
            wp_mail( $admin_email, 'Échec de l\'envoi d\'e-mail', 'Échec de l\'envoi d\'un e-mail via WP Mail SMTP.' );
        }
    }
    add_action( 'wp_mail_failed', 'wpms_failed_email_notification' );

}

