<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.gdexpress.com
 * @since             1.0.0
 * @package           Gdex_Prime
 *
 * @wordpress-plugin
 * Plugin Name:       myGDEX Prime for Woocommerce
 * Plugin URI:        https://myprime.gdexpress.com
 * Description:       WooCommerce integration for myGDEX Prime.
 * Version:           2.0.2
 * Author:            GDEX
 * Author URI:        https://www.gdexpress.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gdex-prime
 * Domain Path:       /languages
 *
 * WC requires at least: 4.0.0
 * WC tested up to: 8.9
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GDEX_PRIME_VERSION', '2.0.2' );
define( 'GDEX_PRIME_PLUGIN_ID', 'gdex-prime' );
define( 'GDEX_PRIME_PRODUCTION_API_URL', 'https://myopenapi.gdexpress.com/api/prime/' );
define( 'GDEX_PRIME_PRODUCTION_API_SUBSCRIPTION_KEY', 'b1937ed30a4244f48788a14f31026f6c' );
define( 'GDEX_PRIME_TESTING_API_URL', 'https://myopenapi.gdexpress.com/api/demo/prime/' );
define( 'GDEX_PRIME_TESTING_API_SUBSCRIPTION_KEY', '2ea02a25449046f2816f8c771700d3c7' );
define( 'GDEX_PRIME_TIMEZONE', 'Asia/Kuala_Lumpur' );
define( 'GDEX_PRIME_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'GDEX_PRIME_PLUGIN_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'GDEX_PRIME_TESTING', false );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gdex-prime-activator.php
 */
function activate_gdex_prime() {
	require_once GDEX_PRIME_PLUGIN_PATH . 'includes/class-gdex-prime-activator.php';
	Gdex_Prime_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gdex-prime-deactivator.php
 */
function deactivate_gdex_prime() {
	require_once GDEX_PRIME_PLUGIN_PATH . 'includes/class-gdex-prime-deactivator.php';
	Gdex_Prime_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gdex_prime' );
register_deactivation_hook( __FILE__, 'deactivate_gdex_prime' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require GDEX_PRIME_PLUGIN_PATH . 'includes/class-gdex-prime.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gdex_prime() {
	new Gdex_Prime();
}

run_gdex_prime();