<?php

use Automattic\WooCommerce\Admin\Composer\Package;

/**
 * Return gdex settings
 *
 * @param  null  $setting
 *
 * @return array
 */
function gdex_prime_settings( $setting = null ) {
	$settings = array_merge( [
		'gdex_prime_api_user_access_token' => '',
		'gdex_prime_order_push_status'     => '',
	], get_option( 'woocommerce_gdex_prime_settings', [] ) );

	if ( $setting ) {
		return $settings[ $setting ];
	}

	return $settings;
}

function gdex_prime_api_user_access_token() {
	return gdex_prime_settings( 'gdex_prime_api_user_access_token' );
}

function gdex_prime_api_url() {
	return GDEX_PRIME_TESTING ? GDEX_PRIME_TESTING_API_URL : GDEX_PRIME_PRODUCTION_API_URL;
}

function gdex_prime_api_subscription_key() {
	return GDEX_PRIME_TESTING ? GDEX_PRIME_TESTING_API_SUBSCRIPTION_KEY : GDEX_PRIME_PRODUCTION_API_SUBSCRIPTION_KEY;
}

function gdex_prime_order_push_status() {
	return gdex_prime_settings( 'gdex_prime_order_push_status' ) ?: 'wc-processing';
}

function gdex_prime_api_get_user_detail( $token = null ) {
	global $gdex_prime;

	$token = $token ?: gdex_prime_api_user_access_token();

	if ( empty( $gdex_prime['user_details'][ $token ] ) ) {
		$gdex_prime['user_details'][ $token ] = ( new Gdex_Prime_Api )->get_user_detail( $token );
	}

	return $gdex_prime['user_details'][ $token ];
}

/**
 * @param  \WC_Order  $order
 *
 * @return false|int
 */
function gdex_prime_api_create_shipment_order( WC_Order $order ) {
	$order_weight = gdex_prime_wc_order_total_weight( $order );
	if ( ! $order_weight ) {
		return false;
	}

	$shipment_content = [];

	$customer_order_item_list = [];
	foreach ( $order->get_items() as $line_item ) {
		/**
		 * @var \WC_Order_Item_Product $line_item
		 */

		$product = $line_item->get_product();
		if ( $product->is_virtual() ) {
			continue;
		}

		$shipment_content[] = sprintf( '%s x %d', $product->get_name(), $line_item->get_quantity() );

		$customer_order_item_list[] = [
			'itemSku'    => $product->get_sku(),
			'quantity'   => $line_item->get_quantity(),
			'unitPrice'  => (float) $product->get_price(),
			'totalPrice' => (float) $line_item->get_total(),
		];
	}

	$gdex_prime_shipment_order_id = ( new Gdex_Prime_Api )->create_shipment_order( [
		'customerOrderNumber'   => $order->get_id(),
		'receiverName'          => $order->get_formatted_shipping_full_name(),
		'receiverMobile'        => method_exists( $order, 'get_shipping_phone' ) && $order->get_shipping_phone()
			? $order->get_shipping_phone()
			: $order->get_billing_phone(),
		'receiverEmail'         => $order->get_billing_email(),
		'receiverAddress1'      => $order->get_shipping_address_1(),
		'receiverAddress2'      => $order->get_shipping_address_2(),
		'receiverPostcode'      => $order->get_shipping_postcode(),
		'receiverDistrict'      => $order->get_shipping_city(),
		'receiverState'         => WC()->countries->get_states( $order->get_shipping_country() )[ $order->get_shipping_state() ],
		'receiverCountryName'   => WC()->countries->get_countries()[ $order->get_shipping_country() ],
		'receiverCompany'       => $order->get_shipping_company(),
		'shipmentContent'       => implode( ', ', $shipment_content ),
		'shipmentValue'         => $order->get_subtotal(),
		'shipmentWeight'        => $order_weight,
		'orderAmount'           => (float) $order->get_total(),
		'customerOrderItemList' => $customer_order_item_list,
	] );

	return $gdex_prime_shipment_order_id;
}

function gdex_prime_is_api_user_access_token_valid( $token ) {
	try {
		gdex_prime_api_get_user_detail( $token );

		return true;
	} catch ( \Exception $exception ) {
		return false;
	}
}

function gdex_prime_is_stored_api_user_access_token_valid() {
	$token = gdex_prime_api_user_access_token();

	if ( ! $token ) {
		return false;
	}

	return gdex_prime_is_api_user_access_token_valid( $token );
}

/**
 * Woocommerce order's gdex prime shipment order id
 *
 * @param $order
 *
 * @return mixed
 *
 */
function gdex_prime_wc_order_shipment_order_id( $order ) {
	$order = wc_get_order( $order );
	if ( ! $order ) {
		throw new InvalidArgumentException( __( 'Invalid order object', 'gdex-prime' ) );
	}

	return $order->get_meta( 'gdex_prime_shipment_order_id' );
}

