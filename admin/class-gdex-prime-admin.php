<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.gdexpress.com
 * @since      1.0.0
 *
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Gdex_Prime
 * @subpackage Gdex_Prime/admin
 * @author     GDEX <one@geeksworking.com>
 */
class Gdex_Prime_Admin {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->define_hooks();
	}

	public function define_hooks() {
		add_action( 'plugins_loaded', [ $this, 'check_woocommerce_activated' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'admin_menu', [ $this, 'print_admin_notices' ] );

		add_filter( 'http_request_timeout', [ $this, 'increase_http_request_timeout' ], 10, 2 );
		add_action( 'requests-requests.after_request', [ $this, 'log_api_response' ], 10, 4 );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( 'fontawesome', 'https://use.fontawesome.com/releases/v5.8.0/css/all.css', [], '5.8', 'all' );
		wp_enqueue_style( GDEX_PRIME_PLUGIN_ID . '-admin', GDEX_PRIME_PLUGIN_URL . 'admin/css/gdex-prime-admin.css', [], GDEX_PRIME_VERSION, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'vue',
			GDEX_PRIME_PLUGIN_URL . 'admin/js/vue.min.js',
			[],
			'2.6',
			true
		);

		wp_enqueue_script(
			GDEX_PRIME_PLUGIN_ID . '-admin',
			GDEX_PRIME_PLUGIN_URL . 'admin/js/gdex-prime-admin.js',
			[ 'jquery', 'vue' ],
			GDEX_PRIME_VERSION,
			true
		);
	}

	/**
	 * Check if Woocommerce installed
	 */
	public function check_woocommerce_activated() {
		if ( defined( 'WC_VERSION' ) ) {
			return;
		}

		add_action( 'admin_notices', [ $this, 'notice_woocommerce_required' ] );
	}

	/**
	 * Admin error notifying user that Woocommerce is required
	 */
	public function notice_woocommerce_required() {
		?>
        <div class="notice notice-error">
            <p><?php
				_e( 'GDEX requires WooCommerce to be installed and activated!', $this->plugin_name ); ?></p>
        </div>
		<?php
	}

	public function print_admin_notices() {
		foreach ( gdex_prime_get_admin_notices() as $notice ) {
			include __DIR__ . '/partials/admin-notice.php';
		}

		gdex_prime_clear_admin_notices();
	}

	/**
	 * Increase http request timeout to 30 sec
	 *
	 * @param $timeout
	 * @param $url
	 *
	 * @return int
	 */
	public function increase_http_request_timeout( $timeout, $url ) {
		return 30;
	}

	/**
	 * Log API response
	 *
	 * @param  Requests_Response|\WpOrg\Requests\Response  $response
	 * @param $req_headers
	 * @param $req_data
	 * @param $options
	 *
	 * @return void
	 */
	public function log_api_response( $response, $req_headers, $req_data, $options ) {
		$isGdexApiRequest = strpos( $response->url, gdex_prime_api_url() ) === 0;
		if ( ! $isGdexApiRequest ) {
			return;
		}

		gdex_prime_api_log( print_r( [
			'request'  => [
				'url'     => $response->url,
				'headers' => $req_headers,
				'data'    => $req_data,
				'type'    => $options['type'],
			],
			'response' => [
				'status_code' => $response->status_code,
				'body'        => $response->body,
			],
		], true ) );
	}

}
