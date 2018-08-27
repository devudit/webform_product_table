<?php
/**
 * d8-webform
 *
 * @package     d8-webform
 * @author      Udit Rawat <uditrawat@fabwebstudio.com>
 * @license     GPL-2.0+
 * @link        http://fabwebstudio.com/
 * @copyright   Fab Web Studio
 * Date:        8/14/2018
 * Time:        12:57 PM
 */
namespace Drupal\webform_product_table;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManager;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\node\Entity\Node;
use Drupal\webform_product_table\Commerce\Product;

/**
 * Class EntityHelper
 * @package Drupal\webform_product_table
 */
class EntityHelper {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * EntityHelper constructor.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entityFieldManager, Connection $database) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entityFieldManager;
    $this->db = $database;
  }

  /**
   * Check if entity type id supported or not
   *
   * @param $entity_type_id
   *
   * @return bool
   */
  public function isSupportedEntityType($entity_type_id) {
    try {
      $entity = $this->entityTypeManager->getDefinition('node');
      if ($entity->getBundleEntityType() == $entity_type_id) {
        return TRUE;
      }
    } catch (PluginNotFoundException $pnfe){
      \Drupal::logger('wpt')->error('Supported Entity Type Error: '.$pnfe->getMessage());
    }
    return FALSE;
  }

  /**
   * Get supported entity text fields
   *
   * @param $bundle
   * @param string $entity_type_id
   *
   * @return array
   */
  public function getEntityTextFields($bundle ,$entity_type_id = 'node'){
    $allowed_fields = [
      'string',
      'image',
      'text',
      'text_long',
      'text_with_summary',
      'string_long'
    ];
    $fieldList = [];
    if(!empty($bundle)){
      $bundle_fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
      foreach($bundle_fields as $field_name => $field_definition){
        if(in_array($field_definition->getType(),$allowed_fields)) {
          if ($field_definition->getLabel() instanceof TranslatableMarkup) {
            $fieldList[$field_name] = $field_definition->getLabel()->render();
          } else {
            $fieldList[$field_name] = $field_definition->getLabel();
          }
        }
      }
    }
    return $fieldList;
  }

  /**
   * Get supported entity numeric fields
   *
   * @param $bundle
   * @param string $entity_type_id
   *
   * @return array
   */
  public function getEntityNumericFields($bundle ,$entity_type_id = 'node'){
    $allowed_fields = [
      'integer',
      'decimal',
      'float'
    ];
    $fieldList = [];
    if(!empty($bundle)){
      $bundle_fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
      foreach($bundle_fields as $field_name => $field_definition){
        if(in_array($field_definition->getType(),$allowed_fields)) {
          if ($field_definition->getLabel() instanceof TranslatableMarkup) {
            $fieldList[$field_name] = $field_definition->getLabel()->render();
          } else {
            $fieldList[$field_name] = $field_definition->getLabel();
          }
        }
      }
    }
    return $fieldList;
  }

  /**
   * Get supported entity term fields
   *
   * @param $bundle
   * @param string $entity_type_id
   *
   * @return array
   */
  public function getEntityTermFields($bundle ,$entity_type_id = 'node'){
    $allowed_fields = [
      'entity_reference'
    ];
    $fieldList = [];
    $bundle_fields = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $bundle);
    foreach($bundle_fields as $field_name => $field_definition){
      if(in_array($field_definition->getType(),$allowed_fields)) {
        if ($field_definition->getLabel() instanceof TranslatableMarkup) {
          $fieldList[$field_name] = $field_definition->getLabel()->render();
        } else {
          $fieldList[$field_name] = $field_definition->getLabel();
        }
      }
    }
    return $fieldList;
  }

  /**
   * Get entity products
   *
   * @param array $settings
   * @param array $category
   * @param int $limit
   * @param int $offset
   * @param string $entity_type
   *
   * @return array|null
   */
  public function getEntityProducts($settings=[],$category=[],$limit=20,$offset=0,$entity_type='node'){
    if(!empty($settings) && is_array($settings)){
      $products_ids = \Drupal::entityQuery($entity_type)
        ->condition('status', 1)
        ->condition('type',$settings['type']);
      if(!empty($category)){
        $products_ids->condition($settings['item_category'],$category,'IN');
      }
      $products_ids = $products_ids->range($offset,$limit)
        ->sort('nid', 'ASC')
        ->execute();

      if(!empty($products_ids)){
        $filtered_products = [];
        $products_ids = array_values($products_ids);
        foreach ($products_ids as $key => $products_id){
          $product = Node::load($products_id);

          // set product
          $title = $product->hasField($settings['item_title']) ? $product->get($settings['item_title'])->value : $product->getTitle();
          $price = $product->hasField($settings['item_price']) ? $product->get($settings['item_price'])->value : 0;
          $number = $product->hasField($settings['item_number']) ? $product->get($settings['item_number'])->value : 0;
          $description = $product->hasField($settings['item_description']) ? $product->get($settings['item_description'])->value : 0;
          $discount = $product->hasField($settings['item_discount']) ? $product->get($settings['item_discount'])->value : 0;

          $product = new Product($product->id(),$product->bundle(),$title,$price);
          // Set product attribute
          $product->set('number',$number)->set('description',$description)->set('discount',$discount);
          $filtered_products[$key] = $product;
        }

        return $filtered_products;
      }
    }
    return NULL;
  }

  /**
   * Get product by id
   *
   * @param $product_id
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\Node|\Drupal\webform_product_table\Commerce\Product|null
   */
  public function getProduct($product_id){
    if(!empty($product_id)){
      $product = Node::load($product_id);
      $settings = \Drupal::service('wpt.config')->getBundleSettings('node_type',$product->bundle());
      // set product
      $title = $product->hasField($settings['item_title']) ? $product->get($settings['item_title'])->value : $product->getTitle();
      $price = $product->hasField($settings['item_price']) ? $product->get($settings['item_price'])->value : 0;
      $number = $product->hasField($settings['item_number']) ? $product->get($settings['item_number'])->value : 0;
      $description = $product->hasField($settings['item_description']) ? $product->get($settings['item_description'])->value : 0;
      $discount = $product->hasField($settings['item_discount']) ? $product->get($settings['item_discount'])->value : 0;

      $product = new Product($product->id(),$product->bundle(),$title,$price);
      // Set product attribute
      $product->set('number',$number)->set('description',$description)->set('discount',$discount);

      return $product;
    }
    return null;
  }

  /**
   * Get category ids from name string
   *
   * @param $category
   *
   * @return array
   */
  public function getCategoryIds($category){
    $category = explode(',',$category);
    $ids = [];
    if(is_array($category)){
      foreach ($category as $cat){
        $properties['name'] = $cat;
        $terms = \Drupal::service('entity.manager')->getStorage('taxonomy_term')->loadByProperties($properties);
        if(!empty($terms)){
          $term = reset($terms);
          $ids[] = $term->id();
        }
      }
    }
    return $ids;
  }

}