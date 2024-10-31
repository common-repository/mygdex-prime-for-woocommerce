<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.gdexpress.com
 * @since      1.0.0
 *
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/public
 * @author     GDEX <one@geeksworking.com>
 */
class Gdex_Prime_Public {

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->define_hooks();
	}

	public function define_hooks() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gdex_Prime_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gdex_Prime_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style(
			GDEX_PRIME_PLUGIN_ID . '-public',
			GDEX_PRIME_PLUGIN_URL . 'public/css/gdex-prime-public.css',
			[],
			GDEX_PRIME_VERSION, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gdex_Prime_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gdex_Prime_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script(
			GDEX_PRIME_PLUGIN_ID . '-public',
			GDEX_PRIME_PLUGIN_URL . 'js/gdex-prime-public.js',
			[ 'jquery' ],
			GDEX_PRIME_VERSION,
			false
		);
	}

}
