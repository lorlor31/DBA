<?php
if( !defined( 'ABSPATH' ) )
die( 'Cheatin\' uh?' );

add_action('wc_ajax_yith_ywraq_action', 'custom_yith_ywraq_action');
add_action('wc_ajax_nopriv_yith_ywraq_action', 'custom_yith_ywraq_action');

function custom_yith_ywraq_action() {
    $data = $_POST;

    error_log('Custom YITH Request a Quote AJAX handler called');
    error_log(print_r($data, true));

    $action = isset($data['ywraq_action']) ? $data['ywraq_action'] : 'unknown';

    error_log('Action: ' . $action);

    switch ($action) {
        case 'add_item':
            error_log('Adding item to quote');
            error_log(print_r($data, true));
            break;
        case 'refresh_quote_list':
            error_log('Refreshing quote list');
            wp_send_json(array('response' => do_shortcode('[yith_ywraq_request_quote_table]')));
            break;
        default:
            error_log('Unknown action: ' . $action);
            break;
    }

    yith_ywraq_action();
}

/**
 *  Mon compte Télécharger vos photos 
 */
	// Ajoute un nouvel onglet dans l'espace Mon compte
	function ajouter_onglet_photos_compte_client($items) {
		$items['vos-photos'] = __('Vos Photos & Vidéos', 'text-domain');
		return $items;
	}
	add_filter('woocommerce_account_menu_items', 'ajouter_onglet_photos_compte_client');
	// Affiche le contenu de l'onglet
	function afficher_contenu_onglet_photos() {
		echo '<h3>'. __('Télécharger vos photos ou vidéos', 'text-domain') .'</h3>';
		echo '<p>'. __('Plus il y en a, plus on est ravi ! ', 'text-domain') .'</p>';
		echo '<form action="" method="post" enctype="multipart/form-data">';
		echo '<input type="file" name="media" id="media">';
		echo '<input type="submit" name="upload_media" value="'. __('Envoyer', 'text-domain') .'">';
		echo '</form>';
		if (isset($_POST['upload_media']) && !empty($_FILES['media']['name'])) {
			if (!current_user_can('upload_files')) {
				echo '<p>'. __('Vous n\'êtes pas autorisé à envoyer des fichiers.', 'text-domain') .'</p>';
				return;
			}
			$file = $_FILES['media'];
			$file_type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
			$allowed_file_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'video/ogg'];

			if (!in_array($file_type['type'], $allowed_file_types)) {
				echo '<p>'. __('Type de fichier non autorisé.', 'text-domain') .'</p>';
				return;
			}

			if ($file['size'] > 150000000) { // 150 Mo en octets
				echo '<p>'. __('Le fichier est trop volumineux.', 'text-domain') .'</p>';
				return;
			}

			require_once(ABSPATH . 'wp-admin/includes/image.php');
			require_once(ABSPATH . 'wp-admin/includes/file.php');
			require_once(ABSPATH . 'wp-admin/includes/media.php');

			$attachment_id = media_handle_upload('media', 0); // Mise à jour 'photo' par 'media'
			
			if (is_wp_error($attachment_id)) {
				echo '<p>'. __('Erreur lors du téléchargement.', 'text-domain') .'</p>';
			} else {
				$media_type = strpos($file_type['type'], 'image/') !== false ? __('Photo', 'text-domain') : __('Vidéo', 'text-domain');
				echo '<p>'. __('Votre fichier a été envoyé avec succès.', 'text-domain') .'</p>';
				echo wp_get_attachment_image($attachment_id, 'thumbnail');
				// echo '<p class="media-type">'. $media_type .'</p>';
				$user = wp_get_current_user();
				$user_email = $user->user_email;
				$user_name = $user->display_name; 
				$to = 'service-client@armoireplus.fr'; 
				$cc = 'cfeltin@armoireplus.fr'; 
				$subject = 'Un client a chargé une photo ou vidéo';
				$body = 'L\'utilisateur '. $user_name.' ( '. $user_email. ' ) a chargé une ' . $media_type . '<br><br>' . 'ID du fichier dans la médiathèque : ' . $attachment_id;
				$headers = array(
					'Content-Type: text/plain; charset=UTF-8',
					'Cc: ' . $cc // Ajout de l'adresse en copie
				);
				
				// Envoi de l'email
				wp_mail($to, $subject, $body, $headers);

				echo '<br><p>'. __('Une fois visualisée par notre service qualité, nous reviendrons vers vous.', 'text-domain') .'</p>';
			}
		}
	}
	add_action('woocommerce_account_vos-photos_endpoint', 'afficher_contenu_onglet_photos');
	function ajouter_onglet_photos_compte_client_endpoints() {
		add_rewrite_endpoint('vos-photos', EP_ROOT | EP_PAGES);
	}
	add_action('init', 'ajouter_onglet_photos_compte_client_endpoints');




