<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );
// **********************************************************************// 
// ! Etheme Ajax Account Register
// **********************************************************************//

class Etheme_Account_Register{

	// ! Declare variations
	protected $response = array(
		'status' => 'success',
		'fields' => array()

	);
	protected $post = array();

	// ! Main construct
	function __construct(){
		$this->post = $_POST;

		add_action( 'wp_ajax_etheme_account_register', array( $this, 'actions_router' ) );
		add_action( 'wp_ajax_nopriv_etheme_account_register', array( $this, 'actions_router' ) );
	}

	// ! Actions router
	public function actions_router(){

		if ( ! check_ajax_referer( 'et_new_user', 'security', false ) ) {
            echo json_encode( 'No direct script access allowed' );
            die();
        }

        $this->register_user();
	}

	// ! Register new user
	protected function register_user(){

		if ( etheme_get_option('google_captcha_site') && etheme_get_option('google_captcha_secret') ) {
			if ( ! $this->check_captcha() ) {
				$this->response['status'] = 'failed';
	    		$this->response['fields']['greresponse'] = 'invalid';
			}
		}

		$this->check_mail();

		if ( ! $this->check_param( 'username', false ) ) {
			$this->response['status'] = 'failed';
			$this->response['fields']['username'] = 'empty';
		}

		if ( username_exists($this->post['username']) ){
			$this->response['status'] = 'failed';
			$this->response['fields']['username'] = 'exist';
		}

        if ( $this->check_param( 'password1', false ) && $this->check_param( 'password2', false ) ) {
        	if ( $this->post['password1'] != $this->post['password2'] ) {
	        	$this->response['status'] = 'failed';
	    		$this->response['fields']['password1'] = 'match';
	    		$this->response['fields']['password2'] = 'match';
	        } elseif( strlen( $this->post['password1'] ) >= 1 && strlen( $this->post['password2'] ) < 10 ){
	        	$this->response['status'] = 'failed';
	    		$this->response['fields']['password1'] = 'length';
	    		$this->response['fields']['password2'] = 'length';
	        }
        } elseif ( strlen( $this->post['password1'] ) < 1 ) {
        	$this->response['status'] = 'failed';
    		$this->response['fields']['password1'] = 'empty';
    		$this->response['fields']['password2'] = 'empty';
        } else {
        	$this->response['status'] = 'failed';
    		$this->response['fields']['password1'] = 'match';
    		$this->response['fields']['password2'] = 'match';
        }

        if ( $this->response['status'] != 'failed' ) {
        	$this->register_user_process();
        }

        echo json_encode($this->response);
		die();
	}

	// ! Update user meta
	protected function update_user_meta($param){
		update_user_meta( $this->post['user_id'], $param, $this->post[$param] );
	}

	// ! Check mail
	protected function check_mail(){
        if ( is_email( $this->post['usermail'] ) ) {
        	if( $this->check_param( 'usermail', false ) && email_exists( $this->post['usermail'] ) ){
				$this->response['status'] = 'failed';
	        	$this->response['fields']['usermail'] = 'exists';
			}
        } elseif( ! $this->check_param( 'usermail', false ) ) {
        	$this->response['status'] = 'failed';
	       	$this->response['fields']['usermail'] = 'empty';
        } elseif ( ! is_email( $this->post['usermail'] ) ) {
        	$this->response['status'] = 'failed';
    		$this->response['fields']['usermail'] = 'invalid';
        }
	}

	// ! Try to register new user
	protected function register_user_process(){
        $userdata = array(
	        'user_pass'    => $this->post['password1'],
	        'user_login'   => $this->post['username'],
	        'user_email'   => $this->post['usermail'],
	        'display_name' => $this->post['username'],
	        'nickname'     => $this->post['username']
	    );

	    $user_id = wp_insert_user( $userdata );

	    if ( is_wp_error( $user_id ) ) {
	    	$this->response['status'] = 'failed';
    		$this->response['fields']['register'] = $user_id->get_error_message();
	    } else {
	    	$this->post['user_id'] = $user_id;
	    	$this->login_user();
	    	$this->send_mail();
	    }
	}

