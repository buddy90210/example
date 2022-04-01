<?php

namespace Drupal\restme_foods\Plugin\Field\FieldWidget;


use Drupal\restme\Restme;
use Drupal\restme_foods\Entity\OfferFood;
use Drupal\restme_foods\Entity\OfferFoodInterface;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Plugin implementation of the 'restme_inline_entity_form_offer_table' widget.
 *
 * @FieldWidget(
 *   id = "restme_offer_food_table",
 *   label = @Translation("Offer food table"),
 *   field_types = {
 *     "entity_reference"
 *   },
 *   multiple_values = true
 * )
 */
class OfferFoodTable extends InlineEntityFormOfferTable {
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

    $offer_form_state = $form_state->get('offer_form');

    $this->setFoodTypesMatchData($form_state);

    $element['#element_validate'][] = [get_class($this), 'validateOfferFoods'];

    /** @var NodeInterface $offer */
    $offer = $items->getEntity();

    $order = $offer->get('field_offer_for_order')->entity;

    if ($order) {
      $main_format = $order->get('field_format')->entity;
    }
    else {
      $main_format = $offer->get('field_offer_format')->entity;
    }

    $offer_format_has_subgroups = FALSE;

    if ($main_format) {
      $offer_format_has_subgroups = !$main_format->get('field_subgroups')->isEmpty();
    }

    $order_formats_count = $order ? $order->get('field_format')->count() : 0;

    if ($order_formats_count <= 1 && !$offer_format_has_subgroups) {
      unset($element['entities']['#table_fields']['format']);
    }

    if (!empty($element['actions']['ief_add'])) {
      $element['actions']['ief_add']['#value'] = 'Добавить блюдо';
    }

    // Add custom link to open node_add_form of food bundle
    if (empty($offer_form_state['client_edit_mode'])) {
      $element['actions']['add_food'] = [
        '#type' => 'link',
        '#title' => 'Новое блюдо',
        '#url' => \Drupal\Core\Url::fromRoute('node.add',
          ['node_type' => 'food']),
        '#attributes' => ['class' => 'add-food', 'target' => '_blank'],
      ];
    }