function load_custom_admin_styles() {
    wp_enqueue_style('custom_admin_styles', get_stylesheet_directory_uri() . '/style_admin.css');
}
add_action('admin_enqueue_scripts', 'load_custom_admin_styles');

   /**
     * J'ajoute un tooltip ayu dessu du mail de livraison
     */
function custom_admin_scripts() {
    wp_enqueue_script('custom_admin_js', get_stylesheet_directory_uri() . '/js/custom-script.js', array('jquery'), null, true);
}
add_action('admin_enqueue_scripts', 'custom_admin_scripts');



   /**
     * Load custom css and js files
     */
    // add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
    // function theme_enqueue_styles() {
        // wp_enqueue_style( 'legenda-restyle', get_template_directory_uri() . '/vc_legenda_style.css' );
        // wp_enqueue_style( 'parent-style', get_stylesheet_directory_uri() . '/style.css' );
        // wp_enqueue_style( 'parent-style', get_stylesheet_directory_uri() . '/style-cookiebot.css' );
        // wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri().'/js/custom-script.js', array ( 'jquery' ) );
        // if ( is_product() ) wp_enqueue_script( 'pdfobject-script', get_stylesheet_directory_uri().'/js/pdfobject.min.js', array ( 'jquery' ) );
    // }
/**
 * Load custom css and js files  MAJ requete AJAX pour code postaux caray
 */
function enqueue_code_postaux_script() {
    wp_enqueue_script('code-postaux-script', get_stylesheet_directory_uri() . '/js/code-postaux-script.js', array('jquery'), null, true);
    wp_localize_script('code-postaux-script', 'wc_params', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_code_postaux_script');
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'legenda-restyle', get_template_directory_uri() . '/vc_legenda_style.css' );
    wp_enqueue_style( 'parent-style', get_stylesheet_directory_uri() . '/style.css' );
    wp_enqueue_style( 'parent-cookiebot-style', get_stylesheet_directory_uri() . '/style-cookiebot.css' );
    wp_enqueue_script( 'custom-script', get_stylesheet_directory_uri().'/js/custom-script.js', array('jquery'), null, true );

    if ( is_product() ) {
        wp_enqueue_script( 'pdfobject-script', get_stylesheet_directory_uri().'/js/pdfobject.min.js', array('jquery'), null, true );
    }
}

    if (is_admin()){
        /**
     * Load admin css and js files
     */
    add_action( 'admin_enqueue_scripts', 'theme_admin_enqueue_script' );
    function theme_admin_enqueue_script() {
        wp_enqueue_script( 'admin-script', get_stylesheet_directory_uri() . '/js/admin-script.js', true, '1.1', 'all' );
    }
}


