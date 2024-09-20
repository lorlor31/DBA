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
 * @author      Dovy Paukstys
 * @version     3.1.5
 */
// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'ReduxFramework_fonts_uploader' ) ) {

    class ReduxFramework_fonts_uploader extends ReduxFramework {

        // ! Field Constructor
        function __construct( $field = array(), $value ='', $parent = '' ) {

            $this->parent = $parent;
            $this->field = $field;
            $this->value = $value;
            $this->post_data = $_POST;
            $this->file_data = $_FILES;
            $this->errors = array();

            if ( empty( $this->extension_dir ) ) {
                $this->extension_dir = trailingslashit( str_replace( '\\', '/', dirname( __FILE__ ) ) );
                $this->extension_url = site_url( str_replace( trailingslashit( str_replace( '\\', '/', ABSPATH ) ), '', $this->extension_dir ) );
            }

            // Set default args for this field to avoid bad indexes. Change this to anything you use
            $defaults = array(
                'options'           => array(),
                'stylesheet'        => '',
                'output'            => true,
                'enqueue'           => true,
                'enqueue_frontend'  => true
            );
            $this->field = wp_parse_args( $this->field, $defaults );
            
            // ! Call upload file function
            if ( isset( $this->post_data['et-upload'] ) ) $this->upload_action();
        }

        // ! Field Render Function
        public function render() {

            $out = $style = '';

            // ! Close default structure
            $out .= '</td></tr></table>';

            $out .= '
                <div class="et_fonts-notifications etheme-options-info">
                    <p>' . esc_html__( 'Please, make sure that you upload font formats that are supported by all the browsers.', 'legenda' ) . '</p>
                    <h4 clas="et_fonts-table-title">' . esc_html__( 'Browser Support for Font Formats', 'legenda' ) . '</h4>
                    <table>
                        <tbody>
                            <tr>
                                <th>' . esc_html__( 'Font format', 'legenda' ) . '</th>
                                <th class="et_fonts-br-name et_ie"><i class="fa fa-internet-explorer" aria-hidden="true"></i> / <i class="fa fa-edge" aria-hidden="true"></i></th>
                                <th class="et_fonts-br-name et_chrome"><i class="fa fa-chrome" aria-hidden="true"></i></th>
                                <th class="et_fonts-br-name et_firefox"><i class="fa fa-firefox" aria-hidden="true"></i></th>
                                <th class="et_fonts-br-name et_safari"><i class="fa fa-safari" aria-hidden="true"></i></th>
                                <th class="et_fonts-br-name et_opera"><i class="fa fa-opera" aria-hidden="true"></i></th>                
                            </tr>
                            <tr>
                                <td>TTF/OTF</td>
                                <td>9.0*</td>
                                <td>4.0</td>
                                <td>3.5</td>
                                <td>3.1</td>
                                <td>10.0</td>
                            </tr>
                            <tr>
                                <td>WOFF</td>
                                <td>9.0</td>
                                <td>5.0</td>
                                <td>3.6</td>
                                <td>5.1</td>
                                <td>11.1</td>
                            </tr>
                            <tr>
                                <td>WOFF2</td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                                <td>36.0</td>
                                <td>35.0*</td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                                <td>26.0</td>
                            </tr>
                            <!-- <tr>
                                <td>SVG</td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                                <td>4.0</td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                                <td>3.2</td>
                                <td>9.0</td>
                            </tr> -->
                            <tr>
                                <td>EOT</td>
                                <td>6.0</td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                                <td><i class="et_deprecated fa fa-times" aria-hidden="true"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            ';

            $out .= '<div class="etheme-fonts-section">';

                $out .= '<p class="add-form">' . esc_html__( 'Upload font', 'legenda' ) . '</p>';

                if ( count( $this->errors ) > 0 ) {
                    foreach ( $this->errors as $value ) $out .= '<div class="et_errors-block"><p class="et_font-error">' . $value . '</p></div>';
                }

                $fonts = get_option( 'etheme-fonts', false );

                // ! Out font information
                if ( $fonts ) {
                    $out .= '<div class="et_fonts-info">';
                        $out .= '<h2>' . esc_html__( 'Uploaded fonts', 'legenda' ) . '</h2>';
                        $out .= '<ul>';

                            foreach ( $fonts as $value ) {

                                // ! Set HTML
                                $out .= '<li>';
                                    $out .= '<p>';
                                        $out .= '<span class="et_font-name">' . $value['name'] . '</span>';
                                        $out .= '<i class="et_font-remover fa fa-times" aria-hidden="true" data-id="' . $value['id'] . '"></i>';
                                    $out .= '</p>';

                                    if ( ! etheme_file_exists( $value['file']['name'] ) ){
                                        $out .= '<p class="et_font-error">';
                                            $out .= esc_html__( 'It looks like font file was removed from the folder directly', 'legenda' );
                                        $out .= '</p>';
                                        continue;
                                    }

                                    $out .= '<p class="et_font-preview" style="font-family: ' . $value['name'] . ';"> 1 2 3 4 5 6 7 8 9 0 A B C D E F G H I J K L M N O P Q R S T U V W X Y Z a b c d e f g h i j k l m n o p q r s t u v w x y z </p>';
                                    $out .= '<details>';
                                        $out .= '<summary>' . esc_html__( 'Font details', 'legenda' ) . '</summary>';
                                        $out .= '<ul>';
                                            $out .= '<li>' . esc_html__( 'Uploaded at', 'legenda' ) . ' : ' . $value['file']['time'] . '</li>';
                                            $out .= '<li>';
                                                $out .= esc_html__( 'Uploaded by', 'legenda' ) . ' : ' . $value['user']['user_login'];
                                                $out .= ' "' . $value['user']['user_email'] . '"';
                                                foreach ( $value['user']['roles'] as $role ) $out .= ' ' . $role;
                                            $out .='</li>';
                                            $out .= '<li>' . esc_html__( 'File name', 'legenda' ) . ' : ' . $value['file']['name'] . '</li>';
                                            $out .= '<li>' . esc_html__( 'File size', 'legenda' ) . ' : ' . $this->file_size( $value['file']['size'] ) . '</li>';
                                        $out .= '</ul>';
                                    $out .= '</details>';
                                $out .= '</li>';

                                // ! Validate format
                                switch ( $value['file']['extension'] ) {
                                    case 'ttf':
                                        $format = 'truetype';
                                        break;
                                    case 'otf':
                                        $format = 'opentype';
                                        break;
                                    case 'eot':
                                        $format = false;
                                        break;
                                    case 'eot?#iefix':
                                        $format = 'embedded-opentype';
                                        break;
                                    case 'woff2':
                                        $format = 'woff2';
                                        break;
                                    case 'woff':
                                        $format = 'woff';
                                        break;
                                    default:
                                        $format = false;
                                        break;
                                } 

                                $format = ( $format ) ? 'format("' . $format . '")' : '';

                                $font_url = ( is_ssl() && (strpos($value['file']['url'], 'https') === false) ) ? str_replace('http', 'https', $value['file']['url']) : $value['file']['url'];

                                // ! Set fonts
                                $style .= '
                                    @font-face {
                                        font-family: ' . $value['name'] . ';
                                        src: url(' . $font_url . ') ' . $format . ';
                                    }
                                ';
                            }

                        $out .= '</ul>';
                    $out .= '</div>';
                }

            $out .= '</div>';

            $out .= '<table class="form-table no-border" style="margin-top: 0;"><tbody><tr style="border-bottom:0; display:none;"><th style="padding-top:0;"></th><td style="padding-top:0;">';

            echo '<style>' . $style .'</style>' . $out;
        }

        // ! Upload file
        private function upload_action(){

            // ! Return if name file
            if ( ! isset( $this->file_data['et-fonts'] ) || empty( $this->file_data['et-fonts'] ) ){
                $this->errors[] = esc_html__( 'Empty Font file field', 'legenda' );
                return;
            } 

            // ! Require file
            if ( ! function_exists( 'wp_handle_upload' ) ) require_once( ABSPATH . 'wp-admin/includes/file.php' );

            // ! Set Valid file formats
            $valid_formats = array( 'eot', 'woff2', 'woff', 'ttf', 'otf' );

            $file = $this->file_data['et-fonts'];
            
            // ! Get file extension
            $extension = pathinfo( $file['name'], PATHINFO_EXTENSION );

            // ! Check file extension
            if ( ! in_array( strtolower( $extension ), $valid_formats ) ){
                $this->errors[] = esc_html__( 'Wrong file extension "use only: eot, woff2, woff, ttf, otf"', 'legenda' );
                return;
            } 

            // ! Check size 5mb limit
            if ( $file['size'] > ( 1048576 * 5 ) ){
                $this->errors[] = esc_html__( 'File size more then 5MB', 'legenda' );
                return;
            } 
            
            if ( $file['name'] ) {

                // ! Set overrides
                $overrides = array( 
                    'test_form' => false,
                    'test_type' => false,
                );

                // ! Set font user data
                $user  = wp_get_current_user();
                $by = array();
                $by['user_email'] = $user->user_email;
                $by['user_login'] = $user->user_login;
                $by['roles'] = array();
                foreach ( $user->roles as $value ) $by['roles'][] = $value;
               
                $font_file = array(
                    'name' => $file['name'],
                    'type' => $file['type'],
                    'size' => $file['size'],
                    'extension' => $extension,
                    'time' => current_time( 'mysql' ),
                );

                // ! Change upload dir
                add_filter( 'upload_dir', array( $this, 'etheme_upload_dir' ) );

                $status = wp_handle_upload( $file, $overrides );

                // ! Set upload dir to default
                remove_filter( 'upload_dir', array( $this, 'etheme_upload_dir' ) );

                if ( $status && ! isset( $status['error'] ) ) {
                    $font_file['url'] = $status['url'];
                    $this->gafq_files[] = $font_file;
                    $this->errors[] = esc_html__( 'File was successfully uploaded.', 'legenda' );

                    // ! Update fonts
                    $fonts = get_option( 'etheme-fonts', false );
                    $font = array();

                    $font['id'] = mt_rand( 1000000,9999999 );
                    $font['name'] = str_replace( '.' . $extension, '', $file['name'] );
                    $font['file'] = $font_file;
                    $font['user'] = $by;
                    $fonts[] = $font;
                    update_option( 'etheme-fonts', $fonts );

                } else {
                    //$this->errors[] = $status['error'];
                }
            }
            return;
        }

        // ! Upload dir filter function
        public function etheme_upload_dir($dir){
            $time = current_time( 'mysql' );
            $y = substr( $time, 0, 4 );
            $m = substr( $time, 5, 2 );
            $subdir = "/$y/$m";

            return array(
                'path' => $dir['basedir'] . '/custom-fonts' . $subdir,
                'url' => $dir['baseurl'] . '/custom-fonts' . $subdir ,
                'subdir' => '/custom-fonts' . $subdir,
            ) + $dir;
        }

        // Get formated file size
        public function file_size( $bytes ){
            if ( $bytes  >= 1073741824 ) {
                $bytes  = number_format( $bytes  / 1073741824, 2 ) . ' GB';
            } elseif ( $bytes  >= 1048576) {
                $bytes  = number_format( $bytes  / 1048576, 2 ) . ' MB';
            } elseif ( $bytes  >= 1024 ) {
                $bytes  = number_format( $bytes  / 1024, 2 ) . ' KB';
            } elseif ( $bytes  > 1 ) {
                $bytes  = $bytes  . ' bytes';
            } elseif ( $bytes  == 1 ) {
                $bytes  = $bytes  . ' byte';
            } else {
                $bytes  = '0 bytes';
            }
            return $bytes;
        }

        // ! Enqueue Function
        public function enqueue() {
            wp_enqueue_script(
                'redux-field-fonts-uploader-js',
                $this->extension_url . 'field_fonts_uploader.js',
                array( 'jquery' ),
                time(),
                true
            );

            wp_enqueue_style(
                'redux-field-fonts-uploader-css',
                $this->extension_url . 'field_fonts_uploader.css',
                time(),
                true
            );
        }

        // ! Output Function
        public function output() {
            if ( $this->field['enqueue_frontend'] ) {
                // ! return nothing
            }
        }
    }
}
