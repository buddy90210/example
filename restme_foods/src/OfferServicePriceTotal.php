<?php

namespace Drupal\restme_foods;


use Drupal\restme_foods\Entity\OfferServiceInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Class OfferServicePriceTotal
 * @package Drupal\restme_foods
 */
class OfferServicePriceTotal extends FieldItemList {

  use ComputedItemListTrait;

  /**
   *
   */
  protected function ensureComputedValue() {
    $this->computeValue();
  }


  /**
   * Computes the values for an item list.
   */
  protected function computeValue() {
    /** @var OfferServiceInterface $entity */
    $entity = $this->getEntity();

    $service = $entity->getService();

    if ($service) {
      $food_count = $entity->getCount();
      $field_service_price = $service->get('field_service_price')->value;

      $this->list[0] = $this->createItem(0, $field_service_price * $food_count);
    }
  }
}
