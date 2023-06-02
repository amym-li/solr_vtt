<?php

namespace Drupal\solr_vtt\Plugin\search_api\processor;

use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Processor\ProcessorProperty;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Item\ItemInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\search_api\Plugin\PluginFormTrait;

/**
 * Adds additional field to a Nested Object in Solr document
 *
 * These are added automatically in the preIndex step
 * @see VttExtractor::preIndexSave()
 * Values are populated via @see VttExtractor::addFieldValues()
 *
 *
 * @SearchApiProcessor(
 *   id = "vtt_extractor",
 *   label = @Translation("WebVTT Extractor"),
 *   description = @Translation("Adds a nested object to document in index"),
 *   stages = {
 *     "add_properties" = 0
 *   },
 *   locked = false,
 *   hidden = false,
 * )
 */
class VttExtractor extends ProcessorPluginBase {

  use PluginFormTrait;

  const NODE_DATASOURCE = 'entity:node';
  const P_MY_FIELD_NAME = 'vtt';

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(DatasourceInterface $datasource = NULL) {
    $properties = [];

    // fields are only available on nodes
    if ($datasource && $datasource->getPluginId() == 'entity:node') {
      $definition = [
        'label' => $this->t('Extracted WebVTT'),
        'description' => $this->t('Extracted WebVTT'),
        'type' => 'solr_document', // set to a Drupal TypedData plugin
        'processor_id' => $this->getPluginId(),
        'hidden' => FALSE,
      ];
      $properties[self::P_MY_FIELD_NAME] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * Implement addFieldValues to map actual values to the definitions from getPropertyDefinitions
   * @param \Drupal\search_api\Item\ItemInterface $item
   */
  public function addFieldValues(ItemInterface $item) {
    // retrieve all necessary fields
    $fieldsHelper = $this->getFieldsHelper();
    $myFieldHolder = current($fieldsHelper
      ->filterForPropertyPath($item->getFields(), self::NODE_DATASOURCE, self::P_MY_FIELD_NAME));

    $test = [];
    $test[] = [
      'id' => mt_rand(1, 10),
      'ss_start' => mt_rand(10, 30) . ':' . mt_rand(10, 50),
      'ts_vtt_text' => 'My line of dialogue',
    ];
    $test[] = [
      'id' => mt_rand(1, 10),
      'ss_start' => mt_rand(30, 50) . ':' . mt_rand(10, 50),
      'ts_vtt_text' => 'Another speaking line',
    ];
    $myFieldHolder->addValue($test);
  }

  /**
   * {@inheritdoc}
   */
  public function preIndexSave() {
    foreach ($this->index->getDatasources() as $datasource_id => $datasource) {
      $entity_type = $datasource->getEntityTypeId();
      if ($entity_type == 'node') {
        $this->ensureField($datasource_id, self::P_MY_FIELD_NAME);
      }
    }
  }
}
