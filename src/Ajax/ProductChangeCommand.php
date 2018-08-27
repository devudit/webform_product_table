<?php
/**
 * Created by PhpStorm.
 * User: Coders Earth
 * Date: 4/17/2018
 * Time: 12:11 PM
 */

namespace Drupal\webform_product_table\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class ProductChangeCommand implements CommandInterface{

  protected $element_id;
  protected $product;

  public function __construct($element_id,$product) {
    $this->element_id = $element_id;
    $this->product = $product;
  }

  public function render()
  {
    return [
      'command' => 'productChangeCommand',
      'element_id' => $this->element_id,
      'number' => $this->product->get('number'),
      'description' => $this->product->get('description'),
      'price' => $this->product->get('price'),
      'discount' => $this->product->get('discount'),
    ];
  }

}