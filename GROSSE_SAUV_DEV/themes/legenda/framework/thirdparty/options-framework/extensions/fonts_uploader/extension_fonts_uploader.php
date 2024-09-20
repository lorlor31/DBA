<?php

/**
 * Redux Framework is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Redux Framework is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Redux Framework. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package     ReduxFramework
 * @author      Dovy Paukstys (dovy)
 * @version     3.0.0
 */


// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) exit;

// Don't duplicate me!
if( !class_exists( 'ReduxFramework_extension_fonts_uploader' ) ) {


    /**
     * Main ReduxFramework custom_field extension class
     *
     * @since       3.1.6
     */
    class ReduxFramework_extension_fonts_uploader extends ReduxFramework {

        // Protected vars
        protected $parent;
        public $extension_url;
        public $extension_dir;
        public static $theInstance;

        /**
        * Class Constructor. Defines the args for the extions class
        *
        * @since       1.0.0
        * @access      public
        * @param       array $sections Panel sections.
        * @param       array $args Class constructor arguments.
        * @param       array $extra_tabs Extra panel tabs.
        * @return      void
        */
        public function __construct( $parent ) {
            
            $this->parent = $parent;
            if ( empty( $this->extension_dir ) ) {
                $this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
            }
            $this->field_name = 'fonts_uploader';

            self::$theInstance = $this;

            add_filter( 'redux/'.$this->parent->args['opt_name'].'/field/class/'.$this->field_name, array( &$this, 'overload_field_path' ) ); // Adds the local field

            // ! Remove font file by ajax
            add_action( 'wp_ajax_et_ajax_fonts_remove', array( $this, 'et_ajax_fonts_remove') );


        }

        // ! Remove font by ajax
        public function et_ajax_fonts_remove() {

                $post_data = $_POST;
                $fonts = get_option( 'etheme-fonts', false );
                $out = array(
                    'messages' => array(),
                    'status' => 'error'
                );

                if ( ! isset( $post_data['id'] ) || empty( $post_data['id'] ) ) {
                    $out['messages'][] = esc_html__( 'File ID does not exist', 'legenda' );
                    echo json_encode( $out );
                    die();
                }

                if ( ! function_exists( 'wp_delete_file' ) ) require_once ABSPATH . WPINC . '/functions.php';

                foreach ( $fonts as $key => $value ) {

                    if ( $value['id'] == $post_data['id'] ) {

                        $file = $value['file'];

                        $upload_dir = wp_upload_dir();
                        $upload_dir = $upload_dir['basedir'];

                        $url = explode( '/uploads', $file['url'] );

                        wp_delete_file( $upload_dir . $url[1] );

                        if ( etheme_file_exists( $file['url'] ) ) {
                            $out['messages'][] = esc_html__( 'File was\'t deleted', 'legenda' );
                            die();
                        } else {
                            unset( $fonts[$key] );
                        }
                    }
                }

                update_option( 'etheme-fonts', $fonts );

                if ( count( $out['messages'] ) < 1 ){
                    $out['status'] = 'success';
                    $out['messages'][] = esc_html__( 'File was deleted', 'legenda' );
                } 

            echo json_encode($out);
            die();
        }

        public function getInstance() {
            return self::$theInstance;
        }

        // Forces the use of the embeded field path vs what the core typically would use    
        public function overload_field_path($field) {
            return dirname(__FILE__).'/'.$this->field_name.'/field_'.$this->field_name.'.php';
        }

    } // class


} // if