	// ! Login new user
	protected function login_user(){
		$signon_reset = array();
        $signon_reset['user_login'] = $this->post['username'];
        $signon_reset['user_password'] = $this->post['password1'];
        $signon_reset['remember'] = true;
        $signon = wp_signon( $signon_reset, false );

        if ( is_wp_error( $signon ) ){
        	$this->response['status'] = 'failed';
			$this->response['fields']['signon'] = 'invalid';
        } else {
        	$this->response['redirecturl'] = get_permalink( get_option( 'woocommerce_myaccount_page_id' ) );
        }
	}

	// ! Send mail
	protected function send_mail(){

		global $woocommerce;
        $logoimg = etheme_get_option('logo');
        $logoimg = apply_filters('etheme_logo_src',$logoimg['url']);
        ob_start(); ?>
            <div style="background-color: #f5f5f5;width: 100%;-webkit-text-size-adjust: none;margin: 0;padding: 70px 0 70px 0;">
                <div style="-webkit-box-shadow: 0 0 0 3px rgba(0,0,0,0.025) ;box-shadow: 0 0 0 3px rgba(0,0,0,0.025);-webkit-border-radius: 6px;border-radius: 6px ;background-color: #fdfdfd;border: 1px solid #dcdcdc; padding:20px; margin:0 auto; width:500px; max-width:100%; color: #737373; font-family:Arial; font-size:14px; line-height:150%; text-align:left;">
                    <?php if($logoimg): ?>
                        <a href="<?php echo home_url(); ?>" style="display:block; text-align:center;"><img style="max-width:100%;" src="<?php echo esc_url($logoimg); ?>" alt="<?php bloginfo( 'description' ); ?>" /></a>
                    <?php else: ?>
                        <a href="<?php echo home_url(); ?>" style="display:block; text-align:center;"><img style="max-width:100%;" src="<?php echo PARENT_URL.'/images/logo.png'; ?>" alt="<?php bloginfo('name'); ?>"></a>
                    <?php endif ; ?>
                    <br/>
                    <p><?php printf(__('Thanks for creating an account on %s. Your username is %s.', 'legenda'), get_bloginfo( 'name' ), '<b>' . $this->post['username'] . '</b>');?></p>
                    <?php if (class_exists('Woocommerce')): ?>

                        <p><?php printf(__('You can access your account area to view your orders and change your password here: <a href="%s" target="_blank">%s</a>.', 'legenda'), get_permalink( get_option('woocommerce_myaccount_page_id') ), get_permalink( get_option('woocommerce_myaccount_page_id') ));?></p>

                    <?php endif; ?>

                </div>
            </div>
        <?php
        $message = ob_get_contents();
        ob_end_clean();

	 	$from = get_bloginfo( 'name' );
        $from_email = get_bloginfo( 'admin_email' );
        $headers = 'From: '.$from . " <". $from_email .">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $subject = __("Registration successful", 'legenda');

        $send_reg = function_exists('et_mail') ? et_mail( $this->post['usermail'], $subject, $message, $headers) : '';

        if ( ! $send_reg ) {
        	// $this->response['status'] = 'success';
			$this->response['fields']['mail'] = 'Can not send mail';
        }
	}

	// ! Validade google recaptcha
	protected function check_captcha(){
		if ( $this->check_param( 'greresponse', false ) ) {
			$secret = etheme_get_option('google_captcha_secret');

			if ( !function_exists('etheme_fgcontent') ) {
				echo esc_html__('Activate Legenda Core plugin to use google captcha', 'legenda');
				return false;
			}
			// $verify = etheme_fgcontent( "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$this->post['greresponse']}" );
			$verify = etheme_fgcontent( "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$this->post['greresponse']}", true, null );
			$captcha_success = json_decode( $verify );

			if ( $captcha_success->success == true ) {
				return true;
			}
		}
		$this->response['status'] = 'failed';
		return false;
	}

	// ! Chack post params
	protected function check_param($param, $required = true){
		if ( isset( $this->post[$param] ) && ! empty( $this->post[$param] ) ){
			return true;
		} else {
			if ( $required ) {
				$this->response['fields'][] = $param;
				$this->response['status'] = 'failed';
			}
			return false;
		}
	}
}
new Etheme_Account_Register();