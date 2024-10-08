'use strict';

//COMPONENTS MANAGER

//config
var hmenu_img_width = 160;
var hmenu_img_height = 160;
var hmenu_img_src = '';

//component switch
function hmenu_switch_components() {
	hmenu_bind_field_convert();
	hmenu_img_src = hmenu_plugin_url + 'assets/images/admin/palette.png';
}

//elements
function hmenu_bind_field_convert() {
	jQuery('.hero_main, .hero_popup_main').find('input').each(function (index, element) {
		switch (jQuery(element).attr('type')) {
			case 'text':
				if (jQuery(this).data('bound') != 'set' && !jQuery(this).hasClass('color_picker')) { hmenu_bind_check(element) };
				if (jQuery(this).data('hero_type') == 'px') { jQuery(this).addClass('hero_px').addClass('hero_int_only'); };
				if (jQuery(this).data('hero_type') == 'ms') { jQuery(this).addClass('hero_ms').addClass('hero_int_only'); };
				if (jQuery(this).data('hero_type') == 'perc') { jQuery(this).addClass('hero_perc').addClass('hero_int_only'); };
				if (jQuery(this).data('hero_type') == 'img') { jQuery(this).addClass('hero_img'); };
				break;
			case 'password':
				if (jQuery(this).data('bound') != 'set') { hmenu_bind_check(element) };
				break;
			case 'checkbox':
				if (jQuery(this).data('bound') != 'set') { hmenu_bind_switch(element); };
				break;
			case 'radio':
				if (jQuery(this).data('bound') != 'set') { hmenu_bind_switch(element); };
				break;
		}
	});
	jQuery('.hero_int_only').on('keydown keyup paste', function (event) {
		hmenu_hero_force_int(this);
	});
	jQuery('.hero_main, .hero_popup_main').find('textarea').each(function (index, element) {
		if (jQuery(this).data('bound') != 'set') { hmenu_bind_check(element); };
	});
	jQuery('.hero_main, .hero_popup_main').find('select').each(function (index, element) {
		if (jQuery(this).data('bound') != 'set') { hmenu_bind_select(element); }
	});
	jQuery('.color_picker').each(function (index, element) {
		if (jQuery(this).data('bound') != 'set') {
			var current_value = jQuery(this).val();
			var picker_id = jQuery(this).attr('id');
			var picker_name = jQuery(this).attr('name');
			var picker_class = jQuery(this).attr('class');
			hmenu_bind_color_html(element, index, current_value, picker_id, picker_name, picker_class);
		}
	});
	jQuery('.hero_picker_wrap').each(function (index, element) {
		if (jQuery(this).data('bound') != 'set') {
			hmenu_bind_color_picker(element);
			hmenu_create_canvas_element(element);
		}
	});
	jQuery('.hero_media_uploader').each(function (index, element) {
		if (jQuery(this).data('bound') != 'set') { hmenu_bind_media_uploader(element); }
	});
	hmenu_activate_hplugin_tooltips();
}

//activate media uploader
function hmenu_bind_media_uploader(elem) {

	var file_frame;

	jQuery(elem).off().on('click', function (event) {

		//connected with an items ID
		var the_connected_input = jQuery('#' + jQuery(this).data('connect-with'));

		//set multiple status true for multiple, false for single selection
		var the_mulitple_status = jQuery('#' + jQuery(this).data('multiple'));

		//set the image size
		var the_size = jQuery(this).data('size');

		var attachment;

		if (the_mulitple_status == 'true') {
			the_mulitple_status = true;
		} else {
			the_mulitple_status = false;
		}

		event.preventDefault();

		if (file_frame) {
			file_frame.open();
			return;
		}

		file_frame = wp.media.frames.file_frame = wp.media({
			title: jQuery(this).data('title'),
			button: {
				text: jQuery(this).data('text'),
			},
			multiple: the_mulitple_status
		});

		file_frame.on('select', function () {

			attachment = file_frame.state().get('selection').first().toJSON();

			//logs file data: 
			hmenu_console_log(attachment);
			hmenu_console_log(the_size);
			if (the_size == 'full') {
				if (typeof (attachment.sizes.full) !== 'undefined') {
					the_connected_input.val(attachment.sizes.full.url);
				}
			} else if (the_size == 'medium') {
				if (typeof (attachment.sizes.medium) !== 'undefined') {
					the_connected_input.val(attachment.sizes.medium.url);
				}
			} else if (the_size == 'thumbnail') {
				if (typeof (attachment.sizes.thumbnail) !== 'undefined') {
					the_connected_input.val(attachment.sizes.thumbnail.url);
				}
			} else {
				if (typeof (attachment.sizes.medium) !== 'undefined') {
					the_connected_input.val(attachment.sizes.medium.url);
				} else {
					the_connected_input.val(attachment.sizes.full.url);
				}
			}

			jQuery(the_connected_input).trigger('change');

		});

		file_frame.open();

	});

}

