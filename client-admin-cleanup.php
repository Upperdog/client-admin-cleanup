<?php
/**
 * Plugin Name: Client Admin Cleanup
 * Description: This plugin removes and cleans up some parts of the WordPress admin that should not be available to the client.
 * Author: Upperdog
 * Author URI: https://upperdog.com
 */

if ( !function_exists( 'wp_get_current_user' ) ) {
    include( ABSPATH . 'wp-includes/pluggable.php' );
}

function client_admin_cleanup_get_users() {
	$client_admin_cleanup_users = array(
		'super_admins' => get_super_admins(), 
		'current_user' => wp_get_current_user(), 
	);
	
	return $client_admin_cleanup_users;
}

class ClientAdminCleanup {
	
	function __construct() {
		$client_admin_cleanup_users = client_admin_cleanup_get_users();
		
		if ( !in_array( $client_admin_cleanup_users[ 'current_user' ]->user_login, $client_admin_cleanup_users[ 'super_admins' ] ) ) {
			
			if ( is_admin() ) {
				add_action( 'admin_init', array( $this, 'disallowed_admin_pages') );
				add_action( 'admin_bar_menu', array( $this, 'remove_admin_bar_customize_link' ), 999 );
				add_action( 'admin_menu', array( $this, 'remove_admin_menu_items' ) );
				add_action( 'customize_register', array( $this, 'remove_customizer_custom_css' ), 15 );
				add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
			}
			
			$this->disallow_file_edit_and_mods();
		}
	}
	
	function remove_admin_menu_items() {
		
		// Dashboard -> Plugins
		remove_submenu_page( 'index.php', 'update-core.php' );
		
		// Jetpack
		remove_menu_page( 'jetpack' );
		
		// Themes
		remove_submenu_page( 'themes.php', 'themes.php' );
		
		//  Plugins
		remove_menu_page( 'plugins.php' );
	}
	
	function disallow_file_edit_and_mods() {
		
		// Disallow file edit
		if ( !defined( 'DISALLOW_FILE_EDIT' ) ) {
			define( 'DISALLOW_FILE_EDIT', true );
		}
		
		// Disallow file mods
		if ( !defined( 'DISALLOW_FILE_MODS' ) ) {
			define( 'DISALLOW_FILE_MODS', true );
		}
	}
	
	function remove_admin_bar_customize_link( $wp_admin_bar ) {
	    $wp_admin_bar->remove_menu( 'customize' );
	}
	
	function remove_customizer_custom_css( $wp_customize ) {
		$wp_customize->remove_section( 'custom_css' );
	}
	
	function disallowed_admin_pages() {
		global $pagenow;
		
		$disallowed_pages = array( 'themes.php', 'plugins.php', 'update-core.php' );
		
		if ( in_array( $pagenow, $disallowed_pages ) ) {
			wp_redirect( admin_url( '/' ) );
			exit;
		}
	}
	
	function remove_dashboard_widgets () {
		remove_meta_box( 'dashboard_primary','dashboard','side' );
		remove_meta_box( 'dashboard_secondary','dashboard','side' );
		remove_meta_box( 'dashboard_plugins','dashboard','normal' );
		remove_action( 'welcome_panel','wp_welcome_panel' );
	}
}

$client_admin_cleanup = new ClientAdminCleanup();