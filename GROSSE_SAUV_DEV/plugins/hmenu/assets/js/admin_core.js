'use strict';
/*
	BACK-END CORE
	notes: loaded on every plugin back-end view (available to this plugin only)
*/

//GLOBALS
//admin core
var hmenu_menu_object;
var hmenu_menu_config;
var hmenu_menu_icon_path = 'assets/icons/';
var hmenu_save_required = false;
var hmenu_header_loaded_watch = false;
var hmenu_header_loaded_timer;
var hmenu_cur_sub_view;



//CORE EVENT LISTENERS
//load
jQuery(function () {
	hmenu_maintain_panel_structure(); //maintain panel structure
	hmenu_load_json_menu_object(); //load the json menu object
	hmenu_delegate_sidebar_dropdown_menu_animation(); //delegate sidebar dropdown menu animation
	hmenu_maintain_popup_size_position(); //maintain popup size and position
});
//window resize
jQuery(window).on('resize', function () {
	hmenu_maintain_panel_structure(); //maintain panel structure
	hmenu_maintain_popup_size_position(); //maintain popup size and position
});
//unload
jQuery(window).on('beforeunload', function () {
	return hmenu_check_save_required();
});



//PANEL
//maintain panel structure
function hmenu_maintain_panel_structure() {
	//get window dimensions
	var hmenu_window_height = jQuery(window).height();
	//get wordpress core dimensions
	var hmenu_wpadminbar_height = jQuery('#wpadminbar').height();
	//hero_admin
	jQuery('.hero_admin').css({
		'min-height': (hmenu_window_height - hmenu_wpadminbar_height) + 'px'
	});
}