//activate tooltips
function hmenu_activate_hplugin_tooltips() {
	jQuery('[data-tooltip]').each(function () {
		if (jQuery(this).data('tooltip') != '') {
			hmenu_bind_hplugin_tooltip(this, jQuery(this).data('tooltip'));
			jQuery(this).attr('data-tooltip', '');
		}
	});
}

//bind to element
function hmenu_bind_hplugin_tooltip(elem, tooltip) {
	jQuery(elem).addClass('hplugin-tooltip').on('mouseover', function () {
		hmenu_show_hplugin_tooltip(elem, tooltip);
	}).on('mouseout', function () {
		hmenu_hide_hplugin_tooltip();
	});
}

//show tooltip
function hmenu_show_hplugin_tooltip(elem, tooltip) {
	var container_pos = jQuery('.hero_main').offset();
	var elem_pos = jQuery(elem).offset();
	var tooltip = '<div class="hplugin-tooltip-container"><div class="hplugin-tooltip-content">' + tooltip + '</div></div>';
	jQuery(tooltip).insertAfter('.hero_admin');
	jQuery('.hplugin-tooltip-container').css({
		'top': (elem_pos.top - container_pos.top) - (jQuery('.hplugin-tooltip-container').outerHeight()) + 'px',
		'left': (elem_pos.left - container_pos.left) - 8 + 'px'
	}).stop().animate({
		'opacity': 0.8,
		'margin-top': '-' + 8 + 'px'
	}, 100);
}

//hide tooltip
function hmenu_hide_hplugin_tooltip() {
	jQuery('.hplugin-tooltip-container').stop().animate({
		'opacity': 0,
		'margin-top': 0 + 'px'
	}, 50, function () {
		jQuery(this).remove();
	});
}

//create color picker html
function hmenu_bind_color_html(element, index, current_value, picker_id, picker_name, picker_class) {
	hmenu_set_bound(element);
	var color_html = '';
	var the_input = jQuery(element).clone();
	color_html += '<div class="hero_picker_wrap" id="custom_picker_' + index + '">';
	color_html += '<div class="hero_current_color rounded_3">';
	color_html += '<div class="hero_current_color_btn"></div>';
	color_html += '<div class="hero_palette rounded_3">';
	color_html += '<div class="hero_canvas"></div>';
	color_html += '<div class="hero_current_sample rounded_3"></div>';
	color_html += '</div>';
	color_html += '</div> ';
	color_html += '</div> ';
	jQuery(element).replaceWith(color_html);
	jQuery('#custom_picker_' + index).append(the_input);
}

//create canvas element
function hmenu_create_canvas_element(element) {
	jQuery('#' + jQuery(element).attr('id')).children('.hero_current_color').children('.hero_palette').children('.hero_canvas').append('<canvas id="hero_picker" class="hero_colour_picker_' + jQuery(element).attr('id') + '"></canvas>');
	jQuery('.hero_colour_picker_' + element.id).attr('width', hmenu_img_width);
	jQuery('.hero_colour_picker_' + element.id).attr('height', hmenu_img_height);
	var canvas = jQuery('.hero_colour_picker_' + element.id)[0];
	var context = canvas.getContext("2d");
	var img = new Image();
	img.src = hmenu_img_src;
	jQuery(img).on('load', function () {
		context.drawImage(img, 0, 0);
		canvas.addEventListener('mousemove', hmenu_update_sample, false);
		canvas.addEventListener('click', hmenu_select_colour, false);
	});
	var the_field = jQuery('#' + element.id).children('input');
	var the_current_color = jQuery('#' + element.id).children('.hero_current_color');
	hmenu_bind_input_change_listener(the_field, the_current_color);
	hmenu_set_bound(element);
}

