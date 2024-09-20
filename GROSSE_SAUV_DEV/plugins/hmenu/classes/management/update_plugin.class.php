<?php
    
    #UPDATE PLUGIN
    class hmenu_update_plugin
    {
        
        #CLASS VARS
        private $plugin_name;
        private $plugin_version;
        private $plugin_old_version;
        private $plugin_dir;
        
        #CONSTRUCT
        public function __construct($plugin_name, $plugin_version, $plugin_old_version, $plugin_dir)
        {
            //set class vars
            $this->plugin_name = $plugin_name;
            $this->plugin_version = $plugin_version;
            $this->plugin_old_version = $plugin_old_version;
            $this->plugin_dir = $plugin_dir;
        }
        
        #TEARDOWN PLUGIN
        public function hmenu_update_plugin()
        {
            
            //access globals
            global $wpdb;
            global $hmenu_helper;
            
            //update plugin tables
            
            // < 0.1.7
            if (version_compare($this->plugin_old_version .'', '0.1.7', "<")) {
                //update main styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles` 
					ADD COLUMN `logoPaddingLeft` VARCHAR(11) NOT NULL DEFAULT '0',
					ADD COLUMN `mobileLogoPaddingLeft` VARCHAR(11) NOT NULL DEFAULT '10',
					ADD COLUMN `stickyLogoPaddingLeft` VARCHAR(11) NOT NULL DEFAULT '10';
				");
            }
            
            // < 1.1.23
            if (version_compare($this->plugin_old_version .'', '1.1.23', "<")) {
                //update nav items table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_nav_items` 
					ADD COLUMN `method` TINYINT(1) NOT NULL DEFAULT '0',
					ADD COLUMN `methodReference` VARCHAR(255) NOT NULL DEFAULT 'functionName';
				");
            }
            
            // < 1.2.3
            if (version_compare($this->plugin_old_version .'', '1.2.3', "<")) {
                //update main styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles` 
					ADD COLUMN `bgMainImage` TINYINT(1) NOT NULL DEFAULT '0',
					ADD COLUMN `bgMainImageUrl` VARCHAR(255) NOT NULL DEFAULT '',
					ADD COLUMN `bgMainImagePosition` VARCHAR(50) NOT NULL DEFAULT 'left',
					ADD COLUMN `bgMainImageRepeat` VARCHAR(50) NOT NULL DEFAULT 'repeat';
				");
            }
            
            // < 1.2.16
            if (version_compare($this->plugin_old_version .'', '1.2.16', "<")) {
                //update font pack table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_font_pack` 
					ADD COLUMN `fontWoff2` BLOB DEFAULT NULL
				");
            }
            
            // < 1.3.10
            if (version_compare($this->plugin_old_version .'', '1.3.10', "<")) {
                //update main styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles`
					ADD COLUMN `logoLink` VARCHAR(255) DEFAULT NULL,
					ADD COLUMN `logoLinkTarget` VARCHAR(20) NOT NULL DEFAULT '_self';
				");
            }
            
            // < 1.3.12
            if (version_compare($this->plugin_old_version .'', '1.3.12', "<")) {
                //settings
                $contraint_uid = date('Hidmy');
                //sql
                $sql_create = "
					CREATE TABLE IF NOT EXISTS `". $wpdb->base_prefix ."hmenu_mobile_styles` (
					  `mobileStyleId` int(11) NOT NULL AUTO_INCREMENT,
					  `menuId` int(11) NOT NULL,
					  `bgBarStartColor` varchar(11) DEFAULT NULL,
					  `bgBarGradient` tinyint(1) DEFAULT NULL,
					  `bgBarEndColor` varchar(11) DEFAULT NULL,
					  `bgBarGradientPath` varchar(11) DEFAULT NULL,
					  `bgBarTransparency` decimal(2,1) DEFAULT NULL,
					  `fontBarFamily` varchar(45) DEFAULT NULL,
					  `fontBarColor` varchar(11) DEFAULT NULL,
					  `fontBarHoverColor` varchar(45) DEFAULT NULL,
					  `fontBarSize` varchar(11) DEFAULT NULL,
					  `fontBarSizing` varchar(11) DEFAULT NULL,
					  `fontBarWeight` varchar(11) DEFAULT NULL,
					  `bgMenuStartColor` varchar(11) DEFAULT NULL,
					  `bgMenuGradient` tinyint(1) DEFAULT NULL,
					  `bgMenuEndColor` varchar(11) DEFAULT NULL,
					  `bgMenuGradientPath` varchar(11) DEFAULT NULL,
					  `bgMenuTransparency` decimal(2,1) DEFAULT NULL,
					  `bgHoverStartColor` varchar(11) DEFAULT NULL,
					  `bgHoverGradient` tinyint(1) DEFAULT NULL,
					  `bgHoverEndColor` varchar(11) DEFAULT NULL,
					  `bgHoverGradientPath` varchar(11) DEFAULT NULL,
					  `bgHoverTransparency` decimal(2,1) DEFAULT NULL,
					  `fontMobileFamily` varchar(45) DEFAULT NULL,
					  `fontMobileColor` varchar(11) DEFAULT NULL,
					  `fontMobileHoverColor` varchar(45) DEFAULT NULL,
					  `fontMobileSize` varchar(11) DEFAULT NULL,
					  `fontMobileSizing` varchar(11) DEFAULT NULL,
					  `fontMobileWeight` varchar(11) DEFAULT NULL,
					  `fontTabletFamily` varchar(45) DEFAULT NULL,
					  `fontTabletColor` varchar(11) DEFAULT NULL,
					  `fontTabletHoverColor` varchar(45) DEFAULT NULL,
					  `fontTabletSize` varchar(11) DEFAULT NULL,
					  `fontTabletSizing` varchar(11) DEFAULT NULL,
					  `fontTabletWeight` varchar(11) DEFAULT NULL,
					  `paddingLeft` varchar(60) DEFAULT NULL,
					  `paddingRight` varchar(60) DEFAULT NULL,
					  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					  `lastModified` datetime DEFAULT NULL,
					  `deleted` tinyint(1) NOT NULL DEFAULT '0',
					  PRIMARY KEY (`mobileStyleId`),
					  KEY `hmenu_mobile_styles_menuId_".$contraint_uid."_FK_idx` (`menuId`),
					  CONSTRAINT `hmenu_mobile_styles_menuId_".$contraint_uid."_FK` FOREIGN KEY (`menuId`) REFERENCES `". $wpdb->base_prefix ."hmenu_menu` (`menuId`) ON DELETE NO ACTION ON UPDATE NO ACTION
					) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;	
				";
                dbDelta($sql_create);
                $sql_drop = "
					DROP TRIGGER IF EXISTS `". $wpdb->base_prefix ."hmenu_mobile_styles`;
				";
                $wpdb->query($sql_drop);
                $sql_create = "
					CREATE TRIGGER `". $wpdb->base_prefix ."hmenu_mobile_styles`
					BEFORE UPDATE ON `". $wpdb->base_prefix ."hmenu_mobile_styles`
					FOR EACH ROW SET NEW.lastModified = NOW();	
				";
                dbDelta($sql_create);
                
                //INSERT DATA
                $result = $wpdb->get_results("
					SELECT
						*
					FROM
						`". $wpdb->base_prefix ."hmenu_menu` `hm`
						INNER JOIN `". $wpdb->base_prefix ."hmenu_main_styles` `hms` ON(`hms`.`menuId` = `hm`.`menuId`)
					WHERE
						`hm`.`deleted` = '0'
					ORDER BY
						`hm`.`created` DESC;
				");
                
                if ($result) {
                    foreach ($result as $menu) {
                        $this->hmenu_insert_mobile_defaults(intval($menu->menuId), $menu);
                    }
                }
            }
            
            // < 1.4.0
            if (version_compare($this->plugin_old_version .'', '1.4.0', "<")) {
                //update main styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles`
					ADD COLUMN `logoAlt` VARCHAR(255) DEFAULT NULL;
				");
            }
            
            // < 1.4.2
            if (version_compare($this->plugin_old_version .'', '1.4.2', "<")) {
                //update main styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles` 
					ADD COLUMN `customCss` BLOB DEFAULT NULL
				");
                //update search styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_search` 
					ADD COLUMN `placeholder` varchar(255) DEFAULT NULL
				");
            }
            
            // < 1.4.8
            if (version_compare($this->plugin_old_version .'', '1.4.8', "<")) {
                //update main styles table
                $wpdb->query("UPDATE `". $wpdb->base_prefix ."hmenu_nav_items`
					SET
						`link` = ''
					WHERE
						`type` = 'mega';
				");
            }
            
            // < 1.5.1
            if (version_compare($this->plugin_old_version .'', '1.5.1', "<")) {
                //update social link
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_social` 
					MODIFY `link` BLOB DEFAULT NULL
				");
                //update mega images table - add target
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_image` 
					ADD COLUMN `target` varchar(20) NOT NULL DEFAULT '_blank'
				");
            }
            
            // < 1.5.5
            if (version_compare($this->plugin_old_version .'', '1.5.5', "<")) {
                //update main styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles` 
					ADD COLUMN `logoPaddingRight` VARCHAR(11) NOT NULL DEFAULT '0'
				");
                //update meag text limit
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_image` 
					MODIFY `text` BLOB DEFAULT NULL
				");
            }
            
            // < 1.5.12
            if (version_compare($this->plugin_old_version .'', '1.5.12', "<")) {
                //update mega contact shortcode
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_contact` 
					MODIFY `formShortcode` BLOB DEFAULT NULL
				");
                //update mega map shortcode
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_map` 
					MODIFY `mapShortcode` BLOB DEFAULT NULL
				");
                //update mega nav items
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_nav_items` 
					MODIFY `type` VARCHAR(255) DEFAULT NULL
				");
            }
            
            // < 1.5.14
            if (version_compare($this->plugin_old_version .'', '1.5.14', "<")) {
                //update mega blog
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_blog` 
					MODIFY `type` VARCHAR(255) DEFAULT NULL
				");
            }
            
            // < 1.6.0 - woocommerce styling elements
            if (version_compare($this->plugin_old_version .'', '1.6.0', "<")) {
                //update mega blog
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_styles`
					ADD COLUMN `wooPriceColor` varchar(11) NOT NULL DEFAULT '#FFFFFF',
					ADD COLUMN `wooPriceFamily` varchar(45) NOT NULL DEFAULT 'Open Sans',
					ADD COLUMN `wooPriceWeight` varchar(11) NOT NULL DEFAULT 'normal',	
					ADD COLUMN `wooPriceSize` varchar(11) NOT NULL DEFAULT '22',
					ADD COLUMN `wooPriceSizing` varchar(11) NOT NULL DEFAULT 'px',										
					ADD COLUMN `wooPriceOldColor` varchar(11) NOT NULL DEFAULT '#DC4551',
					ADD COLUMN `wooPriceOldFamily` varchar(45) NOT NULL DEFAULT 'Open Sans',
					ADD COLUMN `wooPriceOldWeight` varchar(11) NOT NULL DEFAULT 'normal',	
					ADD COLUMN `wooPriceOldSize` varchar(11) NOT NULL DEFAULT '12',
					ADD COLUMN `wooPriceOldSizing` varchar(11) NOT NULL DEFAULT 'px',										
					ADD COLUMN `wooPriceSaleColor` varchar(11) NOT NULL DEFAULT '#FFFFFF',
					ADD COLUMN `wooPriceSaleFamily` varchar(45) NOT NULL DEFAULT 'Open Sans',
					ADD COLUMN `wooPriceSaleWeight` varchar(11) NOT NULL DEFAULT 'normal',				
					ADD COLUMN `wooPriceSaleSize` varchar(11) NOT NULL DEFAULT '22',
					ADD COLUMN `wooPriceSaleSizing` varchar(11) NOT NULL DEFAULT 'px',														
					ADD COLUMN `wooBtnText` varchar(255) NOT NULL DEFAULT 'View Now',
					ADD COLUMN `wooBtnFontFamily` varchar(45) NOT NULL DEFAULT 'normal',
					ADD COLUMN `wooBtnFontColor` varchar(11) NOT NULL DEFAULT '#DC4551',
					ADD COLUMN `wooBtnFontSize` varchar(11) NOT NULL DEFAULT '12',
					ADD COLUMN `wooBtnFontSizing` varchar(11) NOT NULL DEFAULT 'px',
					ADD COLUMN `wooBtnFontWeight` varchar(11) NOT NULL DEFAULT 'normal',
					ADD COLUMN `wooBtnFontDecoration` varchar(25) NOT NULL DEFAULT 'underline'					
				");
            }

            // < 1.7.5
            if (version_compare($this->plugin_old_version .'', '1.7.5', "<")) {
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles`
					ADD COLUMN `bgStickyHoverColor` VARCHAR(11) NOT NULL DEFAULT '#888888'
				");

                $result = $wpdb->get_results("
					SELECT
						*
					FROM
						`". $wpdb->base_prefix ."hmenu_main_styles`
					WHERE
						`deleted` = '0'
					ORDER BY
						`created` DESC;
				");

                if ($result) {
                    foreach ($result as $main_styles) {
                        $wpdb->query("UPDATE `". $wpdb->base_prefix ."hmenu_main_styles`
							SET
								`bgStickyHoverColor` = '".$main_styles->bgStickyStart."'
							WHERE
								`mainStyleId` = ".$main_styles->mainStyleId.";
						");
                    }
                }
            }

            // < 1.8.2
            if (version_compare($this->plugin_old_version .'', '1.8.2', "<")) {
                //update nav items table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_nav_items`
					ADD COLUMN `cssClass` VARCHAR(255) NOT NULL DEFAULT ''
				");
            }

            // < 1.8.6
            if (version_compare($this->plugin_old_version .'', '1.8.6', "<")) {
                //update mega blog
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_list_items`
					MODIFY `name` BLOB DEFAULT NULL
				");
            }

            // < 1.8.9
            if (version_compare($this->plugin_old_version .'', '1.8.9', "<")) {
                //update search styles table
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_nav_items`
					ADD COLUMN `role` TINYINT(1) DEFAULT NULL
				");
                //update nav items - roles
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_nav_items`
					ADD COLUMN `roles` BLOB DEFAULT NULL
				");
                //update mainstyles
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_main_styles`
					ADD COLUMN `eyebrow` TINYINT(1) NOT NULL DEFAULT '0',
				    ADD COLUMN `eyeExcerpt` BLOB DEFAULT NULL,
				    ADD COLUMN `eyeLoginUrl` BLOB DEFAULT NULL,
				    ADD COLUMN `eyeBackground` varchar(20) DEFAULT NULL,
				    ADD COLUMN `eyeColor` varchar(20) DEFAULT NULL,
				    ADD COLUMN `eyeColorHover` varchar(20) DEFAULT NULL,
				    ADD COLUMN `eyePaddingLeft` varchar(11) NOT NULL DEFAULT '10',
				    ADD COLUMN `eyePaddingRight` varchar(11) NOT NULL DEFAULT '10'
				");
            }

            // < 1.9.4
            if (version_compare($this->plugin_old_version .'', '1.9.4', "<")) {
                //update maega list items
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mega_list_items`
					MODIFY `name` BLOB DEFAULT NULL
				");
            }

            // < 1.15.7
            if (version_compare($this->plugin_old_version .'', '1.15.7', "<")) {
                //update mobile styles
                $wpdb->query("ALTER TABLE `". $wpdb->base_prefix ."hmenu_mobile_styles`
					ADD COLUMN `menuBarHeightMobile` varchar(60) NOT NULL DEFAULT '40',
					ADD COLUMN `menuBarbuttonSize` varchar(60) NOT NULL DEFAULT '20',	
					ADD COLUMN `menuBarbuttonPosition` varchar(60) NOT NULL DEFAULT 'right',
					ADD COLUMN `menuBarbuttonColor` varchar(60) DEFAULT NULL,		
					ADD COLUMN `menuBarMobileIcon` varchar(60) NOT NULL DEFAULT 'e912',	
					ADD COLUMN `menuBarMobileIconPaddingLeft` varchar(60) NOT NULL DEFAULT '10',
					ADD COLUMN `menuBarMobileIconPaddingRight` varchar(60) NOT NULL DEFAULT '10'
				");
            }
            
            //generate
            if ($GLOBALS['wp_rewrite']) {
                $this->hmenu_process_generate();
            } else {
                add_action('wp_loaded', array(&$this, 'hmenu_process_generate'));
            }
            
            //mark the upgrade as successful
            $this->hmenu_mark_update_complete();
        }
        
        #INERT MOBILE DEFAULTS
        public function hmenu_insert_mobile_defaults($menu_id, $obj)
        {
            global $wpdb;
            
            #DATA: MOBILE STYLES
            $wpdb->query(
                "
					INSERT INTO `". $wpdb->base_prefix ."hmenu_mobile_styles` 
					(					
						`menuId`,						
						`bgBarStartColor`,
						`bgBarGradient`,
						`bgBarEndColor`,
						`bgBarGradientPath`,
						`bgBarTransparency`,						
						`fontBarFamily`,
						`fontBarColor`,
						`fontBarHoverColor`,
						`fontBarSize`,
						`fontBarSizing`,
						`fontBarWeight`,						
						`bgMenuStartColor`,
						`bgMenuGradient`,
						`bgMenuEndColor`,
						`bgMenuGradientPath`,
						`bgMenuTransparency`,						
						`bgHoverStartColor`,
						`bgHoverGradient`,
						`bgHoverEndColor`,
						`bgHoverGradientPath`,
						`bgHoverTransparency`,						
						`fontMobileFamily`,
						`fontMobileColor`,
						`fontMobileHoverColor`,
						`fontMobileSize`,
						`fontMobileSizing`,
						`fontMobileWeight`,						
						`fontTabletFamily`,
						`fontTabletColor`,
						`fontTabletHoverColor`,
						`fontTabletSize`,
						`fontTabletSizing`,
						`fontTabletWeight`,						
						`paddingLeft`,
						`paddingRight`,
						`menuBarHeightMobile`,
						`menuBarbuttonSize`,
						`menuBarbuttonPosition`,
						`menuBarbuttonColor`				
					) VALUES (					
						'".$menu_id."', 												
						'".$obj->bgMenuStartColor."',
						'".$obj->bgMenuGradient."',
						'".$obj->bgMenuEndColor."',
						'".$obj->bgMenuGradientPath."',
						'".$obj->bgMenuTransparency."',																	
						'".$obj->fontFamily."',
						'".$obj->fontColor."',
						'".$obj->fontHoverColor."',
						'".$obj->fontSize."',
						'".$obj->fontSizing."',
						'".$obj->fontWeight."',												
						'".$obj->bgMenuStartColor."',
						'".$obj->bgMenuGradient."',
						'".$obj->bgMenuEndColor."',
						'".$obj->bgMenuGradientPath."',
						'".$obj->bgMenuTransparency."',												
						'".$obj->bgHoverStartColor."',
						'".$obj->bgHoverGradient."',
						'".$obj->bgHoverEndColor."',
						'".$obj->bgHoverGradientPath."',
						'".$obj->bgHoverTransparency."',											
						'".$obj->fontFamily."',
						'".$obj->fontColor."',
						'".$obj->fontHoverColor."',
						'".$obj->fontSize."',
						'".$obj->fontSizing."',
						'".$obj->fontWeight."',												
						'".$obj->fontFamily."',
						'".$obj->fontColor."',
						'".$obj->fontHoverColor."',
						'".$obj->fontSize."',
						'".$obj->fontSizing."',
						'".$obj->fontWeight."',												
						'".$obj->paddingLeft."',
						'".$obj->paddingRight."',
						'".$obj->menuBarHeightMobile."',
						'".$obj->menuBarbuttonSize."',
						'".$obj->menuBarbuttonPosition."',
						'".$obj->menuBarbuttonColor."',
						'".$obj->menuBarMobileIcon."',
						'".$obj->menuBarMobileIconPaddingLeft."',
						'".$obj->menuBarMobileIconPaddingRight."'
					)
				"
            );
        }
        
        #RE-GENERATE THE MENU FILES
        public function hmenu_process_generate()
        {
            
            #GLOBALS
            global $wpdb;
            
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE deleted = '0' ORDER BY created DESC");
    
            if ($result) {
                $backend = new hmenu_backend($this->plugin_dir);
                $get = new hmenu_class_get($this->plugin_dir);
                $generate = new hmenu_class_generate($this->plugin_dir);
                foreach ($result as $menu) {
                    $menu_object = $get->hmenu_get_main_menu_object(intval($menu->menuId), false);
                    $backend->hmenu_get_fonts('icons', false);
                    $generate->hmenu_generate_files($menu_object, false);
                }
            }
            
            return true;
        }
        
        #MARK UPDATE COMPLETE
        private function hmenu_mark_update_complete()
        {
            //access globals
            global $wpdb;
            //once updates are complete, mark the plugin version in the DB
            $wpdb->query("UPDATE `". $wpdb->base_prefix ."hplugin_root` SET `plugin_version` = '". $this->plugin_version ."' WHERE `plugin_name` = '". $this->plugin_name ."';");
        }
    }