//MENU
//load the json menu object
function hmenu_load_json_menu_object() {
	jQuery.getJSON(hmenu_plugin_url + 'menu/menu_object.js', function (data) {
		//attach to global menu object(s)
		hmenu_menu_object = data.menu.structure;
		hmenu_menu_config = data.menu.config;
		//check menu config
		hmenu_check_menu_configuration();
		//iterate the menu object
		hmenu_iterate_menu_object();
	});
}
//check menu configuration
function hmenu_check_menu_configuration() {
	//check if development more is active
	if (hmenu_menu_config.development_mode) {
		//auto-generate views based on menu structure
		jQuery.ajax({
			url: hmenu_ajax_url,
			type: "POST",
			data: {
				'action': plugin_name + '_autoGenerateViews',
				'menu_object': hmenu_menu_object
			},
			dataType: "json"
		});
	}
}
//iterate the menu object and construct the menu(s)
function hmenu_iterate_menu_object() {
	var hmenu_first_item = true;
	jQuery.each(hmenu_menu_object, function (key, val) {
		//add root item to sidebar
		hmenu_add_root_item_to_sidebar(key, hmenu_first_item);
		hmenu_first_item = false;
	});
	//prepopulate sidebar elements
	if (typeof hmenu_prepopulate_sidebar_elements == 'function') {
		hmenu_prepopulate_sidebar_elements();
	}
}
//add root item to sidebar
function hmenu_add_root_item_to_sidebar(key, first_item) {
	//get menu item
	var hmenu_menu_item = hmenu_menu_object[key];
	//construct menu item html
	var hmenu_item_html = '';
	switch (jQuery(hmenu_menu_item).attr('type')) {
		//link
		case 'link':
			if (hmenu_menu_item.show_in_sidebar) {
				hmenu_item_html += '<div id="' + hmenu_menu_item.id + '" class="hero_sidebar_item" onclick="hmenu_load_core_view(' + key + ', \'' + hmenu_menu_item.id + '\', \'' + hmenu_menu_item.title + '\', \'' + hmenu_menu_item.viewpath + '\',undefined,undefined, ' + hmenu_menu_item.header.auto_generate + ', ' + hmenu_menu_item.header.show_save + ');">';
				hmenu_item_html += '<div class="hero_sidebar_parent">';
				hmenu_item_html += '<div class="hero_sidebar_icon" style="background-image:url(' + hmenu_plugin_url + hmenu_menu_icon_path + hmenu_menu_item.icon + '.png)"></div>';
				hmenu_item_html += '<div class="hero_sidebar_label">' + hmenu_menu_item.title + '</div>';
				hmenu_item_html += '</div>';
				hmenu_item_html += '</div>';
			}
			break;
		//dropdown
		case 'dropdown':
			hmenu_item_html += '<div id="' + hmenu_menu_item.id + '" class="hero_sidebar_item" data-visible="hidden">';
			hmenu_item_html += '<div class="hero_sidebar_parent hero_sidebar_dropdown_item">';
			hmenu_item_html += '<div class="hero_sidebar_icon" style="background-image:url(' + hmenu_plugin_url + hmenu_menu_icon_path + hmenu_menu_item.icon + '.png)"></div>';
			hmenu_item_html += '<div class="hero_sidebar_label">' + hmenu_menu_item.title + '</div>';
			hmenu_item_html += '<div class="_dropdown_arrow hero_arrow_open"></div>';
			hmenu_item_html += '</div>';
			hmenu_item_html += '<div class="hero_sub">';
			//add submenu items
			jQuery.each(hmenu_menu_item.submenu, function (key, val) {
				switch (jQuery(val).attr('type')) {
					//holder
					case 'holder':
						hmenu_item_html += '<ul class="' + val.id + '">';
						hmenu_item_html += '</ul>';
						break;
					//button
					case 'button':
						hmenu_item_html += '<div class="hero_sidebar_button rounded_3 hero_white" id="' + val.id + '">';
						hmenu_item_html += val.title;
						hmenu_item_html += '</div>';
						break;
				}
			});
			hmenu_item_html += '</div>';
			hmenu_item_html += '</div>';
			hmenu_item_html += '';
			hmenu_item_html += '';
			hmenu_item_html += '';
			hmenu_item_html += '';
			break;
		//button	
		case 'button':
			hmenu_item_html += '<div class="hero_sidebar_button rounded_3 hero_white" id="' + hmenu_menu_item.id + '">';
			hmenu_item_html += hmenu_menu_item.title;
			hmenu_item_html += '</div>';
			break;
	}
	//append sidebar content
	jQuery('.hero_sidebar .hero_sidebar_nav').append(hmenu_item_html);
	//preselect first item view
	if (first_item) {
		hmenu_load_core_view(key, hmenu_menu_item.id, hmenu_menu_item.title, hmenu_menu_item.viewpath, undefined, undefined, hmenu_menu_item.header.auto_generate, hmenu_menu_item.header.show_save);
	}
}
//delegate sidebar dropdown menu animation
function hmenu_delegate_sidebar_dropdown_menu_animation() {
	jQuery('.hero_main').on('click', '.hero_sidebar_dropdown_item', function () {
		//get height of menu item
		var hmenu_sidebar_item_height = jQuery('.hero_sidebar_item').height();
		//check if open
		if (jQuery(this).parent().data('visible') == 'hidden') { //show
			//get content height
			var hmenu_sub_item_height = jQuery(this).parent().children('.hero_sub').height();
			jQuery(this).parent().stop().animate({
				'height': (hmenu_sidebar_item_height + hmenu_sub_item_height) + 'px'
			}, 500, function () {
				jQuery(this).children('.hero_sidebar_parent').children('._dropdown_arrow').removeClass('hero_arrow_open').addClass('hero_arrow_close');
			}).data('visible', 'visible');
		} else { //hide
			jQuery(this).parent().stop().animate({
				'height': hmenu_sidebar_item_height + 'px'
			}, 500, function () {
				jQuery(this).children('.hero_sidebar_parent').children('._dropdown_arrow').removeClass('hero_arrow_close').addClass('hero_arrow_open');
			}).data('visible', 'hidden');
		}
	});
}



