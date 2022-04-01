<?php

namespace Drupal\restme_foods\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Offer food entity entity.
 *
 * @ingroup restme_foods
 *
 * @ContentEntityType(
 *   id = "offer_food_entity",
 *   label = @Translation("Offer food entity"),
 *   handlers = {
 *     "access" = "Drupal\restme_foods\OfferFoodEntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   fieldable = FALSE,
 *   translatable = FALSE,
 *   persistent_cache = FALSE,
 *   render_cache = FALSE,
 *   base_table = "offer_foods",
 *   admin_permission = "administer offer food entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 * )
 */
class OfferFood extends ContentEntityBase implements OfferFoodInterface {
  /**
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
  }


  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['food_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Food'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler_settings', ['target_bundles' => ['food' => 'food']])
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
      ])
      ->setDisplayOptions('form', [
        'type' => 'food_service_autocomplete',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Food count'))
      ->setSetting('unsigned', TRUE)
      ->setRequired(TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'number_integer',
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
      ])
      ->setSetting('suffix', ' шт.');

    $fields['format'] = BaseFieldDefinition::create('restme_offer_food_format')
      ->setLabel(t('Format'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler_settings', ['target_bundles' => ['format' => 'format']])
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'entity_reference_label',
      ])
      ->setDisplayOptions('form', [
        'type' => 'restme_offer_food_format_widget',
      ])
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['output'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Output and price data'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\restme_foods\OfferFoodOutput')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'food_output_formatter',
        'settings' => ['thousand_separator' => ' '],
      ]);

    $fields['output_total'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Output and price data'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\restme_foods\OfferFoodOutputTotal')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'food_output_formatter',
        'settings' => ['thousand_separator' => ' '],
      ]);

    $fields['price'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Output and price data'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\restme_foods\OfferFoodPrice')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'number_integer',
        'settings' => ['thousand_separator' => ' '],
      ])
      ->setSetting('suffix', ' ₽');

    $fields['price_total'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Output and price data'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\restme_foods\OfferFoodPriceTotal')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'number_integer',
        'settings' => ['thousand_separator' => ' '],
      ])
      ->setSetting('suffix', ' ₽');

    return $fields;
  }

  /**
   * Returns referenced food entity
   * @return NodeInterface
   */
  public function getFood() {
    return $this->get('food_id')->entity;
  }

  /**
   * Returns id of referenced food entity
   * @return int
   */
  public function getFoodId() {
   return $this->get('food_id')->target_id;
  }

  /**
   * Sets food reference entity
   * @param \Drupal\node\NodeInterface $food
   * @return $this
   */
  public function setFood(NodeInterface $food) {
    $this->set('food_id', $food->id());
    return $this;
  }

  /**
   * Sets id of food reference entity
   * @param $food_id
   * @return $this
   */
  public function setFoodId($food_id) {
    $this->set('food_id', $food_id);
    return $this;
  }

  /**
   * Returns referenced format
   * @return TermInterface
   */
  public function getFormat() {
    return $this->get('format')->entity;
  }

  /**
   * Returns id of referenced format
   * @return mixed
   */
  public function getFormatId() {
    return $this->get('format')->target_id;
  }

  /**
   * Returns delta of referenced format
   * @return int
   */
  public function getFormatDelta() {
    return $this->get('format')->delta;
  }

  /**
   * Sets referenced format
   * @param \Drupal\taxonomy\TermInterface $format
   * @return $this
   */
  public function setFormat(TermInterface $format, $delta) {
    $this->set('format', ['target_id' => $format->id(), 'delta' => $delta]);
    return $this;
  }

  /**
   * Sets referenced format id
   * @param $format_id
   * @return $this
   */
  public function setFormatId($format_id, $delta) {
    $this->set('format', ['target_id' => $format_id, 'delta' => $delta]);
    return $this;
  }

  /**
   * Returns food items count
   * @return int
   */
  public function getCount() {
    return $this->get('count')->value;
  }

  /**
   * Sets food items count
   * @param $count
   * @return $this
   */
  public function setCount($count) {
    $this->set('count', $count);
    return $this;
  }

  /**
   * @return int
   */
  public function getOutput() {
    return $this->get('output')->value;
  }

  /**
   * @return int
   */
  public function getOutputTotal() {
    return $this->get('output_total')->value;
  }

  /**
   * @return int
   */
  public function getPrice() {
    return $this->get('price')->value;
  }

  /**
   * @return int
   */
  public function getPriceTotal() {
    return $this->get('price_total')->value;
  }
}