    return $element;
  }

  /**
   * @param $element
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $form
   */
  public static function validateOfferFoods(
    $element,
    FormStateInterface $form_state,
    $form
  ) {
    $triggering_element = $form_state->getTriggeringElement();

    if (empty($triggering_element['#ief_submit_trigger_all']) || in_array($triggering_element['#name'],
        ['food', 'service'])
    ) {
      return;
    }

    $ief_id = $element['#ief_id'];

    /** @var NodeInterface $offer */
    $offer = $form_state->getFormObject()->getEntity();

    /** @var OfferFoodInterface[] $foods */
    $foods = $form_state->get(['inline_entity_form', $ief_id, 'entities']);

    $offer_form_state = $form_state->get('offer_form');

    $required_formats = $offer_form_state['required_formats'];
    $order_formats_delta = $offer_form_state['order_formats_delta'];
    $required_food_types = $offer_form_state['required_food_types'];

    if ($offer->get('field_offer_sale_access')->value) {
      $offer_format = $form_state->getValue('field_offer_format');

      if ($offer_format && $offer_format != '_none') {
        $required_formats = [$offer_format];
      }
    }

    // Проверка на создание предложения без блюд
    if ($required_food_types) {
      $empty_foods = TRUE;

      foreach ($foods as $key => $food) {
        if (!empty($food['entity']->getFoodId())) {
          $empty_foods = FALSE;
          break;
        }
      }

      if ($empty_foods) {
        $form_state->setError($element,
          'Вы не можете создать предложение без блюд.');
      }
    }

    $order_formats_name = [];
    foreach ($required_formats as $tid) {
      $format = Term::load($tid);
      if (!$format) {
        continue;
      }
      $order_formats_name[] = $format->label();
    }

    $foods_format_delta = [];

    $full_check = \Drupal::currentUser()->hasPermission('create offer content');

    foreach ($foods as $key => $offer_food) {
      $entity_form =& $element['entities'][$key]['form']['inline_entity_form'];

      $offer_food_entity = $entity_form['#entity'];

      if (empty($offer_food_entity)) {
        continue;
      }

      $food = $offer_food_entity->getFood();

      if (empty($food)) {
        continue;
      }

      $current_user = \Drupal::currentUser();
      if ($current_user->hasPermission('edit any order content') || $current_user->id() == $offer->getOwnerId()) {
        $food_link = Link::createFromRoute($food->getTitle(),
          'entity.node.edit_form', ['node' => $food->id()], [
            'attributes' => [
              'target' => '_blank',
            ],
          ])->toString();
      }
      else {
        $food_link = $food->getTitle();
      }

      if ($full_check && $offer->get('field_offer_status')->value == RestMe::OFFER_DRAFT && !$food->isPublished()) {
        $form_state->setError($entity_form['food_id']['widget'][0]['target_id'],
          new FormattableMarkup('Блюдо «%food» отключено. Замените на актуальное.',
            [
              '%food' => $food_link
            ]));
      }

      $count = $offer_food_entity->getCount();

      if ($count > 0) {
        $minimum_order = $food->get('field_minimum_order')->value ?: 1;

        if ($count < $minimum_order) {
          $form_state->setError($entity_form['count']['widget'][0]['value'],
            new FormattableMarkup('Минимальное количество для заказа «%food» - %count.',
              [
                '%food' => $food_link,
                '%count' => $minimum_order,
              ]));
        }

        // Проверка формата
        if ($full_check && !empty($required_formats) && !RestMe::checkAccessFoods($food->id(),
            $required_formats)
        ) {
          $form_state->setError($entity_form['food_id']['widget'][0]['target_id'],
            new FormattableMarkup('Блюдо «%food» не подходит под формат заявки (%formats).',
              [
                '%food' => $food_link,
                '%formats' => implode(', ', $order_formats_name),
              ]));
        }
      }

      if ($offer_food_entity->getFormatId() && count($order_formats_delta) > 1) {
        $foods_format_delta[] = [
          'format' => $offer_food_entity->getFormatId(),
          'delta' => $offer_food_entity->getFormatDelta(),
        ];
      }

      // Проверка обязательное фото
      $partners_profile = restme_partners_get_partner_profile($offer->getOwnerId());
      $food_without_photo = !empty($partners_profile) ? $partners_profile->get('field_food_without_photo')->value : 0;

      if ($full_check && !$food_without_photo && empty($food->get('field_food_image')->target_id)) {
        $food_type = Term::load($food->get('field_food_type')->target_id);
        if (!empty($food_type->get('field_required_photo')->value)) {
          $form_state->setError($entity_form['food_id']['widget'][0]['target_id'],
            t('Блюдо «%food» нельзя добавить без фото.', [
              '%food' => $food_link,
            ]));
        }
      }
    }

    if ($triggering_element['#name'] == 'op') {
      // Проверка добавления блюд по всем форматам
      if (count($order_formats_delta) > 1) {
        foreach ($order_formats_delta as $order_format_item) {
          $check = FALSE;
          foreach ($foods_format_delta as $food_format_item) {
            if ($order_format_item['format'] == $food_format_item['format'] and $order_format_item['delta'] == $food_format_item['delta']) {
              $check = TRUE;
              continue;
            }
          }
          if (!$check) {
            $form_state->setError($element,
              t('Отсутствуют блюда для формата %delta. "%format ".', [
                '%format' => Term::load($order_format_item['format'])->label(),
                '%delta' => $order_format_item['delta'] + 1,
              ]));
          }
        }
      }

      if (!empty($required_food_types)) {
        $offer_food_types = self::getFoodTypes($form_state, $ief_id);

        $missing = array_diff($required_food_types, $offer_food_types);
        $extra = array_diff($offer_food_types, $required_food_types);

        $types_text = '';

        if (!empty($missing)) {
          $missing = array_map(function ($tid) {
            $term = Term::load($tid);
            return $term->label();
          }, $missing);
          $types_text = 'Не включены: ' . implode(', ', $missing);
        }

        if (!empty($extra)) {
          $extra = array_map(function ($tid) {
            $term = Term::load($tid);
            return $term->label();
          }, $extra);
          $types_text .= (!$types_text ? 'Дополнительно: ' : ', дополнительно: ') . implode(', ',
              $extra);
        }

        if (!empty($types_text)) {
          \Drupal::logger('cm_offer_check')->info('%types', [
            '%types' => $types_text,
            'link' => $offer->toLink()->toString(),
          ]);
        }
      }
    }
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   */
  public static function createEntity() {
    return OfferFood::create();
  }

  /**
   * Сохраняем информацию о соответствии предложения типам блюд заявки
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function setFoodTypesMatchData(FormStateInterface $form_state) {
    $required_food_types = $form_state->get([
      'offer_form',
      'required_food_types'
    ]);

    if (!$required_food_types) {
      return;
    }

    $food_types = self::getFoodTypes($form_state, $this->iefId);

    $missing = array_diff($required_food_types, $food_types);
    $extra = array_diff($food_types, $required_food_types);

    $food_types_match_data = [];

    if ($missing || $extra) {
      $food_types_match_data = [
        'missing' => $missing,
        'extra' => $extra,
      ];
    }

    $form_state->set([
      'inline_entity_form',
      $this->iefId,
      'food_types_match_data'
    ], $food_types_match_data);
  }

  /**
   * Возвращает типы блюд предложения
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return array
   */
  protected static function getFoodTypes(
    FormStateInterface $form_state,
    $ief_id
  ) {
    $offer_foods = $form_state->get([
      'inline_entity_form',
      $ief_id,
      'entities'
    ]);

    $food_types = [];

    foreach ($offer_foods as $offer_food) {
      if (empty($offer_food['entity'])) {
        continue;
      }

      $food = $offer_food['entity']->getFood();

      if (empty($food)) {
        continue;
      }

      $food_type = $food->get('field_food_type')->target_id;

      if ($food_type && !in_array($food_type, $food_types)) {
        $food_types[] = $food_type;
      }
    }

    return $food_types;
  }
}