//CORE VIEW
//load core view
var hmenu_last_loaded_core_view;
function hmenu_load_core_view(key, id, title, viewpath, json, callback, header, show_save, dropdown_id) {

	//close all dropdown menu(s)
	jQuery('.hero_arrow_close').each(function () {
		if (jQuery(this).parent().parent().attr('id') != dropdown_id) {
			jQuery(this).parent().trigger('click');
		}
	});

	//check if loacked
	if (hmenu_last_loaded_core_view != id) {
		//lock core view
		hmenu_lock_core_view_reload(id);
		hmenu_header_loaded_watch = false;
		var hmenu_allow_nav = true;
		if (hmenu_save_required) {
			var hmenu_allow_nav = confirm('Please note that you have unsaved data. If you leave this page, you will lose all changes that took place after your last save. Click OK to leave this page and CANCEL to stay on this page.');
		}
		if (hmenu_allow_nav) {
			hmenu_cur_sub_view = undefined;
			//reset save required
			hmenu_remove_save_required();
			//empty core view
			jQuery('.hero_admin').empty();
			//append loader
			jQuery('.hero_admin').append('<div class="loader"><div>loading ' + title.toLowerCase() + ' core...</div></div>');
			//set active class
			jQuery('.hero_sidebar_item').removeClass('hero_main_active');
			jQuery('#' + id).addClass('hero_main_active');
			jQuery('.hero_sub ul li').removeClass('active_sidebar_elem');
			//load core
			jQuery('.hero_admin').load(hmenu_core_view_path + 'views/' + viewpath + 'index.html?p=' + hmenu_core_view_path + '&v=' + hmenu_core_view_path + 'views/' + viewpath, function () {
				if (typeof callback !== 'undefined' && callback !== 'undefined' && typeof json !== 'undefined' && json !== 'undefined') {
					eval("" + callback + "(hmenu_extract_json_object('" + json + "'));");
				}
				if (header) {
					hmenu_generate_view_header(key, show_save);
				}
				if (hmenu_menu_object[key].auto_load_subview) {
					hmenu_load_view_submenu(key, 0);
				}
				//switch components
				hmenu_switch_components();
				//remove loader
				jQuery('.hero_admin .loader').remove();
			});
		}
	}
}
//load core view manually
function hmenu_manual_load_core_view(id, json_object, callback) {
	//get menu key
	var key;
	jQuery.each(hmenu_menu_object, function (idx, val) {
		if (val.id == id) {
			key = idx;
			return false;
		}
	});
	//load core view
	hmenu_load_core_view(key, hmenu_menu_object[key].id, hmenu_menu_object[key].title, hmenu_menu_object[key].viewpath, encodeURIComponent(JSON.stringify(json_object)), callback, hmenu_menu_object[key].header.auto_generate, hmenu_menu_object[key].header.show_save);
}
//lock core view reload (reload blocked)
function hmenu_lock_core_view_reload(id) {
	hmenu_last_loaded_core_view = id;
}
//unlock core view reload (reload allowed)
function hmenu_unlock_core_view_reload() {
	hmenu_last_loaded_core_view = undefined;
}
//json extractor
function hmenu_extract_json_object(json) {
	if (json !== 'undefined') {
		return JSON.parse(decodeURIComponent(json));
	}
	return false;
}
//load view manually
function hmenu_manual_load_view(core_view_id) {
	clearTimeout(hmenu_header_loaded_timer);
	jQuery('.hero_viewport').append('<div class="loader"><div>loading view...</div></div>');
	if (hmenu_header_loaded_watch) {
		//get menu key
		var key;
		jQuery.each(hmenu_menu_object, function (idx, val) {
			if (val.id == core_view_id) {
				key = idx;
				return false;
			}
		});
		hmenu_header_loaded_watch = false;
		hmenu_load_view_submenu(key, 0);
	} else {
		hmenu_header_loaded_timer = setTimeout(function () {
			hmenu_manual_load_view(core_view_id);
		}, 100);
	}
}



