<?php
/*
Plugin Name: Collapsible Widgets
Plugin URI: http://plugins.ten-321.com/collapsible-widget-area/
Description: Allows you to set up a tabbed or accordion-style widget area to be displayed wherever you choose within WordPress
Version: 0.5.2
Author: cgrymala
Author URI: http://ten-321.com/
License: GPL2
*/
if ( ! class_exists( 'collapsible_widget_area' ) ) {
	require_once( 'class.collapsible-widget-area.php' );
}

/**
 * Make sure, if this is multisite, that we have the ability to use the 
 * 		is_plugin_active_for_network() function
 */
if( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) ) {
	require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

/**
 * Instantiate an instance of the collapsible_widget_area class and store it 
 * 		in a global variable.
 */
function init_collapsible_widget_area() {
	global $collapsible_widget_area;
	return $collapsible_widget_area = new collapsible_widget_area;
}
add_action( 'plugins_loaded', 'init_collapsible_widget_area' );
?>