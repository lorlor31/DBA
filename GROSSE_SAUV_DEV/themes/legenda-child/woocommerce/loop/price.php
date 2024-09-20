<?php
/**
 * Loop Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;

	// Code rajouter par Gabriel / modifie l'affichage du prix en prenant en compte les promotions en Bo et par url avec le module
	/*
	Code de base : */
	 if ( $price_html = $product->get_price_html() ) : ?>
	<span class="price"><?php echo $price_html; ?></span>
	<?php endif; 
	/*
	$html = '';
	$taux_reduc = false;
	if ( $price_html = $product->get_price_html() ) { 
		preg_match_all('/<span class="woocommerce-Price-amount amount">(.*?)<\/span>/', $price_html, $matches);
				$test = $matches;
				if (isset($test[0][0])){
					$value_00 = preg_replace('/[^\d.,€]/', '', $test[0][0]);
					$value_00 = str_replace(',', '',$value_00 ); 
				}
				if (isset($test[0][1])){
					$value_01 = preg_replace('/[^\d.,€]/', '', $test[0][1]);
					$value_01 = str_replace(',', '',$value_01 ); 
				}
				if (isset($test[0][2])){
					$value_02= preg_replace('/[^\d.,€]/', '', $test[0][2]);
					$value_02 = str_replace(',', '',$value_02 ); 
				}
			 if ($product->get_type() == 'variable' ) { 
				if (count($test[0])== 1) {
				$html .= '<span class="price">' .$test[0][0]. '<span class="suffix_euro">HT</span></span> </span>';
				 } elseif (count($test[0])== 2){
					if ($product->is_on_sale()){
						$min_var_reg_price = $product->get_variation_regular_price( 'min', true );
						$min_var_sale_price = $product->get_variation_sale_price( 'min', true );
						$max_var_reg_price = $product->get_variation_regular_price( 'max', true );
						$max_var_sale_price = $product->get_variation_sale_price( 'max', true );
						if ( !($min_var_reg_price == $max_var_reg_price && $min_var_sale_price == $max_var_sale_price) ) {
							if ( $min_var_sale_price < $min_var_reg_price ) { 
								$html .= '<span class="price" style="margin:0;"><span class="apartirde">À PARTIR DE &nbsp;</span><del style="margin-right:5px;" aria-hidden="true"><span class="woocommerce-Price-amount amount"><bdi>' . $min_var_reg_price . '&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></bdi></span></del><in><span class="woocommerce-Price-amount amount"><bdi>' . $min_var_sale_price. '&nbsp;<span class="woocommerce-Price-currencySymbol">€</span></bdi></span></in><span class="suffix_euro">HT</span></span></span> </span>';
								$prix_reduc = floatval($min_var_reg_price) - floatval($min_var_sale_price);
								$taux_reduc = ( $prix_reduc / floatval($min_var_reg_price)) * 100;
								$taux_reduc = round($taux_reduc);
							}
						}
					}else {
					
						if (floatval($value_00) > floatval($value_01)) {
							$html .= '<span class="price"><span class="apartirde">À PARTIR DE &nbsp;</span><del style="margin-right:5px;">' . $test[0][0]. '</del>'. $test[0][1]. '<span class="suffix_euro">HT</span></span></span> ';
							$prix_reduc = floatval($value_00) - floatval($value_01);
							$taux_reduc = ($prix_reduc / floatval($value_00)) * 100;
							$taux_reduc = round($taux_reduc);
						}elseif (floatval($value_01) > floatval($value_00)) {
							$html .= '<span class="price"><span class="apartirde">À PARTIR DE &nbsp;</span>'. $test[0][0].'<span class="suffix_euro">HT</span></span></span>';
						}
					}
				}elseif (count($test[0])== 4){
					$html .= '<span class="price"><span class="apartirde">À PARTIR DE &nbsp;</span><del style="margin-right:5px;">'.$test[0][0].'</del>'. $test[0][2].'<span class="suffix_euro">HT</span></span> </span>';
					$prix_reduc = floatval($value_00) - floatval($value_02);
					$taux_reduc = ( $prix_reduc / floatval($value_00) ) * 100;
					$taux_reduc = round($taux_reduc);
			}
		}else {
			if (count($test[0])== 1){
				$html .= '<span class="price">'.$test[0][0].'<span class="suffix_euro">HT</span></span> </span>';
			}elseif (count($test[0])== 2){
				$html .= '<span class="price"><del style="margin-right:5px;">'. $test[0][0].'</del>'. $test[0][1].'<span class="suffix_euro">HT</span></span></span>';
				$prix_reduc = floatval($value_00) - floatval($value_01);
				$taux_reduc = ( floatval($prix_reduc) / floatval($value_00) ) * 100;
				$taux_reduc = round($taux_reduc);
			}
		}
	}
	global $post;
	if ( $post->post_type == 'product' ) {
		$tva = 1.2;
		if ( !is_product_category() && !is_shop() && !is_page('bon-plan') ) {
			if ($product->get_type() == 'variable' ) { 
				if (count($test[0])== 1) {
					$value_00 = preg_replace('/[^\d.,€]/', '', $test[0][0]);
					$value_00 = str_replace(',', '',$value_00 ); 
					$produit_tva = floatval($value_00) * $tva; 
				} elseif (count($test[0])== 2){
					if ($product->is_on_sale()){
						$min_var_reg_price = $product->get_variation_regular_price( 'min', true );
						$min_var_sale_price = $product->get_variation_sale_price( 'min', true );
						$max_var_reg_price = $product->get_variation_regular_price( 'max', true );
						$max_var_sale_price = $product->get_variation_sale_price( 'max', true );
						if ( !($min_var_reg_price == $max_var_reg_price && $min_var_sale_price == $max_var_sale_price) ) {
							if ( $min_var_sale_price < $min_var_reg_price ) { 
								$produit_tva = $min_var_sale_price * $tva;
							}
						}
					}else {
						$value_00 = preg_replace('/[^\d.,€]/', '', $test[0][0]);
						$value_00 = str_replace(',', '',$value_00 ); 
						$value_01 = preg_replace('/[^\d.,€]/', '', $test[0][1]);
						$value_01 = str_replace(',', '',$value_01 ); 
						if (floatval($value_00) > floatval($value_01)) {
							$produit_tva = floatval($value_01) * $tva; 
						}elseif (floatval($value_01) > floatval($value_00)) {
							$produit_tva = floatval($value_00) * $tva; 
						}
					}
				}elseif (count($test[0])== 4){
					$value_02 = preg_replace('/[^\d.,€]/', '', $test[0][2]);
					$value_02 = str_replace(',', '',$value_02 ); 
					$produit_tva = $value_02 * $tva;
				}
			}else {
				$value_00 = preg_replace('/[^\d.,€]/', '', $test[0][0]);
				$value_00 = str_replace(',', '',$value_00 ); 
				$value_01 = preg_replace('/[^\d.,€]/', '', $test[0][1]);
				$value_01 = str_replace(',', '',$value_01 ); 
				if (count($test[0])== 1){
					$produit_tva = floatval($value_00) * $tva;
				}elseif (count($test[0])== 2){
					$produit_tva = floatval($value_01) * $tva; 
				}
			}
			$html .= '<span class="shop_ttc">'.number_format($produit_tva, 2, '.', ' ').'  &euro;<span class="suffix_euro">TTC</span>';
			$fee_datacomp = ['amount' => get_post_meta( $product->get_parent_id(), 'product-fee-amount', true )];
			$fee_data = ['amount' => get_post_meta( $product->get_id(), 'product-fee-amount', true )];
			if ( $fee_datacomp['amount'] != false ) {
				$fee_datacomp_format = number_format($fee_datacomp['amount'] * $tva, 2, '.', ' ');
				$html .= '<span class="eco_contribution_temp">éco-participation'. $fee_datacomp_format .' &euro;'. do_shortcode('[icon name="leaf" Size="10" Color="#008912"]') .' </span>';
			} elseif ( $fee_data['amount'] != false ) {
				$fee_data_format = number_format($fee_data['amount'] * $tva, 2, '.', ' ');
				$html .= '<span class="eco_contribution">éco-participation'. $fee_data_format .' &euro;'. do_shortcode('[icon name="leaf" Size="10" Color="#008912"]') .'</span>';
			}
			$html .= '</span>';
		}
		if ( is_product() ) {
			if ($taux_reduc != false){
				$html .= '<div class="economie">Vous économisez '. $prix_reduc .' € (' . $taux_reduc  .'%)</div>';
			}
		}
		if ( (is_woocommerce() || is_page('bon-plan')) && $product->is_on_sale() ) {
			if ($product->get_id() == 683 || $product->get_id() == 71) {
				$html .= '<span class="fin-promo">Prix bloqués</span>';
			}else{
				$date_end = date('t/m/Y');
				$html .= "<span class='fin-promo'>Valable jusqu'au ".$date_end ."</span>";
			}
		}
		echo $html;
	}*/
	