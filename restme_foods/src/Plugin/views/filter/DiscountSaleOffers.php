<?php
namespace Drupal\restme_foods\Plugin\views\filter;

use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Фильтр готовых со скидкой
 *
 * @package Drupal\restme\Plugin\views\filter
 * @ingroup views_filter_handlers
 * @ViewsFilter("discount_sale_offers")
 */
class DiscountSaleOffers extends FilterPluginBase {
  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function valueForm(
    &$form,
    \Drupal\Core\Form\FormStateInterface $form_state
  ) {
    $form['value'] = [
      '#type' => 'checkbox',
      '#title' => 'Скидки и акции',
      '#default_value' => $this->value,
      '#value_callback' => [get_class($this), 'valueCallback'],
    ];
  }

  /**
   * Фильтр готовых со скидками
   */
  public function query() {
    $offer_cost = $this->query->ensureTable('node__field_offer_cost');
    $offer_total = $this->query->ensureTable('node__field_offer_total');

    $condition = new Condition('AND');

    $condition
      ->where("($offer_cost.field_offer_cost_value - $offer_total.field_offer_total_value) / $offer_cost.field_offer_cost_value * 100 >= 5")
      ->where("($offer_cost.field_offer_cost_value - $offer_total.field_offer_total_value) / $offer_cost.field_offer_cost_value * 100 <= 50");

    $this->query->addWhere($this->options['group'], $condition);
  }

  /**
   * @param $element
   * @param $input
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return int
   */
  public static function valueCallback($element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      // Use #default_value as the default value of a checkbox, except change
      // NULL to 0, because FormBuilder::handleInputElement() would otherwise
      // replace NULL with empty string, but an empty string is a potentially
      // valid value for a checked checkbox.
      return isset($element['#default_value']) ? $element['#default_value'] : 0;
    }
    else {
      return !empty($input) ? $element['#return_value'] : 0;
    }
  }


}
