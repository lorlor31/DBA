<?php
// framework/theme-functions.php
function et_send_msg_action() {
        $error_name  = false;
        $error_email = false;
        $error_msg   = false;
        $error_tel   = false;

        if(isset($_GET['contact-submit'])) {
            header("Content-type: application/json");
            $name = '';
            $tel = '';
            $email = '';
            $website = '';
            $message = '';
            $reciever_email = '';
            $return = array();

            if ( etheme_get_option('google_captcha_site') && etheme_get_option('google_captcha_secret') ) {
                if ( et_check_captcha($_GET['greresponse']) != true ) {
                    $return['status'] = 'error';
                    $return['msg'] = __('The security code you entered did not match. Please try again.', 'legenda');
                    echo json_encode($return);
                    die();
                }
            }

            if(trim($_GET['contact-name']) === '') {
                $error_name = true;
            } else{
                $name = trim($_GET['contact-name']);
            }

            if(trim($_GET['contact-email']) === '' || !isValidMail($_GET['contact-email'])) {
                $error_email = true;
            } else{
                $email = trim($_GET['contact-email']);
            }

            if(trim($_GET['contact-msg']) === '') {
                $error_msg = true;
            } else{
                $message = trim($_GET['contact-msg']);
            }
            if(trim($_GET['contact-tel']) === '') {
                $error_tel = true;
            } else{
                $tel = trim($_GET['contact-tel']);
            }

            $website = stripslashes(trim($_GET['contact-website']));

            // Check if we have errors

            if(!$error_name && !$error_email && !$error_msg && !$error_tel) {
                // Get the received email
                $reciever_email = etheme_get_option('contacts_email');

                $subject = 'Vous avez été contacté par ' . $name;

               // $body = "Vous avez été contacté par $name. Le message est: " . PHP_EOL . PHP_EOL;
                $body .= $message . PHP_EOL . PHP_EOL;
                $body .= $name . PHP_EOL . $tel . PHP_EOL;
				  // Get the referal
				$referal = $_COOKIE['referral'] ?? 'Indéfini';
                $body .= PHP_EOL . PHP_EOL . "<br><br><br>REFERAL: " . $referal;
                $body .= PHP_EOL . PHP_EOL;
                 // $body .= "You can contact $name via email at $email";
                if ($website != '') {
                    $body .= " et visité leur site : $website" . PHP_EOL . PHP_EOL;
                }
                $body .= PHP_EOL . PHP_EOL;

                $headers = "From $email ". PHP_EOL;
                $headers .= "Reply-To: $email". PHP_EOL;
                $headers .= "MIME-Version: 1.0". PHP_EOL;
                $headers .= "Content-type: text/plain; charset=utf-8". PHP_EOL;
                $headers .= "Content-Transfer-Encoding: quoted-printable". PHP_EOL;

                if(function_exists('et_mail') && et_mail($reciever_email, $subject, $body, $headers)) {
                    $return['status'] = 'success';
                    $return['msg'] = __('All is well, your email has been sent.', 'legenda');
                } else{
                    $return['status'] = 'error';
                    $return['msg'] = __('Error while sending a message!', 'legenda');
                }

            }else{
                // Return errors
                $return['status'] = 'error';
                $return['msg'] = __('Please, fill in the required fields!', 'legenda');
            }

            echo json_encode($return);
            die();
        }
    }
