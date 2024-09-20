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

if( ! class_exists( 'ReduxFramework_theme_versions' ) ) {

    class ReduxFramework_theme_versions extends ReduxFramework {

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
            
        }

        // ! Field Render Function
        public function render() {


            echo '</td></tr></table>';

                /* turns arguments array into variables */
                    // extract( $args );

                    /* verify a description */
                    // $has_desc = $field_desc ? true : false;

                    $versions_imported = get_option('versions_imported', array());
                    $version_now = get_option('version_now', '');

                    /* format setting outer wrapper */
                    echo '<div class="format-setting type-backup">';

                        $versions = et_get_versions_option();
                        $home_versions = et_get_home_option();

                        $demo_data_installed = get_option('demo_data_installed');

                        $button_label = esc_html__('Install base demo content', 'legenda');

                        if($demo_data_installed != 'yes') : ?>  
                          
                          <button class="et-button et-button-green install-ver" data-ver="ecommerce" data-home_id="129" ><?php esc_html_e('Install Base Demo Content' , 'legenda' ); ?></button>
                          <br />
                          <p><?php _e('<strong>Note:</strong> We recommend to install base demo content first', 'legenda') ?></p>

                        <?php else : ?>

                          <div class="et-install-demo et-message et-info">
                            <p><?php _e('<strong>Note:</strong> You have already installed demo content.', 'legenda') ?></p>
                          </div>

                        <?php endif; ?>

                        <?php $ajax_nonce = wp_create_nonce('et_nonce'); ?>
  
                        <span class="et_nonce hidden" value="<?php echo wp_create_nonce('et_nonce'); ?>"></span>

                          <div class="format-setting-label">
                            <h3 class="label">Home pages</h3>
                          </div>

                          <div class="et-install-demo">
                              <select class="option-tree-ui-select" id="demo_data_style">
                                <?php
                                  foreach ( $home_versions as $key => $value) {
                                    echo '<option  data-home_id="' . $value['home_id'] . '" value="' . esc_attr( $key ) . '">' . esc_attr( $value['title'] ) . '</option>';
                                  }
                                ?>
                              </select>
                              <button class="et-button et-button-green" id="install_home_pages" ><?php esc_html_e('Install Home Page' , 'legenda' ) ?></button>
                          </div>
                          <br/>
                          
                          <div class="format-setting-label">
                            <h3 class="label">Demo versions</h3>
                          </div>

                          <div class="et-theme-versions">
                              <?php foreach($versions as $key => $value): ?>
                                <?php $imported = in_array($key, $versions_imported); 
                                      $is_active = $version_now == $key;
                                ?>
                                  <div class="theme-ver <?php if ($imported) { echo ' imported'; } if ($is_active) { echo ' is-active'; } ?>">
                                      <img src="<?php echo ETHEME_CODE_IMAGES_URL.'/vers/v_'.$key.'.jpg'; ?>"> 
                                      <button class="install-ver" data-ver="<?php echo esc_attr($key); ?>" data-home_id="<?php echo esc_attr($value['home_id']); ?>" ><?php echo (!$imported) ? esc_html__('Install Version' , 'legenda' ) : esc_html__('Activate Version' , 'legenda' ) ?></button>
                                      <h3><?php echo esc_html($value['title']); ?></h3>
                                  </div>
                              <?php endforeach; ?>
                          </div>

                        <?php

                    echo '</div>';
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
                $this->extension_url . 'field_theme_versions.js',
                array( 'jquery' ),
                time(),
                true
            );

            wp_enqueue_style(
                'redux-field-fonts-uploader-css',
                $this->extension_url . 'field_theme_versions.css',
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
