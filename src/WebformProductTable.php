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
 * Time:        3:54 PM
 */

namespace Drupal\webform_product_table;


use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;

class WebformProductTable {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $db;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\webform_product_table\EntityHelper
   */
  protected $entityHelper;

  /**
   * WebformProductTable constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\webform_product_table\EntityHelper $entityHelper
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    Connection $database,
    EntityTypeManagerInterface $entityTypeManager,
    EntityHelper $entityHelper
  ) {
    $this->configFactory = $configFactory;
    $this->db = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->entityHelper = $entityHelper;
  }


  /**
   * Get bundle settings
   *
   * @param null $entity_type_id
   * @param null $bundle_name
   *
   * @return array|bool|mixed|null
   */
  public function getBundleSettings($entity_type_id = NULL, $bundle_name = NULL) {
    if (NULL !== $entity_type_id) {
      $bundle_name = empty($bundle_name) ? $entity_type_id : $bundle_name;
      $bundle_settings = $this->configFactory
        ->get("webform_product_table.bundle_settings.$entity_type_id.$bundle_name")
        ->get();
      return !empty($bundle_settings) ? $bundle_settings : FALSE;
    }
    else {
      $config_names = $this->configFactory->listAll('webform_product_table.bundle_settings.');
      $all_settings = [];
      foreach ($config_names as $config_name) {
        $config_name_parts = explode('.', $config_name);
        $all_settings[$config_name_parts[2]][$config_name_parts[3]] = $this->configFactory->get($config_name)->get();
      }
      return $all_settings;
    }
  }

  /**
   * Save bundle settings
   *
   * @param $entity_type_id
   * @param null $bundle_name
   * @param array $settings
   */
  public function saveBundleSettings($entity_type_id, $bundle_name = NULL, $settings = []){
    $bundle_name = empty($bundle_name) ? $entity_type_id : $bundle_name;
    if(!empty($entity_type_id)){
      $bundle_settings = $this->configFactory
        ->getEditable("webform_product_table.bundle_settings.$entity_type_id.$bundle_name");
      foreach ($settings as $setting_key => $setting) {
        $bundle_settings->set($setting_key, $setting);
      }
      $bundle_settings->save();
    }
  }

  /**
   * Get all enable bundles
   * @return array
   */
  public function getEnableBundles(){
    $bundles = $this->getBundleSettings();
    if(!empty($bundles) && isset($bundles['node_type'])){
      $available = [];
      foreach ($bundles['node_type'] as $key => $bundle){
        if($bundle['enable']){
          $available[$key] = $bundle['type'];
        }
      }
      return $available;
    }
    return [];
  }

  /**
   * Get bundle products
   *
   * @param $bundle
   * @param null $category
   * @param int $limit
   * @param int $offset
   *
   * @return array|null
   */
  public function getBundleProduct($bundle,$category = null,$limit=20,$offset=0){

    /* Get bundle settings, This will be array */
    if($bundle) {
      /* we are going to limit this code to Node_Type only */
      $settings = $this->getBundleSettings('node_type', $bundle);
      $bundle_product = [];
      if($settings['enable']){
        $category = explode(',',$category);
        $bundle_product = $this->entityHelper->getEntityProducts($settings,$category,$limit,$offset);
      }
      return $bundle_product;
    }

    return NULL;
  }

  /**
   * Check if form have product table element
   *
   * @param $form
   *
   * @return bool
   */
  public function isFormHaveProductTable($form){
    if(isset($form['elements']) & !empty($form['elements'])){
      foreach ($form['elements'] as $key => $element){
        if($element['#type'] == "webform_multiple"){
          if($element['#element']['#type'] == 'webform_product_table'){
            return $element['#element'];
          }
        } elseif($element['#type'] == "fieldset"){
          foreach ($element as $subelem){
            if(is_array($subelem) && isset($subelem['#type']) && $subelem['#type'] == "webform_multiple"){
              if($subelem['#element']['#type'] == 'webform_product_table'){
                return $subelem['#element'];
              }
            }
          }
        }
      }
    }
    return FALSE;
  }
}