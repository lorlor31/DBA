<?php
Class Livraison {

    // Init instance
    private static $_instance = null;

    // Cache variable
    private static $cache = [];

    // Init variable
    private static $formule = []; // Formule livraison
    private static $supplier = []; // Liaison formule / supplier
    private static $decalage = []; // Décalage fabrication / livraison
    private static $stock = []; // Force nouveau délai fabrication par sku
    private static $attribute = []; // Décalage délai fabrication par attribut
    private static $postcode = []; // Décalage délai livraison par code postal

    // Constructor
    function __construct() {
        self::$formule = [
            'Formule CLÉ EN MAIN'   => 'cle_en_main',
            'Formule CLÉ EN MAIN HARTMANN'   => 'cle_en_main_hartmann',
            'Formule Messagerie HARTMANN'   => 'messagerie_hartmann',
            'Formule ECO [OFFERTE]' => 'eco_offerte',
            'Formule ECO Plus'      => 'eco_plus',
            'Formule ECO'      => 'eco',
        ];
        self::$decalage = [
            'fabrication'   => 0, // base 1
            'cle_en_main'   => 1, // base 1
            'cle_en_main_hartmann'   => 0, // base 0
            'messagerie_hartmann'   => 0, // base 0
            'eco_offerte'   => 0, // base 0
            'eco_plus'      => 0, // base 0
            'eco'      => 0, // base 0
        ];
        self::$stock = [
            // Armoire rideaux 198 x 120 cm
            'amc198120' => 1,
            'amc198120BLBL' => 1,
            'amc198120LCLC' => 1,
            'amc198120ANAN' => 1,
            'amc198120BNBN' => 1,
            'amc198120GRGR' => 1,
            'amc198120NRNR' => 1,
            'amp198120' => 1,
            'amp198120BLBL' => 1,
            'amp198120LCLC' => 1,
            'amp198120ANAN' => 1,
            'amp198120GRGR' => 1,
            'amp198120NRNR' => 1,
            // Armoire rideaux 100 x 120 cm
            'amc100120' => 1,
            'amc100120BLBL' => 1,
            'amc100120LCLC' => 1,
            'amc100120ANAN' => 1,
            'amc100120BNBN' => 1,
            'amc100120GRGR' => 1,
            'amc100120NRNR' => 1,
            'amp100120' => 1,
            'amp100120BLBL' => 1,
            'amp100120LCLC' => 1,
            'amp100120ANAN' => 1,
            'amp100120GRGR' => 1,
            'amp100120NRNR' => 1,
            // Vestiaire propre
            'VMIP31' => 1,
            'VMIP31M1GRBU' => 1,
            'VMIP32' => 1,
            'VMIP32M1GRBU' => 1,
            'VMIP33' => 1,
            'VMIP33M1GRBU' => 1,
            'VMIP34' => 1,
            'VMIP34M1GRBU' => 1,
            // Vestiaire salissant
            'VMIS41' => 1,
            'VMIS41M1GRBU' => 1,
            'VMIS42' => 1,
            'VMIS42M1GRBU' => 1,
            'VMIS43' => 1,
            'VMIS43M1GRBU' => 1,
            // Coffre-fort Duo Protect Classe 1
            'PR1054G6' => 5,
            'PR1060G6' => 5,
            'PR1080G6' => 5,
            'PR1253G6' => 5,
            'PR1291G6' => 5,
            // Coffre-fort Vulcain Depot
            'VD0130G2' => 5,
            'VD0130G4' => 5,
            'VD0080G2' => 5,
            'VD0080G4' => 5,
            // Coffre-fort Vulcain norme Allemande
            'VL0040G2' => 5,
            'VL0060G2' => 5,
            'VL0080G2' => 5,
            'VL0130G2' => 5,
            'VL0170G2' => 5,
            'VL0200G2' => 5,
            // Coffre-fort Zephir Depot
            'ZD0801G4' => 5,
            'ZD0801G6' => 5,
            'ZD0802G4' => 5,
            'ZD0802G6' => 5,
            'ZD0803G4' => 5,
            'ZD0803G6' => 5,
            // Coffre-fort Zephir Duo
            'ZR1080G6' => 5,
            'ZR1114G6' => 5,
            'ZR1149G6' => 5,
            'ZR1228G4' => 5,
            'ZR1228G6' => 5,
            'ZR2043G6' => 5,
            'ZR2084G6' => 5,
            'ZR2120G6' => 5,
            'ZR2156G6' => 5,
            'ZR2192G6' => 5,
            'ZR3064G6' => 5,
            // Coffre-fort Mural VDS
            'MR0050G7V' => 5,
            'MR0050G4V' => 5,
            'MR0060G7V' => 5,
            'MR0060G1V' => 5,
            // Armoire Media DUO
            'MD1430G4' => 5,
            'MD1280G4' => 5,
            'MD1240G4' => 5,
            'MD1175G4' => 5,
			// SPE
            'VMM243SGRPU' => 1,
			
        ];
        self::$attribute = [
            [
                'name' => 'Bureau Droit - Ligne PLUS',
                'attr' => ['bureau-longueur-80-cm'],
                'plus' => 3
            ],
            [
                'name' => 'Bureau Compact Asymétrique - Ligne PLUS',
                'attr' => ['bureau-longueur-180-cm'],
                'plus' => 3
            ],
            [
                'name' => 'Bureau Compact Asymétrique - Ligne ÉLÉGANCE',
                'attr' => ['bureau-longueur-180-cm'],
                'plus' => 3
            ],
            [
                'name' => '*',
                'attr' => ['par-3-points', 'serrure-a-code-electronique-3780', 'serrure-a-code-4-molettes'],
                'plus' => 1
            ],
        ];
        self::$postcode = [
            '02,13,14,19,21,23,25,31,33,34,35,39,44,45,54,57,59,63,67,69,71,72,79,75,77,78,83,91,92,93,94,95' => 0,
            '01,03,06,10,16,17,18,22,27,28,29,30,37,38,40,41,42,47,49,50,51,52,53,55,56,58,60,61,62,68,76,80,81,84,85,86,88,89' => 1,
            '04,05,07,08,09,11,12,15,24,26,32,36,43,46,48,64,65,66,70,73,74,82,87,90' => 1,
        ];
    }

    // Instance
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new Livraison();
        }
        return self::$_instance;
    }

    // Fabrication
    public function getFabrication($id, $is_var, $attr, $delai):int {

        if (array_key_exists($id, self::$cache)) {
            return self::$cache[$id];
        }
        $product = $is_var ? new WC_Product_Variation($id) : wc_get_product($id);
        if(!$product) {
            exit;
        }
        $sku = $product->get_sku();
        if (array_key_exists($sku, self::$stock)) {
            self::$cache[$id] = self::$stock[$sku];
            return self::$stock[$sku];
        }
        foreach (self::$attribute as $attribute) {
            if ($product->get_name() == $attribute['name'] || $attribute['name'] == '*') {
                if (sizeof(array_intersect($attribute['attr'], $attr)) > 0) {
                    $delai += $attribute['plus'];
                }
                break;
            }
        }
        self::$cache[$id] = $delai + self::$decalage['fabrication'];
        return $delai + self::$decalage['fabrication'];
    }

    // Livraison
    public function getLivraison($id, $is_var, $attr, $delai, $formule, $order_id = null, $add_exp = true):int {

        // $delai = $add_exp ? $this->getFabrication($id, $is_var, $attr, $delai) : 0;
        $delai =  $this->getFabrication($id, $is_var, $attr, $delai);
        $user_cp = '00';
        if (isset(WC()->session) && !empty(WC()->session->get('customer')['shipping_postcode'])) {
            $user_cp = substr(WC()->session->get('customer')['shipping_postcode'], 0, 2);
        } elseif (isset(WC()->customer) && !empty(WC()->customer->get_shipping_postcode())) {
            $user_cp = substr(WC()->customer->get_shipping_postcode(), 0, 2);
        } else {
            $order = new WC_Order($order_id);
            $user_cp = substr($order->get_shipping_postcode(), 0, 2);
        }
        foreach (self::$postcode as $key => $value) {
            if (strpos($key, $user_cp) !== false) {
                $delai += $value;
                break;
            }
        }
        if (isset(self::$formule[$formule])) {
            $formule_id = self::$formule[$formule];
            $delai += self::$decalage[$formule_id];
        }
        return $delai;
    }

    // Magic method
    public function __get($name) {
        if (isset(self::${$name})) {
            return self::${$name};
        }
        return null;
    }
	// Décaller Date si Jours fériée ou Weekend
		public function fourchette($date_livraison, $date_livraison_max, $max_delai, $supplier_name, $order){
			error_log('salutsalutfourchette  ');
            error_log('$date_livraison est ' . $date_livraison->format('Y-m-d H:i:s'));
            error_log('$date_livraison_max est ' . $date_livraison_max->format('Y-m-d H:i:s'));
            error_log('$max_delai est ' . $max_delai); // c'est lui qiu est pas bon 
            ob_start();  
            var_dump($order);
            $output = ob_get_clean();
            $errorMessage = '   $order est :\n'. $output;
            error_log($errorMessage);
        
			// 0. HOLIDAY
			if (isset($order) && null !== $order->get_date_paid()) {
				$dday = new DateTime($order->get_date_paid());
				$currentYear = $dday->format('Y');
			} else {
				$currentYear = date('Y'); // Année actuelle si aucune date de paiement
			}
			$nextYear = $currentYear + 1; // Année suivante
			// $year = date('Y'); // Année actuelle
			// Liste des jours fériés fixes
			$holidays = array(
				'01/01/'.$nextYear, // Nouvel An
				'01/05/'.$currentYear, // Fête du Travail
				'08/05/'.$currentYear, // Victoire des Alliés
				'14/07/'.$currentYear, // Fête Nationale
				'15/08/'.$currentYear, // Assomption
				'01/11/'.$currentYear, // Toussaint
				'11/11/'.$currentYear, // Armistice
				'25/12/'.$currentYear  // Noël
			);
			// Dates variables
			$easterDate = easter_date($currentYear);
			$easterDay = date('j', $easterDate);
			$easterMonth = date('n', $easterDate);
			// Lundi de Pâques
			$holidays[] = date('d/m/Y', mktime(0, 0, 0, $easterMonth, $easterDay + 1, $currentYear));
			// Ascension
			$holidays[] = date('d/m/Y', mktime(0, 0, 0, $easterMonth, $easterDay + 39, $currentYear));
			// Lundi de Pentecôte
			$holidays[] = date('d/m/Y', mktime(0, 0, 0, $easterMonth, $easterDay + 50, $currentYear));
			// Ajout des dates supplémentaires
			$additionalHolidays = array(
				// '12/06/'.$year,
				// '13/06/'.$year,
				// '17/06/'.$year
			);
			$holidays = array_merge($holidays, $additionalHolidays);
			// Ajout d'une période supplémentaire
			$extraPeriodStart = '25/12/'.$currentYear;
			$extraPeriodEnd = '01/01/'.$nextYear;
			$extraPeriod = new DatePeriod(
				DateTime::createFromFormat('d/m/Y', $extraPeriodStart),
				new DateInterval('P1D'),
				DateTime::createFromFormat('d/m/Y', $extraPeriodEnd)
			);
			foreach ($extraPeriod as $day) {
				$holidays[] = $day->format('d/m/Y');
			}
			// Arret des chaines de production
			$supplier_name = (!empty($supplier_name)) ? strtolower($supplier_name):'';
			$holidays_supplier = array();
			if ($supplier_name == "burocean" ) {
				$extraPeriodStart = '26/02/'.$currentYear;
				$extraPeriodEnd = '01/03/'.$currentYear;
				$extraPeriod = new DatePeriod(
					DateTime::createFromFormat('d/m/Y', $extraPeriodStart),
					new DateInterval('P1D'),
					DateTime::createFromFormat('d/m/Y', $extraPeriodEnd)
				);
				foreach ($extraPeriod as $day) {
					$holidays_supplier[] = $day->format('d/m/Y');
				}
			} elseif ($supplier_name == "ggi" || $supplier_name == "evp" || $supplier_name == "hartmann" || $supplier_name == "dba-c" || $supplier_name == "armoire-plus"){
				$extraPeriodStart = '25/12/'.$currentYear;
				$extraPeriodEnd = '31/12/'.$currentYear;
				$extraPeriod = new DatePeriod(
					DateTime::createFromFormat('d/m/Y', $extraPeriodStart),
					new DateInterval('P1D'),
					DateTime::createFromFormat('d/m/Y', $extraPeriodEnd)
				);
				foreach ($extraPeriod as $day) {
					$holidays_supplier[] = $day->format('d/m/Y');
				}
			}
			
			$i = 0;
			$j = 0;
			$dday =  new DateTime('now', new DateTimeZone('Europe/Paris'));
			if (isset($order)){
				if (null !== $order->get_date_paid()){
					$dday = new DateTime($order->get_date_paid());
				}
			}
			$decalage_h = 0;
			$interval_h = new DateInterval('P1D');
			// 1. Ajustement initial de $date_livraison
			do {
				$check_day_0 = strtolower($date_livraison->format('l'));
				if (in_array($date_livraison->format('d/m/Y'), $holidays) || $check_day_0 == "friday" || $check_day_0 == "saturday" || $check_day_0 == "sunday") {
					$date_livraison->add(new DateInterval('P1D'));
					$i++;
				} else {
					break;
				}
			} while (true);
			$date_livraison_max->add(new DateInterval('P' . $i . 'D'));
			// 2. Calcul de $date_livraison suivant les conditions de la fonction fourchette
			if ($supplier_name == "hartmann") {
				if ($max_delai == 0) {
					$date_livraison->add(new DateInterval('P2D'));
					$date_livraison_max->add(new DateInterval('P4D'));
				} elseif ($max_delai == 1) {
					$date_livraison->add(new DateInterval('P10D'));
					$date_livraison_max->add(new DateInterval('P12D'));
				} else {
					$date_livraison->add(new DateInterval('P' . ($max_delai) . 'W'));
					$date_livraison_max->add(new DateInterval('P' . ($max_delai + 1) . 'W'));
				}
			} else {
				$date_livraison->add(new DateInterval('P' . ($max_delai) . 'W'));
				$date_livraison->add(new DateInterval('P2D'));
				$date_livraison_max->add(new DateInterval('P' . ($max_delai) . 'W'));
				$date_livraison_max->add(new DateInterval('P5D'));
			}
			// 2 Bis Décallage congé usines.
			$period_sup_holiday = new DatePeriod($dday, $interval_h, $date_livraison_max);
			foreach ($period_sup_holiday as $dt) {
				// Ajout d'une vérification pour voir si le jour actuel est un jour férié
				$is_holiday_h = in_array($dt->format('d/m/Y'), $holidays_supplier);
				if ($is_holiday_h ) {
					$decalage_h++;
				}
			}
			// Ajout du décalage pour vacances usines
			if ($decalage_h > 0) {
				$date_livraison->add(new DateInterval('P' . $decalage_h . 'D'));
				$date_livraison_max->add(new DateInterval('P' . $decalage_h . 'D'));
			}
			// 3. Ajustement de $date_livraison
			do {
				$check_day_1 = strtolower($date_livraison->format('l'));
				if (in_array($date_livraison->format('d/m/Y'), $holidays) || $check_day_1 == "saturday" || $check_day_1 == "sunday") {
					$date_livraison->add(new DateInterval('P1D'));
					$j++;
				} else {
					break;
				}
			} while (true);
			$date_livraison_max->add(new DateInterval('P'.$j.'D'));
			// 4. Calcul de $date_livraison_max en ajoutant un décalage pour chaque jour férié et week-end rencontré depuis $date_livraison 
			$decalage = 0;
			$interval = new DateInterval('P1D');
			// Ajout d'un jour supplémentaire à $date_livraison_max avant de créer le DatePeriod
			if ($date_livraison->format('d/m/Y') == $date_livraison_max->format('d/m/Y')) {
				$date_livraison_max->add($interval);
			}
			$period = new DatePeriod($date_livraison, $interval, $date_livraison_max);
			foreach ($period as $dt) {
				$check_day = strtolower($dt->format("l"));
				// Ajout d'une vérification pour voir si le jour actuel est un jour férié
				$is_holiday = in_array($dt->format('d/m/Y'), $holidays);
				if ($is_holiday || $check_day == "saturday" || $check_day == "sunday") {
					$decalage++;
				}
			}
			 // 5. Ajustement de $date_livraison_max si $date_livraison_max tombe un weekend
			do {
				$check_day_2 = strtolower($date_livraison_max->format('l'));
				if (in_array($date_livraison_max->format('d/m/Y'), $holidays) || $check_day_2 == "saturday" || $check_day_2 == "sunday") {
					$date_livraison_max->add(new DateInterval('P1D'));
				} else {
					break;
				}
			} while (true);
			// Ajout du décalage à $date_livraison_max
			if ($decalage > 0) {
				$date_livraison_max->add(new DateInterval('P' . $decalage . 'D'));
			}
			// 6. Ajustement de $date_livraison_max
			do {
				$check_day_3 = strtolower($date_livraison_max->format('l'));
				if (in_array($date_livraison_max->format('d/m/Y'), $holidays) || $check_day_3 == "saturday" || $check_day_3 == "sunday") {
					$date_livraison_max->add(new DateInterval('P1D'));
				} else {
					break;
				}
			} while (true);

// Si les variables sont des objets DateTime, les formater en chaîne
$date_livraison_max_str = ($date_livraison_max instanceof DateTime) ? $date_livraison_max->format('Y-m-d H:i:s') : $date_livraison_max;
$date_livraison_str = ($date_livraison instanceof DateTime) ? $date_livraison->format('Y-m-d H:i:s') : $date_livraison;

// Construire un message de log avec les informations pertinentes
$message = "Dates dans fourchette:\n" .
           "Date livraison max: $date_livraison_max_str\n" .
           "Date livraison: $date_livraison_str\n" .
           "Details var_dump:\n" . $output;

// Envoyer le message à error_log
error_log($message);


			return;
		}
}