//get colour
function hmenu_get_colour(evt, the_picker) {
	var canvas = jQuery('.' + the_picker)[0];
	var context = canvas.getContext("2d");
	var image_data = context.getImageData(0, 0, hmenu_img_width, hmenu_img_height).data;
	var elementXPos = evt.offsetX ? evt.offsetX : (evt.layerX - jQuery(evt.target).position().left);
	var elementYPos = evt.offsetY ? evt.offsetY : (evt.layerY - jQuery(evt.target).position().top);
	var i = ((parseInt(elementYPos) * hmenu_img_width) + parseInt(elementXPos)) * 4;
	var pixel_colour = "#" + hmenu_d2Hex(image_data[i]) + hmenu_d2Hex(image_data[i + 1]) + hmenu_d2Hex(image_data[i + 2]);
	return pixel_colour;
}

//convert RGB to HEX
function hmenu_d2Hex(d) {
	var hex = Number(d).toString(16);
	while (hex.length < 2) { hex = "0" + hex; }
	return hex.toUpperCase();
}

//update sample
function hmenu_update_sample(evt) {
	var the_picker = jQuery(this).attr('class');
	var the_sample = jQuery(this).parents('.hero_palette').children('.hero_current_sample');
	var colour = hmenu_get_colour(evt, the_picker);
	jQuery(the_sample).css({
		'background-color': colour
	});
}

//select colour
function hmenu_select_colour(evt) {
	var the_picker = jQuery(this).attr('class');
	var the_field = jQuery(this).parents('.hero_picker_wrap').children('input');
	var the_current_color = jQuery(this).parents('.hero_picker_wrap').children('.hero_current_color');
	var colour = hmenu_get_colour(evt, the_picker);
	jQuery(the_field).val(colour).trigger('change');
}

//bind input change listener
function hmenu_bind_input_change_listener(the_field, the_current_color) {
	jQuery(the_field).off().on('change', function () {
		jQuery(the_current_color).css({
			'background-color': jQuery(the_field).val()
		});
	});
}

//enable color picker
function hmenu_bind_color_picker(element) {
	var current_color = jQuery('#' + jQuery(element).attr('id')).children('.hero_current_color');
	var current_pallete = jQuery('#' + jQuery(element).attr('id')).children('.hero_current_color').children('.hero_palette');
	jQuery(current_pallete).hide();
	jQuery('.hero_current_color_btn').on('click', function (e) {
		jQuery('.hero_palette').hide();
		jQuery(this).parents('.hero_current_color').children('.hero_palette').toggle();
		e.stopPropagation();
	});
	jQuery(document).on('click', function () {
		jQuery(current_pallete).hide();
	});
	setTimeout(() => {
		var input_val = jQuery('#' + element.id + ' input').val();
		if (input_val != '') {
			jQuery(current_color).css({
				'background-color': input_val
			});
		} else {
			jQuery(current_color).css({
				'background-color': '#efefef'
			});
		}
	}, 100);
	hmenu_set_bound(element);
}

//change sizes on input fields
function hmenu_bind_check(element) {
	switch (jQuery(element).data('size')) {
		case 'lrg':
			jQuery(element).addClass('hero_field_lrg');
			break;
		case 'med':
			jQuery(element).addClass('hero_field_med');
			break;
		case 'sml':
			jQuery(element).addClass('hero_field_sml');
			break;
	}
	hmenu_set_bound(element);
}

