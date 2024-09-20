<?php
    
    #PLUGIN SETUP
    class hmenu_setup
    {
        
        #CLASS VARS
        private $capability = 'publish_posts';
        private $plugin_name;
        private $plugin_friendly_name;
        private $plugin_version;
        private $plugin_prefix;
        private $plugin_dir;
        private $plugin_url;
        private $first_release;
        private $last_update;
        private $plugin_friendly_description;
        private $display;
        
        #CONSTRUCT
        public function __construct($plugin_name, $plugin_dir, $plugin_url, $plugin_friendly_name, $plugin_version, $plugin_prefix, $first_release, $last_update, $plugin_friendly_description)
        {
            //define class vars
            $this->plugin_name = $plugin_name;
            $this->plugin_dir = $plugin_dir;
            $this->plugin_url = $plugin_url;
            $this->plugin_friendly_name = $plugin_friendly_name;
            $this->plugin_version = $plugin_version;
            $this->plugin_prefix = $plugin_prefix;
            $this->first_release = $first_release;
            $this->last_update = $last_update;
            $this->plugin_friendly_description = $plugin_friendly_description;
            //construct admin menu
            add_action('admin_menu', array(&$this, 'hmenu_construct_admin_menu'));
            //add meta
            add_action('admin_head', array(&$this,'hmenu_add_admin_meta'));
            //load javascript
            add_action('admin_enqueue_scripts', array(&$this, 'hmenu_load_admin_javascript'));
            //load css
            add_action('admin_enqueue_scripts', array(&$this, 'hmenu_load_admin_css'));
            //instantiate display class
            $this->display = new hmenu_display($this->plugin_dir);
            //initialise shortcode listener
            $shortcode = new hmenu_shortcodes($this->plugin_prefix, $this->plugin_name, $this->plugin_dir, $this->plugin_url);
            add_action('init', array(&$shortcode,'hmenu_initialise_shortcode_listener'));
            //non shortcode front-end output
            add_filter('wp_nav_menu_items', array(&$this,'hmenu_implement_custom_menu'), 1, 2);
            add_filter('wp_nav_menu_args', array(&$this,'hmenu_implement_fallback_menu'), 12, 2);
        }
        
        #MENU FILTER FUNCTION
        public function hmenu_implement_fallback_menu($args = array())
        {
            
            #GLOBALS
            global $wpdb;
            
            $args['fallback_cb'] = array(&$this, 'hmenu_menu_fallback');
            
            #GET MENU LOCATION
            $menus_locations = get_registered_nav_menus();
            
            #GET MENUS
            $result = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE overwrite ='".$args['theme_location']."' AND deleted = '0' ORDER BY created DESC");
            
            #CHECK THE RUSULT
            if ($result) {
                foreach ($menus_locations as $location => $description) {
                    if ($result->overwrite == $location) {
                        $args['menu_class'] = '';
                        $args['container'] = '';
                        $args['items_wrap'] = '%3$s';
                    };
                }
            }
                    
            return $args;
        }
        
        #MENU CALL BACK FUNCTION
        public function hmenu_menu_fallback($args)
        {
            
            #INSTANTIATE SHORTCODE CLASS
            $shortcode = new hmenu_shortcodes('hmenu_', 'hmenu', dirname(__FILE__), plugins_url('hmenu') .'/');
            
            #GLOBALS
            global $wpdb;
            
            #GET MENU LOCATION
            $menus_locations = get_registered_nav_menus();
            
            #GET MENUS
            $result = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE overwrite ='".$args['theme_location']."' AND deleted = '0' ORDER BY created DESC");
            
            #CHECK THE RUSULT
            if ($result) {
                foreach ($menus_locations as $location => $description) {
                    if ($result->overwrite == $location) {
                        $atts['id'] = $result->menuId;
                        echo $shortcode->hmenu_use_shortcode($atts);
                    };
                }
            }
        }
        
        #CUSTOM MENU IMPLEMENTATION
        public function hmenu_implement_custom_menu($menu, $args)
        {
            
            #INSTANTIATE SHORTCODE CLASS
            $shortcode = new hmenu_shortcodes('hmenu_', 'hmenu', dirname(__FILE__), plugins_url('hmenu') .'/');
            
            #GLOBALS
            global $wpdb;
            
            #GET MENU LOCATION
            $args = (array)$args;
            
            #GET MENUS
            $result = $wpdb->get_row("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE overwrite ='".$args['theme_location']."' AND deleted = '0' ORDER BY created DESC");
            
            #CHECK THE RUSULT
            if ($result && $args['theme_location'] != '') {
                $atts['id'] = $result->menuId;
                return $shortcode->hmenu_use_shortcode($atts);
            }
            
            #RETURN
            return $menu;
        }
                
        #PAGE LOADER
        public function hmenu_load_page()
        {
            //load global helper
            global $hplugin_helper;
            //load page content
            $this->display->hmenu_output_admin($hplugin_helper, $this->plugin_name, $this->plugin_friendly_name, $this->plugin_version, $this->plugin_url, $this->first_release, $this->last_update, $this->plugin_friendly_description);
        }
        
        #CONSTRUCT ADMIN MENU ITEM
        public function hmenu_construct_admin_menu()
        {
            add_menu_page($this->plugin_friendly_name, 'Hero Menu', $this->capability, 'hmenu', array(&$this,'hmenu_load_page'), 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDIzLjAuMSwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPgo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IlVudGl0bGVkLTEuZnctUGFnZV94MjVfMjAxIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIgoJIHg9IjBweCIgeT0iMHB4IiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTAwIDEwMDsiIHhtbDpzcGFjZT0icHJlc2VydmUiPgo8c3R5bGUgdHlwZT0idGV4dC9jc3MiPgoJLnN0MHtmaWxsOm5vbmU7ZW5hYmxlLWJhY2tncm91bmQ6bmV3ICAgIDt9Cgkuc3Qxe2ZpbGw6I0RFNTI1QjtlbmFibGUtYmFja2dyb3VuZDpuZXcgICAgO30KCS5zdDJ7ZmlsbDojMjMxRjIwO30KPC9zdHlsZT4KPHBhdGggY2xhc3M9InN0MCIgZD0iTTk3Mi41LTQ5MC41Ii8+CjxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik05NzIuNS00OTAuNSIvPgo8cGF0aCBjbGFzcz0ic3QyIiBkPSJNODcuNSw2Mi42TDEwMCw1MS40djIyLjR2MjIuNEw4Ny41LDg1TDc1LDczLjhMODcuNSw2Mi42eiBNNzUsNzFMNzUsNzFsMTIuNS0xMS4yTDEwMCw0OC42TDg3LjUsMzcuNEw3NSwyNi4yCglsMCwwTDYyLjUsMzcuNEw1MCw0OC42aDB2MGwwLDBsMCwwdjBoMEwzNy41LDM3LjRMMjUsMjYuMmwwLDBMMTIuNSwzNy40TDAsNDguNmwxMi41LDExLjJMMjUsNzF2MHYwbDEyLjUsMTEuMkw1MCw5My4zVjcxVjQ4LjZsMCwwCglsMCwwVjcxdjIyLjRsMTIuNS0xMS4yTDc1LDcxTDc1LDcxeiBNMCw1MS40djIyLjR2MjIuNEwxMi41LDg1TDI1LDczLjhMMTIuNSw2Mi42TDAsNTEuNHogTTUwLDQ1LjhWMjMuNFYzLjhMMjYuNSwyNC43bDExLDkuOAoJTDUwLDQ1Ljh6IE03My40LDI0LjhMNTAsMy44djE5LjZ2MjIuNGwxMi41LTExLjJMNzMuNCwyNC44eiIvPgo8L3N2Zz4=');
        }
        
        #ADD META TO ADMIN
        public function hmenu_add_admin_meta()
        {
            //load global helper
            global $hmenu_helper;
            if (is_admin() && $hmenu_helper->hmenu_onAdmin()) { //admin panel
                echo "<meta name='robots' content='noindex, nofollow' />\n";
            }
        }
        
        #LOAD JAVASCRIPT
        public function hmenu_load_admin_javascript()
        {
            //load global helper
            global $hmenu_helper;
            //load jQuery
            wp_enqueue_script('jquery');
            wp_enqueue_media();
            //load plugin js
            if (is_admin() && $hmenu_helper->hmenu_onAdmin()) { //admin panel
                //component manager scripts
                wp_register_script($this->plugin_prefix .'component_manager', $this->plugin_url .'assets/js/component_manager.js', array('jquery'), $this->plugin_version);
                wp_enqueue_script($this->plugin_prefix .'component_manager');
                //admin core scripts
                wp_register_script($this->plugin_prefix .'admin', $this->plugin_url .'assets/js/admin_core.js', array('jquery'), $this->plugin_version);
                wp_enqueue_script($this->plugin_prefix .'admin');
                //toggle manager scripts
                wp_register_script($this->plugin_prefix .'toggle_manager', $this->plugin_url .'assets/js/toggle_manager.js', array('jquery'), $this->plugin_version);
                wp_enqueue_script($this->plugin_prefix .'toggle_manager');
                //toggle manager scripts
                wp_register_script($this->plugin_prefix .'file_processor', $this->plugin_url .'assets/js/font_processor.js', array('jquery'), $this->plugin_version);
                wp_enqueue_script($this->plugin_prefix .'file_processor');
            }
        }

        #LOAD STYLES
        public function hmenu_load_admin_css()
        {
            //load global helper
            global $hmenu_helper;
            //load plugin css
            if (is_admin() && $hmenu_helper->hmenu_onAdmin()) { //admin panel
                //admin core css
                wp_register_style($this->plugin_prefix .'adminstyles', $this->plugin_url .'assets/css/admin_styles.css');
                wp_enqueue_style($this->plugin_prefix .'adminstyles');
                //backend user css
                wp_register_style($this->plugin_prefix .'backendstyles', $this->plugin_url .'assets/css/backend_styles.css');
                wp_enqueue_style($this->plugin_prefix .'backendstyles');
                //google fonts
                wp_register_style($this->plugin_prefix .'googlefonts', '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,400,300,600,700');
                wp_enqueue_style($this->plugin_prefix .'googlefonts');
                //backend static font social
                wp_register_style($this->plugin_prefix .'backendicons', $this->plugin_url .'_static_fonts/hero_static_fonts.css');
                wp_enqueue_style($this->plugin_prefix .'backendicons');
            }
        }
    }
