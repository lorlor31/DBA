<?php  

class ETheme_Version_Check {

    private $current_version = '';
    private $new_version = '';
    private $theme_name = 'legenda';
    private $api_url = '';
    private $ignore_key = 'etheme_notice';
    public $information;
    public $api_key;
    public $url = 'https://8theme.com/demo/docs/legenda/legenda-changelog.txt';
    public $notices;
	public $is_subscription = false;



    function __construct() {
        $theme_data = wp_get_theme('legenda');
        $activated_data = get_option( 'etheme_activated_data' );
        $this->current_version = $theme_data->get('Version');
        $this->api_url = ETHEME_API;
        $this->api_key = ( ! empty( $activated_data['api_key'] ) ) ? $activated_data['api_key'] : false;

	    $this->is_subscription = ( isset($activated_data['item']) && isset($activated_data['item']['license']) && $activated_data['item']['license'] == '8theme-subscription' );

	    add_action('admin_init', array($this, 'dismiss_notices'));
        add_action('admin_notices', array($this, 'show_notices'), 50 );

        if( ! get_option( 'envato_setup_complete', false ) ) {
            $this->setup_notice();
        }

        if( $this->is_update_available() ) {
            $this->update_notice();
        }

        add_action( 'switch_theme', array( $this, 'update_dismiss' ) );
        
        add_filter( 'site_transient_update_themes', array( $this, 'update_transient' ), 20, 2 );
        add_filter( 'pre_set_site_transient_update_themes', array( $this, 'set_update_transient' ) );
        add_filter( 'themes_api', array(&$this, 'api_results'), 10, 3);

    }

	public function activation_page() {
		$this->process_form();
		?>
		<?php if ( etheme_is_activated() ): ?>
			<?php
			    $activated_data = get_option( 'etheme_activated_data' );
			    $activated_data = ( isset( $activated_data['purchase'] ) && ! empty( $activated_data['purchase'] ) ) ? $activated_data['purchase'] : '';
			?>
            <p><?php esc_html_e('Your theme is activated! Now you have lifetime updates, top-notch 24/7 live support and much more.', 'legenda'); ?></p>
			<?php $this->process_form(); ?>
            <p class="etheme-purchase"><i class="et-admin-icon et-key"></i> <span><?php echo substr($activated_data, 0, -8) . '********'; ?></span></p>
            <span class="et-button et-button-active etheme-deactivator no-loader last-button"><?php esc_html_e( 'Deactivate theme', 'legenda' ); ?></span>
		<?php else: ?>
            <p class="et-message et-warning"><?php esc_html_e('Your product should be activated so you may get the access to all the Legenda demos, auto theme updates and included premium plugins. The instructions below in toggle format must be followed exactly.', 'legenda'); ?></p>
            <form action="" class="etheme-form" method="post">
                <p>
                    <label for="purchase-code"><?php esc_html_e('Purchase code', 'legenda'); ?></label>
                    <input type="text" name="purchase-code" placeholder="Example: f20b1cdd-ee2a-1c32-a146-66eafea81761" id="purchase-code" />
                </p>
                <p>
                    <input class="button-primary" name="etheme-purchase-code" type="submit" value="<?php esc_attr_e( 'Activate theme', 'legenda' ); ?>" />

                </p>
            </form>
		<?php endif ?>
		<?php
	}

    public function old_purchase_code() {
        $code = '';

        $activated_data = get_option( 'etheme_activated_data' );

        $option = $activated_data['purchase'];

        if( $option ) {
            $code = $option;
        }

        if( isset( $_POST['purchase-code'] ) && ! empty( $_POST['purchase-code'] ) ) $code = $_POST['purchase-code'];

        return $code;
    }

    public function show_notices() {
        global $current_user;
        $user_id = $current_user->ID;
        if( ! empty( $this->notices ) ) {
            foreach ($this->notices as $key => $notice) {
                if ( ! get_user_meta($user_id, $this->ignore_key . $key) ) {
                    echo '<div class="updated etheme-notification">' . $notice['message'] . '</div>';
                }
            }
        }
    }

