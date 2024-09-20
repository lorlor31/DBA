<?php
    
    #PLUGIN SHORTCODE MANAGEMENT
    class hmenu_shortcodes
    {
        
        #CLASS VARS
        private $shortcode; //plugin shortcode is the same as the plugin name
        private $plugin_prefix;
        private $plugin_dir;
        private $plugin_url;
        private $display;
        private $frontend;
        private $hmenu_menu_id;
        
        #CONSTRUCT
        public function __construct($plugin_prefix, $plugin_name, $plugin_dir, $plugin_url)
        {
            $this->plugin_prefix = $plugin_prefix;
            $this->shortcode = $plugin_name;
            $this->plugin_dir = $plugin_dir;
            $this->plugin_url = $plugin_url;
            $this->display = new hmenu_display($this->plugin_dir);
            $this->frontend = new hmenu_frontend($this->plugin_dir, $this->plugin_url);
            $this->hmenu_menu_id = 0;
        }
        
        #INITIALISE SHORTCODE LISTENER
        public function hmenu_initialise_shortcode_listener()
        {
            //remove shortcode listener
            remove_shortcode($this->shortcode);
            //add shortcode listener
            add_shortcode($this->shortcode, array(&$this,'hmenu_use_shortcode'));
        }
        
        #USE SHORTCODE
        public function hmenu_use_shortcode($atts)
        { //all front-end code can be initialised here...
            
            //output front-end JS references
            add_action('wp_footer', array( $this, 'hmenu_add_vars_script_footer'));
            
            //define content
            $content = $this->frontend->hmenu_get_shortcode_content($atts);
            //display content on front-end
            $this->hmenu_load_menu_shortcode($atts);

            //load front-end css
            $this->hmenu_load_frontend_css();
            //load front-end scripts
            $this->hmenu_load_frontend_javascript();

            return $this->display->hmenu_output_frontend($content); //this ensure output buffering takes place
        }

        public function hmenu_load_menu_shortcode($atts)
        {
            $this->hmenu_menu_id = $atts['id'];
        }

        public function hmenu_add_vars_script_footer()
        {
            //output front-end JS references
            echo '<script type="text/javascript" data-cfasync="false">
					var hmenu_ajax_url = "'. admin_url('admin-ajax.php') .'";
					var '. $this->plugin_prefix .'url = "'. $this->plugin_url .'";
				</script>';
        }
        
        #IMPLEMENT FRONT-END CSS
        private function hmenu_load_frontend_css()
        {
            //front-end css
            wp_register_style($this->plugin_prefix .'userstyles', $this->plugin_url .'assets/css/frontend_styles.css');
            wp_enqueue_style($this->plugin_prefix .'userstyles');
            //backend static font social
            wp_register_style($this->plugin_prefix .'backendiconsocial', $this->plugin_url .'_static_fonts/hero_static_fonts.css');
            wp_enqueue_style($this->plugin_prefix .'backendiconsocial');
        }
        
        #IMPLEMENT FRONT-END JS
        private function hmenu_load_frontend_javascript()
        {
            $hmenu_current_menu_id = $this->hmenu_menu_id;
            $hmenu_current_url = $this->frontend->hmenu_menu_activation_vars();
   
            //front-end javascript
            wp_register_script($this->plugin_prefix .'user', $this->plugin_url .'assets/js/frontend_script.js', array('jquery'));
            wp_localize_script(
                $this->plugin_prefix .'user',
                'hmenu_frontend_menu',
                array(
                    'hmenu_menu_id' => $hmenu_current_menu_id,
                    'hmenu_menu_url' => $hmenu_current_url
                    )
            );
            wp_enqueue_script($this->plugin_prefix .'user');
            //front-end dimentions
            wp_register_script($this->plugin_prefix .'userdimentions', $this->plugin_url .'assets/js/frontend_dimensions.js', array('jquery'));
            wp_enqueue_script($this->plugin_prefix .'userdimentions');
        }
    }
