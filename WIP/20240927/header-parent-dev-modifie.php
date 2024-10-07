<!doctype html>
<html <?php language_attributes(); ?> <?php et_html_tag_schema(); ?>>
<head>
    <?php global $etheme_responsive, $woocommerce; ?>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
    <?php if($etheme_responsive): ?>
	    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
	<?php else: ?>
		<meta name="viewport" content="width=1200">
	<?php endif; ?>
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE10" >
	<link rel="preconnect" href="https://consentcdn.cookiebot.com">
		<?php
			if ( is_singular() && get_option( 'thread_comments' ) )
				wp_enqueue_script( 'comment-reply' );

			wp_head();
		?>

	    <!-- CMP Cookiebot -->
		<script data-cookieconsent="ignore">
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('consent', 'default', {
            ad_storage: 'denied',
            analytics_storage: 'denied',
            functionality_storage: 'denied',
            personalization_storage: 'denied',
            security_storage: 'granted',
            wait_for_update: 2000,
        });
        gtag('set', 'ads_data_redaction', true);
    </script>
	   <?php if ( !is_checkout() ): ?>
			<script id="Cookiebot" src="https://consent.cookiebot.com/uc.js" data-cbid="0201ef7d-6289-4dd9-9402-06f8ac76638f" data-blockingmode="auto" type="text/javascript" defer="defer"></script>
	   <?php endif; ?>
<!-- Hotjar Tracking Code for www.armoireplus.fr -->
<?php 
// if ( is_front_page() || is_product_category() || is_product() || is_cart() || is_checkout() ) :
if ( 
// is_front_page() 
    // || (is_product_category() && (get_queried_object()->slug == 'vestiaire-metallique'))
    // || (is_product() && strpos(get_permalink(), '/boutique/vestiaire-metallique/') !== false) 
    // || is_cart() 
     is_cart() 
    || is_checkout() 
) :
?>

<script>
   (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:2460531,hjsv:6};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
<?php endif; ?></head>
<?php
	$ht = $ht_class =''; $ht = apply_filters('custom_header_filter',$ht);

	if ($ht == 9) {
	 	$ht = $ht_class = 'transparent';
	}
	$search_form = etheme_get_option('search_form');
	$cart_widget = etheme_get_option('cart_widget');
	$top_links = etheme_get_option('top_links');