/**
 * Woocommerce order's shipment order created date time
 *
 * @param $order
 *
 * @return \DateTime
 * @throws \Exception
 */
function gdex_prime_wc_order_shipment_order_created_at( $order ) {
	$order = wc_get_order( $order );
	if ( ! $order ) {
		throw new InvalidArgumentException( __( 'Invalid order object', 'gdex-prime' ) );
	}

	$shipping_order_created_at = $order->get_meta( 'gdex_prime_shipment_order_created_at' );
	if ( ! $shipping_order_created_at ) {
		return;
	}

	return ( new DateTime( '@' . $shipping_order_created_at ) )->setTimezone( gdex_prime_timezone() );
}

/**
 * Woocommerce order's shipment order created timestamp with offset
 *
 * @param $order
 *
 * @return int
 * @throws \Exception
 */
function gdex_prime_wc_order_shipment_order_created_at_timestamp_with_offset( $order ) {
	$shipment_order_created_at = gdex_prime_wc_order_shipment_order_created_at( $order );
	if ( ! $shipment_order_created_at ) {
		return;
	}

	return $shipment_order_created_at->getTimestamp() + $shipment_order_created_at->getOffset();
}

/**
 * Woocommerce order's shipment order created date i18n
 *
 * @param $order
 *
 * @return string
 * @throws \Exception
 */
function gdex_prime_wc_order_shipment_order_created_at_date_i18n( $order ) {
	$shipment_order_created_at_timestamp_with_offset = gdex_prime_wc_order_shipment_order_created_at_timestamp_with_offset( $order );
	if ( ! $shipment_order_created_at_timestamp_with_offset ) {
		return;
	}

	return date_i18n( wc_date_format() . ' ' . wc_time_format(), $shipment_order_created_at_timestamp_with_offset );
}

/**
 * Woocommerce order total weight
 *
 * @param  \WC_Order  $order
 *
 * @return float|int
 */
function gdex_prime_wc_order_total_weight( WC_Order $order ) {
	$weights = array_map( function ( WC_Order_Item $item ) {
		$product = $item->get_product();
		if ( ! $product ) {
			return 0;
		}

		$product_weight = $product->get_weight();
		if ( ! $product_weight ) {
			return 0;
		}

		return $product_weight * $item->get_quantity();
	}, $order->get_items() );

	return array_sum( $weights );
}

/**
 * Timezone
 *
 * @return \DateTimeZone
 */
function gdex_prime_timezone() {
	$timezone = get_option( 'timezone_string' );
	if ( ! $timezone ) {
		$gmt_offset = get_option( 'gmt_offset' );
		if ( $gmt_offset >= 0 ) {
			$timezone = "+{$gmt_offset}";
		}
	}

	return new DateTimeZone( $timezone );
}

/**
 * Log message
 *
 * @param $message
 * @param  string  $level
 */
function gdex_prime_log( $message, $level = WC_Log_Levels::NOTICE ) {
	( new WC_Logger )->add( 'gdex-prime', $message, $level );
}

/**
 * Log message
 *
 * @param $message
 * @param  string  $level
 */
function gdex_prime_api_log( $message, $level = WC_Log_Levels::NOTICE ) {
	( new WC_Logger )->add( 'gdex-prime-api', $message, $level );
}

/**
 * Check if remote response is error
 *
 * @param $response
 *
 * @return bool
 */
function gdex_prime_wp_remote_is_error( $response ) {
	$response_code = wp_remote_retrieve_response_code( $response );
	if ( $response_code === '' ) {
		return true;
	}

	$response_code_type = (int) floor( $response_code / 100 );

	return $response_code_type === 4 || $response_code_type === 5;
}

/**
 * Add admin notice
 *
 * @param $notice
 * @param $type
 *
 * @throws \Exception
 */
function gdex_prime_add_admin_notice( $notice, $type ) {
	if ( ! is_array( $notice ) ) {
		$notice = [
			'message' => $notice,
		];
	}

	if ( empty ( $notice['message'] ) ) {
		throw new \RuntimeException( 'notice message is missing.' );
	}

	$notice['message'] = wp_kses_post( $notice['message'] );

	$notice = array_merge( [
		'title'   => '',
		'message' => '',
		'list'    => [],
	], $notice );

	$notice['type'] = $type;

	$notices   = gdex_prime_get_admin_notices();
	$notices[] = $notice;

	gdex_prime_set_admin_notices( $notices );
}

/**
 * Get admin noitces
 *
 * @return false|mixed|void
 */
function gdex_prime_get_admin_notices() {
	return get_option( 'gdex_prime_admin_notices', [] );
}

/**
 * @param $notices
 *
 * @return bool
 */
function gdex_prime_set_admin_notices( $notices ) {
	return update_option( 'gdex_prime_admin_notices', $notices );
}

/**
 * Clean admin notices
 */
function gdex_prime_clear_admin_notices() {
	delete_option( 'gdex_prime_admin_notices' );
}