<?php
/**
// Ajouter Clé passe pour vestiaire serrure à code à molette
*
**/
add_action( 'woocommerce_add_to_cart', 'verifier_et_ajouter_cle_passe_partout_avec_attribut_debug', 10, 6 );
function verifier_et_ajouter_cle_passe_partout_avec_attribut_debug( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
    $id_article_ajouter = 45388; // Remplacez par l'ID réel de la clé passe-partout

    // Log pour confirmer l'ajout au panier
    error_log("Produit ajouté au panier: ID produit $product_id, ID variation $variation_id");

    if ( $variation_id ) {
        $produit = wc_get_product( $variation_id );

        // Récupère les attributs spécifiques à la variation
        $attributs_variation = $produit->get_attributes();

        foreach ( $attributs_variation as $attribut_key => $value ) {
            // Log pour chaque attribut de la variation
            error_log("Attribut variation: $attribut_key => $value");

            if ( strpos( $attribut_key, 'pa_' ) === 0 ) {
                $attribut_clean_key = str_replace( 'attribute_', '', $attribut_key );

                // Log pour confirmer la reconnaissance de l'attribut
                error_log("Attribut reconnu: $attribut_clean_key avec la valeur $value");

                if ( 'pa_serrure-vestiaire' === $attribut_clean_key && 'Serrure à code 4 molettes' === $value ) {
                    // Vérifie si la clé passe-partout n'est pas déjà dans le panier
                    if ( !is_cart_contains_product_id( $id_article_ajouter ) ) {
                        WC()->cart->add_to_cart( $id_article_ajouter );
                        // Log pour confirmer l'ajout de la clé passe-partout
                        error_log("Clé passe-partout ajoutée au panier.");
                    }
                }
            }
        }
    }
}

