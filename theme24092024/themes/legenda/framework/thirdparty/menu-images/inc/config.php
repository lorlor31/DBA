<?php
/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) exit;

function et_get_menu_fields() {
	// global $wp_registered_sidebars;
	// $sidebar_option = array(''=>'sidebar');
	// foreach ($wp_registered_sidebars as $key => $sidebar) {
	// 	$sidebar_option[$key] = $sidebar['name'];
	// }

 //    $blocks = array();
 //    $blocks[''] = "";  

 //    $posts = get_posts( array(
 //        'post_type' => 'staticblocks',
 //        'numberposts' => 10,
 //    ) );

 //    if ( $posts ) {
 //        foreach ( $posts as $post ) {
	// 		$blocks[$post->ID] = $post->post_title;
 //        }
 //    }

	return array(
		array(
			'id' => 'disable_titles',
			'type' => 'checkbox',
			'title' => esc_html__('Disable navigation label', 'legenda'),
			'width' => 'wide',
			'value' => 1,
			'levels' => array(0,1)
		),
		// array(
		// 	'id' => 'anchor',
		// 	'type' => 'text',
		// 	'title' => 'Anchor',
		// 	'width' => 'wide'
		// ),
		array(
			'id' => 'design',
			'type' => 'select',
			'title' => esc_html__('Design', 'legenda'),
			'width' => 'wide',
			'options' => array(
				'' => esc_html__('design', 'legenda'),
				'dropdown' => esc_html__('Dropdown', 'legenda'),
				'full-width' => esc_html__('Mega menu', 'legenda'),
				'full-width open-by-click' => esc_html__('Mega menu open by click', 'legenda'),
				//'posts-subcategories' => 'Subcategories + Posts',
				//'image-column' => 'Image column',
				//'image-no-space' => 'Image column no space',
			),
			'levels' => 0
		),
		array(
			'id' => 'column_width',
			'type' => 'text',
			'title' => esc_html__('Column width (for ex.: 30%)', 'legenda'),
			'width' => 'wide',
			'input_type' => 'number',
			'attributes' => array(
				'min' => 1,
				'max' => 100
			),
			'levels' => array(1)
		),
		array(
			'id' => 'design2',
			'type' => 'select',
			'title' => esc_html__('Design', 'legenda'),
			'width' => 'wide',
			'options' => array(
				'' => esc_html__('Design', 'legenda'),
				'image' => esc_html__('Image', 'legenda'),
				'image-no-borders' => esc_html__('Image without spacing', 'legenda'),
				//'image-column' => 'Image column',
				//'image-no-space' => 'Image column no space',
			),
			'levels' => array(1,2)
		),
		array(
			'id' => 'columns',
			'type' => 'select',
			'title' => esc_html__('Columns', 'legenda'),
			'width' => 'wide',
			'options' => array(
				2 => 2,
				3 => 3,
				4 => 4,
				5 => 5,
				6 => 6,
			),
			'levels' => 0
		),
		/*array(
			'id' => 'block',
			'type' => 'select',
			'title' => 'Static block',
			'width' => 'wide',
			'options' => $blocks,
			'levels' => array(1)
		),*/
		// array(
		// 	'id' => 'widget_area',
		// 	'type' => 'select',
		// 	'title' => 'Widget area',
		// 	'width' => 'wide',
		// 	'options' => $sidebar_option,
		// 	'levels' => array(1,2)
		// ),
		array(
			'id' => 'icon',
			'type' => 'text',
			'title' => esc_html__('Icon name (from fonts Awesome)', 'legenda'),
			'width' => 'wide',
			'levels' => 0,
		),
		array(
			'id' => 'label',
			'type' => 'select',
			'title' => esc_html__('Label', 'legenda'),
			'width' => 'wide',
			'options' => array(
				'' => esc_html__('Label', 'legenda'),
				'hot' => esc_html__('Hot', 'legenda'),
				'sale' => esc_html__('Sale', 'legenda'),
				'new' => esc_html__('New', 'legenda'),
			)
		),
		array(
			'id' => 'background_repeat',
			'type' => 'select',
			'title' => esc_html__('Background Repeat', 'legenda'),
			'width' => 'thin',
			'options' => array(
				'' => esc_html__('background-repeat', 'legenda'),
				'no-repeat' => esc_html__('No Repeat', 'legenda'),
				'repeat' => esc_html__('Repeat All', 'legenda'),
				'repeat-x' => esc_html__('Repeat Horizontally', 'legenda'),
				'repeat-y' => esc_html__('Repeat Vertically', 'legenda'),
				'inherit' => esc_html__('Inherit', 'legenda'),
			),
			'levels' => array(0,1,2)
		),
		array(
			'id' => 'background_position',
			'type' => 'select',
			'title' => esc_html__('Background Position', 'legenda'),
			'width' => 'thin',
			'options' => array(
				'' => esc_html__('background-position', 'legenda'),
				'left top' => esc_html__('Left Top', 'legenda'),
				'left center' => esc_html__('Left Center', 'legenda'),
				'left bottom' => esc_html__('Left Bottom', 'legenda'),
				'center center' => esc_html__('Center Center', 'legenda'),
				'center bottom' => esc_html__('Center Bottom', 'legenda'),
				'right top' => esc_html__('Right Top', 'legenda'),
				'right center' => esc_html__('Right Center', 'legenda'),
				'right bottom' => esc_html__('Right Bottom', 'legenda'),
			),
			'levels' => array(0,1,2)
		),
	);
}