    public function dismiss_notices() {
        global $current_user;
        $user_id = $current_user->ID;
        if ( isset( $_GET['et-hide-notice'] ) && isset( $_GET['_et_notice_nonce'] ) ) {
            if ( ! wp_verify_nonce( $_GET['_et_notice_nonce'], 'etheme_hide_notices_nonce' ) ) {
                return;
            }

            add_user_meta($user_id, $this->ignore_key . '_' . $_GET['et-hide-notice'], 'true', true);
        }
    }

    public function setup_notice() {
        $this->notices['_setup'] = array(
            'message' => '
                <p><strong>'.esc_html__('Welcome to Legenda', 'legenda').'</strong> – '.esc_html__('You\'re almost ready to start selling', 'legenda') .':)</p>
                <p class="submit"><a href="' . admin_url( 'themes.php?page=etheme-setup' ) . '" class="button-primary">' . esc_html__('Run the Setup Wizard', 'legenda') . '</a> <a class="button-secondary skip" href="' . esc_url( wp_nonce_url( add_query_arg( 'et-hide-notice', 'setup' ), 'etheme_hide_notices_nonce', '_et_notice_nonce' ) ). '">'.esc_html__('Skip Setup', 'legenda').'</a></p>
            '
        );
    }

    public function activation_notice() {
        $this->notices['_activation'] = array(
            'message' => '
                <p><strong>'.esc_html__('You need to activate Legenda', 'legenda').'</strong></p>
                <p class="submit"><a href="' . admin_url( 'themes.php?page=etheme-setup' ) . '" class="button-primary">'.esc_html__('Activate theme', 'legenda').'</a></p>
            '
        );
    }

    public function update_notice() {
        if( isset( $_GET['_wpnonce'] )) return;
        $this->notices['_update'] = array(
            'message' => '
                    <p>'.esc_html__('There is a new version of Legenda Theme available.', 'legenda').'</p>
                    <p class="submit"><a href="' . admin_url( 'update-core.php?force-check=1&theme_force_check=1' ) . '" class="button-primary">'.esc_html__('Update now', 'legenda').'</a><a class="button-secondary skip" href="' . esc_url( wp_nonce_url( add_query_arg( 'et-hide-notice', 'update' ), 'etheme_hide_notices_nonce', '_et_notice_nonce' ) ). '">'.esc_html__('Dismiss', 'legenda').'</a></p>
                '
        );
    }

    private function api_get_version() {

        $raw_response = wp_remote_get($this->api_url . '?theme=' . ETHEME_THEME_SLUG);
        if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
            $response = json_decode($raw_response['body'], true);
            if(!empty($response['version'])) $this->new_version = $response['version'];
        }
    }

    public function update_dismiss() {
        global $current_user;
        #$user_id = $current_user->ID;
        #delete_user_meta($user_id, $this->ignore_key);
    }


    public function update_transient($value, $transient) {
        if(isset($_GET['theme_force_check']) && $_GET['theme_force_check'] == '1') return false;
        return $value;
    }


    public function set_update_transient($transient) {
    
        $this->check_for_update();

        if( isset( $transient ) && ! isset( $transient->response ) ) {
            $transient->response = array();
        }

        if( ! empty( $this->information ) && is_object( $this->information ) ) {
            if( $this->is_update_available() ) {
                $transient->response[ $this->theme_name ] = json_decode( json_encode( $this->information ), true );
            }
        }

        remove_filter( 'site_transient_update_themes', array( $this, 'update_transient' ), 20, 2 );

        return $transient;
    }


    public function api_results($result, $action, $args) {
    
        $this->check_for_update();

        if( isset( $args->slug ) && $args->slug == $this->theme_name && $action == 'theme_information') {
            if( is_object( $this->information ) && ! empty( $this->information ) ) {
                $result = $this->information;
            }
        }

        return $result;
    }


    protected function check_for_update() {
        $force = false;

        if( isset( $_GET['theme_force_check'] ) && $_GET['theme_force_check'] == '1') $force = true;
        
        // Get data
        if( empty( $this->information ) ) {
            $version_information = get_option( 'etheme-update-info', false );
            $version_information = $version_information ? $version_information : new stdClass;
            
            $this->information = is_object( $version_information ) ? $version_information : maybe_unserialize( $version_information );
            
        }
        
        $last_check = get_option( 'etheme-update-time' );
        if( $last_check == false ){ 
            update_option( 'etheme-update-time', time() );
        }
        
        if( time() - $last_check > 172800 || $force || $last_check == false ){
            
            $version_information = $this->api_info();

            if( isset( $version_information ) ) {
                update_option( 'etheme-update-time', time() );
                
                $this->information          = $version_information;
                $this->information->checked = time();
                $this->information->url     = $this->url;
                $this->information->package = $this->download_url();

            }

        }
        
        // Save results
        update_option( 'etheme-update-info', $this->information );
    }

    public function api_info() {
        $version_information = new stdClass;

        $response = wp_remote_get( $this->api_url . 'info/' . $this->theme_name );
        $response_code = wp_remote_retrieve_response_code( $response );

        if( $response_code != '200' ) {
            return array();
        }

        $response = json_decode( wp_remote_retrieve_body( $response ) );
        if( ! isset( $response ) || ! isset( $response->new_version ) || empty( $response->new_version ) ) {
            return $version_information;
        } 

        $version_information = $response;

        return $version_information;
    }

    public function is_update_available() {
        return version_compare( $this->current_version, $this->release_version(), '<' );
    }

    public function download_url() {
        return ETHEME_API . 'files/get/' . $this->theme_name . '.zip?token=' . $this->api_key;
    }
    public function release_version() {
        $this->check_for_update();
        return $this->information->new_version;
    }


    public function activate( $purchase, $args ) {

        $data = array(
            'api_key' => $args['token'],
            'theme' => ETHEME_DOMAIN,
            'purchase' => $purchase,
        );

	    foreach ( $args as $key => $value ) {
		    $data['item'][$key] = $value;
	    }

        update_option( 'etheme_activated_data', maybe_unserialize( $data ) );
        update_option( 'xtheme_is_activated', true );
	    delete_transient('etheme_plugins_info');
    }

    public function process_form() {
        if( isset( $_POST['etheme-purchase-code'] ) && ! empty( $_POST['etheme-purchase-code'] ) ) {
            $code = trim( $_POST['purchase-code'] );

            if( empty( $code ) ) {
               echo  '<p class="et-message et-error">Enter the purchase code</p>';
                return;
            }
            $theme_id = 5888906;
            $response = wp_remote_get( $this->api_url . 'activate/' . $code . '?envato_id='. $theme_id .'&domain=' .$this->domain() );
            $response_code = wp_remote_retrieve_response_code( $response );

            if( $response_code != '200' ) {
                echo  '<p class="et-message et-error">API request call error.  1) Contact your server provider and make sure that OpenSSL system library is 1.0 or higher  2) Make sure that your purshase code is associated with buyer. If you bought theme using guest account create account with the same email that was used during purchase process.</p>';
                return;
            }

            $data = json_decode( wp_remote_retrieve_body($response), true );

            if( isset( $data['error'] ) ) {
               echo  '<p class="et-message et-error">' . $data['error'] . '</p>';
                return;
            } 

            if( ! $data['verified'] ) {
               echo  '<p class="et-message et-error">Code is not verified!</p>';
                return;
            } 

            $this->activate( $code, $data );

	        wp_safe_redirect(admin_url( 'admin.php?page=et-panel-welcome' ));

	        exit();
        }
    }
    
    public function domain() {
        $domain = get_option('siteurl'); //or home
        $domain = str_replace('http://', '', $domain);
        $domain = str_replace('https://', '', $domain);
        $domain = str_replace('www', '', $domain); //add the . after the www if you don't want it
        return urlencode($domain);
    }
}

if(!function_exists('etheme_check_theme_update')) {
    add_action('init', 'etheme_check_theme_update');
    function etheme_check_theme_update() {
        new ETheme_Version_Check();
    }
}