// framework/theme-functions.php
function etheme_top_links() {
    ?>
    <ul class="links">
        <?php if ( is_user_logged_in() ) : ?>
            <?php if(class_exists('Woocommerce')): ?> <li class="my-account-link"><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>"><?php esc_html_e( 'Your Account', 'legenda' ); ?></a>
                <div class="submenu-dropdown">
                    <?php  if ( has_nav_menu( 'account-menu' ) ) : ?>
                        <?php wp_nav_menu(array(
                            'theme_location' => 'account-menu',
                            'before' => '',
                            'after' => '',
                            'link_before' => '',
                            'link_after' => '',
                            'depth' => 4,
                            'fallback_cb' => false
                        )); ?>
                    <?php else: ?>
                        <h4 class="a-center install-menu-info">Set your account menu in <em>Apperance &gt; Menus</em></h4>
                    <?php endif; ?>
                </div>
                </li><?php endif; ?>
            <li class="logout-link"><a href="<?php echo wp_logout_url(home_url()); ?>"><?php esc_html_e( 'Logout', 'legenda' ); ?></a></li>
        <?php else : ?>
            <?php
            $reg_id = etheme_tpl2id('et-registration.php');
            $reg_url = get_permalink($reg_id);
            ?>
            <?php if(class_exists('Woocommerce')): ?><li class="login-link"><button onclick="location.href='<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>'"><?php esc_html_e( 'Sign In', 'legenda' ); ?></button></li><?php endif; ?>
            <?php if(!empty($reg_id)): ?><li class="register-link"><a href="<?php echo esc_url($reg_url); ?>"><?php esc_html_e( 'Register', 'legenda' ); ?></a></li><?php endif; ?>
        <?php endif; ?>
    </ul>
    <?php
}
// framework/theme-functions.php
   function et_page_heading(){
    $bk_style = etheme_get_option('breadcrumb_bg');

$style = '';
$bk_color = '';

if (!empty($bk_style['background-color'])) {
    $style .= 'background-color:' . $bk_style['background-color'] . '; ';
    $bk_color .= ' style="' . $style . '"';
}

if (!empty($bk_style['background-repeat'])) {
    $style .= 'background-repeat:' . $bk_style['background-repeat'] . '; ';
}

if (!empty($bk_style['background-attachment'])) {
    $style .= 'background-attachment:' . $bk_style['background-attachment'] . '; ';
}

if (!empty($bk_style['background-position'])) {
    $style .= 'background-position:' . $bk_style['background-position'] . '; ';
}

if(!empty($bk_style['background-image'])) {
    $style .= 'background-image: url(' . $bk_style['background-image'] . '); ';
}

?>
<div class="page-heading bc-type-<?php etheme_option('breadcrumb_type'); ?> " <?php echo 'style="'.$style.'"'; ?>>
        <?php echo '<div class="container"' . $bk_color . '>'; ?>

            <div class="row-fluid">
                <div class="span12 a-center">

                    <?php if (et_is_woo_exists()&&(is_woocommerce()||is_cart()||is_checkout()||is_product_category()||is_product_tag())): ?>
                            <?php
                                /**
                                 * woocommerce_before_main_content hook
                                 *
                                 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
                                 * @hooked woocommerce_breadcrumb - 20
                                 */
                                do_action('woocommerce_before_main_content');
                            ?>

                        <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

                            <?php if ( is_shop() || is_product_category() || is_product_tag() || is_tax() ): ?>
                                <?php echo '<h1 class="title"><span' . $bk_color . '>';
                                    woocommerce_page_title();
                                echo '</span></h1>'; ?>
                            <?php elseif ( !is_product() ) : ?>
                                <?php echo '<h1 class="title product_title"><span' . $bk_color .'>';
                                    the_title(); ?>
                                <?php echo '</span></h1>'; ?>
                            <?php endif; ?>

                        <?php endif; ?>

                    <?php else: ?>

                        <h1 class="title">
                            <?php echo '<span' . $bk_color . '>'; ?>
                                <?php
                                    if( is_home() && get_option( 'page_for_posts' ) ) {
                                        $postspage_id = get_option( 'page_for_posts' );
                                        echo get_the_title($postspage_id);
                                    } elseif ( is_search() ) {
                                        echo get_search_query();
                                    } elseif ( is_category() ) {
                                        $cat = get_category(get_query_var( 'cat' ), false);
                                        echo esc_html($cat->name);
                                    } elseif ( is_tag() ) {
                                        echo single_tag_title();
                                    } elseif ( is_author() ) {
                                        echo get_the_author();
                                    } elseif ( is_year() ) {
                                        echo get_the_time( 'Y' );
                                    } elseif ( is_month() ) {
                                        echo get_the_time( 'Y - F' );
                                    } elseif ( is_day() ) {
                                        echo get_the_time( 'Y - F - d' );
                                    } elseif ( is_404() ){
                                        esc_html_e( 'Not found', 'legenda' );
                                    } else {
                                        the_title();
                                    }
                                 ?>
                            </span>
                        </h1>
                            <?php etheme_breadcrumbs(); ?>
                    <?php endif ?>

                </div>
            </div>
        </div>
    </div>
<?php
   }
