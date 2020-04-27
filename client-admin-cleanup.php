<?php
/**
 * Plugin Name: Client Admin Cleanup
 * Description: This WordPress plugin hides and restricts access to some parts of the wp-admin for clients. It's like cleaning up the UI of things they don't need.
 * Version: 1.1.0
 * Author: Upperdog
 * Author URI: https://upperdog.com
 * Author Email: hello@upperdog.com
 * License: GPLv2 or later
 *
 * @package client-admin-cleanup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Client Admin Cleanup.
 */
class Client_Admin_Cleanup {

	/**
	 * Construct.
	 */
	public function __construct() {
		add_action( 'admin_init', array( $this, 'block_admin_pages' ) );
		add_action( 'admin_menu', array( $this, 'remove_admin_menu_items' ) );
		add_action( 'admin_bar_menu', array( $this, 'remove_admin_bar_items' ), 999 );
		add_action( 'customize_register', array( $this, 'remove_customizer_sections' ), 15 );
		add_action( 'wp_dashboard_setup', array( $this, 'remove_dashboard_widgets' ) );
	}

	/**
	 * Checks if current user is allowed.
	 *
	 * @return bool (true|false) If current user is allowed to see full UI or not.
	 */
	public function allow_current_user() {

		// Get current user.
		$current_user = wp_get_current_user();

		// Define default main user id.
		$default_user_id = 1;

		// Define default allowed users.
		if ( is_a( get_user_by( 'ID', $default_user_id ), 'WP_User' ) ) {
			$default_user          = get_user_by( 'ID', $default_user_id );
			$default_allowed_users = array( $default_user->data->user_login );
		} else {
			$default_allowed_users = array();
		}

		// Apply filter to let theme/plugin authors define allowed users.
		$allowed_users = apply_filters( 'client_admin_cleanup_allowed_users', $default_allowed_users );

		// If current user is allowd return true, otherwise false.
		if ( in_array( $current_user->user_login, $allowed_users ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Remove admin menu items.
	 *
	 * @global array $submenu WP admin submenu items.
	 */
	public function remove_admin_menu_items() {

		global $submenu;

		if ( ! $this->allow_current_user() ) {

			// Dashboard -> Updates.
			remove_submenu_page( 'index.php', 'update-core.php' );

			// Jetpack.
			remove_menu_page( 'jetpack' );

			// Customize.
			unset( $submenu['themes.php'][6] );

			// Themes.
			remove_submenu_page( 'themes.php', 'themes.php' );

			// Plugins.
			remove_menu_page( 'plugins.php' );

			// Tools.
			remove_menu_page( 'tools.php' );

			// Options/settings.
			remove_menu_page( 'options-general.php' );
		}
	}

	/**
	 * Remove items from adminbar.
	 *
	 * @param object $wp_admin_bar WP admin bar.
	 */
	public function remove_admin_bar_items( $wp_admin_bar ) {

		if ( ! $this->allow_current_user() ) {
			$wp_admin_bar->remove_node( 'customize' );
			$wp_admin_bar->remove_node( 'themes' );
		}
	}

	/**
	 * Remove customizer sections.
	 *
	 * @param object $wp_customize WP customizer object.
	 */
	public function remove_customizer_sections( $wp_customize ) {

		if ( ! $this->allow_current_user() ) {
			$wp_customize->remove_section( 'custom_css' );
			$wp_customize->remove_section( 'static_front_page' );
		}
	}

	/**
	 * Block admin pages.
	 *
	 * @global array $pagenow WP admin current page.
	 */
	public function block_admin_pages() {

		if ( ! $this->allow_current_user() ) {

			global $pagenow;

			$blocked_admin_pages = array(
				'customize.php',
				'plugins.php',
				'plugin-install.php',
				'plugin-editor.php',
				'themes.php',
				'update-core.php',
				'tools.php',
				'import.php',
				'export.php',
				'site-health.php',
				'export-personal-data.php',
				'erase-personal-data.php',
				'tools.php?page=wp-migrate-db',
				'tools.php?page=regenerate-thumbnails',
				'tools.php?page=disable_comments_tools',
				'options-general.php',
				'options-writing.php',
				'options-reading.php',
				'options-media.php',
				'options-permalink.php',
				'options-privacy.php',
				'options-general.php?page=disable_comments_settings',
			);

			if ( in_array( $pagenow, $blocked_admin_pages ) ) {
				wp_safe_redirect( admin_url( '/' ) );
				exit;
			}
		}
	}

	/**
	 * Remove dashboard widgets.
	 */
	public function remove_dashboard_widgets() {

		if ( ! $this->allow_current_user() ) {
			remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_secondary', 'dashboard', 'side' );
			remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
			remove_meta_box( 'dashboard_site_health', 'dashboard', 'normal' );
			remove_action( 'welcome_panel', 'wp_welcome_panel' );
		}
	}
}

$client_admin_cleanup = new Client_Admin_Cleanup();
