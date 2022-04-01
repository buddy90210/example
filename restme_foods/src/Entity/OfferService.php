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
 * Defines the Offer service entity entity.
 *
 * @ingroup restme_foods
 *
 * @ContentEntityType(
 *   id = "offer_service_entity",
 *   label = @Translation("Offer service entity"),
 *   handlers = {
 *     "access" = "Drupal\restme_foods\OfferServiceEntityAccessControlHandler",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   fieldable = FALSE,
 *   translatable = FALSE,
 *   persistent_cache = FALSE,
 *   render_cache = FALSE,
 *   base_table = "offer_services",
 *   admin_permission = "administer offer service entity entities",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 * )
 */
class OfferService extends ContentEntityBase implements OfferServiceInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['service_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Service'))
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE)
      ->setRequired(TRUE)
      ->setSetting('target_type', 'node')
      ->setSetting('handler_settings', ['target_bundles' => ['service' => 'service']])
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
      ->setLabel(t('Service count'))
      ->setSetting('unsigned', TRUE)
      ->setDefaultValue(1)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'number_integer',
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
      ])
      ->setSetting('suffix', ' шт.');

    $fields['price'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Output and price data'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\restme_foods\OfferServicePrice')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'number_integer',
        'settings' => ['thousand_separator' => ' '],
      ])
      ->setSetting('suffix', ' ₽');

    $fields['price_total'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Output and price data'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\restme_foods\OfferServicePriceTotal')
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'number_integer',
        'settings' => ['thousand_separator' => ' '],
      ])
      ->setSetting('suffix', ' ₽');

    return $fields;
  }

  /**
   * Returns referenced service entity
   * @return NodeInterface
   */
  public function getService() {
    return $this->get('service_id')->entity;
  }

  /**
   * Returns id of referenced service entity
   * @return int
   */
  public function getServiceId() {
   return $this->get('service_id')->target_id;
  }

  /**
   * Sets service reference entity
   * @param \Drupal\node\NodeInterface $service
   * @return $this
   */
  public function setService(NodeInterface $service) {
    $this->set('service_id', $service->id());
    return $this;
  }

  /**
   * Sets id of service reference entity
   * @param $service_id
   * @return $this
   */
  public function setServiceId($service_id) {
    $this->set('service_id', $service_id);
    return $this;
  }

  /**
   * Returns service items count
   * @return int
   */
  public function getCount() {
    return $this->get('count')->value;
  }

  /**
   * Sets service items count
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
