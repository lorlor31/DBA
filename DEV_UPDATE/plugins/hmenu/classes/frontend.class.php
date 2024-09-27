<?php

    #PLUGIN FRONT-END MANAGEMENT
    class hmenu_frontend
    {
        
        #CLASS VARS
        private $menu_id;
        private $plugin_dir;
        private $plugin_url;
        private $count = 0;
        
        #CONSTRUCT
        public function __construct($plugin_dir, $plugin_url)
        {
            $this->plugin_dir = $plugin_dir;
            $this->plugin_url = $plugin_url;
        }
        
        #IMPLEMENT SHORTCODE LISTENER
        public function hmenu_get_shortcode_content($atts)
        {
            
            #ACCESS GLOBALS
            global $wpdb;
            
            if (isset($atts['id'])) {
                return $this->hmenu_get_menu_html(intval($atts['id']));
            }
            
            return 'error: shortcode malformed';
        }

        public function hmenu_menu_activation_vars()
        {
            global $wp;
            $category = get_the_category();
            $current_id = get_the_id();
            if (isset($category[0]) && is_category($category[0]->slug)) {
                $current_url = get_category_link($category[0]->term_id);
            } elseif (is_page()) {
                $current_url = get_the_permalink($current_id);
            } else {
                $current_url = home_url(add_query_arg(array(), $wp->request)) . '/';
            }
            
            return [$current_url];
        }
        
        #IMPLEMENT SHORTCODE LISTENER
        public function hmenu_get_menu_html($menu_id)
        {
            
            #ACCESS GLOBALS
            global $wpdb,$hmenu_helper,$woocommerce,$wp;
            
            #CREATE INSTANCE
            $backend = new hmenu_backend($this->plugin_dir);
            $get = new hmenu_class_get($this->plugin_dir);
            
            $unique_id = str_replace('-', '', $hmenu_helper->hmenu_genGUID());
            
            //////////////////////////////////////////////////////////
            $result = $wpdb->get_results("
				SELECT
					". $get->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_menu', 'm') .",
					". $get->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_nav_items', 'ni') .",
					". $get->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_menu', 'mega') .",
					". $get->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_contact', 'mega_tact') .",
					". $get->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_map', 'mega_map') ."
				FROM
					`". $wpdb->base_prefix ."hmenu_menu` AS `m`
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_nav_items` AS `ni` ON(`ni`.`menuId` = `m`.`menuId` AND `ni`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_menu` AS `mega` ON(`mega`.`navItemId` = `ni`.`navItemId` AND `mega`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_contact` AS `mega_tact` ON(`mega_tact`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_tact`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_map` AS `mega_map` ON(`mega_map`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_map`.`deleted` = '0')
				WHERE
					`m`.`menuId` = ".$menu_id."
				AND
					`m`.`deleted` = 0
				ORDER BY
					`m`.`menuId` ASC;
			");
            
            #CREATE OBJECT
            $menu_object = array(
                'menu' => array()
            );
            //////////////////////////////////////////////////////////
            if ($result) {
                $menu_object['menu'] = array(
                    'id' => $result[0]->m_menuId
                );
            };
            
            #GET CURRENT URL AND SEND IT TO JQUERY TO DO MAGIC
            $category = get_the_category();
            $current_id = get_the_id();
            if (isset($category[0]) && is_category($category[0]->slug)) {
                $current_url = get_category_link($category[0]->term_id);
            } elseif (is_page()) {
                $current_url = get_the_permalink($current_id);
            } else {
                $current_url = home_url(add_query_arg(array(), $wp->request)) . '/';
            }
            
            $menu = '';
            
            if ($this->count == 0) {
                $backend = new hmenu_backend($this->plugin_dir);
                if ($backend->hmenu_get_frontend_fonts()) {
                    foreach ($backend->hmenu_get_frontend_fonts() as $font) {
                        if ($font->fontName == 'hero_default_solid' || $font->fontName == 'hero_default_thin' || $font->fontName == 'hero_default_social') {
                            //don't inject
                        } else {
                            $menu .= '<link rel="stylesheet" property="stylesheet" id="hmenu-'.$font->fontName.'" href="'.$this->plugin_url.'_fonts/'.$font->fontName.'.css" type="text/css" media="all">';
                        }
                    }
                }
            }
            
            #CHECK ADMIN BAR
            if (is_admin_bar_showing()) {
                $menu .= " <style type='text/css'> .hmenu_is_sticky { margin-top:32px !important; } /* This adds the padding for the admin bar when active. */ </style> ";
            }
            
            $menu .= '<link rel="stylesheet" property="stylesheet" id="hmenu-'.$menu_id.'-general-css" href="'.$this->plugin_url.'_frontend_files/_menu_'.$menu_id.'/_css/hero_menu_styles.css" type="text/css" media="all">';
                    
            #ATTACH MENU HTML
            $menu .= $this->hmenu_return_menu_html($menu_id, $current_url, $current_id);
            
            $this->count++;
            
            #RETURN MENU
            return $menu;
        }
        
        #RETURN MENU HTML
        public function hmenu_return_menu_html($menu_id, $current_url, $current_id)
        {
            
            #GLOBALS
            global $wpdb;
        
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE menuId = ".$menu_id." AND deleted = '0' ORDER BY created DESC");
    
            if ($result) {
                $get = new hmenu_class_get($this->plugin_dir);
                $generate = new hmenu_class_generate($this->plugin_dir);
                $menu_object = $get->hmenu_get_main_menu_object(intval($menu_id), false);
                
                #GOOGLE FONTS
                
                $main_font = $menu_object['main_styles'][0]['fontFamily'];
                $standard_font = $menu_object['dropdown_styles'][0]['fontFamily'];
                $mega_font_0 = $menu_object['mega_font_styles'][0]['fontFamily'];
                $mega_font_1 = $menu_object['mega_font_styles'][1]['fontFamily'];
                $mega_font_2 = $menu_object['mega_font_styles'][2]['fontFamily'];
                $mega_font_3 = $menu_object['mega_font_styles'][3]['fontFamily'];
                $search_font = $menu_object['search_styles'][0]['fontFamily'];
                
                $font_array = array(
                    $main_font,
                    $standard_font,
                    $mega_font_0,
                    $mega_font_1,
                    $mega_font_2,
                    $mega_font_3,
                    $search_font
                );
                
                $font_array = array_unique($font_array);
                
                $the_font_string = '';

                #Array used to check if non google fonts are used and to exclude them.
                $font_check_array = array(
                    "inherit",
                    "Arial",
                    "Verdana",
                    "Times New Roman",
                    "Times",
                    "Trebuchet MS",
                    "sans-serif",
                    "serif"
                );
                
                foreach ($font_array as $font) {
                    if (!in_array($font, $font_check_array)) {
                        $the_font_string .= $font . '|';
                    }
                }
                
                $final_font_string = rtrim($the_font_string, '|');
                
                $the_html = '';

                #FONT LINK
                if (!empty($final_font_string)) {
                    $the_html .= '<link href="https://fonts.googleapis.com/css?family='.str_replace(' ', '+', $final_font_string).'" rel="stylesheet" type="text/css">';
                }
                    
                #MENU HTML HOLDER
                $the_html .= '<div class="hmenu_wrapper_state_'. $menu_id .'">';
                $the_html .= '<div id="hmenu_load_'. $menu_id .'" style="display:none" class="hmenu_load_menu hmenu_unique_menu_'. $menu_id .'" data-menu-id="'.$menu_id.'">';
                $the_html .= $generate->hmenu_frontend_call($menu_object, $current_url, $current_id);
                $the_html .= '</div>';
                $the_html .= '</div>';
            } else {
                $the_html = 'Sorry, that menu does not exist.';
            }
            
            return $the_html;
        }
        
        #GET PRODUCT COUNT
        public function hmenu_get_count()
        {
            
            #ACCESS GLOBALS
            global $wpdb,$hmenu_helper,$woocommerce;
            
            if (class_exists('WooCommerce')) {
                #ACTIVE
                $cart_items = $woocommerce->cart->cart_contents_count;
                $cart_link =  get_permalink(get_option('woocommerce_cart_page_id'));
                echo json_encode(array(
                    'status' => 'active',
                    'count' => $cart_items,
                    'url' => $cart_link
                ));
                exit();
            } else {
                #NOT ACTIVE
                echo json_encode(array(
                    'status' => 'not'
                ));
                exit();
            }
        }
        
        #GET FONTS
        public function hmenu_get_frontend_fonts()
        {
            global $wpdb;
            
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_font_pack WHERE deleted = '0' ORDER BY created DESC");
                
            #CREATE OBJECT
            $font_object = array(
                'fonts'=> array()
            );
            
            if ($result) {
                foreach ($result as $fontpack) {
                    array_push($font_object['fonts'], array(
                        'fontId' => $fontpack->fontId,
                        'fontName' => $fontpack->fontName
                    ));
                }
            }
                
            echo json_encode($font_object);
            exit();
        }
        
        #GET FONTS
        public function hmenu_check_menu_status()
        {
            global $wpdb;
            
            $id = $_POST['id'];
            
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE menuId = '".$id."' AND deleted = '0' ORDER BY created DESC LIMIT 1");
                
            if ($result) {
                $status = true;
            } else {
                $status = false;
            }
                
            echo json_encode($status);
            exit();
        }
    }
