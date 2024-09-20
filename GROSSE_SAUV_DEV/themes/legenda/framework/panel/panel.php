<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );

new EthemeAdmin;

class EthemeAdmin{

	function __construct(){
		add_action( 'admin_menu', array( $this, 'et_add_menu_page' ) );
		add_action( 'wp_ajax_et_ajax_panel_popup', array($this, 'et_ajax_panel_popup') );
		add_action('admin_enqueue_scripts', array($this, 'etheme_load_panel_css'));

		$this->update_options();
	}

	public function etheme_load_panel_css(){
		wp_enqueue_style('etheme_panel_css', ETHEME_CODE_URL.'/panel/css/panel.css');
	}

	public function et_add_menu_page(){
		add_menu_page(
			esc_html__( 'Legenda', 'legenda' ),
			esc_html__( 'Legenda', 'legenda' ),
			'manage_options',
			'et-panel-welcome',
			array( $this, 'etheme_panel_page' ),
			ETHEME_CODE_IMAGES_URL . '/etheme.png',
			59
		);
		add_submenu_page(
			'et-panel-welcome',
			esc_html__( 'Dashboard', 'legenda' ),
			esc_html__( 'Dashboard', 'legenda' ),
			'manage_options',
			'et-panel-welcome',
			array( $this, 'etheme_panel_page' )
		);

		if ( ! etheme_is_activated() && ! class_exists( 'Redux' ) ) {
			add_submenu_page(
				'et-panel-welcome',
				esc_html__( 'Setup Wizard', 'legenda' ),
				esc_html__( 'Setup Wizard', 'legenda' ),
				'manage_options',
				admin_url( 'themes.php?page=legenda-setup' ),
				''
			);
		} elseif( ! etheme_is_activated() ){

		} elseif( ! class_exists( 'Redux' ) ){
			add_submenu_page(
				'et-panel-welcome',
				esc_html__( 'Install Plugins', 'legenda' ),
				esc_html__( 'Install Plugins', 'legenda' ),
				'manage_options',
				admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ),
				''
			);
		} else {
			add_submenu_page(
				'et-panel-welcome',
				esc_html__( 'Install Plugins', 'legenda' ),
				esc_html__( 'Install Plugins', 'legenda' ),
				'manage_options',
				admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ),
				''
			);
		}

		add_submenu_page(
			'et-panel-welcome',
			'Theme Options',
			'Theme Options',
			'manage_options',
			admin_url( 'themes.php?page=LegendaThemeOptions' ),
			''
		);
	}

	public function etheme_panel_page(){

		$out = get_template_part( 'framework/panel/templates/header' );

		$out .= get_template_part( 'framework/panel/templates/navigation' );

		$out .= '<div class="et-row etheme-page-content">';

		if( $_GET['page'] == 'et-panel-welcome' ){
			$out .= get_template_part( 'framework/panel/templates/page', 'welcome' );
		} elseif( $_GET['page']  == 'et-panel-options' ){
			// show nothing jast redirect
		} elseif( $_GET['page'] == 'et-panel-plugins' ){
			// show nothing jast redirect
		} else {
			$out .= get_template_part( 'framework/panel/templates/page', 'welcome' );
		}

		$out .= '</div>';

		$out .= get_template_part( 'framework/panel/templates/footer' );

		echo wp_specialchars_decode($out);
	}

	public function et_ajax_panel_popup(){
		$response = array();

		if ( isset( $_POST['type'] ) && $_POST['type'] == 'instagram' ) {
			ob_start();
			get_template_part( 'framework/panel/templates/popup-instagram', 'content' );
			$response['content'] = ob_get_clean();
		}
		wp_send_json($response);
	}

	public function update_options(){
		if (!isset($_POST['etheme-options-save']) ) return;
		if (isset($_POST['old_widgets_panel_type'])){
			update_option( 'old_widgets_panel_type', 1 );
		} else {
			update_option( 'old_widgets_panel_type', 0 );
		}
	}
}