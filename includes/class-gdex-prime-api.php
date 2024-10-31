<?php

class Gdex_Prime_Api {
	const ENDPOINT_GET_USER_DETAIL = 'GetUserDetail';
	const ENDPOINT_CREATE_SHIPMENT_ORDER = 'CreateShipmentOrder';

	/**
	 * Get end point url
	 *
	 * @param  string  $endpoint
	 *
	 * @return string
	 */
	protected function get_endpoint_url( $endpoint ) {
		return gdex_prime_api_url() . $endpoint;
	}

	/**
	 * Get http headers
	 *
	 * @param  array  $headers
	 *
	 * @return array
	 */
	protected function get_headers( $headers = [] ) {
		if ( empty( $headers['Content-Type'] ) ) {
			$headers['Content-Type'] = 'application/json';
		}

		if ( empty( $headers['ApiToken'] ) ) {
			$headers['ApiToken'] = gdex_prime_api_user_access_token();
		}

		if ( empty( $headers['Subscription-Key'] ) ) {
			$headers['Subscription-Key'] = gdex_prime_api_subscription_key();
		}

		return $headers;
	}

	public function get_user_detail( $token = null ) {
		$response = wp_remote_get(
			$this->get_endpoint_url( static::ENDPOINT_GET_USER_DETAIL ),
			[
				'headers' => $this->get_headers( [ 'ApiToken' => $token ] ),
			]
		);

		return $this->parseResponse( $response );
	}

	public function create_shipment_order( array $order, $token = null ) {
		return $this->create_shipment_orders( [ $order ], $token )[0];
	}

	public function create_shipment_orders( $orders, $token = null ) {
		$body = [];

		foreach ( $orders as $order ) {
			$items = [];

			foreach ( $order['customerOrderItemList'] as $item ) {
				$items[] = array_merge( [
					'itemSku'    => null,
					'quantity'   => null,
					'unitPrice'  => null,
					'totalPrice' => null,
				], $item );
			}

			$body[] = array_merge( [
				'customerOrderNumber'   => null,
				'receiverName'          => null,
				'receiverMobile'        => null,
				// 'receiverMobile2' => null,
				'receiverEmail'         => null,
				'receiverAddress1'      => null,
				'receiverAddress2'      => null,
				// 'receiverAddress3'      => null,
				// 'receiverLocation' => null,
				'receiverPostcode'      => null,
				'receiverDistrict'      => null,
				'receiverState'         => null,
				'receiverCountryName'   => null,
				'receiverCompany'       => null,
				'shipmentContent'       => null,
				'shipmentValue'         => null,
				'shipmentWeight'        => null,
				// 'shipmentLength' => null,
				// 'shipmentWidth' => null,
				// 'shipmentHeight' => null,
				// 'volumetricWeight' => null,
				// 'remarks' => null,
				'orderAmount'           => null,
				'customerOrderItemList' => $items,
			], $order );
		}

		$response = wp_remote_post(
			$this->get_endpoint_url( static::ENDPOINT_CREATE_SHIPMENT_ORDER ),
			[
				'headers' => $this->get_headers( [ 'ApiToken' => $token ] ),
				'body'    => json_encode( $body ),
			]
		);

		return $this->parseResponse( $response );
	}

	/**
	 * @param  array|\WP_Error  $response
	 */
	protected function parseResponse( $response ) {
		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message(), (int) $response->get_error_code() );
		}

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			$response_body = wp_remote_retrieve_body( $response );
		}

		if ( gdex_prime_wp_remote_is_error( $response ) ) {
			$response_message = wp_remote_retrieve_response_message( $response );
			if ( ! empty( $response_body['e'] ) ) {
				$response_message = $response_body['e'];
			}

			throw new \RuntimeException( $response_message, (int) wp_remote_retrieve_response_code( $response ) );
		}

		if ( ! empty( $response_body['r'] ) ) {
			return $response_body['r'];
		}

		return $response_body;
	}
}