<?php
// **********************************************************************//
// ! Etheme Import Class
// **********************************************************************//

if ( legenda_theme_old() ) return;

if ( !class_exists('Etheme_Import') ) {

	class Etheme_Import{
		var $args = array();
		var $response = '';

		function __construct(){
			add_action( 'wp_ajax_etheme_import_ajax', array( $this, 'etheme_import_data' ) );
		}

		public function etheme_import_data(){
			defined( 'ETHEME_DOMAIN' ) || exit;
			$this->check();
			$this->args = $_POST;

			if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
				define( 'WP_LOAD_IMPORTERS', true );
			}

			if ( ! class_exists( 'WP_Import' ) ){
				require_once( 'wordpress-importer/wordpress-importer.php' );
			}

			// Load Importer API
			require_once ABSPATH . 'wp-admin/includes/import.php';
			$demo_data_installed = get_option( 'demo_data_installed' );
					
			// check if wp_importer, the base importer class is available, otherwise include it
			if ( ! class_exists( 'WP_Importer' ) ) {
				$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
				if ( file_exists( $class_wp_importer ) ) {
					require_once( $class_wp_importer );
				} else  {
					$this->error( esc_html__( 'The Auto importing script could not be loaded. Please use the wordpress importer and import the XML file that is located in your themes folder manually.', 'legenda-core' ) );
				}
			}

			if( class_exists( 'WP_Importer' ) ){
				try{

					$this->args['folder'] = 'http://8theme.com/import/' . ETHEME_DOMAIN . '_versions/' . $this->args['version'];
					if ( $this->args['version'] != 'ecommerce' ) {
						$this->create_dir();
						$this->create_file();
						$file = PARENT_DIR . '/framework/tmp/version_data.xml';
					} elseif ( $demo_data_installed != 'yes' && $this->args['version'] == 'ecommerce' ) {
						$file = get_template_directory() . '/framework/dummy/Dummy.xml';
					}
					
					$this->import_slider();
					$this->import_widgets();
					$this->import_options();

					// ! Show the response of import_slider, import_options functions
					echo $this->response;

					if ( isset( $file ) && ! empty( $file ) ) {
						$importer = new WP_Import();
						$importer->fetch_attachments = true;
						$importer->import( $file );
					}
					$this->update_pages();
					$this->update_menus();

					$versions_imported = get_option('versions_imported', array());

					if ( !in_array($this->args['version'], $versions_imported)) {

						$versions_imported[] = $this->args['version'];
						update_option('versions_imported', $versions_imported);

					}

					$version_now = get_option('version_now', '');
					update_option('version_now', $this->args['version']);

				} catch ( Exception $e ) {
					$this->error( esc_html__( "Error while importing", 'legenda-core' ) );
				}
				die();
			}
		}

		public function check(){
			if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'et_nonce' ) ) {
				$this->error( esc_html__( '"nonce" check was failed', 'legenda-core' ) );
			}

			foreach ( $_POST as $key => $value ) {
				if (  ! in_array( $key,  array( 'action', 'version', 'id', 'nonce' ) ) ) {
					$this->error( 'wrong actions' );
				} elseif( ! $value || $value == 'false' ){
					$this->error( $key . esc_html__( ' was not set!', 'legenda-core' ) );
				}
			}

			if ( ! function_exists( 'file_put_contents' ) && function_exists( 'wp_remote_get' ) ) {
				$this->error( 'file_put_contents and wp_remote_get' . esc_html__( ' functions must be activated', 'legenda-core' ) );
			} elseif ( ! function_exists( 'file_put_contents' ) ) {
				$this->error( 'file_put_contents' . esc_html__( ' function must be activated', 'legenda-core' ) );
			} elseif ( ! function_exists( 'wp_remote_get' ) ) {
				$this->error( 'wp_remote_get' . esc_html__( ' function must be activated', 'legenda-core' ) );
			}
		}

		public function create_dir(){
			$folder = PARENT_DIR . '/framework/tmp';
			if ( ! file_exists( $folder ) ) {
				if ( ! wp_mkdir_p( $folder ) ) $this->error( 'can not craete ' . '"' . $folder . '" - folder' );
				if ( ! is_writable( PARENT_DIR . '/framework/tmp' ) ) $this->error( '"' . $folder . '" - folder must be writable"' );
			}
		}

		public  function create_file(){
			$xml = $this->args['folder'] .'/version_data.xml';
			$content = $this->et_get_remote_content( $xml );

			if( $content ) {
				$tmpxml = PARENT_DIR . '/framework/tmp/version_data.xml';
				file_put_contents( $tmpxml, $content );
				if ( false === file_put_contents( $tmpxml, $content ) ) {
					$this->error( $tmpxml . esc_html__( 'file is not writable', 'legenda-core' ) );
				}
			}
		}

		public function et_get_remote_content($url){
			$response = wp_remote_get( $url );
			if( is_array( $response ) && isset( $response['headers'] ) && isset( $response['headers']['content-length'] ) && $response['headers']['content-length'] > 1000  ){
				return $response['body'];
			}
			return false;
		}

		public function import_slider(){
			if ( ! class_exists( 'RevSlider' ) ) {
				$this->response .= esc_html__( 'Please install or activate Revolution slider plugin', 'legenda-core' ) . '<br>';
				return;
			}

			$sliders = array();

			if ( $this->args['version'] == 'ecommerce' ) {
				$sliders['ecommerce'] = true;
			} else {
				$sliders[] = $this->et_get_remote_content( $this->args['folder'] . '/slider.zip' );
				$sliders[] = $this->et_get_remote_content( $this->args['folder'] . '/slider2.zip' );
			}

			foreach ( $sliders as $key => $value ) {
				if ( $value ) {
					if ( $this->args['version'] == 'ecommerce' ) {
						$tmpZip = PARENT_DIR . '/framework/dummy/tempSliderZip.zip';
					} else {
						$tmpZip = PARENT_DIR . '/framework/tmp/tempSliderZip.zip';
						file_put_contents( $tmpZip, $value );
					}

					$revapi = new RevSlider();

					ob_start();

					$slider_result = $revapi->importSliderFromPost( true, true, $tmpZip );

					ob_end_clean();

					if ( $slider_result['success'] ) {
						$this->response .= esc_html__( 'Revolution slider installed successfully!', 'legenda-core' ) . '<br>';
					} else {
						$this->response .= esc_html__( 'Revolution slider was not installed!', 'legenda-core' ) . '<br>';
					}
				}
			}
		}

		public function import_options() {

			if(!class_exists('ReduxFrameworkInstances')) return;

			global $legenda_redux_demo;

			// $options_file = $this->args['folder'] . '/options.txt'; // for option tree settings
			$options_file = $this->args['folder'] . '/options.json';

			$new_options = wp_remote_get($options_file);

			// $default_options = require apply_filters('etheme_file_url', ETHEME_THEME . 'default-options.php');

			if( ! is_wp_error( $new_options )) {

				$new_options = json_decode($new_options['body'], true);

				// $new_options = wp_parse_args( $new_options, $default_options );

				$new_options = wp_parse_args( $new_options, $legenda_redux_demo );

				$redux = ReduxFrameworkInstances::get_instance( 'legenda_redux_demo' );

				if ( isset ( $redux->validation_ran ) ) {
					unset ( $redux->validation_ran );
				}

				$redux->set_options( $new_options );
			}
		}

		public function import_widgets(){
			$widgets = require apply_filters('et_file_url', PARENT_DIR . '/framework/widgets-import.php');
			$active_widgets = get_option( 'sidebars_widgets' );
			$widgets_counter = 1;
			$version = $this->args['version'];

			if (!isset($widgets[$version]) || !count($widgets[$version]) || is_null($widgets[$version])){
				return;
			}

			foreach ( $widgets[$version] as $area => $params ) {

				if ( ! empty( $active_widgets[$area] ) && $params['flush'] ) {
					unset( $active_widgets[ $area ] );
				}

				foreach  ($params['widgets'] as $widget => $args ) {
					$active_widgets[ $area ][] = $widget . '-' . $widgets_counter;
					$widget_content = get_option( 'widget_' . $widget );
					$widget_content[ $widgets_counter ] = $args;
					update_option(  'widget_' . $widget, $widget_content );
					$widgets_counter ++;
				}
			}
			update_option( 'sidebars_widgets', $active_widgets );
		}

		public function update_pages(){

			if ( $this->args['version'] == 'ecommerce' ) {

				$home_id = $this->get_page_by_title('Home Page');
				$blog_id = $this->get_page_by_title('Blog');
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $home_id->ID );
				update_option( 'page_for_posts', $blog_id->ID );
				add_option( 'demo_data_installed', 'yes' );

			} else {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $this->args['id'] );
			}		
		}

		public function update_menus(){
			global $wpdb;

		    $menuname = 'Main Menu';
			$bpmenulocation = 'main-menu';
			$mobilemenulocation = 'mobile-menu';
			
			$tablename = $wpdb->prefix.'terms';
			$menu_ids = $wpdb->get_results(
			    "
			    SELECT term_id
			    FROM ".$tablename." 
			    WHERE name= '".$menuname."'
			    "
			);

			$menu_id = false;
			foreach ( $menu_ids as $menu ) {
				$menu_id = $menu->term_id;
			}

		    if( ! has_nav_menu( $bpmenulocation ) ){
		        $locations = get_theme_mod('nav_menu_locations');
				    if (!is_array($locations)){
					    $locations = array();
				    }
		        	if ( $menu_id ) {
						$locations[$bpmenulocation] = absint($menu_id);
						$locations[$mobilemenulocation] = absint($menu_id);
		        	}
		        set_theme_mod( 'nav_menu_locations', $locations );
		    }
		}

		public function error( $msg = false ){
			die( '<span class="et_error">' . $msg . '</span><br>' );
		}

		/**
		 * Get page object by page title
		 *
		 * @since   1.4.1
		 * @version 1.0.0
		 *
		 *
		 * @return object|null post object if post find or null if it not
		 */
		public function get_page_by_title($page_title){
			global $wpdb;

			$sql = $wpdb->prepare(
				"
			SELECT ID
			FROM $wpdb->posts
			WHERE post_title = %s
			AND post_type = 'page'
		",
				$page_title,
			);

			$page = $wpdb->get_var( $sql );

			if ( $page ) {
				return get_post( $page, OBJECT );
			}

			return null;
		}
	}
	new Etheme_Import;
}