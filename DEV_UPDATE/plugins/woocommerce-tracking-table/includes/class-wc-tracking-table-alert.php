<?php

class WC_TrackingTableAlert {

    /**
     * Single instance of WC_TrackingTableAlert
     * @var 	object
     * @access  private
     */
    private static $_instance = null;

    /**
     * Alert type
     * @var 	string
     * @access  private
     */
    private $alert;

    /**
     * Preview mode
     * @var 	string
     * @access  private
     */
    private $preview = false;

    /**
     * Order id
     * @var 	string
     * @access  private
     */
    private $order_id;

     /**
     * Order id
     * @var 	string
     * @access  private
     */
    private $avis_avant = false;


    /**
     * Email copy
     * @var 	string
     * @access  private
     */
    private $email = 'suivi@armoireplus.fr';

	public function __construct() {
	    $this->alert = $_REQUEST['alert'] ?? null;
	    $this->preview = $_REQUEST['preview'] ?? false;
	    $this->order_id = $_REQUEST['order_id'] ?? null;
	}

	public function send() {
        $object = new stdClass();
        $object->id = $this->order_id;
        $object->alert = $this->alert;
	    if ( is_null($this->order_id) || is_null($this->alert) ) {
            $object->message = 'Les paramètres envoyés via AJAX sont incorrects';
            wp_send_json_error($object);
            die();
        }
	    if ( $this->preview ) {
            $object->message = strip_tags($this->send_delay());
            wp_send_json_success($object);
        } else {
	        if ( $this->alert == 'delay' ) {
                $count = get_post_meta( $this->order_id, WC_TrackingTable::$meta_delay, true ) ?: 0;
                update_post_meta( $this->order_id, WC_TrackingTable::$meta_delay, intval($count) + 1 );
                $object->message = '';
                wp_send_json_success($object);
            } else if ( $this->alert == 'review' ) {
                $url_review = $this->getUrlReview();
                if ( !is_null($url_review) ) {
                    $count = get_post_meta( $this->order_id, WC_TrackingTable::$meta_review, true ) ?: 0;
                    update_post_meta( $this->order_id, WC_TrackingTable::$meta_review, intval($count) + 1 );
                    $object->url = $url_review;
                    wp_send_json_success($object);
                }
            } else {
                $object->message = 'Un problème est survenu lors de l\'envoi du mail';
                wp_send_json_error($object);
            }
        }
        die();
    }