if ( is_admin() ) {
    error_log('salutsalutIsAdmin');

    /**
     ************
     * ADMIN PAGE
     ************
     */

    /**
     * Email client [livraison partielle + terminée] : affiche délai livraison
     */
    add_action( 'woocommerce_email_before_order_table', 'email_shipping_delay_partial', 10, 4 );
    function email_shipping_delay_partial( $order, $sent_to_admin, $plain_text, $email ) {
        error_log('salutsalutemail_shipping_delay_partial');

        if ( $email->id != 'customer_note' ) {
            $status = $order->get_status();
            if ( ($status == 'partial-shipped' || $status == 'completed') ) {
                global $wpdb;
                $html = '';
                $count = 1;
                $current_shipped_sku = update_shipping_item($order);
                $drop_order = w_c_dropshipping()->orders->get_order_info($order);
                $shipping_methods = $drop_order['order']->get_shipping_methods();
                $order_items = $order->get_items();
                foreach ( $shipping_methods as $shipping_method ) {
                    $html .= '<table style="width:100%;font-size:9pt;border:dashed 1px #d7d7d7;border-spacing:0;border-collapse:collapse;">';
                    $method_active = false;
                    $supplier_name = $references = '';
                    $method_name = $shipping_method->get_name();
                    $shipping_item = $shipping_method->get_meta('Articles');
                    if ( $status == 'completed' ) {
                        // $date_livraison = new DateTime($order->get_date_paid());
                        $date_livraison = new DateTime('now', new DateTimeZone('Europe/Paris'));
                        $date_livraison_max = new DateTime('now', new DateTimeZone('Europe/Paris'));
                        $max_delai = 0;
                    }
                    foreach ( $order_items as $item ) {
                        $item_id = empty($item->get_data()['variation_id']) ? $item->get_data()['product_id'] : $item->get_data()['variation_id'];
                        if ( strpos( $shipping_item, $item->get_name() ) !== false && in_array(get_post_meta($item_id, '_sku', true), $current_shipped_sku) ) {
                            $product = wc_get_product( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", get_post_meta($item_id, '_sku', true) ) ) );
                            $is_variation = $product->is_type('variation') ? true : false;
                            $attributes = $is_variation ? $product->get_variation_attributes() : [];
                            $product_id = $is_variation ? $product->get_parent_id() : $product->get_id();
                            $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
                            $image_tag = '<img src="' . $image[0] . '" width="60" height="60">';
                            $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
                            $references .= sizeof($attributes) > 0 ?
                                '<tr style="height:60px;vertical-align:center;"><td style="width:60px;padding:5px 10px 5px 20px;background-color:#fcfcfc;">' . $image_tag . '</td><td style="padding:5px 20px 5px 10px;background-color:#fcfcfc;"><span>' . $product->get_name('edit') . '<br><small style="color:#797979;">' . wc_get_formatted_variation($attributes, true) . '</small></span></td></tr>' :
                                '<tr style="height:60px;vertical-align:center;"><td style="width:60px;padding:5px 10px 5px 20px;background-color:#fcfcfc;">' . $image_tag . '</td><td style="padding:5px 20px 5px 10px;background-color:#fcfcfc;"><span>' . $product->get_name('edit') . '</span></td></tr>';
                            if ( $status == 'completed' ) {
                                $delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                                $delai = Livraison::getInstance()->getLivraison($product->get_id(), $is_variation, $attributes, $delai, $method_name, $order->get_id());
                                $max_delai = $delai > $max_delai ? $delai : $max_delai;
                            }
                            $method_active = true;
                        }
                        // NXOVER [[
                        if (function_exists('nxover_order_options_shipping_delay') && 
                                ($o_delay = nxover_order_options_shipping_delay($item, $method_name, $order->get_id())) !== false)
                            $max_delai = max($max_delai, $o_delay);
                        // ]] NXOVER
                    }
                    if ( $method_active ) {
                        if ( $status == 'completed' ) {
							// Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delai,$supplier_name, $order);
							if ($method_name =="Formule CLÉ EN MAIN"){
								$date_livraison->add(new DateInterval('P7D'));
								$date_livraison_max->add(new DateInterval('P12D'));
							}else{
								$date_livraison->add(new DateInterval('P2D'));
								$date_livraison_max->add(new DateInterval('P5D'));
							}
							$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
							$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));
                        }
                        $html .= '<tr><td colspan="2" style="padding:15px 20px 5px;background-color:#fcfcfc;"><b>Livraison ' . $count . ' :</b> ' . $method_name . '</td></tr>';
                        $html .= '<tr><td colspan="2" style="padding:5px 20px;background-color:#fcfcfc;"><b>Expédié par ' . $supplier_name . '</b></td></tr>';
                        switch ( $method_name ) {
                            case 'Formule CLÉ EN MAIN':
                                if ( $status == 'completed' ) {
                                    // $html .= '<tr><td colspan="2" style="padding:5px 20px 20px;"><b style="color:#7f1321;"><span style="color:#333333;">Livraison estimée entre </span> ' . $date_livraison . ' et ' . $date_livraison_max .'</b></td></tr>';
                                }
                                $html .= $references;
                                $html .= '<tr><td colspan="2" style="padding:5px 20px;"><span style="font-size:0.9em;">Le prestataire vous contactera une fois votre commande récupérée par ses soins pour vous proposer une date d\'intervention (sous 2 semaines environ).<br><u>N\'hésitez pas à lui faire part de vos impératifs.</u><br>Nous vous rappelons que les livraisons ont lieu en semaine entre 8h30 et 16h30.</span></td></tr>';
                                $html .= '<tr><td colspan="2" style="padding:5px 20px 20px;"><span style="font-size:0.9em;"><b style="color:#DE2128;">IMPORTANT :</b> Assurez-vous que le mobilier commandé pourra passer les portes, escaliers et ascenseurs. <u>Dans le cadre d\'articles refusés par défaut d\'accessibilité, les frais de retour seront à votre charge et les frais de livraison ne vous seront pas remboursés</u></span></td></tr>';
                                break;
                            case 'Formule CLÉ EN MAIN HARTMANN':
								if ( $status == 'completed' ) {
                                    // $html .= '<tr><td colspan="2" style="padding:5px 20px 20px;"><b style="color:#7f1321;"><span style="color:#333333;">Livraison estimée entre </span> ' . $date_livraison . ' et ' . $date_livraison_max .'</b></td></tr>';
                                }
                                $html .= $references;
                                $html .= '<tr><td colspan="2" style="padding:5px 20px;"><span style="font-size:0.9em;">Le prestataire vous contactera une fois votre commande récupérée par ses soins pour vous proposer une date d\'intervention (sous 1 semaine environ).<br><u>N\'hésitez pas à lui faire part de vos impératifs.</u><br>Nous vous rappelons que les livraisons ont lieu en semaine entre 8h30 et 16h30.</span></td></tr>';
                                $html .= '<tr><td colspan="2" style="padding:5px 20px 20px;"><span style="font-size:0.9em;"><b style="color:#DE2128;">IMPORTANT :</b> Assurez-vous que le mobilier commandé pourra passer les portes, escaliers et ascenseurs. <u>Dans le cadre d\'articles refusés par défaut d\'accessibilité, les frais de retour seront à votre charge et les frais de livraison ne vous seront pas remboursés</u></span></td></tr>';
								break;
                            case 'Formule ECO Plus':
                            case 'Formule ECO':
                            case 'Formule ECO [OFFERTE]':
                                if ( $status == 'completed' ) {
                                    // $html .= '<tr><td colspan="2" style="padding:5px 20px 20px;"><b style="color:#7f1321;"><span style="color:#333333;">Livraison estimée entre </span> ' . $date_livraison . ' et ' . $date_livraison_max .'</b></td></tr>';
                                }
                                $html .= $references;
                                $html .= '<tr><td colspan="2" style="padding:5px 20px;"><span style="font-size:0.9em;">Le transporteur vous contactera une fois votre commande récupérée par ses soins pour vous proposer une date d\'intervention (sous 1 semaine environ).<br><u>N\'hésitez pas à lui faire part de vos impératifs.</u><br>Nous vous rappelons que les livraisons ont lieu en semaine entre 8h30 et 16h30.</span><br>';
                                $html .= '<tr><td colspan="2" style="padding:5px 20px 20px;"><span style="font-size:0.9em;"><b style="color:#DE2128;">IMPORTANT :</b> Assurez-vous que le mobilier commandé pourra passer les portes, escaliers et ascenseurs. <u>Dans le cadre d\'articles refusés par défaut d\'accessibilité, les frais de retour seront à votre charge et les frais de livraison ne vous seront pas remboursés</u></span></td></tr>';
                                break;
                            case 'Messagerie':
							case 'Formule Messagerie HARTMANN':
                                $html .= $references;
                                // $html .= '<tr><td colspan="2" style="padding:5px 20px 20px;"><b style="color:#7f1321;"><span style="color:#333333;">Livraison estimée entre </span> ' . $date_livraison . ' et ' . $date_livraison_max .'</b></td></tr>';
                                break;
                        }
                        $count += 1;
                    }
                }
                $html .= '</table><br>';
                if ( sizeof($shipping_methods) > 0 && current($shipping_methods)['name'] != 'Messagerie') {
                    $html .= '<div style="border:dashed 1px #d7d7d7;font-size:1em;padding:10px 20px;">Une fois le transporteur sur place : Comptez, déballez et contrôlez les marchandises EN PRESENCE DU CHAUFFEUR ou DES LIVREURS.<br><br>
<table align="center" border="0" cellpadding="0" cellspacing="0" style="margin:auto">
	<tbody>
		<tr>
			<td style="border-radius:5px"><a download="" href="https://www.armoireplus.fr/wp-content/uploads/2024/01/Notice-Reception-Livraison-Colis.pdf" style="font-size: 18px; font-family: sans-serif; text-decoration: none; color: #c0392b; display:inline-block; padding: 15px 25px; border-radius: 5px; background-color:#ffffff;border:2px solid #c0392b;text-align:center;"><strong>Adoptez les bons reflexes pour une reception en toute serenit&eacute; !</strong><br /><br />
			T&eacute;l&eacute;charger la fiche de livraison</a></td>
		</tr>
	</tbody>
</table><br>
                <span style="color:#c92f00;">Constater l\'état de l\'emballage ne suffit pas :</span> Conçu pour amortir les chocs de façon optimal, l\'emballage reprend sa forme initiale après un choc 9 fois sur 10. Nous vous garantissons que votre commande quitte l\'usine dans un état irréprochable.<br><br>
                <span style="color:#c92f00;">Si le chauffeur refuse d’attendre</span>, veuillez inscrire sur le bordereau de livraison du transporteur « Le chauffeur refuse d’attendre pour le contrôle »<br><br>
                Si vous deviez relever des détériorations, colis manquants ou autres problèmes :<br>
                <ul>
                    <li style="list-style: none;"><span style="color:#c92f00;font-size:8pt;"">=></span> Inscrivez clairement vos remarques sur le bordereau du transporteur</li>
                    <li style="list-style: none;"><span style="color:#c92f00;font-size:8pt;"">=></span> Si possible, prenez des photos des produits endommagés sous plusieurs angles (sans photo, nous ne pourrons enclencher rapidement la procédure de résolution)</li>
                    <li style="list-style: none;"><span style="color:#c92f00;font-size:8pt;"">=></span> N\'hésitez pas à REFUSER LES MARCHANDISES DETERIOREES en décrivant les dommages sur chaque produit</li>
                    <li style="list-style: none;color:#c92f00;border:1px solid #dddddd;padding:5px;">IMPORTANT : Ne jamais inscrire des réserves du type "sous réserve de..."  ;  "...au déballage..." ; "...après déballage..." ; "...emballage intact..." <b>= Réserves irrecevables = AUCUN RECOURS</b></li>
                    <li style="list-style: none;"><span style="color:#c92f00;font-size:8pt;"">=></span> Notez sur le bordereau du transporteur ce que vous constatez : Description & localisation du dommage sur chaque produit.</li>
                    <li style="list-style: none;"><span style="color:#c92f00;font-size:8pt;"">=></span> Informez-nous via <a href="mailto:service-client@armoireplus.fr">service-client@armoireplus.fr</a></li>
                </ul>
                Votre réclamation sera prise en charge rapidement.<br><br>
                <p style="border:dashed 1px #c92f00;background-color:#fcfcfc;padding:12px;">En cas de non-observation de ces mesures, vous n’aurez aucun recours car le transporteur sera en droit de décliner toute responsabilité. Il nous sera alors impossible de vous remplacer gratuitement le matériel détérioré.</p>
                <span style="font-size:1em;color:#c92f00;">Au moindre doute à la réception de votre commande, APPELEZ-NOUS : <b>05 31 61 98 32</b></span></div>';
                }
                echo $html;
            }
        }
    }

    /**
     * Update shipping : met à jour la meta _shipped_sku avec les items envoyés
     */
    function update_shipping_item( $order ) {
        $status = $order->get_status();
        $order_id = $order->get_id();
        $data_shipment = $order->get_meta('_wxp_shipment');
        $shipped_sku = get_post_meta($order_id, '_shipped_sku', true);
        $new_shipped_sku = [];
        $tab_shipped_sku = empty($shipped_sku) ? [] : array_filter(explode(',', $shipped_sku));
        foreach ( $data_shipment as $item ) {
            if ( (($status == 'partial-shipped' && $item['status'] == 'shipped') || ($status == 'completed')) && array_search($item['sku'], $tab_shipped_sku) === false ) {
                $new_shipped_sku[] = $tab_shipped_sku[] = $item['sku'];
            }
        }
        update_post_meta( $order_id, '_shipped_sku', implode(',', $tab_shipped_sku) );
        return $new_shipped_sku;
    }

    /**
     * Admin commande : Affiche la formule de livraison avec délai dans back-office
     */
    add_action( 'woocommerce_admin_order_data_after_order_details', 'admin_shipping_delay_order' );
    function admin_shipping_delay_order( $order ) {
        $status = $order->get_status();
        if ( $status == 'processing' || $status == 'partial-shipped' || $status == 'completed' ) {
            global $wpdb;
            $count = 1;
            try {
                $drop_order = w_c_dropshipping()->orders->get_order_info($order);
            } catch (Error $e) {
                echo '<b style="color:#7f1321;">Erreur dans le calcul des délais, INFORMER NICOLAS</b>';
                return;
            }
            $shipping_methods = $drop_order['order']->get_shipping_methods();
            $order_items = $order->get_items();
            foreach ( $shipping_methods as $shipping_method ) {
                $supplier_name = '';
                $method_name = $shipping_method->get_name();
                $shipping_item = $shipping_method->get_meta('Articles');
                $date_livraison = new DateTime($order->get_date_paid() ?? '');
                $date_livraison_max = new DateTime($order->get_date_paid() ?? '');
                $max_delai = 0;
                foreach ( $order_items as $item ) {
                    if ( strpos( $shipping_item, $item->get_name() ) !== false ) {
                        $item_id = empty($item->get_data()['variation_id']) ? $item->get_data()['product_id'] : $item->get_data()['variation_id'];
                        $product = wc_get_product( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", get_post_meta($item_id, '_sku', true) ) ) );
                        $is_variation = $product->is_type('variation') ? true : false;
                        $attributes = $is_variation ? $product->get_variation_attributes() : [];
                        $product_id = $is_variation ? $product->get_parent_id() : $product->get_id();
                        $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
                        $delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                        $delai = Livraison::getInstance()->getLivraison($product->get_id(), $is_variation, $attributes, $delai, $method_name, $order->get_id());
                        $max_delai = $delai > $max_delai ? $delai : $max_delai;
                    }
                    // NXOVER [[
                    if (function_exists('nxover_order_options_shipping_delay') && 
                            ($o_delay = nxover_order_options_shipping_delay($item, $method_name, $order->get_id())) !== false)
                        $max_delai = max($max_delai, $o_delay);
                    // ]] NXOVER
                }
				Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delai,$supplier_name,$order);
				// Définissez et formatez $formattedCustomDate ici
				$method_id = $shipping_method->get_id();
				$custom_shipping_date = $order->get_meta('custom_shipping_date_' . $method_id, true);
				$custom_shipping_text = $order->get_meta('custom_shipping_text_' . $method_id, true);
				if (!empty($custom_shipping_date)) {
					$dateObject = DateTime::createFromFormat('Y-m-d', $custom_shipping_date);
					if ($dateObject) {
						$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
						$formattedCustomDate = ucwords($formatter->format($dateObject));
					}
				}
				$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
				$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));
                switch ( $method_name ) {
                    // case 'Formule CLÉ EN MAIN':
                    // case 'Formule CLÉ EN MAIN HARTMANN':
                    // case 'Formule Messagerie HARTMANN':
                    // case 'Formule ECO [OFFERTE]':
                    // case 'Formule ECO Plus':
                    // case 'Formule ECO':
                    // case 'Messagerie':
                        // $html = '<b style="color:#7f1321;"><span style="color:#333333;">Livré entre '. $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
                        // break;
                    // default:
                        // $html = '<b style="color:#007f15;">Non disponible</b><br>';
                    default:
                        $html = '<b style="color:#7f1321;"><span style="color:#333333;">Livré entre '. $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
                }
				if (!empty($custom_shipping_date)) {
					$html = '<b style="color:#7f1321;"><span style="color:#333333;">Livraison le ' . $formattedCustomDate . '</b><br>';
				}elseif(!empty($custom_shipping_text)){
					$html = '<b style="color:#7f1321;"><span style="color:#333333;">Livraison ' . $custom_shipping_text . '</b><br>';
				}
                $margin_top = $count < sizeof($shipping_methods) ? 'margin-top:15px;' : '';
                echo '<p class="form-field form-field-wide wc-customer-user" style="width:96%;font-size:9pt;border:solid 1px #b8b8b8;padding:5px 2%!important;border-radius:3px;' . $margin_top . '"><b>' . $method_name . ' par ' . $supplier_name . '</b><br>' . $html . '</p>';
                $count += 1;
            }
        } else {
            echo '<p class="form-field form-field-wide wc-customer-user" style="width:96%;font-size:9pt;border:solid 1px #b8b8b8;padding:5px 2%!important;border-radius:3px;margin-top:15px;">Estimation livraison : <b style="color:#007f15;">En attente de paiement</b></p>';
        }
    }

	// Champs de selection de date personnalisé 
