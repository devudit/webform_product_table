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
 * Time:        5:53 PM
 */

namespace Drupal\webform_product_table\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webform_product_table\WebformProductTable;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Tags;
use Drupal\Component\Utility\Unicode;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;

class AutocompleteController extends ControllerBase{

  protected $wptConfig;
  protected $entityTypeManager;
  protected $selectionManager;


  public function __construct(SelectionPluginManagerInterface $selectionManager,WebformProductTable $wptConfig,EntityTypeManagerInterface $entityTypeManager) {
    $this->selectionManager = $selectionManager;
    $this->wptConfig = $wptConfig;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.entity_reference_selection'),
      $container->get('wpt.config'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Handler for autocomplete request.
   */
  public function handleAutocomplete(Request $request, $bundle, $count) {
    $matches = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {

      $options = [
        'target_type'      => 'taxonomy_term',
        'handler'          => 'default:taxonomy_term',
        'handler_settings' => [],
      ];


      // Get vocab id
      $vocab_id = '';
      if($bundle != 'none'){
        $fields = $this->wptConfig->getBundleSettings('node_type',$bundle);
        if(isset($fields['item_category'])){
          $storage = $this->entityTypeManager->getStorage('field_config')->loadByProperties(['field_name' => $fields['item_category']]);
          if(!empty($storage)){
            $field_storage = $storage['node.'.$bundle.'.'.$fields['item_category']];
            $options['handler_settings'] = $field_storage->getSetting('handler_settings');
          }
        }
      }

      $typed_string = Tags::explode($input);
      $typed_string = Unicode::strtolower(array_pop($typed_string));

      $handler = $this->selectionManager->getInstance($options);
      $entity_labels = $handler->getReferenceableEntities($typed_string, 'CONTAINS', 10);

      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {

          $entity = $this->entityTypeManager->getStorage('taxonomy_term')->load($entity_id);
          $entity = \Drupal::entityManager()->getTranslationFromContext($entity);

          $type = !empty($entity->type->entity) ? $entity->type->entity->label() : $entity->bundle();
          $key = $label . ' (' . $entity_id . ')';
          // Strip things like starting/trailing white spaces, line breaks and tags.
          $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
          // Names containing commas or quotes must be wrapped in quotes.
          $key = Tags::encode($key);
          $matches[] = [
            'value' => $entity_id,
            'label' => $key
          ];
        }
      }
    }

    return new JsonResponse($matches);
  }

}