if (is_admin()){
	/*
	 * remove_comments & yoast from admin toolbarr
	 */
	function remove_comments(){
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu('comments');
        $wp_admin_bar->remove_menu('wpseo-menu');
	}

	/**
     * Add update warning for a plugin on plugin page
     */
    add_filter( 'plugin_row_meta', 'info_update_plugin', 10, 2 );
    function info_update_plugin($actions, $plugin_name) {
        if (strpos($plugin_name, 'woocommerce-dropshipping/') !== false ||
            strpos($plugin_name, 'woocommerce/woocommerce.php') !== false ||
            strpos($plugin_name, 'hmenu/') !== false ||
            strpos($plugin_name, 'popup-maker/') !== false ||
            strpos($plugin_name, 'wc-partial-shipment-pro/') !== false ||
            strpos($plugin_name, 'filter-everything-pro') !== false)
         {
            echo '<b style="color:#d20808;font-size:1.1em;">Update warning: plugin files are modified</b><br>';
        }
        return $actions;
    }

	/**
     * Gestion des éco-contributions dans édition des commandes back-office (recalculer)
     */
    add_action( 'woocommerce_order_before_calculate_totals', 'custom_order_before_calculate_totals', 10, 2 );
    function custom_order_before_calculate_totals( $and_taxes, $order ) {
        $eco_fee = 0;
        $eco_fee_name = 'éco-participation';
        foreach( $order->get_items() as $item ) {
            $product = $item->get_product();
            $product_quantity = $item->get_quantity();
            if ( $product->is_type('variation') ) {
                $id = $item->get_variation_id();
            } else {
                $id = $item->get_product_id();
            }
            $fee_name = get_post_meta( $id, 'product-fee-name', true );
            $fee_amount = get_post_meta( $id, 'product-fee-amount', true );
            if ( empty($fee_name) && $product->get_type() == 'variation' ) {
                $fee_name = get_post_meta( $item->get_product_id(), 'product-fee-name', true );
            }
            if ( $fee_name == $eco_fee_name && $fee_amount > 0 ) {
                $current_fee = $fee_amount * $product_quantity;
                $eco_fee += $current_fee;
            }
        }
        if ( $eco_fee > 0 ) {
            $add_fee = false;
            foreach( $order->get_items('fee') as $item_id => $item_fee ) {
                if ( $item_fee->get_name() == $eco_fee_name ) {
                    $item_fee->set_total( $eco_fee );
                    $item_fee->save();
                    $add_fee = true;
                }
            }
            if ( !$add_fee ) {
                $item_fee = new WC_Order_Item_Fee();
                $item_fee->set_name( $eco_fee_name );
                $item_fee->set_total( $eco_fee );
                $item_fee->save();
                $order->add_item( $item_fee );
                $order->save();
            }
        }
    }

}else{
	/**
	 * Change the breadcrumb home text from "Home" to "Mobilier Professionnel".
	 */
	add_filter( 'woocommerce_breadcrumb_defaults', 'woo_change_breadcrumb_home_text' );
	function woo_change_breadcrumb_home_text( $defaults ) {
		$defaults['home'] = 'Mobilier Professionnel';
		return $defaults;
	}

	/** Supprimer Query String   */
	function remove_query_strings() {
		if(!is_admin()) {
			add_filter('script_loader_src', 'remove_query_strings_split', 15);
			add_filter('style_loader_src', 'remove_query_strings_split', 15);
		}
	}
	
	function remove_query_strings_split($src){
		$output = preg_split("/(&ver|\?ver)/", $src);
		return $output[0];
	}
	add_action('init', 'remove_query_strings');

    /**
     * Custom the admin bar on front
     */
    add_action( 'admin_bar_menu', 'update_admin_bar', 999 );
    function update_admin_bar($wp_adminbar) {
        $wp_adminbar->remove_node('comments');
        $wp_adminbar->remove_node('LegendaThemeOptions');
        $wp_adminbar->remove_node('updates');
        $wp_adminbar->remove_node('new-content');
        $wp_adminbar->remove_node('assetcleanup-parent');
        $wp_adminbar->remove_node('wpseo-menu');
        $wp_adminbar->remove_node('et-top-bar-menu');
        $wp_adminbar->remove_node('wcct_admin_page_node');
        $wp_adminbar->remove_node('wp-rocket');
        $wp_adminbar->remove_node('top-secondary');
    }
	//Hide WPBakery Admin Bar 
	function vc_remove_wp_admin_bar_button() {
	  remove_action( 'admin_bar_menu', array( vc_frontend_editor(), 'adminBarEditLink' ), 1000 );
	}
	add_action( 'vc_after_init', 'vc_remove_wp_admin_bar_button' );

	/**
     * Variations non dependantes page produit
     * Passer le parametre de 10 à 100 > toutes les variations soient prise en compte sur la page produit en FO et propose en option 2 un attribut dependant du premier
     */
    add_filter( 'woocommerce_ajax_variation_threshold', 'custom_wc_ajax_variation_threshold', 10, 2 );
    function custom_wc_ajax_variation_threshold( $qty, $product ) {
        return 100;
    }

	/**
     * Afficher 3 produits par ligne dans le catalogue
     */
    add_filter('loop_shop_columns', 'loop_columns');
    if (!function_exists('loop_columns')) {
        function loop_columns() {
            return 3;
        }
    }

	/**
     * Modifier le google product feed
     */
    add_filter( 'woocommerce_gpf_feed_item_google', 'lw_woocommerce_gpf_feed_item_google', 11, 2 );
    function lw_woocommerce_gpf_feed_item_google( $feed_item, $product ) {
        $feed_item->guid = strtoupper($product->get_sku());
        if ( $product->get_type() == 'variation' ) {
            $parent = wc_get_product($product->get_parent_id());
            $feed_item->item_group_id = strtoupper($parent->get_sku());
        }
        $feed_item->sale_price_inc_tax = $feed_item->sale_price_ex_tax * 1.2;
        $feed_item->regular_price_inc_tax = $feed_item->regular_price_ex_tax * 1.2;
        $feed_item->description = strip_tags(preg_replace('#(.+)Plus de Caractéristiques(.+)#', '$1', $feed_item->description));
        $feed_item->description = preg_replace('#(.+)was last modified(.+)#', '$1', $feed_item->description);
        $feed_item->description = str_replace('&#8211;', '-', $feed_item->description);
        // $feed_item->additional_elements['product_type'] = array(mb_convert_case(''.$feed_item->additional_elements['product_type'][0], MB_CASE_TITLE, "UTF-8"));
        // $feed_item->additional_elements['product_type'] = array(str_ireplace('&gt;', '>', $feed_item->additional_elements['product_type'][0]));
		if( isset($feed_item->additional_elements['google_product_category']) ){
			$feed_item->additional_elements['google_product_category'] = array(str_ireplace('&gt;', '>', $feed_item->additional_elements['google_product_category'][0]));
		}

        if ( $product->is_on_sale() && $feed_item->sale_price_ex_tax > 0 ) {
            $feed_item->additional_elements['custom_label_0'] = array('promotion');
            $feed_item->additional_elements['custom_label_2'] = array(number_format($feed_item->sale_price_ex_tax, 2) . ' EUR');
        } else {
			if ($feed_item->regular_price_ex_tax != null) {
            unset($feed_item->additional_elements['custom_label_0']);
            $feed_item->additional_elements['custom_label_2'] = array(number_format($feed_item->regular_price_ex_tax, 2) . ' EUR');
			}
        }

        // Doofinder price
		if ($feed_item->regular_price_ex_tax != null) {
        $feed_item->additional_elements['doofinder_regular_price'] = array(number_format($feed_item->regular_price_ex_tax, 2) . ' EUR');
		}
        if ( $product->is_on_sale() && $feed_item->sale_price_ex_tax > 0 ) {
            $feed_item->additional_elements['doofinder_sale_price'] = array(number_format($feed_item->sale_price_ex_tax, 2) . ' EUR');
        }

        // Tableau des best sellers avec chaque ref en majuscule
        $bestseller = ['AMC198120ANAN','VMM243MGRGR','AIPBH1253B','AMC198120NRNR','AMC198120BLBL','AIPBH1253G','VMM343CGRBS','AMC100120ANAN','APB198120AN','AIPBH1243B','VMIS41C1GRGR','AIPBH1053B','AIPBB1253B','VMM234MGRBS','AMC100100ANAN','AMC100120GRGR','AMC100120BNBN','AMC198120LCBL','AMC100120BLER'];
        if (in_array($feed_item->guid, $bestseller)) {
            $feed_item->additional_elements['custom_label_4'] = array('best seller');
        } else {
            unset($feed_item->additional_elements['custom_label_4']);
        }

        return $feed_item;
    }

    /**
     * Fix issue sale_price doofinder with google product feed
     */
    add_filter( 'woocommerce_gpf_feed_item_google', 'lw_woocommerce_gpf_remove_sale', 12, 2 );
    function lw_woocommerce_gpf_remove_sale( $feed_item, $product ) {
        if ( !$product->is_on_sale() ) {
            unset(
                $feed_item->sale_price_inc_tax,
                $feed_item->sale_price_ex_tax,
                $feed_item->sale_price_start_date,
                $feed_item->sale_price_end_date
            );
        }
        return $feed_item;
    }

	/**
     * Info factures dans espace client
     */
    add_action( 'woocommerce_before_account_orders', 'woocommerce_invoice_message_orders' );
    function woocommerce_invoice_message_orders() {
        ?>
        <div class="info info-invoice">
            <i class="ico-invoice"></i><h4>Facturation</h4>
            <p>Les factures sont éditées par le service comptabilité après expédition des marchandises dans un délai de 1 à 2 jours. Vous la recevrez ensuite sur votre compte client et par courrier électronique à l’adresse email de facturation indiquée au moment de la commande. Veuillez <a href="/contact/" style="text-decoration:underline;">nous contacter</a> pour tout impératif.</p>
        </div>
        <?php
    }

    /**
     * Info livraison dans espace client
     */
    add_action( 'woocommerce_account_dashboard', 'woocommerce_invoice_message_delivery' );
    function woocommerce_invoice_message_delivery() {
        ?>
        <div class="info info-delivery">
            <i class="ico-truck"></i><h4>Rappel lors de votre livraison</h4>
            <p>
                Veuillez vérifier <b>l'état de la marchandise avant la signature de votre bon de livraison</b> en présence du chauffeur ou du prestataire.<br><br>
                Si votre commande n'est pas conforme ou présente un défaut, <b>merci d'envoyer un mail à <a href="mailto:contact@armoireplus.fr">contact@armoireplus.fr</a> faisant état de la situation photos à l'appui, ainsi que votre numéro de téléphone et numéro de commande</b>. S'il s'agit d'une détérioration, veillez à prendre des photos présentant le détail de l'impact mais également la vue d'ensemble du meuble endommagé. Le délai moyen de réponse est de 2 jours ouvrés.
            </p>
        </div>
        <?php
    }

	/**
     * Vérifier si un produit est en promotion pr ajouter un meta promo dans la commande necessaire pour la cde envoyée au frs voir packingslip   $promo_ids
     */
	add_action( 'ywraq_after_create_order', 'add_meta_quote_product_promotion', 10, 3 );
	function add_meta_quote_product_promotion( $order_id, $posted, $raq ){
		get_order_product_promotion( $order_id );
	}
    add_action( 'woocommerce_checkout_order_processed', 'get_order_product_promotion' );
    function get_order_product_promotion( $order_id ) {
        $order = wc_get_order( $order_id );
        $items = $order->get_items();
        $current_promo = false;
        $tab_promo = $date_promo = [];
        foreach ( $items as $item_id => $item ) {
            $product = $item->get_product();
            if ( $product->is_on_sale() ) {
                $current_promo = true;
                $tab_promo[] = $product->get_id();
                $date = '';
                if ( !empty($product->get_date_on_sale_to()) ) {
                    $date .= 'jusqu\'au ' . date_format($product->get_date_on_sale_to(), 'd/m/Y');
                }
                $date_promo[] = $date;
            }
        }
        if ( $current_promo ) {
            update_post_meta( $order_id, '_promo_pdt_ids', implode(',', $tab_promo) );
            update_post_meta( $order_id, '_promo_pdt_dates', implode(',', $date_promo) );
        }
    }

	/**
     * Afficher les tags html dans la description des catégories (schema json) et retirer la description des sous pages
     */
    remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
    add_action( 'woocommerce_archive_description', 'woocommerce_edit_category_description', 10 );
    function woocommerce_edit_category_description() {
        if ( is_product_category() ) {
            $paged = get_query_var('paged') ?: 1;
            if ( $paged == 1 ) {
                global $wp_query;
                $cat_id = $wp_query->get_queried_object_id();
                $cat_desc = term_description( $cat_id, 'product_cat' );
                echo '<div class="term-description">' . $cat_desc . '</div>';
            }
        }
    }

    /**
     * Afficher le bouton de la visionneuse pdf
     */
    add_shortcode( 'pdf-embedder', 'create_pdf_popup' );
    function create_pdf_popup( $atts ) {
        $a = shortcode_atts( array(
            'url' => '',
            'title' => ''
        ), $atts );
        if ( empty($a['url']) ) return;
        return "<button class='pdf-viewer-btn' data-url='{$a['url']}' data-title='{$a['title']}'>Ouvrir le document pdf</button>";
    }

    /**
     * Ajouter le logo DBA dans le footer
     */
    add_action( 'wp_footer', 'add_logo_footer' );
    function add_logo_footer() {
        if ( !is_admin() ) {
            echo '<div class="logo-footer-bottom"><button style="border:none" onclick="location.href=\'https://www.armoireplus.fr/nos-marques/\'" aria-labelledby="labeldba"><img src="https://www.armoireplus.fr/wp-content/uploads/2021/12/DBA_3.0_small.png" width="120" id="labeldba"></a></div>';
        }
    }

	/*
	Ajouter description Marketing Single product via la meta desc_courte_market
	*/
	add_filter( 'woocommerce_short_description', 'desc_courte_marketing', 10, 1 );
	function desc_courte_marketing($post_excerpt) {
		$product_id = get_the_ID(); // Récupère l'ID du produit actuel
		$product = wc_get_product($product_id); // Récupère l'objet produit

		if (!$product || !is_object($product)) {
			return $post_excerpt; // Retourne l'extrait original si l'objet produit n'a pas pu être récupéré
		}

		$product_id = $product->get_id(); // Utiliser la méthode get_id() plutôt que la propriété id directement
		$content = get_post_meta($product_id, 'desc_courte_market', true);
		$post_excerpt = ($content != '') ? '<p style="color:#051d95;"><i class="icon-star-empty" style="margin:0 7px 0 2px;font-size:0.8em;"></i>' . $content . '</p><br>' . $post_excerpt : $post_excerpt;

		return $post_excerpt;
	}

	/**
	 * Product TTC + Eco contribution Range
	 */
	add_filter( 'woocommerce_get_price_html', 'format_product_price', 10, 2 );
	function format_product_price( $price, $product ) {
		$tva = 1.2;
		$taux_reduc = false;
		if ( $product->get_type() == 'variable' ) {
			$min_var_reg_price = $product->get_variation_regular_price( 'min', true );
			$min_var_sale_price = $product->get_variation_sale_price( 'min', true );
			$max_var_reg_price = $product->get_variation_regular_price( 'max', true );
			$max_var_sale_price = $product->get_variation_sale_price( 'max', true );
			if ( !($min_var_reg_price == $max_var_reg_price && $min_var_sale_price == $max_var_sale_price) ) {
				if ( $min_var_sale_price < $min_var_reg_price ) {
					$price = sprintf( __( '<span class="apartirde">À PARTIR DE &nbsp;</span><del>%1$s</del> %2$s', 'woocommerce' ), wc_price( $min_var_reg_price ), wc_price( $min_var_sale_price ) );
					$prix_reduc = floatval($min_var_reg_price) - floatval($min_var_sale_price);
					$taux_reduc = ( $prix_reduc / floatval($min_var_reg_price)) * 100;
					$taux_reduc = round($taux_reduc);
				} else {
					$price = sprintf( __( '<span class="apartirde">À PARTIR DE &nbsp;</span> %1$s', 'woocommerce' ), wc_price( $min_var_reg_price ) );
				}
			}
		}else {
			if($product->is_on_sale()){
				$regular_price = $product->get_regular_price();
				$sale_price = $product->get_sale_price();
				$prix_reduc = floatval($regular_price) - floatval($sale_price);
				$taux_reduc = floatval(( $prix_reduc / floatval($regular_price))) * 100;
				$taux_reduc = round($taux_reduc);
			}
		}
		$price .= '<span class="suffix_euro">HT</span>';
		if ( is_product() ) {
			if ($taux_reduc != false){
				$price .= '<div class="economie"><i class="icon-money" style="margin-right: 5px;"></i>Économisez '. round($prix_reduc, 0) .' € (' . $taux_reduc  .'%)</div>';
			}
		}
		if ( (is_woocommerce() || is_page('bon-plan')) && $product->is_on_sale() ) {
			if ($product->get_id() == 683 || $product->get_id() == 71) {
				$price .= '<span class="fin-promo">Prix bloqués</span>';
			}else{
			$date_end = date('t/m/Y');
			// $price .= '<span class="fin-promo">Valable jusqu\'au ' . $date_end . '</span>';
			}
		}
		return $price;
	}

}

