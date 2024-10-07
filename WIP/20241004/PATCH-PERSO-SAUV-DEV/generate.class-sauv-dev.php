<?php

    #GENERATE CLASS
    class hmenu_class_generate extends hmenu_backend
    {
        
        #CLASS VARS
        private $plugin_dir;
        
        private $frontend_directory = "/_frontend_files/";
        private $css_directory = "/_css/";
        private $js_directory = "/_js_files/";
        private $html_directory = "/_html/";
        
        private $menu_object;
        private $frontend_object;
        private $obj_menu;
        private $obj_nav_items;
        private $obj_main_styles;
        private $obj_search_styles;
        private $obj_dropdown_styles;
        private $obj_mega_styles;
        private $obj_mega_font_styles;
        private $obj_social_items;
        
        private $global_mobile_res;
        private $global_current_url;
        private $global_current_id;
        
        #CONSTRUCT
        public function __construct($plugin_dir)
        {
            $this->plugin_dir = $plugin_dir;
        }
        
        #FRONTEND CALL
        public function hmenu_frontend_call($frontend_object, $current_url, $current_id)
        {
            
            #SET
            $this->frontend_object = $frontend_object;
            
            #MENU OBJECT GLOBALS
            $this->obj_menu = $this->frontend_object['menu'];
            $this->obj_main_styles = $this->frontend_object['main_styles'][0];
            $this->obj_dropdown_styles = $this->frontend_object['dropdown_styles'][0];
            $this->obj_mega_styles = $this->frontend_object['mega_styles'][0];
            $this->obj_mobile_styles = $this->frontend_object['mobile_styles'][0];
            $this->obj_mega_font_styles = $this->frontend_object['mega_font_styles'];
            $this->obj_social_items = $this->frontend_object['social_items'];
            $this->obj_search_styles = $this->frontend_object['search_styles'][0];
            $this->obj_menu = $this->frontend_object['menu'];
            $this->obj_nav_items = $this->frontend_object['nav_items'];
            
            $this->global_current_url = $current_url;
            $this->global_current_id = $current_id;
            
            return $this->hmenu_create_html();
        }
        
        #GENERATE FILES
        public function hmenu_generate_files($menu_object = null, $js = true)
        {
            global $wpdb;
            
            #MAIN MENU OBJECT
            $get = new hmenu_class_get($this->plugin_dir);
            $this->menu_object = $menu_object != null ? $menu_object : $get->hmenu_get_main_menu_object(intval($_POST['menu_id']), false);
            ;
            $menu = $this->menu_object['menu'];
            
            #CREATE OBJECT
            $test_object = array(
                'stuff' => array()
            );
            
            #MENU HTML DIRECTORY
            $menu_id = $menu['menuId'];
            $menu_directory = '_menu_' . $menu_id . '/';
                
            #CHECK IF DIRECTORY EXISTS
            if (!is_dir($this->plugin_dir . $this->frontend_directory . $menu_directory)) {
                
                #CREATE FILES DIRECTORY
                if (!is_dir($this->plugin_dir . $this->frontend_directory)) {
                    mkdir($this->plugin_dir . $this->frontend_directory);
                }
                
                #CREATE SUB FOLDERS
                $this->hmenu_create_sub_folders($this->frontend_directory, $menu_directory);
            } else {
            
                //LOOP THORUGH DIRECTORY AND REMOVE OLD FILES
                if ($handle = opendir($this->plugin_dir . $this->frontend_directory . $menu_directory)) {
                    while (false !== ($file = readdir($handle))) {
                        if ('.' === $file) {
                            continue;
                        }
                        if ('..' === $file) {
                            continue;
                        }
                                            
                        $test_object['stuff'][] = array( 'file' => $file );
                        
                        #REMOVE DIRECTORY
                        $this->hmenu_remove_directory($file, $menu_directory);
                    }
                    
                    #CLOSE DIRECTORY
                    closedir($handle);
                    
                    #CREATE SUB FOLDERS
                    $this->hmenu_create_sub_folders($this->frontend_directory, $menu_directory);
                }
            }
            
            if ($js) {
                echo json_encode(true);
                exit();
            } else {
                return true;
            }
        }
        
        #RUN SUBFOLDER CREATION , $json_object
        private function hmenu_create_sub_folders($parent_directory, $menu_directory)
        {
                        
            /* RUN CSS */
            
            #CREATE CSS DIRECTORY
            if (!is_dir($this->plugin_dir . $parent_directory . $menu_directory)) {
                mkdir($this->plugin_dir . $parent_directory . $menu_directory);
            };
            if (!is_dir($this->plugin_dir . $parent_directory . $menu_directory . $this->css_directory)) {
                mkdir($this->plugin_dir . $parent_directory . $menu_directory . $this->css_directory);
            };
                    
            #RUN CREATE CSS
            $this->hmenu_create_css($this->plugin_dir . $parent_directory . $menu_directory . $this->css_directory);
            
            /* RUN JS */
            
            #CREATE JS DIRECTORY
            if (!is_dir($this->plugin_dir . $parent_directory)) {
                mkdir($this->plugin_dir . $parent_directory);
            };
            if (!is_dir($this->plugin_dir . $parent_directory . $this->js_directory)) {
                mkdir($this->plugin_dir . $parent_directory . $this->js_directory);
            };
            
            $this->hmenu_create_js($this->plugin_dir . $parent_directory . $this->js_directory);
        }
        
        #CREATE CSS FILES
        private function hmenu_create_css($location)
        { #GENERATE THE FIRST LINE OF CSS - 26 NOVEMBER 2014 AND THE START OF SOMETHING BEAUTIFUL
            
            #CREATE FILE
            $css_file = fopen($location . '/hero_menu_styles.css', "w");
            
            #MENU OBJECT GLOBALS
            $this->obj_menu = $this->menu_object['menu'];
            $this->obj_main_styles = $this->menu_object['main_styles'][0];
            $this->obj_dropdown_styles = $this->menu_object['dropdown_styles'][0];
            $this->obj_mega_styles = $this->menu_object['mega_styles'][0];
            $this->obj_mobile_styles = $this->menu_object['mobile_styles'][0];
            $this->obj_mega_font_styles = $this->menu_object['mega_font_styles'];
            $this->obj_social_items = $this->menu_object['social_items'];
            $this->obj_search_styles = $this->menu_object['search_styles'][0];
            $this->obj_menu = $this->menu_object['menu'];
            $this->obj_nav_items = $this->menu_object['nav_items'];
            
            #FILE CONTENTS
            $code =
            "
				@charset 'utf-8';
				/* CSS DOCUMENT */
			";
                        
            #MAIN HOLDER
            
            #menu main bar
            $menu_dimentions = $this->obj_main_styles['menuBarDimentions'];

            #eyebrow
            if ($this->obj_main_styles['eyebrow'] > 0) {
                $eyebrow_height = 30 + $this->obj_main_styles['menuBarHeight'] . 'px';
            } else {
                $eyebrow_height = 0 + $this->obj_main_styles['menuBarHeight'] . 'px';
            }

            if ($menu_dimentions == 'full') {
                $menu_width = 'auto';
                $menu_height = $this->obj_main_styles['menuBarHeight'] . 'px';
            } else {
                $menu_width = $this->obj_main_styles['menuBarWidth'] . 'px';
                $menu_height = $this->obj_main_styles['menuBarHeight'] . 'px';
            }
                
            #zindex
            if ($this->obj_main_styles['zindex']) {
                $menu_zindex = $this->obj_main_styles['zindex'];
            } else {
                $menu_zindex = '9999';
            };

            $code .=
            "
				/* menu main holder */
					/* 	
						NOTES: 
						Main holder for entire nav, regardless of width or height, this will wrap it all.
					*/
				.hmenu_wrapper_state_".$this->obj_menu['menuId']."{
					height:".$eyebrow_height.";
				}
				#hmenu_load_".$this->obj_menu['menuId']."{
					display:table; /* This used to have !important - removed to allow custom styles to take affect */
				}
				#hmenu_load_".$this->obj_menu['menuId']." *{
					-webkit-transform: none;
					-moz-transform: none;
					-ms-transform: none;
					-o-transform: none;
					transform: none;
				}
				#hmenu_load_".$this->obj_menu['menuId']."{
					position:relative;					
					z-index:".$menu_zindex.";
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_main_holder{
					max-width:".$menu_width."; /* width of menu, can be 100% or fixed '1050px' */
					height:".$menu_height.";
					display:block;
					z-index:".$menu_zindex.";
					margin:0 auto;
				}
			";

            if ($this->obj_main_styles['eyebrow'] > 0) {
                $code .=
                    "
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_main_holder{
						margin-top:30px;
					}
				";
                $code .=
                    "
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_main_holder .hmenu_eyebrow{
						background-color:".$this->obj_main_styles['eyeBackground'].";
						color:".$this->obj_main_styles['eyeColor'].";
						font-family:".$this->obj_main_styles['fontFamily'].";
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_main_holder .hmenu_eyebrow a{
						text-decoration:none;
						color:".$this->obj_main_styles['eyeColor'].";
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_main_holder .hmenu_eyebrow a:hover{
						text-decoration:none;
						color:".$this->obj_main_styles['eyeColorHover'].";
					}
				";
            }
            
            if ($this->obj_main_styles['sticky'] > 0) {
                $the_padding = $this->obj_main_styles['menuBarHeight'] + 15;
                $code .=
                "
					#hmenu_load_".$this->obj_menu['menuId']."{
						position:relative;
						top:0;
						left:0;
						z-index:".$menu_zindex.";
					}
				";
            }
            
            $code .=
            "				
				/* main holder shadow */	
				".$this->hmenu_box_shadow(
                $this->obj_menu['menuId'],
                '.hmenu_main_holder',
                $this->obj_main_styles['shadow'],
                $this->obj_main_styles['shadowRadius'],
                $this->obj_main_styles['shadowColor'],
                $this->obj_main_styles['shadowTransparency']
            )."
				
				/* main holder border */	
				".$this->hmenu_border_color(
                $this->obj_menu['menuId'],
                '.hmenu_main_holder',
                $this->obj_main_styles['borderType'],
                $this->obj_main_styles['borderColor'],
                $this->obj_main_styles['borderTransparency'],
                $this->obj_main_styles['border']
            )."
				
				/* main holder border radius */	
				".$this->hmenu_border_radius(
                $this->obj_menu['menuId'],
                '.hmenu_main_holder',
                $this->obj_main_styles['border']
            )."
				
			";
            if ($this->obj_main_styles['bgMainImage'] > 0) {
                $code .=
                "	
					/* menu bg image */
					#hmenu_load_".$this->obj_menu['menuId']." #hmenu_holder_".$this->obj_menu['menuId']."{
						background-image:url(".$this->obj_main_styles['bgMainImageUrl'].");
						background-repeat:".$this->obj_main_styles['bgMainImageRepeat'].";
						background-position:".$this->obj_main_styles['bgMainImagePosition'].";
					}
				";
            }
            $code .=
            "	
				/*menu bg */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_main_holder',
                $this->obj_main_styles['bgMenuGradient'],
                $this->obj_main_styles['bgMenuStartColor'],
                $this->obj_main_styles['bgMenuEndColor'],
                $this->obj_main_styles['bgMenuGradientPath'],
                $this->obj_main_styles['bgMenuTransparency'],
                'normal'
            )."		
			";
            
            #MAIN LOGO
            
            $code .=
            "
				/* menu main holder */
					/* 	
						NOTES: 
						Main holder for entire nav, regardless of width or height, this will wrap it all.
					*/
				/* menu logo */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_logo{  
					height:inherit; 
					line-height:".$menu_height."; /* equal to the height of the menu */ 
					float:left; 
					text-align:center; 
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_logo img{ 
					vertical-align:top; 
					width:auto; 
					max-width:100%; 
					position:relative; 
					max-height:".$this->hmenu_get_unique_data('_logo_height').";	
					border:0;	
                    top: 50%;
                    transform: translateY(-50%);			
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_logo a{
					display:inline-block !important;
                    height: inherit;
				}
			";
            
            #MAIN INNER HOLDER
            
            #inner bar
            $menu_nav_dimentions = $this->obj_main_styles['navBarDimentions'];
                
            if ($menu_nav_dimentions == 'full') {
                $menu_nav_width = '100%';
                $menu_nav_height = $this->obj_main_styles['menuBarHeight'] . 'px';
            } else {
                $menu_nav_width = $this->obj_main_styles['navBarWidth'] . 'px';
                $menu_nav_height = $this->obj_main_styles['menuBarHeight'] . 'px';
            }
            
            $code .=
            "
				/* menu inner holder */
					/* 	
						NOTES: 
						Inner holder for all items, used for fixed width of the inner items
					*/
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_inner_holder{
					width:".$menu_nav_width."; 
					height:inherit;
					margin:0 auto;
					position:relative;
				}
			";

            $eye_padding_left = 0;
            $eye_padding_right = 0;

            if ($this->obj_main_styles['eyePaddingLeft']) {
                $eye_padding_left = $this->obj_main_styles['eyePaddingLeft'] . 'px';
            }

            if ($this->obj_main_styles['eyePaddingRight']) {
                $eye_padding_right = $this->obj_main_styles['eyePaddingRight'] . 'px';
            }

            $code .=
            "
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_eyebrow .hmenu_eyebrow_inner{
					width:".$menu_nav_width."; 
					height:inherit;
					margin:0 auto;
					padding-left:".$eye_padding_left.";
					padding-right:".$eye_padding_right.";
				}
			";
            
            #INNER CONTAINER: LEFT, RIGHT AND CENTER
            
            $code .=
            "
				/* menu position holders */
					/* 	
						NOTES: 
						Postion holder for left, right and center
					*/
				.hmenu_left,
				.hmenu_right{
					display:table;
					height:inherit;
				}
				.hmenu_left{
					float:left;
				}
				.hmenu_center{
					left:50%;
					position:absolute;
					display:none;					
					height:inherit;
				}
				.hmenu_right{
					float:right;
				}
				.hmenu_mobile_menu_toggle{
					width:42px;
					height:42px;
					position:absolute;
					right:0; 
					top:0;
					display:none;
				}				
			";
            
            #INNER HOLDERS: NAV, SOCIAL, SEARCH AND PRODUCT
                                
            $code .=
            "
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_remove_mega{
					display: none !important;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_grp_devider,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_item_devider{
					float:left;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder{
					padding-left:5px;
					padding-right:5px;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_show{
					display:none;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder a{
					outline:none;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_holder,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder ul{
					float:left;
				}
				.hmenu_navigation_holder,
				.hmenu_search_holder,
				.hmenu_social_holder,
				.hmenu_product_holder,
				.hmenu_toggle_holder{
					display:table;
					height:inherit;
				}
				#hmenu_load_".$this->obj_menu['menuId']." ul{
					margin: auto !important;
					padding: 0 !important;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder > ul,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder > ul,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder > ul{
					list-style:none;
					height:inherit;
					margin: auto !important;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder > ul li,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder > ul li,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder > ul li{
					float:left;
					height:inherit;
					display:table;
					position:relative;
					cursor:pointer;
					margin:0;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder > ul > li > a,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder > ul > li > a,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder > ul > li > a{
					display:table-cell;
					vertical-align:middle;
					text-decoration:none;
					font-family:'".$this->obj_main_styles['fontFamily']."';
					font-weight:".$this->obj_main_styles['fontWeight'].";
					color:".$this->obj_main_styles['fontColor'].";
					padding:0;
					font-size:".$this->obj_main_styles['fontSize'].$this->obj_main_styles['fontSizing'].";
					position:relative;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a span{
					float:left;
				}				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_wrap{
					padding:10px 0;
					display:table;
					margin:1px;
				}
                #hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder.hmenu_mobile_show .hmenu_wrap {
                    padding: 0px;
                    margin: 0px;
                }
            ";
            if ($this->obj_mobile_styles['menuBarbuttonColor']) {
                $code .= "
                    #hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder.hmenu_mobile_show .hmenu_wrap:before {
                        color: ".$this->obj_mobile_styles['menuBarbuttonColor'].";
                    }
                    ";
            } else {
                $code .= "
                    #hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder.hmenu_mobile_show .hmenu_wrap:before {
                        color: ".$this->obj_mobile_styles['fontBarColor'].";
                    }
                    ";
            }
           
            $code .="

                
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a > .hmenu_wrap:before{
					font-size:20px;
					float:left;
					padding-left:".$this->hmenu_get_unique_data('_padding_left').";
					margin:0;
				}
				
				".$this->hmenu_get_icon_styles('main')."
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a > .hmenu_wrap:after{
					font-size:15px;
					float:left;
					".$this->hmenu_get_unique_data('_menu_arrows')."
				}
				
				/* hover states */
				#hmenu_load_".$this->obj_menu['menuId']." ul.hmenu_hover_color > li > a:hover{ /* normal */	
					color:".$this->obj_main_styles['fontHoverColor']." !important;
					background-color:transparent;
				}
				
				
				/* full hover */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_navigation_holder ul.hmenu_full_hover > li:hover',
                $this->obj_main_styles['bgHoverGradient'],
                $this->obj_main_styles['bgHoverStartColor'],
                $this->obj_main_styles['bgHoverEndColor'],
                $this->obj_main_styles['bgHoverGradientPath'],
                $this->obj_main_styles['bgHoverTransparency'],
                'normal'
            )."	
				
				/* full active state */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_navigation_holder ul.hmenu_full_hover > li.hmenu_active_nav',
                $this->obj_main_styles['bgHoverGradient'],
                $this->obj_main_styles['bgHoverStartColor'],
                $this->obj_main_styles['bgHoverEndColor'],
                $this->obj_main_styles['bgHoverGradientPath'],
                $this->obj_main_styles['bgHoverTransparency'],
                'normal'
            )."	
				
				/* font hover color */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li:hover > a > div,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li:hover > a > div:before,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li:hover > a > div:after,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li.hmenu_active_nav > a > div,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li.hmenu_active_nav > a > div:before,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li.hmenu_active_nav > a > div:after{
					color:".$this->obj_main_styles['fontHoverColor'].";
				}
				
				/* border */
				".$this->hmenu_border_color(
                $this->obj_menu['menuId'],
                '.hmenu_navigation_holder ul.hmenu_border_hover > li > a:hover > .hmenu_wrap',
                'border',
                $this->obj_main_styles['bgHoverStartColor'],
                $this->obj_main_styles['bgHoverTransparency'],
                1
            )."
				
				/* border active state */
				".$this->hmenu_border_color(
                $this->obj_menu['menuId'],
                '.hmenu_navigation_holder ul.hmenu_border_hover > li.hmenu_active_nav > a > .hmenu_wrap',
                'border',
                $this->obj_main_styles['bgHoverStartColor'],
                $this->obj_main_styles['bgHoverTransparency'],
                1
            )."
	
				/* underline */
				".$this->hmenu_border_color(
                $this->obj_menu['menuId'],
                '.hmenu_navigation_holder ul.hmenu_underline_hover li a:hover .hmenu_wrap',
                'border-bottom',
                $this->obj_main_styles['bgHoverStartColor'],
                $this->obj_main_styles['bgHoverTransparency'],
                1
            )."
				
				/* underline active state */
				".$this->hmenu_border_color(
                $this->obj_menu['menuId'],
                '.hmenu_navigation_holder ul.hmenu_underline_hover > li.hmenu_active_nav > a > .hmenu_wrap',
                'border-bottom',
                $this->obj_main_styles['bgHoverStartColor'],
                $this->obj_main_styles['bgHoverTransparency'],
                1
            )."
						
				/* dropdowns */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub{
					position:absolute;					
					".$this->hmenu_get_unique_data('_hmenu_sub_menu_width').";
					display:table;					
					top:".$menu_nav_height."; /* this takes the height of the menu + 1margin */
					left:0;
				}
				
				/* sub sub menu position */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li .hmenu_sub{
					top:0;
					".$this->hmenu_get_unique_data('_hmenu_sub_menu_left_pos').";
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li{
					width:100%;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap{
					display:block;
					".$this->hmenu_get_unique_data('_hmenu_sub_menu_item_padding').";
					margin:0;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." ul.hmenu_underline_hover .hmenu_sub > ul > li > a:hover > .hmenu_wrap{
					border-bottom: none !important;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap > span{
					padding:0;
					float:none;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']."  .hmenu_sub > ul > li > a{
					display:block;
					width:100%;
					font-family: '".$this->obj_dropdown_styles['fontFamily']."';
					font-weight:".$this->obj_dropdown_styles['fontWeight'].";
					color:".$this->obj_dropdown_styles['fontColor'].";
					font-size:".$this->obj_dropdown_styles['fontSize'].$this->obj_dropdown_styles['fontSizing'].";
					text-decoration:none;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap:before{
					font-size:20px;
					float:left;
					padding-right:10px;
					color:".$this->obj_dropdown_styles['fontColor'].";
					text-decoration:none;
					margin:0;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap:after{
					font-size:14px;
					float:right;
					padding-right:0;
					".$this->hmenu_get_unique_data('_hmenu_sub_menu_arrows').";
					margin:0;
					text-decoration:none;
				}
				
			";
            if ($this->obj_dropdown_styles['arrows'] == 0) {
                $code .=
                "
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap:after{
						display:none;
					}
				";
            }
            $code .=
            "
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_drop_devider ul li{					
					".$this->hmenu_get_unique_data('_dropdown_devider')."
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_no_bottom_border{
					border-bottom:none !important;
				}				
								
				/* drop down line height */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > div > span,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap:before,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap:after{
					line-height:".$this->obj_dropdown_styles['fontSize'].$this->obj_dropdown_styles['fontSizing']." !important; /* takes the size of the dropdown font size */
				}
				
				/* sub bg */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_sub',
                $this->obj_dropdown_styles['bgDropGradient'],
                $this->obj_dropdown_styles['bgDropStartColor'],
                $this->obj_dropdown_styles['bgDropEndColor'],
                $this->obj_dropdown_styles['bgDropGradientPath'],
                $this->obj_dropdown_styles['bgDropTransparency'],
                'normal'
            )."	
				
				/* sub menu bg hover */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_sub > ul > li:hover > a .hmenu_wrap',
                $this->obj_dropdown_styles['bgHoverGradient'],
                $this->obj_dropdown_styles['bgHoverStartColor'],
                $this->obj_dropdown_styles['bgHoverEndColor'],
                $this->obj_dropdown_styles['bgHoverGradientPath'],
                $this->obj_dropdown_styles['bgHoverTransparency'],
                'normal'
            )."	
				
				/* sub menu bg active state */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_sub > ul > li.hmenu_active_nav > a .hmenu_wrap',
                $this->obj_dropdown_styles['bgHoverGradient'],
                $this->obj_dropdown_styles['bgHoverStartColor'],
                $this->obj_dropdown_styles['bgHoverEndColor'],
                $this->obj_dropdown_styles['bgHoverGradientPath'],
                $this->obj_dropdown_styles['bgHoverTransparency'],
                'normal'
            )."	
				
				/* sub holder shadow */	
				".$this->hmenu_box_shadow(
                $this->obj_menu['menuId'],
                '.hmenu_sub',
                $this->obj_dropdown_styles['shadow'],
                $this->obj_dropdown_styles['shadowRadius'],
                $this->obj_dropdown_styles['shadowColor'],
                $this->obj_dropdown_styles['shadowTransparency']
            )."
				
				/* sub holder border */	
				".$this->hmenu_border_color(
                $this->obj_menu['menuId'],
                '.hmenu_sub',
                $this->obj_dropdown_styles['borderType'],
                $this->obj_dropdown_styles['borderColor'],
                $this->obj_dropdown_styles['borderTransparency'],
                $this->obj_dropdown_styles['border']
            )."
				
				/* sub holder border radius */	
				".$this->hmenu_border_radius(
                $this->obj_menu['menuId'],
                '.hmenu_sub',
                $this->obj_dropdown_styles['border']
            )."
				
				/* font hover color */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li:hover > a > div,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li:hover > a > .hmenu_wrap:before,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li:hover > a > .hmenu_wrap:after,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li.hmenu_active_nav > a > div,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li.hmenu_active_nav > a > .hmenu_wrap:before,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li.hmenu_active_nav > a > .hmenu_wrap:after{
					color:".$this->obj_dropdown_styles['fontHoverColor']." !important;
				}
				
				/* social and product padding */
				.hmenu_social_holder > ul li a,
				.hmenu_product_holder > ul li a,
				.hmenu_toggle_holder > ul li a{
					padding:0 5px;
				}
				
				/* social and product icon sizing */
				.hmenu_social_holder > ul li a .hmenu_wrap:before,
				.hmenu_product_holder > ul li a .hmenu_wrap:before,
				.hmenu_toggle_holder > ul li a .hmenu_wrap:before{
					font-size:20px;
					float:left;
				}
				
				".$this->hmenu_get_social_css($this->obj_main_styles['social'])."
				
				".$this->hmenu_get_unique_data('_cart_css')."
					
				".$this->hmenu_get_devider_css(
                $menu_nav_height,
                'normal',
                'item',
                $this->obj_main_styles['devider'],
                $this->obj_main_styles['deviderTransparency'],
                $this->obj_main_styles['deviderColor'],
                $this->obj_main_styles['deviderSizing']
            )."
				
				".$this->hmenu_get_devider_css(
                $menu_nav_height,
                'normal',
                'grp',
                $this->obj_main_styles['groupDevider'],
                $this->obj_main_styles['groupTransparency'],
                $this->obj_main_styles['groupColor'],
                $this->obj_main_styles['groupSizing']
            )."				
				
				/* search */
				".$this->hmenu_get_search_css($this->obj_main_styles['search'], $this->obj_search_styles['type']) /* Search Enabled, Search Type */ ."				
				
				/* mega woo styles */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_woo_pricing{
					display:table;
					padding:0 0 5px;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_woo_pricing div{
					width:100%;
					display:table;
					padding:0 10px 0 0;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_woo_pricing .hmenu_mega_price{
					font-family:".$this->obj_mega_styles['wooPriceFamily'].";
					font-weight:".$this->obj_mega_styles['wooPriceWeight'].";
					font-size:".$this->obj_mega_styles['wooPriceSize']."".$this->obj_mega_styles['wooPriceSizing'].";
					color:".$this->obj_mega_styles['wooPriceColor'].";
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_woo_pricing .hmenu_mega_price_old{
					font-family:".$this->obj_mega_styles['wooPriceOldFamily'].";
					font-weight:".$this->obj_mega_styles['wooPriceOldWeight'].";
					font-size:".$this->obj_mega_styles['wooPriceOldSize']."".$this->obj_mega_styles['wooPriceOldSizing'].";
					text-decoration:line-through;
					color:".$this->obj_mega_styles['wooPriceOldColor'].";
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_woo_pricing .hmenu_mega_price_sale{
					font-family:".$this->obj_mega_styles['wooPriceSaleFamily'].";
					font-weight:".$this->obj_mega_styles['wooPriceSaleWeight'].";
					font-size:".$this->obj_mega_styles['wooPriceSaleSize']."".$this->obj_mega_styles['wooPriceSaleSizing'].";
					color:".$this->obj_mega_styles['wooPriceSaleColor'].";
					line-height:".$this->obj_mega_styles['wooPriceSaleSize']."".$this->obj_mega_styles['wooPriceSaleSizing'].";
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_prod_btn{
					font-family:".$this->obj_mega_styles['wooBtnFontFamily'].";
					font-weight:".$this->obj_mega_styles['wooBtnFontWeight'].";
					font-size:".$this->obj_mega_styles['wooBtnFontSize']."".$this->obj_mega_styles['wooBtnFontSizing'].";
					color:".$this->obj_mega_styles['wooBtnFontColor'].";
					text-decoration:".$this->obj_mega_styles['wooBtnFontDecoration'].";
				}
				
				/* mega styles */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub{
					width:100%;
					position:absolute;
					top:".$menu_nav_height.";
					padding:0 0 0 0;
					left:0;
					z-index:9999;
					cursor:default;
					overflow:hidden;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_price{
					font-size:14px !important;
					font-weight:400 !important;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_li{
					position:inherit !important;
				}
				
				".$this->hmenu_get_unique_data('_return_padding')."
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub h2{
					margin:0 0 8px 0 !important; 
					padding:5px 10px 8px 10px !important;
					font-family: '".$this->obj_mega_font_styles[0]['fontFamily']."' !important;
					font-size:".$this->obj_mega_font_styles[0]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing']." !important;
					color:".$this->obj_mega_font_styles[0]['fontColor']." !important;
					font-weight:".$this->obj_mega_font_styles[0]['fontWeight']." !important;
					display:inherit !important;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub h3{	
					font-family: '".$this->obj_mega_font_styles[2]['fontFamily']."' !important;
					font-size:".$this->obj_mega_font_styles[2]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing']." !important;
					color:".$this->obj_mega_font_styles[2]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[2]['fontWeight']." !important;
					line-height:".$this->obj_mega_font_styles[2]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing']." !important;
					padding:5px 0 5px 0 !important;
					margin:0 !important;
					display:block;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub span,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_item{					
					display:block;
					font-family: '".$this->obj_mega_font_styles[3]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[3]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[3]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[3]['fontWeight'].";
					padding:0 !important;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub a{
					text-decoration:none;
				}
				/* global content styles for mega */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_post_item,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_item{
					padding:10px 0 10px 0 !important;	
					display:table !important;
					cursor:pointer;
				}
				/* post styles */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_post_item{
					width:100%;	
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_post_img{
					float:left;
					background-position:center;
					background-size:cover;
					width:30%;
					margin:0 0 0 10px;
					height:60px;
					cursor:pointer;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_post_content{
					display:table;
					padding:0 10px 0 10px;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_bottom_border{
					border-bottom: 1px solid rgb(".$this->hmenu_hex_to_rgb($this->obj_mega_styles['deviderColor']).", ".$this->obj_mega_styles['deviderTransparency']."); border-bottom: 1px solid rgba(".$this->hmenu_hex_to_rgb($this->obj_mega_styles['deviderColor']).", ".$this->obj_mega_styles['deviderTransparency']."); -webkit-background-clip: padding-box; background-clip: padding-box;
				}
				/* text styles */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_text_item{
					padding:10px 10px 10px 10px;
					display:block;
					font-family: '".$this->obj_mega_font_styles[1]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[1]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[1]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[1]['fontWeight'].";
				}
				
				/* list styles */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_item{
					width:100%;
					display:table;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_item:before{
					float:left;
					padding:0 0 0 10px;
					font-size:16px;
					line-height:".$this->obj_mega_font_styles[2]['fontSize'].$this->obj_mega_font_styles[2]['fontSizing'].";
					color:".$this->obj_mega_font_styles[2]['fontColor'].";
				}
				
				".$this->hmenu_get_list_icon_styles()."
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_content{
					display:table;
					padding:0 10px 0 10px;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_content span{
					display:block;
					padding:0 10px 0 10px;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_content h3{
					padding:0 0 5px 0 !important;
					font-family: '".$this->obj_mega_font_styles[2]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[2]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[2]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[2]['fontWeight'].";
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_list_body_text{
					padding:10px 10px 10px 10px;
					margin-bottom:10px;
					display:block;
					font-family: '".$this->obj_mega_font_styles[1]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[1]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[1]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[1]['fontWeight'].";
				}
				/* images styles */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_image_holder{
					width:100%;
					display:table !important;
					cursor:pointer;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_image_inner{ /* give this a fixed height when content is loaded with jquery */
					display:block;
					padding:10px 10px 10px 10px;
					position:relative;
				}
				/* layout 1 */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_one .hmenu_image_heading{
					width:100%;
					padding:0 0 10px 0;
					font-family: '".$this->obj_mega_font_styles[2]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[2]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[2]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[2]['fontWeight'].";
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_one .hmenu_image{
					width:100%;
					height:130px;
					background-position:center top;
					background-size:cover;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_one .hmenu_image_desc{
					display:block;
					font-family: '".$this->obj_mega_font_styles[1]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[1]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[1]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[1]['fontWeight'].";
					padding:10px 0 0 0;
				}
				/* layout 2 */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_two .hmenu_image_heading{
					width:100%;
					padding:0 0 10px 0;
					font-family: '".$this->obj_mega_font_styles[2]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[2]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[2]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[2]['fontWeight'].";
					position:absolute;
					text-align:center;
					top:40%;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_two .hmenu_image{
					width:100%;
					height:200px;
					background-position:center;
					background-size:cover;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_two .hmenu_image_desc{
					display:block;
					font-family: '".$this->obj_mega_font_styles[1]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[1]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[1]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[1]['fontWeight'].";
					padding:10px 0 0 0;
				}
				/* layout 3 */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_three .hmenu_image_inner{
					padding:10px 0 10px 0;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_three .hmenu_image_heading{
					width:100%;
					font-family: '".$this->obj_mega_font_styles[2]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[2]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[2]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[2]['fontWeight'].";
					text-align:left;
					top:0;
					margin:0 0 10px 0;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_three .hmenu_image{
					width:100%;
					height:inherit;
					background-position:center;
					background-size:cover;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_three .hmenu_image_desc_wrap{
					position:absolute;
					bottom:10px;
					width:100%;
					display:table;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_layout_three .hmenu_image_desc{
					display:block;
					font-family: '".$this->obj_mega_font_styles[1]['fontFamily']."';
					font-size:".$this->obj_mega_font_styles[1]['fontSize'].$this->obj_mega_font_styles[0]['fontSizing'].";
					color:".$this->obj_mega_font_styles[1]['fontColor'].";
					font-weight:".$this->obj_mega_font_styles[1]['fontWeight'].";
					padding:10px 5px 10px 5px;					
				}
				
				/* image layout 3 bg */
				.hmenu_layout_three .hmenu_image_desc_wrap{ background: -moz-linear-gradient(top,  rgba(0,0,0,0.5) 0%, rgba(0,0,0,0.5) 100%); } /* full */
				.hmenu_layout_three .hmenu_image_desc_wrap{ background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(0,0,0,0.5)), color-stop(100%,rgba(0,0,0,0.5))); }
				.hmenu_layout_three .hmenu_image_desc_wrap{ background: -webkit-linear-gradient(top,  rgba(0,0,0,0.5) 0%,rgba(0,0,0,0.5) 100%); }
				.hmenu_layout_three .hmenu_image_desc_wrap{ background: -o-linear-gradient(top,  rgba(0,0,0,0.5) 0%,rgba(0,0,0,0.5) 100%); }
				.hmenu_layout_three .hmenu_image_desc_wrap{ background: -ms-linear-gradient(top,  rgba(0,0,0,0.5) 0%,rgba(0,0,0,0.5) 100%); }
				.hmenu_layout_three .hmenu_image_desc_wrap{ background: linear-gradient(to bottom,  rgba(0,0,0,0.5) 0%,rgba(0,0,0,0.5) 100%); }
				.hmenu_layout_three .hmenu_image_desc_wrap{ filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#000000', endColorstr='#000000',GradientType=0 ); }
												
				/* mega hover */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_list_item:hover',
                $this->obj_mega_styles['bgHoverGradient'],
                $this->obj_mega_styles['bgHoverStartColor'],
                $this->obj_mega_styles['bgHoverEndColor'],
                $this->obj_mega_styles['bgHoverGradientPath'],
                $this->obj_mega_styles['bgHoverTransparency'],
                'normal'
            )."
				
				/* mega hover */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_post_item:hover',
                $this->obj_mega_styles['bgHoverGradient'],
                $this->obj_mega_styles['bgHoverStartColor'],
                $this->obj_mega_styles['bgHoverEndColor'],
                $this->obj_mega_styles['bgHoverGradientPath'],
                $this->obj_mega_styles['bgHoverTransparency'],
                'normal'
            )."
				
				.hmenu_unique_menu_".$this->obj_menu['menuId']." .hmenu_post_item:hover h3,
				.hmenu_unique_menu_".$this->obj_menu['menuId']." .hmenu_list_item:hover h3,
				.hmenu_unique_menu_".$this->obj_menu['menuId']." .hmenu_post_item:hover span,
				.hmenu_unique_menu_".$this->obj_menu['menuId']." .hmenu_list_item:hover span,
				.hmenu_unique_menu_".$this->obj_menu['menuId']." .hmenu_list_item:hover:before{
					color:".$this->obj_mega_styles['fontHoverColor']." !important
				}
				
				/* devider color for mega cols */
				".$this->hmenu_get_unique_data('_mega_devider')."
				
				/* col structure */
				.hmenu_col_1{ width: 8.333333333333332%; } 
				.hmenu_col_2{ width: 16.666666666666664%; } 
				.hmenu_col_3{ width: 25%; } 
				.hmenu_col_4{ width: 33.33333333333333%; } 
				.hmenu_col_5{ width: 41.66666666666667%; } 
				.hmenu_col_6{ width: 50%; } 
				.hmenu_col_7{ width: 58.333333333333336%; } 
				.hmenu_col_8{ width: 66.66666666666666%; } 
				.hmenu_col_9{ width: 75%; } 
				.hmenu_col_10{ width: 83.33333333333334%; } 
				.hmenu_col_11{ width: 91.66666666666666%; } 
				.hmenu_col_12{ width: 100%; }
				
				.hmenu_col_1,
				.hmenu_col_2,
				.hmenu_col_3,
				.hmenu_col_4,
				.hmenu_col_5,
				.hmenu_col_6,
				.hmenu_col_7,
				.hmenu_col_8,
				.hmenu_col_9,
				.hmenu_col_10,
				.hmenu_col_11,
				.hmenu_col_12,
				.hmenu_custom_5{ display:table; float:left; position:relative; }
				
				.hmenu_custom_5{ width:20%; }	
				
				/* mobile search and social holder */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_show_for_mobile{ 
					width:100%; 
					list-style:none; 
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_show_for_mobile li{ 
					width:100%; 
					display:table; 
					float:left; 
					color:#FFFFFF; 
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_search_holder{ 
					padding:10px 0;  
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_search_holder input{ 
					width:90%; 
					border:1px solid #efefef;
					background-color:".$this->obj_search_styles['backgroundColor']."; 
					margin:0 auto; 
					display:table; 
					outline:none; 
					padding:5px; 
					color:".$this->obj_search_styles['fontColor'].";
					font-family:'".$this->obj_search_styles['fontFamily']."';
					font-size:12px;
				}
				".$this->hmenu_get_search_border($this->obj_search_styles['border'], '.hmenu_mobile_search_holder input')."
			
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_search_holder .hmenu_search_btn{ position:absolute; right:5%; top:10px; display:table; }
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_search_holder .hmenu_search_btn:before{					
					line-height:30px !important;	
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_social_holder{ 
					padding:10px 0; 
					text-align:center; 
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_submit{ 
					display:none !important; 
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_inner_col{
					display:block;
					padding:0 5px 0 5px;
				}
				
				/* mega bg */
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_mega_sub',
                $this->obj_mega_styles['bgDropGradient'],
                $this->obj_mega_styles['bgDropStartColor'],
                $this->obj_mega_styles['bgDropEndColor'],
                $this->obj_mega_styles['bgDropGradientPath'],
                $this->obj_mega_styles['bgDropTransparency'],
                'normal'
            )."
				
				/* mega holder shadow */	
				".$this->hmenu_box_shadow(
                $this->obj_menu['menuId'],
                '.hmenu_mega_sub',
                $this->obj_mega_styles['shadow'],
                $this->obj_mega_styles['shadowRadius'],
                $this->obj_mega_styles['shadowColor'],
                $this->obj_mega_styles['shadowTransparency']
            )."
				
				/* mega holder border */	
				".$this->hmenu_border_color(
                $this->obj_menu['menuId'],
                '.hmenu_mega_sub',
                $this->obj_mega_styles['borderType'],
                $this->obj_mega_styles['borderColor'],
                $this->obj_mega_styles['borderTransparency'],
                $this->obj_mega_styles['border']
            )."
				
				/* mega holder border radius */	
				".$this->hmenu_border_radius(
                $this->obj_menu['menuId'],
                '.hmenu_mega_sub',
                $this->obj_mega_styles['border']
            )."
				
				/* the line height */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_wrap,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:before, /* main nav before line height */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:after, /* main nav after line height */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder > ul li a .hmenu_wrap:before, /* social line height */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder > ul li a .hmenu_wrap:before, /* product line height */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_container .hmenu_search_inner .hmenu_wrap /* search line height */{
					line-height:".$this->obj_main_styles['fontSize'].$this->obj_main_styles['fontSizing']."; /* this take the font size of the font - so that everything is aligned like a boss */
				}
			";
            $logoPaddingLeft = 0;
            $logoPaddingRight = 0;
            $logoMobilePaddingLeft = 0;
            $logoStickyPaddingLeft = 0;
            if ($this->obj_main_styles['logoPaddingLeft'] != '') {
                $logoPaddingLeft = $this->obj_main_styles['logoPaddingLeft'];
            }
            if ($this->obj_main_styles['logoPaddingRight'] != '') {
                $logoPaddingRight = $this->obj_main_styles['logoPaddingRight'];
            }
            if ($this->obj_main_styles['mobileLogoPaddingLeft'] != '') {
                $logoMobilePaddingLeft = $this->obj_main_styles['mobileLogoPaddingLeft'];
            }
            if ($this->obj_main_styles['stickyLogoPaddingLeft'] != '') {
                $logoStickyPaddingLeft = $this->obj_main_styles['stickyLogoPaddingLeft'];
            }
            $code .= "
				#hmenu_load_".$this->obj_menu['menuId']." .logo_main{
					padding-left: ".$logoPaddingLeft."px;
					padding-right: ".$logoPaddingRight."px
				}
				#hmenu_load_".$this->obj_menu['menuId']." .logo_mobile{
					padding-left: ".$logoMobilePaddingLeft."px
				}
				#hmenu_load_".$this->obj_menu['menuId']." .logo_sticky{
					padding-left: ".$logoStickyPaddingLeft."px
				}
			";
            $code .= "				
				#hmenu_load_".$this->obj_menu['menuId']." .logo_mobile,
				#hmenu_load_".$this->obj_menu['menuId']." .logo_sticky,
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_show_for_mobile{
					display:none;
				}
				
				.hmenu_cart_num_color{
					position: absolute;
					right: -3px;
					top: 0;
					background-color: ".$this->obj_main_styles['fontColor'].";
					display: none;
					padding: 3px;
					border-radius: 10px;
					color: ".$this->obj_main_styles['bgMenuStartColor'].";
					font-size: 9px;
					text-align: center;
					line-height: 6px;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .menu_responsive_label{
					display:none;
				}
				
				/* sticky styles */
				".$this->hmenu_get_sticky_styles(
                $this->obj_menu['menuId'],
                $this->obj_main_styles['sticky']
            )."
				
			";
            
            if ($this->obj_main_styles['siteResponsive'] > 0) {
                $mobile_res = $this->obj_main_styles['siteResponsiveOne'];
                $this->global_mobile_res = $this->obj_main_styles['siteResponsiveOne'];
                $tablet_res = $this->obj_main_styles['siteResponsiveTwo'];
                $desktop_res = $this->obj_main_styles['siteResponsiveThree'];
                $mobile_minus_res = $mobile_res - 1;
                $tablet_minus_res = $tablet_res - 1;
                $desktop_minus_res = $desktop_res - 1;
            } else {
                $mobile_res = 768;
                $this->global_mobile_res = 768;
                $tablet_res = 922;
                $desktop_res = 1200;
                $mobile_minus_res = $mobile_res - 1;
                $tablet_minus_res = $tablet_res - 1;
                $desktop_minus_res = $desktop_res - 1;
            }
            $mobile_height = $this->obj_mobile_styles['menuBarHeightMobile']."px";
            
            $the_margin_top = '0px';
            
            $explode_position = explode(',', $this->obj_menu['leftItems']);
            
            if (in_array('logo', $explode_position) && $this->obj_main_styles['logo'] == 0 && in_array('main', $explode_position)) {
                $the_margin_top = '40px';
            }
                        
            if ($this->obj_main_styles['siteResponsive'] > 0) {
                $code .=
            "
				/* media queries */
				@media (max-width: ".$mobile_minus_res."px) {
			";
            
                $code .=
                "   #hmenu_load_".$this->obj_menu['menuId']." .hmenu_logo{  
                        line-height:".$mobile_height."; /* equal to the height of the menu */ 
                    }
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub{
						width:100% !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap{
						display:table;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap{
						height:42px !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_hide_for_mobile,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_grp_devider{
						display:none;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_right,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_center,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_left{
						width: 100%;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_right{
						height:0px;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_right .hmenu_product_holder,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_center .hmenu_product_holder,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_left .hmenu_product_holder{
						position:absolute;
						right:40px;
						top:0;
					}	
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_center .hmenu_product_holder{
						top:-36px !important;
					}									
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_main_holder{
						height:".$mobile_height.";
					}
					/*menu bg */
					".$this->hmenu_background_color(
                    $this->obj_menu['menuId'],
                    '.hmenu_sticky_'.$this->obj_menu['menuId'] . ' .hmenu_main_holder',
                    $this->obj_main_styles['bgMenuGradient'],
                    $this->obj_main_styles['bgMenuStartColor'],
                    $this->obj_main_styles['bgMenuEndColor'],
                    $this->obj_main_styles['bgMenuGradientPath'],
                    $this->obj_main_styles['bgMenuTransparency'],
                    'sticky'
                )."	
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_logo{
						line-height:".$mobile_height.";
					}
					#hmenu_load_".$this->obj_menu['menuId']."{
						position:relative;
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_inner_holder {
						width: 100%;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_eyebrow .hmenu_eyebrow_inner {
						width: 100%;
					}
					
					/* ///////////////////////////////// MOBILE SPECIFIC STYLES */
					
					/* menu bar */
					".$this->hmenu_background_color(
                    $this->obj_menu['menuId'],
                    '#hmenu_load_'.$this->obj_menu['menuId'] . ' .hmenu_inner_holder',
                    $this->obj_mobile_styles['bgBarGradient'],
                    $this->obj_mobile_styles['bgBarStartColor'],
                    $this->obj_mobile_styles['bgBarEndColor'],
                    $this->obj_mobile_styles['bgBarGradientPath'],
                    $this->obj_mobile_styles['bgBarTransparency'],
                    'normal'
                )."
					
					#hmenu_load_".$this->obj_menu['menuId']." .icon_hero_default_thin_e645 {
						padding:0px;
					}
					 
					#hmenu_load_".$this->obj_menu['menuId']." .icon_hero_default_thin_e645:before {
						color: ".$this->obj_mobile_styles['menuBarbuttonColor'].";
						font-weight: ".$this->obj_mobile_styles['fontBarWeight'].";
						font-size: ".$this->obj_mobile_styles['fontBarSize'].$this->obj_mobile_styles['fontBarSizing'].";	
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .menu_responsive_label {
						color: ".$this->obj_mobile_styles['fontBarColor'].";
						font-family: ".$this->obj_mobile_styles['fontBarFamily'].";
						font-weight: ".$this->obj_mobile_styles['fontBarWeight'].";
						font-size: ".$this->obj_mobile_styles['fontBarSize'].$this->obj_mobile_styles['fontBarSizing'].";
						display:block;
						position:absolute;
						line-height:".$mobile_height.";
						left:50%;
					}
					
					/* menu bar */
					".$this->hmenu_background_color(
                    $this->obj_menu['menuId'],
                    '#hmenu_load_'.$this->obj_menu['menuId'] . ' .hmenu_navigation_holder',
                    $this->obj_mobile_styles['bgMenuGradient'],
                    $this->obj_mobile_styles['bgMenuStartColor'],
                    $this->obj_mobile_styles['bgMenuEndColor'],
                    $this->obj_mobile_styles['bgMenuGradientPath'],
                    $this->obj_mobile_styles['bgMenuTransparency'],
                    'normal'
                )."
					
					/* full hover */
					".$this->hmenu_background_color(
                    $this->obj_menu['menuId'],
                    '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_navigation_holder ul.hmenu_full_hover > li:hover',
                    $this->obj_mobile_styles['bgHoverGradient'],
                    $this->obj_mobile_styles['bgHoverStartColor'],
                    $this->obj_mobile_styles['bgHoverEndColor'],
                    $this->obj_mobile_styles['bgHoverGradientPath'],
                    $this->obj_mobile_styles['bgHoverTransparency'],
                    'normal'
                )."	
					
					/* full active state */
					".$this->hmenu_background_color(
                    $this->obj_menu['menuId'],
                    '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_navigation_holder ul.hmenu_full_hover > li.hmenu_active_nav',
                    $this->obj_mobile_styles['bgHoverGradient'],
                    $this->obj_mobile_styles['bgHoverStartColor'],
                    $this->obj_mobile_styles['bgHoverEndColor'],
                    $this->obj_mobile_styles['bgHoverGradientPath'],
                    $this->obj_mobile_styles['bgHoverTransparency'],
                    'normal'
                )."	
					
					/* font */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a > .hmenu_wrap{
						color: ".$this->obj_mobile_styles['fontMobileColor'].";
						font-family: ".$this->obj_mobile_styles['fontMobileFamily'].";
						font-weight: ".$this->obj_mobile_styles['fontMobileWeight'].";
						font-size: ".$this->obj_mobile_styles['fontMobileSize'].$this->obj_mobile_styles['fontMobileSizing'].";
					}					
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:after{
						font-size:15px !important;
					}
					
					/* font hover color */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li:hover > a > div,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li:hover > a > div:before,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li:hover > a > div:after,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li.hmenu_active_nav > a > div,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li.hmenu_active_nav > a > div:before,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li.hmenu_active_nav > a > div:after{
						color:".$this->obj_mobile_styles['fontMobileHoverColor']." !important;
					}
					
					/* ///////////////////////////////// MOBILE SPECIFIC STYLES */
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_remove_mega_mobile {
						display: none !important;
					}
					/* mobile */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_social_holder > a{
						width:auto !important;
						display:inline-table !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_social_holder > a div{
						height:auto !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_social_holder > a div:before{
						margin:0;
						padding:0 5px;
						display:block;
						line-height:normal !important;
						height:auto !important;
					}
					/* main nav */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_center{
						left:0 !important;
						margin-left: auto !important;
						width:100%;
						position:relative;
						clear:both !important;
						height:0;
					}
					/* main nav */
					 
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub{
						margin-left:auto !important;
						background-image:none !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub{
						background-image:none !important;
					}
					/* main nav */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul{
						height:auto;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:before{
						display:block !important;
						padding-left:5px;
						line-height:40px !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a > .hmenu_wrap:after{
						display:table-cell !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a, .hmenu_social_holder > ul li a, .hmenu_product_holder > ul li a{
						font-size:".$this->obj_mobile_styles['fontMobileSize'].$this->obj_mobile_styles['fontMobileSizing'].";
					}					
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder .hmenu_item_devider{
						display:none;
					}
					
					/* mega */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub h2{
						font-size:14px;
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap:after{
						margin:0 5px;
						text-decoration:none;
						padding-right:5px;
					}
					/* mobile content */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_show_for_mobile{
						display:table;
					}
					/* icons */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder > ul li a .hmenu_wrap:before, 
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder > ul li a .hmenu_wrap:before,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_btn:before{
						font-size:12px;
					}				
					
					/* toggle mobile nav */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder > ul li a .hmenu_wrap:before{
						font-size:".$this->obj_mobile_styles['menuBarbuttonSize']."px;
						line-height:".$mobile_height.";
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_slide{
						width:20px;
					}
					
					/* menu toggle */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_show{
						display:table !important;
                        padding-left: ".$this->obj_mobile_styles['menuBarMobileIconPaddingLeft']."px;
                        padding-right: ".$this->obj_mobile_styles['menuBarMobileIconPaddingRight']."px;
					}
					
					/* main navigation holder */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_devider_small{
						display:none;
					}
					
					";

                if ($this->obj_mobile_styles['menuBarbuttonPosition'] === 'right') {
                    $code.=
                        "
							#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder{
								position:absolute;
								top:0;
								right:0;
							}
						";
                } else {
                    $code.=
                        "
							#hmenu_load_".$this->obj_menu['menuId']." .hmenu_toggle_holder{
								top:0;
								right:0;
							}
						";
                }
                    
                $code .=
                    "					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder{						
						left:0;
						width:100%;						
						margin-top:".$the_margin_top.";
					}
						
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul{
						width:100%;
						display:table;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul > li{
						margin-bottom:0px;
						border-bottom: 1px solid rgb(".$this->hmenu_hex_to_rgb('#000000').", 0.1); 
						border-bottom: 1px solid rgba(".$this->hmenu_hex_to_rgb('#000000').", 0.1); 
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul li{
						width:100%;
						height:auto;
						position:relative;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder ul li a .hmenu_wrap{
						width:100%;
						position:relative;
						height:40px;
						line-height:40px;
						padding:0;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:after{
						text-align: center;
						position: absolute;
						top: 0;
						right: 0px;
						display: table-cell;
						vertical-align: middle;
						padding: 0 10px;
						line-height:40px !important;
						font-size:1.5em;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li .hmenu_sub{
						width:100%;
						position:relative;
						top:auto !important;
						left:auto;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li >a > div > span{
						line-height:40px !important;
						padding-left:10px !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a{
						display:table;
						width:100%;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_submenu{
						opacity: 0;
						filter: Alpha(opacity=1); /* IE8 and earlier */
						display:block;
						visibility: visible;
						height:0;
						overflow:hidden;
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_sub > ul > li > a > .hmenu_wrap:before{						
						padding-right:0;
						margin:0 5px;
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_1,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_2,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_3,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_4,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_5,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_6,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_7,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_8,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_9,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_10,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_11,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_12{ display:table; float:left; position:relative; width:100% !important; }
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder{
						display:none;
						height:auto;
						overflow:hidden;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_li{
						position:relative !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub{
						display:block;
						position:relative;
						height:0;
						overflow:hidden;
						top:auto !important;
						left:auto;
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mobile_menu_toggle{	
						display:table;
						border-left: 1px solid rgba(".$this->hmenu_hex_to_rgb('#000000').", .1); 
						-webkit-background-clip: padding-box; 
						background-clip: padding-box;
						background: rgba(".$this->hmenu_hex_to_rgb('#000000').",0.1);
						display:table;
					}
			";
            
                if ($this->obj_main_styles['mobileLogo'] > 0) {
                    $code .=
                "
						#hmenu_load_".$this->obj_menu['menuId']." .logo_main{
							display:none !important;
						}				
				";
                } else {
                    $code .=
                "
						#hmenu_load_".$this->obj_menu['menuId']." .logo_main{
							display:inline;
						}				
				";
                }
                $code .=
            "
					#hmenu_load_".$this->obj_menu['menuId']." .logo_sticky{
						display:none !important;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .logo_mobile{
						display:inherit !important;
					}
					
				}				
				@media (min-width: ".$mobile_res."px) and (max-width: ".$tablet_minus_res."px) {
					
					".$this->hmenu_get_icon_styles('tablet')."
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a > .hmenu_wrap{
						font-family: ".$this->obj_mobile_styles['fontFamily'].";
						font-weight: ".$this->obj_mobile_styles['fontTabletWeight'].";
						font-size: ".$this->obj_mobile_styles['fontTabletSize'].$this->obj_mobile_styles['fontTabletSizing'].";
					}	
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_inner_holder {
						width: 100%;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_eyebrow .hmenu_eyebrow_inner {
						width: 100%;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder{
						display:table !important;
					}
					/* main nav */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:before{
						display:none !important
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a, .hmenu_social_holder > ul li a, .hmenu_product_holder > ul li a{
						font-size:10px;
					}
					
					/* mega */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub h2{
						font-size:14px;
					}
					
					/* icons */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder > ul li a .hmenu_wrap:before, 
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder > ul li a .hmenu_wrap:before,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_btn:before{
						font-size:12px;
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_slide{
						width:20px;
					}					
				}
				@media (min-width: ".$tablet_res."px) and (max-width: ".$desktop_minus_res."px) {
					
					".$this->hmenu_get_icon_styles('main')."

					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul > li > a > .hmenu_wrap{
						font-family: ".$this->obj_main_styles['fontFamily'].";
						font-weight: ".$this->obj_mobile_styles['fontTabletWeight'].";
						font-size: ".$this->obj_mobile_styles['fontTabletSize'].$this->obj_mobile_styles['fontTabletSizing'].";
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_inner_holder {
						width: 100%;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_eyebrow .hmenu_eyebrow_inner {
						width: 100%;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder{
						display:table !important;
					}
					/* main nav */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:before{
						display:none !important
					}
					
					/* mega */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_sub h2{
						font-size:14px;
					}
					
					/* icons */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_social_holder > ul li a .hmenu_wrap:before, 
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_product_holder > ul li a .hmenu_wrap:before,
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_btn:before{
						font-size:12px;
					}
					
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_slide{
						width:20px;
					}					
				}
				@media (min-width: ".$desktop_res."px) {
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder{
						display:table !important;
					}
					/* main nav */
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_wrap:before{
					}
				}
			";
            
                #CUSTOM CSS
                $code .= "/* custom css */";
                $code .= $this->obj_main_styles['customCss'];
            }
            
            
            #WRITE TO FILE
            fwrite($css_file, $code);
        }
        
        #GET STICKY STYLES
        private function hmenu_get_sticky_styles($menu_id, $sticky)
        {
            $sticky_styles = '';
            
            if ($sticky > 0) {
                $sticky_styles .=
                '
					.hmenu_sticky_'.$menu_id.' .hmenu_main_holder{
						height:'.$this->obj_main_styles['stickyHeight'].'px !important;
					}
					.hmenu_sticky_'.$menu_id.' .hmenu_logo{
						line-height:'.$this->obj_main_styles['stickyHeight'].'px !important;
					}
					.hmenu_sticky_'.$menu_id.' .hmenu_mega_sub,
					.hmenu_sticky_'.$menu_id.' .hmenu_sub{
						top:'.$this->obj_main_styles['stickyHeight'].'px !important;
					}
					.hmenu_sticky_'.$menu_id.' .hmenu_sub > ul > li .hmenu_sub{
						top:0 !important;
					}
					'.$this->hmenu_get_devider_css(
                    $this->obj_main_styles['stickyHeight'].'px',
                    'sticky',
                    'item',
                    $this->obj_main_styles['devider'],
                    $this->obj_main_styles['deviderTransparency'],
                    $this->obj_main_styles['deviderColor'],
                    $this->obj_main_styles['deviderSizing']
                ).'
					
					'.$this->hmenu_get_devider_css(
                    $this->obj_main_styles['stickyHeight'].'px',
                    'sticky',
                    'grp',
                    $this->obj_main_styles['groupDevider'],
                    $this->obj_main_styles['groupTransparency'],
                    $this->obj_main_styles['groupColor'],
                    $this->obj_main_styles['groupSizing']
                ).'	
				
					'.$this->hmenu_background_color(
                    $this->obj_menu['menuId'],
                    '.hmenu_sticky_'.$this->obj_menu['menuId'].' .hmenu_main_holder',
                    0,
                    $this->obj_main_styles['bgStickyStart'],
                    '',
                    '',
                    $this->obj_main_styles['stickyTransparency'],
                    'sticky'
                ).'	
					
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder > ul > li > a,
					.hmenu_sticky_'.$menu_id.'  .hmenu_social_holder > ul > li > a,
					.hmenu_sticky_'.$menu_id.'  .hmenu_product_holder > ul > li > a,
					.hmenu_sticky_'.$menu_id.'  .hmenu_toggle_holder > ul > li > a{
						font-weight:'.$this->obj_main_styles['stickyFontWeight'].' !important;
						color:'.$this->obj_main_styles['stickyFontColor'].' !important;
						font-size:'.$this->obj_main_styles['stickyFontSize'].$this->obj_main_styles['stickyFontSizing'].' !important;
					}
					
					/* font hover color */
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul > li:hover > a > div,
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul > li:hover > a > div:before,
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul > li:hover > a > div:after{
						color:'.$this->obj_main_styles['stickyFontHoverColor'].' !important;
					}

					/* HOVER */

					/* full hover */
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul.hmenu_full_hover > li:hover{
						background-color:'.$this->obj_main_styles['bgStickyHoverColor'].' !important;
					}

					/* full active state */
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul.hmenu_full_hover > li.hmenu_active_nav{
						background-color:'.$this->obj_main_styles['bgStickyHoverColor'].' !important;
					}

					/* border */
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul.hmenu_border_hover > li > a:hover > .hmenu_wrap{
						border:1px solid '.$this->obj_main_styles['bgStickyHoverColor'].' !important;
					}

					/* border active */
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul.hmenu_border_hover > li.hmenu_active_nav > a > .hmenu_wrap{
						border:1px solid '.$this->obj_main_styles['bgStickyHoverColor'].' !important;
					}

					/* underline */
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul.hmenu_underline_hover li a:hover .hmenu_wrap{
						border-bottom:1px solid '.$this->obj_main_styles['bgStickyHoverColor'].' !important;
					}

					/* underline active */
					.hmenu_sticky_'.$menu_id.'  .hmenu_navigation_holder ul.hmenu_underline_hover > li.hmenu_active_nav > a > .hmenu_wrap{
						border-bottom:1px solid '.$this->obj_main_styles['bgStickyHoverColor'].' !important;
					}

					/* HOVER */
					
					/* media queries */
					@media (max-width: 767px) {
						.hmenu_sticky_'.$this->obj_menu['menuId'].' .hmenu_navigation_holder{
							top:'.$this->obj_main_styles['stickyHeight'].'px !important; /* this is the height of the menu */
						}
					}
						
				';
            }
            
            return $sticky_styles;
        }
        
        #CREATE JS FILES
        private function hmenu_create_js($location)
        {
            
            #CREATE FILE
            $js_file = fopen($location . '/hero_script.js', "w");
                        
            #FILE CONTENTS
            $code =
            "
				var hmenu_slide_toggle = true;
				//script
				jQuery(function(){	
					//remove borders
					hmenu_enable_remove_borders();
					//bind search animation
					hmenu_bind_search();
					//enable dropdown script
					if(hmenu_getWidth() > ".($this->global_mobile_res-1)."){
						//enable main menu switch	
						hmenu_enable_dropdown_animation('hover');
					} else { 
						//enable mobile switch	
						hmenu_enable_dropdown_animation('click');
					}
					//scroll
					hmenu_bind_scroll_listener();
					//resize
					hmenu_bind_resize();
				});
				
				/* window resize */
				var hmenu_resize_time_var;
				var hmenu_check_width = jQuery(window).width(), check_height = jQuery(window).height();
				
				// device detection
				if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|ipad|iris|kindle|Android|Silk|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(navigator.userAgent) 
					|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(navigator.userAgent.substr(0,4))){
					
					heroIsMobile = true;					
				} else {					
					heroIsMobile = false;					
				}
				
				if(heroIsMobile){
					if(jQuery(window).width() != hmenu_check_width && jQuery(window).height() != check_height){
						jQuery(window).on('resize', function(){
							//enable dropdown script
							if(hmenu_getWidth() > ".$this->global_mobile_res."){
								//enable main menu switch	
								hmenu_enable_dropdown_animation('hover');
							} else { 
								//enable mobile switch	
								hmenu_enable_dropdown_animation('click');
							}
							//resize lightbox holder
							hmenu_resize();
							hmenu_get_offset();
							clearTimeout(hmenu_resize_time_var);
							hmenu_resize_time_var = setTimeout(function(){
								hmenu_get_offset();
							},500);
						});
					};
				} else {
					jQuery(window).on('resize', function(){
						//enable dropdown script
						if(hmenu_getWidth() > ".$this->global_mobile_res."){
							//enable main menu switch	
							hmenu_enable_dropdown_animation('hover');
						} else { 
							//enable mobile switch	
							hmenu_enable_dropdown_animation('click');
						}
						//resize lightbox holder
						hmenu_resize();
						hmenu_get_offset();
						clearTimeout(hmenu_resize_time_var);
						hmenu_resize_time_var = setTimeout(function(){
							hmenu_get_offset();
						},500);
					});
				}
				
				//remove border
				function hmenu_enable_remove_borders(){
					
					//check the list items and remove first or last occurance of borders	
					jQuery('.hmenu_sub ul').each(function(index, element) {
						jQuery(this).children('li').last().addClass('hmenu_no_bottom_border');	
					});
					
					//nav item last border removed
					jQuery('.hmenu_navigation_holder > ul').each(function(index, element) {
						jQuery(this).children('li').last().children('.hmenu_item_devider').css({
							opacity:0
						});	
					});
					
					//section deviders
					jQuery('.hmenu_inner_holder > div').each(function(index, element) {
						jQuery(this).children('.hmenu_grp_devider').last().remove();	
					});
					
				}
								
				//bind search animations
				function hmenu_bind_search(){
					
					jQuery('.hmenu_trigger_search').off().on('click', function(){
						jQuery(this).parent('form').children('.hmenu_search_submit').trigger('click');
					});
					
					hmenu_bind_search_animation();
					
				}
				
				function hmenu_resize(){
					//lightbox
					jQuery('.hmenu_search_lightbox_input').css({
						height:jQuery(window).height()+'px'
					});
				}
				
				//search animation
				function hmenu_bind_search_animation(){
					
					hmenu_resize();
					
					jQuery('.hmenu_search_slide .hmenu_trigger_lightbox').off().on('click', function(){
						
						var hmenu_the_link = jQuery(this).attr('data-link');
						var hmenu_the_id = jQuery(this).attr('data-id');
						
						//set css
						jQuery('#'+hmenu_the_link).css({
							display:'table'
						});	
						jQuery('#'+hmenu_the_link).animate({
							opacity: 1
						}, 500, function(){
							jQuery('.hmenu_search_'+hmenu_the_id).focus();
							//close
							jQuery('#'+hmenu_the_link+' .hmenu_search_lightbox_close').off().on('click', function(){
								jQuery('#'+hmenu_the_link).animate({
									opacity: 0
								}, 500, function(){
									jQuery('#'+hmenu_the_link).css({
										display:'none'
									});	
								});
							});
						});					
						
					});
					
					//slide full
					jQuery('.hmenu_search_full .hmenu_trigger_full').off().on('click', function(){
						
						var hmenu_the_link = jQuery(this).attr('data-link');
						var hmenu_the_height = jQuery(this).attr('data-height');
						var hmenu_the_id = jQuery(this).attr('data-id');
						var hmenu_this_element = jQuery(this);
						
						if(!jQuery(hmenu_this_element).attr('data-search-toggle') || jQuery(hmenu_this_element).attr('data-search-toggle') == 'close'){	
							jQuery(hmenu_this_element).attr('data-search-toggle', 'open');			
							//open	
							jQuery('#'+hmenu_the_link).stop().animate({
								opacity: 1,
								height: hmenu_the_height+'px'
							}, 200);			
						} 
						
						jQuery('.hmenu_search_'+hmenu_the_id).focus();
						
						jQuery('.hmenu_search_'+hmenu_the_id).focusout(function() {
							jQuery(hmenu_this_element).attr('data-search-toggle', 'close');
							//close
							jQuery('#'+hmenu_the_link).stop().animate({
								opacity: 0,
								height: 0
							}, 200);														
						})
						
					});
					
				}
				
				//dropdown animation
				function hmenu_enable_dropdown_animation(hmenu_event){
					
					if(hmenu_event == 'hover'){	
						//reset
						jQuery('.hmenu_submenu').css({
							'opacity': 0,
							'visibility': 'hidden',
							'display':'none',
							'height': 'auto'
						});
						jQuery('.hmenu_navigation_holder ul').each(function(index, element) {        
							
							jQuery(this).children('li').each(function(index, element) {            
								
								jQuery(this).off().on(
									{
										mouseenter: function(){
											
											if(jQuery(this).find('> .hmenu_submenu').length > 0){
												var hmenu_sub_menu = jQuery(this).find('> .hmenu_submenu');
												//animate menu
												jQuery(this).addClass('hmenu_main_active');
												jQuery(hmenu_sub_menu).css({ 
													'display': 'table-cell',
													'visibility':'visible'
												});

												if(jQuery(hmenu_sub_menu).hasClass('hmenu_sub')){
                                                    var hmenu_normal_sub = jQuery(this).find('> .hmenu_sub');
                                                    var hmenu_off_set = hmenu_normal_sub.offset();
                                                    var hmenu_sub_menu_width = jQuery(this).find('> .hmenu_submenu').width();
                                                    var hmenu_the_check_offset = (hmenu_off_set.left + hmenu_sub_menu_width);
                                                    if(hmenu_normal_sub.attr('data-menu-level') < 1){
                                                        if(hmenu_the_check_offset > jQuery(window).width()){
                                                            jQuery(hmenu_normal_sub).css({
                                                                'left':'auto',
                                                                'right':0
                                                            });
                                                            jQuery(hmenu_normal_sub).addClass('hmenu_has_changed');
                                                        }
                                                    }else{
                                                        if(hmenu_the_check_offset > jQuery(window).width()){
                                                            jQuery(hmenu_normal_sub).css({
                                                                'left':-(hmenu_sub_menu_width)
                                                            });
                                                            jQuery(hmenu_normal_sub).addClass('hmenu_has_changed');
                                                        } else {
                                                            if(jQuery(this).parents().hasClass('hmenu_has_changed')){
                                                                jQuery(hmenu_normal_sub).css({
                                                                    'left':-(hmenu_sub_menu_width)
                                                                });
                                                            }
                                                        }
                                                    }
                                                }

												".$this->hmenu_get_unique_data('_animation_type')."
											};
											if(jQuery(hmenu_sub_menu).hasClass('hmenu_mega_sub')){
												var hmenu_the_height = jQuery(hmenu_sub_menu).height();
												var hmenu_the_pad_top = jQuery(hmenu_sub_menu).children('.hmenu_mega_inner').css('padding-top');
													var hmenu_replace_top = hmenu_the_pad_top.replace('px', '');
												var hmenu_the_pad_bot = jQuery(hmenu_sub_menu).children('.hmenu_mega_inner').css('padding-bottom');
													var hmenu_replace_bot = hmenu_the_pad_bot.replace('px', '');
												var hmenu_final_height = hmenu_the_height - (parseInt(hmenu_replace_top)+parseInt(hmenu_replace_bot));
												jQuery(hmenu_sub_menu).children('.hmenu_mega_inner').children('div').last().children('.hmenu_col_devider').hide();
												jQuery(hmenu_sub_menu).children('.hmenu_mega_inner').children('div').each(function(index, element) {
													jQuery(this).children('.hmenu_col_devider').css({
														'height':hmenu_final_height+'px'
													});
												});
											}
										},
										mouseleave: function(){
											if(jQuery(this).find('> .hmenu_submenu').length > 0){
												var hmenu_sub_menu = jQuery(this).find('> .hmenu_submenu');
												//animate menu
												jQuery(this).removeClass('hmenu_main_active');
												jQuery(hmenu_sub_menu).stop().animate({
													opacity: 0
												}, 100, function(){
													jQuery(this).css({
														'visibility': 'hidden',
														'display':'none'
													});
												});
											};
										}
									}
								);	
								
							});		
						});	
					} else if(hmenu_event == 'click') {
						
						//reset
						jQuery('.hmenu_submenu').css({
							'opacity': 0,
							'display': 'block',
							'visibility': 'visible',
							'height': 0
						});
						
						jQuery('.hmenu_navigation_holder ul').each(function(index, element) {     
							jQuery(this).children('li').each(function(index, element) {  
								jQuery(this).off();
							});
						});
						
						jQuery('.hmenu_navigation_holder').each(function(){
							
							var hmenu_the_parent = jQuery(this).parents('.hmenu_inner_holder');
							
							jQuery(hmenu_the_parent).children('.hmenu_right, .hmenu_left').children('.hmenu_toggle_holder').off().on('click', function(){		
							
								if(!jQuery(this).attr('data-toggle') || jQuery(this).attr('data-toggle') == 'close'){	
									jQuery(this).attr('data-toggle', 'open');			
									//open	
									jQuery(hmenu_the_parent).children('div').children('.hmenu_navigation_holder').hide().slideDown( 'slow', function() {
										
									});					
								} else if(jQuery(this).attr('data-toggle') == 'open'){
									jQuery(this).attr('data-toggle', 'close');
									//close
									jQuery(hmenu_the_parent).children('div').children('.hmenu_navigation_holder').css({ 'display':'block'});
									jQuery(hmenu_the_parent).children('div').children('.hmenu_navigation_holder').slideUp( 'slow', function() {
										jQuery(this).css({ 'display':'none'});
									});					
								}
								
							});
							
						});
						
						jQuery('.hmenu_mobile_menu_toggle').remove();
						
						//add toggle div to menu
						jQuery('.icon_hero_default_thin_e600').each(function(index, element) {
							jQuery(this).parent('a').parent('li').append('<div class=\"hmenu_mobile_menu_toggle\" data-toggle=\"close\"></div>');
						});
						jQuery('.icon_hero_default_thin_e602').each(function(index, element) {
							jQuery(this).parent('a').parent('li').append('<div class=\"hmenu_mobile_menu_toggle\" data-toggle=\"close\"></div>');
						});
						
						if(jQuery('.hmenu_mobile_menu_toggle').length > 0){
							jQuery('.hmenu_mobile_menu_toggle').off().on('click', function(event){
								
								if(jQuery(this).parent('li').parent('ul').hasClass('hmenu_full_hover') && jQuery(this).attr('data-toggle') != 'open'){
									//close any open menu items
									jQuery('.hmenu_navigation_holder ul > li').each(function(index, element) {
									   if(jQuery(this).children('.hmenu_mobile_menu_toggle').attr('data-toggle') == 'open'){
											jQuery(this).children('.hmenu_mobile_menu_toggle').attr('data-toggle', 'close');
											//close
											jQuery(this).children('.hmenu_mobile_menu_toggle').prev().css({ 'display':'block'});				
											jQuery(this).children('.hmenu_mobile_menu_toggle').prev().animate({
												opacity: 0,
												height: 0
											}, 200);
										}	
									});	
								} else if(jQuery(this).parent('li').parent('ul').parent('div').hasClass('hmenu_sub') ){								
									//close any sub open menu items
									jQuery('.hmenu_sub .hmenu_navigation_root > li').each(function(index, element) {
										if(jQuery(this).children('.hmenu_mobile_menu_toggle').attr('data-toggle') == 'open'){
											jQuery(this).children('.hmenu_mobile_menu_toggle').attr('data-toggle', 'close');
											//close
											jQuery(this).children('.hmenu_mobile_menu_toggle').prev().css({ 'display':'block'});				
											jQuery(this).children('.hmenu_mobile_menu_toggle').prev().animate({
												opacity: 0,
												height: 0
											}, 200);
										}	
									});	
								}
								
								if(!jQuery(this).attr('data-toggle') || jQuery(this).attr('data-toggle') == 'close'){
										
									jQuery(this).attr('data-toggle', 'open');			
									
									//open	
									if(jQuery(this).prev().hasClass('hmenu_mega_sub')){
										var hmenu_the_height = jQuery(this).prev().children('.hmenu_mega_inner').height();
									} else {
										var hmenu_the_height = jQuery(this).prev().children('ul').height();
									}
									
									jQuery(this).prev().animate({
										opacity: 1,
										height: hmenu_the_height
									}, 200, function(){
										jQuery(this).css({ 'display':'table', 'height':'auto'});
									});	
											
								} else if(jQuery(this).attr('data-toggle') == 'open'){
									
									jQuery(this).attr('data-toggle', 'close');
									
									//close
									jQuery(this).prev().css({ 'display':'block'});
									
									jQuery(this).prev().animate({
										opacity: 0,
										height: 0
									}, 200);	
												
								}
								
							});
							
						}	
											
					}
					
				}
			";
            
            $code .= "	
				//bind home scroll listener
				function hmenu_bind_resize(){
					var hmenu_mobile_res = ".$this->global_mobile_res.";
					var hmenu_current_width = jQuery( window ).width();
					jQuery( window ).on('resize', function() {
						hmenu_current_width = jQuery( window ).width();
						if(hmenu_current_width < hmenu_mobile_res){
							hmenu_remove_class('remove');
						} else {
							hmenu_remove_class('reset');
							//hmenu_bind_scroll_listener();
						}
					});
					if(hmenu_current_width < hmenu_mobile_res){
						hmenu_remove_class('remove');
					} else {
						hmenu_remove_class('reset');
					}
				}
			";
            
            $code .= "	
				//bind remove and add classes
				function hmenu_remove_class(todo){
					if(todo == 'remove'){
						jQuery('.hmenu_submenu').find('.icon_hero_default_thin_e602').addClass('icon_hero_default_thin_e600').removeClass('icon_hero_default_thin_e602');
					} else{
						jQuery('.hmenu_submenu').find('.icon_hero_default_thin_e600').addClass('icon_hero_default_thin_e602').removeClass('icon_hero_default_thin_e600');
					}					
				}
			";
            
            #GET STICKY
            $sticky = $this->obj_main_styles['sticky'];
            $sticky_logo_active = $this->obj_main_styles['stickyLogoActive'];
            $sticky_url = $this->obj_main_styles['stickyUrl'];
            $sticky_activate = $this->obj_main_styles['stickyActivate'];
            $sticky_height = $this->obj_main_styles['stickyHeight'];
            
            $code .= "	
				//bind home scroll listener
				function hmenu_bind_scroll_listener(){
						
					//variables
					var hmenu_sticky_menu = jQuery('.hmenu_load_menu').find('[data-sticky=\"yes\"]');						
					var hmenu_sticky_height = parseInt(hmenu_sticky_menu.attr('data-height'));						
					var hmenu_sticky_activate = parseInt(hmenu_sticky_menu.attr('data-activate'));						
					var hmenu_body_top = jQuery(document).scrollTop();						
					var hmenu_menu_id = jQuery(hmenu_sticky_menu).parent('.hmenu_load_menu').attr('data-menu-id');
					
					//show menu
					jQuery('.hmenu_load_menu').removeAttr('style');	
					
					//check current state
					if(hmenu_body_top >= hmenu_sticky_activate){
						hmenu_bind_sticky(hmenu_sticky_menu, hmenu_sticky_height, hmenu_sticky_activate, hmenu_body_top, hmenu_menu_id);
					} else {
						hmenu_bind_sticky(hmenu_sticky_menu, hmenu_sticky_height, hmenu_sticky_activate, hmenu_body_top, hmenu_menu_id);
					}
					
					//scroll trigger			
					jQuery(window).on('scroll', function(){
						hmenu_body_top = jQuery(document).scrollTop();		
						hmenu_bind_sticky(hmenu_sticky_menu, hmenu_sticky_height, hmenu_sticky_activate, hmenu_body_top, hmenu_menu_id);
						//hmenu_get_offset();
					});
						
				}
				
				//bind sticky
				function hmenu_bind_sticky(hmenu_sticky_menu, hmenu_sticky_height, hmenu_sticky_activate, hmenu_body_top, hmenu_menu_id){
					
					//get window width
					var hmenu_window_width = jQuery(window).width();
					
					if(hmenu_window_width > ".$this->obj_main_styles['siteResponsiveOne']."){
						//activate switch
						if(hmenu_body_top >= hmenu_sticky_activate){
							
				";
                    
            if ($sticky > 0 && $sticky_url != '') {
                //hide other logos
                $code .= "jQuery('.logo_main').css({display:'none'});";
                $code .= "jQuery('.logo_mobile').css({display:'none'});";
                //show sticky logo
                $code .= "jQuery('.logo_sticky').css({display:'inline'});";
            }
                
            $code .= "						
							//add class
							jQuery(hmenu_sticky_menu).parent('.hmenu_load_menu').addClass('hmenu_is_sticky ' + 'hmenu_sticky_' + hmenu_menu_id);
							if(hmenu_slide_toggle){
								jQuery(hmenu_sticky_menu).parent('.hmenu_load_menu').css({
									'position': 'fixed',
									'top':'-'+hmenu_sticky_height+'px'
								});
								jQuery(hmenu_sticky_menu).parent('.hmenu_load_menu').animate({
									'top':'0px'
								}, 200);
								hmenu_slide_toggle = false;
							}
						} else {
							hmenu_slide_toggle = true;	
				";
                    
            if ($sticky > 0 && $sticky_url != '') {
                //show logo
                $code .= "jQuery('.logo_main').removeAttr('style');";
                //hide sticky logo
                $code .= "jQuery('.logo_sticky').css({display:'none'});";
            }
                
            $code .= "
							//remove class
							jQuery(hmenu_sticky_menu).parent('.hmenu_load_menu').removeClass('hmenu_is_sticky ' + 'hmenu_sticky_' + hmenu_menu_id);	
							jQuery(hmenu_sticky_menu).parent('.hmenu_load_menu').removeAttr('style');							
						}
					}					
				}
				
			";
            
            #WRITE TO FILE
            fwrite($js_file, $code);
        }
        
        #GET NAV ICON STYLES
        private function hmenu_get_icon_styles($for)
        {
            $icon_styles = '';
            
            $padding_left = $this->hmenu_get_unique_data('_padding_left');
            $padding_right = $this->obj_main_styles['paddingRight'];
            
            if ($for == 'tablet') {
                $padding_left = $this->obj_mobile_styles['paddingLeft'].'px';
                $padding_right = $this->obj_mobile_styles['paddingRight'];
            }
            
            foreach ($this->obj_nav_items as $item) {
                if ($item['icon'] > 0) {
                    $icon_styles .=
                    "
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_nav_uni_".$item['navItemId'].":before{
							color:".$item['iconColor']." !important;
							font-size:".$this->hmenu_get_icon_size($item['iconSize'])." !important;
						}
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_nav_uni_".$item['navItemId']." span{
							padding-left:".$padding_left.";
						}
					";
                } else {
                    $icon_styles .=
                    "
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_nav_uni_".$item['navItemId']." span{
							padding-left:".$padding_left.";
						}
					";
                }
                if ($this->obj_main_styles['arrows'] > 0) {
                    $icon_styles .=
                    "
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_nav_uni_".$item['navItemId']." span{
							padding-right:10px;
						}
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_no_sub span{
							padding-right:".$padding_right."px !important;
						}
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_nav_uni_".$item['navItemId'].":after{
							padding-right:".$padding_right."px;
						}
					";
                } else {
                    $icon_styles .=
                    "
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_navigation_holder > ul li a .hmenu_nav_uni_".$item['navItemId']." span{
							padding-right:".$padding_right."px !important;
						}
					";
                }
            }
            
            return $icon_styles;
        }
        
        #GET LIST ICON COLORS STYLES $item['mega_menus'][0]
        private function hmenu_get_list_icon_styles()
        {
            $styles = '';
                        
            foreach ($this->obj_nav_items as $item) {
                if ($item['mega_menus']) {
                    foreach ($item['mega_menus'] as $mega_item) {
                        if ($mega_item['mega_stuff']) {
                            foreach ($mega_item['mega_stuff'] as $stuff) {
                                if ($stuff['type'] == 'list') {
                                    if ($stuff['mega_list_items']) {
                                        foreach ($stuff['mega_list_items'] as $list_item) {
                                            $styles .=
                                                '
													#hmenu_load_'.$this->obj_menu['menuId'].' #hmenu_list_item_uni_'.$list_item['listItemId'].':before{
														font-size:'.$this->hmenu_get_icon_size($list_item['iconSize']).' !important;
													}
												';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            return $styles;
        }
        
        #GET SEARCH STYLES
        private function hmenu_get_search_css($status, $type)
        {
            $css = '';
            #GENERAL STYLES
            $css .= "
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_container{
					float:left;
					height:inherit;
					display:table;
				}				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_container .hmenu_search_inner{
					display:table-cell;
					vertical-align:middle;
					text-decoration:none;
					color:#FFFFFF;
					padding:0;
					font-size:14px;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_container .hmenu_search_inner .hmenu_search_wrap{
					padding:7px 8px;
					display:table;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_display{
					position:relative;
					width:100%;
					display:table;
				}
			";
            
            #SLIDE & CLASSIC STYLES
            if ($type == 'classic') {
                $css .= " 
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type." .hmenu_search_input{
						float:left;
						display:table;
						padding:0 0 0 0;
						width:100%;
					}			
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type." .hmenu_search_input input{
						padding:0 30px 0 5px;
						outline:none;
						background-color:".$this->obj_search_styles['backgroundColor'].";	
						width:".$this->obj_search_styles['width']."px;					
						height:".$this->obj_search_styles['height']."px;
						color:".$this->obj_search_styles['fontColor'].";
						font-family:'".$this->obj_search_styles['fontFamily']."';
						font-size:".$this->obj_search_styles['fontSize'].$this->obj_search_styles['fontSizing'].";
					}
				";
            }
            
            $input = '.hmenu_search_'.$type.' .hmenu_search_input input';
            $css .= $this->hmenu_get_search_border($this->obj_search_styles['border'], $input);
                        
            #GENERAL STYLES
            $css .= "				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type." .hmenu_search_btn {
					position:absolute;
					right:0;
					display:table;
					padding:0;
					cursor:pointer;
				}	
			";
            
            $css .= "	
				/* starting opacity */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_submenu{
					opacity: 0;
					visibility: hidden;
				}				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_btn:before{
					font-size:".$this->hmenu_get_icon_size($this->obj_search_styles['iconSize']).";
					color:".$this->obj_search_styles['fontColor'].";
					line-height:".($this->obj_search_styles['height'] + 5)."px; /* takes the height of the search input height */
				}	
			";
            
            #FULL STYLES
            if ($type == 'full' || $type == 'slide') {
                $css .= "			
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type."{ /* full specific styles */
						position:inherit;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type." .hmenu_search_input{
						position:absolute;
						width:100%;
						background-color:#FFFFFF;
						left:0;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type." .hmenu_search_btn{
						position:relative;
						padding:0;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type."_input{
						display:block; 
						background-color:".$this->obj_search_styles['backgroundColor'].";
						padding:0px 10px;
						opacity: 0;
						filter: Alpha(opacity=00); /* IE8 and earlier */
						overflow:hidden;
						height:0;
						position:relative;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type."_input .hmenu_search_btn{
						width:".$this->obj_search_styles['height']."px;
						position:absolute;
						cursor:pointer;
						z-index:2;
						right:0;
					}
					#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_".$type."_input input[type='text']{
						width:100%;
						display:block;
						padding:0 0;
						border:none;
						font-size:".$this->obj_search_styles['fontSize'].$this->obj_search_styles['fontSizing'].";
						background-color:transparent;
						outline:none;
						position:absolute;
						height:".$this->obj_search_styles['height']."px;
						color:".$this->obj_search_styles['fontColor'].";
						font-family:'".$this->obj_search_styles['fontFamily']."';
						z-index:1;
					}
				";
            }
            
            $css .= "			
				/* lightbox search */
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_lightbox_input{
					width:100%; 
					position:fixed;
					top:0;
					left:0;
					opacity: 0;
					filter: Alpha(opacity=0); /* IE8 and earlier */;
					display:none;
					z-index: 99;
				}			
				".$this->hmenu_background_color(
                $this->obj_menu['menuId'],
                '#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_search_lightbox_input',
                0,
                $this->obj_search_styles['backgroundColor'],
                $this->obj_search_styles['backgroundColor'],
                '',
                0.9,
                ''
            )."
			";
            #BORDER COLOR FOR THE LIGHT BOX SEARCH
            if ($this->obj_search_styles['border'] > 0) {
                $border_color = $this->obj_search_styles['borderColor'];
            } else {
                $border_color = '#CCCCCC';
            }
            $css .= "
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_lightbox_form_holder{
					width:50%; 
					height:".$this->obj_search_styles['height']."px;
					left:50%;
					margin-left:-25%;
					top:45%;
					margin-top:".(($this->obj_search_styles['height'] / 2) - ($this->obj_search_styles['height']))."px;
					position:absolute;
					border-bottom:1px solid ".$border_color.";
					padding:0 0 0 0;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_lightbox_form_holder input[type='text']{
					width:94%;
					height:".$this->obj_search_styles['height']."px;
					border:none;
					color:".$this->obj_search_styles['fontColor'].";
					font-family:'".$this->obj_search_styles['fontFamily']."';
					font-size:".$this->obj_search_styles['fontSize'].$this->obj_search_styles['fontSizing'].";
					background-color:transparent;
					outline:none;
					padding:0 6% 0 0;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_lightbox_form_holder .hmenu_search_btn{
					position:absolute;
					right:0;
					top:0;
					cursor:pointer;
				}
				
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_lightbox_input .hmenu_search_lightbox_close{
					position:absolute; 
					right:0;
					top:0;
					padding:25px;
					cursor:pointer;
				}
				#hmenu_load_".$this->obj_menu['menuId']." .hmenu_search_lightbox_input .hmenu_search_lightbox_close:before{
					font-size:".$this->obj_search_styles['fontSize']."px;
					color:".$this->obj_search_styles['fontColor'].";
					line-height:".($this->obj_search_styles['fontSize']+5)."px;
				}
			";
            
            return $css;
        }
        
        #GET DEVIDER CSS
        private function hmenu_get_devider_css(
            $menu_nav_height,
            $for,
            $type,
            $menu_devider,
            $menu_transparency,
            $menu_color,
            $menu_sizing
        ) {
            $code = "";
            
            $is_important = '';
            
            #FOR IDENTIFIER
            if ($for == 'normal') {
                $for_identifier = '#hmenu_load_'.$this->obj_menu["menuId"].'';
            } else {
                $for_identifier = '.hmenu_sticky_'.$this->obj_menu["menuId"].'';
                $is_important = '!important';
            }
            
            if ($menu_devider > 0) {
                if ($menu_sizing == 'full') {
                    $code .= "
						/* menu deviders */
						/* ". $menu_nav_height ." */
						".$for_identifier." .hmenu_".$type."_devider{
							float:left;
							height:inherit; /* height of nav */
							width:0px;
							border-left: 1px solid rgb(".$this->hmenu_hex_to_rgb($menu_color).", ".$menu_transparency."); border-left: 1px solid rgba(".$this->hmenu_hex_to_rgb($menu_color).", ".$menu_transparency."); -webkit-background-clip: padding-box; background-clip: padding-box;
						}				
					";
                } else {
                    $devider_height = ($menu_nav_height-30)/2;
                    $code .= "
						/* ". $menu_nav_height ." */
						".$for_identifier." .hmenu_".$type."_devider{
							float:left;
							height:30px; /* height of nav */
							width:0px;
							border-left: 1px solid rgb(".$this->hmenu_hex_to_rgb($menu_color).", ".$menu_transparency."); border-left: 1px solid rgba(".$this->hmenu_hex_to_rgb($menu_color).", ".$menu_transparency."); -webkit-background-clip: padding-box; background-clip: padding-box;
							margin-top:".$devider_height."px ". $is_important .";
						}				
					";
                }
            }
            
            return $code;
        }
        
        #GET SOCIAL CSS
        private function hmenu_get_social_css($social)
        {
            $code = "";
            
            if ($social > 0) {
                if (!empty($this->obj_social_items)) {
                    foreach ($this->obj_social_items as $item) {
                        $icon_size = $this->hmenu_get_icon_size($item['iconSize']);
                        $code .=
                        '
							
							/* main */
							#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_social_holder > ul li#hmenu_social_item_'.$item['socialId'].' a .hmenu_wrap:before{
								color:'.$item['iconColor'].';
								font-size:'.$icon_size.';
								width:'.$icon_size.';
							}
							#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_social_holder > ul li#hmenu_social_item_'.$item['socialId'].':hover a .hmenu_wrap:before{
								color:'.$item['iconHoverColor'].';
							}							
							
							#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_mobile_social_holder:hover a .hmenu_wrap:before{
								color:'.$item['iconColor'].';
							}
							#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_mobile_social_holder #hmenu_social_mobile_item_'.$item['socialId'].' .hmenu_wrap:before{
								color:'.$item['iconColor'].';
								font-size:'.$icon_size.' !important;
							}
							#hmenu_load_'.$this->obj_menu['menuId'].' .hmenu_mobile_social_holder #hmenu_social_mobile_item_'.$item['socialId'].':hover .hmenu_wrap:before{
								color:'.$item['iconHoverColor'].';
							}
							
						';
                    }
                }
            }
            
            return $code;
        }
        
        private function hmenu_get_icon_size($size)
        {
            $font_size = "20px";
            
            switch ($size) {
                case 'xsmall':
                    $font_size = '10px';
                break;
                case 'small':
                    $font_size = '15px';
                break;
                case 'medium':
                    $font_size = '20px';
                break;
                case 'large':
                    $font_size = '30px';
                break;
            }
            
            return $font_size;
        }
        
        #CREATE PHP MENU HTML
        private function hmenu_create_html()
        {

            #USER DETAILS
            $current_user = wp_get_current_user();
                            
            #STICKY OPTIONS
            $sticky_options = '';
            if ($this->obj_main_styles['sticky'] > 0) {
                $sticky_options .= 'data-sticky="yes" data-activate="'.$this->obj_main_styles['stickyActivate'].'" data-height="'.$this->obj_main_styles['stickyHeight'].'"';
            }
                                
            #FILE CONTENTS
            $code =
            '			
				<!-- BEGIN: MAIN -->
					<div id="hmenu_holder_'.$this->obj_menu['menuId'].'" class="hmenu_main_holder hmenu_main_bg_color" '.$sticky_options.'>
						';
            if ($this->obj_main_styles['eyebrow'] > 0) {
                $code .= '
						<div class="hmenu_eyebrow">
							<div class="hmenu_eyebrow_inner">
							';
                if ($this->obj_main_styles['eyebrow']) {
                    $code .= '
								<div class="hmenu_eyebrow_left">'.$this->obj_main_styles['eyeExcerpt'].'</div>
							';
                }
                if (0 == $current_user->ID) {
                    if ($this->obj_main_styles['eyeLoginUrl']) {
                        $code .= '
										<div class="hmenu_eyebrow_right"><a href="' . $this->obj_main_styles['eyeLoginUrl'] . '">' . esc_html__('Login', 'hmenu') . '</a></div>
									';
                    }
                } else {
                    if ($this->obj_main_styles['eyeLoginUrl']) {
                        $code .= '
										<div class="hmenu_eyebrow_right"><a href="' . wp_logout_url(home_url()) . '">' . esc_html__('Logout', 'hmenu') . '</a></div>
									';
                    }
                }
                $code .= '
							</div>							
						</div>
					';
            }
            $code .= '
					<!-- BEGIN: INNER -->
						<div class="hmenu_inner_holder">
							';
            if ($this->obj_main_styles['siteResponsive'] > 0 || $this->obj_main_styles['responsiveLabel'] != '') {
                $code .= '<div class="menu_responsive_label">'.$this->obj_main_styles['responsiveLabel'].'</div>';
            }
            $code .= '
							<!-- BEGIN: LEFT -->
            					<div class="hmenu_left">
									'.$this->hmenu_get_col_content('left').'
								</div>
							<!-- END: LEFT -->
							
							<!-- BEGIN: CENTER -->
								<div class="hmenu_center">
									'.$this->hmenu_get_col_content('center').'
								</div>
							<!-- END: CENTER -->
							
							<!-- BEGIN: RIGHT -->
								<div class="hmenu_right">
									'.$this->hmenu_get_col_content('right').'
								</div>
							<!-- END: RIGHT -->
							
						</div>
					<!-- END: INNER -->
					
					<!-- BEGIN: SEARCH FULL INPUT -->
			';
            if ($this->obj_search_styles['type'] == 'full') {
                $code .= $this->hmenu_get_unique_data('_search_full_input');
            } elseif ($this->obj_search_styles['type'] == 'slide') {
                $code .= $this->hmenu_get_unique_data('_search_lightbox_input');
            }
            $code .=
            '          
       				<!-- END: SEARCH FULL INPUT -->
					
					</div>
				<!-- END: MAIN -->				
			';
            
            #RETURN HTML
            return $code;
        }
        
        #GET COL CONTENT
        private function hmenu_get_col_content($position)
        {
            $code = '';
            switch ($position) {
                case 'left':
                    if ($this->obj_mobile_styles['menuBarbuttonPosition'] === 'left') {
                        $code .=
                        '
							<!-- BEGIN: TOGGLE -->
								<div class="hmenu_toggle_holder hmenu_mobile_show" data-toggle="close">
									<div class="hmenu_grp_devider"></div>
									<ul class="hmenu_hover_color">
										<li><a><div class="hmenu_wrap icon_font_mobile_icon_'.$this->obj_mobile_styles['menuBarMobileIcon'].'"></div></a></li>
									</ul>
								</div>
							<!-- END: TOGGLE -->
						';
                    }
                    if ($this->obj_menu['leftItems'] != '') {
                        $left_arr = explode(',', $this->obj_menu['leftItems']);
                        foreach ($left_arr as $item) {
                            $code .= $this->hmenu_return_nav_content($item);
                        }
                    }
                break;
                case 'center':
                    if ($this->obj_menu['centerItems'] != '') {
                        $center_arr = explode(',', $this->obj_menu['centerItems']);
                        foreach ($center_arr as $item) {
                            $code .= $this->hmenu_return_nav_content($item);
                        }
                    }
                break;
                case 'right':
                    if ($this->obj_menu['rightItems'] != '') {
                        $right_arr = explode(',', $this->obj_menu['rightItems']);
                        foreach ($right_arr as $item) {
                            $code .= $this->hmenu_return_nav_content($item);
                        }
                    }
                    if ($this->obj_mobile_styles['menuBarbuttonPosition'] === 'right') {
                        $code .=
                        '
							<!-- BEGIN: TOGGLE -->
								<div class="hmenu_toggle_holder hmenu_mobile_show" data-toggle="close">
									<div class="hmenu_grp_devider"></div>
									<ul class="hmenu_hover_color">
										<li><a><div class="hmenu_wrap icon_font_mobile_icon_'.$this->obj_mobile_styles['menuBarMobileIcon'].'"></div></a></li>
									</ul>
								</div>
							<!-- END: TOGGLE -->
						';
                    }
                    
                break;
            }
            return $code;
        }
        
        #RETURN THE CORRECT CONTENT FOR THE CORRECT COLUMN
        private function hmenu_return_nav_content($item)
        {
            
            #GLOBALS
            global $wpdb, $post;
            
            #SETUP POST DATA
            setup_postdata($post);
            
            #SWITCH CASE TO CHECK WHAT TO RETURN
            switch ($item) {
                case 'logo': #LOGO
                    
                    $html = '';
                    

                        
                        #GET LOGO LINK
                        $the_logo_link = get_site_url();
                        $the_logo_alt = $this->obj_main_styles['logoAlt'];
                        
                        if ($this->obj_main_styles['logoLink'] != '' || !empty($this->obj_main_styles['logoLink'])) {
                            $the_logo_link = $this->obj_main_styles['logoLink'];
                        }
                        
                        $html .=
                        '
							<!-- BEGIN: LOGO -->
								<div class="hmenu_logo">
									<a href="'.$the_logo_link.'" target="'.$this->obj_main_styles['logoLinkTarget'].'">
						';

                        if ($this->obj_main_styles['logo'] > 0 && $this->obj_main_styles['logoUrl'] != '') {
                            $html .=
                                '
										<img class="logo_main" alt="' . $the_logo_alt . '" src="' . $this->obj_main_styles['logoUrl'] . '">
							';
                        }

                        if ($this->obj_main_styles['sticky'] > 0 && $this->obj_main_styles['stickyUrl'] != '' && $this->obj_main_styles['stickyLogoActive'] > 0) {
                            $html .=
                            '
										<img class="logo_sticky" alt="'.$the_logo_alt.'" src="'.$this->obj_main_styles['stickyUrl'].'">
							';
                        }

                        if ($this->obj_main_styles['mobileLogo'] > 0 && $this->obj_main_styles['mobileLogoUrl'] != '') {
                            $html .=
                            '
										<img class="logo_mobile" alt="'.$the_logo_alt.'" src="'.$this->obj_main_styles['mobileLogoUrl'].'">
							';
                        }
                        
                        $html .=
                        '			</a>
								</div>
								<div class="hmenu_grp_devider"></div>
							<!-- END: LOGO -->
						';

                    
                    return $html;
                                    
                break;
                case 'main': #MAIN NAVIGATION
                    
                    $html = '';
                    
                    if ($this->obj_main_styles['menu'] > 0) {
                        #FORMAT ARRAY
                        $nodes = array();
                        $tree = array();
                        
                        foreach ($this->obj_nav_items as &$node) {
                            $node['sub'] = array();
                            $id = $node['navItemId'];
                            $parent_id = $node['parentNavId'];
                            $nodes[$id] =& $node;
                            if (array_key_exists($parent_id, $nodes)) {
                                $nodes[$parent_id]['sub'][] =& $node;
                            } else {
                                $tree[] =& $node;
                            }
                        }
                                
                        $html .=
                        '
							<!-- BEGIN: NAV -->
								<div class="hmenu_navigation_holder">
									'.$this->hmenu_build_nav_html($tree, 'root').'
									'.$this->hmenu_build_mobile_only_content().'
								</div>
								<div class="hmenu_grp_devider"></div>
							<!-- END: NAV -->	
						';
                    }
                    
                    return $html;
                                    
                break;
                case 'search': #SEARCH
                    
                    $html = '';
                    
                    if ($this->obj_main_styles['search'] > 0) {
                        $html .=
                        '
							<!-- BEGIN: SEARCH -->
								<div class="hmenu_search_holder hmenu_hide_for_mobile" >								
									<div class="hmenu_search_container">									
										<div class="hmenu_search_inner">										
											<div class="hmenu_search_wrap">										
												<div class="hmenu_search_display hmenu_search_'.$this->obj_search_styles['type'].'"><!-- hmenu_search_classic, hmenu_search_slide, hmenu_search_full -->												
						';
                        
                        if ($this->obj_search_styles['type'] == 'full') {
                            $trigger = 'hmenu_trigger_full';
                        } elseif ($this->obj_search_styles['type'] == 'classic') {
                            $trigger = 'hmenu_trigger_search';
                        } elseif ($this->obj_search_styles['type'] == 'slide') {
                            $trigger = 'hmenu_trigger_lightbox';
                        }
                        
                        if ($this->obj_search_styles['type'] == 'classic') {
                            $html .= '
								<div class="hmenu_search_input">
									<form role="search" method="get" id="searchform" class="searchform" action="'.esc_url(home_url('/')).'">
									
										<div class="hmenu_search_btn '.$trigger.' icon_hero_default_thin_e654" data-id="'.$this->obj_search_styles['searchId'].'" data-link="hmenu_'.$this->obj_search_styles['type'].'_'.$this->obj_search_styles['searchId'].'" data-type="'.$this->obj_search_styles['type'].'"></div>
							';
                            
                            $html .= $this->hmenu_get_unique_data('_search_input');
                            $html .= '									
									</form>	
								</div>
							';
                        } else {
                            $html .= '
								<div class="hmenu_search_btn '.$trigger.' icon_hero_default_thin_e654" data-id="'.$this->obj_search_styles['searchId'].'" data-link="hmenu_'.$this->obj_search_styles['type'].'_'.$this->obj_search_styles['searchId'].'" data-type="'.$this->obj_search_styles['type'].'" data-width="'.$this->obj_search_styles['width'].'" data-height="'.$this->obj_search_styles['height'].'" data-search-toggle="close"></div>
							';
                        }
                        
                        $html .=
                        '																								
												</div>											
											</div>										
										</div>									
									</div>								
								</div>
								<div class="hmenu_grp_devider"></div>
							<!-- END: SEARCH -->					
						';
                    }
                    
                    return $html;
                    
                break;
                case 'social': #SOCIAL
                    
                    $html = '';
                    
                    if ($this->obj_main_styles['social'] > 0) {
                        $html .=
                        '
							<!-- BEGIN: SOCIAL -->
								<div class="hmenu_social_holder hmenu_hide_for_mobile">
									
									<ul class="hmenu_hover_color">
										'.$this->hmenu_build_social_html('main').'
									</ul>
									
								</div>
								<div class="hmenu_grp_devider"></div>
							<!-- END: SOCIAL -->
						';
                    }
                                
                    return $html;
                
                break;
                case 'product': #PRODUCT
                
                    global $woocommerce;
                    
                    $html = '';
                    
                    if ($this->obj_main_styles['cart'] > 0) {
                        $html .=
                        '
							<!-- BEGIN: PRODUCT -->
									<div class="hmenu_product_holder hmenu_product_toggle_display">												
										<ul class="hmenu_hover_color">
											'.$this->hmenu_build_cart_html().'
										</ul>												
									</div>
									<div class="hmenu_grp_devider hmenu_product_toggle_display"></div>
							<!-- END: PRODUCT -->
						';
                    }
                
                    return $html;
                
                break;
            }
        }
        
        #BUILD MOBILE HTML
        private function hmenu_build_mobile_only_content()
        {
            
            #GLOBALS
            global $wpdb, $post;
            
            #SETUP POST DATA
            setup_postdata($post);
            
            $code = '';
            
            #MOBILE SEARCH AND SOCIAL HTML
            if ($this->obj_main_styles['search'] > 0 || $this->obj_main_styles['social'] > 0) {
                $code = '<ul class="hmenu_show_for_mobile">';
                if ($this->obj_main_styles['search'] > 0) {
                    $code .=
                        '
							<li class="hmenu_mobile_search_holder">
								<form role="search" method="get" id="searchform_mobile" class="searchform_mobile" action="'.esc_url(home_url('/')).'">
									<div class="hmenu_search_btn hmenu_trigger_search icon_hero_default_thin_e654"></div>
									<input type="text" value="'.get_search_query().'" name="s" id="s_mobile" placeholder="'.$this->obj_search_styles['placeholder'].'" />
									<input type="submit" id="hmenu_search_submit_mobile" class="hmenu_search_submit" value="'.esc_attr_x('Search', 'submit button').'" />
								</form>
							</li>
						';
                }
                if ($this->obj_main_styles['social'] > 0) {
                    $code .= '
							<li class="hmenu_mobile_social_holder">
								'.$this->hmenu_build_social_html('mobile').'
							</li>
						';
                }
                $code .= '</ul>';
            }
            
            return $code;
        }
        
        #BUILD SOCIAL HTML
        private function hmenu_build_social_html($type)
        {
            $social_html = '';
            
            if (!empty($this->obj_social_items)) {
                if ($type == 'main') {
                    foreach ($this->obj_social_items as $item) {
                        $social_html .= '<li id="hmenu_social_item_'.$item['socialId'].'"><a href="'.$item['link'].'" target="'.$item['target'].'"><div class="hmenu_wrap '.$item['iconContent'].'"></div></a></li>';
                    }
                } elseif ($type == 'mobile') {
                    foreach ($this->obj_social_items as $item) {
                        $social_html .= '<a id="hmenu_social_mobile_item_'.$item['socialId'].'" href="'.$item['link'].'" target="'.$item['target'].'"><div class="hmenu_wrap '.$item['iconContent'].'"></div></a>';
                    }
                }
            }
            
            return $social_html;
        }
        
        #BUILD CART HTML
        private function hmenu_build_cart_html()
        {
            global $woocommerce;
        
            $cart_html = '';
            
            $cart_html .= '<li><a href=""><div id="hmenu_cart_icon" class="hmenu_wrap icon_hero_default_thin_e611"><div class="hmenu_cart_num_color"></div></div></a></li>';
            
            return $cart_html;
        }
        
        #GET URL
        private function hmenu_get_navitem_url($id, $type, $link)
        {
        
            #GLOBALS
            global $wpdb, $post;
            
            #SETUP POST DATA
            setup_postdata($post);
            
            #URL STRING
            $the_string = '';
            
            switch ($type) {
                case 'basic':
                    $the_string = get_permalink($id);
                break;
                case 'category':
                    $the_string = get_category_link($id);
                break;
                case 'custom':
                    $the_string = $link;
                break;
                case 'mega':
                    if ($link == 'undefined') {
                        $the_string == '';
                    } else {
                        $the_string = $link;
                    }
                break;
                default:
                    $the_string = get_term_link($id, $type);
                break;
            }
            
            return $the_string;
        }
        
        #BUILD HTML
        private function hmenu_build_nav_html($obj, $level)
        {

            #VARIABLES
            $users = $this->hmenu_get_users(true);

            #GET CURRENT USER INFO
            $current_user = wp_get_current_user();
            $current_role = 'hmenu_basic';

            $number_of_roles = sizeof($current_user->roles);

            #HOVER TYPE
            if ($this->obj_main_styles['bgHoverType'] == 'background') {
                $hover_style = 'hmenu_full_hover';
            } elseif ($this->obj_main_styles['bgHoverType'] == 'underline') {
                $hover_style = 'hmenu_underline_hover';
            } elseif ($this->obj_main_styles['bgHoverType'] == 'border') {
                $hover_style = 'hmenu_border_hover';
            }
            
            $root_ul_class = '';
            
            #IF ROOT ITEMS
            if ($level == 'root') {
                $root_ul_class = 'hmenu_hover_color ' . $hover_style;
            }
            
            $code = '<ul class="hmenu_navigation_root '.$root_ul_class.'">';

            #IS LOGGED IN
            if (0 != $current_user->ID) {
                if ($number_of_roles == 1) {
                    $current_role = $current_user->roles[0];
                } else {
                    $current_role = $current_user->roles[1];
                }
            }
            
            foreach ($obj as $i) {
                #NAV ITEM ROLES
                $item_roles = explode(',', $i['roles']);
                $show_item = false;
                if (empty($item_roles[0]) || !empty($item_roles) && in_array($current_role, $item_roles) || is_user_logged_in() == false && $item_roles[0] == 'logged-out') {
                    $show_item = true;
                }
                #GET LINK
                $the_url = '';
                #CHECK ICON ACTIVE STATE
                if ($i['icon'] > 0) {
                    $icon_content = $i['iconContent'] . ' ' . 'hmenu_nav_uni_'.$i['navItemId'];
                } else {
                    $icon_content = 'hmenu_nav_uni_'.$i['navItemId'];
                }
                #CUSTOM CLASS
                $custom_css_class = '';
                if ($i['cssClass'] != '') {
                    $custom_css_class = $i['cssClass'];
                }
                #METHOD
                if ($i['type'] == 'method' && $i['methodReference'] != '') {
                    if (strpos($i['methodReference'], '(') !== false && strpos($i['methodReference'], ');') !== false) {
                        $method_reference = stripslashes('onClick="'.$i['methodReference'].'"');
                    } elseif (strpos($i['methodReference'], '(') !== false && strpos($i['methodReference'], ')') !== false) {
                        $method_reference = stripslashes('onClick="'.$i['methodReference'].';"');
                    } else {
                        $method_reference = stripslashes('onClick="'.$i['methodReference'].'();"');
                    }
                } elseif ($i['type'] == 'mega') {
                    if ($i['link'] == '' || $i['link'] == 'undefined') {
                        $the_url = '';
                    } else {
                        $the_url = 'href="'.$this->hmenu_get_navitem_url($i['postId'], $i['type'], $i['link']).'" target="'.$i['target'].'"';
                    }
                } else {
                    $method_reference = '';
                    $the_url = 'href="'.$this->hmenu_get_navitem_url($i['postId'], $i['type'], $i['link']).'" target="'.$i['target'].'"';
                }
                #CHECK CURRENT URL WITH NAVIGATIONAL ITEM URL
                if ($this->global_current_url == $this->hmenu_get_navitem_url($i['postId'], $i['type'], $i['link'])) {
                    $active_nav_item = 'hmenu_active_nav ';
                } else {
                    $active_nav_item = '';
                }
                #CHECK ROOT
                if ($level != 'root') {
                    $side_arrow = 'icon_hero_default_thin_e602';
                    $item_devider = ''; /* sub menu arrow */
                } else {
                    if ($this->obj_main_styles['arrows'] > 0) {
                        $side_arrow = 'icon_hero_default_thin_e600';
                    } else {
                        $side_arrow = 'icon_hero_default_thin_e600';
                    }
                    $item_devider = '<div class="hmenu_item_devider"></div>'; /* root menu arrow */
                }
                if ($i['sub']) {
                    #ITEM
                    if ($show_item) {
                        $code .= '<li class="'.$active_nav_item.''.$custom_css_class.'"><a title="'.$i['title'].'" '.$the_url.' '.$method_reference.'><div class="hmenu_wrap '.$icon_content.' '.$side_arrow.'"><span>'.$i['name'].'</span></div></a>'.$item_devider;
                        #SUB DIV
                        if ($this->obj_dropdown_styles['devider'] > 0) {
                            $dropdown_devider = 'hmenu_drop_devider';
                        } else {
                            $dropdown_devider = '';
                        }
                        $code .= '<div class="hmenu_submenu hmenu_sub '.$dropdown_devider.'" data-menu-level="'.$i['level'].'">';
                        $code .= $this->hmenu_build_nav_html($i['sub'], 'sub');
                        $code .= '</div>';
                        $code .= '</li>';
                    }
                } else {
                    #ITEM
                    if ($i['mega_menus']) {
                        if ($show_item) {
                            $remove_mega_style = '';
                            $remove_mega_style_mobile = '';
                            if ($i['activeMobile'] > 0) {
                                $remove_mega_style_mobile = 'hmenu_remove_mega_mobile';
                            }
                            if ($i['active'] < 1) {
                                $remove_mega_style = 'hmenu_remove_mega';
                            }
                            $code .= '<li class="hmenu_mega_li '.$remove_mega_style_mobile. ' ' .$remove_mega_style. ' '.$custom_css_class.'"><a '.$the_url.' title="'.$i['title'].'"><div class="hmenu_wrap '.$icon_content.' hmenu_mega_menu '.$side_arrow.'"><span>'.$i['name'].'</span></div></a>'.$item_devider;
                            #MEGA MENU
                            $mega_bg = '';
                            if ($i['mega_menus'][0]['background'] > 0 && $i['mega_menus'][0]['backgroundUrl'] != '') {
                                $mega_bg = 'style="background-position:' . $i['mega_menus'][0]['backgroundPosition'] . '; background-image:url(' . $i['mega_menus'][0]['backgroundUrl'] . ');"';
                            }
                            $code .=
                                '
									<!-- BEGIN: MEGA -->
										<div class="hmenu_submenu hmenu_mega_sub hmenu_drop_devider" '.$mega_bg.'> <!-- MEGA SUB -->
											<div class="hmenu_mega_inner">
												'.$this->hmenu_get_mega_col_data($i['mega_menus'][0]).'
											</div>
										</div>
									<!-- END: MEGA -->
								';
                        }
                    } else {
                        if (!$i['sub']) {
                            if ($show_item) {
                                $code .= '<li class="'.$active_nav_item.''.$custom_css_class.'"><a '.$the_url.' title="'.$i['title'].'" '.$method_reference.'><div class="hmenu_no_sub hmenu_wrap '.$icon_content.'"><span>'.$i['name'].'</span></div></a>'.$item_devider;
                            }
                        } else {
                            if ($show_item) {
                                $code .= '<li class="'.$active_nav_item.''.$custom_css_class.'"><a '.$the_url.' title="'.$i['title'].'" '.$method_reference.'><div class="hmenu_wrap '.$icon_content.'"><span>'.$i['name'].'</span></div></a>'.$item_devider;
                            }
                        }
                    }
                    if ($show_item) {
                        $code .= '</li>';
                    }
                }
            }
            
            $code .= '</ul>';
                        
            return $code;
        }
        
        #GET MEGA MENU DATA
        private function hmenu_get_mega_col_data($obj)
        {
            $columns = explode(',', $obj['layout']);
            
            $custom_layout5 = '';
            
            if ($obj['layout'] == '5,5,5,5,5') {
                $custom_layout5 = 'hmenu_custom_5';
            }
            
            $col_html = '';
            
            foreach ($columns as $key => $col) {
                foreach ($obj['mega_stuff'] as $col_item) {
                    if ($col_item['placement'] == $key) {
                        if ($col_item['type'] == 'contact' && $col_item['shortcode'] == 1) {
                            $plugin_contact_style = 'hmenu_contact_plugin_'.$col_item['id'];
                        } else {
                            $plugin_contact_style = '';
                        }
                        if ($col_item['type'] == 'map' && $col_item['shortcode'] == 1) {
                            $plugin_map_style = 'hmenu_map_plugin_'.$col_item['id'];
                        } else {
                            $plugin_map_style = '';
                        }
                        $col_html .=
                        '<!-- BEGIN: COL -->
							<div class="hmenu_col_'.$col. ' ' .$custom_layout5. ' ' . $plugin_map_style .' '. $plugin_contact_style .'" data-type="'.$col_item['type'].'" data-id="'.$col_item['id'].'">
								<div class="hmenu_inner_col hmenu_col_load">
						';
                            
                        $col_type = $col_item['type'];
                        $col_id = $col_item['id'];
                            
                        #MEGA CONTENT HERE
                        switch ($col_type) {
                                
                                #POST
                                case 'post':
                                    $data = $this->hmenu_get_mega_posts($col_id);
                                    $col_html .= $this->hmenu_get_mega_posts_html($data);
                                break;
                                
                                #TEXT
                                case 'text':
                                    $data = $this->hmenu_get_mega_text($col_id);
                                    $col_html .= $this->hmenu_get_mega_text_html($data);
                                break;
                                
                                #LIST
                                case 'list':
                                    $data = $this->hmenu_get_mega_list($col_id);
                                    $col_html .= $this->hmenu_get_mega_list_html($data);
                                break;
                                
                                #CONTACT
                                case 'contact':
                                    $data = $this->hmenu_get_mega_contact($col_id);
                                    $col_html .= $this->hmenu_get_mega_contact_html($data);
                                break;
                                
                                #WOO
                                case 'woo':
                                    $data = $this->hmenu_get_mega_woo($col_id);
                                    $col_html .= $this->hmenu_get_mega_woo_html($data);
                                break;
                                
                                #MAP
                                case 'map':
                                    $data = $this->hmenu_get_mega_map($col_id);
                                    $col_html .= $this->hmenu_get_mega_map_html($data);
                                break;
                                
                                #IMAGES
                                case 'images':
                                    $data = $this->hmenu_get_mega_images($col_id);
                                    $col_html .= $this->hmenu_get_mega_images_html($data);
                                break;
                                
                            };
                            
                        $col_html .= '
								</div>
						';
                        if ($this->obj_mega_styles['devider'] > 0) {
                            $col_html .= '<div class="hmenu_col_devider"></div>';
                        }
                        $col_html .= '
							</div>
						<!-- END: COL -->';
                    }
                }
            }
            
            return $col_html;
        }
        
        ////////////////////////////////////////////////////////////////////////////////
        
        #GET MEGA POSTS
        private function hmenu_get_mega_posts($id)
        {
            
            #GLOBALS
            global $wpdb, $woocommerce;
                        
            #SETUP POST DATA
            
            #GET THE BLOG POST DETAILS
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_mega_blog WHERE deleted = '0' AND megaBlogId = ".$id."  ORDER BY created DESC");
            
            #ARRAY
            $post_array = array(
                'settings'=> array(),
                'posts'=> array()
            );
            
            #IF HAS RESULT
            if ($result) {
                
                #GET TERM DATA
                $term_data = $wpdb->get_results("SELECT * FROM ". $wpdb->prefix ."term_taxonomy WHERE term_id = " . $result[0]->termId);
                
                $the_post_type = $this->hmenu_get_post_type_ref($term_data[0]->taxonomy);
                
                $t = get_term_by('id', $result[0]->termId, $term_data[0]->taxonomy);
                
                if ($term_data[0]->taxonomy !== 'category') {
                    $the_term = $t->slug;
                } else {
                    $the_term = $t->term_id;
                }
                
                #PAGE ARGUMENTS
                $args = array(
                    'post_type' => $the_post_type,
                    'numberposts' => $result[0]->numberPosts,
                    'orderby' => 'date',
                    $term_data[0]->taxonomy => $the_term,
                    'order' => 'DESC'
                );
                $the_posts = get_posts($args);
                if ($the_posts) {
                    #settings array
                    array_push($post_array['settings'], array(
                        'heading' => $result[0]->heading,
                        'heading_underline' => $result[0]->headingUnderline,
                        'heading_allow' => $result[0]->headingAllow,
                        'description' => $result[0]->description,
                        'description_count' => $result[0]->descriptionCount,
                        'image_allow' => $result[0]->featuredImage,
                        'image_size' => $result[0]->featuredSize,
                        'target' => $result[0]->target
                    ));
                    foreach ($the_posts as $post) {
                        #WOO DATA
                        $price = '';
                        $sale_price = 0;
                        $type_prod = '';
                        if ($post->post_type == 'product') {
                            $product = wc_get_product($post->ID);
                            $type_prod = $product->get_type();

                            $price = get_woocommerce_currency_symbol(get_option('woocommerce_currency')) . ' ' . number_format((float)get_post_meta($post->ID, '_regular_price', true), 2, '.', '');
                            
                            $sale_check = get_post_meta($post->ID, '_sale_price', true);
                            
                            if ($sale_check != '' || $sale_check > 0) {
                                $sale_price = get_woocommerce_currency_symbol(get_option('woocommerce_currency'))  . ' ' . number_format((float)get_post_meta($post->ID, '_sale_price', true), 2, '.', '');
                            }
                        }
                        #FEATURED IMAGE
                        $image_details = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $result[0]->featuredSize);
                        #CONTENT
                        if ($post->post_excerpt != '') {
                            $content = rtrim(mb_substr(strip_shortcodes(strip_tags($post->post_excerpt)), 0, $result[0]->descriptionCount)).'...';
                        } else {
                            $content = rtrim(mb_substr(strip_shortcodes(strip_tags($post->post_content)), 0, $result[0]->descriptionCount));
                        }
                        if ($image_details) {
                            array_push($post_array['posts'], array(
                                'id' => $post->ID,
                                'title' => $post->post_title,
                                'content' => $content,
                                'url' => get_permalink($post->ID),
                                'image' => $image_details[0],
                                'post_type' => $post->post_type,
                                'price' => $price,
                                'sale' => $sale_price,
                                'type' => $type_prod,
                            ));
                        } else {
                            array_push($post_array['posts'], array(
                                'id' => $post->ID,
                                'title' => $post->post_title,
                                'content' => $content,
                                'url' => get_permalink($post->ID),
                                'image' => '',
                                'post_type' => $post->post_type,
                                'price' => $price,
                                'sale' => $sale_price,
                                'type' => $type_prod,
                            ));
                        }
                    }
                    return $post_array;
                } else {
                    return '';
                }
            } else {
                return '';
            }
        }
        
        #RETURN TAXONOMY POST TYPE
        private function hmenu_get_post_type_ref($tax)
        {
            global $wp_taxonomies;
            return (isset($wp_taxonomies[$tax])) ? $wp_taxonomies[$tax]->object_type[0] : array();
        }
        
        #GET MEGA POSTS HTML
        private function hmenu_get_mega_posts_html($data)
        {

            #GLOBALS
            global $woocommerce;
            
            $html = '';
            
            if (!empty($data['posts'])) {
                $posts = $data['posts'];
                $settings = $data['settings'][0];
                
                #HEADING
                if ($settings['heading']) {
                    if ($settings['heading_underline'] > 0) {
                        $html .= '<h2 class="hmenu_mega_bottom_border">';
                    } else {
                        $html .= '<h2>';
                    }
                    $html .= $settings['heading'];
                    $html .= '</h2>';
                }
                
                #POSTS
                foreach ($posts as $post) {
                    $html .= '<a href="'.$post['url'].'" target="'.$settings['target'].'" class="hmenu_post_item">';
                    if ($post['image'] && $settings['image_allow'] > 0) {
                        $html .= '<div class="hmenu_post_img" style="background-image:url('.$post['image'].');"></div>';
                    }
                    $html .= '<div class="hmenu_post_content">';
                    if ($settings['heading_allow'] > 0) {
                        $html .= '<h3>'.$post['title'].'</h3>';
                    }
                    if ($post['post_type'] == 'product') {
                        $html .= '<div class="hmenu_woo_pricing">';
                        if ($post['sale'] != '' || $post['sale'] > 0) {
                            $html .= '<div class="hmenu_mega_price_sale">'.$post['sale'].'</div>';
                            $html .= '<div class="hmenu_mega_price_old">'.$post['price'].'</div>';
                        } else {
                            if ($post['type'] !== 'variable') {
                                $html .= '<div class="hmenu_mega_price">' . $post['price'] . '</div>';
                            }
                        }
                        $html .= '</div>';
                    }
                    if ($settings['description'] > 0) {
                        $html .= '<span>'.$post['content'].'</span>';
                    }
                    if ($post['post_type'] == 'product' && $this->obj_mega_styles['wooBtnText'] != '') {
                        $html .= '<div class="hmenu_mega_prod_btn">'.$this->obj_mega_styles['wooBtnText'].'</div>';
                    }
                    $html .= '</div>';
                    $html .= '</a>';
                };
            } else {
                return '';
            }
            
            return $html;
        }
        
        #GET MEGA TEXT CONTENT
        private function hmenu_get_mega_text($id)
        {
            
            #GLOBALS
            global $wpdb, $post;
                        
            #SETUP POST DATA
            @setup_postdata($post);
            
            #GET THE CONTENT/TEXT DETAILS
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_mega_content WHERE deleted = '0' AND contentId = ".$id."  ORDER BY created DESC");
            
            #ARRAY
            $text_array = array(
                'settings'=> array()
            );
                
            if ($result) {
                #CONTENT
                if ($result[0]->text != '') {
                    $text = $result[0]->text;
                } else {
                    $text = $result[0]->text;
                }
                array_push($text_array['settings'], array(
                    'heading' => $result[0]->heading,
                    'heading_underline' => $result[0]->headingUnderline,
                    'content' => nl2br($text),
                    'content_count' => $result[0]->textCount,
                    'alignment' => $result[0]->textAlignment,
                    'padding_top' => $result[0]->paddingTop.'px',
                    'padding_bottom' => $result[0]->paddingBottom.'px'
                ));
                return $text_array;
            } else {
                return '';
            }
        }
        
        #GET MEGA TEXT HTML
        private function hmenu_get_mega_text_html($data)
        {
            $html = '';
            
            if (!empty($data['settings'])) {
                $settings = $data['settings'][0];
                                
                #HEADING
                if ($settings['heading']) {
                    if ($settings['heading_underline'] > 0) {
                        $html .= '<h2 class="hmenu_mega_bottom_border" style="padding-top:'.$settings['padding_top'].'!important; padding-bottom:'.$settings['padding_bottom'].'!important">';
                    } else {
                        $html .= '<h2 style="padding-top:'.$settings['padding_top'].'!important; padding-bottom:'.$settings['padding_bottom'].'!important">';
                    }
                    $html .= $settings['heading'];
                    $html .= '</h2>';
                }
                
                #CONTENT
                if (strlen($settings['content']) > 0 && $settings['content'] != '') {
                    $html .= '<div class="hmenu_text_item" >';
                    $html .= $settings['content'];
                    $html .= '</div>';
                }
            } else {
                return '';
            }
            
            return $html;
        }
        
        #GET MEGA LIST
        private function hmenu_get_mega_list($id)
        {
            
            #GLOBALS
            global $wpdb, $post;
                        
            #SETUP POST DATA
            @setup_postdata($post);
            
            #GET THE LIST DETAILS
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_mega_list WHERE deleted = '0' AND listId = ".$id."  ORDER BY created DESC");
            
            #ARRAY
            $list_array = array(
                'settings'=> array(),
                'items'=> array()
            );
            
            #CONTENT
            if ($result[0]->text != '') {
                $text = rtrim(substr($result[0]->text, 0, $result[0]->textCount)).'...';
            } else {
                $text = rtrim(substr(''.$result[0]->text, 0, $result[0]->textCount));
            }
                
            #IF HAS RESULT
            if ($result) {
                #settings array
                array_push($list_array['settings'], array(
                    'heading' => $result[0]->heading,
                    'heading_underline' => $result[0]->headingUnderline,
                    'text' => $text,
                    'text_count' => $result[0]->textCount,
                    'alignment' => $result[0]->textAlignment,
                    'padding_top' => $result[0]->paddingTop.'px',
                    'padding_bottom' => $result[0]->paddingBottom.'px'
                ));
                
                #GET THE LIST ITEMS
                $result_items = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_mega_list_items WHERE deleted = '0' AND listId = ".$result[0]->listId."  ORDER BY `order` ASC");
                                
                //CHECK IF LIST ITEMS EXISTS
                if ($result_items) {
                    foreach ($result_items as $item) {
                        switch ($item->type) {
                            case 'pages':
                                #FEATURED IMAGE
                                $image_details = wp_get_attachment_image_src(get_post_thumbnail_id($item->postId), 'small');
                                if ($image_details) {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'item_id' => $item->postId,
                                        'url' => get_permalink($item->postId),
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description,
                                        'image' => $image_details[0]
                                    ));
                                } else {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'item_id' => $item->postId,
                                        'url' => get_permalink($item->postId),
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description
                                    ));
                                }
                                
                            break;
                            case 'categories':
                                $image_details = wp_get_attachment_image_src(get_post_thumbnail_id($item->postId), 'small');
                                if ($image_details) {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'item_id' => $item->termId,
                                        'url' => get_category_link($item->termId),
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description,
                                        'image' => $image_details[0]
                                    ));
                                } else {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'item_id' => $item->termId,
                                        'url' => get_category_link($item->termId),
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description,
                                        'image' => ''
                                    ));
                                }

                            break;
                            case 'custom':
                                $image_details = wp_get_attachment_image_src(get_post_thumbnail_id($item->postId), 'small');
                                if ($image_details) {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'url' => $item->url,
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description,
                                        'image' => $image_details[0]
                                    ));
                                } else {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'url' => $item->url,
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description,
                                        'image' => ''
                                    ));
                                }
                                
                            break;
                            case 'post_types':
                                $image_details = wp_get_attachment_image_src(get_post_thumbnail_id($item->postId), 'small');
								if ($image_details == false){
									$image_details = array('');
								}
                                if ($item->taxonomy == '_na') {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'url' => $item->url,
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description,
                                        'image' => $image_details[0]
                                    ));
                                } else {
                                    array_push($list_array['items'], array(
                                        'id' => $item->listItemId,
                                        'title' => $item->name,
                                        'type' => $item->type,
                                        'item_id' => $item->termId,
                                        'url' => get_term_link((int)$item->termId, $item->taxonomy),
                                        'alt' => $item->alt,
                                        'target' => $item->target,
                                        'icon' => $item->icon,
                                        'icon_content' => $item->iconContent,
                                        'icon_size' => $this->hmenu_get_icon_size($item->iconSize),
                                        'icon_color' => $item->iconColor,
                                        'desc' => $item->desc,
                                        'description' => $item->description,
                                        'image' => $image_details[0]
                                    ));
                                }
                            break;
                        }
                    }
                    return $list_array;
                } else {
                    return '';
                }
            } else {
                return '';
            }
        }
        
        #GET MEGA LIST HTML
        private function hmenu_get_mega_list_html($data)
        {
            $html = '';
            
            if (!empty($data['items'])) {
                $items = $data['items'];
                $settings = $data['settings'][0];
                
                #HEADING
                if ($settings['heading']) {
                    if ($settings['heading_underline'] > 0) {
                        $html .= '<h2 class="hmenu_mega_bottom_border">';
                    } else {
                        $html .= '<h2>';
                    }
                    $html .= $settings['heading'];
                    $html .= '</h2>';
                }
                
                #DESCRIPTION
                if (strlen($settings['text']) > 0 && $settings['text'] != '') {
                    $html .= '<div class="hmenu_list_body_text" style="padding-top:'.$settings['padding_top'].'; padding-bottom:'.$settings['padding_bottom'].'" style="text-align:'.$settings['alignment'].'">';
                    $html .= $settings['text'];
                    $html .= '</div>';
                }
                
                #ITEMS
                foreach ($items as $key => $item) {
                    $icon = '';
                    if ($item['icon'] > 0) {
                        $icon = $item['icon_content'];
                    }
                    $icon_color = $item['icon_color'];
                    $icon_size = $item['icon_size'];
                    
                    
                    $html .= '<a href="'.$item['url'].'" target="'.$item['target'].'" id="hmenu_list_item_uni_'.$item['id'].'" class="hmenu_list_item hmenu_item_'.$key.' '.$icon.'" style="color:'.$icon_color.'; font-size:'.$icon_size.'">';
                    $html .= '<div class="hmenu_list_content">';
                    $html .= '<h3>'.$item['title'].'</h3>';
                    if ($item['desc'] > 0) {
                        $html .= '<span>'.$item['description'].'</span>';
                    }
                    $html .= '</div>';
                    $html .= '</a>';
                }
            } else {
                return '';
            }
            
            return $html;
        }
        
        #GET MEGA CONTACT
        private function hmenu_get_mega_contact($id)
        {
            
            #GLOBALS
            global $wpdb, $post;
                        
            #SETUP POST DATA
            @setup_postdata($post);
            
            #GET THE CONTENT/TEXT DETAILS
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_mega_contact WHERE deleted = '0' AND contactId = ".$id."  ORDER BY created DESC");
            
            #ARRAY
            $contact_array = array(
                'settings'=> array()
            );
                
            if ($result) {
                array_push($contact_array['settings'], array(
                    'heading' => $result[0]->heading,
                    'heading_underline' => $result[0]->headingUnderline,
                    'html' => $result[0]->html,
                    'form_html' => $result[0]->formHtml,
                    'shortcode' => $result[0]->shortcode,
                    'form_shortcode' => $result[0]->formShortcode
                ));
                return $contact_array;
            } else {
                return '';
            }
        }
        
        #GET MEGA CONTACT HTML
        private function hmenu_get_mega_contact_html($data)
        {
            $html = '';
            
            if (!empty($data['settings'])) {
                $settings = $data['settings'][0];
                
                #HEADING
                if ($settings['heading']) {
                    if ($settings['heading_underline'] > 0) {
                        $html .= '<h2 class="hmenu_mega_bottom_border">';
                    } else {
                        $html .= '<h2>';
                    }
                    $html .= $settings['heading'];
                    $html .= '</h2>';
                }
                
                if ($settings['html'] > 0) {
                    $html .= '<div class="hmenu_text_item">';
                    $html .= do_shortcode($settings['form_html']);
                    $html .= '</div>';
                } elseif ($settings['shortcode'] > 0) {
                    $html .= '<div class="hmenu_text_item">';
                    $html .= do_shortcode($settings['form_shortcode']);
                    $html .= '</div>';
                }
            } else {
                return '';
            }
            
            return $html;
        }
        
        #GET MEGA WOO PRODUCTS
        private function hmenu_get_mega_woo()
        {
            return 'woo';
        }
        
        #GET MEGA MAP
        private function hmenu_get_mega_map($id)
        {
            
            #GLOBALS
            global $wpdb, $post;
                        
            #SETUP POST DATA
            @setup_postdata($post);
            
            #GET THE MAPS
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_mega_map WHERE deleted = '0' AND mapId = ".$id."  ORDER BY created DESC");
            
            #ARRAY
            $map_array = array(
                'settings'=> array()
            );
            
            if ($result) {
                array_push($map_array['settings'], array(
                    'map_id' => $result[0]->mapId,
                    'heading' => $result[0]->heading,
                    'heading_underline' => $result[0]->headingUnderline,
                    'map' => $result[0]->map,
                    'map_html' => $result[0]->mapHtml,
                    'shortcode' => $result[0]->shortcode,
                    'map_shortcode' => $result[0]->mapShortcode
                ));
                return $map_array;
            } else {
                return '';
            }
        }
        
        #GET MEGA MAP HTML
        private function hmenu_get_mega_map_html($data)
        {
            $html = '';
            
            if (!empty($data['settings'])) {
                $settings = $data['settings'][0];
                
                #HEADING
                if ($settings['heading']) {
                    if ($settings['heading_underline'] > 0) {
                        $html .= '<h2 class="hmenu_mega_bottom_border">';
                    } else {
                        $html .= '<h2>';
                    }
                    $html .= $settings['heading'];
                    $html .= '</h2>';
                }
                
                if ($settings['map'] > 0) {
                    $html .= '<div class="hmenu_text_item">';
                    $html .= $settings['map_html'];
                    $html .= '</div>';
                } elseif ($settings['shortcode'] > 0) {
                    $html .= '<div class="hmenu_text_item">';
                    $html .= do_shortcode($settings['map_shortcode']);
                    $html .= '</div>';
                }
            } else {
                return '';
            }
            
            return $html;
        }
        
        #GET MEGA IMAGES
        private function hmenu_get_mega_images($id)
        {
            
            #GLOBALS
            global $wpdb, $post;
                        
            #SETUP POST DATA
            @setup_postdata($post);
            
            #GET THE CONTENT/TEXT DETAILS
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_mega_image WHERE deleted = '0' AND imageId = ".$id."  ORDER BY created DESC");
            
            #ARRAY
            $image_array = array(
                'settings'=> array()
            );
                
            if ($result) {
                array_push($image_array['settings'], array(
                    'heading' => $result[0]->heading,
                    'heading_underline' => $result[0]->headingUnderline,
                    'text' => $result[0]->text,
                    'url' => $result[0]->url,
                    'target' => $result[0]->target,
                    'image' => $result[0]->image,
                    'image_heading' => $result[0]->imageHeading,
                    'layout' => $result[0]->displayType
                ));
                return $image_array;
            } else {
                return '';
            }
        }
        
        #GET MEGA POSTS HTML
        private function hmenu_get_mega_images_html($data)
        {
            $html = '';
            
            if (!empty($data['settings'])) {
                $settings = $data['settings'][0];
                
                #HEADING
                if ($settings['heading']) {
                    if ($settings['heading_underline'] > 0) {
                        $html .= '<h2 class="hmenu_mega_bottom_border">';
                    } else {
                        $html .= '<h2>';
                    }
                    $html .= $settings['heading'];
                    $html .= '</h2>';
                }
                
                #IMAGE CONTENT
                $html .= '<a href="'.$settings['url'].'" target="'.$settings['target'].'" class="hmenu_image_holder hmenu_layout_'.$settings['layout'].'">';
                $html .= '<div class="hmenu_image_inner">';
                if ($settings['layout'] != 'three' && $settings['image_heading'] != '') {
                    $html .= '<div class="hmenu_image_heading">'.$settings['image_heading'].'</div>';
                }
                if ($settings['layout'] == 'three') {
                    $html .= '<div class="hmenu_image" style="background-image:url('.$settings['image'].'); height:280px;"></div>';
                } else {
                    $html .= '<div class="hmenu_image" style="background-image:url('.$settings['image'].')"></div>';
                }
                $html .= '<div class="hmenu_image_desc_wrap">';
                $html .= '<div class="hmenu_image_desc">';
                if ($settings['layout'] == 'three' && $settings['image_heading'] != '') {
                    $html .= '<div class="hmenu_image_heading">'.$settings['image_heading'].'</div>';
                }
                $html .= $settings['text'];
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</div>';
                $html .= '</a>';
            } else {
                return '';
            }
            
            return $html;
        }
        
        ////////////////////////////////////////////////////////////////////////////////
                
        #HEX TO RGB
        private function hmenu_hex_to_rgb($hex)
        {
            
            #GET HEX VALUE AND REMOVE THE #
            $hex_value = str_replace('#', '', $hex);
            
            #IF HEX VALUE LENGTH IS 3, ELSE
            if (strlen($hex_value) == 3) {
                $r = hexdec(substr($hex_value, 0, 1).substr($hex_value, 0, 1));
                $g = hexdec(substr($hex_value, 1, 1).substr($hex_value, 1, 1));
                $b = hexdec(substr($hex_value, 2, 1).substr($hex_value, 2, 1));
            } else {
                $r = hexdec(substr($hex_value, 0, 2));
                $g = hexdec(substr($hex_value, 2, 2));
                $b = hexdec(substr($hex_value, 4, 2));
            }
            
            #RGB STRING
            $rgb_string = $r . ',' . $g . ',' . $b;
            
            #RETURN RGB STRING
            return $rgb_string;
        }
        
        private function hmenu_background_color($menu_id, $element_attachment, $gradient, $start_color, $end_color, $path, $transparency, $for)
        {
            if ($for == 'sticky') {
                $importance = ' !important';
            } else {
                $importance = '';
            }
            
            switch ($gradient) {
                
                case 0: #IF NO GRADIENT
                    if ($transparency != 0) {
                        $bg_styles = "
							".$element_attachment."{ 
								background: rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") ".$importance."; 
								filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$start_color."', endColorstr='".$start_color."',GradientType=1 ) ".$importance.";
							}
						";
                    } else {
                        $bg_styles = "
							".$element_attachment."{ 
								background-color:transparent; 
							}
						";
                    }
                break;
                                
                case 1: #IF GRADIENT
                if ($transparency != 0) {
                    if ($path == 'horizontal') {
                        $bg_styles = "					
							/* horizontal */
							".$element_attachment."{ 
								background: ".$start_color.";
								background: -moz-linear-gradient(left, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: -webkit-gradient(linear, left top, right top, color-stop(0%,rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.")), color-stop(100%,rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.")));
								background: -webkit-linear-gradient(left, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: -o-linear-gradient(left, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: -ms-linear-gradient(left, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: linear-gradient(to right, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$start_color."', endColorstr='".$end_color."',GradientType=1 );
							}
						";
                    } else {
                        $bg_styles = "	
							/* vertical */
							".$element_attachment."{ 
								background: ".$start_color.";
								background: -moz-linear-gradient(top, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.")), color-stop(100%,rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.")));
								background: -webkit-linear-gradient(top, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: -o-linear-gradient(top, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: -ms-linear-gradient(top, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								background: linear-gradient(to bottom, rgba(".$this->hmenu_hex_to_rgb($start_color).",".$transparency.") 0%, rgba(".$this->hmenu_hex_to_rgb($end_color).",".$transparency.") 100%);
								filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='".$start_color."', endColorstr='".$end_color."',GradientType=0 );
							}					
						";
                    }
                } else {
                    $bg_styles = "	
						/* no bg */
						".$element_attachment."{ 
							background-color:transparent; 
						}					
					";
                }
                break;
                
            }
            
            #RETURN THE BG STYLES
            return $bg_styles;
        }
        
        //BORDER
        private function hmenu_border_color($menu_id, $element_attachment, $border_type, $start_color, $transparency, $border)
        {
            if ($border > 0) {
                $margin = 'margin-bottom:0;';
                if ($border_type == 'border') {
                    $margin = 'margin:0 auto;';
                }
                $border_styles = "
					#hmenu_load_".$menu_id." ".$element_attachment."{ 
						".$border_type.": 1px solid rgb(".$this->hmenu_hex_to_rgb($start_color).", ".$transparency."); ".$border_type.": 1px solid rgba(".$this->hmenu_hex_to_rgb($start_color).", ".$transparency."); -webkit-background-clip: padding-box; background-clip: padding-box;
						".$margin."
					}
				";
            } else {
                $border_styles = "
					#hmenu_load_".$menu_id." ".$element_attachment."{ 
						border:none;
					}
				";
            }
            #RETURN THE BORDER STYLES
            return $border_styles;
        }
        
        //BORDER
        private function hmenu_get_search_border($border, $element_attachment)
        {
            $border_styles = "";
            if ($border > 0) {
                $border_styles .= "
					#hmenu_load_".$this->obj_menu['menuId']." ".$element_attachment."{ 
						border: 1px solid rgb(".$this->hmenu_hex_to_rgb($this->obj_search_styles['borderColor']).", ".$this->obj_search_styles['borderTransparency']."); border: 1px solid rgba(".$this->hmenu_hex_to_rgb($this->obj_search_styles['borderColor']).", ".$this->obj_search_styles['borderTransparency']."); -webkit-background-clip: padding-box; background-clip: padding-box;
					}
				";
                $radius = str_replace(',', 'px ', $this->obj_search_styles['borderRadius']);
                $border_styles .= "
					#hmenu_load_".$this->obj_menu['menuId']." ".$element_attachment."{ 
						border-radius: ".$radius."px;
						-webkit-border-radius: ".$radius."px;
						-moz-border-radius: ".$radius."px;
					}
				";
            }
            #RETURN THE BORDER STYLES
            return $border_styles;
        }
        
        //SHADOW
        private function hmenu_box_shadow($menu_id, $element_attachment, $shadow, $shadow_radius, $shadow_color, $shadow_transparency)
        {
            if ($shadow > 0) {
                $shadow_arr = explode(',', $shadow_radius);
                $shadow_styles = "
					#hmenu_load_".$menu_id." ".$element_attachment."{ 
						-webkit-box-shadow: 0px 0px ".$shadow_arr[1]."px ".$shadow_arr[0]."px rgba(".$this->hmenu_hex_to_rgb($shadow_color).",".$shadow_transparency.");
						-moz-box-shadow: 0px 0px ".$shadow_arr[1]."px ".$shadow_arr[0]."px rgba(".$this->hmenu_hex_to_rgb($shadow_color).",".$shadow_transparency.");
						box-shadow: 0px 0px ".$shadow_arr[1]."px ".$shadow_arr[0]."px rgba(".$this->hmenu_hex_to_rgb($shadow_color).",".$shadow_transparency.");						
						filter: progid:DXImageTransform.Microsoft.Blur(PixelRadius=".$shadow_arr[0].",MakeShadow=true,ShadowOpacity=".$shadow_transparency.");
						-ms-filter: 'progid:DXImageTransform.Microsoft.Blur(PixelRadius=".$shadow_arr[0].",MakeShadow=true,ShadowOpacity=".$shadow_transparency.")';
						zoom: 1;
					}
				";
                #RETURN THE SHADOW STYLES
                return $shadow_styles;
            }
        }
        
        #BORDER RADIUS
        private function hmenu_border_radius($menu_id, $element_attachment, $border)
        {
            if ($border > 0) {
                if ($element_attachment == '.hmenu_main_holder') {
                    $radius = str_replace(',', 'px ', $this->obj_main_styles['borderRadius']);
                } elseif ($element_attachment == '.hmenu_sub') {
                    $radius = str_replace(',', 'px ', $this->obj_dropdown_styles['borderRadius']);
                } elseif ($element_attachment == '.hmenu_mega_sub') {
                    $radius = str_replace(',', 'px ', $this->obj_mega_styles['borderRadius']);
                }
                
                $border_styles = "
					#hmenu_load_".$menu_id." ".$element_attachment."{ 
						border-radius: ".$radius."px;
						-webkit-border-radius: ".$radius."px;
						-moz-border-radius: ".$radius."px;
					}
				";
            } else {
                $border_styles = "
					#hmenu_load_".$menu_id." ".$element_attachment."{ 
						border-radius: 0 0 0 0;
						-webkit-border-radius: 0 0 0 0;
						-moz-border-radius: 0 0 0 0;
					}
				";
            }
            
            #RETURN THE BG STYLES
            return $border_styles;
        }
        
        #GET DATA FOR CERTAIN CALCULATIONS - used to return small pockets of data for small values that require small conditions, also used to keep the css/js and html functions clean!
        private function hmenu_get_unique_data($for)
        {
            
            #VALUES FOR THE CSS FUNCTION
            switch ($for) {
                case '_animation_type':
                    
                    $animation_type = '';
                    
                    $animation_duration = $this->obj_main_styles['animationDuration'];
                    
                    if ($animation_duration == null || $animation_duration == '') {
                        $animation_duration = 500;
                    }
                    
                    if ($this->obj_main_styles['animation'] == 'show') {
                        $animation_type = '
							jQuery(hmenu_sub_menu).stop().animate({
								opacity: 1
							}, 0);
						';
                    } elseif ($this->obj_main_styles['animation'] == 'fade') {
                        $animation_type = '
							jQuery(hmenu_sub_menu).stop().animate({
								opacity: 1
							}, '.$animation_duration.');
						';
                    }
                    
                    return $animation_type;
                    
                break;
                case '_hmenu_sub_menu_item_padding':
                
                    $padding_array = explode(',', $this->obj_dropdown_styles['padding']);
                    
                    $padding_string = '';
                    
                    foreach ($padding_array as $pad) {
                        $padding_string .= $pad.'px ';
                    }
                    
                    $padding_styles =
                    "
						padding:".$padding_string.";						
					";
                    
                    return $padding_styles;
                    
                break;
                case '_hmenu_sub_menu_width':
                    
                    if ($this->obj_dropdown_styles['width'] == '') {
                        $width = '120px';
                    } else {
                        $width = $this->obj_dropdown_styles['width'] . 'px';
                    }
                    
                    $width_styles =
                    "
						width:".$width.";						
					";
                    
                    return $width_styles;
                    
                break;
                case '_hmenu_sub_menu_left_pos':
                    
                    if ($this->obj_dropdown_styles['width'] == '') {
                        $width = '120px';
                    } else {
                        $width = $this->obj_dropdown_styles['width'] . 'px';
                    }
                    
                    $left_styles =
                    "
						left:".$width.";						
					";
                    
                    return $left_styles;
                    
                break;
                case '_search_full_input':
                    $search_html = '';
                    if ($this->obj_main_styles['search'] > 0) {
                        $search_html =
                        '
							<div class="hmenu_search_full_input" id="hmenu_'.$this->obj_search_styles['type'].'_'.$this->obj_search_styles['searchId'].'">
								<form role="search" method="get" id="searchform" class="searchform" action="'.esc_url(home_url('/')).'">																
									<input type="text" value="'.get_search_query().'" name="s" id="s_full" class="hmenu_search_'.$this->obj_search_styles['searchId'].'" placeholder="'.$this->obj_search_styles['placeholder'].'"/>
									<div class="hmenu_search_btn hmenu_trigger_search icon_hero_default_thin_e654"></div>
									<input type="submit" id="hmenu_search_submit_full" class="hmenu_search_submit" value="'.esc_attr_x('Search', 'submit button').'" />								
								</form>
							</div>
						';
                    }
                    return $search_html;
                break;
                case '_search_lightbox_input':
                    $search_html = '';
                    if ($this->obj_main_styles['search'] > 0) {
                        $search_html =
                        '
							<div class="hmenu_search_lightbox_input" id="hmenu_'.$this->obj_search_styles['type'].'_'.$this->obj_search_styles['searchId'].'">
								<div class="hmenu_search_lightbox_close icon_hero_default_thin_e618"></div>
								<div class="hmenu_lightbox_form_holder">
									<form role="search" method="get" id="searchform" class="searchform" action="'.esc_url(home_url('/')).'">																
										<input type="text" value="'.get_search_query().'" name="s" id="s_lightbox" class="hmenu_search_'.$this->obj_search_styles['searchId'].'" placeholder="'.$this->obj_search_styles['placeholder'].'"/>
										<div class="hmenu_search_btn hmenu_trigger_search icon_hero_default_thin_e654"></div>
										<input type="submit" id="hmenu_search_submit_lightbox" class="hmenu_search_submit" value="'.esc_attr_x('Search', 'submit button').'" />								
									</form>
								</div>
							</div>
						';
                    }
                    return $search_html;
                break;
                case '_search_input':
                    $search_html = '';
                    $search_html =
                    '																										
						<input type="text" value="'.get_search_query().'" name="s" id="s_input" class="hmenu_search_'.$this->obj_search_styles['searchId'].'" placeholder="'.$this->obj_search_styles['placeholder'].'"/>
						<input type="submit" id="hmenu_search_submit_input" class="hmenu_search_submit" value="'.esc_attr_x('Search', 'submit button').'" />
					';
                    return $search_html;
                break;
                case '_logo_height': #HEIGHT VALUE FOR THE LOGO HEIGHT
                    $logo_height = $this->obj_main_styles['logoHeight'];
                    if (!empty($logo_height) && $logo_height <= 100) {
                        return $logo_height . '%';
                    } else {
                        return 100 . '%';
                    }
                break;
                case '_padding_left': #PADDING LEFT
                    $padding = $this->obj_main_styles['paddingLeft'];
                    if (!empty($padding) && $padding <= 100) {
                        return $padding . 'px';
                    } else {
                        return 10 . 'px';
                    }
                break;
                case '_padding_right': #PADDING RIGHT
                    $padding = $this->obj_main_styles['paddingRight'];
                    if (!empty($padding) && $padding <= 100) {
                        return $padding . 'px';
                    } else {
                        return 10 . 'px';
                    }
                break;
                case '_menu_arrows': #PADDING RIGHT
                    if ($this->obj_main_styles['arrows'] > 0) {
                        $arrow_styles =
                        '
							color:'.$this->obj_main_styles['arrowColor'].';
							opacity: '.$this->obj_main_styles['arrowTransparency'].';
							filter: Alpha(opacity='.$this->obj_main_styles['arrowTransparency'].'); /* IE8 and earlier */;
						';
                        return $arrow_styles;
                    } else {
                        $arrow_styles =
                        '
							display:none !important;
						';
                        return $arrow_styles;
                    }
                break;
                case '_hmenu_sub_menu_arrows': #PADDING RIGHT
                    if ($this->obj_dropdown_styles['arrows'] > 0) {
                        $arrow_styles =
                        '
							color:'.$this->obj_dropdown_styles['arrowColor'].';
							opacity: '.$this->obj_dropdown_styles['arrowTransparency'].';
							filter: Alpha(opacity='.$this->obj_dropdown_styles['arrowTransparency'].'); /* IE8 and earlier */;
						';
                        return $arrow_styles;
                    } else {
                        $arrow_styles =
                        '
						
						';
                        return $arrow_styles;
                    }
                break;
                case '_dropdown_devider':
                    $border_styles =
                    "
						border-bottom: 1px solid rgb(".$this->hmenu_hex_to_rgb($this->obj_dropdown_styles['deviderColor']).", ".$this->obj_dropdown_styles['deviderTransparency']."); border-bottom: 1px solid rgba(".$this->hmenu_hex_to_rgb($this->obj_dropdown_styles['deviderColor']).", ".$this->obj_dropdown_styles['deviderTransparency']."); -webkit-background-clip: padding-box; background-clip: padding-box;	
					";
                    return $border_styles;
                break;
                case '_mega_devider':
                    if ($this->obj_mega_styles['devider'] > 0) {
                        $devider_styles =
                        "
							.hmenu_col_devider{								
								position:absolute;
								right:-0.5px;
								top:0;
								width:1px;
								background-color:rgba(".$this->hmenu_hex_to_rgb($this->obj_mega_styles['deviderColor']).", ".$this->obj_mega_styles['deviderTransparency'].");
							}
						";
                        return $devider_styles;
                    }
                break;
                case '_cart_css':
                    if ($this->obj_main_styles['cart'] > 0) {
                        $icon_size = $this->hmenu_get_icon_size($this->obj_main_styles['iconProductSize']);
                        $icon_color = $this->obj_main_styles['iconProductColor'];
                        $icon_hover_color = $this->obj_main_styles['iconProductHoverColor'];
                        $cart_styles =
                        "
							#hmenu_load_".$this->obj_menu['menuId']." #hmenu_cart_icon{
								position:relative;
							}
							#hmenu_load_".$this->obj_menu['menuId']." #hmenu_cart_icon:before{								
								color:".$icon_color.";
								font-size:".$icon_size.";
							}
							#hmenu_load_".$this->obj_menu['menuId']." #hmenu_cart_icon:hover:before{								
								color:".$icon_hover_color.";
							}
						";
                        return $cart_styles;
                    }
                break;
                case '_return_padding':
                    $padding_array = explode(',', $this->obj_mega_styles['padding']);
                    $padding_string = '';
                    
                    foreach ($padding_array as $key=>$pad) {
                        $padding_string .= $pad.'px ';
                        if ($key == 2) {
                            $bottom_padding = $pad.'px';
                        }
                    }
                    
                    $padding_styles =
                    "
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_mega_inner{
							display:block !important;
							padding:".$padding_string.";
							background-position:bottom right;
							background-repeat:no-repeat;
						}
						
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_1,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_2,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_3,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_4,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_5,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_6,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_7,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_8,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_9,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_10,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_11,
						#hmenu_load_".$this->obj_menu['menuId']." .hmenu_col_12{ padding-bottom:".$bottom_padding." }						
					";
                    return $padding_styles;
                break;
            }
        }
        
        #REMOVE DIRECTORY AND CONTENTS
        private function hmenu_remove_directory($dir, $menu_directory)
        {
            $it = new RecursiveDirectoryIterator($this->plugin_dir . $this->frontend_directory . $menu_directory . $dir);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->getFilename() === '.' || $file->getFilename() === '..') {
                    continue;
                }
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($this->plugin_dir . $this->frontend_directory . $menu_directory . $dir);
        }
    }
