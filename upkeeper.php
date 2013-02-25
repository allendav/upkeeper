<?php
/*
Plugin Name: UpKeeper
Plugin URI: http://allendav.wordpress.com/upkeeper
Description: Keep that domain from expiring
Version: 0.1
Author: allendav
Author URI: http://allendav.wordpress.com/
License: GPL2
*/

/*  Copyright 2013 Allen Snook (email: allendav@automattic.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

/******************************************************************************************************/
function upkeeper_add_dashboard_widget() {
	wp_add_dashboard_widget( 'upkeeper_dashboard_widget', 'UpKeeper', 'upkeeper_dashboard_widget' );    
} 
add_action( 'wp_dashboard_setup', 'upkeeper_add_dashboard_widget' );

/******************************************************************************************************/
function upkeeper_dashboard_widget() {
	echo '<div id="upkeeper-list-view">Loading...</div>';
	echo '<p><a href="#" class="upkeeper-add">Add an item</a></p>';
	echo '<p style="display: none;" id="upkeeper-endpoint">' . plugins_url( '/rest.php', __FILE__ ) . "</p>";
}

/******************************************************************************************************/
function upkeeper_queue_scripts() {
	if ( is_admin() ) {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'backbone' );

		$script_url = plugins_url( '/upkeeper.js', __FILE__ ); 
		wp_enqueue_script( 'upkeeper_script', $script_url, array( 'jquery', 'backbone' ) );
	}
}
add_action( 'init', 'upkeeper_queue_scripts' );

/******************************************************************************************************/
function upkeeper_init() {
	register_post_type('upkeeperitem',
		array(
			'labels' => array(
				'name' => __('UpKeeper Items'),
				'singular_name' => __('UpKeeper Item'),
				'add_new_item' => __('Add New UpKeeper Item'),
				'edit_item' => __('Edit UpKeeper Item'),
				'new_item' => __('New UpKeeper Item'),
				'view_item' => __('View UpKeeper Item'),
				'menu_name' => __('UpKeeper Items')
			),
			'supports' => array(
				'title',
				'author'
			),
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'exclude_from_search' => true,
			'show_in_nav_menus' => false,
			'has_archive' => false
		)
	);
}
add_action( 'init', 'upkeeper_init' );
