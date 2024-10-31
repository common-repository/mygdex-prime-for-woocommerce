<?php

class Gdex_Prime_Admin_Shipment_Order {
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->define_hooks();
	}

	public function define_hooks() {
		add_filter( 'bulk_actions-edit-shop_order', [ $this, 'add_push_order_bulk_action' ], 20 );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', [ $this, 'add_push_order_bulk_action' ], 20 );

		add_filter( 'handle_bulk_actions-edit-shop_order', [ $this, 'handle_push_order_bulk_actions' ], 10, 3 );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', [ $this, 'handle_push_order_bulk_actions' ], 10, 3 );

		add_filter( 'manage_edit-shop_order_columns', [ $this, 'add_order_shipment_column_header' ], 20 );
		add_action( 'manage_shop_order_posts_custom_column', [ $this, 'add_order_shipment_column_content' ], 20, 2 );

		add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_order_shipment_column_header' ], 20 );
		add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'add_order_shipment_column_content' ], 20, 2 );

		add_action( 'add_meta_boxes', [ $this, 'add_shipment_order_meta_box' ], 10, 2 );

		add_action( 'wp_ajax_gdex-prime-push-order', [ $this, 'ajax_push_order' ] );

		switch ( gdex_prime_order_push_status() ) {
			case 'wc-processing':
				add_action( 'woocommerce_order_status_processing', [ $this, 'push_woocommerce_order' ] );
				break;
			case 'wc-completed':
				add_action( 'woocommerce_order_status_completed', [ $this, 'push_woocommerce_order' ] );
				break;
		}
	}

	/**
	 * Add push order bulk actions
	 *
	 * @param $actions
	 *
	 * @return array
	 */
	public function add_push_order_bulk_action( $actions ) {
		$actions['gdex_prime_push_order'] = __( 'myGDEX Prime - Send order', 'gdex-prime' );

		return $actions;
	}

	/**
	 * Handle push order bulk actions
	 *
	 * @param $redirect_url
	 * @param $action
	 * @param $order_ids
	 *
	 * @return string
	 */
	public function handle_push_order_bulk_actions( $redirect_url, $action, $order_ids ) {
		if ( $action !== 'gdex_prime_push_order' ) {
			return $redirect_url;
		}

		$success_orders = [];
		$failed_orders  = [];

		foreach ( $order_ids as $order_id ) {
			try {
				$this->push_woocommerce_order( $order_id );
				$success_orders[] = $order_id;
			} catch ( Exception $exception ) {
				$failed_orders[] = $order_id;
			}
		}

		if ( $success_orders ) {
			$notice['message'] = __( 'Selected orders successfully sent to myGdex Prime.', 'gdex-prime' );
			$notice['list']    = $success_orders;

			gdex_prime_add_admin_notice( $notice, 'success' );
		}

		if ( $failed_orders ) {
			$notice['message'] = __( 'Selected orders failed sent to myGdex Prime.', 'gdex-prime' );
			$notice['list']    = $failed_orders;

			gdex_prime_add_admin_notice( $notice, 'error' );
		}

		return $redirect_url;
	}

	/**
	 * Add estimate column header
	 *
	 * @param $columns
	 *
	 * @return array
	 */
	public function add_order_shipment_column_header( $columns ) {
		$new_columns = [];

		foreach ( $columns as $column_name => $column_info ) {
			$new_columns[ $column_name ] = $column_info;

			if ( 'order_total' === $column_name ) {
				$new_columns['gdex_prime_shipment_order_id'] = __( 'myGDEX Prime Order #', 'gdex-prime' );
			}
		}

		return $new_columns;
	}

	/**
	 * Add shipment column content
	 *
	 * @param string $column
     * @param WP_Post|WC_Order $order
	 *
	 * @throws \Exception
	 */
	public function add_order_shipment_column_content(
        string $column,
        $order
    ) {
		if ( $column !== 'gdex_prime_shipment_order_id' ) {
			return;
		}

		$order = $order instanceof WC_Order ? $order : wc_get_order($order);
		if ($order === false) {
			return;
		}

		$shipment_order_id = gdex_prime_wc_order_shipment_order_id( $order );
		if ( ! $shipment_order_id ) {
			$weight = gdex_prime_wc_order_total_weight( $order );
			if ( $weight ) {
				echo ' - ';
			} else {
				?>
                <p class="error">
                    <i class="fas fa-exclamation-circle"></i> <?php
					echo __( 'Total weight is zero', 'gdex-prime' ); ?>
                </p>
				<?php
			}

			return;
		}

		echo $shipment_order_id;

		$date_format     = get_option( 'date_format' );
		$time_format     = get_option( 'time_format' );
		$datetime_format = $date_format . ' ' . $time_format;

		$shipping_order_created_at_timestamp_with_offset = gdex_prime_wc_order_shipment_order_created_at_timestamp_with_offset( $order );
		?>
        <br>
        <small>
            <time
                datetime="<?php
				echo date_i18n( 'c', $shipping_order_created_at_timestamp_with_offset ); ?>"
                title="Created at <?php
				echo date_i18n( $datetime_format, $shipping_order_created_at_timestamp_with_offset ); ?>"
            >
				<?php
				echo date_i18n( $date_format, $shipping_order_created_at_timestamp_with_offset ); ?>
            </time>
        </small>
		<?php
	}

	/**
	 * Add shipment order meta box
	 *
	 * @param  string  $screen
	 * @param  WP_Post|WC_Order  $order
	 */
	public function add_shipment_order_meta_box(
		string $screen,
		$order
	) {
		$order = $order instanceof WC_Order ? $order : wc_get_order($order);
		if ($order === false) {
			return;
		}

		$order_weight = gdex_prime_wc_order_total_weight( $order );
		if ( ! $order_weight ) {
			return;
		}

		add_meta_box(
			'gdex-prime-shipment-order-meta-box',
			__( 'myGDEX Prime Shipment Order', 'gdex-prime' ),
			[ $this, 'render_shipment_order_meta_box' ],
			$screen,
			'side',
			'high'
		);
	}

	/**
	 * Render shipment order meta box
	 *
	 * @param  WP_Post|WC_Order  $order
	 */
	public function render_shipment_order_meta_box(
		$order
	) {
		$order = $order instanceof WC_Order ? $order : wc_get_order($order);

		include_once __DIR__ . '/partials/shipment-order-meta-box.php';
	}

	public function ajax_push_order() {
		check_ajax_referer( 'gdex-prime-push-order', 'nonce' );

		$order = wc_get_order( wc_clean( $_REQUEST['order_id'] ) );
		if ( ! $order ) {
			throw new InvalidArgumentException( __( 'Invalid order', 'gdex-prime' ) );
		}

		$this->push_woocommerce_order( $order );

		$gdex_prime_shipment_order_id                   = gdex_prime_wc_order_shipment_order_id( $order );
		$gdex_prime_shipment_order_created_at_date_i18n = gdex_prime_wc_order_shipment_order_created_at_date_i18n( $order );

		wp_send_json_success( [
			'id'         => $gdex_prime_shipment_order_id,
			'created_at' => $gdex_prime_shipment_order_created_at_date_i18n,
		] );

		wp_die();
	}

	public function push_woocommerce_order( $order ) {
		$order = wc_get_order( $order );

		$gdex_prime_shipment_order_id = gdex_prime_api_create_shipment_order( $order );
		if ( ! $gdex_prime_shipment_order_id ) {
			return false;
		}

		$order->update_meta_data( 'gdex_prime_shipment_order_id', $gdex_prime_shipment_order_id );
		$order->update_meta_data( 'gdex_prime_shipment_order_created_at', time() );
		$order->save_meta_data();

		return $gdex_prime_shipment_order_id;
	}
}