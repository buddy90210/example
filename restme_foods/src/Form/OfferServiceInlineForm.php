<?php
/**
 * User: komm
 * Date: 25.07.18
 * Time: 11:19
 */

namespace Drupal\restme_foods\Form;


use Drupal\restme\Controller\FormOffer;
use Drupal\restme_foods\Entity\OfferServiceInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\inline_entity_form\Form\EntityInlineForm;

/**
 * Class OfferFoodsInlineForm
 * @package Drupal\restme_foods\Form
 */
class OfferServiceInlineForm extends OfferInlineFormRowBase {
  /**
   * @param \string[] $bundles
   * @return array
   */
  public function getTableFields($bundles) {
    $fields = [];

    $fields['service_id'] = [
      'type' => 'callback',
      'label' => 'Услуга',
      'callback' => [get_class($this), 'getServiceId'],
      'weight' => 1,
    ];

    $fields['type'] = [
      'type' => 'callback',
      'label' => '',
      'callback' => [get_class($this), 'getServiceTypeLabel'],
      'weight' => 2,
    ];

    $fields['count'] = [
      'type' => 'callback',
      'label' => 'Количество',
      'callback' => [get_class($this), 'getCount'],
      'weight' => 3,
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
   * @param \Drupal\restme_foods\Entity\OfferServiceInterface $entity
   * @param $variables
   * @param $form
   * @return string
   */
  public static function getServiceId(
    OfferServiceInterface $entity,
    $variables,
    $form
  ) {
    $renderer = \Drupal::service('renderer');

    if (!empty($form['form']['inline_entity_form']['service_id'])) {
      return $renderer->render($form['form']['inline_entity_form']['service_id']);
    }

    return '';
  }

  /**
   * @param \Drupal\restme_foods\Entity\OfferServiceInterface $entity
   * @param $variables
   * @param $form
   * @return string
   */
  public static function getCount(
    OfferServiceInterface $entity,
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
   * Returns food type label
   * @param \Drupal\restme_foods\Entity\OfferServiceInterface $entity
   * @param $variables
   * @return array
   */
  public static function getServiceTypeLabel(
    OfferServiceInterface $entity,
    $variables,
    $form
  ) {

    $output = '';

    if ($entity->getService()) {
      $output = FormOffer::getFoodServiceLabel($entity->getService());
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