//convert select boxes
function hmenu_bind_select(element) {
	var select_size;
	var select_height;
	if (jQuery(element).data('size')) {
		select_size = 'hero_select_' + jQuery(element).data('size');
	} else {
		select_size = 'hero_select_sml';
	}

	var select_html = '';
	select_html += '<div class="hero_select_holder ' + select_size + ' ' + element.id + '">';
	select_html += '<div class="hero_selected rounded_3"><span>';
	select_html += '</span></div>';
	select_html += '<div class="hero_open_select rounded_right_3">';
	select_html += '</div>';
	select_html += '<div class="hero_dropdown rounded_bottom_3">';
	select_html += '</div>';
	select_html += '</div>';
	jQuery('.' + element.id).css({
		'display': 'table'
	});
	jQuery('#' + element.id).after(select_html);
	//set height
	if (jQuery(element).data('height')) {
		jQuery('.' + element.id + ' .hero_dropdown').css({
			height: jQuery(element).data('height') + 'px'
		});
	}
	hmenu_populate_select(element);
	hmenu_toggle_select(element);
	hmenu_activate_click(element.id);
	hmenu_set_bound(element);
}

//set bound items
function hmenu_set_bound(element) {
	jQuery(element).attr('data-bound', 'set');
}

//open close select div
function hmenu_toggle_select(element) {
	var select_holder = jQuery('.' + element.id);
	jQuery(select_holder).on('click', function () {
		jQuery('.' + element.id + ' .hero_dropdown').toggle();
		jQuery(select_holder).on('mouseleave', function () {
			jQuery('.' + element.id + ' .hero_dropdown').hide();
		})
	})
}

//populate select div
function hmenu_populate_select(element) {
	var select_holder = jQuery('.' + element.id);
	var drop_down_row = '';
	jQuery('#' + element.id + ' option').each(function (index, option) {
		drop_down_row += '<div class="hero_drop_row" data-value="' + jQuery(this).val() + '">' + option.text + '</div>';
		if (jQuery(this).is(':selected')) {
			jQuery('.' + element.id + ' .hero_selected span').html(option.text);
		}
	});
	jQuery('.' + element.id + ' .hero_dropdown').html(drop_down_row);
}

//activate click
function hmenu_activate_click(id) {
	jQuery('.' + id + ' .hero_drop_row').on('click', function () {
		var select_value = jQuery(this).data('value');
		var select_text = jQuery(this).text();
		jQuery('#' + id + ' option').removeAttr('selected');
		jQuery('#' + id + ' option').each(function (index, option) {
			if (jQuery(this).val() == select_value) {
				jQuery(this).attr('selected', 'selected').trigger('change');
				jQuery('.' + id + ' .hero_selected span').html(select_text);
			}
		});
	})
}

//convert radion and checkboxes buttons
function hmenu_bind_switch(element) {
	var switch_size;
	if (jQuery(element).data('size')) {
		switch_size = 'hero_switch_' + jQuery(element).data('size');
	} else {
		switch_size = 'hero_switch_sml';
	}
	if (jQuery(element).data('type') == undefined) {
		var switch_html = '';
		switch_html += '<div class="hero_switch_btn ' + switch_size + ' rounded_30 ' + element.id + '">';
		switch_html += '<div class="hero_circle_slide rounded_30"></div>';
		switch_html += '<div class="hero_slide_text">ON</div>';
		switch_html += '</div>';
		jQuery('#' + element.id).after(switch_html);
		var input_size_width = jQuery('.' + element.id).width();
		var input_size_height = jQuery('.' + element.id).height();
		jQuery('#' + element.id).css({
			'width': input_size_width,
			'height': input_size_height
		});
		hmenu_activate_flick(element);
		hmenu_set_bound(element);
	} else {
		var switch_html = '';
		switch_html += '<div class="hero_tick_btn ' + switch_size + ' ' + element.id + '">';
		switch_html += '<div class="hero_tick_status"></div>';
		switch_html += '</div>';
		jQuery('#' + element.id).after(switch_html);
		var input_size_width = jQuery('.' + element.id).width();
		var input_size_height = jQuery('.' + element.id).height();
		jQuery('#' + element.id).css({
			'width': input_size_width,
			'height': input_size_height
		});
		hmenu_activate_tick(element);
		hmenu_set_bound(element);
	}

}

