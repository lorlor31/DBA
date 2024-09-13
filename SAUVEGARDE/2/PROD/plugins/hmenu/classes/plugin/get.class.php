<?php

    #UPDATE CLASS
    class hmenu_class_get extends hmenu_backend
    {
        
        #GET MENUS
        public function hmenu_get_menus()
        {
            global $wpdb;
            
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE deleted = '0' ORDER BY created DESC");
                
            #CREATE OBJECT
            $menu_object = array(
                'menus'=> array()
            );
            
            if ($result) {
                foreach ($result as $menu) {
                    array_push($menu_object['menus'], array(
                        'status' => $this->hmenu_convert_int(1),
                        'menuId' => $this->hmenu_convert_int($menu->menuId),
                        'name' => htmlspecialchars($menu->name),
                        'autoLink' => $this->hmenu_convert_int($menu->autoLink),
                        'leftItems' => $menu->leftItems,
                        'centerItems' => $menu->centerItems,
                        'rightItems' => $menu->rightItems,
                        'customLink' => $menu->customLink,
                        'overwrite' => $menu->overwrite,
                        'created' => $menu->created
                    ));
                }
            }
                
            echo json_encode($menu_object);
            
            exit();
        }
        
        #GET PRESETS
        public function hmenu_get_presets()
        {
            global $wpdb;
            
            $result = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_presets WHERE deleted = '0' ORDER BY created DESC");

            echo json_encode($result);
            
            exit();
        }
        
        #GET LOCATIONS
        public function hmenu_get_menu_locations()
        {
            global $wpdb;
            
            #CREATE LOCATION OBJECT
            $location_object = array(
                'locations'=> array()
            );
            
            $menus_locations = get_registered_nav_menus();
            
            foreach ($menus_locations as $location => $description) {
                array_push($location_object['locations'], array(
                    'location' => $location
                ));
            }
            
            echo json_encode($location_object);
            
            exit();
        }

        #Get the HTML for standard menu itmes
        public function hmenu_load_mega_menu_item_html()
        {
            $the_index = $_GET['index'];
            $plugin_url = $_GET['url'];
            $nav_item_id = $_GET['navItemId'];
            $nav_parent_id = $_GET['parentId'];
            $nav_level = $_GET['lvl'];
            $the_type = $_GET['the_type']; ?>
            <li class="hero_sort_item" data-allow-sub="yes" data-index="<?php echo $the_index; ?>" id="hero_margin_left_<?php echo $nav_level; ?>" data-level="<?php echo $nav_level; ?>" data-id="<?php echo $nav_item_id; ?>" data-menu-type="<?php echo $the_type; ?>" data-parent="<?php echo $nav_parent_id; ?>">
                <div class="hero_item_wrap">
                    <div class="hero_item_bar rounded_3 hero_bar_red">
                        <div class="hero_item_toggle" data-nav-toggle="close"></div>
                        <div class="hero_item_heading size_14 hero_white" id="ni_heading_<?php echo $the_index; ?>">
                            Menu Name
                        </div>
                        <input type="hidden" style="width:60px;" id="item_order_<?php echo $the_index; ?>" name="item_order_<?php echo $the_index; ?>">
                        <input type="hidden" style="width:60px;" id="item_parent_<?php echo $the_index; ?>" name="item_parent_<?php echo $the_index; ?>">
                        <input type="hidden" style="width:60px;" id="item_level_<?php echo $the_index; ?>" name="item_level_<?php echo $the_index; ?>">
                        <div class="hero_item_edits_holder">
                            <div class="hero_nav_type rounded_30 size_10" id="item_type_<?php echo $the_index; ?>">post</div>
                            <div class="hero_edits rounded_20">
                                <div class="hero_edit_item hero_button_edit" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/edit_icon.png)"></div>
                                <div class="hero_edit_item hero_button_delete" data-main-index="<?php echo $the_index; ?>" data-item-id="<?php echo $nav_item_id; ?>" data-parent-id="<?php echo $nav_parent_id; ?>" data-level="<?php echo $nav_level; ?>" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/delete_icon.png)"></div>
                            </div>
                            <div class="hero_item_drag"></div>
                        </div>
                    </div>
                    <div class="hero_col_12 hero_item_content">
                        <div class="hero_col_3">					
                            <label class="size_12">Label</label>
                            <input type="text" data-size="lrg" id="ni_name_<?php echo $the_index; ?>" name="ni_name_<?php echo $the_index; ?>">					
                        </div>
                        <div class="hero_col_3">					
                            <label class="size_12">Title</label>
                            <input type="text" data-size="lrg" id="ni_alt_<?php echo $the_index; ?>" name="ni_alt_<?php echo $the_index; ?>">					
                        </div>
                        <?php
                            if ($the_type == 'method') {
                                ?>   
                        <div class="hero_col_3">					
                            <label class="size_12">Javascript method</label>
                            <input type="text" data-size="lrg" id="ni_event_function_<?php echo $the_index; ?>" name="ni_event_function_<?php echo $the_index; ?>">					
                        </div> 
                        <?php
                            } ?>
                        <?php
                            if ($the_type == 'custom') {
                                ?>
                            <div class="hero_col_3">					
                                <label class="size_12">URL</label><br>
                                <input type="text" data-size="lrg" id="ni_url_<?php echo $the_index; ?>" name="ni_url_<?php echo $the_index; ?>">					
                            </div>
                        <?php
                            } ?>
                        <?php
                            if ($the_type != 'method') {
                                ?>
                        <div class="hero_col_3">					                                  	
                            <label class="size_12">Target</label>
                            <select data-size="lrg" id="ni_target_<?php echo $the_index; ?>" name="ni_target_<?php echo $the_index; ?>">
                                <option value="_blank">New Page</option>
                                <option value="_self">Same Window</option>
                            </select>					
                        </div>
                        <?php
                            } ?>
                        <?php
                            if ($the_type == 'custom') {
                                ?>
                            <div class="hero_col_6" style="float:right">                    
                                <p class="size_11 hero_grey">http://www.example.com</p>
                            </div>
                        <?php
                            } ?>
                        <div class="hero_col_3">
                            <label class="size_12">Custom CSS class</label>
                            <input type="text" data-size="lrg" id="ni_cssclass_<?php echo $the_index; ?>" name="ni_cssclass_<?php echo $the_index; ?>">
                        </div>
                        <div class="hero_col_12 hero_bottom_line">
                            <div class="hero_col_4">
                                <label><h2 class="size_14 hero_green">Icon</h2></label>
                                <div class="hero_switch_position"><input type="checkbox" data-size="sml" data-smltoggler="toggle_ni_icon_<?php echo $the_index; ?>" id="ni_icon_<?php echo $the_index; ?>" name="ni_icon_<?php echo $the_index; ?>" value="1"></div>
                            </div>
                            <div class="toggle_ni_icon_<?php echo $the_index; ?>">
                                <p class="size_12">You can add an icon to display next to your nav item. <a class="hero_open_icons hero_green" data-input-link="ni_icon_content_<?php echo $the_index; ?>" data-panel-toggle="close" data-load-link="hero_load_icons_<?php echo $the_index; ?>">Change Icon</a></p>
                                <div class="hero_col_12">
                                    <div class="hero_col_1">
                                        <div class="hero_selected_icon rounded_3 the_icon_<?php echo $the_index; ?>" data-trigger="hero_load_icons_<?php echo $the_index; ?>">
                                            <div id="hero_inner_icon"></div>
                                        </div>
                                    </div>
                                    <div class="hero_col_4">                                  
                                        <select data-size="lrg" id="ni_icon_size_<?php echo $the_index; ?>" name="ni_icon_size_<?php echo $the_index; ?>">
                                            <option value="xsmall" selected="selected">x-small</option>
                                            <option value="small">small</option>
                                            <option value="medium">medium</option>
                                            <option value="large">large</option>
                                        </select>                                
                                    </div>
                                    <div class="hero_col_4">                                  
                                        <input type="text" id="ni_icon_color_<?php echo $the_index; ?>" class="color_picker" name="ni_icon_color_<?php echo $the_index; ?>">
                                        <input type="hidden" id="ni_icon_content_<?php echo $the_index; ?>" name="ni_icon_content_<?php echo $the_index; ?>">                                
                                    </div>
                                </div>
                                <!--<div class="hero_load_icons rounded_3" id="hero_load_icons_<?php echo $the_index; ?>">
                                    
                                </div>-->
                            </div>
                        </div>

                        <!-- NEW -->
                        <div class="hero_col_12 hero_bottom_line">
                            <div class="hero_col_4">
                                <label><h2 class="size_14 hero_green">User Roles</h2></label>
                                <div class="hero_switch_position"><input type="checkbox" data-size="sml" data-smltoggler="toggle_ni_roles_<?php echo $the_index; ?>" id="ni_role_<?php echo $the_index; ?>" name="ni_role_<?php echo $the_index; ?>" value="1"></div>
                            </div>
                            <div class="toggle_ni_roles_<?php echo $the_index; ?>">
                                <p class="size_12">Select multiple user roles for the current navigational item.</p>
                                <div class="hero_col_12">
                                    <div class="ni_user_roles_<?php echo $the_index; ?>">
                                        <input type="hidden" id="ni_roles_val_<?php echo $the_index; ?>" name="ni_roles_val_<?php echo $the_index; ?>" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- NEW -->

                    </div>
                </div>
                <ul class="transfer_items not_sortable"></ul>
            </li>            
    
        <?php
            wp_die();
        }

        #Get the HTML for mega menu itmes
        public function hmenu_load_menu_item_html()
        {
            $the_index = $_GET['index'];
            $plugin_url = $_GET['url'];
            $nav_item_id = $_GET['navItemId'];
            $nav_parent_id = $_GET['parentId'];
            $nav_level = $_GET['lvl'];
            $the_mega_id = $_GET['megaMenuId']; ?>
             <li class="hero_mega_menu hero_sort_item" data-allow-sub="no" data-index="<?php echo $the_index; ?>" id="hero_margin_left_<?php echo $nav_level; ?>" data-level="<?php echo $nav_level; ?>" data-id="<?php echo $nav_item_id; ?>" data-menu-type="mega" data-parent="<?php echo $nav_parent_id; ?>">
                <div class="hero_item_wrap">
                    <div class="hero_item_bar rounded_3 hero_mega_bg">
                        <div class="hero_item_toggle" data-nav-toggle="close"></div>
                        <div class="hero_item_heading size_14 hero_white" id="mega_heading_<?php echo $the_index; ?>">
                            Menu Name
                        </div>
                        <input type="hidden" style="width:60px;" id="item_order_<?php echo $the_index; ?>" name="item_order_<?php echo $the_index; ?>">
                        <input type="hidden" style="width:60px;" id="item_parent_<?php echo $the_index; ?>" name="item_parent_<?php echo $the_index; ?>">
                        <input type="hidden" style="width:60px;" id="item_level_<?php echo $the_index; ?>" name="item_level_<?php echo $the_index; ?>">
                        <div class="hero_item_edits_holder">
                            <div class="hero_nav_type rounded_30 size_10" id="item_type_<?php echo $the_index; ?>">post</div>
                            <div class="hero_edits rounded_20">
                                <div class="hero_edit_item hero_button_edit" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/edit_icon.png)"></div>
                                <div class="hero_edit_item hero_button_delete" data-main-index="<?php echo $the_index; ?>" data-item-id="<?php echo $nav_item_id; ?>" data-parent-id="<?php echo $nav_parent_id; ?>" data-level="<?php echo $nav_level; ?>" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/delete_icon.png)"></div>
                            </div>
                            <div class="hero_item_drag"></div>
                        </div>
                    </div>
                    <div class="hero_col_12 hero_item_content hero_bottom_line">
                        <div class="hero_col_3">                    
                            <label class="size_12">Mega name</label>
                            <input type="text" data-size="lrg" id="mega_name_<?php echo $the_index; ?>" name="mega_name_<?php echo $the_index; ?>">                    
                        </div>
                        <div class="hero_col_3">                    
                            <label class="size_12">Title</label>
                            <input type="text" data-size="lrg" id="mega_alt_<?php echo $the_index; ?>" name="mega_alt_<?php echo $the_index; ?>">                    
                        </div>
                        <div class="hero_col_3">                    
                            <label class="size_12">URL</label>
                            <input type="text" data-size="lrg" id="mega_url_<?php echo $the_index; ?>" name="mega_url_<?php echo $the_index; ?>">                    
                        </div>
                        <div class="hero_col_3">					                                  	
                            <label class="size_12">Target</label>
                            <select data-size="lrg" id="mega_target_<?php echo $the_index; ?>" name="mega_target_<?php echo $the_index; ?>">
                                <option value="_blank">New Page</option>
                                <option value="_self">Same Window</option>
                            </select>					
                        </div>
                        <div class="hero_col_6">
                            <div class="hero_col_6">
                                <label class="size_12">Custom CSS class</label>
                                <input type="text" data-size="lrg" id="mega_cssclass_<?php echo $the_index; ?>" name="mega_cssclass_<?php echo $the_index; ?>">
                            </div>
                        </div>
                        <div class="hero_col_6" style="float:right">                    
                            <p class="size_11 hero_grey">http://www.example.com</p>
                        </div>
                        <div class="hero_col_12">
                            <div class="hero_col_4">
                                <label><h2 class="size_14 hero_green">Icon</h2></label>
                                <div class="hero_switch_position"><input type="checkbox" data-size="sml" data-smltoggler="toggle_mega_icon_<?php echo $the_index; ?>" id="mega_icon_<?php echo $the_index; ?>" name="mega_icon_<?php echo $the_index; ?>" value="1"></div>
                            </div>
                            <div class="toggle_mega_icon_<?php echo $the_index; ?>">
                                <p class="size_12">You can add an icon to display next to your nav item. <a class="hero_open_icons hero_green" data-input-link="mega_icon_content_<?php echo $the_index; ?>" data-panel-toggle="close" data-load-link="hero_load_icons_<?php echo $the_index; ?>">Change Icon</a></p>
                                <div class="hero_col_12">
                                    <div class="hero_col_1">
                                        <div class="hero_selected_icon rounded_3 the_icon_<?php echo $the_index; ?>" data-trigger="hero_load_icons_<?php echo $the_index; ?>">
                                            <div id="hero_inner_icon"></div>
                                        </div>
                                    </div>
                                    <div class="hero_col_4">                                  
                                        <select data-size="lrg" id="mega_icon_size_<?php echo $the_index; ?>" name="mega_icon_size_<?php echo $the_index; ?>">
                                            <option value="xsmall" selected="selected">x-small</option>
                                            <option value="small">small</option>
                                            <option value="medium">medium</option>
                                            <option value="large">large</option>
                                        </select>                                
                                    </div>
                                    <div class="hero_col_4">                                  
                                        <input type="text" id="mega_icon_color_<?php echo $the_index; ?>" class="color_picker" name="mega_icon_color_<?php echo $the_index; ?>">
                                        <input type="hidden" id="mega_icon_content_<?php echo $the_index; ?>" name="mega_icon_content_<?php echo $the_index; ?>">                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hero_col_12 hero_item_content hero_bottom_line">
                        <div class="hero_col_11">
                            <label class="size_12">Layout</label>
                            <div class="hero_layout_options" id="hero_options_<?php echo $the_index; ?>">
                                <div class="hero_selected_layout rounded_top_3"></div>
                                <div class="hero_option_items">
                                    <div class="hero_12 rounded_3" data-id="hero_12" data-cols="1" data-idx="<?php echo $the_index; ?>" data-layout="12"></div>
                                    <div class="hero_66 rounded_3" data-id="hero_66" data-cols="2" data-idx="<?php echo $the_index; ?>" data-layout="6,6"></div>
                                    <div class="hero_84 rounded_3" data-id="hero_84" data-cols="2" data-idx="<?php echo $the_index; ?>" data-layout="8,4"></div>
                                    <div class="hero_48 rounded_3" data-id="hero_48" data-cols="2" data-idx="<?php echo $the_index; ?>" data-layout="4,8"></div>
                                    <div class="hero_444 rounded_3" data-id="hero_444" data-cols="3" data-idx="<?php echo $the_index; ?>" data-layout="4,4,4"></div>
                                    <div class="hero_633 rounded_3" data-id="hero_633" data-cols="3" data-idx="<?php echo $the_index; ?>" data-layout="6,3,3"></div>
                                    <div class="hero_336 rounded_3" data-id="hero_336" data-cols="3" data-idx="<?php echo $the_index; ?>" data-layout="3,3,6"></div>
                                    <div class="hero_3333 rounded_3" data-id="hero_3333" data-cols="4" data-idx="<?php echo $the_index; ?>" data-layout="3,3,3,3"></div>
                                    <div class="hero_22224 rounded_3" data-id="hero_22224" data-cols="5" data-idx="<?php echo $the_index; ?>" data-layout="2,2,2,2,4"></div>
                                    <div class="hero_42222 rounded_3" data-id="hero_42222" data-cols="5" data-idx="<?php echo $the_index; ?>" data-layout="4,2,2,2,2"></div>
                                    <div class="hero_custom5 rounded_3" data-id="hero_custom5" data-cols="5" data-idx="<?php echo $the_index; ?>" data-layout="5,5,5,5,5"></div>                            
                                    <div class="hero_222222 rounded_3" data-id="hero_222222" data-cols="6" data-idx="<?php echo $the_index; ?>" data-layout="2,2,2,2,2,2"></div>
                                    <input type="hidden" style="width:60px;" id="mega_layout_<?php echo $the_index; ?>" name="mega_layout_<?php echo $the_index; ?>" data-change="hero_id_<?php echo $the_index; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="hero_mega_playground rounded_playground_3 the_playground_<?php echo $the_index; ?>">
                            <div class="mega_col_holder mega_cols_<?php echo $the_index; ?>" id="hero_id_<?php echo $the_index; ?>">
                                <!-- LOAD IN COLS -->
                            </div>
                            <div class="hero_menu_col_options rounded_3 options_<?php echo $the_index; ?>" data-placement="">
                                <div class="hero_close_options rounded_30"></div>
                                <ul class="size_11 hero_white">
                                    <li class="hero_option_one" data-popup="post" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">Posts<div class="hero_mega_option_image"></div></li>
                                    <li class="hero_option_two" data-popup="text" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">Text<div class="hero_mega_option_image"></div></li>
                                    <li class="hero_option_three" data-popup="list" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">List<div class="hero_mega_option_image"></div></li>
                                    <li class="hero_option_four" data-popup="contact" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">Contact/HTML<div class="hero_mega_option_image"></div></li>
                                    <!--<li class="hero_option_five" data-popup="woo" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">Products<div class="hero_mega_option_image"></div></li>
                                    <li class="hero_option_six" data-popup="slider" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">Slider<div class="hero_mega_option_image"></div></li>-->
                                    <li class="hero_option_seven" data-popup="map" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">Map<div class="hero_mega_option_image"></div></li>
                                    <li class="hero_option_eight" data-popup="images" data-main-index="<?php echo $the_index; ?>" data-mega-id="<?php echo $the_mega_id; ?>">Images<div class="hero_mega_option_image"></div></li>
                                </ul>
                            </div>
                        </div>                
                        <div class="hero_col_12">
                            <div class="hero_col_4">
                                <label><h2 class="size_14 hero_green">Nav item active</h2></label>
                                <div class="hero_switch_position"><input type="checkbox" data-size="sml" id="mega_nav_active_<?php echo $the_index; ?>" name="mega_nav_active_<?php echo $the_index; ?>" value="1"></div>
                            </div>
                            <div class="hero_col_4">
                                <label><h2 class="size_14 hero_green">Hide nav item on mobile</h2></label>
                                <div class="hero_switch_position"><input type="checkbox" data-size="sml" id="mega_mobile_active_<?php echo $the_index; ?>" name="mega_mobile_active_<?php echo $the_index; ?>" value="1"></div>
                            </div>
                        </div>
                    </div>
                    <div class="hero_col_12 hero_bottom_line">
                        <div class="hero_col_8">
                            <label>
                                <h2 class="size_18 hero_red weight_600">Background image</h2>
                                <p class="size_12 hero_grey">Enable background image for this mega menu.</p>
                            </label>
                        </div>
                        <div class="hero_col_4">
                            <input type="checkbox" data-size="lrg" id="mega_background_<?php echo $the_index; ?>" data-smltoggler="toggle_mega_background_<?php echo $the_index; ?>" name="mega_background_<?php echo $the_index; ?>" value="1" data-toggler="true">
                        </div>
                    </div>
                    <div class="toggle_mega_background_<?php echo $the_index; ?> hero_bottom_line">
                        <div class="hero_col_12">
                            <div class="hero_col_8">
                                <label><h2 class="size_14 hero_green">Background Position</h2></label>
                                <p class="size_12 hero_grey">Position your background.</p>
                            </div>
                            <div class="hero_col_4">
                                <select data-size="lrg" id="mega_background_position_<?php echo $the_index; ?>" name="mega_background_position_<?php echo $the_index; ?>">
                                    <option value="center" selected="selected">Center</option>
                                    <option value="left">Left</option>
                                    <option value="right">Right</option>
                                    <option value="bottom right">Bottom, Right</option>
                                    <option value="bottom left">Bottom, Left</option>
                                    <option value="top right">Top, Right</option>
                                    <option value="top left">Top, Left</option>                        
                                </select>  
                            </div>
                        </div>
                        <div class="hero_col_12">
                            <div class="hero_col_8">
                                <label><h2 class="size_14 hero_green">Background Url</h2></label>
                                <p class="size_12 hero_grey">Mega menu background url, use your own url if you like.</p>
                            </div>
                            <div class="hero_col_4">
                                <input type="text" data-size="lrg" data-hero_type="img" id="mega_background_url_<?php echo $the_index; ?>" name="mega_background_url_<?php echo $the_index; ?>" value="logo">
                            </div>
                        </div>
                        <div class="hero_col_12">
                            <p class="size_12 hero_red">You can set the background color for mega menus under: <strong class="hero_grey">Styling &raquo; Mega Menu &raquo; Background color</strong></p>
                        </div>
                        <div class="hero_col_12">
                            <div class="hero_col_8">
                                <div class="hero_button_auto green_button rounded_3 hero_media_uploader" data-connect-with="mega_background_url_<?php echo $the_index; ?>" data-multiple="false" data-size="full">Add background</div>
                            </div>
                        </div>
                    </div>

                    <!-- NEW -->
                    <div class="hero_col_12 hero_bottom_line">
                        <div class="hero_col_4">
                            <label><h2 class="size_14 hero_green">User Roles</h2></label>
                            <div class="hero_switch_position"><input type="checkbox" data-size="sml" data-smltoggler="toggle_mega_roles_<?php echo $the_index; ?>" id="mega_role_<?php echo $the_index; ?>" name="mega_role_<?php echo $the_index; ?>" value="1"></div>
                        </div>
                        <div class="toggle_mega_roles_<?php echo $the_index; ?>">
                            <p class="size_12">Select multiple user roles for the current navigational item.</p>
                            <div class="hero_col_12">
                                <div class="mega_user_roles_<?php echo $the_index; ?>">
                                    <input type="hidden" id="mega_roles_val_<?php echo $the_index; ?>" name="mega_roles_val_<?php echo $the_index; ?>" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- NEW -->

                </div>
            </li>           
     
         <?php
             wp_die();
        }

        #GET HTML for mega menu list time
        public function hmenu_load_mega_menu_list_item_html()
        {
            $the_index = $_GET['index'];
            $main_index = $_GET['mainIndex'];
            $content_index = $_GET['contentIndex'];
            $plugin_url = $_GET['url'];
            $list_item_id = $_GET['listId']; ?>
            <li class="hero_list_sort_item" data-index="<?php echo $the_index; ?>" data-main-index="<?php echo $main_index; ?>" data-content-index="<?php echo $content_index; ?>" data-id="<?php echo $list_item_id; ?>" data-menu-type="basic">
                <div class="hero_item_wrap">
                    <div class="hero_item_bar rounded_3 hero_bar_red">
                        <div class="hero_item_toggle" data-nav-toggle="close"></div>
                        <div class="hero_item_heading size_14 hero_white" id="list_heading_<?php echo $the_index; ?>">
                            Menu Name
                        </div>
                        <input type="hidden" style="width:60px;" id="list_item_order_<?php echo $the_index; ?>" name="list_item_order_<?php echo $the_index; ?>">
                        <div class="hero_item_edits_holder">
                            <div class="hero_nav_type rounded_30 size_10" id="item_type_<?php echo $the_index; ?>">post</div><!---->
                            <div class="hero_edits rounded_20">
                                <div class="hero_edit_item hero_button_edit" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/edit_icon.png)"></div>
                                <div class="hero_edit_item hero_button_delete hero_delete_list" data-main-index="<?php echo $main_index; ?>" data-content-index="<?php echo $content_index; ?>" data-id="<?php echo $the_index; ?>" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/delete_icon.png)"></div>
                            </div>
                            <div class="hero_item_drag"></div>
                        </div>
                    </div>
                    <div class="hero_col_12 hero_item_content">
                        <div class="hero_col_3">
                            <label class="size_12">Label</label>
                            <input type="text" data-size="lrg" id="list_name_<?php echo $the_index; ?>" name="list_name_<?php echo $the_index; ?>">					
                        </div>
                        <div class="hero_col_3">					
                            <label class="size_12">Title attribute</label>
                            <input type="text" data-size="lrg" id="list_alt_<?php echo $the_index; ?>" name="list_alt_<?php echo $the_index; ?>">					
                        </div>
                        <div class="hero_col_3 the_list_input_<?php echo $the_index; ?>">					
                            <label class="size_12">URL</label><br>
                            <input type="text" data-size="lrg" id="list_url_<?php echo $the_index; ?>" name="list_url_<?php echo $the_index; ?>">					
                        </div>
                        <div class="hero_col_3">					                                    	
                            <label class="size_12">Target</label>
                            <select data-size="lrg" id="list_target_<?php echo $the_index; ?>" name="list_target_<?php echo $the_index; ?>">
                                <option value="_blank">New Page</option>
                                <option value="_self">Same Window</option>
                            </select>					
                        </div>
                        <div class="hero_col_12">
                            <div class="hero_col_2">
                                <label><h2 class="size_14 hero_green">Icon</h2></label>
                                <div class="hero_switch_position"><input type="checkbox" data-size="sml" data-smltoggler="toggle_list_icon_<?php echo $the_index; ?>" id="list_icon_<?php echo $the_index; ?>" name="list_icon_<?php echo $the_index; ?>" value="1"></div>
                            </div>
                            <div class="hero_col_3">
                                <label><h2 class="size_14 hero_green">Description</h2></label>
                                <div class="hero_switch_position"><input type="checkbox" data-size="sml" data-smltoggler="toggle_desc_icon_<?php echo $the_index; ?>" id="list_desc_<?php echo $the_index; ?>" name="list_desc_<?php echo $the_index; ?>" value="1"></div>
                            </div>
                            <div class="toggle_list_icon_<?php echo $the_index; ?>">
                                <div class="hero_col_12">
                                    <p class="size_12">You can add an icon to display next to your nav item. <a class="hero_open_icons hero_green" data-input-link="list_icon_content_<?php echo $the_index; ?>" data-panel-toggle="close" data-load-link="list_icon_content_<?php echo $the_index; ?>">Change Icon</a></p>
                                    <div class="hero_col_12">
                                        <div class="hero_col_1">
                                            <div class="hero_selected_icon rounded_3 the_list_icon_<?php echo $the_index; ?>">
                                                <div id="hero_inner_icon"></div>
                                            </div>
                                        </div>
                                        <div class="hero_col_4">                                  
                                            <select data-size="lrg" id="list_icon_size_<?php echo $the_index; ?>" name="list_icon_size_<?php echo $the_index; ?>">
                                                <option value="xsmall" selected="selected">x-small</option>
                                                <option value="small">small</option>
                                                <option value="medium">medium</option>
                                                <option value="large">large</option>
                                            </select>
                                            <input type="hidden" id="list_icon_content_<?php echo $the_index; ?>" name="list_icon_content_<?php echo $the_index; ?>" value="icon_none">                                
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="toggle_desc_icon_<?php echo $the_index; ?>">
                            <p class="size_12">Description: Add a short description to your list item.</p>
                            <div class="hero_col_12">
                                <textarea data-size="lrg" id="list_description_<?php echo $the_index; ?>" name="list_description_<?php echo $the_index; ?>" rows="2"></textarea> 
                            </div>                  
                        </div>
                    </div>
                </div>
                <ul class="transfer_items"></ul>
            </li>
        <?php
             wp_die();
        }

        #GET social item HTML
        public function hmenu_load_menu_social_item_html()
        {
            $the_index = $_GET['index'];
            $plugin_url = $_GET['url'];
            $the_icon_class = $_GET['class']; ?>
            <li class="hero_list_sort_item" data-index="<?php echo $the_index; ?>" id="<?php echo $the_icon_class; ?>">
                <div class="hero_item_wrap">
                    <div class="hero_item_bar rounded_3 hero_bar_red">
                        <div class="hero_item_toggle" data-nav-toggle="close"></div>
                        <div class="hero_item_heading size_14 hero_white" id="social_heading_<?php echo $the_index; ?>">
                            Menu Name
                        </div>
                        <div class="hero_social_icon hero_social_icon_display_<?php echo $the_index; ?>">
                            <div id="inner_icon"></div>
                        </div>
                        <input type="hidden" style="width:60px;" id="social_item_order_<?php echo $the_index; ?>" name="social_item_order_<?php echo $the_index; ?>">
                        <input type="hidden" id="social_icon_content_<?php echo $the_index; ?>" name="social_icon_content_<?php echo $the_index; ?>">
                        <div class="hero_item_edits_holder">
                            <div class="hero_nav_type rounded_30 size_10" id="item_type_<?php echo $the_index; ?>">social</div>
                            <div class="hero_edits rounded_20">
                                <div class="hero_edit_item hero_button_edit" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/edit_icon.png)"></div>
                                <div class="hero_edit_item hero_button_delete hero_delete_social" data-index="<?php echo $the_index; ?>" style="background-image:url(<?php echo $plugin_url; ?>/assets/images/admin/delete_icon.png)"></div>
                            </div>
                            <div class="hero_item_drag"></div>
                        </div>
                    </div>
                    <div class="hero_col_12 hero_item_content">
                        <div class="hero_col_3">					
                            <label class="size_12">Label</label>
                            <input type="text" data-size="lrg" id="social_name_<?php echo $the_index; ?>" name="list_name_<?php echo $the_index; ?>">					
                        </div>
                        <div class="hero_col_2">					                                    	
                            <label class="size_12">Target</label>
                            <select data-size="lrg" id="social_target_<?php echo $the_index; ?>" name="social_target_<?php echo $the_index; ?>">
                                <option value="_blank">New Page</option>
                                <option value="_self">Same Window</option>
                            </select>					
                        </div>
                        <div class="hero_col_3">                        
                            <label class="size_12">Icon size</label><br> 
                            <select data-size="lrg" id="social_icon_size_<?php echo $the_index; ?>" name="social_icon_size_<?php echo $the_index; ?>">
                                <option value="xsmall" selected="selected">x-small</option>
                                <option value="small">small</option>
                                <option value="medium">medium</option>
                                <option value="large">large</option>
                            </select>                        
                        </div>
                        <div class="hero_col_2">                        
                            <label class="size_12">Icon color</label><br>  
                            <input type="text" id="social_icon_color_<?php echo $the_index; ?>" class="color_picker" name="social_icon_color_<?php echo $the_index; ?>">                        
                        </div>
                        <div class="hero_col_2">                         
                            <label class="size_12">Icon hover color</label><br> 
                            <input type="text" id="social_icon_hover_color_<?php echo $the_index; ?>" class="color_picker" name="social_icon_hover_color_<?php echo $the_index; ?>">                        
                        </div>
                        <div class="hero_col_12">					
                            <label class="size_12">URL</label><br>
                            <input type="text" data-size="lrg" id="social_url_<?php echo $the_index; ?>" name="social_url_<?php echo $the_index; ?>">					
                        </div>
                    </div>
                </div>
                <ul class="transfer_items"></ul>
            </li>
        <?php
             wp_die();
        }

        public function hmenu_upload_menu_icon_pack()
        {

            #SECURITY CHECK
            $file = require_once('frame_sec.check.php');

            if (isset($_POST['token']) && $_POST['token']) { //secure (display content)
             

                #IF FILE DATA EXISTS
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    
                    #DIRECTORY NAME
                    $directory_name = WP_PLUGIN_DIR.'/hmenu/_tmp_fonts/';
                    
                    #VARS
                    $file_name = $_FILES['hero_font_file']['name'];
                
                    #FILE TYPES
                    $file_mimes = array(
                    'application/zip',
                    'application/x-zip',
                    'application/x-zip-compressed',
                    'application/octet-stream',
                    'application/x-compress',
                    'application/x-compressed',
                    'multipart/x-zip',
                    'application/rar',
                    'application/x-rar',
                    'application/x-rar-compressed'
                );
                
                    #CHECK TO SEE IF FILE EXISTS
                    if (in_array($_FILES['hero_font_file']['type'], $file_mimes)) {
                    
                    #CREATE THE TEMP DIRECTORY
                        if (!is_dir($directory_name)) {
                            mkdir($directory_name);
                        }
                        #MOVE FILE TO TEMP FOLDER
                        $file_name = sprintf('%04x%04x%04x%04x%04x%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
                        $file = $directory_name . $file_name . '.zip';
                        move_uploaded_file($_FILES['hero_font_file']['tmp_name'], $file);

                        echo 'true';
                        wp_die();
                    } else {
                        echo 'false';
                        wp_die();
                    }
                }
            }
            echo $include;
            wp_die();
        }
        
        #CONSTRUCT SELECT STATEMENT
        public function hmenu_prefixed_table_fields_wildcard($table, $alias)
        {
            
            #GLOBALS
            global $wpdb;
            
            $columns = $wpdb->get_results("SHOW COLUMNS FROM $table", ARRAY_A);
            $field_names = array();
            foreach ($columns as $column) {
                $field_names[] = $column["Field"];
            }
            $prefixed = array();
            foreach ($field_names as $field_name) {
                $prefixed[] = "`{$alias}`.`{$field_name}` AS `{$alias}_{$field_name}`";
            }
            return implode(", ", $prefixed);
        }
        
        #MAIN OBJECT
        public function hmenu_get_main_menu_object($menu_id = null, $js = true)
        {
            
            #GLOBALS
            global $wpdb;
            
            #GET POST DATA
            $menu_to_fetch = $menu_id != null ? $menu_id : $_POST['id'];
                
            $result_small = $wpdb->get_results("
				SELECT					
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_menu', 'm') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_main_styles', 'ms') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_dropdown_styles', 'ds') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_styles', 'megs') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mobile_styles', 'mob') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_search', 'srch') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_font_styles', 'megf') ."
				FROM
					`". $wpdb->base_prefix ."hmenu_menu` `m`
					INNER JOIN `". $wpdb->base_prefix ."hmenu_main_styles` `ms` ON(`ms`.`menuId` = `m`.`menuId` AND `ms`.`deleted` = '0')
					INNER JOIN `". $wpdb->base_prefix ."hmenu_dropdown_styles` `ds` ON(`ds`.`menuId` = `m`.`menuId` AND `ds`.`deleted` = '0')
					INNER JOIN `". $wpdb->base_prefix ."hmenu_mega_styles` `megs` ON(`megs`.`menuId` = `m`.`menuId` AND `megs`.`deleted` = '0')	
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mobile_styles` `mob` ON(`mob`.`menuId` = `m`.`menuId` AND `mob`.`deleted` = '0')					
					INNER JOIN `". $wpdb->base_prefix ."hmenu_search` `srch` ON(`srch`.`menuId` = `m`.`menuId` AND `srch`.`deleted` = '0')
					INNER JOIN `". $wpdb->base_prefix ."hmenu_mega_font_styles` `megf` ON(`megf`.`megaStyleId` = `megs`.`megaStyleId` AND `megf`.`deleted` = '0')
				WHERE	
					`m`.`menuId` = ".$menu_to_fetch."
				AND				
					`m`.`deleted` = 0;
			", OBJECT);
            
            $result_big = $wpdb->get_results("
				SELECT
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_nav_items', 'ni') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_menu', 'mega') .",	
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_social', 'scl') .",					
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_blog', 'mega_blog') .",					
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_content', 'mega_cnt') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_contact', 'mega_tact') .",
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_map', 'mega_map') .",					
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_list', 'mega_lst') .",					
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_list_items', 'mega_lst_itm') .",					
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_image', 'mega_img') .",					
					". $this->hmenu_prefixed_table_fields_wildcard($wpdb->base_prefix .'hmenu_mega_product', 'mega_prod') ."
				FROM
					`". $wpdb->base_prefix ."hmenu_nav_items` `ni`
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_menu` `mega` ON(`mega`.`navItemId` = `ni`.`navItemId` AND `mega`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_social` `scl` ON(`scl`.`menuId` = `ni`.`menuId` AND `scl`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_blog` `mega_blog` ON(`mega_blog`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_blog`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_content` `mega_cnt` ON(`mega_cnt`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_cnt`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_contact` `mega_tact` ON(`mega_tact`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_tact`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_map` `mega_map` ON(`mega_map`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_map`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_list` `mega_lst` ON(`mega_lst`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_lst`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_list_items` `mega_lst_itm` ON(`mega_lst_itm`.`listId` = `mega_lst`.`listId` AND `mega_lst_itm`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_image` `mega_img` ON(`mega_img`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_img`.`deleted` = '0')
					LEFT JOIN `". $wpdb->base_prefix ."hmenu_mega_product` `mega_prod` ON(`mega_prod`.`megaMenuId` = `mega`.`megaMenuId` AND `mega_prod`.`deleted` = '0')
				WHERE	
					`ni`.`menuId` = ".$menu_to_fetch."
				AND				
					`ni`.`deleted` = 0
				ORDER BY
					`ni`.`order` ASC,
					`scl`.`order` ASC;
			", OBJECT);
            
            #CREATE OBJECT
            $menu_object = array(
                'menu' => array(),
                'main_styles' => array(),
                'dropdown_styles' => array(),
                'mega_styles' => array(),
                'mobile_styles' => array(),
                'mega_font_styles' => array(),
                'search_styles' => array(),
                'nav_items' => array(),
                'social_items' => array(),
                'all_menus' => array()
            );
            
            #GET ALL MENUS
            $menus = $wpdb->get_results("SELECT * FROM ". $wpdb->base_prefix ."hmenu_menu WHERE deleted = '0' ORDER BY created DESC");
            
            #PUSH ALL MENUS IN HERE, USED TO CHECK LOCATION SETTINGS IN INTEGRATION TAB
            if ($menus) {
                foreach ($menus as $menu) {
                    array_push($menu_object['all_menus'], array(
                        'name' => $menu->name,
                        'menuId' => $this->hmenu_convert_int($menu->menuId),
                        'overwrite' => $menu->overwrite
                    ));
                }
            }
            
            if ($result_small) {
                #POPULATE MENU NODE
                $menu_object['menu'] = array(
                    'status' => $this->hmenu_convert_int(1),
                    'menuId' => $this->hmenu_convert_int($result_small[0]->m_menuId),
                    'name' => $result_small[0]->m_name,
                    'autoLink' => $this->hmenu_convert_int($result_small[0]->m_autoLink),
                    'leftItems' => $result_small[0]->m_leftItems,
                    'centerItems' => $result_small[0]->m_centerItems,
                    'rightItems' => $result_small[0]->m_rightItems,
                    'customLink' => $result_small[0]->m_customLink,
                    'overwrite' => $result_small[0]->m_overwrite,
                    'created' => $result_small[0]->m_created
                );
                #MAIN STYLES NODE
                foreach ($result_small as $styles) {
                    $key = $this->hmenu_search_array($menu_object['main_styles'], 'mainStyleId', $styles->ms_mainStyleId);
                    if (!is_numeric($key)) {
                        #CREATE BEAUTIFUL JSON ARRAY OF MAIN STYLES OBJECT
                        array_push($menu_object['main_styles'], array(
                            'mainStyleId' => $styles->ms_mainStyleId,
                            'menuId' => $styles->ms_menuId,
                            'logo' => $this->hmenu_convert_int($styles->ms_logo),
                            'logoUrl' => $styles->ms_logoUrl,
                            'logoLink' => $styles->ms_logoLink,
                            'logoAlt' => $styles->ms_logoAlt,
                            'logoLinkTarget' => $styles->ms_logoLinkTarget,
                            'logoHeight' => $styles->ms_logoHeight,
                            'mobileLogo' => $this->hmenu_convert_int($styles->ms_mobileLogo),
                            'mobileLogoUrl' => $styles->ms_mobileLogoUrl,
                            'mobileLogoHeight' => $styles->ms_mobileLogoHeight,
                            'search' => $this->hmenu_convert_int($styles->ms_search),
                            'menu' => $this->hmenu_convert_int($styles->ms_menu),
                            'social' => $this->hmenu_convert_int($styles->ms_social),
                            'cart' => $this->hmenu_convert_int($styles->ms_cart),
                            'menuBarDimentions' => $styles->ms_menuBarDimentions,
                            'menuBarWidth' => $styles->ms_menuBarWidth,
                            'menuBarHeight' => $styles->ms_menuBarHeight,
                            'navBarDimentions' => $styles->ms_navBarDimentions,
                            'navBarWidth' => $styles->ms_navBarWidth,
                            'border' => $styles->ms_border,
                            'borderColor' => $styles->ms_borderColor,
                            'borderTransparency' => $styles->ms_borderTransparency,
                            'borderType' => $styles->ms_borderType,
                            'borderRadius' => $styles->ms_borderRadius,
                            'shadow' => $styles->ms_shadow,
                            'shadowRadius' => $styles->ms_shadowRadius,
                            'shadowColor' => $styles->ms_shadowColor,
                            'shadowTransparency' => $styles->ms_shadowTransparency,
                            'bgMenuStartColor' => $styles->ms_bgMenuStartColor,
                            'bgMenuGradient' => $styles->ms_bgMenuGradient,
                            'bgMenuEndColor' => $styles->ms_bgMenuEndColor,
                            'bgMenuGradientPath' => $styles->ms_bgMenuGradientPath,
                            'bgMenuTransparency' => $styles->ms_bgMenuTransparency,
                            'bgHoverStartColor' => $styles->ms_bgHoverStartColor,
                            'bgHoverType' => $styles->ms_bgHoverType,
                            'bgHoverGradient' => $styles->ms_bgHoverGradient,
                            'bgHoverEndColor' => $styles->ms_bgHoverEndColor,
                            'bgHoverGradientPath' => $styles->ms_bgHoverGradientPath,
                            'bgHoverTransparency' => $styles->ms_bgHoverTransparency,
                            'paddingLeft' => $styles->ms_paddingLeft,
                            'paddingRight' => $styles->ms_paddingRight,
                            'orientation' => $styles->ms_orientation,
                            'verticalWidth' => $styles->ms_verticalWidth,
                            'animation' => $styles->ms_animation,
                            'animationDuration' => $styles->ms_animationDuration,
                            'animationTrigger' => $styles->ms_animationTrigger,
                            'animationTimeout' => $styles->ms_animationTimeout,
                            'sticky' => $styles->ms_sticky,
                            'stickyLogoActive' => $styles->ms_stickyLogoActive,
                            'stickyUrl' => $styles->ms_stickyUrl,
                            'stickyActivate' => $styles->ms_stickyActivate,
                            'stickyHeight' => $styles->ms_stickyHeight,
                            'stickyFontColor' => $styles->ms_stickyFontColor,
                            'stickyFontHoverColor' => $styles->ms_stickyFontHoverColor,
                            'stickyFontSize' => $styles->ms_stickyFontSize,
                            'stickyFontSizing' => $styles->ms_stickyFontSizing,
                            'stickyFontWeight' => $styles->ms_stickyFontWeight,
                            'stickyFontHoverDecoration' => $styles->ms_stickyFontHoverDecoration,
                            'bgStickyStart' => $styles->ms_bgStickyStart,
                            'bgStickyEnd' => $styles->ms_bgStickyEnd,
                            'stickyTransparency' => $styles->ms_stickyTransparency,
                            'devider' => $styles->ms_devider,
                            'deviderTransparency' => $styles->ms_deviderTransparency,
                            'deviderColor' => $styles->ms_deviderColor,
                            'deviderSizing' => $styles->ms_deviderSizing,
                            'groupDevider' => $styles->ms_groupDevider,
                            'groupTransparency' => $styles->ms_groupTransparency,
                            'groupColor' => $styles->ms_groupColor,
                            'groupSizing' => $styles->ms_groupSizing,
                            'responsiveLabel' => $styles->ms_responsiveLabel,
                            'icons' => $styles->ms_icons,
                            'iconsColor' => $styles->ms_iconsColor,
                            'arrows' => $styles->ms_arrows,
                            'arrowTransparency' => $styles->ms_arrowTransparency,
                            'arrowColor' => $styles->ms_arrowColor,
                            'fontFamily' => $styles->ms_fontFamily,
                            'fontColor' => $styles->ms_fontColor,
                            'fontHoverColor' => $styles->ms_fontHoverColor,
                            'fontSize' => $styles->ms_fontSize,
                            'fontSizing' => $styles->ms_fontSizing,
                            'fontWeight' => $styles->ms_fontWeight,
                            'fontDecoration' => $styles->ms_fontDecoration,
                            'fontHoverDecoration' => $styles->ms_fontHoverDecoration,
                            'zindex'  => $styles->ms_zindex,
                            'preset'  => $styles->ms_preset,
                            'presetSlug'  => $styles->ms_presetSlug,
                            'iconProductSize'  => $styles->ms_iconProductSize,
                            'iconProductColor'  => $styles->ms_iconProductColor,
                            'iconProductHoverColor'  => $styles->ms_iconProductHoverColor,
                            'siteResponsive'  => $styles->ms_siteResponsive,
                            'siteResponsiveOne'  => $styles->ms_siteResponsiveOne,
                            'siteResponsiveTwo'  => $styles->ms_siteResponsiveTwo,
                            'siteResponsiveThree'  => $styles->ms_siteResponsiveThree,
                            'logoPaddingLeft'  => $styles->ms_logoPaddingLeft,
                            'mobileLogoPaddingLeft'  => $styles->ms_mobileLogoPaddingLeft,
                            'stickyLogoPaddingLeft'  => $styles->ms_stickyLogoPaddingLeft,
                            'bgMainImage' => $styles->ms_bgMainImage,
                            'bgMainImageUrl' => $styles->ms_bgMainImageUrl,
                            'bgMainImagePosition' => $styles->ms_bgMainImagePosition,
                            'bgMainImageRepeat' => $styles->ms_bgMainImageRepeat,
                            'customCss' => $styles->ms_customCss,
                            'logoPaddingRight'  => $styles->ms_logoPaddingRight,
                            'bgStickyHoverColor'  => $styles->ms_bgStickyHoverColor,
                            'eyebrow'  => $styles->ms_eyebrow,
                            'eyeExcerpt'  => $styles->ms_eyeExcerpt,
                            'eyeLoginUrl'  => $styles->ms_eyeLoginUrl,
                            'eyeBackground'  => $styles->ms_eyeBackground,
                            'eyeColor'  => $styles->ms_eyeColor,
                            'eyeColorHover'  => $styles->ms_eyeColorHover,
                            'eyePaddingLeft'  => $styles->ms_eyePaddingLeft,
                            'eyePaddingRight'  => $styles->ms_eyePaddingRight
                        ));
                    }
                }
                #DROPDOWN STYLES
                foreach ($result_small as $drop_styles) {
                    $key = $this->hmenu_search_array($menu_object['dropdown_styles'], 'dropStyleId', $drop_styles->ds_dropStyleId);
                    if (!is_numeric($key)) {
                        #CREATE BEAUTIFUL JSON ARRAY OF DROPDOWN STYLES OBJECT
                        array_push($menu_object['dropdown_styles'], array(
                            'dropStyleId' => $drop_styles->ds_dropStyleId,
                            'menuId' => $drop_styles->ds_menuId,
                            'widthType' => $drop_styles->ds_widthType,
                            'width' => $drop_styles->ds_width,
                            'padding' => $drop_styles->ds_padding,
                            'border' => $drop_styles->ds_border,
                            'borderColor' => $drop_styles->ds_borderColor,
                            'borderTransparency' => $drop_styles->ds_borderTransparency,
                            'borderType' => $drop_styles->ds_borderType,
                            'borderRadius' => $drop_styles->ds_borderRadius	,
                            'shadow' => $drop_styles->ds_shadow,
                            'shadowRadius' => $drop_styles->ds_shadowRadius,
                            'shadowColor' => $drop_styles->ds_shadowColor,
                            'shadowTransparency' => $drop_styles->ds_shadowTransparency,
                            'bgDropStartColor' => $drop_styles->ds_bgDropStartColor,
                            'bgDropGradient' => $drop_styles->ds_bgDropGradient,
                            'bgDropEndColor' => $drop_styles->ds_bgDropEndColor,
                            'bgDropGradientPath' => $drop_styles->ds_bgDropGradientPath,
                            'bgDropTransparency' => $drop_styles->ds_bgDropTransparency,
                            'bgHoverStartColor' => $drop_styles->ds_bgHoverStartColor,
                            'bgHoverGradient' => $drop_styles->ds_bgHoverGradient,
                            'bgHoverEndColor' => $drop_styles->ds_bgHoverEndColor,
                            'bgHoverGradientPath' => $drop_styles->ds_bgHoverGradientPath,
                            'bgHoverTransparency' => $drop_styles->ds_bgHoverTransparency,
                            'arrows' => $drop_styles->ds_arrows,
                            'arrowTransparency' => $drop_styles->ds_arrowTransparency,
                            'arrowColor' => $drop_styles->ds_arrowColor,
                            'devider' => $drop_styles->ds_devider,
                            'deviderTransparency' => $drop_styles->ds_deviderTransparency,
                            'deviderColor' => $drop_styles->ds_deviderColor,
                            'fontFamily' => $drop_styles->ds_fontFamily,
                            'fontColor' => $drop_styles->ds_fontColor,
                            'fontHoverColor' => $drop_styles->ds_fontHoverColor,
                            'fontSize' => $drop_styles->ds_fontSize,
                            'fontSizing' => $drop_styles->ds_fontSizing,
                            'fontWeight' => $drop_styles->ds_fontWeight,
                            'fontDecoration' => $drop_styles->ds_fontDecoration,
                            'fontHoverDecoration' => $drop_styles->ds_fontHoverDecoration
                        ));
                    }
                }
                #MEGA STYLES
                foreach ($result_small as $mega_styles) {
                    $key = $this->hmenu_search_array($menu_object['mega_styles'], 'megaStyleId', $mega_styles->megs_megaStyleId);
                    if (!is_numeric($key)) {
                        #CREATE BEAUTIFUL JSON ARRAY OF MEGA STYLES OBJECT
                        array_push($menu_object['mega_styles'], array(
                            'megaStyleId' => $mega_styles->megs_megaStyleId,
                            'menuId' => $mega_styles->megs_menuId,
                            'widthType' => $mega_styles->megs_widthType,
                            'width' => $mega_styles->megs_width,
                            'padding' => $mega_styles->megs_padding,
                            'border' => $mega_styles->megs_border,
                            'borderColor' => $mega_styles->megs_borderColor,
                            'borderTransparency' => $mega_styles->megs_borderTransparency,
                            'borderType' => $mega_styles->megs_borderType,
                            'borderRadius' => $mega_styles->megs_borderRadius,
                            'shadow' => $mega_styles->megs_shadow,
                            'shadowRadius' => $mega_styles->megs_shadowRadius,
                            'shadowColor' => $mega_styles->megs_shadowColor,
                            'shadowTransparency' => $mega_styles->megs_shadowTransparency,
                            'bgDropStartColor' => $mega_styles->megs_bgDropStartColor,
                            'bgDropGradient' => $mega_styles->megs_bgDropGradient,
                            'bgDropEndColor' => $mega_styles->megs_bgDropEndColor,
                            'bgDropGradientPath' => $mega_styles->megs_bgDropGradientPath,
                            'bgDropTransparency' => $mega_styles->megs_bgDropTransparency,
                            'bgHoverStartColor' => $mega_styles->megs_bgHoverStartColor,
                            'bgHoverGradient' => $mega_styles->megs_bgHoverGradient,
                            'bgHoverEndColor' => $mega_styles->megs_bgHoverEndColor,
                            'bgHoverGradientPath' => $mega_styles->megs_bgHoverGradientPath,
                            'bgHoverTransparency' => $mega_styles->megs_bgHoverTransparency,
                            'arrows' => $mega_styles->megs_arrows,
                            'arrowTransparency' => $mega_styles->megs_arrowTransparency,
                            'arrowColor' => $mega_styles->megs_arrowColor,
                            'devider' => $mega_styles->megs_devider,
                            'deviderTransparency' => $mega_styles->megs_deviderTransparency,
                            'deviderColor' => $mega_styles->megs_deviderColor,
                            'fontHoverColor' => $mega_styles->megs_fontHoverColor,
                            'fontHoverDecoration' => $mega_styles->megs_fontHoverDecoration,
                            'wooPriceColor' => $mega_styles->megs_wooPriceColor,
                            'wooPriceFamily' => $mega_styles->megs_wooPriceFamily,
                            'wooPriceWeight' => $mega_styles->megs_wooPriceWeight,
                            'wooPriceSize' => $mega_styles->megs_wooPriceSize,
                            'wooPriceSizing' => $mega_styles->megs_wooPriceSizing,
                            'wooPriceOldColor' => $mega_styles->megs_wooPriceOldColor,
                            'wooPriceOldFamily' => $mega_styles->megs_wooPriceOldFamily,
                            'wooPriceOldWeight' => $mega_styles->megs_wooPriceOldWeight,
                            'wooPriceOldSize' => $mega_styles->megs_wooPriceOldSize,
                            'wooPriceOldSizing' => $mega_styles->megs_wooPriceOldSizing,
                            'wooPriceSaleColor' => $mega_styles->megs_wooPriceSaleColor,
                            'wooPriceSaleFamily' => $mega_styles->megs_wooPriceSaleFamily,
                            'wooPriceSaleWeight' => $mega_styles->megs_wooPriceSaleWeight,
                            'wooPriceSaleSize' => $mega_styles->megs_wooPriceSaleSize,
                            'wooPriceSaleSizing' => $mega_styles->megs_wooPriceSaleSizing,
                            'wooBtnText' => $mega_styles->megs_wooBtnText,
                            'wooBtnFontFamily' => $mega_styles->megs_wooBtnFontFamily,
                            'wooBtnFontColor' => $mega_styles->megs_wooBtnFontColor,
                            'wooBtnFontSize' => $mega_styles->megs_wooBtnFontSize,
                            'wooBtnFontSizing' => $mega_styles->megs_wooBtnFontSizing,
                            'wooBtnFontWeight' => $mega_styles->megs_wooBtnFontWeight,
                            'wooBtnFontDecoration' => $mega_styles->megs_wooBtnFontDecoration
                        ));
                    }
                }
                #MOBILE STYLES
                foreach ($result_small as $mobile_styles) {
                    $key = $this->hmenu_search_array($menu_object['mobile_styles'], 'mobileStyleId', $mobile_styles->mob_mobileStyleId);
                    if (!is_numeric($key)) {
                        #CREATE BEAUTIFUL JSON ARRAY OF MEGA STYLES OBJECT
                        array_push($menu_object['mobile_styles'], array(
                            'mobileStyleId' => $mobile_styles->mob_mobileStyleId,
                            'menuId' => $mobile_styles->mob_menuId,
                            'bgBarStartColor' => $mobile_styles->mob_bgBarStartColor,
                            'bgBarGradient' => $mobile_styles->mob_bgBarGradient,
                            'bgBarEndColor' => $mobile_styles->mob_bgBarEndColor,
                            'bgBarGradientPath' => $mobile_styles->mob_bgBarGradientPath,
                            'bgBarTransparency' => $mobile_styles->mob_bgBarTransparency,
                            'fontBarFamily' => $mobile_styles->mob_fontBarFamily,
                            'fontBarColor' => $mobile_styles->mob_fontBarColor,
                            'fontBarHoverColor' => $mobile_styles->mob_fontBarHoverColor,
                            'fontBarSize' => $mobile_styles->mob_fontBarSize,
                            'fontBarSizing' => $mobile_styles->mob_fontBarSizing,
                            'fontBarWeight' => $mobile_styles->mob_fontBarWeight,
                            'bgMenuStartColor' => $mobile_styles->mob_bgMenuStartColor,
                            'bgMenuGradient' => $mobile_styles->mob_bgMenuGradient,
                            'bgMenuEndColor' => $mobile_styles->mob_bgMenuEndColor,
                            'bgMenuGradientPath' => $mobile_styles->mob_bgMenuGradientPath,
                            'bgMenuTransparency' => $mobile_styles->mob_bgMenuTransparency,
                            'bgHoverStartColor' => $mobile_styles->mob_bgHoverStartColor,
                            'bgHoverGradient' => $mobile_styles->mob_bgHoverGradient,
                            'bgHoverEndColor' => $mobile_styles->mob_bgHoverEndColor,
                            'bgHoverGradientPath' => $mobile_styles->mob_bgHoverGradientPath,
                            'bgHoverTransparency' => $mobile_styles->mob_bgHoverTransparency,
                            'fontMobileFamily' => $mobile_styles->mob_fontMobileFamily,
                            'fontMobileColor' => $mobile_styles->mob_fontMobileColor,
                            'fontMobileHoverColor' => $mobile_styles->mob_fontMobileHoverColor,
                            'fontMobileSize' => $mobile_styles->mob_fontMobileSize,
                            'fontMobileSizing' => $mobile_styles->mob_fontMobileSizing,
                            'fontMobileWeight' => $mobile_styles->mob_fontMobileWeight,
                            'fontTabletFamily' => $mobile_styles->mob_fontTabletFamily,
                            'fontTabletColor' => $mobile_styles->mob_fontTabletColor,
                            'fontTabletHoverColor' => $mobile_styles->mob_fontTabletHoverColor,
                            'fontTabletSize' => $mobile_styles->mob_fontTabletSize,
                            'fontTabletSizing' => $mobile_styles->mob_fontTabletSizing,
                            'fontTabletWeight' => $mobile_styles->mob_fontTabletWeight,
                            'paddingLeft' => $mobile_styles->mob_paddingLeft,
                            'paddingRight' => $mobile_styles->mob_paddingRight,
                            'menuBarHeightMobile' => $mobile_styles->mob_menuBarHeightMobile,
                            'menuBarbuttonSize' => $mobile_styles->mob_menuBarbuttonSize,
                            'menuBarbuttonPosition' => $mobile_styles->mob_menuBarbuttonPosition,
                            'menuBarbuttonColor' => $mobile_styles->mob_menuBarbuttonColor,
                            'menuBarMobileIcon' => $mobile_styles->mob_menuBarMobileIcon,
                            'menuBarMobileIconPaddingLeft' => $mobile_styles->mob_menuBarMobileIconPaddingLeft,
                            'menuBarMobileIconPaddingRight' => $mobile_styles->mob_menuBarMobileIconPaddingRight
                        ));
                    }
                }
                #MEGA FONT STYLES
                foreach ($result_small as $mega_font_styles) {
                    $key = $this->hmenu_search_array($menu_object['mega_font_styles'], 'megaFontId', $mega_font_styles->megf_megaFontId);
                    if (!is_numeric($key)) {
                        #CREATE BEAUTIFUL JSON ARRAY OF MEGA FONT STYLES OBJECT
                        array_push($menu_object['mega_font_styles'], array(
                            'megaFontId' => $mega_font_styles->megf_megaFontId,
                            'megaStyleId' => $mega_font_styles->megf_megaStyleId,
                            'type' => $mega_font_styles->megf_type,
                            'fontFamily' => $mega_font_styles->megf_fontFamily,
                            'fontColor' => $mega_font_styles->megf_fontColor,
                            'fontSize' => $mega_font_styles->megf_fontSize,
                            'fontSizing' => $mega_font_styles->megf_fontSizing,
                            'fontWeight' => $mega_font_styles->megf_fontWeight
                        ));
                    }
                }
                #SEARCH STYLES
                foreach ($result_small as $search_styles) {
                    $key = $this->hmenu_search_array($menu_object['search_styles'], 'searchId', $search_styles->srch_searchId);
                    if (!is_numeric($key)) {
                        #CREATE BEAUTIFUL JSON ARRAY OF MEGA STYLES OBJECT
                        array_push($menu_object['search_styles'], array(
                            'searchId' => $search_styles->srch_searchId,
                            'menuId' => $search_styles->srch_menuId,
                            'type' => $search_styles->srch_type,
                            'icon' => $search_styles->srch_icon,
                            'label' => $search_styles->srch_label,
                            'iconColor' => $search_styles->srch_iconColor,
                            'iconHoverColor' => $search_styles->srch_iconHoverColor,
                            'iconSize' => $search_styles->srch_iconSize,
                            'animation' => $search_styles->srch_animation,
                            'placement' => $search_styles->srch_placement,
                            'padding' => $search_styles->srch_padding,
                            'width' => $search_styles->srch_width,
                            'height' => $search_styles->srch_height,
                            'fontFamily' => $search_styles->srch_fontFamily,
                            'fontColor' => $search_styles->srch_fontColor,
                            'fontSize' => $search_styles->srch_fontSize,
                            'fontSizing' => $search_styles->srch_fontSizing,
                            'fontWeight' => $search_styles->srch_fontWeight,
                            'border' => $search_styles->srch_border,
                            'borderColor' => $search_styles->srch_borderColor,
                            'borderTransparency' => $search_styles->srch_borderTransparency,
                            'borderRadius' => $search_styles->srch_borderRadius,
                            'backgroundColor' => $search_styles->srch_backgroundColor,
                            'placeholder' => $search_styles->srch_placeholder
                        ));
                    }
                }
                
                #NAV ITEMS VARIABLES
                $count = 0;
                $nav_item_array = array();
                
                #NAV ITEMS
                foreach ($result_big as $nav_items) {
                    
                    #CREATE JSON ARRAY OF NAVIGATION ITEMS AND MEGA MENUS
                    if ($nav_items->ni_navItemId != null) {
                        if (!in_array($nav_items->ni_navItemId, $nav_item_array)) {
                            array_push($nav_item_array, $this->hmenu_convert_int($nav_items->ni_navItemId));
                                            
                            $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)] = array(
                                'navItemId' => $this->hmenu_convert_int($nav_items->ni_navItemId),
                                'parentNavId' => $this->hmenu_convert_int($nav_items->ni_parentNavId),
                                'postId' => $this->hmenu_convert_int($nav_items->ni_postId),
                                'title' => $nav_items->ni_title,
                                'active' => $nav_items->ni_active,
                                'activeMobile' => $nav_items->ni_activeMobile,
                                'name' => $nav_items->ni_name,
                                'icon' => $nav_items->ni_icon,
                                'iconContent' => $nav_items->ni_iconContent,
                                'iconColor' => $nav_items->ni_iconColor,
                                'iconSize' => $nav_items->ni_iconSize,
                                'link' => $nav_items->ni_link,
                                'target' => $nav_items->ni_target,
                                'order' => $this->hmenu_convert_int($nav_items->ni_order),
                                'type' => $nav_items->ni_type,
                                'level' => $this->hmenu_convert_int($nav_items->ni_level),
                                'new' => 0,
                                'status' => 0,
                                'deleted' => $this->hmenu_convert_int($nav_items->ni_deleted),
                                'mega_menus' => array(),
                                'method'  => $nav_items->ni_method,
                                'methodReference'  => $nav_items->ni_methodReference,
                                'cssClass'  => $nav_items->ni_cssClass,
                                'role'  => $nav_items->ni_role,
                                'roles'  => $nav_items->ni_roles
                            );
                        }
                    }
                }
                
                #SOCIAL ITEMS
                foreach ($result_big as $social_items) {
                    #CREATE JSON ARRAY OF NAVIGATION ITEMS AND MEGA MENUS
                    if ($social_items->scl_socialId != null) {
                        $key = $this->hmenu_search_array($menu_object['social_items'], 'socialId', $social_items->scl_socialId);
                        if (!is_numeric($key)) {
                            #CREATE BEAUTIFUL JSON ARRAY
                            array_push($menu_object['social_items'], array(
                                'socialId' => $social_items->scl_socialId,
                                'menuId' => $social_items->scl_menuId,
                                'name' => $social_items->scl_name,
                                'icon' => $social_items->scl_icon,
                                'iconContent' => $social_items->scl_iconContent,
                                'iconSize' => $social_items->scl_iconSize,
                                'iconColor' => $social_items->scl_iconColor,
                                'iconHoverColor' => $social_items->scl_iconHoverColor,
                                'link' => $social_items->scl_link,
                                'target' => $social_items->scl_target,
                                'order' => $this->hmenu_convert_int($social_items->scl_order),
                                'new' => 0,
                                'deleted' => $this->hmenu_convert_int($social_items->scl_deleted)
                            ));
                        }
                    }
                }
                
                #MEGA MENU
                foreach ($result_big as $nav_items) {
                    if ($nav_items->ni_navItemId != null) {
                        if ($nav_items->mega_megaMenuId != null && count($menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus']) < 1) {
                            if ($nav_items->mega_megaMenuId != null) {
                                array_push($menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'], array(
                                    'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_megaMenuId),
                                    'navItemId' => $this->hmenu_convert_int($nav_items->mega_navItemId),
                                    'name' => $nav_items->mega_name,
                                    'layout' => $nav_items->mega_layout,
                                    'background' => $nav_items->mega_background,
                                    'backgroundUrl' => $nav_items->mega_backgroundUrl,
                                    'backgroundPosition' => $nav_items->mega_backgroundPosition,
                                    'mega_blog' => array(),
                                    'mega_content' => array(),
                                    'mega_contact' => array(),
                                    'mega_map' => array(),
                                    'mega_image' => array(),
                                    'mega_product' => array(),
                                    'mega_list' => array(),
                                    'mega_stuff' => array(),
                                    'new' => 0,
                                    'status' => 0,
                                    'deleted_items' => array(),
                                    'deleted' => $this->hmenu_convert_int($nav_items->mega_deleted)
                                ));
                            }
                        }
                    }
                }
                
                #MEGA CONTENT
                foreach ($result_big as $nav_items) {
                    if ($nav_items->ni_navItemId != null) {
                        if (count($menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus']) > 0) {
                            
                            #CONTENT
                            if (!in_array($this->hmenu_convert_int($nav_items->mega_cnt_contentId), $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_content'])) {
                                if ($nav_items->mega_cnt_contentId != null) {
                                    $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_content'][$this->hmenu_convert_int($nav_items->mega_cnt_contentId)] = array(
                                        'id' => $this->hmenu_convert_int($nav_items->mega_cnt_contentId),
                                        'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_cnt_megaMenuId),
                                        'heading' => $nav_items->mega_cnt_heading,
                                        'headingUnderline' => $this->hmenu_convert_int($nav_items->mega_cnt_headingUnderline),
                                        'text' => $nav_items->mega_cnt_text,
                                        'textCount' => $this->hmenu_convert_int($nav_items->mega_cnt_textCount),
                                        'textAlignment' => $nav_items->mega_cnt_textAlignment,
                                        'paddingTop' => $this->hmenu_convert_int($nav_items->mega_cnt_paddingTop),
                                        'paddingBottom' => $this->hmenu_convert_int($nav_items->mega_cnt_paddingBottom),
                                        'placement' => $this->hmenu_convert_int($nav_items->mega_cnt_placement),
                                        'type' => $nav_items->mega_cnt_type,
                                        'deleted' => $this->hmenu_convert_int($nav_items->mega_cnt_deleted),
                                        'new' => 0
                                    );
                                }
                            }
                            
                            #BLOG
                            if (!in_array($this->hmenu_convert_int($nav_items->mega_blog_megaBlogId), $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_blog'])) {
                                if ($nav_items->mega_blog_megaBlogId != null) {
                                    $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_blog'][$this->hmenu_convert_int($nav_items->mega_blog_megaBlogId)] = array(
                                        'id' => $this->hmenu_convert_int($nav_items->mega_blog_megaBlogId),
                                        'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_blog_megaMenuId),
                                        'termId' => $this->hmenu_convert_int($nav_items->mega_blog_termId),
                                        'numberPosts' => $this->hmenu_convert_int($nav_items->mega_blog_numberPosts),
                                        'heading' => $nav_items->mega_blog_heading,
                                        'headingUnderline' => $this->hmenu_convert_int($nav_items->mega_blog_headingUnderline),
                                        'headingAllow' => $this->hmenu_convert_int($nav_items->mega_blog_headingAllow),
                                        'description' => $this->hmenu_convert_int($nav_items->mega_blog_description),
                                        'descriptionCount' => $this->hmenu_convert_int($nav_items->mega_blog_descriptionCount),
                                        'featuredImage' => $this->hmenu_convert_int($nav_items->mega_blog_featuredImage),
                                        'featuredSize' => $nav_items->mega_blog_featuredSize,
                                        'placement' => $this->hmenu_convert_int($nav_items->mega_blog_placement),
                                        'type' => $nav_items->mega_blog_type,
                                        'target' => $nav_items->mega_blog_target,
                                        'deleted' => $this->hmenu_convert_int($nav_items->mega_blog_deleted),
                                        'new' => 0
                                    );
                                }
                            }
                            
                            #CONTACT
                            if (!in_array($this->hmenu_convert_int($nav_items->mega_tact_contactId), $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_contact'])) {
                                if ($nav_items->mega_tact_contactId != null) {
                                    $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_contact'][$this->hmenu_convert_int($nav_items->mega_tact_contactId)] = array(
                                        'id' => $this->hmenu_convert_int($nav_items->mega_tact_contactId),
                                        'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_tact_megaMenuId),
                                        'heading' => $nav_items->mega_tact_heading,
                                        'headingUnderline' => $this->hmenu_convert_int($nav_items->mega_tact_headingUnderline),
                                        'html' => $this->hmenu_convert_int($nav_items->mega_tact_html),
                                        'formHtml' => $nav_items->mega_tact_formHtml,
                                        'shortcode' => $this->hmenu_convert_int($nav_items->mega_tact_shortcode),
                                        'formShortcode' => $nav_items->mega_tact_formShortcode,
                                        'placement' => $this->hmenu_convert_int($nav_items->mega_tact_placement),
                                        'sendToEmail' => $nav_items->mega_tact_sendToEmail,
                                        'sendUserEmail' => $nav_items->mega_tact_sendUserEmail,
                                        'sendBccEmail' => $nav_items->mega_tact_sendBccEmail,
                                        'sendCcEmail' => $nav_items->mega_tact_sendCcEmail,
                                        'theme' => $nav_items->mega_tact_theme,
                                        'labels' => $nav_items->mega_tact_labels,
                                        'image' => $nav_items->mega_tact_image,
                                        'footerContent' => $nav_items->mega_tact_footerContent,
                                        'type' => $nav_items->mega_tact_type,
                                        'deleted' => $this->hmenu_convert_int($nav_items->mega_tact_deleted),
                                        'new' => 0
                                    );
                                }
                            }
                            
                            #MAP
                            if (!in_array($this->hmenu_convert_int($nav_items->mega_map_mapId), $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_map'])) {
                                if ($nav_items->mega_map_mapId != null) {
                                    $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_map'][$this->hmenu_convert_int($nav_items->mega_map_mapId)] = array(
                                        'id' => $this->hmenu_convert_int($nav_items->mega_map_mapId),
                                        'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_map_megaMenuId),
                                        'heading' => $nav_items->mega_map_heading,
                                        'headingUnderline' => $this->hmenu_convert_int($nav_items->mega_map_headingUnderline),
                                        'map' => $this->hmenu_convert_int($nav_items->mega_map_map),
                                        'mapHtml' => $nav_items->mega_map_mapHtml,
                                        'shortcode' => $this->hmenu_convert_int($nav_items->mega_map_shortcode),
                                        'mapShortcode' => $nav_items->mega_map_mapShortcode,
                                        'placement' => $this->hmenu_convert_int($nav_items->mega_map_placement),
                                        'description' => $nav_items->mega_map_description,
                                        'type' => $nav_items->mega_map_type,
                                        'deleted' => $this->hmenu_convert_int($nav_items->mega_map_deleted),
                                        'new' => 0
                                    );
                                }
                            }
                            
                            #IMAGE
                            if (!in_array($this->hmenu_convert_int($nav_items->mega_img_imageId), $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_image'])) {
                                if ($nav_items->mega_img_imageId != null) {
                                    $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_image'][$this->hmenu_convert_int($nav_items->mega_img_imageId)] = array(
                                        'id' => $this->hmenu_convert_int($nav_items->mega_img_imageId),
                                        'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_img_megaMenuId),
                                        'heading' => $nav_items->mega_img_heading,
                                        'headingUnderline' => $this->hmenu_convert_int($nav_items->mega_img_headingUnderline),
                                        'placement' => $this->hmenu_convert_int($nav_items->mega_img_placement),
                                        'text' => $nav_items->mega_img_text,
                                        'url' => $nav_items->mega_img_url,
                                        'target' => $nav_items->mega_img_target,
                                        'image' => $nav_items->mega_img_image,
                                        'imageHeading' => $nav_items->mega_img_imageHeading,
                                        'displayType' => $nav_items->mega_img_displayType,
                                        'type' => $nav_items->mega_img_type,
                                        'deleted' => $this->hmenu_convert_int($nav_items->mega_img_deleted),
                                        'new' => 0
                                    );
                                }
                            }
                            
                            #PRODUCT
                            if (!in_array($this->hmenu_convert_int($nav_items->mega_prod_productId), $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_product'])) {
                                if ($nav_items->mega_prod_productId != null) {
                                    $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_product'][$this->hmenu_convert_int($nav_items->mega_prod_productId)] = array(
                                        'id' => $this->hmenu_convert_int($nav_items->mega_prod_productId),
                                        'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_prod_megaMenuId),
                                        'heading' => $nav_items->mega_prod_heading,
                                        'headingUnderline' => $this->hmenu_convert_int($nav_items->mega_prod_headingUnderline),
                                        'icon' => $nav_items->mega_prod_icon,
                                        'description' => $nav_items->mega_prod_description,
                                        'placement' => $this->hmenu_convert_int($nav_items->mega_prod_placement),
                                        'productCategory' => $nav_items->mega_prod_productCategory,
                                        'productToDisplay' => $nav_items->mega_prod_productToDisplay,
                                        'productHeading' => $this->hmenu_convert_int($nav_items->mega_prod_productHeading),
                                        'productPrice' => $this->hmenu_convert_int($nav_items->mega_prod_productPrice),
                                        'productDescription' => $this->hmenu_convert_int($nav_items->mega_prod_productDescription),
                                        'productImage' => $this->hmenu_convert_int($nav_items->mega_prod_productImage),
                                        'productLink' => $nav_items->mega_prod_productLink,
                                        'productTarget' => $nav_items->mega_prod_productTarget,
                                        'type' => $nav_items->mega_prod_type,
                                        'deleted' => $this->hmenu_convert_int($nav_items->mega_prod_deleted),
                                        'new' => 0
                                    );
                                }
                            }
                            
                            #LIST
                            if (!in_array($this->hmenu_convert_int($nav_items->mega_lst_listId), $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_list'])) {
                                if ($nav_items->mega_lst_listId != null) {
                                    $menu_object['nav_items'][$this->hmenu_convert_int($nav_items->ni_navItemId)]['mega_menus'][0]['mega_list'][$this->hmenu_convert_int($nav_items->mega_lst_listId)] = array(
                                        'id' => $this->hmenu_convert_int($nav_items->mega_lst_listId),
                                        'megaMenuId' => $this->hmenu_convert_int($nav_items->mega_lst_megaMenuId),
                                        'heading' => $nav_items->mega_lst_heading,
                                        'headingUnderline' => $this->hmenu_convert_int($nav_items->mega_lst_headingUnderline),
                                        'text' => $nav_items->mega_lst_text,
                                        'textCount' => $this->hmenu_convert_int($nav_items->mega_lst_textCount),
                                        'textAlignment' => $nav_items->mega_lst_textAlignment,
                                        'paddingTop' => $this->hmenu_convert_int($nav_items->mega_lst_paddingTop),
                                        'paddingBottom' => $this->hmenu_convert_int($nav_items->mega_lst_paddingBottom),
                                        'placement' => $this->hmenu_convert_int($nav_items->mega_lst_placement),
                                        'mega_list_items' => array(),
                                        'type' => $nav_items->mega_lst_type,
                                        'deleted' => $this->hmenu_convert_int($nav_items->mega_lst_deleted),
                                        'new' => 0
                                    );
                                    
                                    #FOREACH THE LIST ITEMS
                                    foreach ($result_big as $list_nav_items) {
                                        if ($list_nav_items->mega_lst_itm_listItemId != null) {
                                            if (!in_array($this->hmenu_convert_int($list_nav_items->mega_lst_itm_listItemId), $menu_object['nav_items'][$this->hmenu_convert_int($list_nav_items->ni_navItemId)]['mega_menus'][0]['mega_list'])) {
                                                if ($list_nav_items->mega_lst_itm_listItemId != null) {
                                                    if ($list_nav_items->mega_lst_itm_type != 'custom') {
                                                        $list_item_link = $this->hmenu_get_item_url($list_nav_items->mega_lst_itm_taxonomy, $list_nav_items->mega_lst_itm_postId, $list_nav_items->mega_lst_itm_termId);
                                                    } else {
                                                        $list_item_link = $list_nav_items->mega_lst_itm_url;
                                                    }
                                                    
                                                    $menu_object['nav_items'][$this->hmenu_convert_int($list_nav_items->ni_navItemId)]['mega_menus'][0]['mega_list'][$this->hmenu_convert_int($list_nav_items->mega_lst_listId)]['mega_list_items'][$this->hmenu_convert_int($list_nav_items->mega_lst_itm_listItemId)] = array(
                                                        'listItemId' => $this->hmenu_convert_int($list_nav_items->mega_lst_itm_listItemId),
                                                        'listId' => $this->hmenu_convert_int($list_nav_items->mega_lst_itm_listId),
                                                        'postId' => $this->hmenu_convert_int($list_nav_items->mega_lst_itm_postId),
                                                        'termId' => $this->hmenu_convert_int($list_nav_items->mega_lst_itm_termId),
                                                        'taxonomy' => $list_nav_items->mega_lst_itm_taxonomy,
                                                        'name' => $list_nav_items->mega_lst_itm_name,
                                                        'type' => $list_nav_items->mega_lst_itm_type,
                                                        'alt' => $list_nav_items->mega_lst_itm_alt,
                                                        'url' => $list_item_link,
                                                        'target' => $list_nav_items->mega_lst_itm_target,
                                                        'icon' => $list_nav_items->mega_lst_itm_icon,
                                                        'iconSize' => $list_nav_items->mega_lst_itm_iconSize,
                                                        'iconColor' => $list_nav_items->mega_lst_itm_iconColor,
                                                        'iconContent' => $list_nav_items->mega_lst_itm_iconContent,
                                                        'desc' => $list_nav_items->mega_lst_itm_desc,
                                                        'description' => $list_nav_items->mega_lst_itm_description,
                                                        'deleted' => $this->hmenu_convert_int($list_nav_items->mega_lst_itm_deleted),
                                                        'order' => $this->hmenu_convert_int($list_nav_items->mega_lst_itm_order),
                                                        'new' => 0
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                #SOMETHING WENT WRONG
                if ($js) {
                    echo json_encode(false);
                    exit;
                } else {
                    return false;
                }
            }
            
            #RE-INDEX ARRAY
            $menu_object['nav_items'] = array_values($menu_object['nav_items']);
            
            foreach ($menu_object['nav_items'] as $key => $nav_item) {
                if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_content'])) {
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_content'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_content']);
                }
                if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_blog'])) {
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_blog'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_blog']);
                }
                if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_contact'])) {
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_contact'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_contact']);
                }
                if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_map'])) {
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_map'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_map']);
                }
                if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_list'])) {
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_list'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_list']);
                    #RE-INDEX LIST ITEMS
                    foreach ($menu_object['nav_items'][$key]['mega_menus'][0]['mega_list'] as $list_key => $nav_list_item) {
                        if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_list'][$list_key]['mega_list_items'])) {
                            $menu_object['nav_items'][$key]['mega_menus'][0]['mega_list'][$list_key]['mega_list_items'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_list'][$list_key]['mega_list_items']);
                        }
                    }
                }
                if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_image'])) {
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_image'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_image']);
                }
                if (isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_product'])) {
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_product'] = array_values($menu_object['nav_items'][$key]['mega_menus'][0]['mega_product']);
                }
            }
            
            #MERGE
            foreach ($menu_object['nav_items'] as $key => $nav_item) {
                
                #ARRAY VARIABLES
                $blog_array = isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_blog']) ? $menu_object['nav_items'][$key]['mega_menus'][0]['mega_blog'] : array();
                $content_array = isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_content']) ? $menu_object['nav_items'][$key]['mega_menus'][0]['mega_content'] : array();
                $contact_array = isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_contact']) ? $menu_object['nav_items'][$key]['mega_menus'][0]['mega_contact'] : array();
                $map_array = isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_map']) ? $menu_object['nav_items'][$key]['mega_menus'][0]['mega_map'] : array();
                $list_array = isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_list']) ? $menu_object['nav_items'][$key]['mega_menus'][0]['mega_list'] : array();
                $image_array = isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_image']) ? $menu_object['nav_items'][$key]['mega_menus'][0]['mega_image'] : array();
                $product_array = isset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_product']) ? $menu_object['nav_items'][$key]['mega_menus'][0]['mega_product'] : array();
                                
                if ($menu_object['nav_items'][$key]['type'] == 'mega') {
                    #MERGE ARRAYS
                    $menu_object['nav_items'][$key]['mega_menus'][0]['mega_stuff'] = array_merge($blog_array, $content_array, $list_array, $contact_array, $map_array, $image_array, $product_array);
                } else {
                    unset($menu_object['nav_items'][$key]['mega_menus'][0]);
                }
                
                #UNSET ARRAYS
                unset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_blog']);
                unset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_content']);
                unset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_contact']);
                unset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_map']);
                unset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_list']);
                unset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_image']);
                unset($menu_object['nav_items'][$key]['mega_menus'][0]['mega_product']);
            }
            
            #JSON
            if ($js) {
                echo json_encode($menu_object);
                exit();
            } else {
                return $menu_object;
            }
        }
        
        #GET A LIST ITEMS URL
        public function hmenu_get_item_url($taxonomy, $post_id, $term_id)
        {
            
            #GLOBALS
            global $wpdb, $post;
            
            #SETUP POST DATA
            @setup_postdata($post);
            
            if ($taxonomy != '_na') {
                return (get_category_link($term_id));
            } else {
                return (get_permalink($post_id));
            }
        }
        
        #GET PAGES
        public function hmenu_get_pages()
        {
            
            #GLOBALS
            global $wpdb, $post;
            
            #SETUP POST DATA
            @setup_postdata($post);
            
            #PAGE ARGUMENTS
            $args = array(
                'post_type' => 'page',
                'numberposts' => -1,
                'orderby'=> 'title',
                'order'=>'DESC'
            );
            
            $the_pages = get_pages($args);
            
            #CREATE OBJECT
            $sidebar_object = array(
                'pages' => array(),
                'categories' => array(),
                'post_types' => array()
            );
            
            #JSON PAGES
            if ($the_pages) {
                foreach ($the_pages as $page) {
                    array_push($sidebar_object['pages'], array(
                        'id' => $page->ID,
                        'title' => $page->post_title,
                        'status' => $page->post_status,
                        'menu' => $page->menu_order,
                        'guid' => $page->guid,
                        'perma' => get_permalink($page->ID)
                    ));
                }
            }
            
            $cat_args = array(
                'orderby' => 'name',
                'order' => 'DESC',
                'taxonomy' => 'category'
            );
            
            $the_categories = get_categories($cat_args);
            
            #JSON CATEGORIES
            if ($the_categories) {
                foreach ($the_categories as $cat) {
                    array_push($sidebar_object['categories'], array(
                        'id' => $cat->term_id,
                        'title' => $cat->name,
                        'slug' => $cat->slug,
                        'taxonomy' => $cat->taxonomy,
                        'cat_id' => $cat->cat_ID,
                        'link' => get_category_link($cat->term_id)
                    ));
                }
            }
            
            $type_args = array(
               'public'   => true,
               '_builtin' => false
            );
            
            $post_types = get_post_types($type_args, 'objects');
            
            $count = 0;
            
            #JSON POST TYPES
            if ($post_types) {
                foreach ($post_types as $type) {
                    
                    #TYPE POST ARGUMENTS
                    $post_args = array(
                        'post_type' => $type->name,
                        'numberposts' => -1,
                        'orderby'=> 'title',
                        'order'=>'DESC'
                    );
                    
                    #GET POSTS
                    $type_posts = get_posts($post_args);
                    
                    array_push($sidebar_object['post_types'], array(
                        'name' => $type->name,
                        'icon' => $type->menu_icon,
                        'label' => $type->label,
                        'posts' => array(),
                        'type_categories' => array()
                    ));
                    
                    #PUSH IN POSTS
                    if ($type_posts) {
                        foreach ($type_posts as $tp) {
                            array_push($sidebar_object['post_types'][$count]['posts'], $tp);
                        }
                    }
                    
                    #GET POST TYPE CATEGORIES
                    $taxonomy_objects = get_object_taxonomies($type->name, 'objects');
                    
                    #PUSH IN CUSTOM TAXONOMIES
                    if ($taxonomy_objects) {
                        $tax_count = 0;
                        
                        foreach ($taxonomy_objects as $custom_tax) {
                            if ($custom_tax->query_var == "product_tag" || $custom_tax->query_var == "product_shipping_class" || $custom_tax->query_var == "product_type") {
                                //do nothing
                            } else {
                                array_push($sidebar_object['post_types'][$count]['type_categories'], array(
                                    'name' => $custom_tax->name,
                                    'label' => $custom_tax->label,
                                    'var' => $custom_tax->query_var,
                                    'terms' => array()
                                ));

                            
                                #CHECK TERMS
                                $custom_cat_args = array(
                                    'orderby' => 'name',
                                    'order' => 'DESC',
                                    'taxonomy' => $custom_tax->name
                                );

                                $the_custom_categories = get_categories($custom_cat_args);

                                #PUSH IN TERMS
                                if ($taxonomy_objects) {
                                    foreach ($the_custom_categories as $custom_cat) {
                                        array_push($sidebar_object['post_types'][$count]['type_categories'][$tax_count]['terms'], $custom_cat);
                                    }
                                }

                                $tax_count++;
                            }
                        }
                    }
                    
                    $count++;
                }
            }
            
            echo json_encode($sidebar_object);
            exit();
        }
    }
