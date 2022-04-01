<?php

namespace Drupal\restme_foods\Plugin\Field\FieldWidget;


use Drupal\restme_foods\Entity\OfferService;
use Drupal\restme_foods\Entity\OfferServiceInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'restme_inline_entity_form_offer_table' widget.
 *
 * @FieldWidget(
 *   id = "restme_offer_service_table",
 *   label = @Translation("Offer service table"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class OfferServiceTable extends InlineEntityFormOfferTable {
  /**
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param int $delta
   * @param array $element
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {
    $element = parent::formElement($items, $delta, $element, $form,
      $form_state);

    if (!empty($element['actions']['ief_add'])) {
      $element['actions']['ief_add']['#value'] = 'Добавить услугу';
    }

    $entities = $form_state->get([
      'inline_entity_form',
      $this->getIefId(),
      'entities'
    ]);

    foreach ($entities as $key => $value) {
      /** @var OfferServiceInterface $offer_service */
      $offer_service = $entities[$key]['entity'];

      $service_entity = $offer_service->getService();

      $client_edit_mode = $form_state->get(['offer_form', 'client_edit_mode']);

      $row = &$element['entities'][$key];

      if (!empty($service_entity) && $client_edit_mode) {
        $row['actions']['ief_entity_remove']['#access'] = !$service_entity->get('field_editing_disabled')->value;
      }
    }

    return $element;
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public static function createEntity() {
    return OfferService::create();
  }
}