//SUB-VIEWS
//load subview
var hmenu_view_load_lock = false;
function hmenu_load_sub_view(id, viewpath, view) {
	if (view != hmenu_cur_sub_view) {
		hmenu_cur_sub_view = view;
		//trigger navigation event
		hmenu_trigger_hplugin_event('view-nav');
		//fade out view
		jQuery('.hero_viewport').fadeOut(100, function () {
			//empty view
			jQuery(this).empty();
			//append loader and fade in
			jQuery('.hero_viewport').append('<div class="loader"><div>loading view...</div></div>').fadeIn(200);
			//load core
			jQuery('.hero_viewport').load(hmenu_core_view_path + 'views/' + viewpath + view + '.view.html?vp=' + hmenu_core_view_path + 'views/' + viewpath, function () {
				//switch components if required
				jQuery.each(hmenu_menu_object, function (key, val) {
					if (val.viewpath == viewpath) {
						jQuery.each(val.views, function (key, val) {
							jQuery.each(val.submenu, function (key, val) {
								if (val.auto_load_components) {
									hmenu_switch_components();
									return false;
								}
								return false;
							});
						});
					}
				});
				//remove loader
				jQuery('.hero_viewport .loader').remove();
			});
			jQuery('#hero_submenu_nav li').removeClass('top_sub_active');
			jQuery('#link_' + id).addClass('top_sub_active');
		});
	}
}
function hmenu_reload_sub_view(id, viewpath, view) {
	hmenu_cur_sub_view = view;
	//fade out view
	jQuery('.hero_viewport').fadeOut(100, function () {
		//empty view
		jQuery(this).empty();
		//append loader and fade in
		jQuery('.hero_viewport').append('<div class="loader"><div>loading view...</div></div>').fadeIn(200);
		//load core
		jQuery('.hero_viewport').load(hmenu_core_view_path + 'views/' + viewpath + view + '.view.html?vp=' + hmenu_core_view_path + 'views/' + viewpath, function () {
			//switch components if required
			jQuery.each(hmenu_menu_object, function (key, val) {
				if (val.viewpath == viewpath) {
					jQuery.each(val.views, function (key, val) {
						jQuery.each(val.submenu, function (key, val) {
							if (val.auto_load_components) {
								hmenu_switch_components();
								return false;
							}
							return false;
						});
					});
				}
			});
			//remove loader
			jQuery('.hero_viewport .loader').remove();
		});
		jQuery('#hero_submenu_nav li').removeClass('top_sub_active');
		jQuery('#link_' + id).addClass('top_sub_active');
	});
}



//HEADERS
//generate view header
function hmenu_generate_view_header(key, show_save) {
	hmenu_header_loaded_watch = false;
	var first_item;
	var header_html = '';
	header_html += '<div class="hero_top">';
	header_html += '<div class="hero_top_menu">';
	jQuery.each(hmenu_menu_object[key].views, function (idx, val) {
		header_html += '<div class="hero_top_main" data-view="label" id="' + val.id + '_btn" onclick="hmenu_load_view_submenu(' + key + ', ' + idx + ');">'; //menu here -> hero_top_active
		header_html += '<div class="hero_top_icon" style="background-image:url(' + hmenu_plugin_url + 'assets/icons/' + val.icon + '.png)"></div>';
		header_html += '<div class="hero_top_label">' + val.title + '</div>';
		header_html += '<div class="hero_active_arrow"></div>';
		header_html += '</div>';
		first_item = false;
	});
	header_html += '</div>';
	header_html += '<div class="hero_top_info">';
	header_html += '<div class="hero_dark size_12" id="hero_header_label"></div>';
	header_html += '<div class="hero_white size_20" id="hero_header_title"></div>';
	header_html += '</div>';
	header_html += '<div class="hero_top_status">';
	if (show_save) {
		var disabled = 'hero_btn_disable';
		if (hmenu_save_required) {
			disabled = '';
		}
		header_html += '<div class="hero_button rounded_3 ' + disabled + ' save_button">SAVE</div>';
	}
	header_html += '</div>';
	header_html += '</div>';
	header_html += '<div class="hero_top_sub_nav size_12 hero_white">';
	header_html += '<ul id="hero_submenu_nav">';
	header_html += '</ul>';
	header_html += '</div>';
	jQuery('.hero_viewport').before(header_html);
	hmenu_header_loaded_watch = true;
	hmenu_set_current_header_label(hmenu_menu_object[key].header.header_label, hmenu_menu_object[key].header.header_title);
}
//set header label and title
function hmenu_set_current_header_label(label, title) {
	jQuery('#hero_header_label').html(label.toUpperCase());
	jQuery('#hero_header_title').html(title.toUpperCase());
}
//load view submenu
function hmenu_load_view_submenu(key, idx) {
	jQuery('#hero_submenu_nav').empty();
	var first_item = true;
	//remove active state from current
	jQuery('.hero_top_main').removeClass('hero_top_active');
	jQuery.each(hmenu_menu_object[key].views[idx].submenu, function (index, val) {
		jQuery('#hero_submenu_nav').append('<li id="link_' + val.id + '" onclick="hmenu_load_sub_view(\'' + val.id + '\', \'' + hmenu_menu_object[key].viewpath + '\',\'' + val.view + '\');">' + val.title + '</li>');
		if (first_item) {
			//load first view
			hmenu_load_sub_view(val.id, hmenu_menu_object[key].viewpath + '', val.view);
		}
		first_item = false;
	});
	jQuery('#' + hmenu_menu_object[key].views[idx].id + '_btn').addClass('hero_top_active');
}



