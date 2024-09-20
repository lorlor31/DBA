<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );

$out = '';
$out .= sprintf(
	'<li><a href="%s" class="et-nav%s et-nav-menu">%s</a></li>',
	admin_url( 'admin.php?page=et-panel-welcome' ),
	( ! isset( $_GET['page'] ) || $_GET['page'] == 'et-panel-welcome' ) ? ' active' : '',
	esc_html__( 'Welcome', 'legenda' )

);

if ( ! etheme_is_activated() ) {
	$out .= sprintf(
		'<li><a href="%s" class="et-nav%s et-nav-speed">%s</a></li>',
		admin_url( 'admin.php?page=et-panel-welcome' ),
		( $_GET['page'] == 'et-panel-plugins' ) ? ' active' : '',
		esc_html__( 'Plugins', 'legenda' )
	);
	$out .= sprintf(
		'<li><a href="%s" class="et-nav%s et-nav-general">%s</a></li>',
		admin_url( 'themes.php?page=_options' ),
		( $_GET['page'] == 'et-panel-options' ) ? ' active' : '',
		esc_html__( 'Theme Options', 'legenda' )
	);
} elseif( ! class_exists( 'Redux' ) ) {
	$out .= sprintf(
		'<li><a href="%s" class="et-nav%s et-nav-speed">%s</a></li>',
		admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ),
		( $_GET['page'] == 'et-panel-plugins' ) ? ' active' : '',
		esc_html__( 'Plugins', 'legenda' )
	);
	$out .= sprintf(
		'<li><a href="%s" class="et-nav%s et-nav-general">%s</a></li>',
		admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ),
		( $_GET['page'] == 'et-panel-options' ) ? ' active' : '',
		esc_html__( 'Theme Options', 'legenda' )
	);
} else {
	$out .= sprintf(
		'<li><a href="%s" class="et-nav%s et-nav-speed">%s</a></li>',
		admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ),
		( $_GET['page'] == 'et-panel-plugins' ) ? ' active' : '',
		esc_html__( 'Plugins', 'legenda' )
	);
	$out .= sprintf(
		'<li><a href="%s" class="et-nav%s et-nav-general">%s</a></li>',
		admin_url( 'themes.php?page=LegendaThemeOptions' ),
		( $_GET['page'] == 'et-panel-options' ) ? ' active' : '',
		esc_html__( 'Theme Options', 'legenda' )
	);
}

echo '<div class="etheme-page-nav"><ul>' . $out . '</ul></div>';