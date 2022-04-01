<?php

namespace Drupal\restme_foods\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'restme_offer_food_format' field type.
 *
 * @FieldType(
 *   id = "restme_offer_food_format",
 *   label = @Translation("Offer food format"),
 *   description = @Translation("Offer food format field type") * )
 *   default_widget = "options_select",
 *   default_formatter = "entity_reference_label",
 */
class OfferFoodFormat extends EntityReferenceItem {
  /**
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_definition
   * @return array
   */
  public static function schema(
    FieldStorageDefinitionInterface $field_definition
  ) {
    $schema = parent::schema($field_definition);

    $schema['columns']['delta'] = [
      'type' => 'int',
      'unsigned' => TRUE,
      'not null' => FALSE,
      'description' => 'Delta of the order format',
    ];

    return $schema;
  }

  /**
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $field_definition
   * @return mixed
   */
  public static function propertyDefinitions(
    FieldStorageDefinitionInterface $field_definition
  ) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['delta'] = DataDefinition::create('integer')
      ->setLabel(t('Order format delta'));

    return $properties;
  }


}
