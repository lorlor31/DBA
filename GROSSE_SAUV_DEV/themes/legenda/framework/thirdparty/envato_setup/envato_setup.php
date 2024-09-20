<?php
/**
 * Envato Theme Setup Wizard Class
 *
 * Takes new users through some basic steps to setup their ThemeForest theme.
 *
 * @author      dtbaker
 * @author      vburlak
 * @package     envato_wizard
 * @version     1.2.4
 *
 *
 * 1.2.0 - added custom_logo
 * 1.2.1 - ignore post revisioins
 * 1.2.2 - elementor widget data replace on import
 * 1.2.3 - auto export of content.
 * 1.2.4 - fix category menu links
 *
 * Based off the WooThemes installer.
 *
 *
 *
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Envato_Theme_Setup_Wizard' ) ) {
	/**
	 * Envato_Theme_Setup_Wizard class
	 */
	class Envato_Theme_Setup_Wizard {

		/**
		 * The class version number.
		 *
		 * @since 1.1.1
		 * @access private
		 *
		 * @var string
		 */
		protected $version = '1.2.4';

		/** @var string Current theme name, used as namespace in actions. */
		protected $theme_name = '';

		/** @var string Current Step */
		protected $step = '';

		/** @var array Steps for the setup wizard */
		protected $steps = array();

		/**
		 * Relative plugin path
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_path = '';

		/**
		 * Relative plugin url for this plugin folder, used when enquing scripts
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $plugin_url = '';

		/**
		 * The slug name to refer to this menu
		 *
		 * @since 1.1.1
		 *
		 * @var string
		 */
		protected $page_slug;

		/**
		 * TGMPA instance storage
		 *
		 * @var object
		 */
		protected $tgmpa_instance;

		/**
		 * TGMPA Menu slug
		 *
		 * @var string
		 */
		protected $tgmpa_menu_slug = 'tgmpa-install-plugins';

		/**
		 * TGMPA Menu url
		 *
		 * @var string
		 */
		protected $tgmpa_url = 'themes.php?page=tgmpa-install-plugins';

		/**
		 * The slug name for the parent menu
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_parent;

		/**
		 * Complete URL to Setup Wizard
		 *
		 * @since 1.1.2
		 *
		 * @var string
		 */
		protected $page_url;

		protected $versions;


		/**
		 * Holds the current instance of the theme manager
		 *
		 * @since 1.1.3
		 * @var Envato_Theme_Setup_Wizard
		 */
		private static $instance = null;

		public $api_url;

		/**
		 * @since 1.1.3
		 *
		 * @return Envato_Theme_Setup_Wizard
		 */
		public static function get_instance() {
			if ( ! self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.1
		 * @access private
		 */
		public function __construct() {
			$this->init_globals();
			$this->init_actions();
		}

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.7
		 * @access public
		 */
		public function get_default_theme_style() {
			return 'pink';
		}

		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_header_logo_width() {
			return '250px';
		}


		/**
		 * Get the default style. Can be overriden by theme init scripts.
		 *
		 * @see Envato_Theme_Setup_Wizard::instance()
		 *
		 * @since 1.1.9
		 * @access public
		 */
		public function get_logo_image() {
			return PARENT_URL.'/images/logo.png';
		}

		/**
		 * Setup the class globals.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_globals() {
			$current_theme         = wp_get_theme();
			$this->theme_name      = strtolower( preg_replace( '#[^a-zA-Z]#', '', $current_theme->get( 'Name' ) ) );
			$this->page_slug       = apply_filters( $this->theme_name . '_theme_setup_wizard_page_slug', 'etheme-setup' );
			$this->parent_slug     = apply_filters( $this->theme_name . '_theme_setup_wizard_parent_slug', '' );

            $this->versions = et_get_versions_option();

			//If we have parent slug - set correct url
			if ( $this->parent_slug !== '' ) {
				$this->page_url = 'admin.php?page=' . $this->page_slug;
			} else {
				$this->page_url = 'themes.php?page=' . $this->page_slug;
			}
			$this->page_url = apply_filters( $this->theme_name . '_theme_setup_wizard_page_url', $this->page_url );

			$this->api_url = ETHEME_API;
			//set relative plugin path url
			$this->plugin_path = trailingslashit( $this->cleanFilePath( dirname( __FILE__ ) ) );
			$relative_url      = str_replace( $this->cleanFilePath( get_template_directory() ), '', $this->plugin_path );
			$this->plugin_url  = trailingslashit( get_template_directory_uri() . $relative_url );
		}

		/**
		 * Setup the hooks, actions and filters.
		 *
		 * @uses add_action() To add actions.
		 * @uses add_filter() To add filters.
		 *
		 * @since 1.1.1
		 * @access public
		 */
		public function init_actions() {
			if ( apply_filters( $this->theme_name . '_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {

				if(!is_child_theme()){
					add_action( 'after_switch_theme', array( $this, 'switch_theme' ) );
				}

				if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
					add_action( 'init', array( $this, 'get_tgmpa_instanse' ), 30 );
					add_action( 'init', array( $this, 'set_tgmpa_url' ), 40 );
				}

				add_action( 'admin_menu', array( $this, 'admin_menus' ) );
				add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'admin_init', array( $this, 'admin_redirects' ), 30 );
				add_action( 'admin_init', array( $this, 'init_wizard_steps' ), 30 );
				add_action( 'admin_init', array( $this, 'setup_wizard' ), 30 );
				add_filter( 'tgmpa_load', array( $this, 'tgmpa_load' ), 10, 1 );
				add_action( 'wp_ajax_envato_setup_plugins', array( $this, 'ajax_plugins' ) );
			}
			#add_action( 'upgrader_post_install', array( $this, 'upgrader_post_install' ), 10, 2 );
		}

		/**
		 * After a theme update we clear the setup_complete option. This prompts the user to visit the update page again.
		 *
		 * @since 1.1.8
		 * @access public
		 */
		public function upgrader_post_install( $return, $theme ) {
			if ( is_wp_error( $return ) ) {
				return $return;
			}
			if ( $theme != get_stylesheet() ) {
				return $return;
			}
			update_option( 'envato_setup_complete', false );

			return $return;
		}


		public function enqueue_scripts() {
		}

		public function tgmpa_load( $status ) {
			return is_admin() || current_user_can( 'install_themes' );
		}

		public function switch_theme() {
			set_transient( '_' . $this->theme_name . '_activation_redirect', 1 );
		}

		public function admin_redirects() {
			ob_start();
			if ( ! get_transient( '_' . $this->theme_name . '_activation_redirect' ) || get_option( 'envato_setup_complete', false ) ) {
				return;
			}
			delete_transient( '_' . $this->theme_name . '_activation_redirect' );
			wp_safe_redirect( admin_url( $this->page_url ) );
			exit;
		}

		/**
		 * Get configured TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function get_tgmpa_instanse() {
			$this->tgmpa_instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
		}

		/**
		 * Update $tgmpa_menu_slug and $tgmpa_parent_slug from TGMPA instance
		 *
		 * @access public
		 * @since 1.1.2
		 */
		public function set_tgmpa_url() {

			$this->tgmpa_menu_slug = ( property_exists( $this->tgmpa_instance, 'menu' ) ) ? $this->tgmpa_instance->menu : $this->tgmpa_menu_slug;
			$this->tgmpa_menu_slug = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_menu_slug', $this->tgmpa_menu_slug );

			$tgmpa_parent_slug = ( property_exists( $this->tgmpa_instance, 'parent_slug' ) && $this->tgmpa_instance->parent_slug !== 'themes.php' ) ? 'admin.php' : 'themes.php';

			$this->tgmpa_url = apply_filters( $this->theme_name . '_theme_setup_wizard_tgmpa_url', $tgmpa_parent_slug . '?page=' . $this->tgmpa_menu_slug );

		}

		/**
		 * Add admin menus/screens.
		 */
		public function admin_menus() {

			if ( $this->is_submenu_page() ) {
				//prevent Theme Check warning about "themes should use add_theme_page for adding admin pages"
				$add_subpage_function = 'add_submenu' . '_page';
				$add_subpage_function( $this->parent_slug, esc_html__( 'Setup Wizard', 'legenda' ), esc_html__( 'Setup Wizard', 'legenda' ), 'manage_options', $this->page_slug, array(
					$this,
					'setup_wizard',
				) );
			} else {
				add_theme_page( esc_html__( 'Setup Wizard', 'legenda' ), esc_html__( 'Setup Wizard', 'legenda' ), 'manage_options', $this->page_slug, array(
					$this,
					'setup_wizard',
				) );
			}

		}


		/**
		 * Setup steps.
		 *
		 * @since 1.1.1
		 * @access public
		 * @return array
		 */
		public function init_wizard_steps() {



			$this->steps = array(
				'introduction' => array(
					'name'    => esc_html__( 'Introduction', 'legenda' ),
					'view'    => array( $this, 'envato_setup_introduction' ),
					'handler' => array( $this, 'envato_setup_introduction_save' ),
				),
			);

			$this->steps['updates'] = array(
				'name'    => __( 'Activate', 'legenda' ),
				'view'    => array( $this, 'envato_setup_updates' ),
				'handler' => array( $this, 'envato_setup_updates_save' ),
			);

			$this->steps['customize'] = array(
				'name'    => __( 'Child Theme', 'legenda' ),
				'view'    => array( $this, 'envato_setup_customize' ),
				'handler' => '',
			);

			$this->steps['default_content'] = array(
				'name'    => esc_html__( 'Content', 'legenda' ),
				'view'    => array( $this, 'envato_setup_default_content' ),
				'handler' => '',
			);
			if ( class_exists( 'TGM_Plugin_Activation' ) && isset( $GLOBALS['tgmpa'] ) ) {
				$this->steps['default_plugins'] = array(
					'name'    => esc_html__( 'Plugins', 'legenda' ),
					'view'    => array( $this, 'envato_setup_default_plugins' ),
					'handler' => '',
				);
			}

			// $this->steps['design']          = array(
			// 	'name'    => esc_html__( 'Logo & Design' ),
			// 	'view'    => array( $this, 'envato_setup_logo_design' ),
			// 	'handler' => array( $this, 'envato_setup_logo_design_save' ),
			// );
			$this->steps['help_support']    = array(
				'name'    => esc_html__( 'Support', 'legenda' ),
				'view'    => array( $this, 'envato_setup_help_support' ),
				'handler' => '',
			);
			$this->steps['next_steps']      = array(
				'name'    => esc_html__( 'Ready!', 'legenda' ),
				'view'    => array( $this, 'envato_setup_ready' ),
				'handler' => '',
			);

			$this->steps = apply_filters( $this->theme_name . '_theme_setup_wizard_steps', $this->steps );

		}

		/**
		 * Show the setup wizard
		 */
		public function setup_wizard() {
			if ( empty( $_GET['page'] ) || $this->page_slug !== $_GET['page'] ) {
				return;
			}
			ob_end_clean();

			$this->step = isset( $_GET['step'] ) ? sanitize_key( $_GET['step'] ) : current( array_keys( $this->steps ) );

			wp_register_script( 'jquery-blockui', $this->plugin_url . 'js/jquery.blockUI.js', array( 'jquery' ), '2.70', true );
			wp_register_script( 'envato-setup', $this->plugin_url . 'js/envato-setup.js', array(
				'jquery',
				'jquery-blockui',
			), $this->version );
			wp_localize_script( 'envato-setup', 'envato_setup_params', array(
				'tgm_plugin_nonce' => array(
					'update'  => wp_create_nonce( 'tgmpa-update' ),
					'install' => wp_create_nonce( 'tgmpa-install' ),
				),
				'tgm_bulk_url'     => admin_url( $this->tgmpa_url ),
				'ajaxurl'          => admin_url( 'admin-ajax.php' ),
				'wpnonce'          => wp_create_nonce( 'envato_setup_nonce' ),
				'verify_text'      => esc_html__( '...verifying', 'legenda' ),
			) );

			//wp_enqueue_style( 'envato_wizard_admin_styles', $this->plugin_url . '/css/admin.css', array(), $this->version );
			wp_enqueue_style( 'envato-setup', $this->plugin_url . 'css/envato-setup.css', array(
				'wp-admin',
				'dashicons',
				'install',
			), $this->version );

			//enqueue style for admin notices
			wp_enqueue_style( 'wp-admin' );

			wp_enqueue_media();
			wp_enqueue_script( 'media' );

			// fix for users who are using gutenberg editor plugin
			if ( function_exists('gutenberg_block_editor_admin_print_styles') ) {
				remove_action( 'admin_print_styles', 'gutenberg_block_editor_admin_print_styles' );
				remove_action( 'admin_print_scripts', 'gutenberg_block_editor_admin_print_scripts' );
				remove_action( 'admin_print_footer_scripts', 'gutenberg_block_editor_admin_print_footer_scripts' );
				remove_action( 'admin_footer', 'gutenberg_block_editor_admin_footer' );
			}

			ob_start();
			$this->setup_wizard_header();
			$this->setup_wizard_steps();
			$show_content = true;
			echo '<div class="envato-setup-content">';
			if ( ! empty( $_REQUEST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
				$show_content = call_user_func( $this->steps[ $this->step ]['handler'] );
			}
			if ( $show_content ) {
				$this->setup_wizard_content();
			}
			echo '</div>';
			$this->setup_wizard_footer();
			exit;
		}

		public function get_step_link( $step ) {
			return add_query_arg( 'step', $step, admin_url( 'admin.php?page=' . $this->page_slug ) );
		}

		public function get_next_step_link() {
			$keys = array_keys( $this->steps );

			return add_query_arg( 'step', $keys[ array_search( $this->step, array_keys( $this->steps ) ) + 1 ], remove_query_arg( 'translation_updated' ) );
		}

		/**
		 * Setup Wizard Header
		 */
		public function setup_wizard_header() {
			?>
			<!DOCTYPE html>
			<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
			<head>
				<meta name="viewport" content="width=device-width"/>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
				<?php
				// remove it to prevent Ultimate_VC_Addons 'base' error
				remove_action( 'admin_print_scripts', 'ultimate_tabs_admin', 999 );
				// remove it to prevent WooCommerce get_current_page error
				remove_action( 'admin_head', array( 'Automattic\WooCommerce\Admin\Loader', 'remove_notices' ) );
				remove_action( 'admin_head', array( 'Automattic\WooCommerce\Admin\Loader', 'smart_app_banner' ) );
				// avoid theme check issues.
				echo '<t';
				echo 'itle>' . esc_html__( 'Theme &rsaquo; Setup Wizard', 'legenda' ) . '</ti' . 'tle>'; ?>
				<?php wp_print_scripts( 'envato-setup' ); ?>
				<?php do_action( 'admin_print_styles' ); ?>
				<?php do_action( 'admin_print_scripts' ); ?>
				<?php do_action( 'admin_head' ); ?>
			</head>
			<body class="envato-setup wp-core-ui">
			<?php //etheme_support_chat(); ?>
			<h1 id="wc-logo">
			<?php
					$image_url = $this->get_logo_image();
					if ( $image_url ) {
						$image = '<img class="site-logo" src="%s" alt="%s" style="width:%s; height:auto" />';
						printf(
							$image,
							$image_url,
							get_bloginfo( 'name' ),
							$this->get_header_logo_width()
						);
					} else { ?>
						<img src="<?php echo esc_url($this->plugin_url); ?>images/logo.png" alt="<?php esc_attr_e('Envato install wizard', 'legenda'); ?>" /><?php
					} ?>
			</h1>
			<?php
			}

			/**
			 * Setup Wizard Footer
			 */
			public function setup_wizard_footer() {
			?>
			<?php if ( 'next_steps' === $this->step ) : ?>
				<a class="wc-return-to-dashboard"
				   href="<?php echo esc_url( admin_url() ); ?>"><?php esc_html_e( 'Return to the WordPress Dashboard', 'legenda' ); ?></a>
			<?php endif; ?>
			<p class="copyrights">© Created by <a href="https://www.8theme.com/" target="_blank">8theme</a> - Power Elite ThemeForest Author.</p>
			</body>
			<?php
			@do_action( 'admin_footer' ); // this was spitting out some errors in some admin templates. quick @ fix until I have time to find out what's causing errors.
			do_action( 'admin_print_footer_scripts' );
			?>
			</html>
			<?php
		}

		/**
		 * Output the steps
		 */
		public function setup_wizard_steps() {
			$ouput_steps = $this->steps;
			array_shift( $ouput_steps );
			?>
			<ol class="envato-setup-steps">
				<?php foreach ( $ouput_steps as $step_key => $step ) : ?>
					<li class="<?php
					$show_link = false;
					if ( $step_key === $this->step ) {
						echo 'active';
					} elseif ( array_search( $this->step, array_keys( $this->steps ) ) > array_search( $step_key, array_keys( $this->steps ) ) ) {
						echo 'done';
						$show_link = true;
					}
					?>"><?php
						if ( $show_link ) {
							?>
							<a href="<?php echo esc_url( $this->get_step_link( $step_key ) ); ?>"><?php echo esc_html( $step['name'] ); ?></a>
							<?php
						} else {
							echo esc_html( $step['name'] );
						}
						?></li>
				<?php endforeach; ?>
			</ol>
			<?php
		}

		/**
		 * Output the content for the current step
		 */
		public function setup_wizard_content() {
			isset( $this->steps[ $this->step ] ) ? call_user_func( $this->steps[ $this->step ]['view'] ) : false;
		}

		/**
		 * Introduction step
		 */
		public function envato_setup_introduction() {

			if ( false && isset( $_REQUEST['debug'] ) ) {
				echo '<pre>';
				// debug inserting a particular post so we can see what's going on
				$post_type = 'nav_menu_item';
				$post_id   = 239; // debug this particular import post id.
				$all_data  = $this->_get_json( 'default.json' );
				if ( ! $post_type || ! isset( $all_data[ $post_type ] ) ) {
					echo "Post type $post_type not found.";
				} else {
					echo "Looking for post id $post_id \n";
					foreach ( $all_data[ $post_type ] as $post_data ) {

						if ( $post_data['post_id'] == $post_id ) {
							print_r( $post_data );
						}
					}
				}
				print_r( $this->logs );

				echo '</pre>';
			} else if ( isset( $_REQUEST['export'] ) ) {

				// @include('envato-setup-export.php');

			} else if ( get_option( 'envato_setup_complete', false ) ) {
				?>
				<img src="<?php echo ETHEME_CODE_IMAGES_URL; ?>/big-eight.png" alt="eight theme" class="elogo">
				<h1><?php printf( esc_html__( 'Welcome to the setup wizard for %s.', 'legenda' ), wp_get_theme() ); ?></h1>
				<p><?php esc_html_e( 'It looks like you have already run the setup wizard. Below are some options: ', 'legenda' ); ?></p>
				<ul>
					<li>
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="button-primary button button-next button-large"><?php esc_html_e( 'Run Setup Wizard Again', 'legenda' ); ?></a>
					</li>
			<!-- 		<li>
						<form method="post">
							<input type="hidden" name="reset-font-defaults" value="yes">
							<input type="submit" class="button-primary button button-large button-next"
							       value="<?php esc_attr_e( 'Reset font style and colors', 'legenda' ); ?>" name="save_step"/>
							<?php wp_nonce_field( 'envato-setup' ); ?>
						</form>
					</li> -->
				</ul>
				<p style="border:2px solid #ff4242; color:#c33434; padding:10px;">Important! If you need to update Legenda theme, just Cancel the setup wizard.

Go to Theme Options and Activate theme with your Purchase code.</p>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="button button-large"><?php esc_html_e( 'Cancel', 'legenda' ); ?></a>
				</p>
				<?php
			} else {
				?>

				<img src="<?php echo ETHEME_CODE_IMAGES_URL; ?>/big-eight.png" alt="eight theme" class="elogo">
				<h1><?php printf( esc_html__( 'Welcome to Legenda Setup Wizard', 'legenda' ), wp_get_theme() ); ?></h1>
				<p><?php printf( esc_html__( 'Thank you for choosing our Legenda template.', 'legenda' ), wp_get_theme() ); ?></p>
				<p><?php printf( esc_html__( 'This setup wizard will help you to refresh and configure your website with a new layout. You will have Child Theme, Content, and Plugins installed in 5-10 minutes (depending on your server configuration). ', 'legenda' ), wp_get_theme() ); ?></p>
				<p><?php esc_html_e( 'No time right now? If you do not want to go through the wizard, you can skip, and get back to WordPress dashboard. Come back any time to continue!', 'legenda' ); ?></p>
				<p style="border:2px solid #ff4242; color:#c33434; padding:10px;">Important! If you need to update Legenda theme, just Cancel the setup wizard.

Go to Theme Options and Activate theme with your Purchase code.</p>
				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="et-button button-active button-next"><?php esc_html_e( 'Let\'s Go!', 'legenda' ); ?></a>
					<a href="<?php echo esc_url( wp_get_referer() && ! strpos( wp_get_referer(), 'update.php' ) ? wp_get_referer() : admin_url( '' ) ); ?>"
					   class="et-button"><?php esc_html_e( 'Not right now', 'legenda' ); ?></a>
				</p>
				<?php
			}
		}

		public function filter_options( $options ) {
			return $options;
		}

		/**
		 *
		 * Handles save button from welcome page. This is to perform tasks when the setup wizard has already been run. E.g. reset defaults
		 *
		 * @since 1.2.5
		 */
		public function envato_setup_introduction_save() {

			check_admin_referer( 'envato-setup' );

			if ( ! empty( $_POST['reset-font-defaults'] ) && $_POST['reset-font-defaults'] == 'yes' ) {

				// clear font options
				update_option( 'tt_font_theme_options', array() );

				// reset site color
				remove_theme_mod( 'dtbwp_site_color' );

				if ( class_exists( 'dtbwp_customize_save_hook' ) ) {
					$site_color_defaults = new dtbwp_customize_save_hook();
					$site_color_defaults->save_color_options();
				}

				$file_name = get_template_directory() . '/style.custom.css';
				if ( file_exists( $file_name ) ) {
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					WP_Filesystem();
					global $wp_filesystem;
					$wp_filesystem->put_contents( $file_name, '' );
				}
				?>
				<p>
					<strong><?php esc_html_e( 'Options have been reset. Please go to Appearance > Customize in the WordPress backend.', 'legenda' ); ?></strong>
				</p>
				<?php
				return true;
			}

			return false;
		}

		private function _get_plugins( $version = false ) {
			$instance = call_user_func( array( get_class( $GLOBALS['tgmpa'] ), 'get_instance' ) );
			$plugins  = array(
				'all'      => array(), // Meaning: all plugins which still have open actions.
				'install'  => array(),
				'update'   => array(),
				'activate' => array(),
			);

			foreach ( $instance->plugins as $slug => $plugin ) {

				$new_is_plugin_active = (
					( ! empty( $instance->plugins[ $slug ]['is_callable'] ) && is_callable( $instance->plugins[ $slug ]['is_callable'] ) )
					|| in_array( $instance->plugins[ $slug ]['file_path'], (array) get_option( 'active_plugins', array() ) ) || is_plugin_active_for_network( $instance->plugins[ $slug ]['file_path'] )
				);
				
				if ( $new_is_plugin_active && false === $instance->does_plugin_have_update( $slug ) ) {
					// No need to display plugins if they are installed, up-to-date and active.
					continue;
				} else {
					$plugins['all'][ $slug ] = $plugin;

					if ( ! $instance->is_plugin_installed( $slug ) ) {
						$plugins['install'][ $slug ] = $plugin;
					} else {
						if ( false !== $instance->does_plugin_have_update( $slug ) ) {
							$plugins['update'][ $slug ] = $plugin;
						}

						if ( $instance->can_plugin_activate( $slug ) ) {
							$plugins['activate'][ $slug ] = $plugin;
						}
					}
				}
			}

			return $plugins;
		}

		/**
		 * Page setup
		 */
		public function envato_setup_default_plugins() {

			tgmpa_load_bulk_installer();
			// install plugins with TGM.
			if ( ! class_exists( 'TGM_Plugin_Activation' ) || ! isset( $GLOBALS['tgmpa'] ) ) {
				die( 'Failed to find TGM' );
			}
			$url     = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'envato-setup' );
			$plugins = $this->_get_plugins();

			// copied from TGM

			$method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
			$fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.

			if ( false === ( $creds = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields ) ) ) {
				return true; // Stop the normal page form from displaying, credential request form will be shown.
			}

			// Now we have some credentials, setup WP_Filesystem.
			if ( ! WP_Filesystem( $creds ) ) {
				// Our credentials were no good, ask the user for them again.
				request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );

				return true;
			}

			$version_import = false;
			$home_id = 0;

			if( isset( $_GET['version'] ) ) {
				$version_import = $_GET['version'];
				$version = (isset( $this->versions[$version_import] ) ) ? $this->versions[$version_import] : 5;
				$home_id = $version['home_id'];
			}


			/* If we arrive here, we have the filesystem */

			?>
			<h1><?php esc_html_e( 'Default Plugins', 'legenda' ); ?></h1>
			<form method="post" class="plugins-form" data-version="<?php echo esc_attr( $version_import ); ?>" data-nonce="<?php echo wp_create_nonce('et_nonce'); ?>" data-home_id="<?php echo esc_attr( $home_id ); ?>">

				<?php
				$plugins = $this->_get_plugins( $version_import );

				$required = array_filter($plugins['all'], function($el) {
					return $el['required'];
				});

				$version_plugins = ( ! empty( $this->versions[ $version_import ]['plugins'] ) ) ? $this->versions[ $version_import ]['plugins'] : array();

				$for_version = array_filter($plugins['all'], function($el) use($version_plugins) {
					return in_array( $el['slug'], array_merge($version_plugins) );
				});

				$recommended = array_filter($plugins['all'], function($el) use( $for_version ) {
					return ( ! $el['required'] && ! isset( $for_version[ $el['slug'] ] ) );
				});

				if ( count( $plugins['all'] ) ) {
					?>
					<p><?php esc_html_e( 'Your website requires some additional plugins.  The following plugins will be installed:', 'legenda' ); ?></p>
					<ul class="envato-wizard-plugins">
						<li class="plugins-title">Required</li>
						<?php $this->_list_plugins( $required, $plugins ); ?>
						<?php if ( ! empty( $for_version ) ): ?>
							<li class="plugins-title">Needed for this version</li>
							<?php $this->_list_plugins( $for_version, $plugins, true ); ?>
						<?php endif ?>
						<li class="plugins-title">Additional (not required)</li>
						<?php $this->_list_plugins( $recommended, $plugins ); ?>
					</ul>
					<?php
				} else {
					echo '<p><strong>' . esc_html_e( 'Good news! All plugins are already installed and up to date. Please continue.', 'legenda' ) . '</strong></p>';
				} ?>

				<p><?php esc_html_e( 'Please, note that every external plugin can affect your website loading speed. You can add and remove plugins later on from within WordPress.', 'legenda' ); ?></p>

	            <div class="loading-info">
                    <strong>Importing demo content...</strong><br>
	                <h2>Please wait, it may take up to 2 minutes.</h2>
	                <div class="et-loader">
	                    <svg viewBox="0 0 187.3 93.7" preserveAspectRatio="xMidYMid meet">
	                        <path
	                            stroke="#ededed"
	                            class="outline"
	                            fill="none"
	                            stroke-width="4"
	                            stroke-linecap="round"
	                            stroke-linejoin="round"
	                            stroke-miterlimit="10"
	                            d="M93.9,46.4c9.3,9.5,13.8,17.9,23.5,17.9s17.5-7.8,17.5-17.5s-7.8-17.6-17.5-17.5c-9.7,0.1-13.3,7.2-22.1,17.1 c-8.9,8.8-15.7,17.9-25.4,17.9s-17.5-7.8-17.5-17.5s7.8-17.5,17.5-17.5S86.2,38.6,93.9,46.4z">

	                        </path>
	                        <path
	                            class="outline-bg"
	                            opacity="0.05"
	                            fill="none"
	                            stroke="#ededed"
	                            stroke-width="4"
	                            stroke-linecap="round"
	                            stroke-linejoin="round"
	                            stroke-miterlimit="10"
	                            d="M93.9,46.4c9.3,9.5,13.8,17.9,23.5,17.9s17.5-7.8,17.5-17.5s-7.8-17.6-17.5-17.5c-9.7,0.1-13.3,7.2-22.1,17.1c-8.9,8.8-15.7,17.9-25.4,17.9s-17.5-7.8-17.5-17.5s7.8-17.5,17.5-17.5S86.2,38.6,93.9,46.4z">

	                        </path>
	                    </svg>
	                </div>
	            </div>

				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="et-button button-active button-next"
					   data-callback="install_plugins"><?php esc_html_e( 'Continue', 'legenda' ); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="et-button"><?php esc_html_e( 'Skip this step', 'legenda' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</p>
			</form>
			<?php
		}

		private function _list_plugins( $plugins, $all, $checked = false ) {
			foreach ($plugins as $slug => $plugin) {
				$this->_plugin_list_item( $slug, $plugin, $all, $checked );
			}
		}

		private function _plugin_list_item( $slug, $plugin, $plugins, $checked = false ) {
			?>
				<li data-slug="<?php echo esc_attr( $slug ); ?>" class="plugin-to-install">
					<label for="plugin-import[<?php echo esc_attr($slug); ?>]">
						<input type="checkbox" name="plugin-import[<?php echo esc_attr($slug); ?>]" id="plugin-import[<?php echo esc_attr($slug); ?>]" <?php if ( $plugin['required'] || $checked ): ?>checked="checked"<?php endif ?> <?php if ( $plugin['required'] ): ?>disabled="disabled"<?php endif ?>>
						<?php echo esc_html( $plugin['name'] ); ?>
						<?php if ( ! empty( $plugin['details_url'] )): ?>
							(<a href="<?php echo esc_url( $plugin['details_url'] ); ?>" target="_blank">View details</a>)
						<?php endif ?>
						<span>
							<?php
						    $keys = array();
						    if ( isset( $plugins['install'][ $slug ] ) ) {
							    $keys[] = 'Installation';
						    }
						    if ( isset( $plugins['update'][ $slug ] ) ) {
							    $keys[] = 'Update';
						    }
						    if ( isset( $plugins['activate'][ $slug ] ) ) {
							    $keys[] = 'Activation';
						    }
						    echo implode( ' and ', $keys ) . ' required';
						    ?>
						</span>
						<div class="spinner"></div>
					</label>
				</li>
			<?php
		}


		public function ajax_plugins() {
			if ( ! check_ajax_referer( 'envato_setup_nonce', 'wpnonce' ) || empty( $_POST['slug'] ) ) {
				wp_send_json_error( array( 'error' => 1, 'message' => esc_html__( 'No Slug Found', 'legenda' ) ) );
			}
			$json = array();
			// send back some json we use to hit up TGM
			$plugins = $this->_get_plugins();
			// what are we doing with this plugin?
			foreach ( $plugins['activate'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-activate',
						'action2'       => - 1,
						'message'       => esc_html__( 'Activating Plugin', 'legenda' ),
					);
					break;
				}
			}
			foreach ( $plugins['update'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-update',
						'action2'       => - 1,
						'message'       => esc_html__( 'Updating Plugin', 'legenda' ),
					);
					break;
				}
			}
			foreach ( $plugins['install'] as $slug => $plugin ) {
				if ( $_POST['slug'] == $slug ) {
					$json = array(
						'url'           => admin_url( $this->tgmpa_url ),
						'plugin'        => array( $slug ),
						'tgmpa-page'    => $this->tgmpa_menu_slug,
						'plugin_status' => 'all',
						'_wpnonce'      => wp_create_nonce( 'bulk-plugins' ),
						'action'        => 'tgmpa-bulk-install',
						'action2'       => - 1,
						'message'       => esc_html__( 'Installing Plugin', 'legenda' ),
					);
					break;
				}
			}

			if ( $json ) {
				$json['hash'] = md5( serialize( $json ) ); // used for checking if duplicates happen, move to next plugin
				wp_send_json( $json );
			} else {
				wp_send_json( array( 'done' => 1, 'message' => esc_html__( 'Success', 'legenda' ) ) );
			}
			exit;

		}


		/**
		 * Page setup
		 */
		public function envato_setup_default_content() {

            $versions_imported = get_option('versions_imported', array());
            $version_now = get_option('version_now', '');

			?>
			<h1><?php esc_html_e( 'Default Content', 'legenda' ); ?></h1>
			<form method="post">
				<p>Complete your new website with some default content. Choose the appropriate demo version from the variants listed below. Select the necessary pages from the list to be imported also.

Once imported, this content can be managed from the WordPress dashboard.  </p>

	            <div class="import-demos-wrapper">
	                <h3><?php esc_html_e( 'Import demo versions', 'legenda'); ?></h3>
	                <div class="import-demos">
	                    <?php $i=0; foreach ($this->versions as $key => $version): $i++; ?>
                            <?php $imported = in_array($key, $versions_imported); 
                            	$is_active = $i == 1;
                            	if ( $version_now != '' ) {
                                  $is_active = $version_now == $key;
                            	}
                            ?>
	                    	<div class="version-preview version-preview-<?php echo esc_attr( $key ); ?> <?php if ($imported) { echo ' imported'; } ?><?php if ($is_active) { echo ' active-version'; } ?>" data-version="<?php echo esc_attr( $key ); ?>">
	                            <div class="version-screenshot">
	                                <img src="<?php echo ETHEME_CODE_IMAGES_URL.'/vers/v_'.$key.'.jpg'; ?>" alt="<?php echo esc_attr( $key ); ?>">
	                                <a href="#" class="et-button button-import-version button-import-version" data-version="<?php echo esc_attr( $key ); ?>">
	                                    <?php echo esc_html__('Import demo', 'legenda'); ?>
	                                </a>
	                            </div>
	                            <span class="version-title"><?php echo esc_html( $version['title'] ); ?></span>
	                        </div>
	                    <?php endforeach ?>
	                </div>
	            </div>

	            <input type="hidden" name="et-version" id="et-version" value="ecommerce">

				<p class="envato-setup-actions step">
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="et-button button-active button-next"
					   data-callback="install_content"><?php esc_html_e( 'Continue', 'legenda' ); ?></a>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="et-button"><?php esc_html_e( 'Skip this step', 'legenda' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</p>
			</form>
			<?php
		}



		private function _imported_term_id( $original_term_id, $new_term_id = false ) {
			$terms = get_transient( 'importtermids' );
			if ( ! is_array( $terms ) ) {
				$terms = array();
			}
			if ( $new_term_id ) {
				if ( ! isset( $terms[ $original_term_id ] ) ) {
					$this->log( 'Insert old TERM ID ' . $original_term_id . ' as new TERM ID: ' . $new_term_id );
				} else if ( $terms[ $original_term_id ] != $new_term_id ) {
					$this->error( 'Replacement OLD TERM ID ' . $original_term_id . ' overwritten by new TERM ID: ' . $new_term_id );
				}
				$terms[ $original_term_id ] = $new_term_id;
				set_transient( 'importtermids', $terms, 60 * 60 * 24 );
			} else if ( $original_term_id && isset( $terms[ $original_term_id ] ) ) {
				return $terms[ $original_term_id ];
			}

			return false;
		}


		public function vc_post( $post_id = false ) {

			$vc_post_ids = get_transient( 'import_vc_posts' );
			if ( ! is_array( $vc_post_ids ) ) {
				$vc_post_ids = array();
			}
			if ( $post_id ) {
				$vc_post_ids[ $post_id ] = $post_id;
				set_transient( 'import_vc_posts', $vc_post_ids, 60 * 60 * 24 );
			} else {

				$this->log( 'Processing vc pages 2: ' );

				return;
				if ( class_exists( 'Vc_Manager' ) && class_exists( 'Vc_Post_Admin' ) ) {
					$this->log( $vc_post_ids );
					$vc_manager = Vc_Manager::getInstance();
					$vc_base    = $vc_manager->vc();
					$post_admin = new Vc_Post_Admin();
					foreach ( $vc_post_ids as $vc_post_id ) {
						$this->log( 'Save ' . $vc_post_id );
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
						//twice? bug?
						$vc_base->buildShortcodesCustomCss( $vc_post_id );
						$post_admin->save( $vc_post_id );
						$post_admin->setSettings( $vc_post_id );
					}
				}
			}

		}

		private function _imported_post_id( $original_id = false, $new_id = false ) {
			if ( is_array( $original_id ) || is_object( $original_id ) ) {
				return false;
			}
			$post_ids = get_transient( 'importpostids' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $new_id ) {
				if ( ! isset( $post_ids[ $original_id ] ) ) {
					$this->log( 'Insert old ID ' . $original_id . ' as new ID: ' . $new_id );
				} else if ( $post_ids[ $original_id ] != $new_id ) {
					$this->error( 'Replacement OLD ID ' . $original_id . ' overwritten by new ID: ' . $new_id );
				}
				$post_ids[ $original_id ] = $new_id;
				set_transient( 'importpostids', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _post_orphans( $original_id = false, $missing_parent_id = false ) {
			$post_ids = get_transient( 'postorphans' );
			if ( ! is_array( $post_ids ) ) {
				$post_ids = array();
			}
			if ( $missing_parent_id ) {
				$post_ids[ $original_id ] = $missing_parent_id;
				set_transient( 'postorphans', $post_ids, 60 * 60 * 24 );
			} else if ( $original_id && isset( $post_ids[ $original_id ] ) ) {
				return $post_ids[ $original_id ];
			} else if ( $original_id === false ) {
				return $post_ids;
			}

			return false;
		}

		private function _cleanup_imported_ids() {
			// loop over all attachments and assign the correct post ids to those attachments.

		}

		private $delay_posts = array();

		private function _delay_post_process( $post_type, $post_data ) {
			if ( ! isset( $this->delay_posts[ $post_type ] ) ) {
				$this->delay_posts[ $post_type ] = array();
			}
			$this->delay_posts[ $post_type ][ $post_data['post_id'] ] = $post_data;

		}


		// return the difference in length between two strings
		public function cmpr_strlen( $a, $b ) {
			return strlen( $b ) - strlen( $a );
		}

		private function _parse_gallery_shortcode_content($content){
			// we have to format the post content. rewriting images and gallery stuff
			$replace      = $this->_imported_post_id();
			$urls_replace = array();
			foreach ( $replace as $key => $val ) {
				if ( $key && $val && ! is_numeric( $key ) && ! is_numeric( $val ) ) {
					$urls_replace[ $key ] = $val;
				}
			}
			if ( $urls_replace ) {
				uksort( $urls_replace, array( &$this, 'cmpr_strlen' ) );
				foreach ( $urls_replace as $from_url => $to_url ) {
					$content = str_replace( $from_url, $to_url, $content );
				}
			}
			if ( preg_match_all( '#\[gallery[^\]]*\]#', $content, $matches ) ) {
				foreach ( $matches[0] as $match_id => $string ) {
					if ( preg_match( '#ids="([^"]+)"#', $string, $ids_matches ) ) {
						$ids = explode( ',', $ids_matches[1] );
						foreach ( $ids as $key => $val ) {
							$new_id = $val ? $this->_imported_post_id( $val ) : false;
							if ( ! $new_id ) {
								unset( $ids[ $key ] );
							} else {
								$ids[ $key ] = $new_id;
							}
						}
						$new_ids                   = implode( ',', $ids );
						$content = str_replace( $ids_matches[0], 'ids="' . $new_ids . '"', $content );
					}
				}
			}
			// contact form 7 id fixes.
			if ( preg_match_all( '#\[contact-form-7[^\]]*\]#', $content, $matches ) ) {
				foreach ( $matches[0] as $match_id => $string ) {
					if ( preg_match( '#id="(\d+)"#', $string, $id_match ) ) {
						$new_id = $this->_imported_post_id( $id_match[1] );
						if ( $new_id ) {
							$content = str_replace( $id_match[0], 'id="' . $new_id . '"', $content );
						} else {
							// no imported ID found. remove this entry.
							$content = str_replace( $matches[0], '(insert contact form here)', $content );
						}
					}
				}
			}
			return $content;
		}

		private function _elementor_id_import( &$item, $key ) {
			if ( $key == 'id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'page' && ! empty( $item ) ) {

				if( false !== strpos( $item, "p." ) ){
					$new_id = str_replace('p.', '', $item);
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $new_id );
					if ( $new_meta_val ) {
						$item = 'p.' . $new_meta_val;
					}
				}else if(is_numeric($item)){
					// check if this has been imported before
					$new_meta_val = $this->_imported_post_id( $item );
					if ( $new_meta_val ) {
						$item = $new_meta_val;
					}
				}
			}
			if ( $key == 'post_id' && ! empty( $item ) && is_numeric( $item ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( $key == 'url' && ! empty( $item ) && strstr( $item, 'ocalhost' ) ) {
				// check if this has been imported before
				$new_meta_val = $this->_imported_post_id( $item );
				if ( $new_meta_val ) {
					$item = $new_meta_val;
				}
			}
			if ( ($key == 'shortcode' || $key == 'editor') && ! empty( $item ) ) {
				// we have to fix the [contact-form-7 id=133] shortcode issue.
				$item = $this->_parse_gallery_shortcode_content($item);

			}
		}


		private function _get_json( $file ) {
			if ( is_file( __DIR__ . '/content/' . basename( $file ) ) ) {
				WP_Filesystem();
				global $wp_filesystem;
				$file_name = __DIR__ . '/content/' . basename( $file );
				if ( file_exists( $file_name ) ) {
					return json_decode( $wp_filesystem->get_contents( $file_name ), true );
				}
			}

			return array();
		}

		private function _get_sql( $file ) {
			if ( is_file( __DIR__ . '/content/' . basename( $file ) ) ) {
				WP_Filesystem();
				global $wp_filesystem;
				$file_name = __DIR__ . '/content/' . basename( $file );
				if ( file_exists( $file_name ) ) {
					return $wp_filesystem->get_contents( $file_name );
				}
			}

			return false;
		}


		public $logs = array();

		public function log( $message ) {
			$this->logs[] = $message;
		}

		public $errors = array();

		public function error( $message ) {
			$this->logs[] = 'ERROR!!!! ' . $message;
		}

		/**
		 * Logo & Design
		 */
		public function envato_setup_logo_design() {

			?>
			<h1><?php esc_html_e( 'Logo &amp; Design', 'legenda' ); ?></h1>
			<form method="post">
				<p><?php printf( esc_html__( 'Please add your logo below. For best results, the logo should be a transparent PNG ( 466 by 277 pixels). The logo can be changed at any time from the Appearance > Customize area in your dashboard. Try %sEnvato Studio%s if you need a new logo designed.', 'legenda' ), '<a href="http://studiotracking.envato.com/aff_c?offer_id=4&aff_id=1564&source=DemoInstall" target="_blank">', '</a>' ); ?></p>

				<table>
					<tr>
						<td>
							<div id="current-logo">
								<?php
								$image_url = $this->get_logo_image();
								if ( $image_url ) {
									$image = '<img class="site-logo" src="%s" alt="%s" style="width:%s; height:auto" />';
									printf(
										$image,
										$image_url,
										get_bloginfo( 'name' ),
										$this->get_header_logo_width()
									);
								} ?>
							</div>
						</td>
						<td>
							<a href="#" class="button button-upload"><?php esc_html_e( 'Upload New Logo', 'legenda' ); ?></a>
						</td>
					</tr>
				</table>

				<?php
				$demo_styles = apply_filters( 'dtbwp_default_styles', array() );
				if ( ! $this->get_default_theme_style() || count( $demo_styles ) <= 1 ) {

				} else {
					?>

					<p><?php esc_html_e( 'Please choose the color scheme for this website. The color scheme (along with font colors &amp; styles) can be changed at any time from the Appearance > Customize area in your dashboard.', 'legenda' ); ?></p>

					<div class="theme-presets">
						<ul>
							<?php
							$current_demo = get_theme_mod( 'dtbwp_site_color', $this->get_default_theme_style() );
							foreach ( $demo_styles as $demo_name => $demo_style ) {
								?>
								<li
									<?php if ( $demo_name == $current_demo ) {
										echo 'class="current"';
									} ?>>
									<a href="#" data-style="<?php echo esc_attr( $demo_name ); ?>"><img
											src="<?php echo esc_url( $demo_style['image'] ); ?>"></a>
								</li>
							<?php } ?>
						</ul>
					</div>
				<?php } ?>

				<p><em>Please Note: Advanced changes to website graphics/colors may require extensive PhotoShop and Web
						Development knowledge. We recommend hiring an expert from <a
							href="http://studiotracking.envato.com/aff_c?offer_id=4&aff_id=1564&source=DemoInstall"
							target="_blank">Envato Studio</a> to assist with any advanced website changes.</em></p>
				<div style="display: none;">
					<img src="http://studiotracking.envato.com/aff_i?offer_id=4&aff_id=1564&source=DemoInstall"
					     width="1" height="1"/>
				</div>


				<input type="hidden" name="new_logo_id" id="new_logo_id" value="">
				<input type="hidden" name="new_style" id="new_style" value="">

				<p class="envato-setup-actions step">
					<input type="submit" class="button-primary button button-large button-next"
					       value="<?php esc_attr_e( 'Continue', 'legenda' ); ?>" name="save_step"/>
					<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
					   class="button button-large button-next"><?php esc_html_e( 'Skip this step', 'legenda' ); ?></a>
					<?php wp_nonce_field( 'envato-setup' ); ?>
				</p>
			</form>
			<?php
		}

		/**
		 * Save logo & design options
		 */
		public function envato_setup_logo_design_save() {
			check_admin_referer( 'envato-setup' );

			$new_logo_id = (int) $_POST['new_logo_id'];
			// save this new logo url into the database and calculate the desired height based off the logo width.
			// copied from dtbaker.theme_options.php
			if ( $new_logo_id ) {
				$attr = wp_get_attachment_image_src( $new_logo_id, 'full' );
				if ( $attr && ! empty( $attr[1] ) && ! empty( $attr[2] ) ) {

					set_theme_mod( 'custom_logo', $new_logo_id );
					set_theme_mod( 'header_textcolor', 'blank' );
					set_theme_mod( 'logo_header_image', $attr[0] );
					// we have a width and height for this image. awesome.
					$logo_width  = (int) get_theme_mod( 'logo_header_image_width', '467' );
					$scale       = $logo_width / $attr[1];
					$logo_height = $attr[2] * $scale;
					if ( $logo_height > 0 ) {
						set_theme_mod( 'logo_header_image_height', $logo_height );
					}
				}
			}

			$new_style = isset( $_POST['new_style'] ) ? $_POST['new_style'] : false;
			if ( $new_style ) {
				$demo_styles = apply_filters( 'dtbwp_default_styles', array() );
				if ( isset( $demo_styles[ $new_style ] ) ) {
					set_theme_mod( 'dtbwp_site_color', $new_style );
					if ( class_exists( 'dtbwp_customize_save_hook' ) ) {
						$site_color_defaults = new dtbwp_customize_save_hook();
						$site_color_defaults->save_color_options( $new_style );
					}
				}
			}

			wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
			exit;
		}

		/**
		 * Payments Step
		 */
		public function envato_setup_updates() {
			$this->process_form();
			?>
			<?php if ( etheme_is_activated() ): ?>
				<img src="<?php echo ETHEME_CODE_IMAGES_URL; ?>/success-icon-alt.png" alt="eight theme" class="elogo">
				<h2>Thank you for activation</h2>
				<p>Now you have lifetime updates, 6 months of free top-notch support, 24/7 live support and much more.</p>

				<?php if( get_filesystem_method() === 'direct' ) : ?>

					<p class="envato-setup-actions step">
						<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
						   class="et-button button-active"><?php esc_html_e( 'Continue', 'legenda' ); ?></a>
						<?php wp_nonce_field( 'envato-setup' ); ?>
					</p>

				<?php else: ?>
					<p>We see that the server configurations do not follow the requirements. So, please, install a theme and plugins manually. You can implement this via Theme Options>>Import/Export The following <a href="https://www.youtube.com/watch?v=bHE_UhSJM10" target="blank">Video tutorial</a> can assist you with that. If you encounter any difficulties, please, contact us in a Live Chat that can found in the right bottom corner of your Dashboard.<br> Thank you for using our theme.</p><br><br>
					<div class="support-includes">
						<div>
							<p><?php esc_html_e( 'More Resources', 'legenda' ); ?></p>
							<ul>
								<li class="documentation"><a href="http://8theme.com/demo/docs/" target="_blank"><?php esc_html_e( 'Read the Theme Documentation', 'legenda' ); ?></a></li>
								<li class="howto"><a href="https://wordpress.org/support/"target="_blank"><?php esc_html_e( 'Learn how to use WordPress', 'legenda' ); ?></a></li>
								<li class="rating"><a href="http://themeforest.net/downloads"target="_blank"><?php esc_html_e( 'Leave an Item Rating', 'legenda' ); ?></a></li>
								<li class="support"><a href="https://www.8theme.com/forums/"target="_blank"><?php esc_html_e( 'Get Help and Support', 'legenda' ); ?></a></li>
							</ul>
						</div>
						<div class="envato-setup-next-steps-last">
							<p>How to install theme manually</p>
							<br>
							<iframe width="560" height="315" style="max-width:100%; height:auto;" src="https://www.youtube.com/embed/bHE_UhSJM10?list" frameborder="0" allowfullscreen></iframe>
						</div>
					</div>
					<p class="envato-setup-actions step">
						<a class="et-button button-active" href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'View your new website!', 'legenda' ); ?></a>
						<a class="et-button" href="https://themeforest.net/user/8theme" target="_blank"><?php esc_html_e( 'Follow @8theme on ThemeForest', 'legenda' ); ?></a>
						<a class="et-button button-active" href="<?php echo esc_url( get_admin_url() ); ?>"><?php esc_html_e( 'Go to Dashboard', 'legenda' ); ?></a>
					</p>

				<?php endif;?>

			<?php else: ?>
				<h1><?php _e('Activate', 'legenda'); ?></h1>
				<form method="post">
	                <p>
	                    <label for="purchase-code"><?php _e('Enter Purchase code', 'legenda'); ?></label>
	                    <input type="text" name="purchase-code" placeholder="Example: f20b1cdd-ee2a-1c32-a146-66eafea81761" id="purchase-code" />
	                </p>
	                <p>
	                        <input class="et-button button" name="etheme-purchase-code" type="submit" value="<?php esc_attr_e( 'Activate theme', 'legenda' ); ?>" />

	                </p>
	            </form>
				<p><?php _e('Use your purchase code to activate Legenda template. Please, note, that you won’t be able to use it without activation.', 'legenda'); ?></p>
				<h3 class="text-uppercase"><?php esc_html_e('Where can I find my purchase code?', 'legenda'); ?></h3>

                <ul style="text-align: left;">
                    <li><b><?php esc_html_e( 'If you bought theme on  ', 'legenda' ); ?> <a
                                    href="https://themeforest.net/">https://themeforest.net/ : </a></b>
                    </li>
                    <li>1. <?php esc_html_e('Please enter your Envato account and find ', 'legenda'); ?> <a href="https://themeforest.net/downloads"><?php esc_html_e('Downloads tab', 'legenda'); ?></a></li>
                    <li>2. <?php esc_html_e('Find Legenda theme in the list and click on the opposite', 'legenda');?> <span><?php echo esc_html__('Download', 'legenda'); ?></span> <?php esc_html_e('button', 'legenda'); ?></li>
                    <li>3. <?php esc_html_e('Select', 'legenda'); ?> <span><?php echo esc_html__('License Certificate & Purchase code', 'legenda'); ?></span> <?php esc_html_e('for download', 'legenda'); ?></li>
                    <li>4. <?php esc_html_e('Copy the', 'legenda'); ?> <span><?php esc_html_e('Item Purchase Code', 'legenda'); ?> </span><?php esc_html_e('from the downloaded document', 'legenda'); ?></li>
                    <br/>
                    <li><b><?php esc_html_e( 'If you bought a subscription on  ', 'legenda' ); ?> <a
                                    href="https://www.8theme.com/">https://www.8theme.com/ :</a></b>
                    </li>
                    <li>1. <?php esc_html_e( 'Please enter your 8theme account and find the Subscription License Key section', 'legenda' ); ?>
                    </li>
                    <li>2. <?php esc_html_e( 'Copy the existing code or generate the new one if you already used previously generated code. You need to generate separate codes for every single activation on different domains.', 'legenda' ); ?>
                    </li>
                    <li>3. <?php esc_html_e( 'Use it to activate the theme', 'legenda' ); ?>
                    </li>

                </ul>
			<?php endif ?>
			<?php
		}

		/**
		 * Payments Step save
		 */
		public function envato_setup_updates_save() {
			check_admin_referer( 'envato-setup' );

			// redirect to our custom login URL to get a copy of this token.
			$url = $this->get_oauth_login_url( $this->get_step_link( 'updates' ) );

			wp_redirect( esc_url_raw( $url ) );
			exit;
		}


		public function envato_setup_customize() {
		?>
			<h1>Setup Legenda Child Theme (Optional)</h1>

			<p>
				Use Child theme, please, if you are going to make any modifications to the theme source code. It will allow the parent theme to receive updates without overwriting   your source code changes. Please, avoid changing the original theme HTML/CSS/PHP code.
			Use the form below to create and activate the Child Theme.</p>

			<?php if(!isset($_REQUEST['theme_name'])){ ?>
			<p class="lead"> Click on Skip This Step if you are not going to use Child theme now.</p>
			<?php } ?>

			<?php
				// Create Child Theme
				if(isset($_REQUEST['theme_name']) && current_user_can('manage_options')){
					$this->_make_child_theme(esc_html($_REQUEST['theme_name']));
				}
				$theme = get_option('etheme_has_child') ? wp_get_theme(get_option('etheme_has_child') )->Name : 'Legenda Child';
			 ?>

			<?php if(!isset($_REQUEST['theme_name'])){ ?>

			<form action="<?php $_PHP_SELF ?>" method="POST">
			 <div class="child-theme-input" style="margin-bottom: 20px;">
			 <label>Child Theme Title</label>
		 	 <input type="text" name="theme_name" value="<?php echo esc_attr($theme); ?>" />
		 	 </div>
			<p class="envato-setup-actions step">
		        <button type="submit" id= type="submit" class="et-button button-active">
		         <?php _e( 'Create and Use Child Theme', 'legenda' ); ?>
		        </button>
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="et-button"><?php _e( 'Skip this step', 'legenda' ); ?></a>

			</p>
			</form>
			<?php } else { ?>
			<p class="envato-setup-actions step">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="et-button button-active"><?php _e( 'Continue', 'legenda' ); ?></a>
			</p>
			<?php } ?>
			<?php
		}

		private function _make_child_theme( $new_theme_title ) {

				$parent_theme_title = 'Legenda';
				$parent_theme_template = 'legenda';
				$parent_theme_name = get_stylesheet();
				$parent_theme_dir = get_stylesheet_directory();

				// Turn a theme name into a directory name
				$new_theme_name = sanitize_title( $new_theme_title );
				$theme_root = get_theme_root();

				// Validate theme name
				$new_theme_path = $theme_root.'/'.$new_theme_name;
				if ( file_exists( $new_theme_path ) ) {
					// Don't create child theme.
				} else{
					// Create Child theme
					wp_mkdir_p( $new_theme_path );

					$plugin_folder = get_template_directory().'/framework/thirdparty/envato_setup/child-theme/';

					// Make style.css
					ob_start();
					require $plugin_folder.'child-theme-css.php';
					$css = ob_get_clean();

					global $wp_filesystem;

			        if ( empty( $wp_filesystem ) ) {
			            require_once ( ABSPATH . '/wp-admin/includes/file.php' );
			            WP_Filesystem();
			        }

					$wp_filesystem->put_contents( $new_theme_path.'/style.css', $css, FS_CHMOD_FILE );

					//file_put_contents( $new_theme_path.'/style.css', $css );

					// Copy functions.php
					copy( $plugin_folder.'functions.php', $new_theme_path.'/functions.php' );

					// Copy screenshot
					copy( $plugin_folder.'screenshot.png', $new_theme_path.'/screenshot.png' );

					// Make child theme an allowed theme (network enable theme)
					$allowed_themes = get_site_option( 'allowedthemes' );
					$allowed_themes[ $new_theme_name ] = true;
					update_site_option( 'allowedthemes', $allowed_themes );
				}

				// Switch to theme
				if($parent_theme_template !== $new_theme_name){
					echo '<p class="lead success">Child Theme <strong>'.$new_theme_title.'</strong> created and activated! Folder is located in wp-content/themes/<strong>'.$new_theme_name.'</strong></p>';
					update_option('etheme_has_child', $new_theme_name);
					switch_theme( $new_theme_name, $new_theme_name );
				}
		}

		public function envato_setup_help_support() {
			?>
			<h1>Help and Support</h1>
			<p>The theme comes with 6 months item free support from purchase date (with the option to extend the support period).</p>
			<p>If you encounter any difficulties with our product we are ready to assist you via:</p>

			<ul class="support-blocks">
				<li>
					<img src="<?php echo ETHEME_CODE_IMAGES_URL; ?>/telegram.png"><br>
					Telegram chanel<br> <a href="https://t.me/etheme" target="_blank">check</a></li>
				<li>
					<img src="<?php echo ETHEME_CODE_IMAGES_URL; ?>/support-icon.png"><br>
					Support Forum<br> <a href="https://www.8theme.com/forums/" target="_blank">check</a></li>
<!--				<li>-->
<!--					<img src="--><?php //echo ETHEME_CODE_IMAGES_URL; ?><!--/icon-envato.png"><br>-->
<!--					ThemeForest profile<br> <a href="http://prntscr.com/d24xhu" target="_blank">check</a></li>-->
			</ul>

			<div class="support-includes">
				<div class="includes">
					<p>Item Support includes:</p>
					<ul>
						<li>Answering technical questions about theme features</li>
						<li>Assistance with reported bugs and issues</li>
						<li>Help with bundled 3rd party plugins</li>
					</ul>
				</div>
				<div class="excludes">
					<p>Item Support <strong>DOES NOT</strong> Include:</p>
					<ul>
						<li>Customization services</li>
						<li>Installation services</li>
						<li>Support for non-bundled 3rd party plugins. </li>
					</ul>
				</div>
			</div>


<!--			<p>More details about item support can be found in the ThemeForest <a-->
<!--					href="http://themeforest.net/page/item_support_policy" target="_blank">Item Support Policy</a>. </p>-->
			<p class="envato-setup-actions step">
				<a href="<?php echo esc_url( $this->get_next_step_link() ); ?>"
				   class="et-button button-active"><?php esc_html_e( 'Continue', 'legenda' ); ?></a>
				<?php wp_nonce_field( 'envato-setup' ); ?>
			</p>
			<?php
		}

		/**
		 * Final step
		 */
		public function envato_setup_ready() {

			update_option( 'envato_setup_complete', time() );
			?>

			<img src="<?php echo ETHEME_CODE_IMAGES_URL; ?>/success-icon-alt.png" alt="eight theme" class="elogo">
			<h1><?php esc_html_e( 'Your Website is Ready!', 'legenda' ); ?></h1>

			<p>Congratulations! Legenda theme has been activated on your website and ready to use. Login to your WordPress dashboard to make all the necessary changes and upload your content. </p>

			<br>
			<br>

			<div class="support-includes">
				<div>
					<p><?php esc_html_e( 'More Resources', 'legenda' ); ?></p>
					<ul>
						<li class="documentation"><a href="http://8theme.com/demo/docs/"
						                             target="_blank"><?php esc_html_e( 'Read the Theme Documentation', 'legenda' ); ?></a>
						</li>
						<li class="howto"><a href="https://wordpress.org/support/"
						                     target="_blank"><?php esc_html_e( 'Learn how to use WordPress', 'legenda' ); ?></a>
						</li>
						<li class="rating"><a href="http://themeforest.net/downloads"
						                      target="_blank"><?php esc_html_e( 'Leave an Item Rating', 'legenda' ); ?></a></li>
						<li class="support"><a href="https://www.8theme.com/forums/"
						                       target="_blank"><?php esc_html_e( 'Get Help and Support', 'legenda' ); ?></a></li>
					</ul>
				</div>
				<div class="envato-setup-next-steps-last">
					<p>Come back and leave us <span>5-star rating</span></p>
					<br>
					<img src="<?php echo ETHEME_CODE_IMAGES_URL . '/no-video.jpg'; ?>" alt="rate">
				</div>
			</div>
			<p class="envato-setup-actions step">
				<a class="et-button button-active" href="<?php echo esc_url( home_url() ); ?>"><?php esc_html_e( 'View your new website!', 'legenda' ); ?></a>
<!--				<a class="et-button" href="https://themeforest.net/user/8theme" target="_blank">--><?php //esc_html_e( 'Follow @8theme on ThemeForest', 'legenda' ); ?><!--</a>-->
			</p>
			<?php
		}



		/**
		 * @param $array1
		 * @param $array2
		 *
		 * @return mixed
		 *
		 *
		 * @since    1.1.4
		 */
		private function _array_merge_recursive_distinct( $array1, $array2 ) {
			$merged = $array1;
			foreach ( $array2 as $key => &$value ) {
				if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
					$merged [ $key ] = $this->_array_merge_recursive_distinct( $merged [ $key ], $value );
				} else {
					$merged [ $key ] = $value;
				}
			}

			return $merged;
		}

		/**
		 * Helper function
		 * Take a path and return it clean
		 *
		 * @param string $path
		 *
		 * @since    1.1.2
		 */
		public static function cleanFilePath( $path ) {
			$path = str_replace( '', '', str_replace( array( '\\', '\\\\', '//' ), '/', $path ) );
			if ( $path[ strlen( $path ) - 1 ] === '/' ) {
				$path = rtrim( $path, '/' );
			}

			return $path;
		}

		public function is_submenu_page() {
			return ( $this->parent_slug == '' ) ? false : true;
		}


	    public function activate( $purchase, $api_key ) {

	    	$data = array(
	            'api_key' => $api_key,
	            'theme' => ETHEME_DOMAIN,
	            'purchase' => $purchase,
	        );

        	update_option( 'etheme_activated_data', maybe_unserialize( $data ) );
	        update_option( 'xtheme_is_activated', true );

	    }

	    public function get_api_key() {
	        $api_key = false;
	        $activated_data = get_option( 'etheme_activated_data' );
	        $stored = $activated_data['api_key'];
	        if( $stored && ! empty( $stored ) ) $api_key = $stored;
	        return $api_key;
	    }


	    public function get_stored_code() {
	        $code = false;

	        $stored = get_option( 'theme_purchase_code', false );

	        if( $stored ) $code = $stored;

	        return $code;
	    }

	    public function process_form() {
	        if( isset( $_POST['etheme-purchase-code'] ) && ! empty( $_POST['etheme-purchase-code'] ) ) {
	            $code = trim( $_POST['purchase-code'] );

	            if( empty( $code ) ) {
	               echo  '<p class="error">Enter the purchase code</p>';
	                return;
	            }
				$theme_id = 5888906;
				$response = wp_remote_get( $this->api_url . 'activate/' . $code . '?envato_id='. $theme_id .'&domain=' .$this->domain() );
	            $response_code = wp_remote_retrieve_response_code( $response );

	            if( $response_code != '200' ) {
	                echo  '<p class="error">API request call error.  1) Contact your server provider and make sure that OpenSSL system library is 1.0 or higher  2) Make sure that your purshase code is associated with buyer. If you bought theme using guest account create account with the same email that was used during purchase process.</p>';
	                return;
	            }

	            $data = json_decode( wp_remote_retrieve_body($response), true );

	            if( isset( $data['error'] ) ) {
	               echo  '<p class="error">' . $data['error'] . '</p>';
	                return;
	            }

	            if( ! $data['verified'] ) {
	               echo  '<p class="error">Code is not verified!</p>';
	                return;
	            }

	            $this->activate( $code, $data['token'] );

                #echo  '<p class="updated">Theme is activated!</p>';

	        }
	    }

	    // public function form_purchase_code_value() {
	    //     $code = '';

	    //     $stored = $this->get_stored_code();

	    //     if( $stored ) $code = $stored;

	    //     if( isset( $_POST['purchase-code'] ) && ! empty( $_POST['purchase-code'] ) ) $code = $_POST['purchase-code'];

	    //     return $code;
	    // }

	    public function domain() {
	        $domain = get_option('siteurl'); //or home
	        $domain = str_replace('http://', '', $domain);
	        $domain = str_replace('https://', '', $domain);
	        $domain = str_replace('www', '', $domain); //add the . after the www if you don't want it
	        return urlencode($domain);
	    }


	}

}// if !class_exists

/**
 * Loads the main instance of Envato_Theme_Setup_Wizard to have
 * ability extend class functionality
 *
 * @since 1.1.1
 * @return object Envato_Theme_Setup_Wizard
 */
add_action( 'after_setup_theme', 'envato_theme_setup_wizard', 10 );
if ( ! function_exists( 'envato_theme_setup_wizard' ) ) :
	function envato_theme_setup_wizard() {
		Envato_Theme_Setup_Wizard::get_instance();
	}
endif;