// framework/woo.php
function et_cart_summ() {
    ?>
    <button onclick="location.href='<?php echo wc_get_cart_url(); ?>'" class="cart-summ" data-items-count="<?php echo WC()->cart->get_cart_contents_count(); ?>">
        <div class="cart-bag">
            <?php echo wp_kses_data( sprintf( '<span class="badge-number">%1$u %2$s</span>', WC()->cart->get_cart_contents_count(), _nx( 'item for', 'items for', WC()->cart->get_cart_contents_count(), 'top cart items count text', 'legenda' ) ) );?>
            <span class="price-summ cart-totals"><?php wc_cart_totals_subtotal_html(); ?></span>
        </div>
    </button>
    <?php
}
// framework/woo.php
function etheme_wc_get_product_labels( $product_id = '' ) {
    global $product;
    $output = '';
    $count_labels = 0;
    if ( etheme_get_option('sale_icon') ):
        if ( $product->get_id() == "683" || $product->get_id() == "71"){
            $output .= $output .= '<span class="label-icon top-vente-label"></span>';
            $count_labels++;
        } elseif ($product->is_on_sale()) {
            $output .= '<span class="label-icon sale-label">'.__( 'Sale!', 'legenda' ).'</span>';
            $count_labels++;
        }
		if ($product->is_on_sale()) {
			if ($product->is_type('variable')) {
				$variations = $product->get_available_variations();
				foreach ($variations as $variation) {
					$prix_regulier = floatval($variation['display_regular_price']);
					$prix_promo = floatval($variation['display_price']);
					if ($prix_regulier > 0) {
						$pourcentage = round((($prix_regulier - $prix_promo) / $prix_regulier) * 100);
						echo '<div style="position: absolute; bottom: 0; right: 0; background-color: white; color: #c93004; padding: 10px 5px; border:solid 1px #c93004;border-radius: 50%;z-index:1;">-' . $pourcentage . '%</div>';
					}
				}
			} else {
				$prix_regulier = floatval($product->get_regular_price());
				$prix_promo = floatval($product->get_sale_price());
				if ($prix_regulier > 0) {
					$pourcentage = round((($prix_regulier - $prix_promo) / $prix_regulier) * 100);
					echo '<div style="position: absolute; bottom: 0; right: 0; background-color: white; color: #c93004; padding: 10px 5px; border:solid 1px #c93004;border-radius: 50%;z-index:1;">-' . $pourcentage . '%</div>';
				}
			}
		}
    endif;
    if ($product->get_attribute('pa_delai-dexpedition') == 'Sous 0 semaine'){
        $position_top = 'position-'. $count_labels;
        $output.= '<span class="label-icon livr-rapide-label '. $position_top. '"></span>';
        $count_labels++;
    }
    $shipping_meta = get_post_meta( $product->get_id(), 'free_shipping', true );
    if ( !empty($shipping_meta) ):
        $position_top = 'position-' . $count_labels;
        $output .= '<span class="label-icon shipping-label ' . $position_top . '"></span>';
        $count_labels++;
    endif;
    if ( etheme_get_option('new_icon') ):
        if ( etheme_product_is_new($product_id) ):
            switch ( get_post_meta( $product->get_id(), 'label_garantie', true ) ) {
                case '2':
                    $class = 'label-2-ans';
                    break;
				case '3':
					$class = 'label-3-ans';
					break;
                case '5':
                    $class = 'label-5-ans';
                    break;
                default:
                    $class = '';
                    break;
            }
			$position_top = 'position-' . $count_labels;
            $output .= '<span class="label-icon new-label ' . $class . ' ' . $position_top . '">'.__( 'New!', 'legenda' ).'</span>';
			$count_labels++;
        endif;
		$label_inflation = get_post_meta( $product->get_id(), 'label_inflation', true );
		if ( $label_inflation == 'enable' ):
			$position_top = 'position-' . $count_labels;
			$output .= '<span class="label-icon label_inflation ' . $position_top . '"></span>';
		endif;
		$origin_meta = get_post_meta( $product->get_id(), 'country_origin', true );
		if ( empty($origin_meta) || $origin_meta == 'fr' ):
			$output .= '<span class="label-icon origin-label"></span>';
		endif;
    endif;
    return $output;
}