function determine_supplier_name_for_shipping_method($shipping_method, $order) {
    error_log('salutsalutdetermine_supplier_name_for_shipping_method');

    global $wpdb;
    $supplier_name = '';
    $order_items = $order->get_items();
    // $shipping_item = $shipping_method->get_meta('Articles');
    foreach ($shipping_method->get_meta_data() as $meta_data) {
        if ($meta_data->key === 'Articles' && !empty($meta_data->value)) {
            $shipping_item = $meta_data->value;
            break; // Utiliser la première valeur non vide
        }
    }
    $shipping_item_normalized = preg_replace('/\×\s*\d+/', '', $shipping_item);

    foreach ($order_items as $item) {
        $item_name_normalized = $item->get_name();
        if (strpos($shipping_item_normalized, $item_name_normalized) !== false) {
            $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
            if (!empty($supplier_name)) {
                break; // Fournisseur trouvé
            }
        }
    }

    return $supplier_name;
}

		function admin_shipping_delay_order_inpout( $order ) {
			echo '<div class="edit-date-liv" style="clear:both;">';

			// Récupérer les méthodes d'expédition de la commande
			$shipping_methods = $order->get_shipping_methods();

			foreach ($shipping_methods as $method_id => $shipping_method) {
				// Déterminer le nom du fournisseur pour cette méthode d'expédition
				$supplier_name = determine_supplier_name_for_shipping_method($shipping_method, $order);

				// Ajoutez votre champ de sélection de date ici pour chaque méthode d'expédition
				woocommerce_wp_text_input( array(
					'id' => 'custom_shipping_date_' . $method_id,
					'label' => '<b><u>' . $shipping_method->get_name() . ' - ' . $supplier_name . '</u></b><br>Date de Livraison',
				'description' => 'Sélectionnez une date de livraison',
				'desc_tip' => 'true',
					'type' => 'date',
				));

				// Champ de texte libre pour chaque méthode d'expédition
				woocommerce_wp_text_input(array(
					'id' => 'custom_shipping_text_' . $method_id,
					'label' => '<br>Champs libre : "Livraison ...."',
				'description' => '<div style="text-align:left;">=> Peut contenir "semaine XX" pour que le numéro de semaine XX soit converti en une date pour le tableau de suivi<br>=> Peut contenir "entre le 5 Janv. et ...".<br>--- Abréviations reconnues : "Janv., Févr., Mars, Avr., Mai, Juin, Juil., Août, Sept., Oct., Nov., Déc."</div>',
				'desc_tip' => 'true',
					'type' => 'text',
				));
			}

			echo '</div>';
			echo '<script>
			document.addEventListener("DOMContentLoaded", function() {
				const dateInputs = document.querySelectorAll("input[type=\'date\']");

				dateInputs.forEach(function(input) {
					input.addEventListener("change", function() {
						const selectedDate = new Date(this.value);
						const currentDate = new Date();
						currentDate.setHours(0, 0, 0, 0); // Réinitialiser l\'heure pour la comparaison

						if (selectedDate < currentDate) {
							alert("La date de livraison ne peut pas être antérieure à la date du jour.");
							this.value = ""; // Réinitialiser la valeur du champ
						}
					});
				});
			});
			</script>';
		}
		add_action( 'woocommerce_admin_order_data_after_order_details', 'admin_shipping_delay_order_inpout' );

		function save_custom_shipping_date($post_id) {
			$order = wc_get_order($post_id);

			// Récupérer les méthodes d'expédition de la commande
			$shipping_methods = $order->get_shipping_methods();

			foreach ($shipping_methods as $method_id => $shipping_method) {
				$custom_shipping_date = isset($_POST['custom_shipping_date_' . $method_id]) ? sanitize_text_field($_POST['custom_shipping_date_' . $method_id]) : '';
				$custom_shipping_text = isset($_POST['custom_shipping_text_' . $method_id]) ? sanitize_text_field($_POST['custom_shipping_text_' . $method_id]) : '';

				// Sauvegarde ou suppression des données pour chaque méthode d'expédition
				if (!empty($custom_shipping_date)) {
					$order->update_meta_data('custom_shipping_date_' . $method_id, $custom_shipping_date);
					$order->delete_meta_data('custom_shipping_text_' . $method_id); // Supprimer le texte si la date est définie
				} elseif (!empty($custom_shipping_text)) {
					$order->update_meta_data('custom_shipping_text_' . $method_id, $custom_shipping_text);
					$order->delete_meta_data('custom_shipping_date_' . $method_id); // Supprimer la date si le texte est défini
				} else {
					// Si les deux champs sont vides, supprimer les deux métadonnées
					$order->delete_meta_data('custom_shipping_date_' . $method_id);
					$order->delete_meta_data('custom_shipping_text_' . $method_id);
				}
			}

			$order->save();
		}
		add_action('woocommerce_process_shop_order_meta', 'save_custom_shipping_date');


} else {

    /**
     ************
     * FRONT PAGE
     ************
     */

    /**
     * Page panier : Affiche délai fabrication et attributs produit dans liste panier
     */
    add_filter( 'woocommerce_cart_item_name', 'cart_item_delay_info', 10, 3 );
    function cart_item_delay_info( $item_name, $cart_item, $cart_item_key ) {
        if ( is_cart() || is_checkout() ) {
            $html = '';
            $is_variation = $cart_item['variation_id'] == 0 ? false : true;
            $product_id = !$is_variation ? $cart_item['product_id'] : $cart_item['variation_id'];
            $product = new WC_Product($cart_item['product_id']);
            $monobloc = get_post_meta($cart_item['product_id'], 'monobloc', true);
            $weight = wc_get_product($product_id)->get_weight();
            $delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $cart_item['product_id'], 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
            $delai = Livraison::getInstance()->getFabrication($product_id, $is_variation, $cart_item['variation'], $delai);
            // NXOVER [[
            //laure
                error_log('eske nxover est aplé affich front' .function_exists('nxover_cart_options_shipping_delay') );
            if (function_exists('nxover_cart_options_manuf_delay') && ($o_delay = nxover_cart_options_manuf_delay($cart_item)) !== false)
                $delai = max($delai, $o_delay);
            // ]] NXOVER
            if ( $product->is_visible() ) {
                $html .= sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_name() ) . '<br>';
            } else {
                $html .= sprintf( $product->get_name() ) . '<br>';
            }
            if ( is_cart() && current_user_can( 'manage_options' ) ) {
                $product_ugs = $is_variation ? get_post_meta($cart_item['variation_id'], '_sku', true) : $product->get_sku();
                $product_ugs = empty($product_ugs) ? 'Non défini' : $product_ugs;
                $html .= '<span class="attvalue_ugs" style="color:#d24f08;">' . $product_ugs . '</span><br>';
            }
            if ( is_cart() ) {
                $pluriel = $delai > 1 ? 's' : '';
                $delai = ($delai == 0) ? '<span class="attvalue_byme"><i class="icon-truck"></i> <strong>En stock, Départ imminent' : '<span class="attvalue_byme"><i class="icon-truck"></i> Départ usine sous <strong>' . $delai . ' semaine' . $pluriel;
				$html .= $delai . ' par ' . get_post_meta($cart_item['product_id'], 'supplier', true) . '</strong></span><br>';
            }
            if ( !is_null($weight) && !empty($weight)) {
                $html .= '<span class="attpoids_byme"><i class="icon-info-sign"></i> Poids: ' . $weight . ' Kg</span>';
            }
            if ( !empty($monobloc) ) {
                switch ($monobloc) {
                    case 'oui':
                        $html .= '<span class="cart-fabrication" style="margin-left:10px;"><i class="icon-inbox"></i> Livré Monobloc <i class="icon-question tooltip1" style="color:#d24f08;"><i class="tooltiptext1">Meuble Assemblé et Soudé en Usine</i></i></span>';
                        break;
                    case 'mixte':
                        $html .= '<span class="cart-fabrication" style="margin-left:10px;"><i class="icon-inbox"></i> Livré Monobloc, à équiper <i class="icon-question tooltip1" style="color:#d24f08;"><i class="tooltiptext1">Meuble Assemblé et Soudé en Usine, Equipement livré séparement à monter par vos soins</i></i></span>';;
                        break;
                    case 'caisson':
                        $html .= '<span class="cart-fabrication" style="margin-left:10px;"><i class="icon-inbox"></i> Livré Partiellement Monobloc <i class="icon-question tooltip1" style="color:#d24f08;"><i class="tooltiptext1">Caisson Monobloc, Bureau à monter</i></i></span>';;
                        break;
                    case 'non':
                        $html .= '<span class="cart-fabrication" style="margin-left:10px;"><i class="icon-inbox"></i> Livré démonté</span>';
                        break;
                }
            }
            return '<div id="addinfo_attribu">' . $html . '</div>';
        }
    }

    /**
     * Page panier et checkout : Affiche délai livraison pour chaque formule
     */
    add_filter( 'woocommerce_cart_shipping_method_full_label', 'cart_shipping_delay_order', 10, 2 );
    function cart_shipping_delay_order( $label, $method ) {
        $max_delai = 0;
        //laure
        $i=0 ;
        $cart = WC()->cart->get_cart();
        $product_list = $method->meta_data['Articles'];
        foreach ( $cart as $cart_item ) {
            //laure
            for ( $i=0 ; $i<count($cart) ; $i++ ) {
                $product = $cart_item['data'];
                if (strpos($product_list, $product->get_name()) !== false) {
                    $is_variation = $cart_item['variation_id'] == 0 ? false : true;
                    $product_id = $is_variation ? $cart_item['variation_id'] : $cart_item['product_id'];
                    $delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $cart_item['product_id'], 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                    //laure
                    error_log('$delai est ' .$i  . $delai); //01
                    $supplier_name = get_post_meta($cart_item['product_id'], 'supplier', true);
                    // NXOVER [[
                        error_log('eske nxover cart opt ship delay est aplé' .function_exists('nxover_cart_options_shipping_delay') );
                        error_log('nxover_cart_options_shipping_delay($cart_item, $method)) !== false' . nxover_cart_options_shipping_delay($cart_item, $method));

                    if (function_exists('nxover_cart_options_shipping_delay') && ($o_delay = nxover_cart_options_shipping_delay($cart_item, $method)) !== false)
                                        //original
                                        // $delai = max($delai, $o_delay);
                        //laure
                        $max_delai = max($delai, $o_delay);

                    error_log('$o_delay est ' .$i  . $o_delay); //08

                    // ]] NXOVER     
                    $delai =  (strtolower($supplier_name) == "vinco") ? Livraison::getInstance()->getLivraison($product_id, $is_variation, $cart_item['variation'], $delai, $method->label) : $delai;

                    $max_delai = $delai > $max_delai ? $delai : $max_delai;
                    error_log('$delai est l901' .$i  . $delai); //01
                    error_log('$max_delai est ' .$i  . $max_delai);//00
                }
            }
        }
		$date_livraison = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $date_livraison_max = new DateTime('now', new DateTimeZone('Europe/Paris'));
		Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delai,$supplier_name,$order = null);
		$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
		$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));
        $label .= '<br><small>';
        switch ( $method->label ) {
            case 'Formule CLÉ EN MAIN':
					$label .= '<span style="color:#0b4797;text-decoration:underline;">Livré entre <b>' . $date_livraison . ' et ' . $date_livraison_max .' </b></span><br>';
                $label .= '<img src="/wp-content/themes/legenda-child/img/icone_livraison-enmain.jpg" alt="Formule CLÉ EN MAIN">';
                $label .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par véhicule passe-partout avec installation complète dans la pièce de votre choix (hors accès par escaliers).</b><br>';
                $label .= '<span class="shipping-detail">Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage. Le mobilier est alors directement prêt à l\'emploi.<br>Livraison en Semaine entre 8h et 16h30.<br><span style="color:#560f56;"><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><u>ATTENTION</u> : Ces frais s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur) <u>ou bien installation en RDC.</u></span></i><br></span>';
                $label .= '<hr style="margin:10px 0;">';
                break;
            case 'Formule CLÉ EN MAIN HARTMANN':
				$label .= '<span style="color:#0b4797;text-decoration:underline;">Livré entre <b>' . $date_livraison . ' et ' . $date_livraison_max .' </b></span><br>';
                $label .= '<img src="/wp-content/themes/legenda-child/img/icone_livraison-hartmann.jpg" alt="Formule CLÉ EN MAIN">';
                $label .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par véhicule 19 Tonnes sur Rendez-vous avec mise en place dans la pièce de votre choix (hors accès par escaliers).</b><br>';
                $label .= '<span class="shipping-detail">Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage. Mise en place en RDC avec 2 marches maximum ou étage avec ascenseur. Maximum de 20 mètres de roulage.<br>Livraison en Semaine entre 8h et 16h30.<br><span style="color:#560f56;"><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><u>ATTENTION</u> : Tarif HORS accès difficile (forte pente, roulage suppérieur à 20 mètre, accès par escalier; fixation au sol par technicien agréé)</u></span></i><br></span>';
                $label .= '<hr style="margin:10px 0;">';
                break;        
				case 'Formule Messagerie HARTMANN':
				$label .= '<span style="color:#0b4797;text-decoration:underline;">Livré entre <b>' . $date_livraison . ' et ' . $date_livraison_max .' </b></span><br>';
                $label .= '<img src="/wp-content/themes/legenda-child/img/icone_livraison-messagerie-hartmann.jpg" alt="Formule Messagerie HARTMANN">';
                $label .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par véhicule 19 Tonnes sans Rendez-vous.</b><br>';
                $label .= '<span class="shipping-detail">Commande déposée au pied du camion.<br>Livraison en Semaine entre 8h et 16h30.<br><span style="color:#560f56;"><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><u>ATTENTION</u> : Tarif HORS accès difficile (forte pente, roulage suppérieur à 20 mètre, accès par escalier; fixation au sol par technicien agréé)</u></span></i><br></span>';
                $label .= '<hr style="margin:10px 0;">';
                break;
            case 'Formule ECO [OFFERTE]':
            case 'Formule ECO':
				$label .= '<span style="color:#0b4797;text-decoration:underline;">Livré entre <b>' . $date_livraison . ' et ' . $date_livraison_max .' </b></span><br>';
                $label .= '<img src="/wp-content/themes/legenda-child/img/icone_livraison-eco.jpg" alt="Formule ECO">';
                $label .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par semi-remorque sans HAYON.</b><br>';
                $label .= '<span class="shipping-detail">Déchargement par vos soins.<br>Livraison en Semaine entre 8h et 16h30.<br></span>';
                $label .= '<hr style="margin:10px 0;">';
                break;
            case 'Formule ECO Plus':
				$label .= '<span style="color:#0b4797;text-decoration:underline;">Livré entre <b>' . $date_livraison . ' et ' . $date_livraison_max .' </b></span><br>';
                $label .= '<img src="/wp-content/themes/legenda-child/img/icone_livraison-ecoplus.jpg" alt="Formule ECO Plus">';
                $label .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par semi-remorque équipé d\'un HAYON.</b><br>';
                $label .= '<span class="shipping-detail">Commande déposée au pied du camion.<br>Livraison en Semaine entre 8h et 16h30.<br><i><span style="color:#560f56;"><u>Attention sur devis</u> :</span> Livraison par véhicule type porteur, avec capacité de chargement réduite</i><br></span>';
                $label .= '<hr style="margin:10px 0;">';
                break;
            case 'Messagerie':
                $label .= '<span style="color:#0b4797;text-decoration:underline;text-transform:capitalize;">Livraison sous 10 jours.</span><br>';
                $label .= '<img src="/wp-content/themes/legenda-child/img/icone_livraison-messagerie.jpg" alt="Messagerie">';
                $label .= '<span class="shipping-detail">Frais de conditionnement, préparation, traitement et livraison.<br></span>';
                $label .= '<hr style="margin:10px 0;">';
                break;
            default:
                $label .= 'Nous contacter';
        }
        $label .= '</small>';
        return $label;
    }
