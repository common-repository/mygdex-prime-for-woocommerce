<?php

use Automattic\WooCommerce\Utilities\FeaturesUtil;

class Gdex_Prime_Shared {
	public function __construct() {
		$this->define_hooks();
	}

	private function define_hooks() {
		add_action( 'before_woocommerce_init', [ $this, 'declare_hpos_compatibility' ] );
	}

	public function declare_hpos_compatibility(): void {
		if ( class_exists( FeaturesUtil::class ) ) {
			FeaturesUtil::declare_compatibility( 'custom_order_tables', GDEX_PRIME_PLUGIN_PATH . 'gdex-prime.php', true );
		}
	}

}