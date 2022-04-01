<?php

namespace Drupal\restme_foods\Plugin\Field\FieldWidget;

use Drupal\restme\Controller\FormOffer;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Ajax\SettingsCommand;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;


/**
 * Class InlineEntityFormOfferTable
 * @package Drupal\restme_foods\Plugin\Field\FieldWidget
 */
abstract class InlineEntityFormOfferTable extends InlineEntityFormComplex {
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

    // Не показываем цены при редактировании заказчиком
    $element['entities']['#table_fields'] = array_filter($element['entities']['#table_fields'],
      function ($field_name) use ($offer_form_state) {
        if ($field_name == 'price' || $field_name == 'price_total') {
          return !$offer_form_state['client_edit_mode'] || $offer_form_state['show_prices'];
        }

        return TRUE;
      }, ARRAY_FILTER_USE_KEY);

    $element['entities']['#theme'] = 'form_offer_table';

    $entities = $form_state->get([
      'inline_entity_form',
      $this->getIefId(),
      'entities'
    ]);

    $entities_count = count($entities);
    $weight_delta = max(ceil($entities_count * 1.2), 50);

    // Determine the wrapper ID for the entire element.
    $wrapper = 'inline-entity-form-' . $this->getIefId();

    // Force to enable tabledrag functionality. In default implementation
    // tabletrag is disabled when 'form' key exists
    foreach ($entities as $key => $value) {
      $row = &$element['entities'][$key];
      $row['title'] = [];
      $row['delta'] = [
        '#type' => 'weight',
        '#delta' => $weight_delta,
        '#default_value' => $value['weight'],
        '#attributes' => ['class' => ['ief-entity-delta']],
      ];

      // Add an actions container with edit and delete buttons for the entity.
      $row['actions'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['ief-entity-operations']],
      ];

      $row['actions']['ief_entity_remove'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove'),
        '#name' => 'ief-' . $this->getIefId() . '-entity-remove-' . $key,
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => [get_class($this), 'rebuildTable'],
          'wrapper' => $wrapper,
        ],
        '#submit' => [[get_class($this), 'submitConfirmRemove']],
        '#ief_submit_trigger' => TRUE,
        '#ief_row_delta' => $key,
      ];
    }

    if ($offer_form_state['client_edit_mode']) {
      unset($element['actions']);
    }
    else {
      $element['actions']['ief_add']['#limit_validation_errors'] = [];
      $element['actions']['ief_add']['#ief_submit_trigger'] = TRUE;
      $element['actions']['ief_add']['#ajax'] = [
        'callback' => [get_class($this), 'rebuildTable'],
        'wrapper' => $wrapper,
      ];
      $element['actions']['ief_add']['#submit'] = [
        [get_class($this), 'addEmptyRow'],
      ];
    }


    return $element;
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public static function rebuildTable($form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    $element = inline_entity_form_get_element($form, $form_state);

    $wrapper = '#inline-entity-form-' . $element['#ief_id'];

    $response->addCommand(new ReplaceCommand($wrapper, $element));
    $response->addCommand(new SettingsCommand([
      'offerEditForm' => $form['#attached']['drupalSettings']['offerEditForm']
    ]));

    return $response;
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function addEmptyRow($form, FormStateInterface $form_state) {
    $entity_form = inline_entity_form_get_element($form, $form_state);

    $ief_id = $entity_form['#ief_id'];

    $entity = static::createEntity();

    $weight = 0;
    $entities = $form_state->get(['inline_entity_form', $ief_id, 'entities']);
    if (!empty($entities)) {
      $weight = max(array_keys($entities)) + 1;
    }
    // Add the entity to form state, mark it for saving, and close the form.
    $entities[] = [
      'entity' => $entity,
      'weight' => $weight,
      'form' => 'edit',
      'needs_save' => TRUE,
    ];

    $form_state->set(['inline_entity_form', $ief_id, 'entities'], $entities);
    $form_state->setRebuild();
  }

  /**
   * @param $entity_form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function submitSaveEntity(
    $entity_form,
    FormStateInterface $form_state
  ) {
    parent::submitSaveEntity($entity_form, $form_state);

    // Force to keep edit form opened after submission
    $ief_id = $entity_form['#ief_id'];

    $entities = $form_state->get(['inline_entity_form', $ief_id, 'entities']);
    $last_key = max(array_keys($entities));

    $entities[$last_key]['form'] = 'edit';
    $form_state->set(['inline_entity_form', $ief_id, 'entities'], $entities);
  }


  /**
   * Нам необходимо показывать форму предложения сразу же в таблице
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   * @param bool $translating
   */
  protected function prepareFormState(
    FormStateInterface $form_state,
    FieldItemListInterface $items,
    $translating = FALSE
  ) {
    $initial_state = $form_state->get(['inline_entity_form', $this->iefId]);

    // Выставляем режим отображения формы по умолчанию
    parent::prepareFormState($form_state, $items, $translating);

    if (empty($initial_state)) {
      $widget_state = $form_state->get(['inline_entity_form', $this->iefId]);

      foreach ($items as $delta => $item) {
        $entity = $item->entity;

        if ($entity) {
          $widget_state['entities'][$delta]['form'] = 'edit';
        }
      }

      $form_state->set(['inline_entity_form', $this->iefId], $widget_state);
    }

    FormOffer::prepareFormState($form_state);
  }

  /**
   * Remove form submit callback.
   *
   * The row is identified by #ief_row_delta stored on the triggering
   * element.
   * This isn't an #element_validate callback to avoid processing the
   * remove form when the main form is submitted.
   *
   * @param $form
   *   The complete parent form.
   * @param $form_state
   *   The form state of the parent form.
   */
  public static function submitConfirmRemove(
    $form,
    FormStateInterface $form_state
  ) {
    $element = inline_entity_form_get_element($form, $form_state);
    $remove_button = $form_state->getTriggeringElement();
    $delta = $remove_button['#ief_row_delta'];

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $element['entities'][$delta]['form']['inline_entity_form']['#entity'];

    $entity_id = $entity->id();

    $form_state->setRebuild();

    $widget_state = $form_state->get([
      'inline_entity_form',
      $element['#ief_id']
    ]);
    // This entity hasn't been saved yet, we can just unlink it.
    if (empty($entity_id)) {
      unset($widget_state['entities'][$delta]);
    }
    else {
      $widget_state['delete'][] = $entity;
      unset($widget_state['entities'][$delta]);
    }
    $form_state->set(['inline_entity_form', $element['#ief_id']],
      $widget_state);
  }

  /**
   * @return \Drupal\Core\Entity\EntityInterface
   * @throws \Exception
   */
  public static function createEntity() {
    throw new \Exception('This method must be overriden!');
  }

}
