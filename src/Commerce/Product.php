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
 * Time: 9:01 PM
 */
namespace Drupal\webform_product_table\Commerce;

class Product implements ProductInterface {

  /**
   * Product item content type
   *
   * @var string $type
   */
  protected $type;

  /**
   * Product item title
   *
   * @var string $title
   */
  protected $title;

  /**
   * Product item node id
   *
   * @var integer $originId
   */
  protected $originId;

  /**
   * Product item number
   *
   * @var integer $number
   */
  protected $number;

  /**
   * Product item description
   *
   * @var string $description
   */
  protected $description;

  /**
   * Product item price
   *
   * @var float $price
   */
  protected $price;

  /**
   * Product item discount
   *
   * @var integer $discount
   */
  protected $discount;


  /**
   * Product constructor.
   *
   * @param $originId
   * @param $type
   * @param $title
   * @param $price
   */
  public function __construct($originId,$type,$title,$price) {

    $this->set('originId',$originId)
      ->set('type',$type)
      ->set('title',$title)
      ->set('price',floatval($price));

  }

  /**
   * Set property of product
   *
   * @param $property
   * @param null $value
   *
   * @return $this
   */
  public function set($property, $value = NULL) {
    $this->$property = $value;
    return $this;
  }

  /**
   * Return property value of product
   *
   * @param $property
   *
   * @return mixed
   */
  public function get($property) {
    return $this->$property;
  }
}