//activate the sliding ability
function hmenu_activate_tick(element) {
	setTimeout(function () {
		if (jQuery(element).is(':checked')) {
			hmenu_tick_on(element, 0);
		} else {
			hmenu_tick_off(element, 0);
		}
	}, 100);
	jQuery(element).on('click', function () {
		if (jQuery(element).attr('type') == 'radio') {
			jQuery('input[name="' + element.name + '"]').each(function (index, element) {
				if (jQuery(this).is(':checked')) {
					hmenu_tick_on(element, 200);
				} else {
					hmenu_tick_off(element, 200);
				}
			});
		} else if (jQuery(element).attr('type') == 'checkbox') {
			if (jQuery(this).is(':checked')) {
				hmenu_tick_on(element, 200);
			} else {
				hmenu_tick_off(element, 200);
			}
		}
	});
}

//set on
function hmenu_tick_on(element, speed) {
	var hero_switch_btn = jQuery('.' + jQuery(element).attr('id'));
	jQuery(hero_switch_btn).css({
		'background-color': '#A8CE83'
	});
	jQuery(hero_switch_btn).children('.hero_tick_status').animate({
		'background-color': '#A8CE83'
	}, speed);
}

//set off
function hmenu_tick_off(element, speed) {
	var hero_switch_btn = jQuery('.' + element.id);
	jQuery(hero_switch_btn).css({
		'background-color': '#F1F1F1'
	});
	jQuery(hero_switch_btn).children('.hero_tick_status').animate({
		'background-color': '#F1F1F1'
	}, speed);
}

//activate the sliding ability
function hmenu_activate_flick(element) {
	setTimeout(function () {
		if (jQuery(element).is(':checked')) {

			hmenu_set_on(element, 0);
		} else {
			hmenu_set_off(element, 0);
		}
	}, 100);
	jQuery(element).on('click', function () {
		if (jQuery(element).attr('type') == 'radio') {
			jQuery('input[name="' + element.name + '"]').each(function (index, element) {
				if (jQuery(this).is(':checked')) {
					hmenu_set_on(element, 200);
				} else {
					hmenu_set_off(element, 200);
				}
			});
		} else if (jQuery(element).attr('type') == 'checkbox') {
			if (jQuery(this).is(':checked')) {
				hmenu_set_on(element, 200);
			} else {
				hmenu_set_off(element, 200);
			}
		}
	});
}

//set on
function hmenu_set_off(element, speed) {
	var hero_switch_btn = jQuery('.' + jQuery(element).attr('id'));
	jQuery(hero_switch_btn).css({
		'background-color': '#CCC'
	});
	jQuery(hero_switch_btn).children('.hero_slide_text').html(
		'OFF'
	);
	var circle_size = jQuery(hero_switch_btn).children('.hero_circle_slide').width() + 1;
	var text_size = jQuery(hero_switch_btn).children('.hero_slide_text').width();
	jQuery(hero_switch_btn).children('.hero_circle_slide').animate({
		'left': '0',
		'margin-left': '1px'
	}, speed);
	jQuery(hero_switch_btn).children('.hero_slide_text').animate({
		'left': '100%',
		'margin-left': '-' + text_size + 'px'
	}, speed);
}

//set off
function hmenu_set_on(element, speed) {
	var hero_switch_btn = jQuery('.' + element.id);
	jQuery(hero_switch_btn).css({
		'background-color': '#A7CF7F'
	});
	jQuery(hero_switch_btn).children('.hero_slide_text').html(
		'ON'
	);
	var circle_size = jQuery(hero_switch_btn).children('.hero_circle_slide').width() + 1;
	var text_size = jQuery(hero_switch_btn).children('.hero_slide_text').width();
	jQuery(hero_switch_btn).children('.hero_circle_slide').animate({
		'left': '100%',
		'margin-left': '-' + circle_size + 'px'
	}, speed);
	jQuery(hero_switch_btn).children('.hero_slide_text').animate({
		'left': '0',
		'margin-left': '0'
	}, speed);
}

//update component
function hmenu_update_select_component(element) {
	if (element.prop('tagName') == 'SELECT') {
		jQuery('.hero_select_holder.' + element.attr('id')).remove();
		jQuery('#' + element.attr('id')).each(function (index, element) {
			hmenu_bind_select(element);
		});
	}
}

//force integer
function hmenu_hero_force_int(object) {
	var val = jQuery(object).val();
	if (!/^[0-9]+$/.test(val)) {
		jQuery(object).val(jQuery(object).val().replace(/[^0-9]/g, ''));
	}
}
