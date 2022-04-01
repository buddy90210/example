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
interface OfferFoodInterface extends ContentEntityInterface {

  /**
   * Returns referenced food entity
   * @return NodeInterface
   */
  public function getFood();

  /**
   * Returns id of referenced food entity
   * @return int
   */
  public function getFoodId();

  /**
   * Sets food reference entity
   * @param \Drupal\node\NodeInterface $food
   * @return $this
   */
  public function setFood(NodeInterface $food);

  /**
   * Sets id of food reference entity
   * @param $food_id
   * @return $this
   */
  public function setFoodId($food_id);

  /**
   * Returns referenced format
   * @return TermInterface
   */
  public function getFormat();

  /**
   * Returns id of referenced format
   * @return mixed
   */
  public function getFormatId();

  /**
   * Returns delta of referenced format
   * @return int
   */
  public function getFormatDelta();

  /**
   * Sets referenced format
   * @param \Drupal\taxonomy\TermInterface $format
   * @return $this
   */
  public function setFormat(TermInterface $format, $delta);

  /**
   * Sets referenced format id
   * @param $format_id
   * @return $this
   */
  public function setFormatId($format_id, $delta);


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


  /**
   * Returns output per item
   * @return int
   */
  public function getOutput();


  /**
   * Returns total output
   * @return int
   */
  public function getOutputTotal();


  /**
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
