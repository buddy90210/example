<?php

namespace Drupal\restme_foods;


use Drupal\restme_foods\Entity\OfferServiceInterface;
use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Class OfferServicePrice
 * @package Drupal\restme_foods
 */
class OfferServicePrice extends FieldItemList {
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
      $field_service_price = $service->get('field_service_price')->value;
      $this->list[0] = $this->createItem(0, intval($field_service_price));
    }
  }
}
