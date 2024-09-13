<?php 

if (is_admin()){
    /**
     * Ajoute l'adresse email de livraison aux emails des commandes terminées
     */
    add_filter( 'woocommerce_email_recipient_customer_completed_order', 'delivery_email_recipient_completed_order', 10, 2 );
    function delivery_email_recipient_completed_order( $recipient, $order ) {
        $delivery_email = get_post_meta( $order->get_id(), '_shipping_email', true );
        if ( !empty($delivery_email) && $order->get_billing_email() !== $delivery_email ) {
            $recipient = $recipient . ', ' . $delivery_email;
        }
        return $recipient;
    }

     /**
     * Modifier mail nouvel utilisateur à partir du BO, bienvenue & changer le mot de pass provisoire
     */
    add_filter( 'wp_new_user_notification_email', 'custom_new_user_notification_email', 10, 3 );
    function custom_new_user_notification_email( $wp_new_user_notification_email, $user, $blogname ) {
        $message = "Bonjour,<br><br>";
        if ( isset($user->get_role_caps()['customer']) && $user->get_role_caps()['customer'] === true ) {
            $pass = $_POST['pass1'];
            $url = wc_get_page_permalink( 'myaccount' );
            $message .= "Voici votre accès sur Armoireplus.fr qui vous permettra de consulter vos documents et suivre vos commandes.<br><br>";
            $message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "<br><br>";
            $message .= sprintf( __( 'Password: %s' ), $pass ) . "<br><br>";
            $message .= "Veuillez <b>définir un nouveau mot de passe</b> une fois connecté à votre compte client.<br><br>";
            $message .= __( 'Pour accèder à votre compte, visitez cette adresse :' ) . " <a href='$url'>$url</a><br><br>";
            $message .= "À votre disposition,<br><br>";
            $message .= "<div style='float:left;'>";
            $message .= "<img src='https://www.armoireplus.fr/wp-content/uploads/signature/signature-armoireplus.png' alt='Logo Armoire PLUS' width='198' height='55' style='margin:0 10px 0 0;'>";
            $message .= "</div>";
            $message .= "<div style='float:left;float:left;border-left: 1px solid #dadada;padding-left:15px;margin-top:5px;'>";
            $message .= "<span style='font-size:10pt'><strong style='font-weight:600;'>Jean-Baptiste - <span style='color:#ce1427'>Armoire PLUS</span></strong></span><br>";
            $message .= "<span style='font-size:8pt'>Tel. : 05.31.61.98.32 / Fax : 05.17.47.54.02</span><br>";
            $message .= "<span style='font-size:8pt'>Email : <a href='mailto:contact@armoireplus.fr'>contact@armoireplus.fr</a> / Site : <a href='http://www.armoireplus.fr/'>www.armoireplus.fr</a></span>";
            $message .= "</div>";
            $wp_new_user_notification_email['message'] = $message;
            $wp_new_user_notification_email['subject'] = "[Armoire PLUS] Votre accès sur Armoireplus.fr";
        } else {
            $key = get_password_reset_key( $user );
            if ( is_wp_error( $key ) ) { return $key; }
            $url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
            $message .= "Voici votre accès sur Armoireplus.fr :<br><br>";
            $message .= sprintf( __( 'Username: %s' ), $user->user_login ) . "<br><br>";
            $message .= __( 'To set your password, visit the following address:' ) . " <a href='$url'>$url</a><br><br>";
            $message .= "Bien cordialement,";
            $wp_new_user_notification_email['message'] = $message;
            $wp_new_user_notification_email['subject'] = "[Armoire PLUS] Votre accès sur Armoireplus.fr";
        }
        return $wp_new_user_notification_email;
    }

    /**
     * Modifier email demande de réinitialisation du mdp à partir du BO
     */
    add_filter( 'retrieve_password_message', 'custom_retrieve_password_message', 10, 4 );
    function custom_retrieve_password_message( $message, $key, $user_login, $user_data ) {
        $user = new WP_User($user_data->id);
        $message = "Bonjour,<br><br>";
        $message .= "Quelqu’un a demandé la réinitialisation de votre mot de passe pour le compte suivant :<br><br>";
        $message .= sprintf( __( 'Username: %s' ), $user_login ) . "<br><br>";
        $message .= "Si ceci est une erreur, ignorez cet e-mail et rien ne se passera.<br><br>";
        if ( isset($user->get_role_caps()['customer']) && $user->get_role_caps()['customer'] === true ) {
            $url = wc_lostpassword_url() . "?key=$key&login=" . rawurlencode($user_login);
            $message .= __( 'To reset your password, visit the following address:' ) . " <a href='$url'>$url</a><br><br>";
            $message .= "À votre disposition,<br><br>";
            $message .= "<div style='float:left;'>";
            $message .= "<img src='https://www.armoireplus.fr/wp-content/uploads/signature/signature-armoireplus.png' alt='Logo Armoire PLUS' width='198' height='55' style='margin:0 10px 0 0;'>";
            $message .= "</div>";
            $message .= "<div style='float:left;float:left;border-left: 1px solid #dadada;padding-left:15px;margin-top:5px;'>";
            $message .= "<span style='font-size:10pt'><strong style='font-weight:600;'>Jean-Baptiste - <span style='color:#ce1427'>Armoire PLUS</span></strong></span><br>";
            $message .= "<span style='font-size:8pt'>Tel. : 05.31.61.98.32 / Fax : 05.17.47.54.02</span><br>";
            $message .= "<span style='font-size:8pt'>Email : <a href='mailto:contact@armoireplus.fr'>contact@armoireplus.fr</a> / Site : <a href='http://www.armoireplus.fr/'>www.armoireplus.fr</a></span>";
            $message .= "</div>";
        } else {
            $url = network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
            $message .= __( 'To reset your password, visit the following address:' ) . " <a href='$url'>$url</a><br><br>";
            $message .= "Bien cordialement,";
        }
        return $message;
    }

    /**
     * Ajouter "mandat administratif" dans sujet email nouvelle commnade (admin)
     */
    add_filter( 'woocommerce_email_subject_new_order', 'woocommerce_email_subject_mandat_order', 10, 2 );
    function woocommerce_email_subject_mandat_order( $subject, $order ) {
        foreach ( $order->get_items() as $item_id => $item ) {
        $product_id = $item->get_product_id();
        $taquet = '';
        $Smyley = '';
        $Smyley2 = '';
            if($product_id == '4548')
            {
                $Smyley = '"\ud83e\udd11 "';
                $taquet = ' de ** Taquets ** ';
            }elseif($product_id == '51432'){
                $Smyley = '"\ud83e\udd11 "';
                $taquet = ' de ** Cadenas ** ';
            }
            
        }	
        if ( $order->get_payment_method() == 'cod' ) {
            $paiement ='par ** Mandat Administratif **';
            $Smyley2 .= '"\ud83e\uddd0 "';
        }elseif( $order->get_payment_method() == 'other_payment' ){
            $paiement ='à ** Reception de Facture **';
            $Smyley2 .= '"\ud83e\uddd0 "';
        }
        $subject = __( json_decode($Smyley) . json_decode($Smyley2) .'[Armoire PLUS] Nouvelle commande '.$taquet. $paiement.' (' . $order->get_order_number() . ')' );
        return $subject;
    }
}