if (!function_exists('process_ywapo_meta_data')) {
    function process_ywapo_meta_data($ywapo_meta_data, $order_item_meta) {
        error_log('salutsalutprocess_ywapo_meta_data');
        global $wpdb;
        $addons_info = [];

        foreach ($ywapo_meta_data as $meta_item) {
			
            if (is_array($meta_item)) {
                //laure
                foreach ($meta_item as $meta_item_key => $meta_item_value) {
                    foreach ($meta_item_value as $k=> $v) {
                    //Si la valeur de la méta contient 'product-'
                        if (strpos($v, 'product-'!==false)){
                        $addonCompleteId = $v;
                        }
                    }

                    // //laure
                    // ob_start();  
                    // var_dump($meta_key);
                    // var_dump($meta_value);
                    // $output = ob_get_clean();
                    // $errorMessage = '  key - val :\n'. $output;
                    // error_log($errorMessage);
                    // //fin laure

                    if (strpos($addonCompleteId, 'product-') === 0) {
						$addon_id = explode('-', $meta_item_key)[0];
						$addon_settings = get_addon_settings($addon_id);
						$addon_title = $addon_settings['title'] ?? '';
                        $quantity = 1; // Quantité par défaut

                        foreach ($order_item_meta as $meta) {
                            if ($meta->key === $addon_title) {
                                $quantity = extract_quantity_from_string($meta->value);
								$addon_info = preg_replace('/\s*\(.*?\)/', '', $meta->value);
                                break;
                            }
                        }
                        preg_match('/product-(\d+)/', $addonCompleteId, $matches);
                        $product_id = $matches[1] ?? 0;

                        $product_cog = get_post_meta($product_id, '_wc_cog_cost', true) ?: "N/A";
                        $product_mpn = get_product_ugs($product_id, $wpdb);

                        $addons_info[] = [
                            'key' => $meta_item_key,
                            'value' => $meta_item_value,
                            'settings' => $addon_settings,
                            'title' => $addon_title,
                            'info' => $addon_info,
                            'quantity' => $quantity,
                            'product_id' => $product_id,
                            'product_cog' => $product_cog,
                            'product_mpn' => $product_mpn,
                        ];
                    }
                }
            }
        }
        return $addons_info;
    }
}