?>
<body <?php body_class($ht_class); ?>>
	<?php 
		if ( function_exists( 'wp_body_open' ) ) {
		    wp_body_open();
		} else {
		    do_action( 'wp_body_open' );
		} 
	?>
	<?php if(etheme_get_option('mobile_loader')): ?>
		<div class="mobile-loader hidden-desktop">
			<div id="floatingCirclesG"><div class="f_circleG" id="frotateG_01"></div><div class="f_circleG" id="frotateG_02"></div><div class="f_circleG" id="frotateG_03"></div><div class="f_circleG" id="frotateG_04"></div><div class="f_circleG" id="frotateG_05"></div><div class="f_circleG" id="frotateG_06"></div><div class="f_circleG" id="frotateG_07"></div><div class="f_circleG" id="frotateG_08"></div></div>
			<h5><?php _e('Loading the content...', 'legenda'); ?></h5>
		</div>
	<?php endif; ?>

	<div class="mobile-nav side-block">
		<div class="close-mobile-nav close-block"><?php esc_html_e('Navigation', 'legenda') ?></div>
		<?php
			wp_nav_menu(array(
				'theme_location' => 'mobile-menu',
				'walker' => new Et_Navigation_Mobile
			));
		?>

		<?php if ($top_links): ?>
			<div class="mobile-nav-heading"><i class="fa fa-user"></i><?php esc_html_e('Account', 'legenda'); ?></div>
			<?php etheme_top_links(array('popups' => false)); ?>
		<?php endif; ?>

		<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('mobile-sidebar')): ?>

		<?php endif; ?>

		<?php if ($search_form): ?>
			<div class="search">
					<?php echo etheme_search(array()); ?>
			</div>
		<?php endif ?>
	</div>

	<?php if(etheme_get_option('right_panel')): ?>
		<div class="side-area side-block hidden-phone hidden-tablet">
			<div class="close-side-area close-block"><i class="icon-remove"></i></div>
			<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('right-panel-sidebar')): ?>

				<div class="sidebar-widget">
					<h6><?php _e('Add any widgets you want in Apperance->Widgets->"Right side panel area"', 'legenda') ?></h6>
				</div>

			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php $ht = $ht_class = ''; $ht = apply_filters('custom_header_filter',$ht); ?>


	<?php if (etheme_get_option('fixed_nav')): ?>
		<?php $f_color = 'header-color-';
		$f_color .= etheme_get_option('fixed_header_color'); ?>
		<div class="fixed-header-area fixed-menu-type<?php etheme_option('menu_type'); ?> <?php echo esc_attr($f_color); ?>">
			<div class="fixed-header">
				<div class="container">
					<div class="menu-wrapper">

					    <div class="menu-icon hidden-desktop"><i class="icon-reorder"></i></div>
						<div class="logo-with-menu">
							<?php etheme_logo( 'fixed' ); ?>
						</div>

						<div class="modal-buttons">
							<?php if (class_exists('Woocommerce') && $top_links && $cart_widget): ?>
	                        	<a href="#" class="shopping-cart-link hidden-desktop" data-toggle="modal" data-target="#cartModal"></a>
							<?php endif ?>
							<?php if (is_user_logged_in() && $top_links): ?>
								<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="my-account-link hidden-desktop"></a>
							<?php elseif($top_links): ?>
								<a class="popup-with-form my-account-link hidden-tablet hidden-desktop" href="#loginModal"></a>
							<?php endif ?>
							<?php if ($search_form): ?>
								<a class="popup-with-form search-link" href="#searchModal"></a>
							<?php endif ?>
						</div>

                        <?php et_get_main_menu(); ?>

					</div>
				</div>
			</div>
		</div>
	<?php endif ?>

	<?php if (etheme_get_option('top_panel')): ?>
		<div class="top-panel">
			<div class="container">
				<?php if(!function_exists('dynamic_sidebar') || !dynamic_sidebar('top-panel-sidebar')): ?>

					<div class="sidebar-widget">
						<h6><?php _e('Add any widgets you want in Apperance->Widgets->"Hidden top panel area"', 'legenda') ?></h6>
					</div>

				<?php endif; ?>
			</div>
		</div>
	<?php endif ?>

	<div class="page-wrapper">

	 <?php 
	 if ($ht == 9) {
	 	$ht = $ht_class = '4 transparent';
	 }

	 $ht_class = $ht . ' ' . etheme_get_option('header_color_scheme');
	 // old
    //$ht .= ' ' . etheme_get_option('header_color_scheme');
	 ?>

	<div class="header-wrapper<?php if(etheme_get_option('fade_animation')): ?> fade-in delay1<?php endif; ?> header-type-<?php echo esc_attr($ht_class); ?><?php if( !$cart_widget ) { echo " cart-disabled"; } ?>">
		<?php if (etheme_get_option('top_bar')): ?>
			<div class="top-bar">
				<div class="container">
					<div class="row-fluid">
						<div class="languages-area">
							<?php if(etheme_get_option('languages_area') && (!function_exists('dynamic_sidebar') || !dynamic_sidebar('languages-sidebar'))): ?>
									<div class="languages hidden-phone">
										<ul class="links">
											<li class="active"><a href="#">EN</a></li>
											<li><a href="#">DE</a></li>
											<li><a href="#">ES</a></li>
											<li><a href="#">FR</a></li>
										</ul>
									</div>
							<?php endif; ?>
						</div>

						<?php if (etheme_get_option('top_panel')): ?>
							<div class="show-top-panel hidden-phone"></div>
						<?php endif ?>

						<?php if ($search_form): ?>
							<div class="search hide-input a-right">
								<a class="popup-with-form search-link" href="#searchModal"><?php echo esc_html('Search', 'legenda'); ?></a>
							</div>
						<?php endif ?>

						<?php if ( class_exists('Woocommerce') ):
							if ( $ht != 8 ) {
                        		et_cart_summ();
                        	} 
						endif ?>


						<?php if (is_user_logged_in() && $top_links): ?>
							<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="my-account-link hidden-desktop">&nbsp;</a>
						<?php elseif($top_links): ?>
							<a class="popup-with-form my-account-link hidden-tablet hidden-desktop" href="#loginModal">&nbsp;</a>
						<?php endif ?>



						<?php if ($top_links): ?>
							<div class="top-links hidden-phone a-center">
								<?php etheme_top_links(); ?>
							</div>
						<?php endif ?>

						<?php if (class_exists('YITH_WCWL') && etheme_get_option('wishlist_link')): $wl = new YITH_WCWL(array());?>
							<div class="fl-r wishlist-link">
								<a href="<?php echo esc_url($wl->get_wishlist_url()); ?>"><i class="icon-heart-empty"></i><span><?php esc_html_e('Wishlist', 'legenda') ?></span></a>
							</div>
						<?php endif ?>
						<?php if(etheme_get_option('right_panel')): ?>
							<div class="side-area-icon hidden-phone hidden-tablet"><i class="icon-reorder"></i></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		<?php endif ?>

		<header class="header header<?php echo esc_attr($ht_class); if(!et_is_woo_exists()){echo " without-woocommerce";} ?> <?php echo (!$cart_widget) ? 'cart-ghost' : ''; ?>">

			<div class="container">
				<div class="table-row">

    				<?php if ($search_form || in_array($ht, array( 1, 5, 7 ) ) ): ?>
    					<div class="search search-left hidden-phone hidden-tablet a-left <?php echo ( !$search_form ) ? 'ghost' : ''; ?>">
								<?php echo etheme_search(array()); ?>
    					</div>
    				<?php endif ?>

					<div class="logo"><?php etheme_logo(); ?></div>

					<?php if ( ( $search_form && !in_array($ht, array(1, 5, 6, 7) ) ) || in_array($ht, array(2))): ?>
						<div class="search search-center hidden-phone hidden-tablet <?php echo ( !$search_form ) ? 'ghost' : ''; ?>">
