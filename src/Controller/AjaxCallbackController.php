<?php
/**
 * d8-webform
 *
 * @package     d8-webform
 * @author      Udit Rawat <uditrawat@fabwebstudio.com>
 * @license     GPL-2.0+
 * @link        http://fabwebstudio.com/
 * @copyright   Fab Web Studio
 * Date:        8/16/2018
 * Time:        1:13 PM
 */
namespace Drupal\webform_product_table\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\webform_product_table\Ajax\ProductChangeCommand;
use Drupal\webform_product_table\Ajax\TotalChangeCommand;
use Drupal\webform_product_table\Commerce\ProductOperations;
use Drupal\webform_product_table\EntityHelper;
use Drupal\webform_product_table\Form\FormHelper;
use Drupal\webform_product_table\WebformProductTable;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AjaxCallbackController extends ControllerBase{

  /**
   * @var \Drupal\webform_product_table\EntityHelper
   */
  protected $entityHelper;

  /**
   * @var \Drupal\webform_product_table\WebformProductTable
   */
  protected $wptConfig;


  protected $formHelper;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static
    (
      $container->get('wpt.entity_helper'),
      $container->get('wpt.config'),
      $container->get('wpt.form_helper')
    );
  }

  public function __construct(EntityHelper $entityHelper,WebformProductTable $wptConfig,FormHelper $formHelper)
  {
    $this->entityHelper = $entityHelper;
    $this->wptConfig = $wptConfig;
    $this->formHelper = $formHelper;
  }

  public function changeAjaxHandler($type,$element_id,$id){
    $response = new AjaxResponse();
    switch ($type){
      case 'product-change':
        $this->productChange($element_id,$id,$response);
        break;
      case 'quantity-change':
        $product_data = explode('-_',$id);
        $this->quantityChange($element_id,$product_data,$response);
        break;
    }
    return $response;
  }

  private function productChange($element_id,$id,&$response){
    if($id > 0 ){
      $product = $this->entityHelper->getProduct($id);
      $response->addCommand(new ProductChangeCommand($element_id,$product));
    }
  }

  private function quantityChange($element_id,$product_data,&$response){
    /**
     * $product_data[0] => Product id
     * $product_data[1] => Product Qty
     * $product_data[2] => Product Discount
     */
    if($product_data[0]){
      ProductOperations::setProductQuantity($product_data[0],$product_data[1],intval($product_data[2]));
      $product_total = ProductOperations::getProductPrice($product_data[0]);
      $total = ProductOperations::calcualteTotal();
      $response->addCommand(new TotalChangeCommand($element_id,$product_total,$total));
    }
  }

}