if (!function_exists('get_product_ugs')) {
    error_log('salutsalutget_product_ugs');

    function get_product_ugs($product_id, $wpdb) {
		global $wpdb;
        $meta_value_serialized = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->prefix}postmeta WHERE post_id = %d AND meta_key = '_woocommerce_gpf_data'",
            $product_id
        ));
        $product_mpn = 'N/A';
        if ($meta_value_serialized) {
            $meta_value = maybe_unserialize($meta_value_serialized);
            if (is_array($meta_value) && isset($meta_value['mpn'])) {
                $product_mpn = $meta_value['mpn'];
            }
        }
        return $product_mpn;
    }
}

if (!function_exists('get_addon_settings')) {
    function get_addon_settings($addon_id) {
        error_log('salutsalutget_addon_settings');

        global $wpdb;
        $table_name = $wpdb->prefix . 'yith_wapo_addons';
        $query = $wpdb->prepare("SELECT settings FROM {$table_name} WHERE ID = %d", $addon_id);
        $result = $wpdb->get_var($query);
        return maybe_unserialize($result);
    }
}
    /**
     * Page thankyou et compte client : Affiche délai livraison pour les commandes
     */
if (!function_exists('process_addons_for_order_items')) {
    function process_addons_for_order_items($item_order) {
        error_log('salutsalutprocess_addons_for_order_items');

		$addons_for_items  = [];

        foreach ($item_order as $order_item) {

			$order_item_id = $order_item->get_id();
            //laure
            // error_log('$order_item_id est ' . $order_item_id);
            $addons_simple = [];
			$addons_product = [];
            $meta_data_array = $order_item->get_meta_data();
            foreach ($meta_data_array as $meta_data) {
                //laure on a bien chaq meta
                    // ob_start();
                    // var_dump($meta_data);
                    // $output = ob_get_clean();
                    // $errorMessage = '   $meta_data est :\n' . $output;
                    // error_log($errorMessage);
                if (($meta_data->key === '_ywapo_meta_data') || ($meta_data->key ==='_ywraq_wc_ywapo')) {
                    $ywapo_meta_data = $meta_data->value;

                    $addons_info = process_ywapo_meta_data($ywapo_meta_data, $order_item->get_meta_data());

                    foreach ($ywapo_meta_data as $meta_item) {
                        if (is_array($meta_item)) {
                            foreach ($meta_item as $meta_key => $meta_value) {
								// error_log("meta_value de process_addons_for_order_items " . $meta_key . "value : " . print_r($meta_value, true));
                                $addOnCompletePid = $meta_value[0];

                                if (strpos($addOnCompletePid, 'product-') === 0) {
                                    $product_addon_found = false;
                                    foreach ($addons_info as $addon) {
                                        if ($addon['key'] === $meta_key) {
                                            $addons_product[] = $addon;
                                            $product_addon_found = true;
											error_log("product_addon_found de process_addons_for_order_items " . $product_addon_found);
                                            break; // Sortir de la boucle des addons_info
                                        }
                                    }
                                    if (!$product_addon_found) {
                                        $addons_product[] = [
                                            'key' => $meta_key,
                                            'value' => $meta_value
                                        ];
                                    }
                                } else {
									$addon_id = explode('-', $meta_key)[0];
									$addon_settings = get_addon_settings($addon_id);
									$addon_title = $addon_settings['title'] ?? '';
									
                                    $addons_simple[] = [
                                        'key' => $meta_key,
                                        'value' => $meta_value,
                                        'title' => $addon_title
                                    ];
                                }
                            }
                        }
                    }
                }
            }
            $addons_for_items[$order_item_id] = [
                'addons_simple' => $addons_simple,
                'addons_product' => $addons_product
            ];
        }
        return $addons_for_items;
    }
}
    add_action( 'woocommerce_order_details_before_order_table', 'client_shipping_delay_order', 10, 2 );
    function client_shipping_delay_order( $order ) {
        global $wpdb;
        $html = '<h2 class="woocommerce-order-details__title">Information de livraison</h2>';
        $status = $order->get_status();
        $view_estimate = $status == 'processing' || $status == 'completed' || $status == 'partial-shipped';
        $drop_order = w_c_dropshipping()->orders->get_order_info($order);
        $shipping_methods = $drop_order['order']->get_shipping_methods();
        $order_items = $order->get_items();
		
        foreach ( $shipping_methods as $shipping_method ) {
            $supplier_name = $references = '';
            $method_name = $shipping_method->get_name();
            $shipping_item = $shipping_method->get_meta('Articles');
            $date_livraison = new DateTime(''.$order->get_date_paid() ?? '');
            $date_livraison_max = new DateTime(''.$order->get_date_paid() ?? '');
            $max_delai = 0;
            foreach ( $order_items as $item ) {

                if ( strpos( $shipping_item, $item->get_name() ) !== false ) {
                    $item_id = empty($item->get_data()['variation_id']) ? $item->get_data()['product_id'] : $item->get_data()['variation_id'];
                    $product = wc_get_product( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", get_post_meta($item_id, '_sku', true) ) ) );
                    $is_variation = $product->is_type('variation') ? true : false;
                    $attributes = $is_variation ? $product->get_variation_attributes() : [];
                    $product_id = $is_variation ? $product->get_parent_id() : $product->get_id();
                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
                    $image_tag = '<img src="' . $image[0] . '" width="60" height="60">';
                    $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
					
					
					
					$order_item_id = $item->get_id();
					$addons_for_items = process_addons_for_order_items($order_items);

                    $references .= sizeof($attributes) > 0 ?
                        '<p class="line-product">' . $image_tag . '<span><b>' . $product->get_name('edit') . '</b><br><small style="color:#797979;">' . wc_get_formatted_variation($attributes, true) . '</small>' :
                        '<p class="line-product">' . $image_tag . '<span><b>' . $product->get_name('edit') . '</b>';
						
            // Ajouter les addons simples
            if (isset($addons_for_items[$order_item_id]['addons_simple']) && sizeof($addons_for_items[$order_item_id]['addons_simple']) > 0) {
                $references .= '<br><small style="color:#797979;">Addons: ';
                foreach ($addons_for_items[$order_item_id]['addons_simple'] as $addon) {
                    $references .= esc_html($addon['title']) . ': ' . esc_html($addon['value']) . ', ';
                }
                $references = rtrim($references, ', ') . '</small>';
            }

            // Ajouter les addons de produits
            if (isset($addons_for_items[$order_item_id]['addons_product']) && sizeof($addons_for_items[$order_item_id]['addons_product']) > 0) {
                $references .= '<br><small style="color:#797979;">';
                foreach ($addons_for_items[$order_item_id]['addons_product'] as $addon) {
                    $references .= esc_html($addon['info']) . ', ';
                }
                $references = rtrim($references, ', ') . '</small>';
            }
			$references .= '</span></p>';
						
                    $delai = intval(preg_replace('/[^0-9.]/', '', ''.wp_get_post_terms( $product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                    $add_expedition = !($status == 'completed');
                    $delai = Livraison::getInstance()->getLivraison($product->get_id(), $is_variation, $attributes, $delai, $method_name, $order->get_id(), $add_expedition);
                    $max_delai = $delai > $max_delai ? $delai : $max_delai;
                }
                // NXOVER [[
                if (function_exists('nxover_order_options_shipping_delay') && ($o_delay = nxover_order_options_shipping_delay($item, $method_name, $order->get_id())) !== false)
                    $max_delai = max($max_delai, $o_delay);
                // ]] NXOVER
            }
			Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delai,$supplier_name,$order);
				// Définissez et formatez $formattedCustomDate ici
				$method_id = $shipping_method->get_id();
				$custom_shipping_date = $order->get_meta('custom_shipping_date_' . $method_id, true);
				$custom_shipping_text = $order->get_meta('custom_shipping_text_' . $method_id, true);
				if (!empty($custom_shipping_date)) {
					$dateObject = DateTime::createFromFormat('Y-m-d', $custom_shipping_date);
					if ($dateObject) {
						$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
						$formattedCustomDate = ucwords($formatter->format($dateObject));
					}
				}
			$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
			$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));
            $html .= '<div class="woocommerce-order-delivery">';
            $html .= $references . '<hr style="margin:5px 0;">';
            $html .= '<div class="delivery-content">';
            $html .= '<div class="delivery-supplier"><b>Expédié par ' . $supplier_name . '</b><h3>' . $method_name . '</h3></div>';
            $label_livraison = $status == 'completed' ? 'Délai de livraison' : 'Délai de préparation et livraison';
            $html .= '<div class="delivery-details">';
            switch ( $method_name ) {
                case 'Formule CLÉ EN MAIN':
                    if ( $view_estimate ) {
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
							$html .= '<b style="color:gray;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">' . $label_livraison . ' estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
							$html .= '<b style="color:gray;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}
                        
                    } else {
                        $html .= '<b style="color:#007f15;text-transform:uppercase;">Délai de livraison estimé : en attente du paiement</b><br>';
                    }
                    $html .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;margin:0 7px;"></i><b>Livraison par véhicule passe-partout avec installation complète dans la pièce de votre choix (hors accès par escaliers).</b><br>Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage. Le mobilier est alors directement prêt à l\'emploi.<br>Livraison en Semaine entre 8h et 16h30.<br>';
                    $html .= '<span style="color:#560f56;"><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><u>ATTENTION</u> : Ces frais s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur) <u>ou bien installation en RDC.</u></i></span>';
                    break;
                case 'Formule CLÉ EN MAIN HARTMANN':
                    if ( $view_estimate ) {
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
							$html .= '<b style="color:gray;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">' . $label_livraison . ' estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
							$html .= '<b style="color:gray;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}
                        
                    } else {
                        $html .= '<b style="color:#007f15;text-transform:uppercase;">Délai de livraison estimé : en attente du paiement</b><br>';
                    }
                    $html .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;margin:0 7px;"></i><b>Livraison par véhicule type 19 Tonnes avec installation complète dans la pièce de votre choix (hors accès par escaliers), roulage de 20 mètres maximum.</b><br>Déballage, mise en place et évacuation des emballages pour recyclage. Le mobilier est alors directement prêt à l\'emploi.<br>Livraison en Semaine entre 8h et 16h30.<br>';
                    $html .= '<span style="color:#560f56;"><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><u>ATTENTION</u> : Ces frais s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur) <u>ou bien installation en RDC.</u></i></span>';
                    break;
                case 'Formule Messagerie HARTMANN':
                    if ( $view_estimate ) {
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">' . $label_livraison . ' estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
						}
                    } else {
                        $html .= '<b style="color:#007f15;text-transform:uppercase;">Délai de livraison estimé : en attente du paiement</b><br>';
                    }
                    $html .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;margin:0 7px;"></i><b>Livraison par véhicule type 19 Tonnes, sans rendez-vous.</b><br>Manutention par vos soins à partir du camion<br>Livraison en Semaine entre 8h et 16h30.<br>';
                    $html .= '<span style="color:#560f56;"><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><u>ATTENTION</u> : Ces frais s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur) <u>ou bien installation en RDC.</u></i></span>';
                    break;
                case 'Formule ECO [OFFERTE]':
                case 'Formule ECO':
                    if ( $view_estimate ) {
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">' . $label_livraison . ' estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}
                        
                    } else {
                        $html .= '<b style="color:#007f15;text-transform:uppercase;">Délai de livraison estimé : en attente du paiement</b><br>';
                    }
                    $html .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;margin:0 7px;"></i><b>Livraison par semi-remorque sans HAYON.</b><br>Déchargement par vos soins.<br>Livraison en Semaine entre 8h et 16h30.';
                    break;
                case 'Formule ECO Plus':
                    if ( $view_estimate ) {
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">' . $label_livraison . ' estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}
                        
                    } else {
                        $html .= '<b style="color:#007f15;text-transform:uppercase;">Délai de livraison estimé : en attente du paiement</b><br>';
                    }
                    $html .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;margin:0 7px;"></i><b>Livraison par semi-remorque équipé d\'un HAYON.</b><br>Commande déposée au pied du camion.<br>Livraison en Semaine entre 8h et 16h30.<br><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><span style="color:#560f56;"><u>ATTENTION SUR DEVIS</u> :</span> Livraison par véhicule type porteur, avec capacité de chargement réduite</i>';
                    break;
                case 'Messagerie':
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">' . $label_livraison . ' estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .'</b><br>';
						}
                    break;
                default:
                    $html .= 'Nous contacter';
            }
            $html .= '</div></div></div>';
        }
        echo $html;
    }

    /**
     * Page devis client : Affiche délai livraison pour les devis
     */
    add_action( 'woocommerce_quote_details_before_order_table', 'client_shipping_delay_quote', 10, 2 );
    function client_shipping_delay_quote( $order ) {
        ini_set('display_errors', 1);
        global $wpdb;
        $html = '<div class="title-shipping">Information de livraison</div>';
        $drop_order = w_c_dropshipping()->orders->get_order_info($order);
        $shipping_methods = $drop_order['order']->get_shipping_methods();
        $order_items = $order->get_items();
        foreach ( $shipping_methods as $shipping_method ) {
            $supplier_name = $references = '';
            $method_name = $shipping_method->get_name();
            $shipping_item = $shipping_method->get_meta('Articles');
            $date_livraison = new DateTime($order->get_date_created());
            $date_livraison_max = new DateTime($order->get_date_created());
            $max_delai = 0;
            foreach ( $order_items as $item ) {
                if ( strpos( $shipping_item, $item->get_name() ) !== false ) {
                    $item_id = empty($item->get_data()['variation_id']) ? $item->get_data()['product_id'] : $item->get_data()['variation_id'];
                    $product = wc_get_product( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", get_post_meta($item_id, '_sku', true) ) ) );
                    $is_variation = $product->is_type('variation') ? true : false;
                    $attributes = $is_variation ? $product->get_variation_attributes() : [];
                    $product_id = $is_variation ? $product->get_parent_id() : $product->get_id();
                    $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
                    $image_tag = '<img src="' . $image[0] . '" width="60" height="60">';
                    $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
					$order_item_id = $item->get_id();
					$addons_for_items = process_addons_for_order_items($order_items);

                    $references .= sizeof($attributes) > 0 ?
                        '<p class="line-product">' . $image_tag . '<span><b>' . $product->get_name('edit') . '</b><br><small style="color:#797979;">' . wc_get_formatted_variation($attributes, true) . '</small>' :
                        '<p class="line-product">' . $image_tag . '<span><b>' . $product->get_name('edit') . '</b>';
						
            // Ajouter les addons simples
            if (isset($addons_for_items[$order_item_id]['addons_simple']) && sizeof($addons_for_items[$order_item_id]['addons_simple']) > 0) {
                $references .= '<br><small style="color:#797979;">Addons: ';
                foreach ($addons_for_items[$order_item_id]['addons_simple'] as $addon) {
                    $references .= esc_html($addon['title']) . ': ' . esc_html($addon['value']) . ', ';
                }
                $references = rtrim($references, ', ') . '</small>';
            }

            // Ajouter les addons de produits
            if (isset($addons_for_items[$order_item_id]['addons_product']) && sizeof($addons_for_items[$order_item_id]['addons_product']) > 0) {
                $references .= '<br><small style="color:#797979;">';
                foreach ($addons_for_items[$order_item_id]['addons_product'] as $addon) {
                    $references .= esc_html($addon['info']) . ', ';
                }
                $references = rtrim($references, ', ') . '</small>';
            }
			$references .= '</span></p>';
						
                    $delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                    $delai = Livraison::getInstance()->getLivraison($product->get_id(), $is_variation, $attributes, $delai, $method_name, $order->get_id());
                    $max_delai = $delai > $max_delai ? $delai : $max_delai;
                }
                // NXOVER [[
                if (function_exists('nxover_order_options_shipping_delay') && ($o_delay = nxover_order_options_shipping_delay($item, $method_name, $order->get_id())) !== false)
                    $max_delai = max($max_delai, $o_delay);
                // ]] NXOVER
            }
			Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delai,$supplier_name,$order);
				// Définissez et formatez $formattedCustomDate ici
				$method_id = $shipping_method->get_id();
				$custom_shipping_date = $order->get_meta('custom_shipping_date_' . $method_id, true);
				$custom_shipping_text = $order->get_meta('custom_shipping_text_' . $method_id, true);
				if (!empty($custom_shipping_date)) {
					$dateObject = DateTime::createFromFormat('Y-m-d', $custom_shipping_date);
					if ($dateObject) {
						$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
						$formattedCustomDate = ucwords($formatter->format($dateObject));
					}
				}
			$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
			$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));
            $html .= '<div class="woocommerce-order-delivery">';
            $html .= $references . '<hr style="margin:5px 0;">';
            $html .= '<div class="delivery-content">';
            $html .= '<div class="delivery-supplier"><b>Expédié par ' . $supplier_name . '</b><h3>' . $method_name . '</h3></div>';
            $html .= '<div class="delivery-details">';
            switch ( $method_name ) {
                case 'Formule CLÉ EN MAIN':
                case 'Formule CLÉ EN MAIN HARTMANN':          
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
							$html .= '<b style="color:gray;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison estimée entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
							$html .= '<b style="color:gray;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}
                    
                    $html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
                    break;
                case 'Formule ECO [OFFERTE]':
                case 'Formule ECO':
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison estimée entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}
                    
                    $html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
                    break;
				case 'Formule Messagerie HARTMANN':
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison estimée entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
						}
                    $html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
                    break;
                case 'Formule ECO Plus':
						if (!empty($custom_shipping_date)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison le ' . $formattedCustomDate . '</b><br>';
						}elseif (!empty($custom_shipping_text)) {
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison ' . $custom_shipping_text . '</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}else{
							$html .= '<b style="color:#007f15;text-transform:uppercase;">Livraison estimée entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
							$html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
						}
                    
                    $html .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;margin:0 7px;"></i><b>Livraison par semi-remorque équipé d\'un HAYON.</b><br>Commande déposée au pied du camion.<br>Livraison en Semaine entre 8h et 16h30.<br><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><span style="color:#560f56;"><u>ATTENTION SUR DEVIS</u> :</span> Livraison par véhicule type porteur, avec capacité de chargement réduite</i><br>';
                    $html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
                    break;
                case 'Messagerie':
                    $html .= 'Frais de conditionnement, préparation, traitement et livraison.<br>';
                    $html .= 'Livraison sous 10 jours.';
                    break;
                default:
                    $html .= 'Nous contacter';
            }
            $html .= '</div></div></div>';
        }
        echo $html;
    }

}

