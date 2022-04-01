<?php

namespace Drupal\restme_foods;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Offer food entity entity.
 *
 * @see \Drupal\restme_foods\Entity\OfferService.
 */
class OfferFoodEntityAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\restme_foods\Entity\OfferFoodInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view offer food entity entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit offer food entity entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete offer food entity entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add offer food entity entities');
  }

}