<!--							<div class="site-description hidden-phone hidden-tablet">--><?php //bloginfo( 'description' ); ?><!--</div>-->
								<?php echo etheme_search(array()); ?>
						</div>
					<?php endif ?>

					<?php if($ht == 8 && $search_form): ?>
						<div class="search hide-input a-right">

							<a class="popup-with-form search-link" href="#searchModal">search</a>
						</div>
					<?php endif; ?>

					<?php if ( !etheme_get_option('just_catalog') ) : ?>
			            <?php if( ( class_exists('Woocommerce') && $cart_widget ) && in_array($ht, array( 1, 2, 3, 5, 7, 8 ) ) ): ?>
		                    <?php if ( $ht == 8 ) etheme_top_cart(false);
		                    		else etheme_top_cart(); ?>
			            <?php endif ;?>
		        	<?php endif; ?>
					<div class="menu-icon hidden-desktop"><i class="icon-reorder"></i></div>
				</div>
			</div>

		</header>
		<script type="application/javascript">
            window.addEventListener("DOMContentLoaded", function(e) {
                setTimeout(function() {
                    document.querySelector('.text_slider_wrapper').style.display = 'block';
                }, 2000);
            });
        </script>
		<div class="main-nav visible-desktop">
			<div class="double-border">
				<div class="container">
					<div class="menu-wrapper menu-type<?php etheme_option('menu_type'); ?>">
						<div class="logo-with-menu">
							<?php etheme_logo(); ?>
						</div>
						<?php et_get_main_menu(); ?>
					</div>
				</div>
			</div>
		</div>

		<?php if($ht == 8): ?>
			<div class="languages-area">
				<?php if((!function_exists('dynamic_sidebar') || !dynamic_sidebar('languages-sidebar'))): ?>
					<div class="languages">
						<ul class="links">
							<li class="active">EN</li>
							<li><a href="#">FR</a></li>
							<li><a href="#">GE</a></li>
						</ul>
					</div>
					<div class="currency">
						<ul class="links">
							<li><a href="#">£</a></li>
							<li><a href="#">€</a></li>
							<li class='active'>$</li>
						</ul>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
