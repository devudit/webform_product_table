<?php
/**
 * d8-premium
 *
 * @package   d8-premium
 * @author    Udit Rawat <eklavyarwt@gmail.com>
 * @license   GPL-2.0+
 * @link      http://emerico.in/
 * @copyright Emerico Web Solutions
 * Date: 15-Aug-18
 * Time: 9:04 PM
 */

namespace Drupal\webform_product_table\Commerce;


interface ProductInterface {

  public function set($property,$value = null);

  public function get($property);

}