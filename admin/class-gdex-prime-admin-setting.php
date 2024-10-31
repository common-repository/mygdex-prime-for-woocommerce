<?php

class Gdex_Prime_Admin_Setting {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->define_hooks();
	}

	public function define_hooks() {
		add_filter( 'woocommerce_shipping_methods', [ $this, 'add_shipping_method' ] );
	}

	public function add_shipping_method( $methods ) {
		require_once GDEX_PRIME_PLUGIN_PATH . 'includes/woocommerce/class-gdex-prime-shipping-method.php';

		$methods['gdex_prime'] = Gdex_Prime_Shipping_Method::class;

		return $methods;
	}
}