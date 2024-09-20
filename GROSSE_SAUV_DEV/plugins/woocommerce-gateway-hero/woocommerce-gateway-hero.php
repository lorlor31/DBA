<?php
/**
 * Plugin Name: WooCommerce Gateway Hero
 * Plugin URI: http://hero.fr/
 * Description: Take credit card payments on your store using Hero.
 * Author: Hero
 * Author URI: https://hero.fr/
 * Text Domain: woocommerce-gateway-hero
 * Domain Path: /languages
 * Version: 1.0.26
 * Requires at least: 4.6
 * Tested up to: 5.8
 * WC requires at least: 3.3
 */

if (!defined('ABSPATH')) {
    exit;
}
register_activation_hook(__FILE__, function () {
    if (!extension_loaded('curl')) {
        die('The php-curl extension required. Please contact your hosting provider for additional help.');
    }
});

if (!function_exists('init_wc_hero')) {
    define('HERO_PLUGIN_FILE', __FILE__);
    function woocommerce_hero_missing_wc_notice()
    {
        echo '<div class="error"><p><strong>' . sprintf(__('Hero requires WooCommerce to be installed and active. You can download %s here.', 'woocommerce-gateway-stripe'), '<a href="https://woocommerce.com/" target="_blank">WooCommerce</a>') . '</strong></p></div>';
    }

    function init_wc_hero()
    {
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', 'woocommerce_hero_missing_wc_notice');
            return;
        }
        if (!class_exists('WC_Payment_Gateway')) return;

        class WC_Gateway_Hero extends WC_Payment_Gateway
        {
            /**
             * Whether or not logging is enabled
             *
             * @var bool
             */
            public static $log_enabled = false;
            /**
             * Logger instance
             *
             * @var WC_Logger
             */
            public static $log = false;
            
            const HEROURL = 'https://api.hero.fr/api/graphql';
            const HEROURL_STAGING = 'https://api.hero.fr/api-staging/graphql';
            protected $_paymentOptions;
            private static $settings_url_params = [
                'page' => 'wc-settings',
                'tab' => 'checkout',
                'section' => 'hero',
            ];

            public function __construct()
            {
                $this->id = 'hero';
                $this->order_button_text = __('Payer', 'woocommerce-gateway-hero');
                $this->method_title = __('Hero', 'woocommerce-gateway-hero');
                $this->method_description = __('Hero - Plugin de Paiement B2B', 'woocommerce-gateway-hero');
                $this->supports = array();
                $this->has_fields = false;
                $this->plugin_title = __('Hero - Paiement B2B', 'woocommerce-gateway-hero');
                $this->plugin_description = __('Acceptez le paiement en plusieurs fois ou à 30 jours avec Hero', 'woocommerce-gateway-hero');

                // Load the settings.
                $this->init_form_fields();
                $this->init_settings();

                $this->staging_active = $this->get_option('staging_active');
                self::$log_enabled    = 'yes' === $this->get_option( 'log_enabled', 'no' );

                // Define user set variables.
                $this->title = __('Hero', 'woocommerce-gateway-hero');
                $this->description = __('Hero - Plugin de Paiement B2B', 'woocommerce-gateway-hero');

                add_filter('plugin_action_links_' . plugin_basename(HERO_PLUGIN_FILE), [__CLASS__, 'add_plugin_links']);
                add_filter('wc_get_template', [$this, 'wc_get_template'], 10, 3);
                add_action('woocommerce_payment_gateways', array($this, 'register_gateway'));
                add_action('woocommerce_update_options_payment_gateways_hero', array($this, 'process_admin_options'));
                $types = ['Pay1X', 'Pay3X','Pay4X','Pay15D','Pay30D','Pay45D','Pay60D'];
                foreach ($types as $type){
                    add_action('woocommerce_receipt_hero'. $type, array($this, 'show_payment_info'));
                    add_action('woocommerce_thankyou_hero'. $type, array($this, 'show_payment_info'));
                }
                add_filter('woocommerce_save_settings_checkout_hero', [$this, 'settings_save']);
                add_filter('option_woocommerce_gateway_order', [$this, 'woocommerce_gateway_order'],10,1);
                add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
                add_action('woocommerce_api_heropayments', array($this, 'gatewayBack'));

            }

            /**
             * Initialise Gateway Settings Form Fields.
             */
            public function init_form_fields()
            {
                $this->form_fields = array(
                    'enabled' => array(
                        'title' => __('Plugin activé', 'woocommerce-gateway-hero'),
                        'label' => __('Activer les paiements via Hero', 'woocommerce-gateway-hero'),
                        'type' => 'checkbox',
                        'description' => '',
                        'default' => 'no',
                    ),
                    'staging_active' => array(
                        'title' => __('Tests', 'woocommerce-gateway-hero'),
                        'label' => __("Utiliser l'environnement de test (staging)", 'woocommerce-gateway-hero'),
                        'type' => 'checkbox',
                        'description' => '',
                        'default' => 'no',
                    ),
                    'api_key' => array(
                        'title' => __('Clef API', 'woocommerce-gateway-hero'),
                        'type' => 'text',
                        'description' => __('Entrez votre clef d\'API Hero, disponible sur votre dashboard Hero (<a href="https://dashboard.hero.fr/overview" target="_blank">https://dashboard.hero.fr/overview</a>)', 'woocommerce-gateway-hero'),
                        //'desc_tip' => true,
                    ),
                    'staging_api_key' => array(
                        'title' => __('Clef API de test (staging)', 'woocommerce-gateway-hero'),
                        'type' => 'text',
                        'description' => __('Entrez votre clef d\'API Hero de test (staging), disponible sur votre dashboard Hero (<a href="https://staging.dashboard.hero.fr/signup" target="_blank">https://staging.dashboard.hero.fr/signup</a>)', 'woocommerce-gateway-hero'),
                        //'desc_tip' => true,
                    ),
                    'log_enabled' => array(
                        'title' => __('Logs', 'woocommerce-gateway-hero'),
                        'type' => 'checkbox',
                        'label' => 'Enregistrer les informations dans le journal de WooCommerce',
                        'default' => 'no',
                    ),
                );
            }

            /**
             * Process the payment and return the result.
             * @param int $order_id
             * @return array
             */
            public function process_payment($order_id)
            {
                try {
                    $order = wc_get_order($order_id);
                    $types = $this->get_payment_types(null,$order->get_total()*100);
                    if (!is_array($types) || count($types) < 1) {
                        throw new Exception('Ce paiement n\'est pas disponible');
                    }
                    $heroId = $this->createHeroPayment($order);
                    if ($heroId) {
                        $order->update_meta_data('hero_id', $heroId);
                        $type = 'Pay3X';
                        if ($order->get_payment_method()) {
                            $type = $order->get_payment_method();
                            if (substr($type, 0, 4) == 'hero') {
                                $type = substr($type, 4);
                            }
                        }
                        $order->update_meta_data('hero_type', $type);
                        $order->save();

                        $url = $this->getHeroRedirectUrl($heroId);
                        return array(
                            'result' => 'success',
                            'redirect' => $url
                        );
                    }
                    throw new Exception('Réponse incorrecte');
                } catch (\Exception $e){
$this->log('erreur sur function process_payment()');
$this->log($e);
                    $message = $e->getMessage();
                    wc_add_notice($message,'error');
                }
            }

            /**
             * @param WC_Order $order
             *
             */
            public function createHeroPayment($order)
            {
$this->log('function createHeroPayment()');
                //$redirectUri = $order->get_checkout_payment_url(true);
                $amount = $order->get_total() * 100;
                $order_id = $order->get_id();
                $hashData = ['orderId' => $order_id,'amount'=>$amount];
                $hashDataJson = json_encode($hashData);
                $hash = base64_encode($hashDataJson);
                $redirectUri = add_query_arg(['wc-api'=> 'heropayments','hash'=>$hash], home_url('/'));
                $type = 'Pay3X';
                if ($order->get_payment_method()) {
                    $type = $order->get_payment_method();
                    if (substr($type, 0, 4) == 'hero') {
                        $type = substr($type, 4);
                    }
                }
                $address1 = $order->get_billing_address_1();
                $address2 = $order->get_billing_address_2();
                $city = $order->get_billing_city();
                $postcode = $order->get_billing_postcode();
                $firstname = $order->get_billing_first_name( 'edit' );
                $lastname = $order->get_billing_last_name( 'edit' );
                $email = $order->get_billing_email();
                $phone = $order->get_billing_phone();
                $vatNumber = '';
                $siret = '';
                $customer_id = $order->get_customer_id();
                $data = ['query' => <<<EOT
mutation {
  createPayment(
    payment: {
      amount: {$amount}
      redirectUri: "{$redirectUri}"
      type: {$type}
      issuer: {
        address: { line1: "{$address1}", line2: "{$address2}", city: "{$city}", zipCode: "{$postcode}" }
        name: "{$firstname} {$lastname}"
        siret: "{$siret}"
        vatNumber: "{$vatNumber}"
        contactEmail: "{$email}"
        contactPhone: "{$phone}"
        customerReference: "{$customer_id}"
      }
    }
  ) {
    id
  }
}
EOT
                ];
$this->log("Les données envoyées pour la commande $order_id sont :");
$this->log($data);
                $outputJson = $this->heroRequest($data);
                $output = json_decode($outputJson, true);
$this->log('La réponse est :');
$this->log($output);
                if (is_array($output) && isset($output['data']) && isset($output['data']['createPayment']) && isset($output['data']['createPayment']['id'])) {
                    $id = $output['data']['createPayment']['id'];

                    // setOrderId
                    $setOrderId_data = [
                        'query' => "
                            mutation {
                                setOrderId(paymentId: \"$id\", orderId: \"$order_id\")
                            }"
                    ];
                    $this->heroRequest($setOrderId_data);

                    return $id;
                } else {
                    $logger = wc_get_logger();
                    $context = array( 'source' => 'woocommerce-gateway-hero' );
                    $logger->info( 'Response: '.$outputJson, $context );
                }
            }


            /**
             * Hooks into the checkout page to display Hero-related payment info.
             */
            public function show_payment_info($order_id){
                $order = wc_get_order($order_id);
                if (!$order->needs_payment()){
                    require __DIR__ . '/templates/completed.php';
                }
            }
            public function gatewayBack()
            {
$this->log('function gatewayBack()');
                global $wp;
                try {
                    $hash = $_GET['hash'];
                    $hashJson = base64_decode($hash);
                    $hashData = json_decode($hashJson,true);
$this->log('Les données du hash sont :');
$this->log($hashData);
                    $order_id = sanitize_text_field($hashData['orderId']);
                    $amount = (int) round(sanitize_text_field($hashData['amount']));
                    $order = wc_get_order($order_id);
                    if (!$order->needs_payment()) {
$this->log('!$order->needs_payment()');
                        // thankyou page requested, but order is still unpaid
                        wp_redirect($order->get_checkout_order_received_url(true));
                        exit;
                    }
                    $heroId = $order->get_meta('hero_id');
$this->log('$heroId = ' . $heroId);
                    if ($heroId) {
                        $orderAmount = (int) round($order->get_total() * 100);
                        if ( $amount !== $orderAmount ){
$this->log('Les montants sont différents');
$this->log('$amount = ' . $amount . ' - $orderAmount = ' . $orderAmount);
                            $this->_noticeHero($heroId, 'ORDER_AMOUNT_INCONSISTENT');
                            $message = 'Erreur de paiement. Montant de la commande incorrect. Veuillez contacter support@hero.fr. ';
                            throw new Exception($message);
                        }
                        $data = ['query' => "
                        query {
                            paymentStatus(
                                id: \"{$heroId}\"
                            ) {
                                isSuccess,
                                error
                            }
                        }
                        "
                        ];
$this->log('La requête paymentStatus est :');
$this->log($data);
                        $outputJson = $this->heroRequest($data);
                        $output = json_decode($outputJson, true);
$this->log('La réponse paymentStatus est :');
$this->log($output);
                        if (is_array($output) && isset($output['data']) && isset($output['data']['paymentStatus']) && isset($output['data']['paymentStatus']['isSuccess'])) {
                            if ($output['data']['paymentStatus']['isSuccess'] == true) {
                                WC()->cart->empty_cart();
                                $order->payment_complete($heroId);
                            } else {
$this->log('Erreur de paiement.');
                                throw new Exception('Erreur de paiement. Veuillez contacter support@hero.fr');
                            }
                        } else {
$this->log('Réponse incorrecte.');
                            throw new Exception('Réponse incorrecte. Veuillez contacter support@hero.fr');
                        }
                    } else {
$this->log('Order missing hero id - 1');
                        throw new Exception('Order missing hero id');
                    }

                    if ($order->has_status('cancelled')) {
$this->log('$order->has_status(cancelled) -> redirection vers get_checkout_payment_url');
                        // invoice expired, reload page to display expiry message
                        wp_redirect($order->get_checkout_payment_url(true));
                        exit;
                    }

                    if ($order->needs_payment()) {
$this->log('Order missing hero id - 2');
                        throw new Exception('Order missing hero id');
                    } elseif ($order->has_status(array('processing', 'completed'))) {
$this->log('Redirection get_checkout_order_received_url()');
                        $url = $order->get_checkout_order_received_url();
                        wp_redirect($url);
                        exit;
                    }
$this->log('Redirection wc_get_cart_url()');
                    wp_redirect(wc_get_cart_url());
                    exit;
                } catch (\Exception $exception){
$this->log('$exception -> Redirection wc_get_cart_url()');
$this->log($exception);
                    $message = $exception->getMessage();
                    wc_add_notice($message, 'error');
                    wp_redirect(wc_get_cart_url());
                    exit;
                }
            }

            public function is_available()
            {
                $valid = parent::is_available();
                /*if ($valid) {
                    if (!is_admin()) {
                        $orderTotal = $this->get_order_total() * 100;
                        if ($orderTotal) {
                            $types = $this->get_payment_types(null, $orderTotal);

                            if (is_array($types) && count($types) >= 1) {
                                $valid = $valid;
                            }
                        }
                    }
                }*/
                return $valid;
            }

            /**
             * Register as a WooCommerce gateway.
             */
            public function register_gateway($methods)
            {
                if (is_checkout() || is_ajax()) {
$this->log('function register_gateway()');
                    $orderTotal = $this->get_order_total() * 100;
                    if ($orderTotal){
                        $payments = $this->get_payment_types(null,$orderTotal);
$this->log('$payments');
$this->log($payments);
                        // Get max Payment
                        $wp_user_id = get_current_user_id();
                        // $wp_user_id = 'cus1';
                        $gql_data = [
                            'query' => "query {
                                maxPaymentForCustomer(customerReference: \"$wp_user_id\"){
                                    current
                                    maxPay30D
                                    maxPay3X
                                } }"
                        ];
$this->log('$gql_data');
$this->log($gql_data);
                        $gql_response = $this->heroRequest($gql_data);
$this->log('$gql_response');
$this->log($gql_response);
                        $response = json_decode($gql_response, true);
$this->log('$response');
$this->log($response);
                        if ( empty($response['errors'])){
                            $maxPay30D_remaining = (int) $response['data']['maxPaymentForCustomer']['maxPay30D'] - (int) $response['data']['maxPaymentForCustomer']['current'];
                            $maxPay3X_remaining = (int) $response['data']['maxPaymentForCustomer']['maxPay3X'] - (int) $response['data']['maxPaymentForCustomer']['current'];
                        }
                        if (is_array($payments)) {
                            foreach ($payments as $payment) {
$this->log('$payment');
$this->log($payment);
                                // remove unavailable methods by maxPaymentForCustomer
                                if ( ! empty($maxPay30D_remaining) &&
                                ($orderTotal > $maxPay30D_remaining) &&
                                in_array($payment, ['Pay30D','Pay60D'])
                                ) {
$this->log('continue $maxPay30D_remaining');
                                    continue;
                                }
                                if ( ! empty($maxPay3X_remaining) &&
                                ($orderTotal > $maxPay3X_remaining) &&
                                in_array($payment, ['Pay3X','Pay4X'])
                                ) {
$this->log('continue $maxPay3X_remaining');
                                    continue;
                                }


                                $method = clone $this;
                                $method->id .= $payment;
                                $method->code = $payment;
                                $method->method_title = $this->getNameByCode($payment);
                                $methods[] = $method;
                            }
                        }
                    }
                } else {
                    $methods[] = $this;
                }
// $this->log('$methods');
// $this->log($methods);
                return $methods;
            }

            public function get_payment_types($apiKey = null, $amount = 1000)
            {
$this->log('get_payment_types()');
                if (empty($this->_paymentOptions)) {
                    if (is_null($apiKey)) {
                        $apiKey = $this->get_option('api_key');
                    }
                    if (!$apiKey) {
                        return;
                    }
                    $data = ['query' => 'query{ availablePaymentTypes(amount: '.$amount.')}'];
$this->log('get_payment_types() $data');
$this->log($data);
                    $outputJson = $this->heroRequest($data);
                    $output = json_decode($outputJson, true);
$this->log('get_payment_types() $output');
$this->log($output);
                    $payment_options = [];
                    if (is_array($output)) {
                        if (isset($output['data']) && isset($output['data']) && isset($output['data']['availablePaymentTypes'])) {
                            $payments = $output['data']['availablePaymentTypes'];
                            foreach ($payments as $payment) {
                                if (!in_array($payment, ['Pay1X', 'Pay3X','Pay4X','Pay15D','Pay30D','Pay45D','Pay60D'])) {
                                    continue;
                                }
                                $payment_options[] = $payment;
                            }
                        }
                        uasort($payment_options, function ($a, $b) {
                            $method_sequence = ['Pay1X' => 1, 'Pay3X' => 2, 'Pay4X' => 3, 'Pay15D' => 4, 'Pay30D' => 5, 'Pay45D' => 6, 'Pay60D' => 7];
                            return $method_sequence[$a] > $method_sequence[$b];
                        });
                        $this->_paymentOptions = $payment_options;
                    }
                }
                return $this->_paymentOptions;
            }
            public function validateApiKey($apiKey)
            {
                if (!$apiKey) {
                    return false;
                }
                $data = ['query' => 'query{ availablePaymentTypes(amount: 0)}'];
                $outputJson = $this->heroRequest($data,$apiKey);
                $output = json_decode($outputJson, true);

                if (is_array($output)) {
                    if (isset($output['data']) && isset($output['data']) && isset($output['data']['availablePaymentTypes'])) {
                        return true;
                    }
                }
                return false;
            }

            public function payment_fields()
            {
                return '';
            }

            public function getNameByCode($code)
            {
                $titles = [
                    'Pay1X' => 'Paiement comptant',
                    'Pay3X' => 'Payer en 3 fois sans frais',
                    'Pay4X' => 'Payer en 4 fois sans frais',
                    'Pay15D' => 'Payer à 15 jours sans frais',
                    'Pay30D' => 'Payer à 30 jours sans frais',
                    'Pay45D' => 'Payer à 45 jours sans frais',
                    'Pay60D' => 'Payer à 60 jours sans frais',
                ];
                if (isset($titles[$code])) {
                    return $titles[$code];
                }
                return $code;
            }

            /**
             * Adds links to the plugin's row in the "Plugins" Wp-Admin page.
             *
             * @see https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
             * @param array $links The existing list of links that will be rendered.
             * @return array The list of links that will be rendered, after adding some links specific to this plugin.
             */
            public static function add_plugin_links($links)
            {
                $plugin_links = [
                    '<a href="' . esc_attr(WC_Gateway_Hero::get_settings_url()) . '">' . __('Réglages', 'woocommerce-gateway-hero') . '</a>',
                ];

                return array_merge($plugin_links, $links);
            }

            public static function get_settings_url()
            {
                return admin_url(add_query_arg(self::$settings_url_params, 'admin.php'));
            }

            public function settings_save($data)
            {
                if (empty($_REQUEST['woocommerce_hero_enabled'])) {
                    return $data;
                }
                if (empty($_REQUEST['woocommerce_hero_api_key']) && empty($_REQUEST['woocommerce_hero_api_key'])){
                    WC_Admin_Settings::add_error(__('Veuillez indiquer une clé API'));
                    return false;
                }
                if ($this->is_staging_enable()){
                    $apiKey = $_REQUEST['woocommerce_hero_staging_api_key'];
                } else {
                    $apiKey = $_REQUEST['woocommerce_hero_api_key'];
                }
                $valid = $this->validateApiKey($apiKey);
                if ($valid) {
                    //WC_Admin_Settings::add_message(__('Votre clé API est valide'));
                    return true;
                } else {
                    if ($this->is_staging_enable()) {
                        WC_Admin_Settings::add_error(__('Votre clé API de test est invalide'));
                    } else {
                        WC_Admin_Settings::add_error(__('Votre clé API est invalide'));
                    }
                    
                    return false;
                }
                
                return $data;
            }

            public static function wc_get_template($template, $template2 = null, $template3 = null)
            {
                if ($template2 == 'checkout/payment-method.php') {
                    if (is_array($template3) && isset($template3['gateway'])) {
                        $gateway = $template3['gateway'];
                        if (get_class($gateway) != 'WC_Gateway_Hero') {
                            return $template;
                        }
                        $template = plugin_dir_path(__FILE__) . 'templates/' . $template2;
                        return $template;
                    }
                }
                return $template;
            }
            public function woocommerce_gateway_order($order){
                $newOrder = [];
                $sort = 0;
                foreach ($order as $method=>$oldSort){
                    if ($method=='hero'){
                        $newOrder['hero'] = $sort++;
                        $newOrder['heroPay1X'] = $sort++;
                        $newOrder['heroPay3X'] = $sort++;
                        $newOrder['heroPay4X'] = $sort++;
                        $newOrder['heroPay15D'] = $sort++;
                        $newOrder['heroPay30D'] = $sort++;
                        $newOrder['heroPay45D'] = $sort++;
                        $newOrder['heroPay60D'] = $sort++;
                    } else {
                        $newOrder[$method] = $sort++;
                    }
                }
                return $newOrder;
            }
            public function payment_scripts(){
                if( ! function_exists('get_plugin_data') ){
                    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }
                $plugin_data = get_plugin_data( __FILE__ );
                $plugin_version = $plugin_data['Version'];
                wp_register_style( 'hero_styles', plugins_url( 'assets/css/hero-styles.css', __FILE__ ), [], $plugin_version );
                wp_enqueue_style( 'hero_styles' );
            }
            public function getHeroUrl(){
                if ($this->is_staging_enable()) {
                    $file = $this::HEROURL_STAGING;
                } else {
                    $file = $this::HEROURL;
                }
                return $file;
            }
            public function is_staging_enable(){
                if (!empty($_REQUEST['woocommerce_hero_enabled']) ) {
                    if (!empty($_REQUEST['woocommerce_hero_staging_active'])){
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    if ( 'yes' === $this->staging_active ) {
                        return true;
                    }
                }
                return false;
            }
            public function getHeroRedirectUrl($id){
                if ($this->is_staging_enable()){
                    $url = 'https://staging.payment.hero.fr/' . $id;
                } else {
                    $url = 'https://payment.hero.fr/' . $id;
                }
                return $url;
            }
            public function heroRequest($data,$apiKey=null){
                if (!$apiKey) {
                    $apiKey = $this->get_option('api_key');
                    if ($this->is_staging_enable()) {
                        $apiKey = $this->get_option('staging_api_key');
                    } else {
                        $apiKey = $this->get_option('api_key');
                    }
                }
                $data_string = json_encode($data);
                $file = $this->getHeroUrl();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $file);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
                curl_setopt($ch, CURLOPT_HEADER, false);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                if( ! function_exists('get_plugin_data') ){
                    require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
                }
                $data = get_plugin_data(HERO_PLUGIN_FILE);
                $wpVersion = get_bloginfo('version');
                curl_setopt(
                    $ch,
                    CURLOPT_HTTPHEADER,
                    [
                        'Authorization:Api-Key ' . $apiKey,
                        'Content-Type:application/json',
                        'Content-Length: ' . strlen($data_string),
                        'x-hero-agent-module-version: '. $data['Version'],
                        'x-hero-agent-platform-type: woocommerce',
                        'x-hero-agent-platform-version: '. $wpVersion
                    ]
                );
                $outputJson = curl_exec($ch);
                curl_close($ch);
                return $outputJson;
            }
            protected function _noticeHero($id, $error_code){
                $data = ['query' => "
                    mutation {
                        moduleLog(
                            log: {
                                paymentId: \"{$id}\",
                                error: \"{$error_code}\"
                            }
                        )
                    }
                    "
                ];

                $outputJson = $this->heroRequest($data);
                $output = json_decode($outputJson, true);
            }

            /**
             * Logging method.
             *
             * @param string $message Log message.
             * @param string $level Optional. Default 'info'. Possible values:
             *                      emergency|alert|critical|error|warning|notice|info|debug.
             */
            public static function log( $message, $level = 'info' ) {
                if ( self::$log_enabled ) {
                    if ( empty( self::$log ) ) {
                        self::$log = wc_get_logger();
                    }
                    self::$log->log( $level, print_r($message, true), array( 'source' => 'Hero' ) );
                }
            }
        }

        new WC_Gateway_Hero();
    }

    add_action('plugins_loaded', 'init_wc_hero');
    load_plugin_textdomain('woocommerce-gateway-hero', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
