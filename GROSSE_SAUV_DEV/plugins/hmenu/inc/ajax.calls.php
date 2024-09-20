<?php

    /*
        notes:
        ------
        - All actions are prefixed by the plugin prefix
            e.g. if the plugin prefix is "hplugin_" and the action name is "get_data", the actions, as referenced by the ajax call, will be "hplugin_get_data"
        - Ensure that all "actions" are unique
        - User registrations are registered for administrators as well to ensure that functionality remains the same if logged in
    */

    #ADMIN AJAX CALLS
    $backend_ajax_calls = array( //all methods must be contained by the backend class
        //font load
        array('action' => 'load_fonts','method' => 'hmenu_get_fonts'),
        //validate mega menu
        array('action' => 'load_validate_mega','method' => 'hmenu_validate_mega'),
        //validate custom nav items
        array('action' => 'load_validate_custom','method' => 'hmenu_validate_custom'),
        //validate custom nav items
        array('action' => 'load_validate_custom_method','method' => 'hmenu_validate_custom_method'),
        //get users
        array('action' => 'get_users','method' => 'hmenu_get_users')
    );
    $class_update_ajax_calls = array( //all methods must be contained by the backend class
        //update object
        array('action' => 'send_update_object','method' => 'hmenu_update_object'),
        //update object
        array('action' => 'run_delete_menu','method' => 'hmenu_delete_menu')
    );
    $class_insert_ajax_calls = array( //all methods must be contained by the backend class
        //menu insert
        array('action' => 'transfer_menu','method' => 'hmenu_insert_menu')
    );
    $class_get_ajax_calls = array( //all methods must be contained by the backend class
        //menu load
        array('action' => 'load_menus','method' => 'hmenu_get_menus'),
        //main menu object
        array('action' => 'load_menu_object','method' => 'hmenu_get_main_menu_object'),
        //main menu object
        array('action' => 'load_presets','method' => 'hmenu_get_presets'),
        //main menu object
        array('action' => 'load_pages','method' => 'hmenu_get_pages'),
        //main locations
        array('action' => 'load_locations','method' => 'hmenu_get_menu_locations'),
        //standard menu item html content
        array('action' => 'load_menu_item_html','method' => 'hmenu_load_menu_item_html'),
        //mega menu html content
        array('action' => 'load_mega_menu_item_html','method' => 'hmenu_load_mega_menu_item_html'),
        //mega menu list item html content
        array('action' => 'load_mega_menu_list_item_html','method' => 'hmenu_load_mega_menu_list_item_html'),
        //menu social item html content
        array('action' => 'load_menu_social_item_html','method' => 'hmenu_load_menu_social_item_html'),
        //icon upload
        array('action' => 'upload_menu_icon_pack','method' => 'hmenu_upload_menu_icon_pack')
    );
    $class_generate_ajax_calls = array( //all methods must be contained by the backend class
        //generate files
        array('action' => 'generate','method' => 'hmenu_generate_files')
    );
    
    #USER AJAX CALLS
    $frontend_ajax_calls = array( //all methods must be contained by the frontend class
        //font load
        array('action' => 'load_frontend_fonts','method' => 'hmenu_get_frontend_fonts'),
        //check menu status
        array('action' => 'check_menu_status','method' => 'hmenu_check_menu_status'),
        //mega content load
        array('action' => 'load_frontend_mega','method' => 'get_mega_content'),
        //get count
        array('action' => 'get_count','method' => 'hmenu_get_count')
    );