/**
 * Fix compatibulity with Woocommerce 5.6 and F4 shipping to display phone shipping number
 */
add_filter('F4/WCSPE/append_phone_field_to_formatted_address', '__return_true');

/**
 * Reduce the strength requirement for woocommerce registration password.
 * Strength Settings:
 * 0 = Nothing = Anything
 * 1 = Weak
 * 2 = Medium
 * 3 = Strong (default)
 */
add_filter( 'woocommerce_min_password_strength', 'woocommerce_set_password_strength' );
function woocommerce_set_password_strength( $strength ) {
    return 1;
}

/**
 * Vérifier si environnement staging ou production
 */
function is_staging() {
    $staging_markers = ['mdev', 'staging', 'test']; // Ajoutez d'autres identifiants au besoin
    foreach ($staging_markers as $marker) {
        if (strpos(get_site_url(), $marker) !== false) {
            return true;
        }
    }
    return false;
}


/**
 * Changer le logo sur la page connexion
 */
add_action( 'login_enqueue_scripts', 'woo_login_style' );
function woo_login_style() { ?>
    <style type="text/css">
        #login h1 a, .login h1 a {width:300px;background-size:300px;background-image:url('https://www.armoireplus.fr/wp-content/uploads/2020/02/Logo-ArmoirePlus_small.png');}
    </style>
<?php }