//SIDEBAR
//add sidbar element
function hmenu_add_sidebar_element(dropdown_id, elem_id, title, json, callback) {
	jQuery('#' + dropdown_id + ' .hero_sub ul').append('<li id="sub_item_row_' + elem_id + '" data-json="' + encodeURIComponent(JSON.stringify(json)) + '" onclick="hmenu_load_sidebar_dropdown_view(jQuery(this),\'' + dropdown_id + '\',\'' + callback + '\');">' + title + '</li>');
}
//load sidebar dropdown view
function hmenu_load_sidebar_dropdown_view(id, dropdown_id, callback) {
	//open dropdown menu
	if (jQuery('#' + dropdown_id).children('.hero_sidebar_parent').children('._dropdown_arrow').hasClass('hero_arrow_open')) {
		jQuery('#' + dropdown_id).children('.hero_sidebar_parent').trigger('click');
	}
	//get menu key
	var key;
	jQuery.each(hmenu_menu_object, function (idx, val) {
		if (val.id == dropdown_id) {
			key = idx;
			return false;
		}
	});
	//load core view
	hmenu_load_core_view(key, hmenu_menu_object[key].id, hmenu_menu_object[key].title, hmenu_menu_object[key].viewpath, id.data('json'), callback, hmenu_menu_object[key].header.auto_generate, hmenu_menu_object[key].header.show_save, dropdown_id);
}



//SAVE MANAGEMENT
//check save required
function hmenu_check_save_required() {
	if (hmenu_save_required) {
		return 'Please note that you have unsaved data. If you leave this page, you will lose all changes that took place after your last save.';
	}
}
//flag save required
function hmenu_flag_save_required(callback, json) {
	json = encodeURIComponent(JSON.stringify(json));
	//flag required
	hmenu_save_required = true;
	//update button to red
	jQuery('.save_button').removeClass('hero_btn_disable');
	//reset save delegate
	jQuery('.hero_admin').off('click', '.save_button');
	//bind event listener
	jQuery('.hero_admin').on('click', '.save_button', function () {
		hmenu_remove_save_required();
		if (typeof callback !== 'undefined') {
			eval("" + callback + "(hmenu_extract_json_object('" + json + "'));");
		}
	});
}
//remove save required
function hmenu_remove_save_required() {
	//flag not required
	hmenu_save_required = false;
	//reset save delegate
	jQuery('.hero_admin').off('click', '.save_button');
	//update button to red
	jQuery('.save_button').addClass('hero_btn_disable');
}



//CORE CONSOLE LOGGING WRAPPER
//console.log core replacement
function hmenu_console_log(msg) {
	if (hmenu_menu_config.development_mode) {
		console.log(msg);
	}
}



//CORE MESSAGING SYSTEM
//show message
var hmenu_message_id = 0;
function hmenu_show_message(type, title, message) {
	hmenu_message_id++;
	var message_html = '<div id="hero_message_' + hmenu_message_id + '" class="hero_' + type + ' rounded_3">';
	message_html += '<h5 class="size_14">' + title + '</h5>';
	message_html += '<span class="size_12">' + message + '</span>';
	message_html += '</div>';
	jQuery('.hero_message_status').append(message_html);
	jQuery('#hero_message_' + hmenu_message_id).animate({
		'opacity': 1,
		'margin-bottom': 10 + 'px'
	}, 700, function () {
		jQuery(this).delay(4000).animate({
			'opacity': 0,
			'margin-bottom': 20 + 'px'
		}, 700, function () {
			jQuery(this).remove();
		});
	});
}



