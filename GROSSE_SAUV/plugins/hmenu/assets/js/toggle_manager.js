'use strict';
//TOGGLE MANAGER
//toggle elements
function hmenu_find_toggle_elements() {
	jQuery('input').each(function (index, element) {
		if (jQuery(this).data('toggler')) {
			var the_toggle_section_div = jQuery(this).parents('.hero_section_toggle');
			hmenu_set_toggle_sections(element, the_toggle_section_div);
		}
	});
}

//activate the sliding ability
function hmenu_set_toggle_sections(element, the_toggle_section_div) {

	if (jQuery(element).is(':checked')) {
		hmenu_open_section(the_toggle_section_div);
	} else {
		hmenu_close_section(the_toggle_section_div);
	}
	jQuery(element).on('click', function () {
		if (jQuery(element).attr('type') == 'radio') {
			jQuery('input[name="' + element.name + '"]').each(function (index, element) {
				if (jQuery(this).is(':checked')) {
					hmenu_open_section(the_toggle_section_div);
				} else {
					hmenu_close_section(the_toggle_section_div);
				}
			});
		} else if (jQuery(element).attr('type') == 'checkbox') {
			if (jQuery(this).is(':checked')) {
				hmenu_open_section(the_toggle_section_div);
			} else {
				hmenu_close_section(the_toggle_section_div);
			}
		}
	});
}

//open section
function hmenu_open_section(the_toggle_section_div) {
	jQuery(the_toggle_section_div).css({
		'display': 'table',
		'overflow': 'auto'
	});
}

//close section
function hmenu_close_section(the_toggle_section_div) {
	jQuery(the_toggle_section_div).css({
		'display': 'block',
		'overflow': 'hidden',
		'height': '70px'
	});
}

//small toggle elements
function hmenu_find_small_toggle_elements() {
	jQuery('input').each(function (index, element) {
		if (jQuery(this).data('smltoggler')) {
			var the_small_toggle_section_div = jQuery('.' + jQuery(this).data('smltoggler'));
			hmenu_set_small_toggle_sections(element, the_small_toggle_section_div);
		}
	});
}

//activate the sliding ability
function hmenu_set_small_toggle_sections(element, the_small_toggle_section_div) {

	if (jQuery(element).is(':checked')) {
		hmenu_open_small_section(the_small_toggle_section_div);
	} else {
		hmenu_close_small_section(the_small_toggle_section_div);
	}
	jQuery(element).on('click', function () {
		if (jQuery(element).attr('type') == 'radio') {
			jQuery('input[name="' + element.name + '"]').each(function (index, element) {
				if (jQuery(this).is(':checked')) {
					hmenu_open_small_section(the_small_toggle_section_div);
				} else {
					hmenu_close_small_section(the_small_toggle_section_div);
				}
			});
		} else if (jQuery(element).attr('type') == 'checkbox') {
			if (jQuery(this).is(':checked')) {
				hmenu_open_small_section(the_small_toggle_section_div);
			} else {
				hmenu_close_small_section(the_small_toggle_section_div);
			}
		}
	});
}

//open small section
function hmenu_open_small_section(the_small_toggle_section_div) {
	jQuery(the_small_toggle_section_div).css({
		'display': 'block'
	});
}

//close small section
function hmenu_close_small_section(the_small_toggle_section_div) {
	jQuery(the_small_toggle_section_div).css({
		'display': 'none'
	});
}

//small toggle elements
function hmenu_find_image_toggle() {
	jQuery(jQuery('input[data-toggleimage="true"]')).each(function (index, element) {
		if (jQuery(this).data('toggleimage')) {
			var toggle_image = jQuery('.image_' + jQuery(this).attr('id'));
			hmenu_toggle_images(element, toggle_image);
		}
	});
}

//activate the sliding ability
function hmenu_toggle_images(element, toggle_image) {

	if (jQuery(element).is(':checked')) {
		hmenu_show_image(jQuery('.image_' + jQuery(element).attr('id')));
	} else {
		hmenu_hide_image(jQuery('.image_' + jQuery(element).attr('id')));
	}

	jQuery(element).on('click', function () {
		jQuery('input[name="' + element.name + '"]').each(function (index, element) {
			if (jQuery(this).is(':checked')) {
				hmenu_show_image(jQuery('.image_' + jQuery(this).attr('id')));
			} else {
				hmenu_hide_image(jQuery('.image_' + jQuery(this).attr('id')));
			}
		});
	});

}

function hmenu_show_image(toggle_image) {
	jQuery(toggle_image).animate({
		opacity: 1
	});
}

function hmenu_hide_image(toggle_image) {
	jQuery(toggle_image).animate({
		opacity: 0.2
	});
}