/**
 * Retirer attributs dans le nom des variations
 * FILE class-wc-product-variation-data-store-cpt protected function generate_product_title  -- $should_include_attributes = count( $attributes ) < 1; (au lieu de 3)
 */
add_filter( 'woocommerce_product_variation_title_include_attributes', 'custom_product_variation_title', 10, 2 );
function custom_product_variation_title( $should_include_attributes, $product ){
    $should_include_attributes = false;
    return $should_include_attributes;
}

/******
 * YITH
 *****/

/**
 * YITH QUOTE PDF : desactive cache : If after changed some element in the Order Request and updated the order, you create again the PDF and then View it and no changes have been applied, add this code
 */
add_filter( 'ywraq_pdf_file_url','ywraq_avoid_pdf_cache' );
function ywraq_avoid_pdf_cache( $fileurl ) {
    return $fileurl . '?' . rand();
}

 /**
 * YITH QUOTE BUTTON : show after submit
 */
if( class_exists('YITH_YWRAQ_Frontend') ) {
    remove_action('woocommerce_review_order_before_submit', array( YITH_YWRAQ_Frontend(), 'show_button_on_checkout' ));
    add_action('woocommerce_review_order_after_submit', array( YITH_YWRAQ_Frontend(), 'show_button_on_checkout' ));
}

 /**
 * YITH ADD ON PRODUCT -  -  disable the gallery variations of Yith product add on
 */
