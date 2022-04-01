<?php

namespace Drupal\restme_foods\Form;


use Drupal\restme\Controller\FormOffer;
use Drupal\restme_foods\Entity\OfferFoodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\inline_entity_form\ElementSubmit;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Class OfferFoodsInlineForm
 * @package Drupal\restme_foods\Form
 */
class OfferFoodInlineForm extends OfferInlineFormRowBase {
  /**
   * @param \string[] $bundles
   * @return array
   */
  public function getTableFields($bundles) {
    $fields = [];

    $fields['food_id'] = [
      'type' => 'callback',
      'label' => 'Блюдо',
      'callback' => [get_class($this), 'getFoodId'],
      'weight' => 1,
    ];

    $fields['type'] = [
      'type' => 'callback',
      'label' => '',
      'callback' => [get_class($this), 'getFoodTypeLabel'],
      'weight' => 2,
    ];

    $fields['format'] = [
      'type' => 'callback',
      'label' => 'Формат',
      'callback' => [get_class($this), 'getFormatDropdown'],
      'weight' => 3,
    ];

    $fields['count'] = [
      'type' => 'callback',
      'label' => 'Количество',
      'callback' => [get_class($this), 'getCount'],
      'weight' => 4,
    ];

    $fields['output'] = [
      'type' => 'field',
      'label' => 'Выход',
      'weight' => 4,
      'display_options' => [
        'type' => 'food_output_formatter',
        'settings' => ['thousand_separator' => ' ', 'prefix_suffix' => TRUE],
      ],
    ];

    $fields['output_total'] = [
      'type' => 'field',
      'label' => 'Выход общий',
      'weight' => 5,
      'display_options' => [
        'type' => 'food_output_formatter',
        'settings' => ['thousand_separator' => ' ', 'prefix_suffix' => TRUE],
      ],
    ];

    $fields['price'] = [
      'type' => 'field',
      'label' => 'Стоимость',
      'weight' => 6,
      'display_options' => [
        'type' => 'number_integer',
        'settings' => ['thousand_separator' => ' ', 'prefix_suffix' => TRUE],
      ],
    ];

    $fields['price_total'] = [
      'type' => 'field',
      'label' => 'Итого',
      'weight' => 7,
      'display_options' => [
        'type' => 'number_integer',
        'settings' => ['thousand_separator' => ' ', 'prefix_suffix' => TRUE],
      ],
    ];

    return $fields;
  }

  /**
   * @param \Drupal\restme_foods\Entity\OfferFoodInterface $entity
   * @param $variables
   * @param $form
   * @return string
   */
  public static function getFoodId(
    OfferFoodInterface $entity,
    $variables,
    $form
  ) {
    $renderer = \Drupal::service('renderer');

    if (!empty($form['form']['inline_entity_form']['food_id'])) {
      $food_element = $form['form']['inline_entity_form']['food_id'];
      return $renderer->render($food_element);
    }

    return '';
  }

  /**
   * @param \Drupal\restme_foods\Entity\OfferFoodInterface $entity
   * @param $variables
   * @param $form
   * @return string
   */
  public static function getCount(
    OfferFoodInterface $entity,
    $variables,
    $form
  ) {
    $renderer = \Drupal::service('renderer');

    if (!empty($form['form']['inline_entity_form']['count'])) {
      return $renderer->render($form['form']['inline_entity_form']['count']);
    }

    return '';
  }

  /**
   * @param \Drupal\restme_foods\Entity\OfferFoodInterface $entity
   * @param $variables
   * @param $form
   */
  public static function getFormatDropdown(
    OfferFoodInterface $entity,
    $variables,
    $form
  ) {
    return \Drupal::service('renderer')->render($form['form']['inline_entity_form']['format']);
  }


  /**
   * Returns food type label
   * @param \Drupal\restme_foods\Entity\OfferFoodInterface $entity
   * @param $variables
   * @return array
   */
  public static function getFoodTypeLabel(
    OfferFoodInterface $entity,
    $variables,
    $form
  ) {
    $output = '';

    if ($entity->getFood()) {
      $output = FormOffer::getFoodServiceLabel($entity->getFood());
    }

    return [
      '#markup' => $output,
    ];
  }

  /**
   * @param array $element
   * @return bool
   */
  public function isTableDragEnabled($element) {
    return TRUE;
  }
}
