<?php

namespace Drupal\solr_vtt\Plugin\search_api\data_type;

use Drupal\search_api\DataType\DataTypePluginBase;

/**
 * Provides a custom full text data type.
 *
 * @SearchApiDataType(
 *   id = "solr_nested_document",
 *   label = @Translation("Nested Document"),
 *   description = @Translation("Solr Nested Document Field."),
 *   prefix = "n",
 * )
 */
class NestedDataType extends DataTypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFallbackType() {
    // By returning NULL, we prevent that this data type is handled as a string
    // and e.g. text processors won't run on this value since string is the
    // default fallback type.
    return NULL;
  }
}