/**
 ********************
 * ADMIN & FRONT PAGE
 ********************
 */

/**
 * Email client [en attente + en cours] : affiche délai livraison
 */
add_action( 'woocommerce_email_before_order_table', 'email_shipping_delay_order', 10, 4 );
function email_shipping_delay_order( $order, $sent_to_admin, $plain_text, $email ) {
    error_log('salutsalut5');
    if ( $email->id != 'customer_note' ) {
        $html = '';
        if ( $order->get_payment_method() == 'cod' ) {
            $html .= '<p style="font-size:9pt;border:solid 1px #c92f00;padding:8px;"><b>Vous avez opté pour le mandat administratif :</b> votre commande devra être réglée à réception de votre facture, après l’expédition des marchandises. Vous trouverez notre RIB en pièce jointe pour réaliser le virement. <span style="color:#c92f00;">S’agissant d’une procédure de mise à disposition de votre facture via la plateforme CHORUS, vous devrez nous communiquer le numéro SIRET et accessoirement le numéro de service et d’engagement.</span></p>';
        }
        $order_status = $order->get_status();
        if ( $order_status == 'processing' || $order_status == 'on-hold' ) {
            global $wpdb;
            $count = 1;
            $drop_order = w_c_dropshipping()->orders->get_order_info($order);
            $shipping_methods = $drop_order['order']->get_shipping_methods();
            $order_items = $order->get_items();

            foreach ( $shipping_methods as $shipping_method ) {
                $html .= '<table style="width:100%;font-size:9pt;border:dashed 1px #d7d7d7;border-spacing:0;border-collapse:collapse;">';
                $supplier_name = $references = '';
                $method_name = $shipping_method->get_name();
                $shipping_item = $shipping_method->get_meta('Articles');
                $date_livraison = new DateTime($order->get_date_paid());
                $date_livraison_max = new DateTime($order->get_date_paid());
                $max_delai = 0;
                foreach ( $order_items as $item ) {
                    if ( strpos( $shipping_item, $item->get_name() ) !== false ) {
                        $item_id = empty($item->get_data()['variation_id']) ? $item->get_data()['product_id'] : $item->get_data()['variation_id'];
                        $product = wc_get_product( $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", get_post_meta($item_id, '_sku', true) ) ) );
                        $is_variation = $product->is_type('variation') ? true : false;
                        $attributes = $is_variation ? $product->get_variation_attributes() : [];
                        $product_id = $is_variation ? $product->get_parent_id() : $product->get_id();
                        //laure  
                        // error_log('$product_id est ' . $product_id);
                        $image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_id ), 'thumbnail' );
                        $image_tag = '<img src="' . $image[0] . '" width="60" height="60">';
                        $supplier_name = get_post_meta($item['product_id'], 'supplier', true);
						$order_item_id = $item->get_id();
                        //laure  
                        error_log('$order_item_id est ' . $order_item_id);

						$addons_for_items = process_addons_for_order_items($order_items);
                        //laure  n'est pas lancé
    ob_start();  
    var_dump($addons_for_items);
    $output = ob_get_clean();
    $errorMessage = '  $addons_for_items est :\n'. $output;
    error_log($errorMessage);
						$references .= sizeof($attributes) > 0 ?
							'<tr style="height:60px;vertical-align:center;"><td style="width:60px;padding:5px 10px 5px 0;background-color:#fcfcfc;">' . $image_tag . '</td><td style="padding:5px 0;background-color:#fcfcfc;"><span><b>' . $product->get_name('edit') . '</b><br><small style="color:#797979;">' . wc_get_formatted_variation($attributes, true) . '</small></span></td></tr>' :
							'<tr style="height:60px;vertical-align:center;"><td style="width:60px;padding:5px 10px 5px 0;background-color:#fcfcfc;">' . $image_tag . '</td><td style="padding:5px 0;background-color:#fcfcfc;"><span><b>' . $product->get_name('edit') . '</b></span></td></tr>';
							
						// Ajouter les addons simples
						if (isset($addons_for_items[$order_item_id]['addons_simple']) && sizeof($addons_for_items[$order_item_id]['addons_simple']) > 0) {
							$references .= '<br><small style="color:#797979;">Addons: ';
							foreach ($addons_for_items[$order_item_id]['addons_simple'] as $addon) {
								$references .= esc_html($addon['title']) . ': ' . esc_html($addon['value']) . ', ';
							}
							$references = rtrim($references, ', ') . '</small>';
						}

						// Ajouter les addons de produits
						if (isset($addons_for_items[$order_item_id]['addons_product']) && sizeof($addons_for_items[$order_item_id]['addons_product']) > 0) {
							$references .= '<br><small style="color:#797979;">';
							foreach ($addons_for_items[$order_item_id]['addons_product'] as $addon) {
								$references .= esc_html($addon['info']) . ', ';
							}
							$references = rtrim($references, ', ') . '</small>';
						}
						$references .= '</span></p>';
						                       

                        $delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                        $delai = Livraison::getInstance()->getLivraison($product->get_id(), $is_variation, $attributes, $delai, $method_name, $order->get_id());
                        $max_delai = $delai > $max_delai ? $delai : $max_delai;
                    }
                    // NXOVER [[
                    if (function_exists('nxover_order_options_shipping_delay') && ($o_delay = nxover_order_options_shipping_delay($item, $method_name, $order->get_id())) !== false)
                        $max_delai = max($max_delai, $o_delay);
                    // ]] NXOVER
                }
				Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delai,$supplier_name,$order);
				// Définissez et formatez $formattedCustomDate ici
				$method_id = $shipping_method->get_id();
				$custom_shipping_date = $order->get_meta('custom_shipping_date_' . $method_id, true);
				$custom_shipping_text = $order->get_meta('custom_shipping_text_' . $method_id, true);
				if (!empty($custom_shipping_date)) {
					$dateObject = DateTime::createFromFormat('Y-m-d', $custom_shipping_date);
					if ($dateObject) {
						$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
						$formattedCustomDate = ucwords($formatter->format($dateObject));
					}
				}
				$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
				$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));
                $html .= '<tr><td colspan="2" style="padding:5px 10px;background-color:#fcfcfc;"><b>Livraison ' . $count . ' :</b> ' . $method_name . '</td></tr>';
                $html .= '<tr><td colspan="2" style="padding:5px 10px;background-color:#fcfcfc;"><b>Expédié par ' . $supplier_name . '</b></td></tr>';
                if ( $order_status == 'on-hold' ) {
                    $html .= '<tr><td colspan="2" style="padding:5px 10px;background-color:#fcfcfc;"><b>Délai de livraison estimé :</b> en attente du paiement</td></tr>';
                } else {   
					if (!empty($custom_shipping_date)) {
						$html .= '<tr><td colspan="2" style="padding:5px 10px;background-color:#fcfcfc;"><b>Livraison le <b>' . $formattedCustomDate . '</td></tr>';
					}elseif (!empty($custom_shipping_text))  {
						$html .= '<tr><td colspan="2" style="padding:5px 10px;background-color:#fcfcfc;"><b>Livraison <b>' . $custom_shipping_text . '</td></tr>';
					}else{
						$html .= '<tr><td colspan="2" style="padding:5px 10px;background-color:#fcfcfc;"><b>Délai de livraison estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .'</td></tr>';
					}
                }
                $html .= $references;
                $html .= '</table><br>';
                $count += 1;
            }
        }
        echo $html;
    }
}

