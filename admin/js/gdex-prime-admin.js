(function ($, Vue) {
  'use strict'

  $(document).ready(function () {
    init_order_page()
  })

  function init_order_page () {
    const adminpage = window.adminpage
    const typenow = window.typenow

    if (typenow !== 'shop_order') {
      return
    }

    if (adminpage !== 'post-new-php' && adminpage !== 'post-php' && adminpage !== 'woocommerce_page_wc-orders') {
      return
    }

    init_shipment_order_meta_box()

    function init_shipment_order_meta_box () {
      const $box = $('#gdex-prime-shipment-order-meta-box')
      if (!$box.length) {
        return
      }

      new Vue({
        el: $box[0],

        data: {
          id: gdex_prime_shipment_order_meta_box.id,
          created_at: gdex_prime_shipment_order_meta_box.created_at
        },

        computed: {
          $box () {
            return $(this.$el)
          },
        },

        methods: {
          submit () {
            block_meta_box(this.$box)

            var vm = this

            $.post(woocommerce_admin_meta_boxes.ajax_url, {
              action: 'gdex-prime-push-order',
              order_id: woocommerce_admin_meta_boxes.post_id,
              nonce: $('#gdex-prime-shipment-order-meta-box-nonce').val(),
            }, function (response) {
              if (response.success) {
                vm.id = response.data.id
                vm.created_at = response.data.created_at
              }

              unblock_meta_box(vm.$box)
            })
          }
        }
      })
    }
  }

  function block_meta_box ($box) {
    $box.block({
      message: null,
      overlayCSS: {
        background: '#fff',
        opacity: 0.6
      }
    })
  }

  function unblock_meta_box ($box) {
    $box.unblock()
  }

})(jQuery, Vue)
