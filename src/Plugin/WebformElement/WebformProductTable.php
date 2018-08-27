<?php

namespace Drupal\webform_product_table\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform_product_table\Commerce\ProductOperations;

/**
 * Provides a 'webform_product_table' element.
 *
 * @WebformElement(
 *   id = "webform_product_table",
 *   label = @Translation("Webform Product Table"),
 *   description = @Translation("Content type as product representation."),
 *   category = @Translation("Product Element"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_product_table\Element\WebformProductTable
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class WebformProductTable extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return [
        'content_type' => '',
        'limit' => 20,
        'categories' => '',
        'currency_text' => '',
        'multiple' => true,
        'flexbox' => true
      ] + parent::getDefaultProperties();
  }

  /**
   * {@inheritdoc}
   * @param array $element
   * @param \Drupal\webform\WebformSubmissionInterface|NULL $webform_submission
   */
  public function prepare(array &$element, WebformSubmissionInterface $webform_submission = NULL) {
    parent::prepare($element, $webform_submission);
    $element['#flexbox'] = true;
    $element['#multiple'] = true;
  }

  /**
   * {@inheritdoc}
   * @param array $element
   */
  protected function prepareMultipleWrapper(array &$element) {
    parent::prepareMultipleWrapper($element);

    // Remove operations
    $element['#operations'] = false;
    // Add rows and total
    $products = $this->getProductByElementProperty($element);
    $element['#empty_items'] = count($products);
    $element['#item_total'] = ProductOperations::calcualteTotal();
    // Add key
    $element['#is_product_table'] = true;

  }

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);

    $item_title = $value['item_title'] ? ProductOperations::getItemTitle($value['item_title']) : '';
    $item_quantity = $value['item_quantity'] ? $value['item_quantity'] : '';
    $item_number = $value['item_number'] ? $value['item_number'] : '';
    $item_discount = $value['item_discount'] ? $value['item_discount'].'%' : '0%';
    $item_price = $value['item_price'] ? $value['item_price'] : 0;
    $item_description  = $value['item_description'] ? $value['item_description'] : '';


    $lines = [];
    $lines[] = $item_title. ' - qty('.$item_quantity.') - price('.$item_price.') - '.$item_number. ' - ' .$item_discount. ' - ' .$item_description;
    return $lines;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $available_bundle = \Drupal::service('wpt.config')->getEnableBundles();

    $bundle = 'none';
    $properties = \Drupal::request()->get('properties');
    if(isset($properties['content_type']) && !empty($properties['content_type'])){
      $bundle = $properties['content_type'];
    } elseif(!empty($this->configuration['#content_type'])){
      $bundle = $this->configuration['#content_type'];
    }


    $form['webform_product_table'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Table content settings'),
    ];
    $form['webform_product_table']['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content type'),
      '#options' => $available_bundle,
      '#required' => TRUE,
      '#ajax'          => [
        'callback'      => [$this, 'columnCallback'],
        'wrapper'       => 'category-wrapper',
      ],
    ];
    $form['webform_product_table']['limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Limit'),
      '#required' => TRUE,
    ];
    $form['webform_product_table']['currency_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Currency Text'),
      '#required' => TRUE,
    ];
    $form['category'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'category-wrapper'],
    ];
    $form['webform_product_table']['category']['categories'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Categories'),
      '#required' => TRUE,
      '#autocomplete_route_name' => 'wpt.autocomplete',
      '#autocomplete_route_parameters' => array('bundle' => $bundle, 'count' => 10),
    ];
    return $form;
  }

  public function columnCallback(array&$form, FormStateInterface $form_state) {
    return $form['category'];
  }

  /**
   * @param $element
   *
   * @return mixed
   */
  protected function getProductByElementProperty($element){
    // get field settings
    $content_type = isset($element['#content_type']) ?$element['#content_type'] : '';
    $categories = isset($element['#categories']) ? $element['#categories'] : '';
    $limit = isset($element['#limit']) ? $element['#limit'] : 20;
    // get product
    $products = \Drupal::service('wpt.config')
      ->getBundleProduct($content_type, $categories, $limit);

    // set products to session
    if(isset($element['#default_value']) && !empty($element['#default_value'])){
      ProductOperations::setProductDefaults($element['#default_value']);
    } else{
      ProductOperations::setProducts($products);
    }

    return $products;
  }

}
