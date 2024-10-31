<?php

class Gdex_Prime_Shipping_Method extends WC_Shipping_Method {
	/**
	 * Init and hook in the integration.
	 */
	public function __construct( $instance_id = 0 ) {
		parent::__construct( $instance_id );

		$this->id                 = 'gdex_prime';
		$this->method_title       = __( 'myGDEX Prime', 'gdex-prime' );
		$this->method_description = __( 'To start syncing myGDEX Prime shipments, please fill in your user credentials as shown in your myGDEX Prime user portal.', 'gdex-prime' );

		$this->init();
	}

	/**
	 * Init setting
	 */
	public function init() {
		$this->init_form_fields();
		$this->init_settings();

		add_action( "woocommerce_update_options_shipping_{$this->id}", [ $this, 'process_admin_options' ] );
		add_action( "woocommerce_update_options_shipping_{$this->id}", [ $this, 'display_errors' ] );
	}

	/**
	 * Initialise form fields.
	 */
	public function init_form_fields() {
		$this->init_api_form_fields();

		if ( gdex_prime_is_stored_api_user_access_token_valid() ) {
			$this->init_user_details_form_fields();
			$this->init_order_form_fields();
		}
	}

	/**
	 * Initialise Settings.
	 *
	 * Store all settings in a single database entry
	 * and make sure the $settings array is either the default
	 * or the settings stored in the database.
	 *
	 * @since 1.0.0
	 * @uses get_option(), add_option()
	 */
	public function init_settings() {
		parent::init_settings();

		if ( gdex_prime_is_stored_api_user_access_token_valid() ) {
			$user_detail = gdex_prime_api_get_user_detail();

			$this->settings['gdex_prime_user_details_customer_account_no'] = $user_detail['customerAccountNo'];
			$this->settings['gdex_prime_user_details_company_name']        = $user_detail['companyName'];
		}
	}

	/**
	 * Initialise api form fields
	 */
	public function init_api_form_fields() {
		$fields['gdex_prime_api'] = [
			'title'       => __( 'API', 'gdex-prime' ),
			'type'        => 'title',
			'description' => __( 'Configure your access towards the myGDEX Prime APIs by means of authentication.', 'gdex' ),
		];

		$fields['gdex_prime_api_user_access_token'] = [
			'title'             => __( 'User Access Token', 'gdex-prime' ),
			'type'              => 'text',
			'description'       => __( 'Go to <a href="https://myprime.gdexpress.com/apiToken" target="_blank">myGDEX Prime API Token</a> page, generate and obtain your user access token.',
				'gdex-prime' ),
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$this->form_fields += $fields;
	}

	/**
	 * Initialise api form fields
	 */
	public function init_user_details_form_fields() {
		$fields['gdex_prime_user_details'] = [
			'title'       => __( 'User Details', 'gdex-prime' ),
			'type'        => 'title',
			'description' => __( 'Configure your user details underneath.', 'gdex-prime' ),
		];

		$fields['gdex_prime_user_details_customer_account_no'] = [
			'title'             => __( 'Customer Account No', 'gdex-prime' ),
			'type'              => 'text',
			'disabled'          => true,
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$fields['gdex_prime_user_details_company_name'] = [
			'title'             => __( 'Company Name', 'gdex-prime' ),
			'type'              => 'text',
			'disabled'          => true,
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$this->form_fields += $fields;
	}

	public function init_order_form_fields() {
		$fields['gdex_prime_order'] = [
			'title'       => __( 'Order', 'gdex-prime' ),
			'type'        => 'title',
			'description' => __( 'Configure your order parameters underneath.', 'gdex-prime' ),
		];

		$fields['gdex_prime_order_push_status'] = [
			'title'             => __( 'Push Status', 'gdex-prime' ),
			'type'              => 'select',
			'options'           => [
				'wc-processing' => _x( 'Processing', 'Order status', 'woocommerce' ),
				'wc-completed'  => _x( 'Completed', 'Order status', 'woocommerce' ),
			],
			'default'           => 'wc-processing',
			'description'       => __( 'Default order status used for shipment creation.', 'gdex-prime' ),
			'custom_attributes' => [ 'required' => 'required' ],
		];

		$this->form_fields += $fields;
	}

	/**
	 * Validate api user access token field
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return mixed
	 */
	public function validate_gdex_prime_api_user_access_token_field( $key, $value ) {
		$is_token_valid = gdex_prime_is_api_user_access_token_valid( $value );
		if ( ! $is_token_valid ) {
			throw new \RuntimeException( __( 'user access token is invalid.', 'gdex-prime' ) );
		}

		return $value;
	}
}