/**
 * Ajoute informations de rappel pr la livraison en bas des emails client
 */
add_action( 'woocommerce_email_after_order_table', 'add_order_email_instructions_bottom', 10, 2 );
function add_order_email_instructions_bottom( $order, $sent_to_admin ) {
    if ( !$sent_to_admin ) {
        $current_methods = [];
        $status = $order->get_status();
        $order_items = $order->get_meta('_wxp_shipment');
        $order_shipped_sku = get_post_meta($order->get_id(), '_shipped_sku', true);
        $drop_order = w_c_dropshipping()->orders->get_order_info($order);
        $shipping_methods = $drop_order['order']->get_shipping_methods();
        foreach ( $shipping_methods as $shipping_method ) {
            $shipping_name = $shipping_method->get_name();
            $shipping_items = $shipping_method->get_meta('Articles');
            if ( $status == 'partial-shipped' || $status == 'completed' ) {
                foreach ( $order_items as $item ) {
                    if ( strpos($shipping_items, $item['name']) !== false && strpos($order_shipped_sku, $item['sku']) !== false && !in_array($shipping_name, $current_methods) ) {
                        $current_methods[] = $shipping_name;
                    }
                }
            } elseif( !in_array($shipping_name, $current_methods) ) {
                $current_methods[] = $shipping_name;
            }
        }
        foreach ( $current_methods as $current_method ) {
            if ( 'Formule CLÉ EN MAIN' == $current_method ) {
                echo '<p style="font-size:8pt;border:solid 1px #b8b8b8;padding:6px;"><strong style="color:#DE2128;">Formule CLÉ EN MAIN [RAPPEL] :</strong> Livraison en VL ou véhicule type porteur, inclut la mise en place. Livraison installation mise en place complète (inclut montage pour les armoires Série PLUS KIT, les tables de réunion et les bureaux) et évacuation des emballages pour recyclage. <span style="color:#DE2128;"><br /><u>ATTENTION</u> : Ces frais de livraison s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur, ou bien en RDC). Dans le cas contraire la livraison sera interrompue et des frais supplémentaires pourront vous être présentés.</span></p>';
            } else if ( 'Formule CLÉ EN MAIN HARTMANN' == $current_method ){
                echo '<p style="font-size:8pt;border:solid 1px #b8b8b8;padding:6px;"><strong style="color:#DE2128;">Formule CLÉ EN MAIN HARTMANN [RAPPEL] :</strong> Livraison véhicule type porteur 19 Tonnes, inclut la mise en place. Livraison installation mise en place complète et évacuation des emballages pour recyclage. <span style="color:#DE2128;"><br /><u>ATTENTION</u> : Ces frais de livraison s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur, ou bien en RDC et roulage dsur 20 mètres maximum). Dans le cas contraire la livraison sera interrompue et des frais supplémentaires pourront vous être présentés.</span></p>';
            } else if ( 'Formule Messagerie HARTMANN' == $current_method ){
                echo '<p style="font-size:8pt;border:solid 1px #b8b8b8;padding:6px;"><strong style="color:#DE2128;">Formule Messagerie HARTMANN [RAPPEL] :</strong> Livraison véhicule type porteur 19 Tonnes, sans rendez-vous.Accès poids lourd obligatoire. <u>Dans le cas contraire la livraison sera interrompue et des frais supplémentaires pourront vous être présentés</u>.<br /><span  style="color:#DE2128;"><u>ATTENTION</u> : Déballage et controle des marchandises en présence du chauffeur. Déchargement et manutention par vos soins à partir du camion.</span></p>';
            } else if ( 'Formule ECO Plus' == $current_method ){
                echo '<p style="font-size:8pt;border:solid 1px #b8b8b8;padding:6px;"><strong style="color:#DE2128;">Formule ECO Plus [RAPPEL] :</strong> Livraison par semi-Remorque (AVEC HAYON).<br />Accès poids lourd 38 Tonnes obligatoire. <u>Dans le cas contraire la livraison sera interrompue et des frais supplémentaires pourront vous être présentés</u>.<br /><span  style="color:#DE2128;"><u>ATTENTION</u> : Déballage et controle des marchandises en présence du chauffeur. Déchargement et manutention par vos soins à partir du camion.</span></p>';
            } else if ( 'Formule ECO [OFFERTE]' == $current_method ){
                echo '<p style="font-size:8pt;border:solid 1px #b8b8b8;padding:6px;"><strong style="color:#DE2128;">Formule ECO [OFFERTE] [RAPPEL] :</strong> Livraison par semi-Remorque (SANS HAYON).<br />Accès poids lourd 38 Tonnes obligatoire. <u>Dans le cas contraire la livraison sera interrompue et des frais supplémentaires pourront vous être présentés</u>.<br /><span style="color:#DE2128;"><u>ATTENTION</u> : Déballage et controle des marchandises en présence du chauffeur. Déchargement et manutention par vos soins à partir du camion.</span></p>';
            }else if ( 'Formule ECO' == $current_method ){
                echo '<p style="font-size:8pt;border:solid 1px #b8b8b8;padding:6px;"><strong style="color:#DE2128;">Formule ECO [RAPPEL] :</strong> Livraison par semi-Remorque (SANS HAYON).<br />Accès poids lourd 38 Tonnes obligatoire. <u>Dans le cas contraire la livraison sera interrompue et des frais supplémentaires pourront vous être présentés</u>.<br /><span style="color:#DE2128;"><u>ATTENTION</u> : Déballage et controle des marchandises en présence du chauffeur. Déchargement et manutention par vos soins à partir du camion.</span></p>';
            }
        }
        echo '<p style="font-size:8pt;border:solid 1px #b8b8b8;padding:6px;"><strong style="color:#DE2128;">IMPORTANT :</strong> À l\'exception des produits Armoire Rideaux <i>SÉRIE KIT</i>, Tables de Réunion et Bureaux, <b>TOUS LES MEUBLES SONT MONOBLOCS</b> (assemblés au moment de la fabrication). Assurez-vous que le mobilier commandé pourra passer les portes, escaliers et ascenseurs. <u>Dans le cadre d\'articles refusés par défaut d\'accessibilité, les frais de retour seront à votre charge et les frais de livraison ne vous seront pas remboursés</u>.</p>';
    }
}