/**
 * Envoyer un email au fournisseur si commande annulée
 */
if ( class_exists('WC_Dropshipping_Orders') ) {
	remove_action('woocommerce_order_status_cancelled', array('WC_Dropshipping_Orders', 'order_cancelled'));
}
add_filter('woocommerce_order_status_changed', 'woocommerce_send_mail_cancelled_order', 10, 4 );
function woocommerce_send_mail_cancelled_order(  $order_id, $old_status, $new_status, $order ) {
	if ( ($old_status == 'processing' || $old_status == 'failed') && ( $new_status === 'cancelled' || $new_status === 'refunded' ) ) {
		$order = wc_get_order( $order_id );
		$num_order = get_post_meta( $order->get_id(), '_ywson_custom_number_order_complete', true );
		$items = $order->get_items();
		$is_supplier = false;
		if ( count( $items ) > 0 ) {
			foreach( $items as $item_id => $item ) {
				$sup_name = get_post_meta( $item['product_id'], 'supplier', true );
				if ( $sup_name != "" || !empty($sup_name) || !is_null($sup_name) ) {
					$sup_id = get_post_meta( $item['product_id'], 'supplierid', true );
					$supplier = get_term_meta( $sup_id, 'meta', true );
					$is_supplier = true;
					break;
				}
			}
		}
		if ( $is_supplier ) {
			$headers[] = 'From: Armoire Plus <contact@armoireplus.fr>';
			$headers[] = 'Content-Type: text/html; charset=UTF-8';
			$headers[] = 'Cc: Armoire Plus <contact@armoireplus.fr>';
			$mailer = WC()->mailer();
			$title = 'Annulation de la commande ' . $num_order;
			$message = 'Bonjour,<br><br>
		Nous tenons à vous informer que la commande <b>' . $num_order . '</b> est annulée.<br><br>
		<b>Merci de nous confirmer la bonne prise en compte de cette demande</b> et nous nous excusons pour le désagrément occasionné.<br><br>
		Bien cordialement,<br><br>
		L\'équipe Armoire PLUS';
			$wrapped_message = $mailer->wrap_message($title, $message);
			$wc_email = new WC_Email;
			$html_message = $wc_email->style_inline($wrapped_message);
			wp_mail( $supplier['order_email_addresses'], $title, $html_message, $headers );
		}
	}
}