add_filter( 'yith_wccl_enable_handle_variation_gallery', '__return_false', 99 );

 /**
 * YITH ADD ON PRODUCT -  -   hide the variation price if it has add-ons displayed in the page:
 */
if ( ! function_exists( 'yith_wapo_custom_hide_variation_price' ) ) {
	function yith_wapo_custom_hide_variation_price() {
		$js = "
		jQuery ( function ( $ ) {
			function yith_wapo_hide_variation_price (){
				if ( $( '.yith-wapo-container .yith-wapo-addon' ).length ) {
					$( '.cart.variations_form .woocommerce-variation-price' ).remove();
				}
			}
			yith_wapo_hide_variation_price();
			$( document ).on( 'yith-wapo-after-reload-addons', yith_wapo_hide_variation_price );
		} );
		";
		wp_add_inline_script( 'yith_wapo_front', $js );
	}
	add_action( 'wp_enqueue_scripts', 'yith_wapo_custom_hide_variation_price', 99 );
}
 
 
  /**
 * YITH ADD ON PRODUCT -  -  Affiche SKU dans le nom du addon:
 */

// Ajouter ce code dans le fichier functions.php de votre thème enfant

// Filtrer pour ajouter le SKU, la quantité, le prix et les attributs à l'affichage des add-ons dans le panier
add_filter('yith_wapo_get_addon_value_on_cart', 'add_details_to_addon_value_on_cart', 10, 6);
function add_details_to_addon_value_on_cart($value, $addon_id, $option_id, $key, $original_value, $cart_item) {
    if (!is_cart() && !is_checkout()) {
        return $value;
    }
    $info = yith_wapo_get_option_info($addon_id, $option_id);
    $addon_type = $info['addon_type'] ?? '';

    if ('product' === $addon_type) {
        $option_product_info = explode('-', $original_value);
        $option_product_id = isset($option_product_info[1]) ? $option_product_info[1] : '';
        $option_product = wc_get_product($option_product_id);

        if ($option_product instanceof WC_Product) {
            $product_name = apply_filters('yith_wapo_product_name_in_cart', $option_product->get_name(), $option_product);
            $product_sku = $option_product->get_sku();
            $option_product_qty = isset($cart_item['yith_wapo_qty_options'][$key]) ? $cart_item['yith_wapo_qty_options'][$key] : 1;
            $attribute_summary = $option_product->get_attribute_summary();

            $value = $option_product_qty . ' x ' . $product_name ;
            if (!empty($attribute_summary)) {
                $value .= ', ' . $attribute_summary;
            }
            if (!empty($product_sku)) {
                $value .= ' [réf.' . $product_sku . ']';
            }
        }
    }
    return $value;
}

 /**
 * Créer un rapport CSP
 */
// Pour créer un endpoint personnalisé dans WordPress
function custom_rewrite_rule() {
    add_rewrite_rule('^csp-reports/?', 'index.php?csp_report=true', 'top');
}
add_action('init', 'custom_rewrite_rule', 10, 0);
function add_custom_query_var( $vars ){
  $vars[] = "csp_report";
  return $vars;
}
add_filter( 'query_vars', 'add_custom_query_var' );
// Gérer la réception et l'enregistrement du rapport:
function handle_csp_report() {
    if (get_query_var('csp_report')) {
        $data = json_decode(file_get_contents('php://input'), true);
        // Enregistrez le rapport dans un fichier ou dans la base de données
        $logfile = "/var/www/vhosts/armoireplus.fr/logs/log_csp.txt";
        file_put_contents($logfile, print_r($data, true)."\n", FILE_APPEND);
        exit;
    }
}
add_action('template_redirect', 'handle_csp_report');