<?php
/**
 * Plugin Name: Client Admin Cleanup
 * Description: This WordPress plugin hides and restricts access to some parts of the wp-admin for clients.
 * Version: 1.0.0
 * Author: Upperdog
 * Author URI: https://upperdog.com
 * Author Email: hello@upperdog.com
 * License: GPLv2 or later
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

class Client_Admin_Cleanup {
	
	/**
	 * Construct
	 */
	function __construct() {
		add_action( 'admin_init', array( $this, 'block_admin_pages') );
		add_action( 'admin_menu', array( $this, 'remove_admin_menu_items' ) );
		add_action( 'admin_bar_menu', array( $this, 'remove_admin_bar_customize_link' ), 999 );
		add_action( 'customize_register', array( $this, 'remove_customizer_custom_css' ), 15 );
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
	}
	
	/**
	 * Check if current user is allowed
	 */
	function allow_current_user() {
		$current_user = wp_get_current_user();
		$default_user_id = 1;

		if ( is_a( get_user_by( 'ID', $default_user_id ), 'WP_User' ) ) {
			$default_user = get_user_by( 'ID', $default_user_id );
			$default_allowed_users = array( $default_user->data->user_login );
		} else {
			$default_allowed_users = array();
		}
		
		$allowed_users = apply_filters( 'client_admin_cleanup_allowed_users', $default_allowed_users );
		
		if ( in_array( $current_user->user_login, $allowed_users ) ) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Remove admin menu items
	 */
	function remove_admin_menu_items() {
		
		global $submenu; 
		
		if ( !$this->allow_current_user() ) {
			
			// Dashboard -> Plugins
			remove_submenu_page( 'index.php', 'update-core.php' );
			
			// Jetpack
			remove_menu_page( 'jetpack' );
			
			// Customize
			unset( $submenu[ 'themes.php' ][ 6 ] );
			
			// Themes
			remove_submenu_page( 'themes.php', 'themes.php' );
			
			//  Plugins
			remove_menu_page( 'plugins.php' );
		}
	}
	
	/**
	 * Remove admin bar customize link
	 */
	function remove_admin_bar_customize_link( $wp_admin_bar ) {
		
		if ( !$this->allow_current_user() ) {
	    	$wp_admin_bar->remove_menu( 'customize' );
	    }
	}
	
	/**
	 * Remove customizer custom CSS
	 */
	function remove_customizer_custom_css( $wp_customize ) {
		
		if ( !$this->allow_current_user() ) {
			$wp_customize->remove_section( 'custom_css' );
		}
	}
	
	/**
	 * Block admin pages
	 */
	function block_admin_pages() {
		
		if ( !$this->allow_current_user() ) {
			
			global $pagenow;
			
			$blocked_admin_pages = array( 
				'customize.php', 
				'plugins.php', 
				'plugin-install.php', 
				'plugin-editor.php', 
				'themes.php', 
				'update-core.php'
			);
			
			if ( in_array( $pagenow, $blocked_admin_pages ) ) {
				wp_redirect( admin_url( '/' ) );
				exit;
			}
		}
	}
	
	/**
	 * Remove dashboard widgets
	 */
	function remove_dashboard_widgets () {
		
		if ( !$this->allow_current_user() ) {
			remove_meta_box( 'dashboard_primary','dashboard','side' );
			remove_meta_box( 'dashboard_secondary','dashboard','side' );
			remove_meta_box( 'dashboard_plugins','dashboard','normal' );
			remove_action( 'welcome_panel','wp_welcome_panel' );
		}
	}
}

$client_admin_cleanup = new Client_Admin_Cleanup();