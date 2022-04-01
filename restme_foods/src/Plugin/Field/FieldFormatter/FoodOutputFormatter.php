<?php

namespace Drupal\restme_foods\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\IntegerFormatter;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'food_output_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "food_output_formatter",
 *   label = @Translation("Food output formatter"),
 *   field_types = {
 *     "integer"
 *   }
 * )
 */
class FoodOutputFormatter extends IntegerFormatter {

  protected $food_items = [];

  /**
   * @return array
   */
  protected function getFieldSettings() {
    $settings = parent::getFieldSettings();

    if (!empty($this->food_items)) {
      $food = $this->food_items[0]->getFood();
    }

    if (!empty($food)) {
      $field_food_unit = $food->get('field_food_unit')->value;
      $field_food_unit_settings = $food->get('field_food_unit')->getSettings();
      $food_unit = $field_food_unit_settings['allowed_values'][$field_food_unit];
    }

    if (!empty($food_unit)) {
      $settings['suffix'] = ' ' . $food_unit;
    }

    return $settings;
  }

  /**
   * @param array $entities_items
   */
  public function prepareView(array $entities_items) {
    foreach ($entities_items as $delta => $item) {
      $this->food_items[$delta] = $item->getEntity();
    }
  }


}
