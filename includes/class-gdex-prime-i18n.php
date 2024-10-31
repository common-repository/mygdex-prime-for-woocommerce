<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://www.gdexpress.com
 * @since      1.0.0
 *
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/includes
 * @author     GDEX <one@geeksworking.com>
 */
class Gdex_Prime_i18n {

	public function __construct() {
		$this->define_hooks();
	}

	protected function define_hooks() {
		add_action( 'plugins_loaded', [ $this, 'load_plugin_textdomain' ] );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'gdex-prime',
			false,
			GDEX_PRIME_PLUGIN_PATH . 'languages/'
		);
	}
}
