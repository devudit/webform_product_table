(function ($, Drupal, drupalSettings, window) {

  "use strict";

  let systemAjax = function ($url) {
    $.ajax({
      url: $url,
      success: function (data) {
        let ajaxObject = Drupal.ajax({
          url: "",
          base: false,
          element: false,
          progress: false
        });
        // Then, simulate an AJAX response having arrived, and let the Ajax
        // system handle it.
        ajaxObject.success(data, "success");
      }
    });
  };

  let productChange = function ($element_id, $product_id) {
    if($element_id){
      systemAjax("/wpt/ajax/product-change/" + $element_id + "/" + $product_id);
    }
  };

  let quantityChange = function ($element_id, $product_id) {
    if($element_id){
      systemAjax("/wpt/ajax/quantity-change/" + $element_id + "/" + $product_id);
    }
  };

  /**
   * Drupal Ajax behaviours and ajax prototypes
   * @type {{attach: attach, detach: detach}}
   */
  Drupal.behaviors.wptAjax = {
    attach: function (context, settings) {

      $(context).find('.triggerProductChange').on('change',function(){
        let product_id = $(this).val();
        let element_id = $(this).attr('id');
        productChange(element_id,product_id);
      });

      $(context).find('.triggerProductQtyChange').on('change',function(){
        let element_id = $(this).attr('id');
        let product_id = $(this).parents('.webform-product-table--wrapper').find('.product_id').val();
        let product_quant = $(this).val();
        let product_disccount = $(this).parents('.webform-product-table--wrapper').find('.item-discount').val();
        let product_strings = product_id+'-_'+product_quant+'-_'+product_disccount;
        quantityChange(element_id,product_strings);
      });

      $(context).find('.item-discount').on('change',function(){
        let element_id = $(this).attr('id');
        let product_id = $(this).parents('.webform-product-table--wrapper').find('.product_id').val();
        let product_quant = $(this).parents('.webform-product-table--wrapper').find('.triggerProductQtyChange').val();
        let product_disccount = $(this).val();
        let product_strings = product_id+'-_'+product_quant+'-_'+product_disccount;
        quantityChange(element_id,product_strings);
      });

      /* Ajax Prototypes */
      Drupal.AjaxCommands.prototype.productChangeCommand = function (ajax, response, status) {
        let $parent = $('#'+response.element_id).parents('.webform-product-table--wrapper');
        $parent.find('.item-number').val(response.number);
        $parent.find('.item-description').val(response.description);
        $parent.find('.item-price').val(response.price);
      };
      Drupal.AjaxCommands.prototype.totalChangeCommand = function (ajax, response, status) {
        let $parent = $('#'+response.element_id).parents('.webform-product-table--wrapper');
        let $form = $('#'+response.element_id).parents('form');
        //$parent.find('.item-price').val(response.productTotal);
        $form.find('.total-price').text(response.total);
      };

    },
    detach: function (context) {
      $(context).find('.triggerProductChange').unbind("click");
      $(context).find('.item-discount').unbind("change");
      $(context).find('.triggerProductQtyChange').unbind("change");
    }
  };

}(jQuery, Drupal, drupalSettings, window));