<?php

namespace Drupal\restme_foods\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsSelectWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\OptGroup;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Plugin implementation of the 'offer_food_format_widget' widget.
 *
 * @FieldWidget(
 *   id = "restme_offer_food_format_widget",
 *   label = @Translation("Offer food format widget"),
 *   field_types = {
 *     "restme_offer_food_format"
 *   }
 * )
 */
class OfferFoodFormatWidget extends OptionsSelectWidget {

  /**
   * Static cache for order formats.
   *
   * @var array
   */
  protected $orderFormats = NULL;

  /**
   * Формат предложения.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected $mainFormat = NULL;

  /**
   * Static cache for options.
   *
   * @var array
   */
  protected $options = NULL;

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(
    array $values,
    array $form,
    FormStateInterface $form_state
  ) {
    $values = parent::massageFormValues($values, $form, $form_state);

    foreach ($values as $key => $value) {
      $original_delta = $value['_original_delta'];

      if (empty($value[0])) {
        continue;
      }

      $target_id = $value[0]['target_id'];
      $delta = 0;

      if (preg_match('/^(\d+)_(\d+)$/', $value[0]['target_id'], $res)) {
        $target_id = $res[1];
        $delta = $res[2];
      }

      $values[$key] = [
        'target_id' => $target_id,
        'delta' => $delta,
        '_original_delta' => $original_delta,
      ];
    }

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(
    FieldItemListInterface $items,
    $delta,
    array $element,
    array &$form,
    FormStateInterface $form_state
  ) {

    /** @var \Drupal\Node\NodeInterface $offer */
    $offer = $form_state->getFormObject()->getEntity();

    if ($offer && $offer->hasField('field_offer_for_order')) {
      $order = $offer->get('field_offer_for_order')->entity;
    }

    if (!empty($order)) {
      $this->orderFormats = $this->getOrderFormats($order);
      $this->mainFormat = $order->get('field_format')->entity;
    }
    else {
      $this->mainFormat = $offer->get('field_offer_format')->entity;
    }

    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getOptions(FieldableEntityInterface $entity) {
    if (isset($this->options)) {
      return $this->options;
    }

    $options = [];

    if (!empty($this->mainFormat) && !$this->mainFormat->get('field_subgroups')->isEmpty()) {
      foreach ($this->mainFormat->get('field_subgroups') as $delta => $item) {
        $options[$this->mainFormat->id() . '_' . $delta] = $item->value;
      }
    }
    elseif (!empty($this->orderFormats)) {
      $format_delta = [];
      $format_terms = [];

      foreach ($this->orderFormats as $tid) {
        if (!isset($format_terms[$tid])) {
          $format_terms[$tid] = Term::load($tid);
        }

        if (!isset($format_delta[$tid])) {
          $format_delta[$tid] = 0;
        }

        $options[$tid . '_' . $format_delta[$tid]] = ($format_delta[$tid] + 1) . '. ' . $format_terms[$tid]->label();
        $format_delta[$tid]++;
      }

      if (isset($options[32])) {
        $options[32] = 'Доставка';
      }
    }

    $this->options = $options;
    return $this->options;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSelectedOptions(FieldItemListInterface $items) {
    // We need to check against a flat list of options.
    $flat_options = OptGroup::flattenOptions($this->getOptions($items->getEntity()));

    $selected_options = [];
    foreach ($items as $item) {
      $value = $item->target_id;
      $delta = $item->delta;

      $value .= '_' . $delta;

      if (isset($flat_options[$value])) {
        $selected_options[] = $value;
      }
    }

    return $selected_options;
  }

  /**
   * Возращает список форматов заявки.
   *
   * @param \Drupal\node\NodeInterface $order
   *   Заявка.
   *
   * @return array
   *   Cписок форматов.
   */
  protected function getOrderFormats(NodeInterface $order) {
    if (isset($this->orderFormats)) {
      return $this->orderFormats;
    }

    $formats = $order->get('field_format')->getValue();

    $formats = array_map(function ($item) {
      return $item['target_id'];
    }, $formats);

    return $formats;
  }

}
