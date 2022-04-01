<?php

namespace Drupal\restme_foods\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Offer food entity entities.
 *
 * @ingroup restme_foods
 */
interface OfferServiceInterface extends ContentEntityInterface {

  /**
   * Returns referenced food entity
   * @return NodeInterface
   */
  public function getService();

  /**
   * Returns id of referenced food entity
   * @return int
   */
  public function getServiceId();

  /**
   * Sets food reference entity
   * @param \Drupal\node\NodeInterface $food
   * @return $this
   */
  public function setService(NodeInterface $food);

  /**
   * Sets id of food reference entity
   * @param $food_id
   * @return $this
   */
  public function setServiceId($food_id);

  /**
   * Returns food items count
   * @return int
   */
  public function getCount();

  /**
   * Sets food items count
   * @param $count
   * @return $this
   */
  public function setCount($count);


  /**s
   * Returns price per item
   * @return int
   */
  public function getPrice();


  /**
   * Returns total price
   * @return int
   */
  public function getPriceTotal();
}
