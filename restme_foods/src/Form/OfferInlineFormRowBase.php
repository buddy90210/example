<?php

namespace Drupal\restme_foods\Form;


use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\ElementSubmit;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Class OfferInlineFormRowBase
 * @package Drupal\restme_foods\Form
 */
class OfferInlineFormRowBase extends EntityInlineForm {
  /**
   * @param array $entity_form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function entityFormValidate(
    array &$entity_form,
    FormStateInterface $form_state
  ) {
    parent::entityFormValidate($entity_form, $form_state);

    // Нам необходимо выполнить submit обработчики для новых элементов, чтобы все
    // изменения полей сохранялись между ajax запросами
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#ief_submit_trigger'])) {
      ElementSubmit::doSubmit($entity_form, $form_state);
    }
  }
}
