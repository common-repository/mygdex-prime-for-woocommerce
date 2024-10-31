<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.gdexpress.com
 * @since      1.0.0
 *
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/includes
 * @author     GDEX <one@geeksworking.com>
 */
class Gdex_Prime {

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->load_dependencies();
		$this->set_locale();
		$this->define_shared_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Gdex_Prime_Loader. Orchestrates the hooks of the plugin.
	 * - Gdex_Prime_i18n. Defines internationalization functionality.
	 * - Gdex_Prime_Admin. Defines all hooks for the admin area.
	 * - Gdex_Prime_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once GDEX_PRIME_PLUGIN_PATH . 'includes/class-gdex-prime-i18n.php';

		require_once GDEX_PRIME_PLUGIN_PATH . 'includes/class-gdex-prime-api.php';
		require_once GDEX_PRIME_PLUGIN_PATH . 'includes/class-gdex-prime-shipment-order.php';
		require_once GDEX_PRIME_PLUGIN_PATH . 'includes/class-gdex-prime-shared.php';

		require_once GDEX_PRIME_PLUGIN_PATH . 'includes/helpers.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once GDEX_PRIME_PLUGIN_PATH . 'admin/class-gdex-prime-admin.php';
		require_once GDEX_PRIME_PLUGIN_PATH . 'admin/class-gdex-prime-admin-setting.php';
		require_once GDEX_PRIME_PLUGIN_PATH . 'admin/class-gdex-prime-admin-shipment-order.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once GDEX_PRIME_PLUGIN_PATH . 'public/class-gdex-prime-public.php';
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gdex_Prime_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		new Gdex_Prime_i18n();
	}

	private function define_shared_hooks() {
		new Gdex_Prime_Shared();
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		new Gdex_Prime_Admin();
		new Gdex_Prime_Admin_Setting();
		new Gdex_Prime_Admin_Shipment_Order();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		new Gdex_Prime_Public();
	}
}