//CORE POPUP MANAGEMENT
//maintain popup size and position
function hmenu_maintain_popup_size_position() {
	var hero_width = jQuery(window).width();
	var hero_height = jQuery(window).height();
	var popup_resize_height = (hero_height - 200);
	var popup_inner_height = (popup_resize_height - 50);
	var popup_top_margin_offset = (popup_inner_height / 2);
	jQuery('.hero_popup_resize').css({
		'height': popup_resize_height + 'px',
		'margin-top': '-' + popup_top_margin_offset + 'px'
	});
	jQuery('.hero_popup_inner').css({
		'height': popup_inner_height + 'px'
	});
	jQuery('.hero_popup_main').css({
		'height': hero_height + 'px'
	});
}
//launch popup
function hmenu_launch_hero_popup(path_to_html, load_method, update_method, cancel_method, json) {
	//clean JSON
	json = encodeURIComponent(JSON.stringify(json));
	//load content
	jQuery('.hero_popup_inner').load(hmenu_core_view_path + 'views/' + path_to_html, function () {
		//call load method if set
		if (load_method != null) {
			eval("" + load_method + "(hmenu_extract_json_object('" + json + "'));");
		}
		//bind update method
		jQuery('.hero_popup_update_btn').off().on('click', function () {
			if (update_method != null) {
				eval("" + update_method + "(hmenu_extract_json_object('" + json + "'));");
			}
			hmenu_hide_hero_popup();
		});
		//bind cancel method
		jQuery('.hero_popup_cancel_btn').off().on('click', function () {
			if (cancel_method != null) {
				eval("" + cancel_method + "(hmenu_extract_json_object('" + json + "'));");
			}
			hmenu_hide_hero_popup();
		});
		//re-call component binding (component_manager.js)
		hmenu_bind_field_convert();
		//show popup
		hmenu_show_hero_popup();
	});
}
//show popup
function hmenu_show_hero_popup() {
	jQuery('.hero_popup_main').fadeIn(300);
}
//hide popup
function hmenu_hide_hero_popup() {
	jQuery('.hero_popup_main').fadeOut(300, function () {
		jQuery('.hero_popup_inner').empty();
		jQuery('.hero_popup_update_btn').off();
		jQuery('.hero_popup_cancel_btn').off();
	});
}



//CORE CUSTOM EVENT SYSTEM
//trigger system-wide events that can be bound to
function hmenu_trigger_hplugin_event(evt) {
	jQuery('.hero_viewport').trigger(evt);
}
//event subscribe
function hmenu_hplugin_event_subscribe(evt, callback, json) {
	//clean JSON
	json = encodeURIComponent(JSON.stringify(json));
	jQuery('.hero_viewport').on(evt, function () {
		eval("" + callback + "(hmenu_extract_json_object('" + json + "'));");
	});
}
//event subscribe once
function hmenu_hplugin_event_subscribe_once(evt, callback, json) {
	//clean JSON
	json = encodeURIComponent(JSON.stringify(json));
	jQuery('.hero_viewport').on(evt, function () {
		hmenu_hplugin_event_unsubscribe(evt);
		eval("" + callback + "(hmenu_extract_json_object('" + json + "'));");
	});
}
//event unsubscribe
function hmenu_hplugin_event_unsubscribe(evt) {
	jQuery('.hero_viewport').off(evt);
}


//LOAD IFRAME SECURELY
//load iframe
var hmenu_iframe_src;
var hmenu_iframe_height;
var hmenu_iframe_container;
function hmenu_load_secure_iframe(src, height, container) {
	hmenu_iframe_src = src;
	hmenu_iframe_height = height;
	hmenu_iframe_container = container;
	jQuery.ajax({
		url: hmenu_ajax_url,
		type: "POST",
		data: {
			'action': hmenu_plugin_name + '_get_security_code'
		},
		dataType: "json"
	}).done(function (token) {
		//load iframe
		jQuery(container).empty().append('<iframe frameborder="0" height="' + height + '" width="100%" scrolling="no" src="' + hmenu_plugin_url + src + '?st=' + token + '"></iframe>');
	});
}
//show security tag timeout error
function hmenu_show_security_tag_timeout_error() {
	if (typeof hmenu_iframe_src !== 'undefined' && typeof hmenu_iframe_height !== 'undefined' && typeof hmenu_iframe_container !== 'undefined') {
		hmenu_load_secure_iframe(hmenu_iframe_src, hmenu_iframe_height, hmenu_iframe_container);
	}
	hmenu_show_message("error", "Security Token", "The security token has timed out. Please try again.");
}


//CONVERTERS
//hex to rgb
function hmenu_hexToRgb(hex) {
	var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
	return result ? parseInt(result[1], 16) + ',' + parseInt(result[2], 16) + ',' + parseInt(result[3], 16) : null;
}