// Fonction pour vérifier si le panier contient déjà le produit spécifié par son ID
function is_cart_contains_product_id( $product_id ) {
    foreach( WC()->cart->get_cart() as $cart_item ) {
        if ( $cart_item['product_id'] == $product_id ) {
            error_log("Le panier contient déjà le produit ID $product_id."); // Log pour confirmer la présence du produit
            return true;
        }
    }
    return false;
}


    // Cart and minicart 
    add_filter( 'woocommerce_cart_item_price', 'change_cart_item_price_html', 10, 3 );
    function change_cart_item_price_html( $price_html, $cart_item, $cart_item_key ) {
        if( $cart_item['data']->get_price() == 0 ) {
            return '<span class="woocommerce-Price-amount amount">Offert</span>';
        }
        return $price_html;
    }
 
    /**
     *  popup devis sous panier à cacher
     */
    if(!function_exists('etheme_top_cart')) {
        // function etheme_top_cart($content = true) {
        function etheme_top_cart($content = false) {
            ?>
            <div class="shopping-cart-widget a-right" <?php if(etheme_get_option('favicon_badge')) echo 'data-fav-badge="enable"' ?>>
                <?php et_cart_summ(); ?>
                <?php if ($content) : ?>
                    <div class="widget_shopping_cart_content">
                        <?php woocommerce_mini_cart(); ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php
        }
    }

    /**
     * Ajouter un lien vers la page livraison dans le total du panier
    */
    add_action( 'woocommerce_cart_totals_before_shipping', 'add_delivery_link_cart' );
    function add_delivery_link_cart() {
        echo '<tr class="woocommerce-delivery-info"><td colspan="2"><h3>Livraison</h3><a href="/livraison/">(tout savoir)</a></td></tr>';
    }

   /**
     * Affiche informations réassurance sous le panier + bandeau devis
     */
    add_action( 'woocommerce_after_add_to_cart_form', 'wc_reassurance_panier', 10 );
    function wc_reassurance_panier(){
        global $product;
        $id_prod = $product->get_id();
        $name_prod = $product->get_name();
        $brand_name = wp_get_post_terms( $id_prod, 'brand', array( 'fields' => 'names' ) );
		$supplier_name = '';
		$supplier_meta = get_post_meta($id_prod, 'supplier', true);
        $is_vinco = $brand_name[0] == 'VINCO';
        $product_cats = wp_get_post_terms( $id_prod, 'product_cat');
		$delai_init = wp_get_post_terms( $id_prod, 'pa_delai-dexpedition', array( 'fields' => 'names' ) );
        $delai = intval(preg_replace('/[^0-9.]/', '', ''. $delai_init[0] ) );
        $delai = Livraison::getInstance()->getFabrication($id_prod, false, [], $delai);
		$date_livraison = new DateTime('now', new DateTimeZone('Europe/Paris'));
        $date_livraison_max = new DateTime('now', new DateTimeZone('Europe/Paris'));
		Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$delai,$supplier_meta,null);
		$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMMM', 'fr' ));
        $array_cat = array();
        $array_execption_drop = array('accessoires','equipements');
        $array_execption_drop_pdt_name = array('LOT de 2 TABLETTES','Tablette Armoire PB / PP','Support UC à fixer sous plateau','Ecran de séparation','Voile de Fond Bureau Ligne ÉLÉGANCE','Convivialité Demi-Lune');
        $array_echantillon = array('armoire-de-bureau','bureau-plus');
        $array_2ans = array('casier','accessoires','equipements');
        echo '<div class="info_cart_bottom_button"><div class="info_cart_check">';
        // Picto livraison gratuite page produit
        $shipping_mode = get_post_meta( $product->get_id(), 'free_shipping', true );
        if ( $shipping_mode == 'installation' ) {
            echo '<div class="free-shipping"><img src="/wp-content/themes/legenda-child/img/icone_livraison-gratuite.svg" width="40" height="30" alt="Livraison gratuite"><div class="detail-shipping"><h4>Livraison et installation gratuite</h4><span>Installation et mise en place en rez de chaussée</span></div></div>';
        }elseif ( $shipping_mode == 'simple_hartmann' ) {
            echo '<div class="free-shipping"><img src="/wp-content/themes/legenda-child/img/icone_livraison-gratuite.svg" width="40" height="30" alt="Livraison gratuite"><div class="detail-shipping"><h4>Livraison gratuite</h4><span>Mise en place par vos soins</span></div></div>';			
		}
        $pluriel = $delai > 1 ? 's' : '';
        if ( $is_vinco ) {
	        echo '<i class="icon-calendar" style="color:#0b4797;font-size:15px;padding-right:5px;margin-left:7px;"></i> <span>Livraison dès le  <strong> ' . $date_livraison . '</strong> <i class="icon-question  tooltip1"><i class="tooltiptext1">Délai pouvant être ajustés dans votre panier suivant le code postal de livraison renseigné </i></i></span><br>';
            echo '<i class="icon-check" style="color:#0b4797;font-size:15px;padding-right:5px;margin-left:7px;"></i> <span>Départ Direct Usine Française</span><br>';
        } else {
	        echo '<i class="icon-calendar" style="color:#0b4797;font-size:15px;padding-right:5px;margin-left:7px;"></i> <span>Livraison dès le  <strong> ' . $date_livraison . '</strong> <i class="icon-question tooltip1"><i class="tooltiptext1">Délai pouvant être ajustés dans votre panier suivant le code postal de livraison renseigné </i></i></span><br>';
        }
        foreach( $product_cats as $key => $cat ) {
            $array_cat[] = $cat->slug ;
        }
        if ( $is_vinco && array_intersect($array_execption_drop, $array_cat) == null && !in_array($name_prod, $array_execption_drop_pdt_name) ) {
            echo '<i class="icon-check" style="color:#0b4797;font-size:15px;padding-right:5px;margin-left:7px;"></i> <span>Option Service Installation <b>Clés en Main</b> <i class="icon-question tooltip1"><i class="tooltiptext1">+1 Semaine de délai. Délai pouvant être ajustés dans votre panier suivant le code postal de livraison renseigné </i></i></span><br>';
        }
        if ( $is_vinco && array_intersect($array_echantillon, $array_cat) != null && array_intersect($array_2ans, $array_cat) == null && !in_array($name_prod, $array_execption_drop_pdt_name) ) {
            echo '<i class="icon-check" style="color:#0b4797;font-size:15px;padding-right:5px;margin-left:7px;"></i> Echantillons de Couleur <a href="/contact"><b>sur demande</b></a><br>';
        }
        echo '</div></div>';
    }

        /**
         * Action: ajouter la reassurance paiement dans le panier + Location financiere
         */
        //add_action( 'woocommerce_after_cart_totals', 'add_finanacement_cart', 10 );
        //add_action( 'woocommerce_review_order_before_payment', 'add_finanacement_cart', 10 );
        function add_finanacement_cart(){
            $cart_total_ht = floatval( preg_replace( '#[^\d.]#', '', WC()->cart->get_total_ex_tax() ) );
            $vingt_quatre_mois = number_format($cart_total_ht * 4.9/100, 2, ',', ' ');
            $trente_six_mois = number_format($cart_total_ht * 3.49/100, 2, ',', ' ');
            $quarante_huit_mois = number_format($cart_total_ht * 2.8/100, 2, ',', ' ');
            $soixante_mois = number_format($cart_total_ht * 2.44/100, 2, ',', ' ');
            if ($cart_total_ht>2000){
                echo '
            <table class="financemt_byme" style="background: #fafafa;border: #dddddd 1px solid;text-align:center;margin-top:15px;">
                <tr>
                    <td colspan="4"><span style="color:#0B4797;font-size:16px;font-weight:bold;">Financement Locatif</span><span style="font-size:12px;font-style:italic;"> (Estimation)</span></td>
                </tr>
                <tr class="nb_mensualite">
                    <td>
                        <span>24x <b>'.$vingt_quatre_mois.'€</b></span>
                    </td>
                    <td>
                        <span>36x <b>'.$trente_six_mois.'€</b></span>
                    </td>
                    <td>
                        <span>48x <b>'.$quarante_huit_mois.'€</b></span>
                    </td>
                    <td>
                        <span>60x <b>'.$soixante_mois.'€</b></span>
                    </td>
                </tr>
                <tr class="etude_fin">
                    <td colspan="4"><span style="color:#0B4797;font-size:10px;margin:auto;">Demandez un devis - <a href="https://www.armoireplus.fr/contact/">En savoir plus</a></span></td>
                </tr>
            </table>
        ';
            } else {
                $deuxfois = number_format($cart_total_ht / 2, 2, ',', ' ');
                $deuxfois_tax = number_format($cart_total_ht * 0.007, 2, ',', ' ');
                $deuxfois_first = $deuxfois + $deuxfois_first;
                $troisfois = number_format($cart_total_ht / 3, 2, ',', ' ');
                $troisfois_tax = number_format($cart_total_ht * 0.014, 2, ',', ' ');
                $troisfois_first = $troisfois + $troisfois_tax;
                $quatrefois = number_format($cart_total_ht / 4, 2, ',', ' ');
                $quatrefois_tax = number_format($cart_total_ht * 0.021, 2, ',', ' ');
                $quatrefois_first = $quatrefois + $quatrefois_tax;
                echo '
                <table class="financemt_byme" style="background: #fafafa;border: #dddddd 1px solid;text-align:center;margin-top:15px;">
                <tr>
                    <td colspan="4"><span style="color:#0B4797;font-size:16px;font-weight:bold;">Facilité de paiement</span></td>
                </tr>		
                <tr class="nb_mensualite">
                    <td>
                        <span><b>2x '.$deuxfois.'€</b> +'.$deuxfois_tax.'€</span>
                    </td>
                    <td>
                        <span><b>3x '.$troisfois.'€</b> +'.$troisfois_tax.'€</span>
                    </td>
                    <td>
                        <span><b>4x '.$quatrefois.'€</b> +'.$quatrefois_tax.'€</span>
                    </td>
                </tr>
                <tr class="etude_fin">
                    <td colspan="4"><span style="color:#0B4797;font-size:10px;margin:auto;">Une solution ALMA </span></td>
                </tr>
            </table>
            ';
            }
        }

        /**
         * Ajouter la reassurance paiement dans le panier + Location financiere
         */
        add_action( 'woocommerce_after_cart_totals', 'add_reassusrance_cart', 10 );
        function add_reassusrance_cart(){
            echo '<div><i class="icon-ok" style="color:#007F15;font-size:15px;padding-right:5px;margin-left:7px;margin-bottom:7px;"></i> <b>PAIEMENT SÉCURISÉ</b><br>';
            echo '<img class="vc_single_image-img" src="https://www.armoireplus.fr/wp-content/uploads/2022/10/moyens-paiement-armoireplus_4.png" width="338" height="72" alt="moyen-de-paiement-armoireplus" title="moyen-de-paiement-armoireplus"></div>';
            echo '<div><i class="icon-ok" style="color:#007F15;font-size:15px;padding-right:5px;margin-left:7px;"></i> <b>GARANTIE ARMOIRE PLUS</b> : Parce qu\'une garantie est essentielle à votre satisfaction, nous présentons jusqu\'à 10 ans de garantie sur l\'ensemble de notre catalogue.</div>';
        }

        
        /**
         * Ajouter un lien vers la page livraison dans le total du checkout
         */
        add_action( 'woocommerce_review_order_before_shipping', 'add_delivery_link_checkout' );
        function add_delivery_link_checkout() {
            echo '<tr class="woocommerce-delivery-info"><td colspan="2"><h3>Méthode de livraison</h3><button type="button" class="popup_livraison pum-trigger">Comparatif des formules</button><button type="button" class="popup_acces pum-trigger">Accès escaliers et ascenseurs</button></td></tr>';
        }

        /**
         * Afficher réassurance panier / produit
         */
        add_action( 'woocommerce_after_cart_table', 'add_reassurance_armoireplus' );
        function add_reassurance_armoireplus() {
            echo do_shortcode('[block id="35047"]');
        }

        /**
         * Desactive ville calcul frais de port
         */
        add_filter( 'woocommerce_shipping_calculator_enable_city', '__return_false' );

        /**
         * Show Regular/Sale Price @ WooCommerce Cart Table
         */
        add_filter( 'woocommerce_cart_item_price', 'bbloomer_change_cart_table_price_display', 30, 3 );
        function bbloomer_change_cart_table_price_display( $price, $values, $cart_item_key ) {
            $slashed_price = $values['data']->get_price_html();
            $is_on_sale = $values['data']->is_on_sale();
            if ( $is_on_sale ) {
                $price = $slashed_price;
            }
            return $price;
        }

        /**
         * Display cart total weight on the cart page
         */
        add_action('woocommerce_before_cart_totals', 'myprefix_cart_extra_info');
        function myprefix_cart_extra_info() {
            global $woocommerce;
            $volume = 0;
            $cart_items = WC()->cart->get_cart();
            $volume += $woocommerce->cart->cart_contents_weight;
            if ( $volume != 0 ) {
                $html = '<div class="poids-extra-info"><p class="total-poids">Poids Total ' . $volume . ' ' . get_option('woocommerce_weight_unit') . '</p></div>';
                echo $html;
            }
        }

    /**
     * Hide bouton devis rapide (pdf) sur le panier Module cart to pdf utilisé pour les cde frs en BO
     */
    remove_action( 'woocommerce_proceed_to_checkout', 'wc_cart_pdf_button', 21 );

    
    /**
     * CHECKOUT
     */

    /**
    * Change texte bouton commande
    */
    add_filter( 'woocommerce_available_payment_gateways', 'woocommerce_available_payment_gateways' );
    function woocommerce_available_payment_gateways( $available_gateways ) {
        if (! is_checkout() ) return $available_gateways;  // stop doing anything if we're not on checkout page.
        if (array_key_exists('cheque',$available_gateways)) {
            $available_gateways['cheque']->order_button_text = __( 'Valider et Payer par Chèque', 'woocommerce' );
        }
        if (array_key_exists('cod',$available_gateways)) {
            $available_gateways['cod']->order_button_text = __( 'Valider la Commande', 'woocommerce' );
        }
        if (array_key_exists('bacs',$available_gateways)) {
            $available_gateways['bacs']->order_button_text = __( 'Valider et Payer par Virement', 'woocommerce' );
        }
        if (array_key_exists('paypal-pro-hosted',$available_gateways)) {
            $available_gateways['paypal-pro-hosted']->order_button_text = __( 'Valider et Payer par Paypal', 'woocommerce' );
        }
        if (array_key_exists('atos2',$available_gateways)) {
            $available_gateways['atos2']->order_button_text = __( 'Valider et Payer par CB', 'woocommerce' );
        }
        if (array_key_exists('mercanet_onetime',$available_gateways)) {
            $available_gateways['mercanet_onetime']->order_button_text = __( 'Valider et Payer par CB', 'woocommerce' );
        }
        if (array_key_exists('heroPay3X',$available_gateways)) {
            $available_gateways['heroPay3X']->order_button_text = __( 'Valider et Payer en 3 fois par CB [SIRET obligatoire]', 'woocommerce' );
        }
        return $available_gateways;
    }

        

        /**
         *  Panier + checkout
         */

        /**
         * SD Tiroir : renders notices and prevents checkout
         */
        add_action( 'woocommerce_check_cart_items', 'check_category_for_minimum' );
        function check_category_for_minimum() {
            $category_id = 338;
            $modulo_quantity = 3;
            $category_quantity = get_category_quantity_in_cart ( $category_id );
            if ( $category_quantity % $modulo_quantity != 0 ) {
                wc_add_notice( 'Ces kits sont fait pour être installés par 3. Commander minimum 3 kits ou un multiple de 3 pour constituer un module complet.', 'error' );
            }
        }
        function get_category_quantity_in_cart( $category_id ) {
            $card = WC()->cart->get_cart();
            $category_quantity = 0;
            foreach ( $card as $cart_item_key => $cart_item ) {
                if ( has_term( $category_id, 'product_cat', $cart_item['product_id'] ) ) {
                    $category_quantity += $cart_item['quantity'];
                }
            }
            return $category_quantity;
        }

        /**
         * Check zip code to command pour ouvrir le module automatiquement
         */
        add_action( 'woocommerce_check_cart_items', 'check_shipping_postcode' ); // Cart and Checkout
        function check_shipping_postcode() {
            $active_script = false;
            $customer = WC()->session->get('customer');
            foreach ( WC()->shipping->get_packages() as $key => $package ) {
                if (empty($package['rates'])){ $active_script = true; }
            }
            if( ( $customer['shipping_postcode'] ) == '' || $active_script == true){
                echo '<script>
                jQuery(function(){  
                    jQuery(".woocommerce-cart #ywraq_cart_quote").on("click",function(e){
                        e.preventDefault();
                        var _this = this;
                        jQuery(".shipping-calculator-form").css("display","block");
                        jQuery("table.shipping__table").css("border","1px solid #F2522A");
                        jQuery(".shipping-calculator-form .button").on("click",function(){
                            jQuery(_this).off();
                        });
                    });
                });
            </script>';
            }
        }

    // Cart and Checkout 
    add_filter( 'woocommerce_cart_item_subtotal', 'change_checkout_item_subtotal_html', 10, 3 );
    function change_checkout_item_subtotal_html( $subtotal_html, $cart_item, $cart_item_key ) {
        if( $cart_item['data']->get_price() == 0 ) {
            return '<span class="woocommerce-Price-amount amount">Offert</span>';
        }
        return $subtotal_html;
    }