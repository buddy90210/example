<?php

namespace Drupal\restme_foods;


use Drupal\restme_foods\Entity\OfferFoodInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Class FoodComputedOutput
 * @package Drupal\restme_foods
 */
class OfferFoodOutput extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * Override to compute every time
   */
  protected function ensureComputedValue() {
    $this->computeValue();
  }


  /**
   * Computes the values for an item list.
   */
  protected function computeValue() {
    /** @var OfferFoodInterface $entity */
    $entity = $this->getEntity();

    $food = $entity->getFood();

    $value = 0;

    if ($food) {
      $value = intval($food->get('field_food_output')->value);
    }

    $this->list[0] = $this->createItem(0, $value);
  }
}
