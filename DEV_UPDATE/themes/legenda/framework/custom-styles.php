<?php  
	// add_action('wp_head', 'etheme_init');
    add_action( 'wp_enqueue_scripts', 'etheme_init', 999 );
if(!function_exists('etheme_init')) {
    function etheme_init() { 

        $css = '';
        ob_start(); ?>
            <?php if ( etheme_get_option('sale_icon') ) : ?>
                <?php $sale_label_bg = etheme_get_option('sale_icon_url'); ?>
                .label-icon.sale-label {
                    width: <?php echo (etheme_get_option('sale_icon_width')) ? etheme_get_option('sale_icon_width') : 67 ?>px;
                    height: <?php echo (etheme_get_option('sale_icon_height')) ? etheme_get_option('sale_icon_height') : 67 ?>px;
                }
                .label-icon.sale-label { background-image: url(<?php echo (is_array($sale_label_bg) && !empty($sale_label_bg['url'])) ? $sale_label_bg['url'] : get_template_directory_uri() .'/images/label-sale.png' ?>); }
            <?php endif; ?>

            <?php if ( etheme_get_option('new_icon') ) : ?>
                <?php $new_label_bg = etheme_get_option('new_icon_url'); ?>
                .label-icon.new-label {
                    width: <?php echo (etheme_get_option('new_icon_width')) ? etheme_get_option('new_icon_width') : 67 ?>px;
                    height: <?php echo (etheme_get_option('new_icon_height')) ? etheme_get_option('new_icon_height') : 67 ?>px;
                }
                .label-icon.new-label { background-image: url(<?php echo (is_array($new_label_bg) && !empty($new_label_bg['url'])) ? $new_label_bg['url'] : get_template_directory_uri() .'/images/label-new.png' ?>); }
            <?php endif; ?>

            <?php if ( etheme_get_option( 'fixed_bg' ) ): ?>
                .fixed-header {
                    background-color: <?php echo etheme_get_option('fixed_bg'); ?>
                }
            <?php endif; ?>

            <?php if ( etheme_get_option( 'mobile_menu_bg' ) ): ?>
                .mobile-nav, .mobile-nav.side-block .close-block, .mobile-nav .et-mobile-menu li > a,.mobile-nav .et-mobile-menu li .open-child, .mobile-nav .et-mobile-menu > li > ul li{
                    background-color: <?php echo etheme_get_option( 'mobile_menu_bg' );?> !important;
                }
            <?php endif; ?>

            <?php if ( etheme_get_option( 'mobile_menu_br' ) ): ?>
                .et-mobile-menu li > a,.mobile-nav ul.links{
                    border-top:1px solid <?php echo etheme_get_option( 'mobile_menu_br' );?> !important;
                }
            <?php endif; ?>

            <?php
                if ( etheme_get_option( 'mobile_menu_font' ) ):
                    $mobile_font = etheme_get_option( 'mobile_menu_font' ); ?>
	                .mobile-nav .et-mobile-menu li > a, .mobile-nav ul.links li a {
	                    <?php if(!empty($mobile_font['color'])) :?>      color: <?php echo esc_attr($mobile_font['color']).';'; endif; ?>
	                    <?php if(!empty($mobile_font['font-family'])): ?>     font-family: <?php echo esc_attr($mobile_font['font-family']).';'; endif; ?>
	                    <?php if(!empty($mobile_font['font-size'])): ?>       font-size: <?php echo esc_attr($mobile_font['font-size']).';';
	                        if(!empty($mobile_font['line-height'])){ ?>
                                line-height: <?php echo esc_attr( ((int) $mobile_font['line-height'] / (int) $mobile_font['font-size']) ) . ';'; ?>
	                        <?php }
                        endif; ?>
	                    <?php if(!empty($mobile_font['font-style'])): ?>      font-style: <?php echo esc_attr($mobile_font['font-style']).';'; endif; ?>
	                    <?php if(!empty($mobile_font['font-weight'])): ?>     font-weight: <?php echo esc_attr($mobile_font['font-weight']).';'; endif; ?>
	                    <?php if(!empty($mobile_font['font-variant'])): ?>    font-variant: <?php echo esc_attr($mobile_font['font-variant']).';'; endif; ?>
	                    <?php if(!empty($mobile_font['letter-spacing'])): ?>  letter-spacing: <?php echo esc_attr($mobile_font['letter-spacing']).';'; endif; ?>
	                    <?php if(!empty($mobile_font['text-transform'])): ?>  text-transform:  <?php echo esc_attr($mobile_font['text-transform']).';'; endif; ?>
	                }
	            <?php endif; ?>

            <?php
                if ( etheme_get_option( 'mobile_menu_headings_font' ) ):
                    $mobile_heading = etheme_get_option( 'mobile_menu_headings_font' ); ?>
                	.mobile-nav .mobile-nav-heading, .mobile-nav .close-mobile-nav {
	                    <?php if(!empty($mobile_heading['color'])) :?>      color: <?php echo esc_attr($mobile_heading['color']).';'; endif; ?>
	                    <?php if(!empty($mobile_heading['font-family'])): ?>     font-family: <?php echo esc_attr($mobile_heading['font-family']).';'; endif; ?>
                        <?php if(!empty($mobile_heading['font-size'])): ?>       font-size: <?php echo esc_attr($mobile_heading['font-size']).';';
                            if(!empty($mobile_heading['line-height'])){ ?>
                                line-height: <?php echo esc_attr( ((int) $mobile_heading['line-height'] / (int) $mobile_heading['font-size']) ) . ';'; ?>
                            <?php }
                        endif; ?>
	                    <?php if(!empty($mobile_heading['font-style'])): ?>      font-style: <?php echo esc_attr($mobile_heading['font-style']).';'; endif; ?>
	                    <?php if(!empty($mobile_heading['font-weight'])): ?>     font-weight: <?php echo esc_attr($mobile_heading['font-weight']).';'; endif; ?>
	                    <?php if(!empty($mobile_heading['font-variant'])): ?>    font-variant: <?php echo esc_attr($mobile_heading['font-variant']).';'; endif; ?>
	                    <?php if(!empty($mobile_heading['letter-spacing'])): ?>  letter-spacing: <?php echo esc_attr($mobile_heading['letter-spacing']).';'; endif; ?>
	                    <?php if(!empty($mobile_heading['text-transform'])): ?>  text-transform:  <?php echo esc_attr($mobile_heading['text-transform']).';'; endif; ?>
	                }
            <?php endif; ?>

        	<?php

            $selectors = array();
            $selectors['main_font'] = '
                .dropcap,
                blockquote,
                .team-member .member-mask .mask-text fieldset legend,
                .button,
                button,
                .coupon .button,
                input[type="submit"],
                .font2,
                .shopping-cart-widget .totals,
                .main-nav .menu > li > a,
                .menu-wrapper .menu .nav-sublist-dropdown .menu-parent-item > a,
                .fixed-header .menu .nav-sublist-dropdown .menu-parent-item > a,
                .fixed-header .menu > li > a,
                .side-block .close-block,
                .side-area .widget-title,
                .et-mobile-menu li > a,
                .page-heading .row-fluid .span12 > .back-to,
                .breadcrumbs .back-to,
                .recent-post-mini a,
                .etheme_widget_recent_comments ul li .post-title,
                .product_list_widget a,
                .widget_price_filter .widget-title,
                .widget_layered_nav .widget-title,
                .widget_price_filter h4,
                .widget_layered_nav h4,
                .products-list .product .product-name,
                .table.products-table th,
                .table.products-table .product-name a,
                .table.products-table .product-name dl dt,
                .table.products-table .product-name dl dd,
                .cart_totals .table .total th strong,
                .cart_totals .table .total td strong .amount,
                .pricing-table table .plan-price,
                .pricing-table table.table thead:first-child tr:first-child th,
                .pricing-table.style3 table .plan-price sup,
                .pricing-table.style2 table .plan-price sup,
                .pricing-table ul li.row-title,
                .pricing-table ul li.row-price,
                .pricing-table.style2 ul li.row-price sup,
                .pricing-table.style3 ul li.row-price sup,
                .tabs .tab-title,
                .left-bar .left-titles .tab-title-left,
                .right-bar .left-titles .tab-title-left,
                .slider-container .show-all-posts,
                .bc-type-variant2 .woocommerce-breadcrumb,
                .bc-type-variant2 .breadcrumbs,
                .post-single .post-share .share-title,
                .toggle-element .toggle-title,
                #bbpress-forums li.bbp-header,
                #bbpress-forums .bbp-forum-title,
                #bbpress-forums .bbp-topic-title,
                #bbpress-forums .bbp-reply-title,
                .product-thumbnails-slider .slides li.video-thumbnail span,
                .coupon label,
                .product-image-wrapper .out-of-stock,
                .shop_table .product-name a,
                .shop_table th,
                .cart_totals .order-total th,
                .page-heading .row-fluid .span12 .back-to,
                .woocommerce table.shop_table th,
                .woocommerce-page table.shop_table th,
                .mobile-nav-heading,
                .links a,
                .top-bar .wishlist-link a,
                .top-bar .cart-summ,
                .shopping-cart-link span
            ';

            $selectors['font_color'] = '
                body,
                select,
                .products-small .product-item a,
                .woocommerce-breadcrumb,
                #breadcrumb,
                .woocommerce-breadcrumb a,
                #breadcrumb a,
                .etheme_widget_recent_comments .comment_link a,
                .product-categories li ul a,
                .product_list_widget del .amount,
                .page-numbers li a,
                .page-numbers li span,
                .pagination li a,
                .pagination li span,
                .images .main-image-slider ul.slides .zoom-link:hover,
                .quantity .qty,
                .price .from,
                .price del,
                .shopping-cart-widget .cart-summ .items,
                .shopping-cart-widget .cart-summ .for-label,
                .posted-in a,
                .tabs .tab-title,
                .toggle-element .open-this,
                .blog-post .post-info .posted-in a,
                .menu-type1 .menu ul > li > a,
                .post-next-prev a

            ';
            $selectors['active_color'] = '
                a:hover,
                .button:hover,
                button:hover,
                input[type="submit"]:hover,
                .menu-icon:hover,
                .widget_layered_nav ul li:hover,
                .page-numbers li span,
                .pagination li span,
                .page-numbers li a:hover,
                .pagination li a:hover,
                .largest,
                .thumbnail:hover i,
                .demo-icons .demo-icon:hover,
                .demo-icons .demo-icon:hover i,
                .switchToGrid:hover,
                .switchToList:hover,
                .switcher-active,
                .switcher-active:hover,
                .emodal .close-modal:hover,
                .prev.page-numbers:hover:after,
                .next.page-numbers:hover:after,
                strong.active,
                span.active,
                em.active,
                a.active,
                p.active,
                .shopping-cart-widget .cart-summ .price-summ,
                .products-small .product-item h5 a:hover,
                .slider-container .slider-next:hover:before,
                .slider-container .slider-prev:hover:before,
                .fullwidthbanner-container .tp-rightarrow.default:hover:before,
                .fullwidthbanner-container .tp-leftarrow.default:hover:before,
                .side-area .close-block:hover i,
                .back-to-top:hover, .back-to-top:hover i,
                .product-info .single_add_to_wishlist:hover:before,
                .images .main-image-slider ul.slides .zoom-link i:hover,
                .footer_menu li:hover:before,
                .main-nav .menu > li.current-menu-parent > a,
                .main-nav .menu > li.current-menu-item > a,
                .page-numbers .next:hover:before,
                .pagination .next:hover:before,
                .etheme_twitter .tweet a,
                .small-slider-arrow.arrow-left:hover,
                .small-slider-arrow.arrow-right:hover,
                .active2:hover,
                .active2,
                .checkout-steps-nav a.button.active,
                .checkout-steps-nav a.button.active:hover,
                .button.active,
                button.active,
                input[type="submit"].active,
                .widget_categories .current-cat a,
                .widget_pages .current_page_parent > a,
                div.dark_rounded .pp_contract:hover,
                div.dark_rounded .pp_expand:hover,
                div.dark_rounded .pp_close:hover,
                .etheme_cp .etheme_cp_head .etheme_cp_btn_close:hover,
                .hover-icon:hover,
                .side-area-icon:hover,
                .etheme_cp .etheme_cp_content .etheme_cp_section .etheme_cp_section_header .etheme_cp_btn_clear:hover,
                .header-type-3 .main-nav .menu-wrapper .menu > li.current-menu-item > a,
                .header-type-3 .main-nav .menu-wrapper .menu > li.current-menu-parent > a,
                .header-type-3 .main-nav .menu-wrapper .menu > li > a:hover,
                .fixed-header .menu > li.current-menu-item > a,
                .fixed-header .menu > li > a:hover,
                .main-nav .menu > li > a:hover,
                .product-categories > li > a:hover,
                .custom-info-block.a-right span,
                .custom-info-block.a-left span,
                .custom-info-block a i:hover,
                .product-categories > li.current-cat > a,
                .menu-wrapper .menu .nav-sublist-dropdown .menu-parent-item > a:hover,
                .woocommerce .woocommerce-breadcrumb a:hover,
                .woocommerce-page .woocommerce-breadcrumb a:hover,
                .product-info .posted_in a:hover,
                .slide-item .product .products-page-cats a:hover,
                .products-grid .product .products-page-cats a:hover,
                .widget_layered_nav ul li:hover a,
                .page-heading .row-fluid .span12 > .back-to:hover,
                .breadcrumbs .back-to:hover,
                #breadcrumb a:hover,
                .links li a:hover,
                .menu-wrapper .menu > .nav-sublist-dropdown .menu-parent-item ul li:hover,
                .menu-wrapper .menu > .nav-sublist-dropdown .menu-parent-item ul li:hover a,
                .menu-wrapper .menu ul > li > a:hover,
                .filled.active,
                .shopping-cart-widget .cart-summ a:hover,
                .product-categories > li > ul > li > a:hover,
                .product-categories > li > ul > li > a:hover + span,
                .product-categories ul.children li > a:hover,
                .product-categories ul.children li > a:hover + span,
                .product-categories > li.current-cat > a+span,
                .widget_nav_menu .current-menu-item a,
                .widget_nav_menu .current-menu-item:before,
                .fixed-menu-type2 .fixed-header .nav-sublist-dropdown li a:hover,
                .product-category h5:hover,
                .product-categories .children li.current-cat,
                .product-categories .children li.current-cat a,
                .product-categories .children li.current-cat span,
                .pricing-table ul li.row-price,
                .product-category:hover h5,
                .widget_nav_menu li a:hover,
                .widget_nav_menu li:hover:before,
                .list li:before,

                .toolbar .switchToGrid:hover:before,
                .toolbar .switchToList:hover:before,
                .toolbar .switchToGrid.switcher-active:before,
                .toolbar .switchToList.switcher-active:before,

                .toolbar .switchToGrid.switcher-active,
                .toolbar .switchToList.switcher-active,

                .blog-post .post-info a:hover,
                .show-all-posts:hover,
                .cbp-qtrotator .testimonial-author .excerpt,
                .top-bar .wishlist-link a:hover span,
                .menu-type2 .menu .nav-sublist-dropdown .menu-parent-item li:hover:before,
                .back-to-top:hover:before,
                .tabs .tab-title:hover,
                .flex-direction-nav a:hover,
                .widget_layered_nav ul li a:hover,
                .widget_layered_nav ul li:hover,
                .product-categories .open-this:hover,
                .widget_categories li:hover:before,
                .etheme-social-icons li a:hover,
                .product-categories > li.opened .open-this:hover,
                .slider-container .show-all-posts:hover,
                .widget_layered_nav ul li.chosen .count,
                .widget_layered_nav ul li.chosen a,
                .widget_layered_nav ul li.chosen a:before,
                .recent-post-mini strong,
                .menu-wrapper .menu ul > li:hover:before,
                .fixed-header .menu ul > li:hover:before,
                .team-member .member-mask .mask-text a:hover,
                .show-quickly:hover,
                .header-type-6 .top-bar .top-links .submenu-dropdown ul li a:hover,
                .header-type-6 .top-bar .top-links .submenu-dropdown ul li:hover:before,
                .side-area-icon i:hover:before,
                .menu-icon i:hover:before,
                a.bbp-author-name,
                #bbpress-forums #bbp-single-user-details #bbp-user-navigation li.current a,
                #bbpress-forums #bbp-single-user-details #bbp-user-navigation li.current:before,
                .bbp-forum-header a.bbp-forum-permalink,
                .bbp-topic-header a.bbp-topic-permalink,
                .bbp-reply-header a.bbp-reply-permalink,
                .et-tweets.owl-carousel .owl-prev:hover:before,
                .et-tweets.owl-carousel .owl-next:hover:before,
                .etheme_widget_brands ul li.active-brand a,
                .comment-block .author-link a:hover,
                .header-type-3 .shopping-cart-link span.amount,
                .header-type-4 .shopping-cart-link span.amount,
                .header-type-6 .shopping-cart-link span.amount,
                a.view-all-results:hover,
                .bottom-btn .left
            ';

            // important
            $selectors['active_color_important'] = '
                .hover-icon:hover,
                .breadcrumbs .back-to:hover
            ';

            // Price COLOR!
            $selectors['pricecolor'] = '
                .products-small .product-item .price,
                .product_list_widget .amount,
                .cart_totals .table .total .amount,
                .price
            ';

            $selectors['active_bg'] = '
                .filled:hover,
                .progress-bar > div,
                .active2:hover,
                .button.active:hover,
                button.active:hover,
                input[type="submit"].active:hover,
                .checkout-steps-nav a.button.active:hover,
                .portfolio-filters .active,
                .product-info .single_add_to_cart_button,
                .product-info .single_add_to_wishlist:hover,
                .checkout-button.button,
                .checkout-button.button:hover,
                .header-type-6 .top-bar,
                .filled.active,
                .block-with-ico.ico-position-top i,
                .added-text,
                .etheme_cp_btn_show,
                .button.white.filled:hover,
                .button.active,
                .button.active2,
                .button.white:hover,
                .woocommerce-checkout-payment .place-order .button,
                .bottom-btn .right
            ';
            $selectors['active_border'] = '
                .button:hover,
                button:hover,
                .button.white.filled:hover,
                input[type="submit"]:hover,
                .button.active,
                button.active,
                input[type="submit"].active,
                .filled:hover,
                .widget_layered_nav ul li:hover,
                .page-numbers li span,
                .pagination li span,
                .page-numbers li a:hover,
                .pagination li a:hover,
                .switchToGrid:hover,
                .switchToList:hover,
                .toolbar .switchToGrid.switcher-active,
                .toolbar .switchToList.switcher-active,
                textarea:focus,
                input[type="text"]:focus,
                input[type="password"]:focus,
                input[type="datetime"]:focus,
                input[type="datetime-local"]:focus,
                input[type="date"]:focus,
                input[type="month"]:focus,
                input[type="time"]:focus,
                input[type="week"]:focus,
                input[type="number"]:focus,
                input[type="email"]:focus,
                input[type="url"]:focus,
                input[type="search"]:focus,
                input[type="tel"]:focus,
                input[type="color"]:focus,
                .uneditable-input:focus,
                .active2,
                .woocommerce.widget_price_filter .ui-slider .ui-slider-range,
                .woocommerce-page .widget_price_filter .ui-slider .ui-slider-range,
                .checkout-steps-nav a.button.active,
                .product-info .single_add_to_cart_button,
                .main-nav .menu > li.current-menu-parent > a:before,
                .main-nav .menu > li.current-menu-item > a:before,
                .cta-block.style-filled,
                .search #searchform input[type="text"]:focus,
                .product-categories .open-this:hover,
                .product-categories > li.opened .open-this:hover,
                .woocommerce-checkout-payment .place-order .button,
                .bottom-btn .left

            ';


            $selectors['darken_color'] = '';

            $selectors['darken_bg'] = '
                .woocommerce.widget_price_filter .ui-slider .ui-slider-handle
            ';

            $selectors['darken_border'] = '';

        	?>

            <?php echo jsString($selectors['font_color']); ?> { color: #6f6f6f; }

	        <?php
	            $activeColor = (etheme_get_option('activecol')) ? etheme_get_option('activecol') : '#d7a200';
	            $priceColor = (etheme_get_option('pricecolor')) ? etheme_get_option('pricecolor') : '#d7a200';

	            $rgb = hex2rgb($activeColor);


	            $darkenRgb = array();

	            $darkenRgb[0] = ($rgb[0] > 30) ? $rgb[0] - 30 : 0;
	            $darkenRgb[1] = ($rgb[1] > 30) ? $rgb[1] - 30 : 0;
	            $darkenRgb[2] = ($rgb[2] > 30) ? $rgb[2] - 30 : 0;

	            $darkenColor = 'rgb('.$darkenRgb[0].','.$darkenRgb[1].','.$darkenRgb[2].')';

	        ?>

	        <?php echo jsString($selectors['active_color']); ?>              { color: <?php echo esc_attr($activeColor); ?>; }

	        <?php echo jsString($selectors['active_color_important']); ?>    { color: <?php echo esc_attr($activeColor); ?>!important; }

	        <?php echo jsString($selectors['active_bg']); ?>                 { background-color: <?php echo esc_attr($activeColor); ?>; }

	        <?php echo jsString($selectors['active_border']); ?>             { border-color: <?php echo esc_attr($activeColor); ?>; }

	        <?php echo jsString($selectors['pricecolor']); ?>              { color: <?php echo esc_attr($priceColor); ?>; }

	        <?php echo jsString($selectors['darken_color']); ?>              { color: <?php echo esc_attr($darkenColor); ?>; }

	        <?php echo jsString($selectors['darken_bg']); ?>                 { background-color: <?php echo esc_attr($darkenColor); ?>; }

	        <?php echo jsString($selectors['darken_border']); ?>             { border-color: <?php echo esc_attr($darkenColor); ?>; }

			?>

	        .woocommerce.widget_price_filter .ui-slider .ui-slider-range,
	        .woocommerce-page .widget_price_filter .ui-slider .ui-slider-range{
	          background: <?php echo esc_attr('rgba('.$rgb[0].','.$rgb[1].','.$rgb[2].',0.35)'); ?>;
	        }

            <?php

           	$h1 = etheme_get_option('h1');
            $h2 = etheme_get_option('h2');
            $h3 = etheme_get_option('h3');
            $h4 = etheme_get_option('h4');
            $h5 = etheme_get_option('h5');
            $h6 = etheme_get_option('h6');
            $headerfont = etheme_get_option('header_menu_font');
            $sfont = etheme_get_option('sfont');
            $mainfont = etheme_get_option('mainfont');

            // ! Use @font-face to load font on page

            $used_fonts = array();
            if ( isset($h1['font-family']) )
                $used_fonts[] = $h1['font-family'];
            if ( isset($h2['font-family']) )
                $used_fonts[] = $h2['font-family'];
            if ( isset($h3['font-family']) )
                $used_fonts[] = $h3['font-family'];
            if ( isset($h4['font-family']) )
                $used_fonts[] = $h4['font-family'];
            if ( isset($h5['font-family']) )
                $used_fonts[] = $h5['font-family'];
            if ( isset($h6['font-family']) )
                $used_fonts[] = $h6['font-family'];
            if ( isset($headerfont['font-family']) )
                $used_fonts[] = $headerfont['font-family'];
            if ( isset($sfont['font-family']) )
                $used_fonts[] = $sfont['font-family'];
            if ( isset($mainfont['font-family']) )
                $used_fonts[] = $mainfont['font-family'];

            $fonts = get_option( 'etheme-fonts', false );

            if ( $fonts ) {
                foreach ( $fonts as $value ) {
                    // ! load only used fonts
                    if ( ! in_array( $value['name'], $used_fonts ) ) {
                        continue;
                    }

                    // ! Validate format
                    switch ( $value['file']['extension'] ) {
                        case 'ttf':
                            $format = 'truetype';
                            break;
                        case 'otf':
                            $format = 'opentype';
                            break;
                        case 'eot':
                            $format = false;
                            break;
                        case 'eot?#iefix':
                            $format = 'embedded-opentype';
                            break;
                        case 'woff2':
                            $format = 'woff2';
                            break;
                        case 'woff':
                            $format = 'woff';
                            break;
                        default:
                            $format = false;
                            break;
                    }

                    $format = ( $format ) ? 'format("' . $format . '")' : '';

	                $font_url = ( is_ssl() && (strpos($value['file']['url'], 'https') === false) ) ? str_replace('http', 'https', $value['file']['url']) : $value['file']['url'];

	                // ! Set fonts
                    echo '
                        @font-face {
                            font-family: ' . $value['name'] . ';
                            src: url(' . $font_url . ') ' . $format . ';
                            font-display: swap;
                        }
                    ';
                }
            }


           	$bg = etheme_get_option('background_img'); 
            $bg = is_array($bg) ? $bg : array(); ?>

           	<?php 

           	$typography_css = array(
       			'color',
       			'font-family',
       			'font-size',
       			'font-style',
       			'font-weight',
       			'font-variant',
       			'letter-spacing',
       			'line-height',
       			'text-transform',
           	);

           	$typography_selectors = array(
           		'h1' => $typography_css,
           		'h2' => $typography_css,
           		'h3' => $typography_css,
           		'h4' => $typography_css,
           		'h5' => $typography_css,
           		'h6' => $typography_css
           	);

           	foreach ($typography_selectors as $selector => $value) {
           		echo esc_html($selector) . '{';
           		$settings = etheme_get_option($selector);
           			foreach ($typography_css as $key2 ) {
           				if ( !empty($settings[$key2]) ) {
           					if ( $key2 == 'font-family' ) $settings[$key2] = '"'.str_replace('+', ' ', $settings[$key2]) . '"';
           					else $settings[$key2] = esc_attr($settings[$key2]);
           					echo esc_attr($key2) . ':' . $settings[$key2] . ';';
           				}
           			}
           		echo '}';
           	}

            ?>
            
            html {
                <?php if(!empty($sfont['font-size'])): ?> font-size: <?php echo esc_attr($sfont['font-size']).';'; endif; ?>
            }

            body {
                <?php if(!empty($sfont['color'])) :?>      color: <?php echo esc_attr($sfont['color']).';'; endif; ?>
                <?php if(!empty($sfont['font-family'])): ?>     font-family: <?php echo '"' . str_replace('+', ' ', $sfont['font-family']).'";'; endif; ?>
                <?php if(!empty($sfont['font-size'])): ?>       font-size: <?php echo esc_attr($sfont['font-size']).';';
                    if(!empty($sfont['line-height'])){ ?>
                        line-height: <?php echo esc_attr( ((int) $sfont['line-height'] / (int) $sfont['font-size']) ) . ';'; ?>
                    <?php }
                endif; ?>
                <?php if(!empty($sfont['font-style'])): ?>      font-style: <?php echo esc_attr($sfont['font-style']).';'; endif; ?>
                <?php if(!empty($sfont['font-weight'])): ?>     font-weight: <?php echo esc_attr($sfont['font-weight']).';'; endif; ?>
                <?php if(!empty($sfont['font-variant'])): ?>    font-variant: <?php echo esc_attr($sfont['font-variant']).';'; endif; ?>
                <?php if(!empty($sfont['letter-spacing'])): ?>  letter-spacing: <?php echo esc_attr($sfont['letter-spacing']).';'; endif; ?>
                <?php if(!empty($sfont['text-transform'])): ?>  text-transform:  <?php echo esc_attr($sfont['text-transform']).';'; endif; ?>
                <?php foreach ($bg as $key => $value) {
            		if ( $key == 'background-image' ) {
            			echo (!empty($value)) ? esc_attr($key) . ':' . 'url('.$value.')' : '';
            		}
        			else {
        				echo (!is_array($value) && !empty($value)) ? $key . ':' . $value . ';' : '';
        			}
        		} ?>
            }
	
	        <?php if(!empty($headerfont['color'])) :?>
                .main-nav .menu > li > a:hover, .menu-wrapper .menu .nav-sublist-dropdown .menu-parent-item > a:hover, .main-nav .menu > li .nav-sublist-dropdown .container > ul > li a:hover,.fixed-header .menu > li > a:hover, .fixed-header .menu > li .nav-sublist-dropdown .container > ul > li a:hover{
                    color: <?php echo etheme_get_option('activecol').' !important;'; ?>
                }
            <?php endif; ?>

            <?php
        
	        $main_font_styles = array();
            $main_font_settings = array(
                'color' => (!empty($mainfont['color']) ? $mainfont['color'] : ''),
                'font-family' => (!empty($mainfont['font-family']) ? $mainfont['font-family'] : ''),
                'font-size' => (!empty($mainfont['font-size']) ? $mainfont['font-size'] : ''),
                'font-style' => (!empty($mainfont['font-style']) ? $mainfont['font-style'] : ''),
                'font-weight' => (!empty($mainfont['font-weight']) ? $mainfont['font-weight'] : ''),
                'font-variant' => (!empty($mainfont['font-variant']) ? $mainfont['font-variant'] : ''),
                'letter-spacing' => (!empty($mainfont['letter-spacing']) ? $mainfont['letter-spacing'] : ''),
                'line-height' => ((!empty($mainfont['line-height']) && !empty($mainfont['font-size']) ) ? ((int) $mainfont['line-height'] / (int) $mainfont['font-size']) : ''),
                'text-transform' => (!empty($mainfont['text-transform']) ? $mainfont['text-transform'] : ''),
            );
            foreach ($main_font_settings as $key => $value) {
                if (empty($value)) continue;
                if ( $key == 'font-family' ) $value = '"'. str_replace('+', ' ', $value ) . '"';
                else $value = esc_attr($value);
                $main_font_styles[] = esc_attr($key) . ':' . $value . ';';
            }
            if ( count($main_font_styles) ) {
                echo jsString($selectors['main_font']) . '{' .
                     implode('', $main_font_settings) .
                 '}';
            }
            
            $mix_styles = array();
            ?>

            <?php if(!empty($headerfont['color'])) : $mix_styles[] = 'color:' . esc_attr($headerfont['color']).' !important;'; endif; ?>
            <?php if(!empty($headerfont['font-family'])): $mix_styles[] = 'font-family:' . str_replace('+', ' ', $headerfont['font-family']).';'; endif; ?>
            <?php if(!empty($headerfont['font-size'])): $mix_styles[] = 'font-size:' . esc_attr($headerfont['font-size']).';';
                if(!empty($headerfont['line-height'])){
	                $mix_styles[] =  'line-height:' . esc_attr( ((int) $headerfont['line-height'] / (int) $headerfont['font-size']) ) . ';'; ?>
                <?php }
            endif; ?>
            <?php if(!empty($headerfont['font-style'])): $mix_styles[] = 'font-style:' . esc_attr($headerfont['font-style']).';'; endif; ?>
            <?php if(!empty($headerfont['font-weight'])): $mix_styles[] = 'font-weight:' . esc_attr($headerfont['font-weight']).';'; endif; ?>
            <?php if(!empty($headerfont['font-variant'])): $mix_styles[] = 'font-variant:' . esc_attr($headerfont['font-variant']).';'; endif; ?>
            <?php if(!empty($headerfont['letter-spacing'])): $mix_styles[] = 'letter-spacing:' . esc_attr($headerfont['letter-spacing']).';'; endif; ?>
            <?php if(!empty($headerfont['text-transform'])): $mix_styles[] = 'text-transform:' . esc_attr($headerfont['text-transform']).';'; endif; ?>
        
            <?php if (count($mix_styles)) {
                echo '.fixed-header .menu > li.menu-full-width .nav-sublist-dropdown .container > ul > li > a, .main-nav .menu > li.menu-full-width .nav-sublist-dropdown .container > ul > li > a, .fixed-header .menu > li > a, .main-nav .menu > li > a, .fixed-header .menu > li .nav-sublist-dropdown .container > ul > li a, .main-nav .menu > li .nav-sublist-dropdown .container > ul > li a {'.
                     implode('', $mix_styles).
                 '}';
            }
            ?>

            <?php echo et_custom_styles_responsive(); ?>

        <?php $css = ob_get_clean(); 

        $js = ob_start(); ?>

            var ajaxFilterEnabled = <?php echo (etheme_get_option('ajax_filter')) ? 1 : 0; ?>;
            var successfullyAdded = '<?php esc_html_e('successfully added to your shopping cart', 'legenda') ?>';
            var view_mode_default = '<?php echo etheme_get_option('view_mode'); ?>';
            var catsAccordion = false;

            <?php if (etheme_get_option('cats_accordion')) { ?>
                var catsAccordion = true;
            <?php } ?>
            <?php if (class_exists('WooCommerce')) {
                global $woocommerce;
                ?>
                    var checkoutUrl = '<?php echo esc_url( wc_get_checkout_url() ); ?>';
                    var contBtn = '<?php _e('Continue shopping', 'legenda') ?>';
                    var checkBtn = '<?php _e('Checkout', 'legenda') ?>';
                <?php
            } ?>
        <?php $js = ob_get_clean(); ?>
        <?php 
            wp_register_style( 'custom-style', false );
            wp_enqueue_style( 'custom-style' );
            wp_add_inline_style( 'custom-style', $css ); 

            wp_register_script( 'et-custom-js', false );
            wp_enqueue_script( 'et-custom-js' );
            wp_add_inline_script( 'et-custom-js', $js );
    }
}
?>