    private function send_delay() {
        $order = wc_get_order($this->order_id);
        $to = $this->email;
        $subject = 'Signalement d\'un retard sur la commande ' . $order->get_order_number() . ' sur armoireplus.fr';
        $headers = 'From: Contact <contact@armoireplus.fr>' . "\r\n";
        //$headers .= !empty($this->email_cc) ? 'Cc: ' . $this->email_cc . "\r\n" : '';
        $products_message = $date_message = '';
        // vendor products return
        $vendor_comment = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_comment, true );
        if ( !empty($vendor_comment) ) {
            preg_match_all('/(\w{1,}\d+\w*)-?/', $vendor_comment, $matches);
            $products_in_delay = [];
            if ( sizeof($matches[0]) > 0 ) {
                $items = $order->get_items();
                foreach ($items as $item) {
                    $product = $item->get_product();
                    foreach ($matches[0] as $match) {
                        if (strpos($product->get_description(), $match) !== false) {
                            $products_in_delay[] = $product->get_name();
                            break;
                        }
                    }
                }
                if ( sizeof($products_in_delay) > 0 ) {
                    $s = sizeof($products_in_delay) > 1 ? 's' : '';
                    $products_message .= '<p><b>Produit'.$s.' concerné'.$s.' par le retard :</b><ul>';
                    foreach ($products_in_delay as $value) {
                        $products_message .= $this->preview ? PHP_EOL . "\t - " : '';
                        $products_message .= '<li>' . $value . '</li>';
                    }
                    $products_message .= '</ul></p>';
                }
            }
        }
        // vendor delay return
        $vendor_date = get_post_meta( $order->get_id(), WC_TrackingTable::$meta_date, true );
        if ( !empty($vendor_date) ) {
            $vendor_date = new DateTime(str_replace('/', '-', $vendor_date));
            require_once WCTT_PATH . 'includes/class-wc-tracking-table-order.php';
            $date_shipment = WC_TrackingTableOrder::check_date_shipment($order);
            if ( is_array($date_shipment) ) {
                $expedition = new DateTime(str_replace('/', '-', $date_shipment['date_exp']));
                $interval = $expedition->diff($vendor_date);
                if ( $interval->format('%R%a') > 0 ) {
                    $livraison = WC_TrackingTableOrder::check_date_delivery($order);
                    $date_min = new DateTime(str_replace('/', '-', $livraison['date_min']));
                    $date_max = new DateTime(str_replace('/', '-', $livraison['date_max']));
                    $date_min->add($interval);
                    $date_max->add($interval);
                    $date_message = '<p><b style="color:#ad0d0d;">Nouveau prévisionnel de livraison entre le ' . $date_min->format('d/m/Y') . ' et le ' . $date_max->format('d/m/Y') . '.</b></p>';
                }
            }
        }
        $message = '<div style="font-family:Arial,Helvetica,sans-serif;">
        <p>Bonjour,</p>
        <p>Nous sommes désolés de devoir vous annoncer un retard sur la commande ' . $order->get_order_number() . ' sur armoireplus.fr</p>
        ' . $products_message . '
        ' . $date_message . '
        <p>Notre service commercial reste à votre disposition pour toute question relative à votre commande.</p>
        <p style="margin-bottom:0;">Cordialement, l\'équipe Armoire PLUS</p>
        <img src="https://www.armoireplus.fr/wp-content/themes/legenda-child/img/logo_ArmoirePlus-sign.png" width="150" style="margin-bottom:5px;"><br>
        <small style="font-size:.9em;">Tél. : 05.31.61.98.32 / Fax : 05.17.47.54.02<br>
        <a href="mailto:contact@armoireplus.fr">contact@armoireplus.fr</a> - Site web <a href="www.armoireplus.fr">www.armoireplus.fr</a></small>
        </div>';
        if ( !$this->preview ) {
            add_filter( 'wp_mail_content_type', function() { return "text/html"; } );
            return wp_mail( $to, $subject, $message, $headers );
        } else {
            return $message;
        }
    }

    public function send_review_by_cron($order_id) {
        if ( is_null($order_id) ) {
            return false;
        }
        $this->order_id = $order_id;
        if ( $this->send_review() ) {
            update_post_meta( $this->order_id, WC_TrackingTable::$meta_review, 1 );
            return true;
        }
        return false;
    }

    private function send_review() {
	    $order = wc_get_order($this->order_id);
        $this->avis_avant = $this->order_id;
        return $order;
       /* $to = $order->get_billing_email();
        // $subject = 'Votre avis à propos de la commande ' . $order->get_order_number() . ' sur armoireplus.fr';
        $subject = '[Armoire PLUS] Tout est en Ordre ? Votre avis compte !';
        $headers = 'From: Contact <contact@armoireplus.fr>' . "\r\n";
        $headers .= !empty($this->email_cc) ? 'Cc: ' . $this->email_cc . "\r\n" : '';

        $url_mailto = 'mailto:service-client@armoireplus.fr?subject=Re:%20[Armoire%20PLUS]%20Tout%20est%20en%20ordre%20?&amp;body=Bonjour,%0D%0A%0D%0AJe%20souhaite%20vous%20faire%20part%20du%20problème%20que%20j\'ai%20rencontré%20:%0D%0A%0D%0A%0D%0A%0D%0A%0D%0ACordialement,%0D%0A';
        $url_review = $this->getUrlReview();

        $template = file_get_contents(WCTT_TEMPLATES . DSP . 'email-review.html');
        $research = ['{{ order_id }}', '{{ url_mailto }}', '{{ url_review }}'];
        $template = str_replace($research, [$order->get_order_number(), $url_mailto, $url_review], $template);
        preg_match('/<body.*?>(.*)<\/body>/s', $template, $message);

        add_filter( 'wp_mail_content_type', function() { return "text/html"; } );
        return wp_mail( $to, $subject, $message[1], $headers );*/
    }

    public function get_avis_avant() {
        return $this->avis_avant;
    }

    private function getToken() {
        $endpoint = 'https://login.etrusted.com/oauth/token';
        $curl = curl_init();
        $options = [
            'client_id' => '024acd7df38d--armoireplus2021review',
            'client_secret' => '3091c41a-31e5-456f-9b41-7b4b079f4b2c',
            'grant_type' => 'client_credentials',
            'audience' => 'https://api.etrusted.com'
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query($options),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if ( isset($response->access_token) ) {
            return $response->access_token;
        } else {
            $object = new stdClass();
            $object->message = 'Un problème est survenu lors de l\'authentification avec l\'api trustedshops';
            wp_send_json_error($object);
        }
    }

    private function getUrlReview() {
        $token = $this->getToken();
        $order = wc_get_order($this->order_id);
        $endpoint = 'https://api.etrusted.com/questionnaire-links';

        $products = '[';
        foreach ( $order->get_items() as $item ) {
            if ( $item->get_variation_id() != 0 ) {
                // Variation produit
                $parent_id = $item->get_product_id();
                $parent = wc_get_product($parent_id);
                $product_id = $item->get_variation_id();
                $product = wc_get_product($product_id);
                $sku = $parent->get_sku();
                $img = wp_get_attachment_url($parent->get_image_id());
                $url = get_permalink($parent->get_id());
            } else {
                // Produit simple
                $product_id = $item->get_product_id();
                $product = wc_get_product($product_id);
                $sku = $product->get_sku();
                $img = wp_get_attachment_url($product->get_image_id());
                $url = get_permalink($product->get_id());
            }
            $gpf = maybe_unserialize(get_post_meta($product_id, '_woocommerce_gpf_data', true));
            $mpn = $gpf['mpn'] ?? '';
            $gtin = $gpf['gtin'] ?? '';
            $brand = $gpf['brand'] ?? '';
            $products .= '{
                "sku": "'.$sku.'",
                "name": "'.$product->get_name().'",
                "mpn": "'.$mpn.'",
                "gtin": "'.$gtin.'",
                "brand": "'.$brand.'",
                "imageUrl": "'.$img.'",
                "url": "'.$url.'"
            },';
        }
        $products = substr($products, 0, -1) . ']';

        $body = json_decode('{
        "type": "after-sales",
        "questionnaireTemplate": {
            "id": "tpl-qst-aabd1dee-2676-4303-995a-1e6762a78b44_fr-FR"
        },
        "system": "woocommerce",
        "systemVersion": "6.3",
        "channel": {
            "id": "chl-6a46466a-a770-4824-9c2c-73edf12d4b1a"
        },
        "transaction": {
            "reference": "'.$order->get_order_number().'",
            "date": "'.$order->get_date_paid().'"
        },
        "customer": {
            "firstname": "'.$order->get_billing_first_name().'",
            "lastname": "'.$order->get_billing_last_name().'",
            "email": "'.$order->get_billing_email().'"
        },
        "products": '.$products.'
        }');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: text/plain',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = json_decode(curl_exec($curl));
        curl_close($curl);

        if ( isset($response->link) ) {
            return $response->link;
        } else {
            $object = new stdClass();
            $object->message = 'Un problème est survenu lors de la communication avec l\'api trustedshops';
            wp_send_json_error($object);
            return null;
        }
    }

    public function getEmail() {
        return $this->email;
    }

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

}
