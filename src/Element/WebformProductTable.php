<?php

namespace Drupal\webform_product_table\Element;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Element\WebformCompositeBase;

/**
 * Provides a 'webform_product_table'.
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("webform_product_table")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_product_table\Element\WebformProductTable
 */
class WebformProductTable extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + ['#theme' => 'webform_product_table'];
  }

  /**
   * {@inheritdoc}
   */
  public static function processWebformCompositeElementsRecursive(&$element, array &$composite_elements, FormStateInterface $form_state, &$complete_form) {
    $index = $element['#parents'][2];
    if(is_numeric($index)){
      $content_type = isset($element['#content_type']) ? $element['#content_type'] : '';
      $categories = isset($element['#categories']) ? $element['#categories'] : '';
      $products = \Drupal::service('wpt.config')->getBundleProduct($content_type,$categories,1,$index);

      // Fill form values
      if(!empty($products)){
        $product = $products[0];
        if(isset($element['#value']['item_title']) && empty($element['#value']['item_title'])){
          $element['#value'] = [
            'item_quantity' => 0,
            'item_title' => $product->get('title'),
            'item_id' => $product->get('originId'),
            'item_number' => $product->get('number'),
            'item_description' => $product->get('description'),
            'item_price' => $product->get('price'),
            'item_discount' => 0,
          ];
        }
      }
    }
    parent::processWebformCompositeElementsRecursive($element, $composite_elements, $form_state, $complete_form);
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element) {
    // Generate an unique ID that can be used by #states.
    $html_id = Html::getUniqueId('webform_product_table');

    $elements = [];
    $elements['item_quantity'] = [
      '#type' => 'number',
      '#title' => t('Quantity'),
      '#attributes' => [
        'data-wpt-id' => $html_id . '--item_quantity',
        'class' => ['triggerProductQtyChange'],
      ]
    ];
    $elements['item_id'] = [
      '#type' => 'hidden',
      '#attributes' => [
        'class' => ['product_id']
      ]
    ];
    $elements['item_title'] = [
      '#type' => 'textfield',
      '#title' => t('Item Title'),
      '#attributes' => [
        'data-wpt-id' => $html_id . '--item_title',
        'class' => ['triggerProductChange'],
        'readonly' => 'readonly',
        'disabled' => 'disabled'
      ]
    ];

    $elements['item_number'] = [
      '#type' => 'number',
      '#title' => t('Item Number'),
      '#attributes' => [
        'data-wpt-id' => $html_id . '--item_number',
        'class' => ['item-number'],
        'readonly' => 'readonly',
        'disabled' => 'disabled'
      ],
    ];

    $elements['item_description'] = [
      '#type' => 'textfield',
      '#title' => t('Item Description'),
      '#attributes' => [
        'data-wpt-id' => $html_id . '--item_description',
        'class' => ['item-description'],
        'readonly' => 'readonly',
        'disabled' => 'disabled'
      ],
    ];

    $elements['item_price'] = [
      '#type' => 'number',
      '#title' => t('Item Price'),
      '#attributes' => [
        'data-wpt-id' => $html_id . '--item_price',
        'class' => ['item-price'],
        'readonly' => 'readonly',
        'disabled' => 'disabled'
        ],
    ];

    $elements['item_discount'] = [
      '#type' => 'number',
      '#title' => t('Item Discount'),
      '#attributes' => [
        'data-wpt-id' => $html_id . '--item_discount',
        'class' => ['item-discount']
      ],
      '#suffix' => '<span class="product-percent">%</span>'
    ];

    return $elements;
  }

}