/**
 * Ajouter des PJ dans les emails selon le statut
 */
add_filter( 'woocommerce_email_attachments', 'wc_emails_add_attachments', 10, 3 );
function wc_emails_add_attachments( $attachments, $status, $order ) {
    if ( ! is_a( $order, 'WC_Order' ) || ! isset( $status ) ) {
        return $attachments;
    }
    switch ( $status ) {
        case 'ywraq_send_quote':
            $attachments[] = wp_upload_dir()['basedir'] . '/2022/09/DBA-RIB-BNP-DBA.pdf';
            include 'proforma.php';
            $attachments[] = generate_pdf_proforma($order->get_id());
            break;
        case 'customer_on_hold_order':
            if ($order->get_payment_method() == 'bacs') {
                $attachments[] = wp_upload_dir()['basedir'] . '/2022/09/DBA-RIB-BNP-DBA.pdf';
            }
            break;
        case 'customer_processing_order':
            if ($order->get_payment_method() == 'cod') {
                $attachments[] = wp_upload_dir()['basedir'] . '/2022/09/DBA-RIB-BNP-DBA.pdf';
            }
            break;
    } 
    return $attachments;
}

/**
 * Envoi email nouvelle commande ou qd on transforme un devis en commande
 */
add_filter( 'woocommerce_order_status_changed', 'woocommerce_send_mail_new_order_from_quote', 30, 4 );
function woocommerce_send_mail_new_order_from_quote(  $order_id, $old_status, $new_status, $order ) {
    if (  $new_status == 'on-hold' || $new_status === 'processing' ) {
        class WC_Quote_New_Order extends WC_Email {
            public function __construct($order) {
                $this->id             = 'admin_quote_new_order';
                $this->customer_email = true;
                $this->title          = __( 'New order', 'woocommerce' );
                $this->subject        = __( '[{site_title}]: New order #{order_number}', 'woocommerce' );
                $this->template_base  = get_stylesheet_directory_uri() . '/';
                $this->template_html  = 'emails/admin-quote-new-order.php';
                $this->template_plain = 'emails/plain/admin-quote-new-order.php';
                $this->placeholders   = array(
                    '{order_date}'   => wc_format_datetime( $order->get_date_created() ),
                    '{order_number}' => $order->get_order_number()
                );
                $this->recipient      = get_option( 'admin_email' );
                $this->enabled        = true;
                $this->order          = $order;
                parent::__construct();
            }
            public function get_content_html() {
                ob_start();
                wc_get_template(
                    $this->template_html,
                    array(
                        'order'             => $this->order,
                        'email_heading'     => $this->get_heading(),
                        'email_description' => $this->format_string( $this->get_option( 'email-description' ) ),
                        'sent_to_admin'     => true,
                        'plain_text'        => false,
                        'email'             => $this,
                    ),
                    '',
                    $this->template_base
                );
                return ob_get_clean();
            }
            public function get_content_plain() {
                ob_start();
                wc_get_template(
                    $this->template_plain,
                    array(
                        'order'             => $this->order,
                        'email_heading'     => $this->format_string( $this->get_heading() ),
                        'email_description' => $this->format_string( $this->get_option( 'email-description' ) ),
                        'sent_to_admin'     => true,
                        'plain_text'        => false,
                        'email'             => $this,
                    ),
                    false,
                    $this->template_base
                );
                $content = ob_get_clean();
                return wordwrap( preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $content ) ), 70 );
            }
            public function trigger() {
                $this->setup_locale();
                $headers = 'Reply-to: ' . $this->recipient . "\r\n";
                $this->send( $this->recipient, $this->subject, $this->get_content(), $headers, $this->get_attachments() );
                $this->restore_locale();
            }
            public function edit_subject() {
                $supplier_list = array();
                foreach ( $this->order->get_items() as $item_id => $item ) {
				$product_id = $item->get_product_id();
				$taquet = '';
				$Smyley = '';
				$Smyley2 = '';
                $Smyley3= '';
                $Fournisseur = '';
					if($product_id == '4548')
					{
						// $subject = __( json_decode('"\ud83e\udd11 "') .'[Armoire PLUS] Nouvelle commande de *** Taquets *** (' . $order->get_order_number() . ') - ' . date("d/m/Y") );
						$Smyley = '"\ud83e\udd11 "';
						$taquet = ' de ** Taquets ** ';
					}elseif($product_id == '51432'){
						$Smyley = '"\ud83e\udd11 "';
						$taquet = ' de ** Cadenas ** ';
					}
                $product_id = $item->get_product_id();
                $product = wc_get_product( $product_id );
                if (get_post_type($product_id) == 'product_variation') {
                    $parent_id = wp_get_post_parent_id($product_id);
                    $parent_product = wc_get_product($parent_id);
                    $supplier = $parent_product->get_meta('supplier');
                } else {
                    $product = wc_get_product($product_id);
                    $supplier = $product->get_meta('supplier');
                }
                if (!in_array($supplier, $supplier_list)) {
                    $supplier_list[] = $supplier;
                }
            }
            if ( $this->order->get_payment_method() == 'cod' ) {
                $paiement ='par ** Mandat Administratif **';
                $Smyley2 .= '"\ud83e\uddd0 "';
            }elseif( $this->order->get_payment_method() == 'other_payment' ){
                $paiement ='à ** Reception de Facture **';
                $Smyley2 .= '"\ud83e\uddd0 "';
            }
            if ( count($supplier_list) >= 2 ) {
                $Fournisseur = 'Plusieurs fournisseurs';
                $Smyley3='"\uD83D\uDE9A"';
            } else if ( count($supplier_list) == 1 && $supplier_list[0]  != 'Vinco' ) {
                $Fournisseur = 'Fournisseurs : ' . $supplier_list[0]  . '' ;
                $Smyley3='"\uD83D\uDE9A"';
            }
				$this->subject = __( json_decode($Smyley) . json_decode($Smyley2) .'[Armoire PLUS] Nouvelle commande ' . $taquet . $paiement . json_decode($Smyley3) . $Fournisseur . ' (' . $this->order->get_order_number() . ')' );
            }
        }
        $new_order = new WC_Quote_New_Order($order);
		$new_order->edit_subject();
        $new_order->trigger();
    }
}