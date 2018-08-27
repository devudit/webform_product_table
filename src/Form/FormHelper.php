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
 * Time:        1:22 PM
 */
namespace Drupal\webform_product_table\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webform_product_table\EntityHelper;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\webform_product_table\WebformProductTable;

/**
 * Class FormHelper
 * @package Drupal\webform_product_table\Form
 */
class FormHelper {

  use StringTranslationTrait;

  /**
   * @var \Drupal\webform_product_table\EntityHelper
   */
  protected $entityHelper;

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\webform_product_table\WebformProductTable
   */
  protected $wptConfig;

  /**
   * @var \Drupal\Core\Form\FormState
   */
  protected $formState;

  /**
   * @var string
   */
  protected $entityTypeId;

  /**
   * @var string
   */
  protected $bundle;

  /**
   * @var array
   */
  protected static $allowedFormOperations = [
    'default',
    'edit',
    'add',
    'register',
  ];

  /**
   * FormHelper constructor.
   * @param \Drupal\webform_product_table\EntityHelper $entityHelper
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   */
  public function __construct(
    EntityHelper $entityHelper,
    AccountProxyInterface $current_user,
    WebformProductTable $wptConfig
  ) {
    $this->entityHelper = $entityHelper;
    $this->currentUser = $current_user;
    $this->wptConfig = $wptConfig;
  }

  /**
   * @param string $entity_type_id
   * @return $this
   */
  public function setEntityTypeId($entity_type_id) {
    $this->entityTypeId = $entity_type_id;
    return $this;
  }

  /**
   * @return string
   */
  public function getEntityTypeId() {
    return $this->entityTypeId;
  }

  /**
   * @param string $bundle
   * @return $this
   */
  public function setBundle($bundle) {
    $this->bundle = $bundle;
    return $this;
  }

  /**
   * @return string
   */
  public function getBundle() {
    return $this->bundle;
  }

  /**
   * Check if current user have permission
   * @return bool
   */
  public function hasPermission() {

    // Do not alter the form if user lacks certain permissions.
    if (!$this->currentUser->hasPermission('administer wpt settings')) {
      return FALSE;
    }

    // Do not alter the form if it is irrelevant to entity type id
    elseif (empty($this->getEntityTypeId())) {
      return FALSE;
    }

    // Do not alter the form if it is irrelevant to bundle
    elseif (empty($this->getBundle())) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Check if current content type is supported or not
   * @return bool
   */
  public function isSupportedContentType(){
    return $this->entityHelper->isSupportedEntityType($this->getEntityTypeId());
  }

  /**
   * Set form state to form helper
   * @param \Drupal\Core\Form\FormStateInterface $formState
   */
  public function setFormState(FormStateInterface $formState){
    $this->formState = $formState;
    /* get form entity */
    $entity = $this->getFormEntity();
    /* Set bundle original id */
    method_exists($entity,'getOriginalId') ? $this->setBundle($entity->getOriginalId()) : $this->setBundle(null);
    /* Set bundle and set entity type id */
    method_exists($entity,'getEntityTypeId') ? $this->setEntityTypeId($entity->getEntityTypeId()) : $this->setEntityTypeId(null);
  }

  /**
   * Gets the object entity of the form if available.
   *
   * @return \Drupal\Core\Entity\Entity|false
   *   Entity or FALSE if non-existent or if form operation is
   *   'delete'.
   */
  protected function getFormEntity() {
    $form_object = $this->formState->getFormObject();
    if (NULL !== $form_object
      && method_exists($form_object, 'getOperation')
      && method_exists($form_object, 'getEntity')
      && in_array($form_object->getOperation(), self::$allowedFormOperations)) {
      return $form_object->getEntity();
    }
    return FALSE;
  }

  public function displayEntitySettings(&$form_fragment) {

    $settings = $this->wptConfig->getBundleSettings($this->entityTypeId,$this->bundle);

    $form_fragment['enable_wpt'] = [
      '#type' => 'radios',
      '#default_value' => $settings['enable'] ? 1 : 0,
      '#options' => [
        0 => 'Disable webform product table on this',
        1 => 'Enable webform product table on this',
      ],
    ];

    $fields_text = $this->entityHelper->getEntityTextFields($this->getBundle());
    $fields_numeric = $this->entityHelper->getEntityNumericFields($this->getBundle());
    $fields_term = $this->entityHelper->getEntityTermFields($this->getBundle());

    $form_fragment['item_category'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Item Category'),
      '#description' => $this->t("Select field for item category"),
      '#options' => $fields_term,
      '#default_value' => $settings['item_category']
    ];

    $form_fragment['item_title'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Item Title'),
      '#description' => $this->t("Select field for item title"),
      '#options' => $fields_text,
      '#default_value' => $settings['item_title']
    ];

    $form_fragment['item_number'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Item Number'),
      '#description' => $this->t("Select field for item number"),
      '#options' => $fields_numeric,
      '#default_value' => $settings['item_number']
    ];

    $form_fragment['item_description'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Item Description'),
      '#description' => $this->t("Select field for item description"),
      '#options' => $fields_text,
      '#default_value' => $settings['item_description']
    ];

    $form_fragment['item_price'] = [
      '#type' => 'select',
      '#required' => TRUE,
      '#title' => $this->t('Item Price'),
      '#description' => $this->t("Select field for item price"),
      '#options' => $fields_numeric,
      '#default_value' => $settings['item_price']
    ];

  }


}