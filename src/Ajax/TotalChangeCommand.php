<?php
/**
 * d8-webform
 *
 * @package     d8-webform
 * @author      Udit Rawat <uditrawat@fabwebstudio.com>
 * @license     GPL-2.0+
 * @link        http://fabwebstudio.com/
 * @copyright   Fab Web Studio
 * Date:        8/18/2018
 * Time:        1:46 PM
 */

namespace Drupal\webform_product_table\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class TotalChangeCommand implements CommandInterface{

  protected $element_id;
  protected $productTotal;
  protected $total;

  public function __construct($element_id,$productTotal,$total) {
    $this->element_id = $element_id;
    $this->productTotal = $productTotal;
    $this->total = $total;
  }

  public function render()
  {
    return [
      'command' => 'totalChangeCommand',
      'element_id' => $this->element_id,
      'productTotal' => $this->productTotal,
      'total' => $this->total
    ];
  }

}