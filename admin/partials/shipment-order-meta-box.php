<?php
/** @var WC_Order $order */

$order_weight = gdex_prime_wc_order_total_weight( $order );

$shipment_order_id                   = gdex_prime_wc_order_shipment_order_id( $order );
$shipment_order_created_at_date_i18n = gdex_prime_wc_order_shipment_order_created_at_date_i18n( $order );
?>

<p
  class="form-field gdex-prime-shipment-order-meta-box-id_field"
>
  <label for="gdex-prime-shipment-order-meta-box-id">Order #</label>
  <input
    v-model="id"
    type="text"
    id="gdex-prime-shipment-order-meta-box-id"
    class="short"
    name="id"
    readonly="readonly"
  >
  <span
    v-if="id"
    v-cloak
    class="description"
  >
    Created at <abbr>{{ created_at }}</abbr>
  </span>
</p>

<?php
woocommerce_wp_hidden_input( [
	'id'    => 'gdex-prime-shipment-order-meta-box-nonce',
	'value' => wp_create_nonce( 'gdex-prime-push-order' ),
] );
?>

<hr>

<button
  type="button"
  id="gdex-prime-shipment-order-meta-box-submit-button"
  class="button button-primary"
	<?php echo ! $order_weight ? 'disabled' : '' ?>
  @click="submit"
>
	<?php _e( 'Send Order', 'gdex-prime' ); ?>
</button>

<script>
  var gdex_prime_shipment_order_meta_box = <?php echo json_encode( [
	  'id'         => $shipment_order_id,
	  'created_at' => $shipment_order_created_at_date_i18n,
  ], JSON_PRETTY_PRINT ); ?>
</script>